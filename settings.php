<?php
$cfg_tutorlink = get_config('block/tutorlink');

$roles=get_records('role');
$options = array();
foreach($roles as $role){
    $options[$role->id]=$role->name;
}
$settings->add(new admin_setting_configselect('tutorrole', get_string('tutorrole', 'block_tutorlink'),get_string('tutorrole_explain', 'block_tutorlink'), null, $options));
$settings->settings->tutorrole->plugin='block/tutorlink';

$settings->add(new admin_setting_configtext('cronfile', get_string('cronfile', 'block_tutorlink'),get_string('cronfiledesc', 'block_tutorlink'), NULL, PARAM_TEXT));
$settings->settings->cronfile->plugin='block/tutorlink';
?>