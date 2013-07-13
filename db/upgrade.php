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
 * Version upgrade scripts for the Tutor Link block
 *
 * @package    block_tutorlink
 * @author     Mark Johnson <mark@barrenfrozenwasteland.com>
 * @copyright  2012 onwards Mark Johnson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_block_tutorlink_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager();

    $result = true;

    // And upgrade begins here. For each one, you'll need one
    // block of code similar to the next one. Please, delete
    // this comment lines once this file start handling proper
    // upgrade code.

    if ($result && $oldversion < 2013071300) {
        $settings = (array)get_config('block/tutorlink');
        foreach ($settings as $setting => $value) {
            set_config($setting, $value, 'block_tutorlink');
        }

        upgrade_block_savepoint(true, 2013071300, 'block_tutorlink');
    }

    return $result;
}
