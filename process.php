<?php

//get DB credentials from config.php
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/tutorlink/locallib.php');
require_once($CFG->dirroot.'/blocks/tutorlink/block_tutorlink_form.php');

$url = '/blocks/tutorlink/process.php';
$PAGE->set_url($url);
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->navbar->add(get_string('pluginname', 'block_tutorlink'));
require_login();
require_sesskey();

$mform = new block_tutorlink_form();

if ($data = $mform->get_data()) {

    if (!has_capability('block/tutorlink:use', $PAGE->context)) {
        print_error('nopermission','block_tutorlink');
    }

    //make sure that there is a tutorrole configured before we go assigning it
    if (get_config('block/tutorlink','tutorrole') === false) {
        print_error('notutorrole','block_tutorlink');
    } else {
        $handler = new block_tutorlink_handler($data->csvfile);
        try {
            $handler->validate();
        } catch (tutorlink_exception $e) {
            print_error($e->errorcode, $e->module, '', $e->a);
        }
        $report = $handler->process();
    }

    $PAGE->set_title(get_string('pluginname', 'block_tutorlink'));
    $PAGE->set_heading(get_string('pluginname', 'block_tutorlink'));
    echo $OUTPUT->header();
    echo $report;
    echo $OUTPUT->footer();
} else {
    print_error('noform', 'block_tutorlink');
}

?>
