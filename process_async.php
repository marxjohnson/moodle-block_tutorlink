<?php
// Slightly embarassing, but lets us verify the user's sesskey, so worth it.
$_COOKIE[$_POST['sname']] = $_POST['sid'];

//get DB credentials from config.php
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/tutorlink/locallib.php');

try {
    $userid = optional_param('userid', 0, PARAM_INT);
    if (!$userid) {
        throw new Exception(get_string('missingparam', '', 'userid'), 400);
    }
    $sesskey = optional_param('sesskey', 0, PARAM_ALPHANUM);
    if (!$sesskey) {
        throw new Exception(get_string('missingparam', '', 'sesskey'), 400);
    }
    if (!in_array('Filedata', $_FILES['Filedata'])) {
        throw new Exception(get_string('nofile'), 400);
    }

    if (!$user = $DB->get_record('user', array('id' => $userid))) {
        throw new Exception(get_string('invaliduserid'), 400);
    }

    if (!confirm_sesskey()) {
        throw new Exception(get_string('invalidsesskey'), 401);
    }
    $context = get_context_instance(CONTEXT_SYSTEM);

    if (!has_capability('block/tutorlink:use', $context)) {
        throw new Exception(get_string('nopermission','block_tutorlink'), 401);
    }

    //make sure that there is a tutorrole configured before we go assigning it
    if (get_config('block/tutorlink','tutorrole') === false) {
        throw new Exception(get_string('notutorrole','block_tutorlink'), 500);
    } else {

        $handler = new block_tutorlink_handler($_FILES['Filedata']['tmp_name']);
        
        try {
            $handler->validate();
        } catch (tutorlink_exception $e) {
            throw new Exception(get_string($e->errorcode, $e->module, $e->a), 415);
        }

        header('Content-Type: application/json');
        echo $handler->process();

    }    
} catch (Exception $e) {
    header('HTTP/1.1 '.$e->getCode());
    die($e->getMessage());
}
?>
