<?php

//get DB credentials from config.php
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/tutorlink/locallib.php');

//$csvfilename=required_param('csvfile',PARAM_FILE);

global $CFG;
global $USER;
$context = get_context_instance(CONTEXT_SYSTEM);
if (!has_capability('block/tutorlink:use', $context)) {
    print_error('nopermission','block_tutorlink');
}

$cfg_tutorlink = get_config('block/tutorlink');

//make sure that there is a tutorrole configured before we go assigning it
if(!$tutorrole=$cfg_tutorlink->tutorrole){
    echo get_string('notutorrole','block_tutorlink');
}else{
    echo block_tutorlink_processfile($_FILES['csvfile']['tmp_name']);
}
?>
