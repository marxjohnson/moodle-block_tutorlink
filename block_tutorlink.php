<?php

class block_tutorlink extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_tutorlink');
        $this->cron=300;        
    }

    function applicable_formats() {
        return array('site' => true,'my' => true);
    }
    function has_config() {return true;}

    function get_content () {
        $this->content->footer='';
        $this->content->text='';
        global $CFG;
        global $USER;
        $context = get_context_instance(CONTEXT_SYSTEM);
       //only let people with permission use the block- everyone else will get an empty string
       if (has_capability('block/tutorlink:use', $context)) {
            //check that there is a tutor role configured
            if(get_config('block/tutorlink','tutorrole')===false){
                $this->content->text.=get_string('notutorrole','block_tutorlink',$CFG->wwwroot.'/admin/settings.php?section=blocksettingtutorlink');
            }else{
                $this->content->text.= get_string('csvfile','block_tutorlink');
                $this->content->text.='<form target="blank" action="'.$CFG->wwwroot.'/blocks/tutorlink/process.php" method="post" enctype="multipart/form-data"><input type="file" name="csvfile"><input type="submit"></form>';
                         
            }
       }

        return $this->content;
    }
    function cron(){
        global $CFG;
        require_once($CFG->dirroot.'/blocks/tutorlink/locallib.php');
        $cfg_tutorlink = get_config('block/tutorlink');
        if(is_file($cfg_tutorlink->cronfile)){
            //process file
            $results=block_tutorlink_processfile($cfg_tutorlink->cronfile,true);

            //move the processed file to prevent wasted time re-processing
            $date=date('Ymd');
            if(rename($cfg_tutorlink->cronfile,$cfg_tutorlink->cronfile.'.'.$date)){
                $results.= "\n\n".$cfg_tutorlink->cronfile.' moved to '.$cfg_tutorlink->cronfile.'.'.$date."\n";
            }
            
            //email outcome to admin
            email_to_user(get_admin(), get_admin(),get_string('tutorlink_log','block_tutorlink'),$results);

        }
        
    }
}    
?>