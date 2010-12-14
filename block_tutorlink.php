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
            //check that there is a tutor role configure
            if (get_config('block/tutorlink','tutorrole') === false) {
                $url = new moodleurl('/admin/settings.php', array('section' => 'blocksettingtutorlink'));
                $this->content->text .= get_string('notutorrole', 'block_tutorlink');
                $this->content->text .= html_writer::tag('a', get_string('blocksettings', 'block_tutorlink'), array('href' => $url->out(false)));
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
            'requires'  =>  array('base', 'node', 'json', 'io', 'uploader', 'overlay')
       );

       $jsdata = array(
           'courseid'   =>  $this->page->course->id,
           'userid'     =>  $USER->id,
           'sesskey'    =>  $USER->sesskey,
           'sname'  =>  session_name(),
           'sid'    =>  session_id()
       );
       
       $this->page->requires->string_for_js('upload', 'moodle');
       $this->page->requires->string_for_js('pluginname', 'block_tutorlink');
       //$this->page->requires->js_init_call('M.block_tutorlink.init', $jsdata, false, $jsmodule);

       return $this->content;
    }

    function cron() {

        global $CFG;
        require_once($CFG->dirroot.'/blocks/tutorlink/locallib.php');
        
        $cfg_tutorlink = get_config('block/tutorlink');

        if (is_file($cfg_tutorlink->cronfile)) {
            $report = array();
            $handler = new block_tutorlink_handler($cfg_tutorlink->cronfile);
            try {
                $handler->validate();            
                //process file
                $report = explode("\n", $handler->process(true));

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
            } catch (tutorlink_exception $e) {
                $message = get_string($e->errorcode, $e->component, $e->a);
                $email = $message;
                $report[] = $message;
            }
            email_to_user(get_admin(), get_admin(), get_string('tutorlink_log','block_tutorlink'), $email);
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