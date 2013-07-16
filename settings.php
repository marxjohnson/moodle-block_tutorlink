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
 * @package    block_tutorlink
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

// Only get the roles we're allowed to assign in user contexts.
$where = 'id IN (SELECT roleid FROM {role_context_levels} WHERE contextlevel = ?)';
$roles = $DB->get_records_select('role', $where, array(CONTEXT_USER));
if (count($roles) > 0) {
    $options = array();
    foreach ($roles as $role) {
        $options[$role->id] = $role->name;
    }

    // Select box for the role the block will assign.
    $settings->add(new admin_setting_configselect('block_tutorlink/tutorrole',
                                                get_string('tutorrole', 'block_tutorlink'),
                                                get_string('tutorrole_explain', 'block_tutorlink'),
                                                null,
                                                $options));
} else {
    $settings->add(new admin_setting_heading('block_tutorlink/noroles',
                                                '',
                                                get_string('noroles', 'block_tutorlink')));
}
// Full path of the file on the server to be processed by the cron job.
$settings->add(new admin_setting_configtext('block_tutorlink/cronfile',
                                          get_string('cronfile', 'block_tutorlink'),
                                          get_string('cronfiledesc', 'block_tutorlink'),
                                          null,
                                          PARAM_TEXT));
// Checkbox - keep old cron files?
$settings->add(new admin_setting_configcheckbox('block_tutorlink/keepprocessed',
                                              get_string('keepprocessed', 'block_tutorlink'),
                                              get_string('keepprocessedlong', 'block_tutorlink'),
                                              0,
                                              1,
                                              0));
// Path of the folder to keep old cron files in (if above is checked).
$settings->add(new admin_setting_configtext('block_tutorlink/cronprocessed',
                                          get_string('cronprocessed', 'block_tutorlink'),
                                          '',
                                          null,
                                          PARAM_TEXT));
// How many days to keep old cron files for (if above is checked).
$settings->add(new admin_setting_configtext('block_tutorlink/keepprocessedfor',
                                          get_string('keepprocessedfor', 'block_tutorlink'),
                                          '',
                                          null,
                                          PARAM_INT,
                                          2));
// Allow wildcard deletion?
$settings->add(new admin_setting_configcheckbox('block_tutorlink/wildcarddeletion',
                                          get_string('wildcarddeletion', 'block_tutorlink'),
                                          get_string('wildcarddeletiondesc', 'block_tutorlink'),
                                          0,
                                          1,
                                          0));
