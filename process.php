<?php

//get DB credentials from config.php
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/tutorlink/locallib.php');
require_once($CFG->dirroot.'/blocks/tutorlink/block_tutorlink_form.php');

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
        if (!has_capability('block/tutorlink:use', $PAGE->context)) {
            throw new tutorlink_exception('nopermission', '', 401);
        }

        //make sure that there is a tutorrole configured before we go assigning it
        if (get_config('block/tutorlink','tutorrole') === false) {
            throw new tutorlink_exception('notutorrole', '', 500);
        } else {
            $handler = new block_tutorlink_handler($data->tutorlink_csvfile);
            $handler->validate();
            $report = $handler->process();
        }

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
    if ($ajax) {
        header('HTTP/1.1 '.$e->http);
        die(get_string($e->errorcode, $e->module, $e->a));
    } else {
        print_error($e->errorcode, $e->module, '', $e->a);
    }
}

?>
