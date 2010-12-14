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
 * Defines admin settings for the tutorlink block
 *
 * @package    blocks
 * @subpackage  tutorlink
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Only get the roles we're allowed to assign in user contexts
    $where = 'id IN (SELECT roleid FROM {role_context_levels} WHERE contextlevel = ?)';
    $roles = $DB->get_records_select('role', $where, array(CONTEXT_USER));
    $options = array();
    foreach($roles as $role){
        $options[$role->id] = $role->name;
    }

    $configs = array();
    // Select box for the role the block will assign
    $configs[] = new admin_setting_configselect('tutorrole', get_string('tutorrole', 'block_tutorlink'),get_string('tutorrole_explain', 'block_tutorlink'), null, $options);
    // Full path of the file on the server to be processed by the cron job.
    $configs[] = new admin_setting_configtext('cronfile', get_string('cronfile', 'block_tutorlink'),get_string('cronfiledesc', 'block_tutorlink'), NULL, PARAM_TEXT);
    // Checkbox - keep old cron files?
    $configs[] = new admin_setting_configcheckbox('keepprocessed', get_string('keepprocessed', 'block_tutorlink'), get_string('keepprocessedlong', 'block_tutorlink'), 0, 1, 0);
    // Path of the folder to keep old cron files in (if above is checked)
    $configs[] = new admin_setting_configtext('cronprocessed', get_string('cronprocessed', 'block_tutorlink'), '', null, PARAM_TEXT);
    // How many days to keep old cron files for (if above is checked)
    $configs[] = new admin_setting_configtext('keepprocessedfor', get_string('keepprocessedfor', 'block_tutorlink'), '', null, PARAM_INT, 2);

    foreach ($configs as $config) {
        $config->plugin = 'block/tutorlink';
        $settings->add($config);
    }
}
?>