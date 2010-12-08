<?php

class block_tutorlink extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_tutorlink');
    }

    function applicable_formats() {
        return array('site' => true,'my' => true);
    }
    function has_config() {return true;}

    function get_content () {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content->footer='';
        $this->content->text='';
        global $CFG;
        global $USER;
        global $OUTPUT;
        $context = get_context_instance(CONTEXT_SYSTEM);
       //only let people with permission use the block- everyone else will get an empty string
       if (has_capability('block/tutorlink:use', $context)) {
            //check that there is a tutor role configured
            if (get_config('block/tutorlink','tutorrole') === false) {
                $url = new moodleurl('/admin/settings.php', array('section' => 'blocksettingtutorlink'));
                $this->content->text .= get_string('notutorrole', 'block_tutorlink');
                $this->content->text .= html_writer::tag('a', get_string('blocksettings', 'block_tutorlink'), array('href' => $url->out(false)));
            } else {
                $this->content->text.= get_string('csvfile', 'block_tutorlink');
                $this->content->text .= $OUTPUT->help_icon('csv', 'block_tutorlink');
                $actionurl = new moodle_url('/blocks/tutorlink/process.php');
                $form = html_writer::start_tag('form', array('action' => $actionurl->out(false), 'method' => 'post', 'enctype' => 'multipart/form-data'));
                $form .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'courseid', 'value' => $this->page->course->id));
                $form .= html_writer::empty_tag('input', array('type' => 'file', 'name' => 'csvfile', 'id' => 'tutorlink_file'));
                $form .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'submit', 'value' => get_string('submit')));
                $form .= html_writer::end_tag('form');
                $this->content->text.= $form;            
            }
       }

       $jsmodule = array(
            'name'  =>  'block_tutorlink',
            'fullpath'  =>  '/blocks/tutorlink/module.js',
            'requires'  =>  array('base', 'node', 'json', 'io')
       );

       $this->page->requires->string_for_js('select', 'moodle');
       $this->page->requires->js_init_call('M.block_tutorlink.init', null, false, $jsmodule);

       return $this->content;
    }

    function cron() {

        global $CFG;
        require_once($CFG->dirroot.'/blocks/tutorlink/locallib.php');
        
        $cfg_tutorlink = get_config('block/tutorlink');

        if (is_file($cfg_tutorlink->cronfile)) {
            //process file
            $report = explode("\n", block_tutorlink_processfile($cfg_tutorlink->cronfile, true));

            if ($cfg_tutorlink->keepprocessed) {
                if (is_dir($cfg_tutorlink->cronprocessed) && is_writable($cfg_tutorlink->cronprocessed)) {
                    //move the processed file to prevent wasted time re-processing
                    $date = date('Ymd');
                    $filenames = new stdClass;
                    $filenames->old = $cfg_tutorlink->cronfile;
                    $filenames->new = $cfg_tutorlink->cronprocessed.'/'.basename($cfg_tutorlink->cronfile).'.'.$date;

                    if (rename($filenames->old, $filenames->new)) {
                        $report[] = get_string('cronmoved', 'block_tutorlink', $filenames);
                    } else {
                        $report[] = get_string('cronnotmoved', 'block_tutorlink', $filenames);
                    }
                } else {
                    $report[] = get_string('nodir', 'block_tutorlink', $cfg_tutorlink->cronprocessed);
                }
            }

            if ($cfg_tutorlink->keepprocessedfor > 0) {
                $removed = 0;
                $processed = scandir($cfg_tutorlink->cronprocessed);
                foreach ($processed as $processed) {
                    if (is_file($cfg_tutorlink->cronprocessed.'/'.$processed)) {
                        $path_parts = pathinfo($cfg_tutorlink->cronprocessed.'/'.$processed);
                        $ext = $path_parts['extension'];
                        $threshold = date('Ymd', time()-($cfg_tutorlink->keepprocessedfor*86400));
                        if ($path_parts['filename'] == basename($cfg_tutorlink->cronfile) && $ext < $threshold) {
                            if (unlink($cfg_tutorlink->cronprocessed.'/'.$processed)) {
                                $removed++;
                            } else {
                                $report[] = get_string('cantremoveold', 'block_tutorlink', $cfg_tutorlink->cronprocessed.'/'.$processed);
                            }
                        }
                    }
                }
                if ($removed) {
                    $report[] = get_string('removedold', 'block_tutorlink', $removed);
                }
            }
            //email outcome to admin
            $email = implode("\n", $report);
            email_to_user(get_admin(), get_admin(), get_string('tutorlink_log','block_tutorlink'), $report);
            foreach($report as $line) {
                mtrace($line);
            }

        } else {
            mtrace(get_string('nocronfile', 'block_tutorlink'));
        }
        return true;
        
    }
}    
?>