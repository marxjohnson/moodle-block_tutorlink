<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $where = 'id IN (SELECT roleid FROM {role_context_levels} WHERE contextlevel = ?)';
    $roles = $DB->get_records_select('role', $where, array(CONTEXT_USER));
    $options = array();
    foreach($roles as $role){
        $options[$role->id] = $role->name;
    }

    $configs = array();
    $configs[] = new admin_setting_configselect('tutorrole', get_string('tutorrole', 'block_tutorlink'),get_string('tutorrole_explain', 'block_tutorlink'), null, $options);
    $configs[] = new admin_setting_configtext('cronfile', get_string('cronfile', 'block_tutorlink'),get_string('cronfiledesc', 'block_tutorlink'), NULL, PARAM_TEXT);

    $configs[] = new admin_setting_configcheckbox('keepprocessed', get_string('keepprocessed', 'block_tutorlink'), get_string('keepprocessedlong', 'block_tutorlink'), 0, 1, 0);
    $configs[] = new admin_setting_configtext('cronprocessed', get_string('cronprocessed', 'block_tutorlink'), '', null, PARAM_TEXT);
    $configs[] = new admin_setting_configtext('keepprocessedfor', get_string('keepprocessedfor', 'block_tutorlink'), '', null, PARAM_INT, 2);

    foreach ($configs as $config) {
        $config->plugin = 'block/tutorlink';
        $settings->add($config);
    }
}
?>