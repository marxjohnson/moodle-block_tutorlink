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
 * Defines custom behat steps for tutorlink block
 *
 * @package    block_tutorlink
 * @author      Mark Johnson <mark@barrenfrozenwasteland.com>
 * @copyright   2013 Mark Johnson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given;
use Behat\Behat\Context\Step\When as When;
use Behat\Behat\Context\Step\Then as Then;
use Behat\Gherkin\Node\TableNode as TableNode;

class behat_tutorlink extends behat_base {

    /**
     * Sets configuration for the block_tutorlink plugin. A table with | Setting name | value | is expected.
     *
     * @Given /^tutorlink has the following settings:$/
     */
    public function tutorlink_has_the_following_settings(TableNode $table) {

        if (!$data = $table->getRowsHash()) {
            return;
        }

        $steps = array(
            new Given('I am on homepage'),
            new Given('I expand "Site administration" node'),
            new Given('I expand "Plugins" node'),
            new Given('I expand "Blocks" node'),
            new Given('I follow "Upload tutor relationships"'),
        );

        foreach ($data as $name => $value) {
            if ($name == "tutorrole") {
                $steps[] = new When('I select "'.$value.'" from "s_block_tutorlink_tutorrole"');
            } else if ($name == "keepprocessed" || $name == 'wildcarddeletion') {
                if ($value == true) {
                    $steps[] = new When('I check "s_block_tutorlink_'.$name.'"');
                } else {
                    $steps[] = new When('I uncheck "s_block_tutorlink_'.$name.'"');
                }
            } else {
                $steps[] = new Given('I fill in "s_block_tutorlink_'.$name.'" with "'.$value.'"');
            }
        }

        $steps[] = new When('I press "Save changes"');

        return $steps;
    }

    /**
     * Creates a role that's assignable in the user context and
     * sets it as the tutor role.
     *
     * @Given /^role "(?P<rolename_string>(?:[^"]|\\")*)" exists and can be assigned in user contexts$/
     */
    public function role_exists_and_can_be_assigned_in_user_contexts($rolename) {
        global $CFG;

        return array(
            $this->getSession()->visit($CFG->wwwroot.'/admin/roles/manage.php'),
            new When('I press "Add a new role"'),
            new Given('I fill in "Short name" with "'.$rolename.'"'),
            new Given('I fill in "Custom full name" with "'.$rolename.'"'),
            new Given('I check "User"'),
            new When('I press "Create this role"')
        );
    }

    /**
     * Creates a file with the given data and runs the cron.
     * A table with | linenumber | operation | tutor_idnum | student_idnum | is expected
     *
     * @When /^the following tutorlink file is processed:$/
     */
    public function the_following_tutorlink_file_is_processed(TableNode $table) {
        global $CFG, $DB;
        if (!$data = $table->getRowsHash()) {
            return;
        }

        $cronfile = get_config('block_tutorlink', 'cronfile');

        $fh = fopen($cronfile, 'w+');

        foreach ($data as $linenum => $line) {
            if (!is_numeric($linenum)) {
                continue;
            }
            fputcsv($fh, $line);
        }

        fclose($fh);

        $DB->set_field('block', 'lastcron', '0', array('name' => 'tutorlink'));

        return array(
            $this->getSession()->visit($CFG->wwwroot.'/admin/cron.php'),
            new Given('I wait until the page is ready')
        );
    }

    /**
     * Checks that the first user is assigned to the second
     *
     * @then /^user "(?P<username1_string>(?:[^"]|\\")*)" should be the tutor of user "(?P<username2_string>(?:[^"]|\\")*)"$/
     */
    public function user1_should_be_the_tutor_of_user2($username1, $username2) {
        global $DB, $CFG;

        $user1 = $DB->get_record('user', array('username' => $username1));
        $user2 = $DB->get_record('user', array('username' => $username2));
        $roleid = get_config('block_tutorlink', 'tutorrole');
        $rolename = $DB->get_field('role', 'name', array('id' => $roleid));

        return array(
            $this->getSession()->visit($CFG->wwwroot.'/admin/user.php'),
            new Given('I wait until the page is ready'),
            new Given('I follow "'.fullname($user1).'"'),
            new Given('I expand "Roles" node'),
            new Given('I follow "This user\'s role assignments"'),
            new Then("\"//h4[@class='contextname']/a[text()='User: ".fullname($user2)."']\" \"xpath_element\" should exists"),
            new Then("I should see \"".$rolename
                ."\" in the \"//h4[@class='contextname']/a[text()='User: ".fullname($user2)."']/../../p\" \"xpath_element\"")
        );
    }

    /**
     * Checks that the first user is assigned to the second
     *
     * @then /^user "(?P<username1_string>(?:[^"]|\\")*)" should not be the tutor of user "(?P<username2_string>(?:[^"]|\\")*)"$/
     */
    public function user1_should_not_be_the_tutor_of_user2($username1, $username2) {
        global $DB, $CFG;

        $user1 = $DB->get_record('user', array('username' => $username1));
        $user2 = $DB->get_record('user', array('username' => $username2));
        $roleid = get_config('block_tutorlink', 'tutorrole');
        $rolename = $DB->get_field('role', 'name', array('id' => $roleid));

        return array(
            $this->getSession()->visit($CFG->wwwroot.'/admin/user.php'),
            new Given('I wait until the page is ready'),
            new Given('I follow "'.fullname($user1).'"'),
            new Given('I expand "Roles" node'),
            new Given('I follow "This user\'s role assignments"'),
            new Then('I should not see "User: '.fullname($user2).'" in the ".region-content .generalbox" "css_element"')
        );
    }

    /**
     * Initialises an xdebug session
     *
     * @given /^I start debugging$/
     */
    public function i_start_debugging() {
        global $CFG;
        return array(
            $this->getSession()->visit($CFG->wwwroot.'/?XDEBUG_SESSION_START=1'),
            new Given('I wait until the page is ready'),
            new Then('I wait "5" seconds')
        );
    }

    /**
     * Ends a debugging session
     *
     * @then /^I stop debugging$/
     */
    public function i_stop_debugging() {
        global $CFG;
        return array(
            $this->getSession()->visit($CFG->wwwroot.'/?XDEBUG_SESSION_STOP=1'),
            new Given('I wait until the page is ready')
        );
    }
}
