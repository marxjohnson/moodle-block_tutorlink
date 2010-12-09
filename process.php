<?php

//get DB credentials from config.php
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/tutorlink/locallib.php');

$courseid = required_param('courseid', PARAM_INT);
$file = optional_param('filedata', '', PARAM_TEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}
$url = new moodle_url('/blocks/tutorlink/process.php', array('courseid' => $course->id));

$PAGE->set_url($url);

require_login($course);
require_sesskey();
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));

if (!has_capability('block/tutorlink:use', $PAGE->context)) {
    print_error('nopermission','block_tutorlink');
}

//make sure that there is a tutorrole configured before we go assigning it
if (get_config('block/tutorlink','tutorrole') === false) {
    print_error('notutorrole','block_tutorlink');
} else {
    $handler = new block_tutorlink_handler($_FILES['csvfile']['tmp_name']);
    try {
        $handler->validate();
    } catch (tutorlink_exception $e) {
        print_error($e->errorcode, $e->module, '', $e->a);
    }
    $report = $handler->process();
}

$PAGE->set_title('Create Tutor Assignments');
$PAGE->set_heading('Create Tutor Assignments');
echo $OUTPUT->header();
echo $report;
echo $OUTPUT->footer($course);
?>
