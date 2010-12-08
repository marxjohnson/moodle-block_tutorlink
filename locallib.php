<?php
/**
 * Processes a file and assigns one user in the user context of another
 * 
 * @param $filename string absolute filename pointing to an appropriately formatted csv file
 * @param plaintext boolean should html be excluded in favour of plain text?
 * 
 * $return string list of successes and failures
 */
function block_tutorlink_processfile($filename, $plaintext = false) {
    global $DB;
    $cfg_tutorlink = get_config('block/tutorlink');
    $return = '';
    if ($plaintext) {
        $nl = "\n";
    } else {
        $nl = '<br />';
    }

    $file = fopen($filename,'r');
    $line = 0;
    while ($csvrow = fgetcsv($file)) {
        $line++;
        //clean idnumbers to prevent sql injection
        $op = clean_param($csvrow[0], PARAM_ALPHANUM);
        $tutor_idnum = clean_param($csvrow[1], PARAM_ALPHANUM);
        $student_idnum = clean_param($csvrow[2], PARAM_ALPHANUM);
        $strings = new stdClass;
        $strings->line = $line;
        $strings->op = $op;

        if (!in_array($op, array('add', 'del'))) {
            $return .= get_string('invalidop', 'block_tutorlink', $strings).$nl;
            continue;
        }
        if (!$tutor = $DB->get_record('user', array('idnumber' => $tutor_idnum))) {
            $return .= get_string('tutornotfound', 'block_tutorlink', $strings).$nl;
            continue;
        }
        if (!$student = $DB->get_record('user', array('idnumber' => $student_idnum))) {
            $return .= get_string('tuteenotfound','block_tutorlink', $strings).$nl;
            continue;
        }

        $strings->student = fullname($student);
        $strings->tutor = fullname($tutor);
        //both users exist
        $studentcontext = get_context_instance(CONTEXT_USER, $student->id);

        if ($op == 'del') {
            if ($DB->get_record('role_assignments', array('contextid' => $studentcontext->id, 'userid' => $tutor->id, 'roleid' => $cfg_tutorlink->tutorrole))) {
                role_unassign($cfg_tutorlink->tutorrole, $tutor->id, $studentcontext->id);
                $return .= get_string('reldeleted','block_tutorlink', $strings).$nl;
            } else {
                $return .= get_string('reldoesntexist', 'block_tutorlink', $strings).$nl;
            }
        } else {
            // default to adding if not recognised as del
            if ($DB->get_record('role_assignments', array('contextid' => $studentcontext->id, 'userid' => $tutor->id, 'roleid' => $cfg_tutorlink->tutorrole))) {
                $return.= get_string('relalreadyexists','block_tutorlink', $strings).$nl;
            } else if (role_assign($cfg_tutorlink->tutorrole, $tutor->id, $studentcontext->id)) {
                $return.= get_string('reladded','block_tutorlink', $strings).$nl;
            } else {
                $return.= get_string('reladderror','block_tutorlink', $strings).$nl;
            }
        }
    }
    return $return;
}