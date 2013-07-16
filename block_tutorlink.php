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
 * Define the tutorlink block's class
 *
 * @package    block_tutorlink
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Tutorlink Block's class
 */
class block_tutorlink extends block_base {

    /**
     * Set the title
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_tutorlink');
    }

    public function has_config() {
        return true;
    }

    /**
     * Set where the block should be allowed to be added
     *
     * This block is an admin tool, so site and my moodle pages should be fine.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('site' => true, 'my' => true);
    }

    /**
     * Generate the contents for the block
     *
     * Check if there has been a tutor role set. If there has, display the form
     * for choosing a file. If not, display a message with a link to the
     * settings page. Also initaliases Javascript for asynchronous processing.
     *
     * @global object $CFG Global config object
     * @global object $USER Current user record
     * @return object Block contents and footer
     */
    public function get_content () {
        global $CFG;
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;

        $this->content->footer='';
        $this->content->text='';
        $context = get_context_instance(CONTEXT_SYSTEM);
        // Only let people with permission use the block- everyone else will get an empty string.
        if (has_capability('block/tutorlink:use', $context)) {
            // Check that there is a tutor role configure.
            if (get_config('block_tutorlink', 'tutorrole') === false) {
                $urlparams = array('section' => 'blocksettingtutorlink');
                $url = new moodle_url('/admin/settings.php', $urlparams);
                $this->content->text .= get_string('notutorrole', 'block_tutorlink');
                $strsettings = get_string('blocksettings', 'block_tutorlink');
                $this->content->text .= html_writer::tag('a', $strsettings, array('href' => $url));
            } else {
                require_once($CFG->dirroot.'/blocks/tutorlink/block_tutorlink_form.php');
                $url = new moodle_url('/blocks/tutorlink/process.php');
                $mform = new block_tutorlink_form($url->out());
                $form = $mform->display();
                $this->content->text.= $form;
            }
        }

        $jsmodule = array(
            'name'  =>  'block_tutorlink',
            'fullpath'  =>  '/blocks/tutorlink/module.js',
            'requires'  =>  array('base', 'node', 'io', 'overlay')
        );

        $this->page->requires->string_for_js('upload', 'moodle');
        $this->page->requires->string_for_js('pluginname', 'block_tutorlink');
        $this->page->requires->js_init_call('M.block_tutorlink.init', null, false, $jsmodule);

        return $this->content;
    }

    /**
     * Cron Function - checks for existence of cron file, and processes
     *
     * If the cron file exists, it is validated and processed.  If specified, it
     * is then moved to a folder for processed files, otherwise it's deleted.
     * Old processed files which are no longer needed are then deleted.
     *
     * @global object $CFG Global config object
     * @return bool
     */
    public function cron() {

        global $CFG;
        require_once($CFG->dirroot.'/blocks/tutorlink/locallib.php');

        $cfg = get_config('block_tutorlink');

        if (is_file($cfg->cronfile)) {
            $report = array();
            $handler = new block_tutorlink_handler($cfg->cronfile);
            try {
                $handler->validate();
                // Process file.
                $report = explode("\n", $handler->process(true));
                $procdir = $cfg->cronprocessed;

                if ($cfg->keepprocessed) {
                    if (is_dir($procdir) && is_writable($procdir)) {
                        // Move the processed file to prevent wasted time re-processing.
                        $date = date('Ymd');
                        $filenames = new stdClass;
                        $filenames->old = $cfg->cronfile;
                        $filenames->new = $procdir.'/'.basename($cfg->cronfile).'.'.$date;

                        if (rename($filenames->old, $filenames->new)) {
                            $report[] = get_string('cronmoved', 'block_tutorlink', $filenames);
                        } else {
                            $report[] = get_string('cronnotmoved', 'block_tutorlink', $filenames);
                        }
                    } else {
                        $report[] = get_string('nodir', 'block_tutorlink', $procdir);
                    }
                } else {
                    unlink($cfg->cronfile);
                }

                if ($cfg->keepprocessedfor > 0) {
                    $removed = 0;
                    $processed = scandir($procdir);
                    foreach ($processed as $processed) {
                        if (is_file($procdir.'/'.$processed)) {
                            $parts = pathinfo($procdir.'/'.$processed);
                            $ext = $parts['extension'];
                            $threshold = date('Ymd', time()-($cfg->keepprocessedfor*86400));
                            $istutorlinkfile = $parts['filename'] == basename($cfg->cronfile);
                            if ($istutorlinkfile && $ext < $threshold) {
                                if (unlink($procdir.'/'.$processed)) {
                                    $removed++;
                                } else {
                                    $report[] = get_string('cantremoveold',
                                                           'block_tutorlink',
                                                           $procdir.'/'.$processed);
                                }
                            }
                        }
                    }
                    if ($removed) {
                        $report[] = get_string('removedold', 'block_tutorlink', $removed);
                    }
                }
                // Email outcome to admin.
                $email = implode("\n", $report);
            } catch (tutorlink_exception $e) {
                $message = get_string($e->errorcode, $e->module, $e->a);
                $email = $message;
                $report[] = $message;
            }
            email_to_user(get_admin(),
                          get_admin(),
                          get_string('tutorlink_log', 'block_tutorlink'),
                          $email);
            foreach ($report as $line) {
                mtrace($line);
            }
        } else {
            mtrace(get_string('nocronfile', 'block_tutorlink'));
        }
        return true;

    }

}
