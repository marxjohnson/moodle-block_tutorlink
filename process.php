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
 * Handles submission of the tutorlink block's form and processes the file
 *
 * @package    block_tutorlink
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get DB credentials from config.php.
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/tutorlink/locallib.php');
require_once($CFG->dirroot.'/blocks/tutorlink/block_tutorlink_form.php');

// Find out if this is an asynchronous request.
$ajax = $_SERVER['HTTP_X_REQUESTED_WITH'];

$url = '/blocks/tutorlink/process.php';
$PAGE->set_url($url);
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->navbar->add(get_string('pluginname', 'block_tutorlink'));
require_login();
require_sesskey();

$mform = new block_tutorlink_form();

try {
    if ($data = $mform->get_data()) {
        // Check the user is allowed to use the block.
        if (!has_capability('block/tutorlink:use', $PAGE->context)) {
            throw new tutorlink_exception('nopermission', '', 401);
        }

        // Make sure that there is a tutorrole configured before we go assigning it.
        if (get_config('block_tutorlink', 'tutorrole') === false) {
            throw new tutorlink_exception('notutorrole', '', 500);
        } else {
            // Validate and process the file.
            $handler = new block_tutorlink_handler($data->tutorlink_csvfile);
            $handler->validate();
            $report = $handler->process();
        }

        // If it's a synchronous request, display a full page with the report
        // from the processing handler. Otherwise, just return the report.
        $PAGE->set_title(get_string('pluginname', 'block_tutorlink'));
        $PAGE->set_heading(get_string('pluginname', 'block_tutorlink'));
        if (!$ajax) {
            echo $OUTPUT->header();
        }
        echo $report;
        if (!$ajax) {
            echo $OUTPUT->footer();
        }
    } else {
        throw new tutorlink_exception('noform', '', 400);
    }
} catch (tutorlink_exception $e) {
    // If async, set the HTTP error code and print the message as plaintext.
    // Otherwise, display a full Moodle error message.
    if ($ajax) {
        header('HTTP/1.1 '.$e->http);
        die(get_string($e->errorcode, $e->module, $e->a));
    } else {
        print_error($e->errorcode, $e->module, '', $e->a);
    }
}
