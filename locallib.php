<?php
/**
 * Processes a file and assigns one user in the user conrttext of another
 * 
 * @param $filename string absolute filename pointing to an appropriately formatted csv file
 * @param plaintext boolean should html be excluded in favour of plain text?
 * 
 * $return string list of successes and failures
 */
function block_tutorlink_processfile($filename,$plaintext=false){
    $cfg_tutorlink = get_config('block/tutorlink');
    $return='';
    if($plaintext){$nl="\n";}else{$nl='<br>';}

    $file = fopen($filename,'r');
    while ($csvrow = fgetcsv($file)){

        //clean idnumbers to prevent sql injection
        $csvrow[0]=clean_param($csvrow[0],PARAM_ALPHANUM);
        $csvrow[1]=clean_param($csvrow[1],PARAM_ALPHANUM);

        if(!$tutor=get_record('user','idnumber',$csvrow[0])){
            $return.= get_string('tutornotfound','block_tutorlink').'('.$csvrow[0].")$nl";
        }elseif(!$student=get_record('user','idnumber',$csvrow[1])){
            $return.= get_string('tuteenotfound','block_tutorlink').'('.$csvrow[1].")$nl";
        }else{
            //both users exist
            $studentcontext = get_context_instance(CONTEXT_USER, $student->id);
            
            if($csvrow[2]=='del'){
                if(delete_records('role_assignments','contextid',$studentcontext->id,'userid',$tutor->id)){
                    $return.= get_string('reldeleted','block_tutorlink').'('.$tutor->firstname.' '.$tutor->lastname.'->'.$student->firstname.' '.$student->lastname.")$nl";
                }
            }else{
                //default to adding if not recognised as del
                if(get_record('role_assignments','contextid',$studentcontext->id,'userid',$tutor->id)){
                    $return.= get_string('relalreadyexists','block_tutorlink').'('.$tutor->firstname.' '.$tutor->lastname.'->'.$student->firstname.' '.$student->lastname.")$nl";
                }elseif(role_assign($cfg_tutorlink->tutorrole,$tutor->id,0,$studentcontext->id)){
                    $return.= get_string('reladded','block_tutorlink').'('.$tutor->firstname.' '.$tutor->lastname.'->'.$student->firstname.' '.$student->lastname.")$nl";
                }else{
                    $return.= get_string('reladderror','block_tutorlink').'('.$tutor->firstname.' '.$tutor->lastname.'->'.$student->firstname.' '.$student->lastname.")$nl";
                }
            }
        }
    }
    return $return;
}