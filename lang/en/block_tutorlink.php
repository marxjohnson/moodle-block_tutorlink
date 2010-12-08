<?php

$string['blocksettings'] = 'Block Settings';
$string['cantremoveold'] = 'Old cron file $a couldn\'t be removed. Please check file permissions.';
$string['csvfile']         = 'Select CSV file';
$string['csv']         = 'CSV File';
$string['csv_help']         = 'The file should be in CSV (comma-seperated value) format. Each assignment should be on 1 row, with 3 columns: operation (add or del), user idnumber, user context idnumber). For example, to add user with idnumber 1234 to the user context of the user with idnumber 4321, the line would read: add, 1234, 4321';
$string['cronfile']         = 'Location of file for automatic processing';
$string['cronfiledesc']         = 'If you enter a file location in here, it will be periodically checked for a file to process automatically.';
$string['cronprocessed']    =   'Processed file location';
$string['cronmoved']    = '{$a->old} moved to {$a->new}';
$string['cronnotmoved']    = '{$a->old} couldn\'t be moved to {$a->new}. Please check folder permissions.';
$string['invalidop']    = 'Line {$a->line}: Invalid operation {$a->op}';
$string['keepprocessed']    = 'Keep Processed files';
$string['keepprocessedlong']    =   'If checked, processed files will be stored in the location below.';
$string['keepprocessedfor']    = 'Days to keep processed files for';
$string['nocronfile']   =   'Cron file doesn\'t exist.';
$string['nodir']    =   '{$a} does not exist or is not writable. Please check folder permissions.';
$string['nopermission']         = 'You do not have permission to upload tutor relationships.';
$string['notutorrole']  =   'Before you use this block, you must select a tutor role in the ';
$string['pluginname']         = 'Upload tutor relationships';
$string['pluginnameplural']         = 'Upload tutor relationships';
$string['reldoesntexist']         = '{$a->tutor} not assigned to {$a->student}, so can\'t be removed';
$string['reladded']         = '{$a->tutor} sucessfully assigned to {$a->student}';
$string['relalreadyexists']         = '{$a->tutor} already assigned to {$a->student}';
$string['reladderror']         = 'Error assigning {$a->tutor} to {$a->student}';
$string['reldeleted']         = '{$a->tutor} unassigned from {$a->student}';
$string['removedold']   = 'Removed {$a} old cron files';
$string['tutorrole']         = 'Tutor role';
$string['tutorrole_explain']         = 'This is the role that the tutors will be assigned in the students\' user context';
$string['tutornotfound']         = 'Line {$a->line}: Tutor not found';
$string['tuteenotfound']         = 'Line {$a->line}: Tutee not found';
$string['tutorlink_log']         = 'Flatfile Tutor Link Log';

?>