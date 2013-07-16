@block @block_tutorlink @javascript
Feature: Cron file processing
    In order to easily assign and remove tutors from students
    As an administrator
    I should be able to upload a file which is processed by the cron to add and remove specified role assignments

    Scenario: Tutor is added to student
        Given the following "users" exists:
            | username     | email                    | firstname | lastname | idnumber |
            | teststudent  | teststudent@example.com  | Test      | Student  | 0001     |
            | testteacher1 | testteacher1@example.com | Test      | Teacher1 | 0002     |
        And I log in as "admin"
        And role "tutor" exists and can be assigned in user contexts
        And tutorlink has the following settings:
            | tutorrole     | tutor |
            | cronfile      | /tmp/tutorlink.csv |
            | keepprocessed | true |
        When the following tutorlink file is processed:
            | 1 | add | 0002 | 0001 |
        Then user "testteacher1" should be the tutor of user "teststudent"

    Scenario: Tutor is added and removed from student
        Given the following "users" exists:
            | username     | email                    | firstname | lastname | idnumber |
            | teststudent  | teststudent@example.com  | Test      | Student  | 0001     |
            | testteacher1 | testteacher1@example.com | Test      | Teacher1 | 0002     |
        And I log in as "admin"
        And role "tutor" exists and can be assigned in user contexts
        And tutorlink has the following settings:
            | tutorrole     | tutor |
            | cronfile      | /tmp/tutorlink.csv |
            | keepprocessed | true |
        When the following tutorlink file is processed:
            | 1 | add | 0002 | 0001 |
        And the following tutorlink file is processed:
            | 1 | del | 0002 | 0001 |
        Then user "testteacher1" should not be the tutor of user "teststudent"

    Scenario: Tutor is added to 2 students
        Given the following "users" exists:
            | username      | email                     | firstname | lastname  | idnumber |
            | teststudent1  | teststudent@example.com   | Test      | Student1  | 0001     |
            | teststudent2  | teststudent1@example.com  | Test      | Student2  | 0002     |
            | teststudent3  | teststudent2@example.com  | Test      | Student3  | 0003     |
            | testteacher1  | testteacher1@example.com  | Test      | Teacher1  | 0004     |
        And I log in as "admin"
        And role "tutor" exists and can be assigned in user contexts
        And tutorlink has the following settings:
            | tutorrole     | tutor |
            | cronfile      | /tmp/tutorlink.csv |
            | keepprocessed | true |
        When the following tutorlink file is processed:
            | linenum | operation | tutor_idnum | student_idnum |
            | 1       | add       | 0004       | 0001 |
            | 2       | add       | 0004       | 0002 |
        Then user "testteacher1" should be the tutor of user "teststudent1"
        And user "testteacher1" should be the tutor of user "teststudent2"
        And user "testteacher1" should not be the tutor of user "teststudent3"

    Scenario: Tutor is added and removed from 2 students
        Given the following "users" exists:
            | username      | email                     | firstname | lastname  | idnumber |
            | teststudent1  | teststudent@example.com   | Test      | Student1  | 0001     |
            | teststudent2  | teststudent1@example.com  | Test      | Student2  | 0002     |
            | teststudent3  | teststudent2@example.com  | Test      | Student3  | 0003     |
            | testteacher1  | testteacher1@example.com  | Test      | Teacher1  | 0004     |
        And I log in as "admin"
        And role "tutor" exists and can be assigned in user contexts
        And tutorlink has the following settings:
            | tutorrole | tutor |
            | cronfile  | /tmp/tutorlink.csv |
            | keepprocessed | true |
        When the following tutorlink file is processed:
            | linenum | operation | tutor_idnum | student_idnum |
            | 1       | add       | 0004       | 0001 |
            | 2       | add       | 0004       | 0002 |
        And the following tutorlink file is processed:
            | linenum | operation | tutor_idnum | student_idnum |
            | 1       | del       | 0004       | 0001 |
            | 2       | del       | 0004       | 0002 |
        Then user "testteacher1" should not be the tutor of user "teststudent1"
        And user "testteacher1" should not be the tutor of user "teststudent2"
        And user "testteacher1" should not be the tutor of user "teststudent3"

    Scenario: Tutor is added to 2 students, removed from 1 but not the other
        Given the following "users" exists:
            | username      | email                     | firstname | lastname  | idnumber |
            | teststudent1  | teststudent@example.com   | Test      | Student1  | 0001     |
            | teststudent2  | teststudent1@example.com  | Test      | Student2  | 0002     |
            | teststudent3  | teststudent2@example.com  | Test      | Student3  | 0003     |
            | testteacher1  | testteacher1@example.com  | Test      | Teacher1  | 0004     |
        And I log in as "admin"
        And role "tutor" exists and can be assigned in user contexts
        And tutorlink has the following settings:
            | tutorrole | tutor |
            | cronfile  | /tmp/tutorlink.csv |
            | keepprocessed | true |
        When the following tutorlink file is processed:
            | linenum | operation | tutor_idnum | student_idnum |
            | 1       | add       | 0004       | 0001 |
            | 2       | add       | 0004       | 0002 |
        And the following tutorlink file is processed:
            | 1       | del       | 0004       | 0001 |
        Then user "testteacher1" should not be the tutor of user "teststudent1"
        And user "testteacher1" should be the tutor of user "teststudent2"
        And user "testteacher1" should not be the tutor of user "teststudent3"

    Scenario: 2 Tutors are added to 2 different students
        Given the following "users" exists:
            | username     | email                     | firstname | lastname  | idnumber |
            | teststudent1 | teststudent@example.com   | Test      | Student1  | 0001     |
            | teststudent2 | teststudent1@example.com  | Test      | Student2  | 0002     |
            | teststudent3 | teststudent2@example.com  | Test      | Student3  | 0003     |
            | testteacher1 | testteacher1@example.com  | Test      | Teacher1  | 0004     |
            | testteacher2 | testteacher2@example.com  | Test      | Teacher2  | 0005     |
        And I log in as "admin"
        And role "tutor" exists and can be assigned in user contexts
        And tutorlink has the following settings:
            | tutorrole | tutor |
            | cronfile  | /tmp/tutorlink.csv |
            | keepprocessed | true |
        When the following tutorlink file is processed:
            | linenum | operation | tutor_idnum | student_idnum |
            | 1       | add       | 0004       | 0001 |
            | 2       | add       | 0005       | 0002 |
        Then user "testteacher1" should be the tutor of user "teststudent1"
        And user "testteacher2" should not be the tutor of user "teststudent1"
        And user "testteacher2" should be the tutor of user "teststudent2"
        And user "testteacher1" should not be the tutor of user "teststudent2"
        And user "testteacher1" should not be the tutor of user "teststudent3"
        And user "testteacher2" should not be the tutor of user "teststudent3"

    Scenario: 2 Tutors are added to the same student
        Given the following "users" exists:
            | username     | email                     | firstname | lastname  | idnumber |
            | teststudent1 | teststudent@example.com   | Test      | Student1  | 0001     |
            | teststudent2 | teststudent1@example.com  | Test      | Student2  | 0002     |
            | testteacher1 | testteacher1@example.com  | Test      | Teacher1  | 0004     |
            | testteacher2 | testteacher2@example.com  | Test      | Teacher2  | 0005     |
        And I log in as "admin"
        And role "tutor" exists and can be assigned in user contexts
        And tutorlink has the following settings:
            | tutorrole | tutor |
            | cronfile  | /tmp/tutorlink.csv |
            | keepprocessed | true |
        When the following tutorlink file is processed:
            | linenum | operation | tutor_idnum | student_idnum |
            | 1       | add       | 0004       | 0001 |
            | 2       | add       | 0005       | 0001 |
        Then user "testteacher1" should be the tutor of user "teststudent1"
        And user "testteacher2" should be the tutor of user "teststudent1"
        And user "testteacher1" should not be the tutor of user "teststudent2"
        And user "testteacher2" should not be the tutor of user "teststudent2"

    Scenario: 2 Tutors are added to the same student, 1 is removed but not the other
        Given the following "users" exists:
            | username     | email                     | firstname | lastname  | idnumber |
            | teststudent1 | teststudent@example.com   | Test      | Student1  | 0001     |
            | teststudent2 | teststudent1@example.com  | Test      | Student2  | 0002     |
            | testteacher1 | testteacher1@example.com  | Test      | Teacher1  | 0004     |
            | testteacher2 | testteacher2@example.com  | Test      | Teacher2  | 0005     |
        And I log in as "admin"
        And role "tutor" exists and can be assigned in user contexts
        And tutorlink has the following settings:
            | tutorrole | tutor |
            | cronfile  | /tmp/tutorlink.csv |
            | keepprocessed | true |
        When the following tutorlink file is processed:
            | linenum | operation | tutor_idnum | student_idnum |
            | 1       | add       | 0004       | 0001 |
            | 2       | add       | 0005       | 0001 |
        And the following tutorlink file is processed:
            | 1       | del       | 0004       | 0001 |
        Then user "testteacher1" should not be the tutor of user "teststudent1"
        And user "testteacher2" should be the tutor of user "teststudent1"
        And user "testteacher1" should not be the tutor of user "teststudent2"
        And user "testteacher2" should not be the tutor of user "teststudent2"

    Scenario: A tutor has 5 students and is removed from all of them at once, but wildcards are disabled
        Given the following "users" exists:
            | username     | email                     | firstname | lastname  | idnumber |
            | teststudent1 | teststudent1@example.com   | Test      | Student1  | 0001     |
            | teststudent2 | teststudent2@example.com  | Test      | Student2  | 0002     |
            | teststudent3 | teststudent3@example.com  | Test      | Student3  | 0003     |
            | teststudent4 | teststudent4@example.com  | Test      | Student4  | 0004     |
            | teststudent5 | teststudent5@example.com  | Test      | Student5  | 0005     |
            | teststudent6 | teststudent6@example.com  | Test      | Student6  | 0006     |
            | testteacher1 | testteacher1@example.com  | Test      | Teacher1  | 0007     |
            | testteacher2 | testteacher2@example.com  | Test      | Teacher2  | 0008     |
        And I log in as "admin"
        And role "tutor" exists and can be assigned in user contexts
        And tutorlink has the following settings:
            | tutorrole | tutor |
            | cronfile  | /tmp/tutorlink.csv |
            | keepprocessed | true |
        When the following tutorlink file is processed:
            | linenum | operation | tutor_idnum | student_idnum |
            | 1       | add       | 0007       | 0001 |
            | 2       | add       | 0007       | 0002 |
            | 3       | add       | 0007       | 0003 | 
            | 4       | add       | 0007       | 0004 | 
            | 5       | add       | 0007       | 0005 |
            | 6       | add       | 0008       | 0001 |
        And the following tutorlink file is processed:
            | 1       | del       | 0007       | * |
        Then user "testteacher1" should be the tutor of user "teststudent1"
        And user "testteacher1" should be the tutor of user "teststudent2"
        And user "testteacher1" should be the tutor of user "teststudent3"
        And user "testteacher1" should be the tutor of user "teststudent4"
        And user "testteacher1" should be the tutor of user "teststudent5"
        And user "testteacher2" should be the tutor of user "teststudent1"

    Scenario: A tutor has 5 students and is removed from all of them at once, and wildcards are enabled
        Given the following "users" exists:
            | username     | email                     | firstname | lastname  | idnumber |
            | teststudent1 | teststudent1@example.com  | Test      | Student1  | 0001     |
            | teststudent2 | teststudent2@example.com  | Test      | Student2  | 0002     |
            | teststudent3 | teststudent3@example.com  | Test      | Student3  | 0003     |
            | teststudent4 | teststudent4@example.com  | Test      | Student4  | 0004     |
            | teststudent5 | teststudent5@example.com  | Test      | Student5  | 0005     |
            | teststudent6 | teststudent6@example.com  | Test      | Student6  | 0006     |
            | testteacher1 | testteacher1@example.com  | Test      | Teacher1  | 0007     |
            | testteacher2 | testteacher2@example.com  | Test      | Teacher2  | 0008     |
        And I log in as "admin"
        And role "tutor" exists and can be assigned in user contexts
        And tutorlink has the following settings:
            | tutorrole | tutor |
            | cronfile  | /tmp/tutorlink.csv |
            | keepprocessed | true |
            | wildcarddeletion | true |
        When the following tutorlink file is processed:
            | linenum | operation | tutor_idnum | student_idnum |
            | 1       | add       | 0007       | 0001 |
            | 2       | add       | 0007       | 0002 |
            | 3       | add       | 0007       | 0003 | 
            | 4       | add       | 0007       | 0004 | 
            | 5       | add       | 0007       | 0005 |
            | 6       | add       | 0008       | 0001 |
        And the following tutorlink file is processed:
            | 1       | del       | 0007       | * |
        Then user "testteacher1" should not be the tutor of user "teststudent1"
        And user "testteacher1" should not be the tutor of user "teststudent2"
        And user "testteacher1" should not be the tutor of user "teststudent3"
        And user "testteacher1" should not be the tutor of user "teststudent4"
        And user "testteacher1" should not be the tutor of user "teststudent5"
        And user "testteacher2" should be the tutor of user "teststudent1"

    Scenario: A student has 5 tutors and has them all removed at once
        Given the following "users" exists:
            | username     | email                     | firstname | lastname  | idnumber |
            | teststudent1 | teststudent1@example.com  | Test      | Student1  | 0001     |
            | teststudent2 | teststudent2@example.com  | Test      | Student2  | 0002     |
            | testteacher1 | testteacher1@example.com  | Test      | Teacher1  | 0003     |
            | testteacher2 | testteacher2@example.com  | Test      | Teacher2  | 0004     |
            | testteacher3 | testteacher3@example.com  | Test      | Teacher3  | 0005     |
            | testteacher4 | testteacher4@example.com  | Test      | Teacher4  | 0006     |
            | testteacher5 | testteacher5@example.com  | Test      | Teacher5  | 0007     |
        And I log in as "admin"
        And role "tutor" exists and can be assigned in user contexts
        And tutorlink has the following settings:
            | tutorrole | tutor |
            | cronfile  | /tmp/tutorlink.csv |
            | keepprocessed | true |
        When the following tutorlink file is processed:
            | linenum | operation | tutor_idnum | student_idnum |
            | 1       | add       | 0003       | 0001 |
            | 2       | add       | 0004       | 0001 |
            | 3       | add       | 0005       | 0001 | 
            | 4       | add       | 0006       | 0001 | 
            | 5       | add       | 0007       | 0001 |
            | 6       | add       | 0003       | 0002 |
        And the following tutorlink file is processed:
            | 1       | del       | *          | 0001 |
        Then user "testteacher1" should be the tutor of user "teststudent1"
        And user "testteacher2" should be the tutor of user "teststudent1"
        And user "testteacher3" should be the tutor of user "teststudent1"
        And user "testteacher4" should be the tutor of user "teststudent1"
        And user "testteacher5" should be the tutor of user "teststudent1"
        And user "testteacher1" should be the tutor of user "teststudent2"

    Scenario: A student has 5 tutors and has them all removed at once, and wildcards are enabled
        Given the following "users" exists:
            | username     | email                     | firstname | lastname  | idnumber |
            | teststudent1 | teststudent1@example.com  | Test      | Student1  | 0001     |
            | teststudent2 | teststudent2@example.com  | Test      | Student2  | 0002     |
            | testteacher1 | testteacher1@example.com  | Test      | Teacher1  | 0003     |
            | testteacher2 | testteacher2@example.com  | Test      | Teacher2  | 0004     |
            | testteacher3 | testteacher3@example.com  | Test      | Teacher3  | 0005     |
            | testteacher4 | testteacher4@example.com  | Test      | Teacher4  | 0006     |
            | testteacher5 | testteacher5@example.com  | Test      | Teacher5  | 0007     |
        And I log in as "admin"
        And role "tutor" exists and can be assigned in user contexts
        And tutorlink has the following settings:
            | tutorrole        | tutor              |
            | cronfile         | /tmp/tutorlink.csv |
            | keepprocessed | true |
            | wildcarddeletion | true               |
        When the following tutorlink file is processed:
            | linenum | operation | tutor_idnum | student_idnum |
            | 1       | add       | 0003       | 0001 |
            | 2       | add       | 0004       | 0001 |
            | 3       | add       | 0005       | 0001 | 
            | 4       | add       | 0006       | 0001 | 
            | 5       | add       | 0007       | 0001 |
            | 6       | add       | 0003       | 0002 |
        And the following tutorlink file is processed:
            | 1       | del       | *          | 0001 |
        Then user "testteacher1" should not be the tutor of user "teststudent1"
        And user "testteacher2" should not be the tutor of user "teststudent1"
        And user "testteacher3" should not be the tutor of user "teststudent1"
        And user "testteacher4" should not be the tutor of user "teststudent1"
        And user "testteacher5" should not be the tutor of user "teststudent1"
        And user "testteacher1" should be the tutor of user "teststudent2"
