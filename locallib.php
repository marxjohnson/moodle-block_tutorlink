<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Defines classes for use in the tutorlink block
 *
 * @package    block_tutorlink
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Validates and processes files for the tutorlink block
 */
class block_tutorlink_handler {

    /**
     * The path of the cron file, or ID of the file uploaded through the form
     *
     * @var string
     */
    private $filename;

    /**
     * tutorlink configuration
     *
     * @var object
     */
    private $cfg;

    /**
     * Constructor, sets the filename
     *
     * @param string $filename
     */
    public function __construct($filename) {
        $this->filename = $filename;
        $this->cfg = get_config('block_tutorlink');
    }

    /**
     * Attempts to open the file
     *
     * Check if the filename is a path to a cron file. If so, open it normally.
     * If not, assume it's an uploaded file and open it using the File API.
     * Return the file handler.
     *
     * @throws tutorlink_exception if the file can't be opened for reading
     * @global object $USER
     * @return object File handler
     */
    public function open_file() {
        global $USER;
        if (is_file($this->filename)) {
            if (!$file = fopen($this->filename, 'r')) {
                throw new tutorlink_exception('cantreadcsv', '', 500);
            }
        } else {
            $fs = get_file_storage();
            $context = get_context_instance(CONTEXT_USER, $USER->id);
            $files = $fs->get_area_files($context->id,
                                         'user',
                                         'draft',
                                         $this->filename,
                                         'id DESC',
                                         false);
            if (!$files) {
                throw new tutorlink_exception('cantreadcsv', '', 500);
            }
            $file = reset($files);
            if (!$file = $file->get_content_file_handle()) {
                throw new tutorlink_exception('cantreadcsv', '', 500);
            }
        }
        return $file;
    }

    /**
     * Checks that the file is valid CSV in the expected format
     *
     * Opens the file, then checks each row contains 3 comma-separated values
     *
     * @see open_file()
     * @throws tutorlink_exeption if there are the wrong number of columns
     * @return true on success
     */
    public function validate() {
        $line = 0;
        $file = $this->open_file();
        while ($csvrow = fgetcsv($file)) {
            $line++;
            if (count($csvrow) < 3) {
                throw new tutorlink_exception('toofewcols', $line, 415);
            }
            if (count($csvrow) > 3) {
                throw new tutorlink_exception('toomanycols', $line, 415);
            }
        }
        fclose($file);
        return true;
    }

    /**
     * Processes the file to assign a user to another user's context
     *
     * Opens the file, loops through each row. Cleans the values in each column,
     * checks that the operation is valid and the user's exist. If all is well,
     * assigns or removes the user in column 2 to/from the user is column 3's
     * context as specified.
     * Returns a report of successess and failures.
     *
     * @see open_file()
     * @global object $DB Database interface
     * @param bool $plaintext Return report as plain text, rather than HTML?
     * @return string A report of successes and failures.S
     */
    public function process($plaintext = false) {
        global $DB;
        // Get the block's configuration, so we know the ID of the role we're assigning.
        $report = array();
        // Set the newline character.
        if ($plaintext) {
            $nl = "\n";
        } else {
            $nl = '<br />';
        }

        // Set a counter so we can report line numbers for errors.
        $line = 0;

        // Open the file.
        $file = $this->open_file();

        // Loop through each row of the file.
        while ($csvrow = fgetcsv($file)) {
            $line++;
            // Clean idnumbers to prevent sql injection.
            $op = strtolower(clean_param($csvrow[0], PARAM_ALPHANUM));
            $strings = new stdClass;
            $strings->line = $line;
            $strings->op = $op;

            try {
                $tutoridnum = $this->clean_wildcard($csvrow[1], $strings);
                $studentidnum = $this->clean_wildcard($csvrow[2], $strings);
            } catch (moodle_exception $e) {
                $report[] = $e->getMessage();
                continue;
            }

            // Need to check the line is valid. If not, add a message to the
            // report and skip the line.

            // Check we've got a valid operation.
            if (!in_array($op, array('add', 'del'))) {
                $report[] = get_string('invalidop', 'block_tutorlink', $strings);
                continue;
            }

            $tutorparams = array(
                'roleid' => $this->cfg->tutorrole
            );

            // Check the user we're assigning exists.
            if (!($op == 'del' && $tutoridnum == '*')) {
                if (!$tutor = $DB->get_record('user', array('idnumber' => $tutoridnum))) {
                    $report[] = get_string('tutornotfound', 'block_tutorlink', $strings);
                    continue;
                }
                $strings->tutor = fullname($tutor);
                $tutorparams['userid'] = $tutor->id;
            }

            if (!($op == 'del' && $studentidnum == '*')) {
                // Check the user we're assigning to exists.
                if (!$student = $DB->get_record('user', array('idnumber' => $studentidnum))) {
                    $report[] = get_string('tuteenotfound', 'block_tutorlink', $strings);
                    continue;
                }
                $strings->student = fullname($student);
                $studentcontext = context_user::instance($student->id);
                $tutorparams['contextid'] = $studentcontext->id;
            }

            if ($op == 'del') {
                // If we're deleting, check the tutor is already assigned to the
                // student, and remove the assignment.  Skip the line if they're
                // not.
                if ($tutoridnum == '*') {
                    if ($assignments = $DB->get_records('role_assignments', $tutorparams)) {
                        foreach ($assignments as $assignment) {
                            $tutor = $DB->get_record('user',
                                array('id' => $assignment->userid));
                            $strings->tutor = fullname($tutor);
                            role_unassign($this->cfg->tutorrole,
                                          $tutor->id,
                                          $studentcontext->id);
                            $report[] =  get_string('reldeleted', 'block_tutorlink', $strings);
                        }
                    } else {
                        $report[] =  get_string('norelforwildtutor', 'block_tutorlink', $strings);
                    }
                } else if ($studentidnum == '*') {
                    if ($assignments = $DB->get_records('role_assignments', $tutorparams)) {
                        foreach ($assignments as $assignment) {
                            $studentcontext = context::instance_by_id($assignment->contextid);
                            if ($studentcontext->contextlevel == CONTEXT_USER) {
                                $student = $DB->get_record('user',
                                    array('id' => $studentcontext->instanceid));
                                $strings->student = fullname($student);
                                role_unassign($this->cfg->tutorrole,
                                              $tutor->id,
                                              $studentcontext->id);
                                $report[] =  get_string('reldeleted', 'block_tutorlink', $strings);
                            }
                        }
                    } else {
                        $report[] =  get_string('norelforwildstudent', 'block_tutorlink', $strings);
                    }
                } else {
                    if ($DB->record_exists('role_assignments', $tutorparams)) {
                        role_unassign($this->cfg->tutorrole, $tutor->id, $studentcontext->id);
                        $report[] =  get_string('reldeleted', 'block_tutorlink', $strings);
                    } else {
                        $report[] =  get_string('reldoesntexist', 'block_tutorlink', $strings);
                    }
                }
            } else {
                // If we're adding, check that the tutor is not already assigned
                // to the student, and add them. Skip the line if they are.
                if ($DB->get_record('role_assignments', $tutorparams)) {
                    $report[] = get_string('relalreadyexists', 'block_tutorlink', $strings);
                } else if (role_assign($this->cfg->tutorrole, $tutor->id, $studentcontext->id)) {
                    $report[] = get_string('reladded', 'block_tutorlink', $strings);
                } else {
                    $report[] = get_string('reladderror', 'block_tutorlink', $strings);
                }
            }
        }
        fclose($file);
        return implode($nl, $report);
    }

    private function clean_wildcard($value, $strings) {
        if (trim($value) == '*') {
            if ($strings->op != 'del') {
                throw new moodle_exception('invalidop', 'block_tutorlink', '', $strings);
            }
            if (!$this->cfg->wildcarddeletion) {
                throw new moodle_exception('wildcardisabled', 'block_tutorlink', '', $strings);
            }
            return trim($value);
        } else {
            return clean_param($value, PARAM_ALPHANUM);
        }
    }
}

/**
 * An exception for reporting errors when processing tutorlink files
 *
 * Extends the moodle_exception with an http property, to store an HTTP error
 * code for responding to AJAX requests.
 */
class tutorlink_exception extends moodle_exception {

    /**
     * Stores an HTTP error code
     *
     * @var int
     */
    public $http;

    /**
     * Constructor, creates the exeption from a string identifier, string
     * parameter and HTTP error code.
     *
     * @param string $errorcode
     * @param string $a
     * @param int $http
     */
    public function __construct($errorcode, $a, $http) {
        parent::__construct($errorcode, 'block_tutorlink', '', $a);
        $this->http = $http;
    }
}
