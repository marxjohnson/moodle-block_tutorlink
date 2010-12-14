<?php
/**
 * Processes a file and assigns one user in the user context of another
 * 
 * @param $filename string absolute filename pointing to an appropriately formatted csv file
 * @param plaintext boolean should html be excluded in favour of plain text?
 * 
 * $return string list of successes and failures
 */

class block_tutorlink_handler {

    private $filename;

    function __construct($filename) {
        $this->filename = $filename;
    }

    function open_file() {
        global $USER;
        if (is_file($this->filename)) {
            if (!$file = fopen($this->filename, 'r')) {
                throw new tutorlink_exception('cantreadcsv', '', 500);
            }
        } else {
            $fs = get_file_storage();
            $context = get_context_instance(CONTEXT_USER, $USER->id);
            if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $this->filename, 'id DESC', false)) {
                throw new tutorlink_exception('cantreadcsv', '', 500);
            }
            $file = reset($files);
            if (!$file = $file->get_content_file_handle()) {
                throw new tutorlink_exception('cantreadcsv', '', 500);
            }
        }
        return $file;
    }

    function validate() {
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

    function process($plaintext = false) {
        global $DB;
        $cfg_tutorlink = get_config('block/tutorlink');
        $report = array();
        if ($plaintext) {
            $nl = "\n";
        } else {
            $nl = '<br />';
        }

        $line = 0;

        $file = $this->open_file();

        while ($csvrow = fgetcsv($file)) {
            $line++;
            //clean idnumbers to prevent sql injection
            $op = clean_param($csvrow[0], PARAM_ALPHANUM);
            $tutor_idnum = clean_param($csvrow[1], PARAM_ALPHANUM);
            $student_idnum = clean_param($csvrow[2], PARAM_ALPHANUM);
            $strings = new stdClass;
            $strings->line = $line;
            $strings->op = $op;

            if (!in_array($op, array('add', 'del'))) {
                $report[] = get_string('invalidop', 'block_tutorlink', $strings);
                continue;
            }
            if (!$tutor = $DB->get_record('user', array('idnumber' => $tutor_idnum))) {
                $report[] = get_string('tutornotfound', 'block_tutorlink', $strings);
                continue;
            }
            if (!$student = $DB->get_record('user', array('idnumber' => $student_idnum))) {
                $report[] = get_string('tuteenotfound','block_tutorlink', $strings);
                continue;
            }

            $strings->student = fullname($student);
            $strings->tutor = fullname($tutor);
            //both users exist
            $studentcontext = get_context_instance(CONTEXT_USER, $student->id);

            if ($op == 'del') {
                if ($DB->get_record('role_assignments', array('contextid' => $studentcontext->id, 'userid' => $tutor->id, 'roleid' => $cfg_tutorlink->tutorrole))) {
                    role_unassign($cfg_tutorlink->tutorrole, $tutor->id, $studentcontext->id);
                    $report[] =  get_string('reldeleted','block_tutorlink', $strings);
                } else {
                    $report[] =  get_string('reldoesntexist', 'block_tutorlink', $strings);
                }
            } else {
                // default to adding if not recognised as del
                if ($DB->get_record('role_assignments', array('contextid' => $studentcontext->id, 'userid' => $tutor->id, 'roleid' => $cfg_tutorlink->tutorrole))) {
                    $report[] = get_string('relalreadyexists','block_tutorlink', $strings);
                } else if (role_assign($cfg_tutorlink->tutorrole, $tutor->id, $studentcontext->id)) {
                    $report[] = get_string('reladded','block_tutorlink', $strings);
                } else {
                    $report[] = get_string('reladderror','block_tutorlink', $strings);
                }
            }
        }
        fclose($file);
        return implode($nl, $report);
    }
}

class tutorlink_exception extends moodle_exception {
    public $http;

    public function __construct($errorcode, $a, $http) {
        parent::__construct($errorcode, 'block_tutorlink', '', $a);
        $this->http = $http;
    }
}