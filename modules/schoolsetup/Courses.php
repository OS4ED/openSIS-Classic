<?php

#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include student demographic info, scheduling, grade book, attendance, 
#  report cards, eligibility, transcripts, parent portal, 
#  student portal and more.   
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send 
#  an email to info@os4ed.com.
#
#  This program is released under the terms of the GNU General Public License as  
#  published by the Free Software Foundation, version 2 of the License. 
#  See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#***************************************************************************************

include('lang/language.php');
include('../../RedirectModulesInc.php');

if (isset($_SESSION['language']) && $_SESSION['language'] == 'fr') {
    define("_classRoom", "Salle de cours");
    define("_period", "Période");
    define("_days", "Journées");
    define("_takesAttendance", "La participation prend");
    define("_room", "Chambre");
    define("_time", "Temps");
} elseif (isset($_SESSION['language']) && $_SESSION['language'] == 'es') {
    define("_classRoom", "Salón de clases");
    define("_period", "Período");
    define("_days", "Dias");
    define("_takesAttendance", "toma de Asistencia");
    define("_room", "Habitación");
    define("_time", "Hora");
} else {
    define("_classRoom", "Class Room");
    define("_period", "Period");
    define("_days", "Days");
    define("_takesAttendance", "Takes Attendance");
    define("_room", "Room");
    define("_time", "Time");
}


unset($_SESSION['_REQUEST_vars']['subject_id']);
unset($_SESSION['_REQUEST_vars']['course_id']);
unset($_SESSION['_REQUEST_vars']['course_period_id']);
foreach ($_REQUEST['tables']['courses'][$_REQUEST['course_id']] as $in => $dt) {
    $_REQUEST['tables']['courses'][$_REQUEST['course_id']][$in] = str_replace("\'", "'", $dt);

    //    $_REQUEST['tables']['courses'][$_REQUEST['course_id']][$in] = str_replace("'", "\'", $dt);
}
foreach ($_REQUEST['tables']['course_periods'][$_REQUEST['cp_id']] as $in => $dt) {
    $_REQUEST['tables']['course_periods'][$_REQUEST['cp_id']][$in] = str_replace("\'", "'", $dt);
    //    $_REQUEST['tables']['course_periods'][$_REQUEST['cp_id']][$in] = str_replace("'", "\'", $dt);
}
if (isset($_SESSION['seat_error'])) {
    echo $_SESSION['seat_error'];
    unset($_SESSION['seat_error']);
}
if ($_REQUEST['error'] == 'Blocked_assoc') {

    echo "<font color=red><b>" . _cannotModifyThisCoursePeriodAsItHasAssociation . "</b></font>";
    unset($_REQUEST['error']);
    $msgFlag = 1;
}
if ($_REQUEST['error'] == 'Blocked_period_room') {
    echo "<font color=red><b>" . _cannotModifyPeriodOrRoomAsThisCoursePeriodHasAssociation . "</b></font>";
    unset($_REQUEST['error']);
}
if ($_REQUEST['course_period_id'] == 'new') {
    if ($_REQUEST['schedule_type'] == 'fixed' && $_REQUEST['tables']['course_periods']['new']['TOTAL_SEATS'] != 0 && $_REQUEST['tables']['course_period_var']['new']['ROOM_ID'] != '') {
        $get_seat = DBGet(DBQuery('SELECT CAPACITY FROM rooms WHERE ROOM_ID=' . $_REQUEST['tables']['course_period_var']['new']['ROOM_ID']));
        if ($get_seat[1]['CAPACITY'] < $_REQUEST['tables']['course_periods']['new']['TOTAL_SEATS']) {
            $_REQUEST['tables']['course_periods']['new']['TOTAL_SEATS'] = $get_seat[1]['CAPACITY'];
            echo '<font color=red>' . _totalSeatsCannotBeMoreThanRoomCapacityHenceRoomCapacityTakenAsTotalSeat . '</font>';
        }
    }
    if ($_REQUEST['tables']['course_periods']['new']['SCHEDULE_TYPE'] == 'VARIABLE' && $_REQUEST['tables']['course_periods']['new']['TOTAL_SEATS'] != 0 && $_REQUEST['course_period_variable']['new']['ROOM_ID'] != '') {
        $get_seat = DBGet(DBQuery('SELECT CAPACITY FROM rooms WHERE ROOM_ID=' . $_REQUEST['course_period_variable']['new']['ROOM_ID']));
        if ($get_seat[1]['CAPACITY'] < $_REQUEST['tables']['course_periods']['new']['TOTAL_SEATS']) {
            $_REQUEST['tables']['course_periods']['new']['TOTAL_SEATS'] = $get_seat[1]['CAPACITY'];
            echo '<font color=red>' . _totalSeatsCannotBeMoreThanRoomCapacityHenceRoomCapacityTakenAsTotalSeat . '</font>';
        }
    }
}
if ($_REQUEST['course_period_id'] != 'new') {
    if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['SCHEDULE_TYPE'] == 'FIXED') {
        if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'] != '')
            $total_seats = $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'];
        else {
            $total_seats = DBGet(DBQuery('SELECT TOTAL_SEATS FROM course_periods WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id']));
            $total_seats = $total_seats[1]['TOTAL_SEATS'];
        }
        if ($_REQUEST['tables']['course_period_var'][$_REQUEST['course_period_id']]['ROOM_ID'] != '')
            $room_id = $_REQUEST['tables']['course_period_var'][$_REQUEST['course_period_id']]['ROOM_ID'];
        else {
            $room_id = DBGet(DBQuery('SELECT ROOM_ID FROM course_period_var WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id']));
            $room_id = $room_id[1]['ROOM_ID'];
        }
        $get_seat = DBGet(DBQuery('SELECT CAPACITY FROM rooms WHERE ROOM_ID=' . $room_id));

        if ($get_seat[1]['CAPACITY'] < $total_seats) {
            $check_asociation = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM schedule WHERE course_period_id=' . $_REQUEST['course_period_id'] . ' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE=\'\' OR END_DATE<\'' . date('Y-m-d') . '\') '));
            if ($check_asociation[1]['REC_EX'] > 0) {
                $old_room = DBGet(DBQuery('SELECT cpv.ROOM_ID,cp.TOTAL_SEATS FROM course_period_var cpv,course_periods cp WHERE cp.COURSE_PERIOD_ID=' . $_REQUEST['course_period_id'] . ' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID'));
                $_REQUEST['tables']['course_period_var'][$_REQUEST['course_period_id']]['ROOM_ID'] = $old_room[1]['ROOM_ID'];
                $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'] = $old_room[1]['TOTAL_SEATS'];
            } else {
                $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'] = $get_seat[1]['CAPACITY'];
            }

            echo '<font color=red>' . _totalSeatsCannotBeGreaterThanRoomCapacityChangeTheCapacityInSchoolSetup . '-> ' . _totalSeatsCannotBeGreaterThanRoomCapacityChangeTheCapacityInSchoolSetup . '</font>';
        }
    }


    if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['SCHEDULE_TYPE'] == 'VARIABLE') {

        $room_ids_check = array();
        foreach ($_REQUEST['course_period_variable'][$_REQUEST['course_period_id']] as $tmp_ai => $tmp_ad) {

            if ($tmp_ad['ROOM_ID'] != '')
                $room_ids_check[$tmp_ai] = $tmp_ad['ROOM_ID'];
            else {
                if ($tmp_ai != 'n') {
                    $room_ids = DBGet(DBQuery('SELECT ROOM_ID FROM course_period_var WHERE ID=' . $tmp_ai));
                    $room_ids_check[$tmp_ai] = $room_ids[1]['ROOM_ID'];
                }
            }
        }

        $room_ids_check = implode(',', $room_ids_check);
        $get_seat = DBGet(DBQuery('SELECT MIN(CAPACITY) as CAPACITY FROM rooms WHERE ROOM_ID IN (' . $room_ids_check . ')'));

        if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'] != '')
            $total_seats = $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'];
        else {
            $total_seats = DBGet(DBQuery('SELECT TOTAL_SEATS FROM course_periods WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id']));
            $total_seats = $total_seats[1]['TOTAL_SEATS'];
        }
        if ($get_seat[1]['CAPACITY'] < $total_seats) {

            $check_asociation = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM schedule WHERE course_period_id=' . $_REQUEST['course_period_id'] . ' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE=\'\' OR END_DATE<\'' . date('Y-m-d') . '\') '));
            if ($check_asociation[1]['REC_EX'] > 0) {
                $old_seat = DBGet(DBQuery('SELECT TOTAL_SEATS FROM course_periods WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id']));
                $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'] = $old_seat[1]['TOTAL_SEATS'];
                foreach ($_REQUEST['course_period_variable'][$_REQUEST['course_period_id']] as $tmp_ai => $tmp_ad) {
                    if ($tmp_ai != 'n') {
                        $old_room = DBGet(DBQuery('SELECT cpv.ROOM_ID,cp.TOTAL_SEATS FROM course_period_var cpv,course_periods cp WHERE cpv.ID=' . $tmp_ai . ' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID'));
                        $_REQUEST['course_period_variable'][$_REQUEST['course_period_id']][$tmp_ai]['ROOM_ID'] = $old_room[1]['ROOM_ID'];
                    }
                }
            } else {
                $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'] = $get_seat[1]['CAPACITY'];
            }

            echo '<font color=red>' . _totalSeatsCannotBeMoreThanRoomCapcity . '</font>';
        }
    }


    if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['SCHEDULE_TYPE'] == 'BLOCKED' && $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'] != '') {

        $total_seats = $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'];

        $room_ids_check = array();
        $room_ids = DBGet(DBQuery('SELECT ROOM_ID FROM course_period_var WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id']));

        foreach ($room_ids as $room_ids_data) {
            $room_ids_check[$room_ids_data['ROOM_ID']] = $room_ids_data['ROOM_ID'];
        }
        if (count($room_ids_check) > 0) {
            $room_ids_check = implode(",", $room_ids_check);
            $get_seat = DBGet(DBQuery('SELECT MIN(CAPACITY) as CAPACITY FROM rooms WHERE ROOM_ID IN (' . $room_ids_check . ')'));

            if ($get_seat[1]['CAPACITY'] < $total_seats) {
                $check_asociation = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM schedule WHERE course_period_id=' . $_REQUEST['course_period_id'] . ' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE=\'\' OR END_DATE<\'' . date('Y-m-d') . '\') '));
                if ($check_asociation[1]['REC_EX'] > 0) {
                    $old_room = DBGet(DBQuery('SELECT cpv.ROOM_ID,cp.TOTAL_SEATS FROM course_period_var cpv,course_periods cp WHERE cp.COURSE_PERIOD_ID=' . $_REQUEST['course_period_id'] . ' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID'));
                    $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'] = $old_room[1]['TOTAL_SEATS'];
                } else {
                    $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'] = $get_seat[1]['CAPACITY'];
                }

                echo '<font color=red>' . _totalSeatsCannotBeMoreThanRoomCapcity . '</font>';
            }
        }
    }

    if ($_REQUEST['modfunc'] == 'detail' && $_REQUEST['values']['ROOM_ID'] != '') {

        $total_seats = DBGet(DBQuery('SELECT TOTAL_SEATS FROM course_periods WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id']));
        $total_seats = $total_seats[1]['TOTAL_SEATS'];
        $room_id = $_REQUEST['values']['ROOM_ID'];

        $get_seat = DBGet(DBQuery('SELECT CAPACITY FROM rooms WHERE ROOM_ID=' . $room_id));
        if ($get_seat[1]['CAPACITY'] < $total_seats) {
            $check_asociation = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM schedule WHERE course_period_id=' . $_REQUEST['course_period_id'] . ' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE=\'\' OR END_DATE<\'' . date('Y-m-d') . '\') '));
            if ($check_asociation[1]['REC_EX'] > 0) {
                if ($_REQUEST['mode'] == 'add') {
                    $_SESSION['seat_error'] = '<font color=red>' . _totalSeatsCannotBeMoreThanRoomCapcity . '</font>';
                    echo '<SCRIPT language=javascript>opener.document.location = "Modules.php?modname=' . $_REQUEST['modname'] . '&subject_id=' . $_REQUEST['subject_id'] . '&course_id=' . $_REQUEST['course_id'] . '&course_period_id=' . $_REQUEST['course_period_id'] . '&month=' . date(strtotime($_REQUEST['meet_date'])) . '"; window.close();</script>';
                }
                if ($_REQUEST['mode'] == 'edit') {
                    $old_room = DBGet(DBQuery('SELECT ROOM_ID FROM course_period_var WHERE ID=' . $_REQUEST['cpv_id']));
                    $_REQUEST['values']['ROOM_ID'] = $old_room[1]['ROOM_ID'];
                    $_SESSION['seat_error'] = '<font color=red>Total seats cannot be more than room capcity.</font>';
                    echo '<SCRIPT language=javascript>opener.document.location = "Modules.php?modname=' . $_REQUEST['modname'] . '&subject_id=' . $_REQUEST['subject_id'] . '&course_id=' . $_REQUEST['course_id'] . '&course_period_id=' . $_REQUEST['course_period_id'] . '&month=' . date(strtotime($_REQUEST['meet_date'])) . '"; window.close();</script>';
                }
            } else {
                DBQuery('UPDATE course_periods SET TOTAL_SEATS=' . $get_seat[1]['CAPACITY'] . ' WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id']);
                $_SESSION['seat_error'] = '<font color=red>' . _totalSeatsCannotBeMoreThanRoomCapcity . '</font>';
                echo '<SCRIPT language=javascript>opener.document.location = "Modules.php?modname=' . $_REQUEST['modname'] . '&subject_id=' . $_REQUEST['subject_id'] . '&course_id=' . $_REQUEST['course_id'] . '&course_period_id=' . $_REQUEST['course_period_id'] . '&month=' . date(strtotime($_REQUEST['meet_date'])) . '"; window.close();</script>';
            }
        }
    }
}
$not_pass_update = false;
if ($_REQUEST['course_period_id'] != 'new') {
    if ($_REQUEST['w_course_period_id'] && $_REQUEST['error'] != 'Blocked_assoc') {

        $chek_assoc = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM schedule WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id'] . ' AND (START_DATE<=\'' . date('Y-m-d') . '\' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE>=\'' . date('Y-m-d') . '\' ))'));

        if ($chek_assoc[1]['REC_EX'] == 0) {
            $sql_parent = "UPDATE course_periods SET PARENT_ID=" . $_REQUEST['w_course_period_id'] . " WHERE COURSE_PERIOD_ID=" . $_REQUEST['course_period_id'];
            DBQuery($sql_parent);
        }
    }

    if (isset($_POST['parent_cp']) && $_REQUEST['error'] != 'Blocked_assoc') {
        $chek_assoc = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM schedule WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id'] . ' AND (START_DATE<=\'' . date('Y-m-d') . '\' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE>=\'' . date('Y-m-d') . '\' ))'));
        if ($chek_assoc[1]['REC_EX'] == 0) {

            $sql_parent = "UPDATE course_periods SET PARENT_ID=" . $_REQUEST['course_period_id'] . " WHERE COURSE_PERIOD_ID=" . $_REQUEST['course_period_id'];
            DBQuery($sql_parent);
        } else {
            if ($msgFlag == '') {
                echo "<font color=red><b>" . _cannotModifyThisCoursePeriodAsItHasAssociation . "</b></font>";
                $msgFlag = 1;
            }
        }
    }
}


if ($_REQUEST['action'] == 'delete') {
    if (scheduleAssociation($_REQUEST['course_period_id'])) {
        $scheduleAssociation = true;
    }
    if (gradeAssociation($_REQUEST['course_period_id'])) {
        $gradeAssociation = true;
    }
    if (!$scheduleAssociation && !$gradeAssociation) {
        if (DeletePromptCommon('course period')) {
            $checking_days1 = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL FROM course_period_var WHERE course_period_id=' . $_REQUEST['course_period_id']));
            if ($checking_days1[1]['TOTAL'] > 1) {
                DBQuery("DELETE FROM course_period_var WHERE id=$_REQUEST[cpv_id]");
                $data_sql = "SELECT period_id,days FROM course_period_var WHERE course_period_id=$_REQUEST[course_period_id]";
                $data_RET = DBGet(DBQuery($data_sql));
                foreach ($data_RET as $count => $data) {
                    if ($data['PERIOD_ID'] != '') {
                        $period = '';
                        $qry = "SELECT short_name FROM school_periods WHERE period_id=$data[PERIOD_ID]";
                        $period = DBGet(DBQuery($qry));
                        $period = $period[1];
                        $p .= $period['SHORT_NAME'];
                    }
                    if ($data['DAYS'] != '')
                        $d .= $data['DAYS'];
                }
                $cp_data_sql = "SELECT mp,short_name,marking_period_id,teacher_id FROM course_periods WHERE course_period_id=$_REQUEST[course_period_id]";
                $cp_data_RET = DBGet(DBQuery($cp_data_sql));

                $cp_data_RET = $cp_data_RET[1];
                if ($cp_data_RET['MP'] != 'FY' && $_REQUEST['date_range'] == 'mp') {
                    if ($cp_data_RET['MP'] == 'SEM')
                        $table = ' school_semesters';
                    if ($cp_data_RET['MP'] == 'QTR')
                        $table = '  school_quarters';

                    $mp_sql = "SELECT short_name FROM $table WHERE marking_period_id=$cp_data_RET[MARKING_PERIOD_ID]";
                    $mp = DBGet(DBQuery($mp_sql));
                    $mp = ' - ' . $mp[1]['SHORT_NAME'];
                } else
                    $mp = 'Custom';

                $teacher_sql = "SELECT first_name,last_name,middle_name FROM staff WHERE staff_id=$cp_data_RET[TEACHER_ID]";
                $teacher_RET = DBGet(DBQuery($teacher_sql));
                $teacher_RET = $teacher_RET[1];
                $teacher .= $teacher_RET['FIRST_NAME'];
                if ($teacher_RET['MIDDLE_NAME'] != '')
                    $teacher .= ' ' . $teacher_RET['MIDDLE_NAME'];
                $teacher .= ' ' . $teacher_RET['LAST_NAME'];

                $title_full = $p . $mp . ' - ' . $d . ' - ' . $cp_data_RET['SHORT_NAME'] . ' - ' . $teacher;
                $update_title_sql = "UPDATE course_periods SET title='" . str_replace("'", "''", trim($title_full)) . "' WHERE course_period_id=$_REQUEST[course_period_id]";
                DBQuery($update_title_sql);
            } else {
                echo '<font color=red>' . _unableToDeleteDataCoursePeriodShouldHaveAtleastOnePeriod . '</font>';
            }
            unset($_REQUEST['action']);
        }
    } else {
        echo '<font color=red>' . _unableToDeleteCoursePeriodBecauseItHasAssociation . '</font>';
    }
}
if (isset($_SESSION['conflict'])) {
    echo '<font color=red>' . $_SESSION['conflict'] . '</font>';
    $_REQUEST['tables'] = $_SESSION['tables'];
    $_REQUEST['conflict'] = 'y';
    unset($_SESSION['conflict']);
    unset($_SESSION['tables']);
}
if ($_REQUEST['modfunc'] != 'delete' && !$_REQUEST['subject_id']) {
    $subjects_RET = DBGet(DBQuery("SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "'"));
    if (count($subjects_RET) == 1)
        $_REQUEST['subject_id'] = $subjects_RET[1]['SUBJECT_ID'];
}

if (clean_param($_REQUEST['course_modfunc'], PARAM_ALPHAMOD) == 'search') {
    PopTable('header',  _search);
    echo "<FORM name=F1 id=F1 action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&course_modfunc=search method=POST>";
    echo '<TABLE><TR><TD><INPUT type=text class=form-control name=search_term value="' . $_REQUEST['search_term'] . '"></TD><TD><INPUT type=submit class="btn btn-primary m-l-10" value=' . _search . ' onclick=\'formload_ajax("F1");\'></TD></TR></TABLE>';
    echo '</FORM>';
    PopTable('footer');

    if ($_REQUEST['search_term']) {
        $subjects_RET = DBGet(DBQuery("SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE (UPPER(TITLE) LIKE '%" . strtoupper($_REQUEST['search_term']) . "%' OR UPPER(SHORT_NAME) = '" . strtoupper($_REQUEST['search_term']) . "') AND SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "'"));
        $courses_RET = DBGet(DBQuery("SELECT SUBJECT_ID,COURSE_ID,TITLE FROM courses WHERE (UPPER(TITLE) LIKE '%" . strtoupper($_REQUEST['search_term']) . "%' OR UPPER(SHORT_NAME) = '" . strtoupper($_REQUEST['search_term']) . "') AND SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "'"));
        $periods_RET = DBGet(DBQuery("SELECT c.SUBJECT_ID,cp.COURSE_ID,cp.COURSE_PERIOD_ID,cp.TITLE FROM course_periods cp,courses c WHERE cp.COURSE_ID=c.COURSE_ID AND (UPPER(cp.TITLE) LIKE '%" . strtoupper($_REQUEST['search_term']) . "%' OR UPPER(cp.SHORT_NAME) = '" . strtoupper($_REQUEST['search_term']) . "') AND cp.SYEAR='" . UserSyear() . "' AND cp.SCHOOL_ID='" . UserSchool() . "'"));

        echo '<div class="row">';
        echo '<div class="col-md-4">';
        echo '<div class="panel panel-white">';
        $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";
        $link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID');
        ListOutput($subjects_RET, array('TITLE' => '' . _subject . ''), '' . _subject . '', '' . _subjects . '', $link, array(), array('search' => false, 'save' => false));
        echo '</div>'; //.panel-white
        echo '</div>'; //.col-md-4

        echo '<div class="col-md-4">';
        echo '<div class="panel panel-white">';
        $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";
        $link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID', 'course_id' => 'COURSE_ID');
        ListOutput($courses_RET, array('TITLE' => '' . _course . ''), '' . _course . '', '' . _courses . '', $link, array(), array('search' => false, 'save' => false));
        echo '</div>'; //.panel-white
        echo '</div>'; //.col-md-4

        echo '<div class="col-md-4">';
        echo '<div class="panel panel-white">';
        $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";
        $link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID', 'course_id' => 'COURSE_ID', 'course_period_id' => 'COURSE_PERIOD_ID');
        ListOutput($periods_RET, array('TITLE' => '' . _coursePeriod . ''), '' . _coursePeriod . '', '' . _coursePeriods . '', $link, array(), array('search' => false, 'save' => false));
        echo '</div>'; //.panel-white
        echo '</div>'; //.col-md-4
        echo '</div>'; //.row
    }
}

if (clean_param($_REQUEST['course_modfunc'], PARAM_ALPHAMOD) == 'standard_search') {
    PopTable('header', '' . _search . '');
    echo "<FORM name=F1 id=F1 action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&course_modfunc=search method=POST>";
    echo '<TABLE><TR><TD><INPUT type=text class=cell_floating name=search_term value="' . $_REQUEST['search_term'] . '"></TD><TD><INPUT type=submit class=btn_medium value=' . _search . ' onclick=\'formload_ajax("F1");\'></TD></TR></TABLE>';
    echo '</FORM>';
    PopTable('footer');

    if ($_REQUEST['search_term']) {
        $subjects_RET = DBGet(DBQuery("SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE (UPPER(TITLE) LIKE '%" . strtoupper($_REQUEST['search_term']) . "%' OR UPPER(SHORT_NAME) = '" . strtoupper($_REQUEST['search_term']) . "') AND SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "'"));
        $courses_RET = DBGet(DBQuery("SELECT SUBJECT_ID,COURSE_ID,TITLE FROM courses WHERE (UPPER(TITLE) LIKE '%" . strtoupper($_REQUEST['search_term']) . "%' OR UPPER(SHORT_NAME) = '" . strtoupper($_REQUEST['search_term']) . "') AND SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "'"));
        $periods_RET = DBGet(DBQuery("SELECT c.SUBJECT_ID,cp.COURSE_ID,cp.COURSE_PERIOD_ID,cp.TITLE FROM course_periods cp,courses c WHERE cp.COURSE_ID=c.COURSE_ID AND (UPPER(cp.TITLE) LIKE '%" . strtoupper($_REQUEST['search_term']) . "%' OR UPPER(cp.SHORT_NAME) = '" . strtoupper($_REQUEST['search_term']) . "') AND cp.SYEAR='" . UserSyear() . "' AND cp.SCHOOL_ID='" . UserSchool() . "'"));

        echo '<TABLE><TR><TD valign=top>';
        $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";
        $link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID');
        ListOutput($subjects_RET, array('TITLE' => '' . _subject . ''), _subject, _subjects, $link, array(), array('search' => false, 'save' => false));
        echo '</TD><TD valign=top>';
        $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";
        $link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID', 'course_id' => 'COURSE_ID');
        ListOutput($courses_RET, array('TITLE' => '' . _course . ''), '' . _course . '', '' . _courses . '', $link, array(), array('search' => false, 'save' => false));
        echo '</TD><TD valign=top>';
        $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";
        $link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID', 'course_id' => 'COURSE_ID', 'course_period_id' => 'COURSE_PERIOD_ID');
        ListOutput($periods_RET, array('TITLE' => '' . _coursePeriod . ''), '' . _coursePeriod . '', '' . _coursePeriods . '', $link, array(), array('search' => false, 'save' => false));
        echo '</TD></TR></TABLE>';
    }
}
// UPDATING
if (clean_param($_REQUEST['tables'], PARAM_NOTAGS) && ($_POST['tables'] || $_REQUEST['ajax']) && AllowEdit()) {

    $where = array(
        'course_subjects' => 'SUBJECT_ID',
        'courses' => 'COURSE_ID',
        'course_periods' => 'COURSE_PERIOD_ID', 'course_period_var' => 'COURSE_PERIOD_ID'
    );

    if ($_REQUEST['tables']['parent_id'])
        $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['PARENT_ID'] = $_REQUEST['tables']['parent_id'];
    //================================ For Custom Range ========================

    if ($_REQUEST['month_begin'] && $_REQUEST['day_begin'] && $_REQUEST['year_begin']) {
        while (!VerifyDate($begin = $_REQUEST['day_begin'] . '-' . $_REQUEST['month_begin'] . '-' . $_REQUEST['year_begin']))
            $_REQUEST['day_begin']--;
        $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['BEGIN_DATE'] = date('Y-m-d', strtotime($begin));
    }
    if ($_REQUEST['month_end'] && $_REQUEST['day_end'] && $_REQUEST['year_end']) {
        while (!VerifyDate($end = $_REQUEST['day_end'] . '-' . $_REQUEST['month_end'] . '-' . $_REQUEST['year_end']))
            $_REQUEST['day_end']--;
        $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['END_DATE'] = date('Y-m-d', strtotime($end));
    }

    if ($_REQUEST['course_period_variable']) {

        if (scheduleAssociation($_REQUEST['course_period_id'])) {
            $scheduleAssociation = true;
        }
        if (gradeAssociation($_REQUEST['course_period_id'])) {
            $gradeAssociation = true;
        }

        $columns['COURSE_PERIOD_ID'] = $_REQUEST['course_period_id'];
        $values = DBGet(DBQuery("SELECT * FROM course_periods WHERE COURSE_PERIOD_ID='" . $_REQUEST['course_period_id'] . "'"));
        if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'])
            $columns['TEACHER_ID'] = $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'];
        else
            $columns['TEACHER_ID'] = $values[1]['TEACHER_ID'];

        if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['SECONDARY_TEACHER_ID'])
            $columns['SECONDARY_TEACHER_ID'] = $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['SECONDARY_TEACHER_ID'];
        else
            $columns['SECONDARY_TEACHER_ID'] = $values[1]['SECONDARY_TEACHER_ID'];

        if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['BEGIN_DATE'])
            $columns['BEGIN_DATE'] = $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['BEGIN_DATE'];
        else
            $columns['BEGIN_DATE'] = $values[1]['BEGIN_DATE'];

        if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['END_DATE'])
            $columns['END_DATE'] = $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['END_DATE'];
        else
            $columns['END_DATE'] = $values[1]['END_DATE'];
        if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['MARKING_PERIOD_ID'])
            $columns['MARKING_PERIOD_ID'] = $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['MARKING_PERIOD_ID'];
        else
            $columns['MARKING_PERIOD_ID'] = $values[1]['MARKING_PERIOD_ID'];
        $columns['CP_SECTION'] = 'cpv';
        //        print_r($_REQUEST['course_period_variable']);
        foreach ($_REQUEST['course_period_variable'] as $cp_id => $days) {
            if ($cp_id != 'new') {
                foreach ($days as $day => $period) {
                    if ($day != 'n') {
                        $day_exits = DBGet(DBQuery("SELECT DAYS,PERIOD_ID,ROOM_ID FROM course_period_var WHERE id='$day' AND course_period_id=$cp_id"));
                        if (isset($period['DAYS']))
                            $days_str .= "'" . $period['DAYS'] . "',";
                        if ($day_exits) {

                            if (($period['PERIOD_ID'] && $period['PERIOD_ID'] != $day_exits[1]['PERIOD_ID']) || ($period['ROOM_ID'] && $period['ROOM_ID'] != $day_exits[1]['ROOM_ID']) || ($period['DAYS'] && $period['DAYS'] != $day_exits[1]['DAYS']) || ($columns['SECONDARY_TEACHER_ID'] != $values[1]['SECONDARY_TEACHER_ID']) || ($columns['TEACHER_ID'] != $values[1]['TEACHER_ID'])) {

                                $columns['START_TIME'] = $period['START_TIME'];
                                $columns['END_TIME'] = $period['END_TIME'];
                                if ($period['PERIOD_ID'])
                                    $columns['PERIOD_ID'] = $period['PERIOD_ID'];
                                else
                                    $columns['PERIOD_ID'] = $day_exits[1]['PERIOD_ID'];
                                if ($period['ROOM_ID'])
                                    $columns['ROOM_ID'] = $period['ROOM_ID'];
                                else
                                    $columns['ROOM_ID'] = $day_exits[1]['ROOM_ID'];
                                $columns['DAYS'] = $period['DAYS'];
                                $columns['SELECT_DAYS'] = $period['DAYS'];
                                $columns['CP_VAR_ID'] = $day;

                                $columns['ID'] = $period['ID'];
                                $conflict = VerifyVariableSchedule_Update($columns);
                                if ($conflict !== true) {
                                    echo '<font color=red>' . $conflict . '</font>';

                                    $not_pass_update = true;
                                    break 2;
                                }
                            }
                            if (($period['PERIOD_ID'] == '' || $period['ROOM_ID'] == '') && !$period['START_TIME']) {
                                $columns['PERIOD_ID'] = $period['PERIOD_ID'];
                                $columns['ROOM_ID'] = $period['ROOM_ID'];
                                $columns['SELECT_DAYS'] = $period['DAYS'];
                                $conflict = VerifyVariableSchedule_Update($columns);
                                if ($conflict !== true) {
                                    echo '<font color=red>' . $conflict . '</font>';

                                    $not_pass_update = true;
                                    break 2;
                                }
                            }
                            $up_day = false;
                            $check_does_attendance = DBGet(DBQuery("SELECT DOES_ATTENDANCE FROM course_period_var WHERE id=" . $period['ID'] . " AND course_period_id=$cp_id"));
                            if ($check_does_attendance[1]['DOES_ATTENDANCE'] == 'Y') {
                                if (!isset($period['DOES_ATTENDANCE']))
                                    $period['DOES_ATTENDANCE'] = 'N';
                            }
                            $days_upsql = "UPDATE course_period_var SET ";
                            foreach ($period as $column => $value) {

                                if (isset($value)) {
                                    if ($column == 'DOES_ATTENDANCE') {

                                        if ($value == 'Y') {
                                            $days_upsql .= $column . " = '" . $value . "',";
                                            $up_day = true;
                                        } else {
                                            if (!scheduleAssociation($_REQUEST['course_period_id'])) {
                                                $days_upsql .= $column . " = NULL,";
                                                $up_day = true;
                                            }
                                        }
                                    } else {
                                        if (!attendanceAssociation($_REQUEST['course_period_id'])) {

                                            if ($column == 'DAYS') {
                                                if ($value != '')
                                                    $days_upsql .= $column . " = '" . $value . "',";
                                                else
                                                    $day_blank_error = '<font color=red>' . _dayCannotBeBlank . '</font>';

                                                $up_day = true;
                                            } else {

                                                if ($value != '')
                                                    $days_upsql .= $column . " = '" . $value . "',";
                                                else
                                                    $days_upsql .= $column . " = NULL,";
                                                $up_day = true;
                                            }
                                        } else {
                                            $check_cp = DBGet(DBQuery("SELECT * FROM course_period_var WHERE course_period_id='" . $_REQUEST['course_period_id'] . "'"));
                                            $check_cp = $check_cp[1];
                                            if (($period['DAYS'] != '' && $check_cp['DAYS'] != $period['DAYS']) || ($period['ROOM_ID'] != '' && $check_cp['ROOM_ID'] != $period['ROOM_ID']) || ($period['PERIOD_ID'] != '' && $check_cp['PERIOD_ID'] != $period['PERIOD_ID'])) {
                                                if ($msgFlag == '') {
                                                    $assoc_err = "<font color=red><b>" . _cannotModifyThisCoursePeriodAsItHasAssociation . "</b></font>";
                                                    $msgFlag = 1;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $days_upsql = substr($days_upsql, 0, -1);
                            $days_upsql .= " WHERE id=" . $period['ID'] . " AND course_period_id=$cp_id";

                            if ($up_day) {

                                DBQuery($days_upsql);
                                unset($days_upsql);
                            }
                        }
                    } else {
                        if (isset($period['PERIOD_ID']) && !isset($period['START_TIME']) && !isset($period['END_TIME']) && $period['PERIOD_ID'] != '') {

                            $period_data = DBGet(DBQuery('SELECT START_TIME,END_TIME FROM school_periods WHERE PERIOD_ID=' . $period['PERIOD_ID'] . ''));
                            if (!empty($period_data)) {
                                $period['START_TIME'] = $period_data[1]['START_TIME'];
                                $period['END_TIME'] = $period_data[1]['END_TIME'];
                            }
                        }

                        if ($period['START_TIME'] != '' && $period['END_TIME'] != '' && $period['PERIOD_ID'] != '' && $period['ROOM_ID'] != '') {
                            $columns['START_TIME'] = $period['START_TIME'];
                            $columns['END_TIME'] = $period['END_TIME'];
                            $columns['PERIOD_ID'] = $period['PERIOD_ID'];
                            $columns['ROOM_ID'] = $period['ROOM_ID'];
                            $columns['CP_VAR_ID'] = 'n';
                            $columns['DAYS'] = $period['DAYS'];
                            $columns['SELECT_DAYS'] = $period['DAYS'];
                            $conflict = VerifyVariableSchedule_Update($columns);
                            if ($conflict !== true) {
                                echo '<font color=red>' . $conflict . '</font>';

                                $not_pass_update = true;
                                break 2;
                            }
                            if (!scheduleAssociation($_REQUEST['course_period_id'])) {
                                $days_sql = "INSERT INTO course_period_var(course_period_id,days,period_id,start_time,end_time,room_id,does_attendance)VALUES";

                                $days_sql .= "($_REQUEST[course_period_id],'$period[DAYS]',$period[PERIOD_ID],'$period[START_TIME]','$period[END_TIME]',$period[ROOM_ID],'$period[DOES_ATTENDANCE]'),";
                                $days_sql = substr($days_sql, 0, -1);
                                DBQuery($days_sql);
                            }
                        }
                        if ($period['DAYS'] != '' && ($period['START_TIME'] == '' || $period['END_TIME'] == '' || $period['PERIOD_ID'] == '' || $period['ROOM_ID'] == '')) {
                            $msg = '<font color=red>Please enter valid data</font>';
                            unset($_REQUEST['course_period_variable']);
                            echo $msg;
                            unset($msg);
                        }
                        unset($_REQUEST['course_period_variable']);
                    }
                }
            }
        }
        if ($not_pass_update != true && $day_blank_error) {
            echo $day_blank_error;
            unset($day_blank_error);
        }
    }
    //=======================================================================
    if ($_REQUEST['tables']['course_period_var']) {
        foreach ($_REQUEST['tables']['course_period_var'] as $columns_var) {
            if ($columns_var['DAYS']) {
                foreach ($columns_var['DAYS'] as $day => $y) {
                    if ($y == 'Y')
                        $days .= $day;
                }
                $columns_var['DAYS'] = $days;
                unset($days);
            }
        }
    }
    //    echo "<pre>";
    //    print_r($_SESSION);exit;

    if ($_REQUEST['course_id'] == 'new') {
        if ($_REQUEST['tables']['courses']['new']['SUBJECT_ID'] != '') {
            $_REQUEST['subject_id'] = $_REQUEST['tables']['courses']['new']['SUBJECT_ID'];
            unset($_REQUEST['tables']['courses']['new']['SUBJECT_ID']);
        }
        foreach ($_REQUEST['tables']['courses']['new'] as $ci => $cd) {
            $_REQUEST['tables']['courses']['new'][$ci] = str_replace('"', '""', $cd);
        }
    }
    if ($_REQUEST['course_id'] != 'new' && $_REQUEST['course_id'] != '') {
        foreach ($_REQUEST['tables']['courses'][$_REQUEST['course_id']] as $ci => $cd) {
            $_REQUEST['tables']['courses'][$_REQUEST['course_id']][$ci] = str_replace('"', '""', $cd);
            $_REQUEST['tables']['courses'][$_REQUEST['course_id']][$ci] = str_replace("\'", "'", $cd);
        }
    }

    $school_fy_dates = DBGet(DBQuery('select start_date,end_date from school_years where syear = ' . UserSyear() . ' and school_id = ' . UserSchool()));
    $school_fy_bdate = $school_fy_dates[1]['START_DATE'];
    $school_fy_edate = $school_fy_dates[1]['END_DATE'];

    foreach ($_REQUEST['tables'] as $table_name => $tables) {
        $go = false;
        $flag_err = 'N';

        foreach ($tables as $id => $columns) {

            if (!(attendanceAssociation($id))) {
                $schedule_type = DBGet(DBQuery('SELECT * FROM  course_periods where course_period_id=\'' . $id . '\''));
                if ($columns['DOES_ATTENDANCE'] == 'Y')
                    $att = 'Y';
                else
                    $att = NULL;
                if ($schedule_type[1]['SCHEDULE_TYPE'] == 'FIXED') {
                    $check_attn = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=(SELECT PERIOD_ID FROM course_period_var WHERE COURSE_PERIOD_ID=' . $id . ')'));
                    $check_attn = $check_attn[1];

                    if ($check_attn['ATTENDANCE'] == 'Y') {

                        DBQuery('update course_period_var set does_attendance=\'' . $att . '\' where course_period_id=\'' . $id . '\'');
                    }
                }
            }

            //            if ($columns['SHORT_NAME'])
            //                        {
            //                            $short_name = $columns['SHORT_NAME'];
            //                            
            //                            }
            //                                      if (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') != 'school_years')
            //                                $mp_title = GetMP($columns['MARKING_PERIOD_ID'], 'SHORT_NAME') . ' - ';
            //                        } else {
            //                            if (GetMP($current[1]['MARKING_PERIOD_ID'], 'TABLE') == 'school_years')
            //                                $current[1]['MP'] = 'FY';
            //                            elseif (GetMP($current[1]['MARKING_PERIOD_ID'], 'TABLE') == 'school_semesters')
            //                                $current[1]['MP'] = 'SEM';
            //                            else
            //                                $current[1]['MP'] = 'QTR';
            //
            //                            if (GetMP($current[1]['MARKING_PERIOD_ID'], 'TABLE') != 'school_years')
            //                                $mp_title = GetMP($current[1]['MARKING_PERIOD_ID'], 'SHORT_NAME') . ' - ';
            //                        }
            if (isset($columns['BEGIN_DATE']) && $columns['BEGIN_DATE'] != '' && isset($columns['END_DATE']) && $columns['END_DATE'] != '') {
                if (strtotime($columns['BEGIN_DATE']) < strtotime($school_fy_bdate)) {
                    echo '<font color=red><b>' . _beginDateCannotBeLessThanFullYearBeginDate . '</b></font>';
                    $not_pass_update = true;
                    break 2;
                }

                if (strtotime($columns['END_DATE']) > strtotime($school_fy_edate)) {
                    echo '<font color=red><b>' . _endDateCannotBeGreaterThanFullYearEndDate . '</b></font>';
                    $not_pass_update = true;
                    break 2;
                }
            }
            if (strtotime($columns['BEGIN_DATE']) > strtotime($columns['END_DATE'])) {
                echo '<font color=red><b>' . _beginDateCannotBeGreaterThanEndDate . '</b></font>';
                $not_pass_update = true;
                break 2;
            }
            if ($columns['MARKING_PERIOD_ID'] != '') {
                $mark_id_dates = DBGet(DBQuery('SELECT * FROM marking_periods WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND MARKING_PERIOD_ID=' . $columns['MARKING_PERIOD_ID'] . ''));
                $columns['BEGIN_DATE'] = $mark_id_dates[1]['START_DATE'];
                $columns['END_DATE'] = $mark_id_dates[1]['END_DATE'];
                if (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') == 'school_years')
                    $columns['MP'] = 'FY';
                elseif (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') == 'school_semesters')
                    $columns['MP'] = 'SEM';
                elseif (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') == 'school_quarters')
                    $columns['MP'] = 'QTR';
            } else {
                if ($columns['BEGIN_DATE'] && $columns['END_DATE']) {
                    $columns['MARKING_PERIOD_ID'] = '';
                    $columns['MP'] = '';
                }
            }

            if ($columns['TOTAL_SEATS'] && !is_numeric($columns['TOTAL_SEATS']))
                $columns['TOTAL_SEATS'] = par_rep('/[^0-9]+/', '', $columns['TOTAL_SEATS']);
            $days_fix = '';
            if ($columns['DAYS']) {
                foreach ($columns['DAYS'] as $day => $y) {
                    if ($y == 'Y')
                        $days_fix .= $day;
                }
                $columns['DAYS'] = $days_fix;
            }

            if ($id != 'new') {

                unset($scheduleAssociation);
                unset($gradeAssociation);
                $tot_seat = $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'];
                $does_attn = $_REQUEST['tables']['course_period_var'][$_REQUEST['course_period_id']]['DOES_ATTENDANCE'];
                if ($tot_seat != '') {
                    $seat_num = DBGet(DBQuery('SELECT FILLED_SEATS FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_REQUEST['course_period_id'] . '\' LIMIT 0,1 '));
                    if ($seat_num[1]['FILLED_SEATS'] > $tot_seat) {
                        $tot_seat = 'error';
                    }
                }
                $update = true;
                if ($tot_seat != '' && $does_attn != '') {
                    if (scheduleAssociation($id)) {
                        $scheduleAssociation = true;
                    }
                    if (gradeAssociation($id)) {
                        $gradeAssociation = true;
                    }
                }


                if ($table_name == 'courses' && $columns['SUBJECT_ID'] && $columns['SUBJECT_ID'] != $_REQUEST['subject_id'])
                    $_REQUEST['subject_id'] = $columns['SUBJECT_ID'];

                $sql = "UPDATE $table_name SET ";
                if ($table_name == 'course_periods')
                    $sql1 = "UPDATE $table_name SET ";
                $calendar_id = $columns['CALENDAR_ID'];
                if ($table_name == 'course_periods') {

                    $current = DBGet(DBQuery("SELECT TEACHER_ID,MARKING_PERIOD_ID,SHORT_NAME,TOTAL_SEATS,CALENDAR_ID FROM course_periods WHERE " . $where[$table_name] . "='$id'"));
                    if ($scheduleAssociation)
                        $cur_total_seat = $current[1]['TOTAL_SEATS'];

                    if ($columns['TEACHER_ID'])
                        $staff_id = $columns['TEACHER_ID'];
                    else
                        $staff_id = $current[1]['TEACHER_ID'];
                    if ($columns['CALENDAR_ID'])
                        $calendar_id = $columns['CALENDAR_ID'];
                    else
                        $calendar_id = $current[1]['CALENDAR_ID'];
                    if ($columns['PERIOD_ID'])
                        $period_id = $columns['PERIOD_ID'];
                    else
                        $period_id = $current[1]['PERIOD_ID'];
                    if (isset($columns['MARKING_PERIOD_ID']))
                        $marking_period_id = $columns['MARKING_PERIOD_ID'];
                    else
                        $marking_period_id = $current[1]['MARKING_PERIOD_ID'];
                    if ($columns['DAYS'])
                        $days = $columns['DAYS'];
                    else
                        $days = $current[1]['DAYS'];
                    if ($columns['SHORT_NAME'])
                        $short_name = $columns['SHORT_NAME'];
                    else
                        $short_name = $current[1]['SHORT_NAME'];


                    if (isset($columns['MARKING_PERIOD_ID'])) {
                        if (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') == 'school_years')
                            $columns['MP'] = 'FY';
                        elseif (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') == 'school_semesters')
                            $columns['MP'] = 'SEM';
                        else
                            $columns['MP'] = 'QTR';

                        if (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') != 'school_years')
                            $mp_title = GetMP($columns['MARKING_PERIOD_ID'], 'SHORT_NAME') . ' - ';
                    } else {
                        if (GetMP($current[1]['MARKING_PERIOD_ID'], 'TABLE') == 'school_years')
                            $current[1]['MP'] = 'FY';
                        elseif (GetMP($current[1]['MARKING_PERIOD_ID'], 'TABLE') == 'school_semesters')
                            $current[1]['MP'] = 'SEM';
                        else
                            $current[1]['MP'] = 'QTR';

                        if (GetMP($current[1]['MARKING_PERIOD_ID'], 'TABLE') != 'school_years')
                            $mp_title = GetMP($current[1]['MARKING_PERIOD_ID'], 'SHORT_NAME') . ' - ';
                    }

                    $col['CP_SECTION'] = 'cp';
                    if ($columns['SCHEDULE_TYPE'] == 'VARIABLE' && $not_pass_update == false) {
                        $col['COURSE_PERIOD_ID'] = $_REQUEST['course_period_id'];
                        $values = DBGet(DBQuery("SELECT * FROM course_periods WHERE COURSE_PERIOD_ID='" . $_REQUEST['course_period_id'] . "'"));
                        if ($columns['TEACHER_ID'])
                            $col['TEACHER_ID'] = $columns['TEACHER_ID'];
                        else
                            $col['TEACHER_ID'] = $values[1]['TEACHER_ID'];

                        if ($columns['SECONDARY_TEACHER_ID'])
                            $col['SECONDARY_TEACHER_ID'] = $columns['SECONDARY_TEACHER_ID'];
                        else
                            $col['SECONDARY_TEACHER_ID'] = $values[1]['SECONDARY_TEACHER_ID'];

                        if ($columns['BEGIN_DATE'])
                            $col['BEGIN_DATE'] = $columns['BEGIN_DATE'];
                        else
                            $col['BEGIN_DATE'] = $values[1]['BEGIN_DATE'];

                        if ($columns['END_DATE'])
                            $col['END_DATE'] = $columns['END_DATE'];
                        else
                            $col['END_DATE'] = $values[1]['END_DATE'];
                        if ($columns['MARKING_PERIOD_ID'])
                            $col['MARKING_PERIOD_ID'] = $columns['MARKING_PERIOD_ID'];
                        else
                            $col['MARKING_PERIOD_ID'] = $values[1]['MARKING_PERIOD_ID'];



                        foreach ($_REQUEST['course_period_variable'] as $cp_id => $days) {
                            foreach ($days as $day => $period) {


                                $day_exits = DBGet(DBQuery("SELECT DAYS,PERIOD_ID,ROOM_ID FROM course_period_var WHERE days='$day' AND course_period_id=$cp_id"));
                                $col['START_TIME'] = $period['START_TIME'];
                                $col['END_TIME'] = $period['END_TIME'];
                                if ($period['PERIOD_ID'])
                                    $col['PERIOD_ID'] = $period['PERIOD_ID'];
                                else
                                    $col['PERIOD_ID'] = $day_exits[1]['PERIOD_ID'];
                                if ($period['ROOM_ID'])
                                    $col['ROOM_ID'] = $period['ROOM_ID'];
                                else
                                    $col['ROOM_ID'] = $day_exits[1]['ROOM_ID'];
                                $col['DAYS'] = $day;
                                $col['SELECT_DAYS'] = $period['DAYS'];


                                if ($period['DAYS'] != '' && $period['PERIOD_ID'] != '' && $period['ROOM_ID'] != '') {

                                    $conflict = VerifyVariableSchedule_Update($col);
                                    if ($conflict !== true) {
                                        echo '<font color=red>' . $conflict . '</font>';
                                        $not_pass_update = true;
                                        break 4;
                                    }
                                }
                            }
                        }
                    }
                    if ($columns['SCHEDULE_TYPE'] == 'VARIABLE') {
                        $title_val = DBGet(DBQuery("SELECT DAYS,PERIOD_ID FROM course_period_var WHERE course_period_id='" . $_REQUEST['course_period_id'] . "'"));
                        foreach ($title_val as $key => $title_value) {

                            $days_n[] = $title_value['DAYS'];
                            $pd_id = DBGet(DBQuery("SELECT SHORT_NAME FROM school_periods WHERE PERIOD_ID='" . $title_value['PERIOD_ID'] . "'"));

                            $period_value_n[] = $pd_id[1]['SHORT_NAME'];
                        }
                        for ($i = count($period_value_n); $i >= 0; $i--) {
                            $period_value .= $period_value_n[$i];
                            $days .= $days_n[$i];
                        }
                    }
                    if ($columns['SCHEDULE_TYPE'] == 'FIXED') {
                        $col['COURSE_PERIOD_ID'] = $_REQUEST['course_period_id'];
                        $values = DBGet(DBQuery("SELECT * FROM course_periods WHERE COURSE_PERIOD_ID='" . $_REQUEST['course_period_id'] . "'"));
                        if ($columns['TEACHER_ID'])
                            $col['TEACHER_ID'] = $columns['TEACHER_ID'];
                        else
                            $col['TEACHER_ID'] = $values[1]['TEACHER_ID'];

                        if ($columns['SECONDARY_TEACHER_ID'])
                            $col['SECONDARY_TEACHER_ID'] = $columns['SECONDARY_TEACHER_ID'];
                        else
                            $col['SECONDARY_TEACHER_ID'] = $values[1]['SECONDARY_TEACHER_ID'];

                        if ($columns['BEGIN_DATE'])
                            $col['BEGIN_DATE'] = $columns['BEGIN_DATE'];
                        else
                            $col['BEGIN_DATE'] = $values[1]['BEGIN_DATE'];

                        if ($columns['END_DATE'])
                            $col['END_DATE'] = $columns['END_DATE'];
                        else
                            $col['END_DATE'] = $values[1]['END_DATE'];
                        if ($columns['MARKING_PERIOD_ID'])
                            $col['MARKING_PERIOD_ID'] = $columns['MARKING_PERIOD_ID'];
                        else
                            $col['MARKING_PERIOD_ID'] = $values[1]['MARKING_PERIOD_ID'];
                        $title_val = DBGet(DBQuery("SELECT DAYS,PERIOD_ID,ROOM_ID FROM course_period_var WHERE course_period_id='" . $_REQUEST['course_period_id'] . "'"));
                        if ($columns_var['DAYS'])
                            $col_var['DAYS'] = $columns_var['DAYS'];
                        else
                            $col_var['DAYS'] = $title_val[1]['DAYS'];
                        if ($columns_var['PERIOD_ID'])
                            $col_var['PERIOD_ID'] = $columns_var['PERIOD_ID'];
                        else
                            $col_var['PERIOD_ID'] = $title_val[1]['PERIOD_ID'];
                        if ($columns_var['ROOM_ID'])
                            $col_var['ROOM_ID'] = $columns_var['ROOM_ID'];
                        else
                            $col_var['ROOM_ID'] = $title_val[1]['ROOM_ID'];
                        $days = $title_val[1]['DAYS'];
                        $pd_id = DBGet(DBQuery("SELECT SHORT_NAME FROM school_periods WHERE PERIOD_ID='" . $title_val[1]['PERIOD_ID'] . "'"));
                        $period_value .= $pd_id[1]['SHORT_NAME'];
                        $conflict = VerifyFixedSchedule($col, $col_var, $update = true);
                        if ($conflict !== true) {
                            echo '<font color=red>' . $conflict . '</font>';
                            $_SESSION['tables'] = $_REQUEST['tables'];
                            break 2;
                        }
                    }

                    if ($columns['SCHEDULE_TYPE'] == 'BLOCKED') {


                        $values = DBGet(DBQuery("SELECT * FROM course_periods WHERE COURSE_PERIOD_ID='" . $_REQUEST['course_period_id'] . "'"));

                        if (isset($columns['CALENDAR_ID']) && ($columns['CALENDAR_ID'] != $values[1]['CALENDAR_ID'])) {
                            $cal_days = DBGet(DBQuery("SELECT * FROM attendance_calendar WHERE CALENDAR_ID=" . $columns['CALENDAR_ID']));

                            $cal_value = DBGet(DBQuery("SELECT course_period_date as cp_dt FROM course_period_var WHERE COURSE_PERIOD_ID='" . $_REQUEST['course_period_id'] . "' "));

                            foreach ($cal_days as $cal_days_k => $cal_days_v) {

                                $cal_days_new[] = $cal_days_v['SCHOOL_DATE'];
                            }
                            foreach ($cal_value as $cal_value_k => $cal_value_v) {
                                if (!in_array($cal_value_v['CP_DT'], $cal_days_new)) {
                                    $dlt_cp_dt = DBQuery("DELETE FROM course_period_var WHERE course_period_id=$_REQUEST[course_period_id] and course_period_date='" . $cal_value_v['CP_DT'] . "'");
                                }
                            }
                        }

                        if (($columns['TEACHER_ID'] && $columns['TEACHER_ID'] != $values[1]['TEACHER_ID'])
                            || ($columns['BEGIN_DATE'] && $columns['BEGIN_DATE'] != $values[1]['BEGIN_DATE']) || ($columns['END_DATE'] && $columns['END_DATE'] != $values[1]['END_DATE']) || ($columns['MARKING_PERIOD_ID'] && $columns['MARKING_PERIOD_ID'] != $values[1]['MARKING_PERIOD_ID'])
                        ) {
                            if ($columns['TEACHER_ID'])
                                $col_bl['TEACHER_ID'] = $columns['TEACHER_ID'];
                            else
                                $col_bl['TEACHER_ID'] = $values[1]['TEACHER_ID'];

                            if ($columns['SECONDARY_TEACHER_ID'])
                                $col_bl['SECONDARY_TEACHER_ID'] = $columns['SECONDARY_TEACHER_ID'];
                            else
                                $col_bl['SECONDARY_TEACHER_ID'] = $values[1]['SECONDARY_TEACHER_ID'];

                            if ($columns['BEGIN_DATE'])
                                $col_bl['BEGIN_DATE'] = $columns['BEGIN_DATE'];
                            else
                                $col_bl['BEGIN_DATE'] = $values[1]['BEGIN_DATE'];

                            if ($columns['END_DATE'])
                                $col_bl['END_DATE'] = $columns['END_DATE'];
                            else
                                $col_bl['END_DATE'] = $values[1]['END_DATE'];
                            if ($columns['MARKING_PERIOD_ID'])
                                $col_bl['MARKING_PERIOD_ID'] = $columns['MARKING_PERIOD_ID'];
                            $conflict = VerifyBlockedSchedule($col_bl, $_REQUEST['course_period_id'], 'cp', true);
                            if ($conflict !== true) {
                                echo '<font color=red>' . $conflict . '</font>';

                                break 2;
                            }
                        }
                    }
                    $teacher = DBGet(DBQuery("SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME FROM staff WHERE STAFF_ID='$staff_id'"));

                    foreach ($_REQUEST['tables']['course_period_var'][$_REQUEST['course_period_id']]['DAYS'] as $days => $day_val) {
                        $day_initial[] = $days;
                    }

                    unset($day_initial);

                    if ($short_name)
                        $mp_title .= paramlib_validation($column = 'SHORT_NAME', $short_name) . ' - ';

                    $title = str_replace("'", "''", str_replace("\'", "'", $mp_title) . $teacher[1]['FIRST_NAME'] . ' ' . $teacher[1]['MIDDLE_NAME'] . ' ' . $teacher[1]['LAST_NAME']);
                    $sql .= "TITLE='" . addslashes($title) . "',calendar_id='$calendar_id',last_updated=NOW(),MODIFIED_BY=" . User('STAFF_ID') . ",";
                    $days = '';
                    if ($table_name == 'course_periods')
                        $sql1 .= "TITLE='" . addslashes($title) . "',SHORT_NAME='" . addslashes($short_name) . "',last_updated=NOW(),MODIFIED_BY=" . User('STAFF_ID') . ",";
                }

                if (!(isset($columns['TITLE']) && trim($columns['TITLE']) == '')) {
                    foreach ($columns as $column => $value) {
                        if ($column == 'GRADE_SCALE_ID' && str_replace("\'", "''", $value) == '') {
                            if (!gradeAssociation($id)) {
                                $sql .= $column . " = NULL,";
                                $go = true;
                            } else {
                                $flag_err = 'Y';
                                continue;
                            }
                        } elseif ($column == 'TEACHER_ID' && str_replace("\'", "''", $value) == 0) {
                            if (!scheduleAssociation($id) && !gradeAssociation($id)) {
                                $sql .= $column . "='" . $staff_id . "',";
                                $go = true;
                            } else {
                                $flag_err = 'Y';
                                continue;
                            }
                        } else {
                            if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                                $value = mysql_real_escape_string($value);
                                $value = str_replace('%u201D', "\"", $value);
                            }

                            if (str_replace("\'", "''", $value) == '') {
                                //                                    if (($table_name == 'course_periods' || $table_name == 'course_period_var') && ($column == 'COURSE_WEIGHT' || $column == 'GRADE_SCALE_ID' || $column == 'CREDITS' || $column == 'DOES_BREAKOFF' || $column == 'DOES_HONOR_ROLL' || $column == 'HALF_DAY' || $column == 'DOES_CLASS_RANK' || $column == 'SECONDARY_TEACHER_ID') && !gradeAssociation($id)) {

                                if (($table_name == 'course_periods' || $table_name == 'course_period_var') && ($column == 'COURSE_WEIGHT' || $column == 'GRADE_SCALE_ID' || $column == 'DOES_HONOR_ROLL' || $column == 'HALF_DAY' || $column == 'DOES_CLASS_RANK' || $column == 'SECONDARY_TEACHER_ID') && !gradeAssociation($id)) {
                                    $sql .= $column . " = NULL,";
                                    $go = true;
                                }

                                if (($column == 'DOES_BREAKOFF' || $column == 'SECONDARY_TEACHER_ID') && $table_name == 'course_periods') {
                                    $sql1 .= $column . " = NULL,";
                                    $go = true;
                                }
                                if (gradeAssociation($id) && ($column == 'TEACHER_ID' || $column == 'SECONDARY_TEACHER_ID' || $column == 'GRADE_SCALE_ID'  || $column == 'DOES_BREAKOFF' || $column == 'DOES_HONOR_ROLL' || $column == 'HALF_DAY' || $column == 'DOES_CLASS_RANK')) {
                                    $flag_err = 'Y';
                                }
                                if ($column == 'DOES_ATTENDANCE' && !(scheduleAssociation($id))) {

                                    $sql .= $column . " = NULL,";
                                    $go = true;
                                    $att = 0;
                                }
                                if ($column == 'DOES_ATTENDANCE' && scheduleAssociation($id) && $value != '') {
                                    if ($msgFlag == '') {
                                        $assoc_err = "<font color=red><b>" . _cannotModifyThisCoursePeriodAsItHasAssociation . "</b></font>";
                                        $msgFlag = 1;
                                    }
                                }
                                if (($column == 'GENDER_RESTRICTION' || $column == 'PARENT_ID') && !scheduleAssociation($id)) {
                                    if ($column == 'PARENT_ID')
                                        $sql .= $column . " = " . $id . ",";
                                    else
                                        $sql .= $column . " = NULL,";
                                    $go = true;
                                }
                                if ($table_name == 'courses' || $table_name == 'course_subjects') {
                                    $sql .= $column . "=NULL,";
                                    $go = true;
                                }
                                if (!scheduleAssociation($id) && ($column == 'MARKING_PERIOD_ID' || $column == 'MP')) {
                                    $sql .= $column . "='" . str_replace("'", "''", $value) . "',";
                                    $go = true;
                                }
                            } else {

                                if ($column == 'SHORT_NAME' || $column == 'TOTAL_SEATS' || $column == 'DOES_ATTENDANCE' || $column == 'DOES_BREAKOFF'  || $column == 'CREDITS') {

                                    if ($column == 'DOES_ATTENDANCE') {
                                        if (scheduleAssociation($id)) {

                                            $check_attn = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=(SELECT PERIOD_ID FROM course_period_var WHERE COURSE_PERIOD_ID=' . $id . ')'));
                                            $check_attn = $check_attn[1];
                                            if ($check_attn['ATTENDANCE'] == 'Y') {
                                                $sql .= $column . "='" . str_replace("'", "''", str_replace("\'", "'", $value)) . "',";
                                                $go = true;
                                            }
                                        } else {
                                            if ($columns['PERIOD_ID'] != '') {
                                                $check_attn = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=' . $columns['PERIOD_ID']));
                                                $check_attn = $check_attn[1];
                                            } else {
                                                $check_attn = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=(SELECT PERIOD_ID FROM course_period_var WHERE COURSE_PERIOD_ID=' . $id . ')'));
                                                $check_attn = $check_attn[1];
                                            }
                                            if ($check_attn['ATTENDANCE'] == 'Y') {
                                                $sql .= $column . "='" . str_replace("'", "''", str_replace("\'", "'", $value)) . "',";
                                                $go = true;
                                            }
                                        }
                                    } else {
                                        $sql .= $column . "='" . str_replace("'", "''", str_replace("\'", "'", $value)) . "',";
                                        if ($table_name == 'course_periods')
                                            $sql1 .= $column . "='" . str_replace("'", "''", str_replace("\'", "'", $value)) . "',";
                                        $go = true;
                                    }
                                } else {
                                    //                                        if (!gradeAssociation($id) && ($column == 'COURSE_WEIGHT' || $column == 'GRADE_SCALE_ID' || $column == 'SECONDARY_TEACHER_ID' || $column == 'TEACHER_ID' || $column == 'CREDITS' || $column == 'DOES_BREAKOFF' || $column == 'DOES_HONOR_ROLL' || $column == 'HALF_DAY' || $column == 'DOES_CLASS_RANK')) {
                                    if (!gradeAssociation($id) && ($column == 'COURSE_WEIGHT' || $column == 'GRADE_SCALE_ID' || $column == 'SECONDARY_TEACHER_ID' || $column == 'TEACHER_ID'  || $column == 'DOES_HONOR_ROLL' || $column == 'HALF_DAY' || $column == 'DOES_CLASS_RANK')) {
                                        $sql .= $column . "='" . str_replace("'", "''", $value) . "',";
                                        if ($table_name == 'course_periods')
                                            $sql1 .= $column . "='" . str_replace("'", "''", $value) . "',";
                                        $go = true;
                                    } else {
                                        //                                            if (gradeAssociation($id) && ($column == 'TEACHER_ID' || $column == 'SECONDARY_TEACHER_ID' || $column == 'GRADE_SCALE_ID' || $column == 'CREDITS' || $column == 'DOES_BREAKOFF' || $column == 'DOES_HONOR_ROLL' || $column == 'HALF_DAY' || $column == 'DOES_CLASS_RANK')) {
                                        if (gradeAssociation($id) && ($column == 'TEACHER_ID' || $column == 'SECONDARY_TEACHER_ID' || $column == 'GRADE_SCALE_ID' ||  $column == 'DOES_HONOR_ROLL' || $column == 'HALF_DAY' || $column == 'DOES_CLASS_RANK')) {
                                            $flag_err = 'Y';
                                        }
                                        if (scheduleAssociation($id) && ($column == 'ROOM_ID' || $column == 'DAYS' || $column == 'PERIOD_ID' || $column == 'MARKING_PERIOD_ID')) {
                                            $flag_err = 'Y';
                                        }
                                        if ($table_name == 'courses' || $table_name == 'course_subjects') {
                                            $sql .= $column . "='" . str_replace("'", "''", $value) . "',";
                                            $go = true;
                                        }
                                        if (!scheduleAssociation($id) && ($column == 'MARKING_PERIOD_ID' || $column == 'MP' || $column == 'GENDER_RESTRICTION' || $column == 'PARENT_ID')) {
                                            $sql .= $column . "='" . str_replace("'", "''", $value) . "',";
                                            $go = true;
                                        }
                                        if (!scheduleAssociation($id) && ($column == 'BEGIN_DATE' || $column == 'END_DATE')) {
                                            $sql .= $column . "='" . str_replace("'", "''", date('Y-m-d', strtotime($value))) . "',";
                                            $go = true;
                                        }
                                        if (!scheduleAssociation($id) && $table_name == 'course_period_var' && $_REQUEST['tables']['course_periods'][$id]['SCHEDULE_TYPE'] == 'FIXED') {

                                            if ($column == 'PERIOD_ID') {
                                                $period_time_edt = DBGet(DBQuery("SELECT START_TIME, END_TIME FROM school_periods WHERE period_id={$value}"));
                                                $period_time_edt = $period_time_edt[1];
                                                $start_time_edt = $period_time_edt['START_TIME'];
                                                $end_time_edt = $period_time_edt['END_TIME'];
                                                $check_attn = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=' . $value));
                                                $check_attn = $check_attn[1];
                                                if ($check_attn['ATTENDANCE'] == 'Y')
                                                    $sql .= " PERIOD_ID=" . $value . " , START_TIME='" . $start_time_edt . "' , END_TIME='" . $end_time_edt . "',";
                                                else
                                                    $sql .= " PERIOD_ID=" . $value . " , START_TIME='" . $start_time_edt . "' , END_TIME='" . $end_time_edt . "',DOES_ATTENDANCE=NULL,";
                                                $go = true;
                                            }
                                            if ($column == 'DOES_BREAKOFF' || $column == 'ROOM_ID' || $column == 'DAYS' || $column == 'DOES_ATTENDANCE') {
                                                if ($column == 'DOES_ATTENDANCE') {
                                                    if ($columns['PERIOD_ID'] != '') {
                                                        $check_attn = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=' . $columns['PERIOD_ID']));
                                                        $check_attn = $check_attn[1];
                                                    } else {
                                                        $check_attn = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=(SELECT PERIOD_ID FROM course_period_var WHERE COURSE_PERIOD_ID=' . $id . ')'));
                                                        $check_attn = $check_attn[1];
                                                    }
                                                    if ($check_attn['ATTENDANCE'] == 'Y') {
                                                        $sql .= $column . "='" . str_replace("'", "''", $value) . "',";
                                                        $go = true;
                                                    }
                                                } else {
                                                    $sql .= $column . "='" . str_replace("'", "''", $value) . "',";
                                                    $go = true;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $sql = substr($sql, 0, -1) . " WHERE " . $where[$table_name] . "='$id'";
                    if ($table_name == 'course_periods')
                        $sql1 = substr($sql1, 0, -1) . " WHERE " . $where[$table_name] . "='$id'";
                    if ($table_name == 'course_periods')
                        DBQuery($sql1);

                    $seat_num_check = DBGet(DBQuery('SELECT FILLED_SEATS FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_REQUEST['course_period_id'] . '\' LIMIT 0,1 '));

                    if ($go && $not_pass_update == false) {


                        $f = 0;
                        if ($seat_num_check[1]['FILLED_SEATS'] > 0) {
                            $cp_gdr_teach_id = DBGet(DBQuery('SELECT * FROM course_periods cp, course_period_var cpv WHERE cp.course_period_id=' . $id . ' AND cp.course_period_id=cpv.course_period_id'));
                            //                                 if ((isset($columns['DOES_BREAKOFF']) && ($columns['DOES_BREAKOFF'] != $cp_gdr_teach_id[1]['DOES_BREAKOFF'])) || (isset($columns['DOES_HONOR_ROLL']) && ($columns['DOES_HONOR_ROLL'] != $cp_gdr_teach_id[1]['DOES_HONOR_ROLL'])) || (isset($columns['HALF_DAY']) && ($columns['HALF_DAY'] != $cp_gdr_teach_id[1]['HALF_DAY'])) || (isset($columns['DOES_CLASS_RANK']) && ($columns['DOES_CLASS_RANK'] != $cp_gdr_teach_id[1]['HALF_DAY'])) && $not_pass_update == false) {
                            if ((isset($columns['BEGIN_DATE']) && ($columns['BEGIN_DATE'] != $cp_gdr_teach_id[1]['BEGIN_DATE'])) || (isset($columns['DOES_HONOR_ROLL']) && ($columns['DOES_HONOR_ROLL'] != $cp_gdr_teach_id[1]['DOES_HONOR_ROLL'])) || (isset($columns['HALF_DAY']) && ($columns['HALF_DAY'] != $cp_gdr_teach_id[1]['HALF_DAY'])) || (isset($columns['DOES_CLASS_RANK']) && ($columns['DOES_CLASS_RANK'] != $cp_gdr_teach_id[1]['HALF_DAY'])) && $not_pass_update == false) {
                                $f = 1;
                            }
                            if ($f == 1) {
                                if ($msgFlag == '') {
                                    //                                echo "<font color=red><b>Cannot Modify this course period as it has association. </b></font>";
                                    $msgFlag = 0;
                                }
                            }
                        } else {

                            if ($f != 1)
                                DBQuery($sql);
                        }
                    } else {
                        $cp_gdr_teach_id = DBGet(DBQuery('SELECT * FROM course_periods WHERE course_period_id=' . $id));

                        if (((isset($columns['GRADE_SCALE_ID']) && $columns['GRADE_SCALE_ID'] != $cp_gdr_teach_id[1]['GRADE_SCALE_ID']) || (isset($columns['TEACHER_ID']) && $columns['TEACHER_ID'] != $cp_gdr_teach_id[1]['TEACHER_ID'])) && $not_pass_update == false)
                            $assoc_err = "<font color=red><b> 6. </b></font>";
                    }
                    if ($flag_err == 'Y') {
                        $cp_gdr_teach_id = DBGet(DBQuery('SELECT * FROM course_periods WHERE course_period_id=' . $id));

                        if (((isset($columns['GRADE_SCALE_ID']) && $columns['GRADE_SCALE_ID'] != $cp_gdr_teach_id[1]['GRADE_SCALE_ID']) || (isset($columns['TEACHER_ID']) && $columns['TEACHER_ID'] != $cp_gdr_teach_id[1]['TEACHER_ID'])) && $not_pass_update == false) {
                            if ($msgFlag == '') {
                                $assoc_err = "<font color=red><b> " . _cannotModifyThisCoursePeriodAsItHasAssociation . " </b></font>";
                                $msgFlag = 1;
                            }
                        }
                    }
                    if ($flag_err == 'Y') {
                        $cp_gdr_teach_id = DBGet(DBQuery('SELECT * FROM course_periods cp, course_period_var cpv WHERE cp.course_period_id=' . $id . ' AND cp.course_period_id=cpv.course_period_id'));
                        if (((isset($columns['ROOM_ID']) && $columns['ROOM_ID'] != $cp_gdr_teach_id[1]['ROOM_ID']) || (isset($columns['PERIOD_ID']) && $columns['PERIOD_ID'] != $cp_gdr_teach_id[1]['PERIOD_ID']) || (isset($columns['DAYS']) && $columns['DAYS'] != $cp_gdr_teach_id[1]['DAYS']) || (isset($columns['MARKING_PERIOD_ID']) && $columns['MARKING_PERIOD_ID'] != $cp_gdr_teach_id[1]['MARKING_PERIOD_ID']))) {
                            if ($msgFlag == '') {
                                $assoc_err = "<font color=red><b>" . _cannotModifyThisCoursePeriodAsItHasAssociation . "</b></font>";
                                $msgFlag = 1;
                            }
                        }
                    }
                    if ($flag_err == 'Y') {
                        $cp_gdr_teach_id = DBGet(DBQuery('SELECT * FROM course_periods WHERE course_period_id=' . $id));

                        if ((isset($columns['DOES_BREAKOFF']) && ($columns['DOES_BREAKOFF'] != $cp_gdr_teach_id[1]['DOES_BREAKOFF'])) || (isset($columns['DOES_HONOR_ROLL']) && ($columns['DOES_HONOR_ROLL'] != $cp_gdr_teach_id[1]['DOES_HONOR_ROLL'])) || (isset($columns['HALF_DAY']) && ($columns['HALF_DAY'] != $cp_gdr_teach_id[1]['HALF_DAY'])) || (isset($columns['DOES_CLASS_RANK']) && ($columns['DOES_CLASS_RANK'] != $cp_gdr_teach_id[1]['HALF_DAY'])) && $not_pass_update == false) {
                            if ($msgFlag == '') {
                                $assoc_err = "<font color=red><b> " . _cannotModifyThisCoursePeriodAsItHasAssociation . "  </b></font>";
                                $msgFlag = 1;
                            }
                        }
                    }
                }


                if ($columns['SCHEDULE_TYPE'] == 'VARIABLE') {

                    if ($_REQUEST['cp_id'] != 'new') {
                        foreach ($_REQUEST['course_period_variable'] as $cp_id => $days) {

                            $schedule_check = DBGet(DBQuery("SELECT COUNT(*) AS TOTAL FROM schedule WHERE COURSE_PERIOD_ID='" . $_REQUEST['course_period_id'] . "'"));
                            foreach ($days as $day => $period) {

                                if ($period['ID'] != '') {
                                    $up_cpv_sql = "UPDATE course_period_var set";
                                    if (!isset($period['DOES_ATTENDANCE'])) {
                                        $period['DOES_ATTENDANCE'] = NULL;
                                    }

                                    foreach ($period as $col => $data) {
                                        if ($schedule_check[1]['TOTAL'] == 0) {
                                            if ($col != 'ID') {
                                                $up_cpv_sql .= " $col='" . $data . "',";
                                            }
                                            if ($col == 'ROOM_ID') {
                                                if ($conflict !== true) {

                                                    $not_pass_update = true;
                                                    break 2;
                                                }
                                            }
                                        } else {
                                            if ($col == 'DOES_ATTENDANCE') {
                                                $up_cpv_sql .= " $col='" . $data . "',";
                                                $does_att = 1;
                                            }
                                            if ($col == 'PERIOD_ID' && $data != '' && $not_pass_update == false) {
                                                if ($msgFlag == '') {
                                                    $error = "<font color=red><b> Cannot Modify this course period as it has association. </b></font>";
                                                    $msgFlag = 1;
                                                }
                                            }
                                            if ($col == 'DAYS' && $data != '' && $not_pass_update == false) {
                                                $schedule_days_check = DBGet(DBQuery("SELECT DAYS FROM course_period_var WHERE ID='" . $period['ID'] . "'"));
                                                if ($schedule_days_check[1]['DAYS'] != $data) {
                                                    if ($msgFlag == '') {
                                                        $error = "<font color=red><b> " . _cannotModifyThisCoursePeriodAsItHasAssociation . " </b></font>";
                                                        $msgFlag = 1;
                                                    }
                                                }
                                            }
                                            if ($col == 'ROOM_ID' && $data != '' && $not_pass_update == false) {

                                                $schedule_days_check = DBGet(DBQuery("SELECT ROOM_ID FROM course_period_var WHERE ID='" . $period['ID'] . "'"));
                                                if ($schedule_days_check[1]['ROOM_ID'] != $data) {
                                                    if ($msgFlag == '') {
                                                        $error = "<font color=red><b> " . _cannotModifyThisCoursePeriodAsItHasAssociation . " </b></font>";
                                                        $msgFlag = 1;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $up_cpv_sql = substr($up_cpv_sql, 0, -1);
                                    $up_cpv_sql .= " WHERE ID='" . $period['ID'] . "'";

                                    if ($not_pass_update == false && $period['ID'] != '')
                                        DBQuery($up_cpv_sql);
                                }
                            }
                        }
                    }
                    if ($error) {
                        echo $error;
                    }
                }
                if (($scheduleAssociation || $gradeAssociation) && is_array($asso_err)) {
                    foreach ($asso_err as $err) {
                        ShowErrPhp($err);
                    }
                }
                if ($tot_seat == 'error') {
                    echo "<font color=red><b>" . _cannotModifySeatsAsItHasAssociation . " </b></font>";
                }
            } else {


                $update = false;
                $sql = "INSERT INTO $table_name ";
                if ($table_name == 'course_subjects') {
                    // $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'course_subjects'"));
                    // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                    $fields = 'SCHOOL_ID,SYEAR,';
                    $values = "'" . UserSchool() . "','" . UserSyear() . "',";
                    // $_REQUEST['subject_id'] = $id[1]['ID'];
                } elseif ($table_name == 'courses') {
                    // $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'courses'"));
                    // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                    // $_REQUEST['course_id'] = $id[1]['ID'];
                    $fields = 'SUBJECT_ID,SCHOOL_ID,SYEAR,';
                    $values = "'$_REQUEST[subject_id]','" . UserSchool() . "','" . UserSyear() . "',";
                } elseif ($table_name == 'course_periods') {
                    if ($columns['SCHEDULE_TYPE'] == 'FIXED') {

                        $pd_id = DBGet(DBQuery("SELECT * FROM school_periods WHERE PERIOD_ID='" . $_REQUEST['tables']['course_period_var']['new']['PERIOD_ID'] . "'"));
                        $period_value .= $pd_id[1]['SHORT_NAME'];
                        $conflict = VerifyFixedSchedule($columns, $columns_var);
                        if ($conflict !== true) {
                            echo '<div class="alert alert-danger alert-bordered">' . $conflict . '</div>';
                            $not_pass = true;
                            $_SESSION['conflict'] = $conflict;
                            $_SESSION['tables'] = $_REQUEST['tables'];
                            echo "<script>window.location.href='Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=new'</script>";
                            break 2;
                        }
                    } elseif ($columns['SCHEDULE_TYPE'] == 'VARIABLE') {
                        $pd_id = DBGet(DBQuery("SELECT SHORT_NAME FROM school_periods WHERE PERIOD_ID='" . $_REQUEST['course_period_variable']['new']['PERIOD_ID'] . "'"));
                        $period_value .= $pd_id[1]['SHORT_NAME'];
                        $conflict = VerifyVariableSchedule($columns);
                        if ($conflict !== true) {
                            echo '<div class="alert alert-danger alert-bordered">' . $conflict . '</div>';
                            $not_pass = true;
                            $_SESSION['conflict'] = $conflict;
                            $_SESSION['tables']['course_periods'] = $_REQUEST['tables']['course_periods'];
                            $_SESSION['tables']['course_period_var']['new']['ROOM_ID'] = $_REQUEST['course_period_variable']['new']['ROOM_ID'];
                            $_SESSION['tables']['course_period_var']['new']['PERIOD_ID'] = $_REQUEST['course_period_variable']['new']['PERIOD_ID'];
                            $_SESSION['tables']['course_period_var']['new']['DAYS'][$_REQUEST['course_period_variable']['new']['DAYS']] = 'Y';
                            if (isset($_REQUEST['course_period_variable']['new']['DOES_ATTENDANCE']))
                                $_SESSION['tables']['course_period_var']['new']['DOES_ATTENDANCE'] = $_REQUEST['tables']['course_period_variable']['new']['DOES_ATTENDANCE'];
                            echo "<script>window.location.href='Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=new'</script>";
                            break 2;
                        }
                    }
                    // $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'course_periods'"));
                    // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                    $fields = 'SYEAR,SCHOOL_ID,COURSE_ID,TITLE,MODIFIED_BY,';
                    $teacher = DBGet(DBQuery("SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME FROM staff WHERE STAFF_ID='$columns[TEACHER_ID]'"));

                    // if (!isset($columns['PARENT_ID']))
                    //     $columns['PARENT_ID'] = $id[1]['ID'];

                    if (isset($columns['MARKING_PERIOD_ID'])) {
                        if (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') == 'school_years')
                            $columns['MP'] = 'FY';
                        elseif (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') == 'school_semesters')
                            $columns['MP'] = 'SEM';
                        else
                            $columns['MP'] = 'QTR';

                        if (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') != 'school_years') {

                            $mp_title = GetMP($columns['MARKING_PERIOD_ID'], 'SHORT_NAME') . ' - ';
                        }
                    }
                    if (isset($columns['MARKING_PERIOD_ID']) == '')
                        $mp_title = 'Custom - ';

                    if ($columns['SCHEDULE_TYPE'] == 'FIXED') {
                        foreach ($_REQUEST['tables']['course_period_var']['new']['DAYS'] as $key => $value)
                            $days .= $key;
                    }

                    if ($columns['SHORT_NAME'])
                        $mp_title .= paramlib_validation($column = 'SHORT_NAME', $columns['SHORT_NAME']) . ' - ';
                    $title = str_replace("'", "''", str_replace("\'", "'", $mp_title) . $teacher[1]['FIRST_NAME'] . ' ' . $teacher[1]['MIDDLE_NAME'] . ' ' . $teacher[1]['LAST_NAME']);
                    $values = "'" . UserSyear() . "','" . UserSchool() . "','$_REQUEST[course_id]','$title'," . User('STAFF_ID') . ",";
                    // $_REQUEST['course_period_id'] = $id[1]['ID'];
                } elseif ($table_name == 'course_period_var') {
                    if ($columns['START_TIME'] == '' && $columns['END_TIME'] == '') {
                        $pd_id = DBGet(DBQuery("SELECT * FROM school_periods WHERE PERIOD_ID='" . $_REQUEST['tables']['course_period_var']['new']['PERIOD_ID'] . "'"));

                        $columns['START_TIME'] = $pd_id[1]['START_TIME'];
                        $columns['END_TIME'] = $pd_id[1]['END_TIME'];
                    }
                    $fields = 'COURSE_PERIOD_ID,';
                    $values = "$_REQUEST[course_period_id],";
                }

                $go = 0;
                foreach ($columns as $column => $value) {
                    if ($value != '') {
                        $value = trim(paramlib_validation($column, $value));
                        $fields .= $column . ',';
                        if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                            $value = mysql_real_escape_string($value);
                        }
                        $value = str_replace('"', '""', $value);
                        $values .= '"' . $value . '",';

                        $go = true;
                    }
                }
                $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';
                if ($go) {
                    DBQuery($sql);
                    if ($table_name == 'course_subjects') {
                        $_REQUEST['subject_id'] = mysqli_insert_id($connection);
                    } else if ($table_name == 'courses') {
                        $_REQUEST['course_id'] = mysqli_insert_id($connection);
                    } else if ($table_name == 'course_periods') {
                        $_REQUEST['course_period_id'] = mysqli_insert_id($connection);
                        if (!isset($columns['PARENT_ID'])) {
                            $update_parent_id = "UPDATE course_periods SET PARENT_ID=" . $_REQUEST['course_period_id'] . " WHERE COURSE_PERIOD_ID = " . $_REQUEST['course_period_id'];
                            DBQuery($update_parent_id);
                        }
                    }
                }

                // ----------------------------------------------- //
                if ($_REQUEST['w_course_period_id']) {
                    $max_id = DBGet(DBQuery("SELECT MAX(COURSE_PERIOD_ID) AS CP_ID FROM course_periods;"));
                    $sql_2 = "UPDATE course_periods SET PARENT_ID=" . $_REQUEST['w_course_period_id'] . " WHERE COURSE_PERIOD_ID = " . $max_id[1]['CP_ID'];
                    DBQuery($sql_2);
                }
                // ----------------------------------------------- //
            }
        }
    }
    if ($assoc_err) {
        echo $assoc_err;
        unset($assoc_err);
    }
    if ($_REQUEST['course_period_variable'] && $not_pass != true && $update == false) {
        $days_sql = "INSERT INTO course_period_var(course_period_id,days,period_id,start_time,end_time,room_id,does_attendance)VALUES";
        foreach ($_REQUEST['course_period_variable'] as $cp_id => $days) {

            $days_sql .= "($_REQUEST[course_period_id],'$days[DAYS]',$days[PERIOD_ID],'" . $days['n']['START_TIME'] . "','" . $days['n']['END_TIME'] . "',$days[ROOM_ID],'$days[DOES_ATTENDANCE]'),";
        }
        $days_sql = substr($days_sql, 0, -1);
        DBQuery($days_sql);
    }

    //------------course period name with period and days------//
    //
    //                $mod_cp_id=$_REQUEST['course_period_id'];
    //              
    //                $qr_cp_det=DBGet (DBQuery ('select * from course_periods where course_period_id=\''.$mod_cp_id.'\''));
    //                $qr_cp_title=$qr_cp_det[1]['TITLE'];
    //                $cp_title_arr=explode('-',$qr_cp_title);
    //              
    //                $rcp_pos=substr($qr_cp_title,0,strrpos($qr_cp_title,'-')-1);
    //              $qr_cpv_det=DBGet (DBQuery ('select * from course_period_var where course_period_id=\''.$mod_cp_id.'\''));
    //         $day_arr=array();
    //                        $pr_arr=array();
    //                        foreach($qr_cpv_det as $key_det => $cpv_det)
    //                        {
    //                            
    //                            if($cpv_det['DAYS']!='')
    //                            $day_arr[]=$cpv_det['DAYS'];
    //                            else
    //                                $day_arr[]=$cpv_det['COURSE_PERIOD_DATE'];
    //
    //                                $pd_id = DBGet(DBQuery("SELECT SHORT_NAME FROM school_periods WHERE PERIOD_ID='" . $cpv_det['PERIOD_ID'] . "'"));
    //                       $period_value1=$pd_id[1]['SHORT_NAME'];
    //                       $pr_arr[]=$period_value1;
    //
    //                                
    //                        }
    //                 $new_cp_ti=$rcp_pos.' -'.implode(' ',$day_arr).' -'.implode(' ',$pr_arr).' -'.$cp_title_arr[count($cp_title_arr)-1];
    //                 DBQuery('update  course_periods  set title=\''.$new_cp_ti.'\' where course_period_id=\''.$mod_cp_id.'\'');
    //------------course period name with period and days------//
    if ($not_pass == false)
        unset($_REQUEST['tables']);
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'delete' && AllowEdit()) {
    unset($sql);
    $course_period_id = paramlib_validation($colmn = 'PERIOD_ID', $_REQUEST['course_period_id']);
    $course_id = paramlib_validation($colmn = 'PERIOD_ID', $_REQUEST['course_id']);
    $subject_id = paramlib_validation($colmn = 'PERIOD_ID', $_REQUEST['subject_id']);

    if (clean_param($_REQUEST['course_period_id'], PARAM_ALPHANUM)) {
        $table = 'course period';
        $sql[] = "UPDATE course_periods SET PARENT_ID=NULL WHERE PARENT_ID='$course_period_id'";
        $sql[] = "DELETE FROM course_periods WHERE COURSE_PERIOD_ID='$course_period_id'";
        $sql[] = "DELETE FROM schedule WHERE COURSE_PERIOD_ID='$course_period_id'";
        $sql[] = "DELETE FROM  gradebook_assignment_types WHERE COURSE_PERIOD_ID='$course_period_id'";
        $sql[] = "DELETE FROM  gradebook_assignments WHERE COURSE_PERIOD_ID='$course_period_id' OR COURSE_ID=" . $_REQUEST['course_id'];
    } elseif (clean_param($_REQUEST['course_id'], PARAM_ALPHANUM)) {
        $table = 'course';
        $course_period = DBGet(DBQuery("SELECT COURSE_PERIOD_ID FROM course_periods WHERE COURSE_ID='$course_id'"));
        foreach ($course_period as $course1)
            if ($course1['COURSE_PERIOD_ID'] == '') {
                $sql[] = "DELETE FROM courses WHERE COURSE_ID='$course_id'";
                $extra_sql = "SELECT COURSE_PERIOD_ID FROM course_periods WHERE COURSE_ID='$course_id'";
                $result_sql = DBGet(DBQuery($extra_sql));
                $sql[] = "UPDATE course_periods SET PARENT_ID=NULL WHERE PARENT_ID = '" . $result_sql . "'";
                $sql[] = "DELETE FROM course_periods WHERE COURSE_ID='$course_id'";
                $sql[] = "DELETE FROM schedule WHERE COURSE_ID='$course_id'";
                $sql[] = "DELETE FROM schedule_requests WHERE COURSE_ID='$course_id'";
                $sql[] = "DELETE FROM  gradebook_assignment_types WHERE COURSE_ID='$course_id'";
                $sql[] = "DELETE FROM  gradebook_assignments WHERE COURSE_ID='$course_id'";
                if (DeletePromptCommon($table)) {
                    if (BlockDelete($table)) {
                        DBQuery($sql);
                        unset($_REQUEST['modfunc']);
                    }
                }
            }



        if ($course1['COURSE_PERIOD_ID'] != '') {
            PopTable('header', '' . _unableToDelete . '');
            DrawHeaderHome('<div class="alert alert-danger alert-bordered">' . _courseCannotBeDeleted . '</div>');
            echo '<div align=right><a href=Modules.php?modname=schoolsetup/Courses.php&subject_id=' . $subject_id . '&course_id=' . $course_id . ' style="text-decoration:none"><b>' . _backToCourse . '</b></a></div>';
            PopTable('footer');
        } else {
            if (DeletePromptCommon($table)) {
                if (BlockDelete($table)) {
                    $sql[] = "DELETE FROM courses WHERE COURSE_ID='$course_id'";
                    $extra_sql = "SELECT COURSE_PERIOD_ID FROM course_periods WHERE COURSE_ID='$course_id'";
                    $result_sql = DBGet(DBQuery($extra_sql));
                    $sql[] = "UPDATE course_periods SET PARENT_ID=NULL WHERE PARENT_ID = '" . $result_sql . "'";
                    $sql[] = "DELETE FROM course_periods WHERE COURSE_ID='$course_id'";
                    $sql[] = "DELETE FROM schedule WHERE COURSE_ID='$course_id'";
                    $sql[] = "DELETE FROM schedule_requests WHERE COURSE_ID='$course_id'";
                    foreach ($sql as $query)
                        DBQuery($query);
                    unset($_REQUEST['modfunc']);
                    unset($_REQUEST[course_id]);
                }
            }
        }
    } elseif (clean_param($_REQUEST['subject_id'], PARAM_ALPHANUM)) {
        $table = 'subject';
        $subject = DBGet(DBQuery("SELECT COURSE_ID FROM courses WHERE SUBJECT_ID='$subject_id'"));
        foreach ($subject as $subject1)
            if ($subject1['COURSE_ID'] == '') {
                $sql[] = "DELETE FROM course_subjects WHERE SUBJECT_ID='$subject_id'";
                $courses = DBGet(DBQuery("SELECT COURSE_ID FROM courses WHERE SUBJECT_ID='$subject_id'"));
                if (count($courses)) {
                    foreach ($courses as $course) {
                        $sql[] = "DELETE FROM courses WHERE COURSE_ID='$course[COURSE_ID]'";


                        $extra_sql2 = "SELECT COURSE_PERIOD_ID FROM course_periods WHERE COURSE_ID='$course[COURSE_ID]'";
                        $result_sql2 = DBGet(DBQuery($extra_sql2));
                        $sql[] = "UPDATE course_periods SET PARENT_ID=NULL WHERE PARENT_ID = '" . $result_sql2 . "'";

                        $sql[] = "DELETE FROM course_periods WHERE COURSE_ID='$course[COURSE_ID]'";
                        $sql[] = "DELETE FROM schedule WHERE COURSE_ID='$course[COURSE_ID]'";
                        $sql[] = "DELETE FROM schedule_requests WHERE COURSE_ID='$course[COURSE_ID]'";
                    }
                }
            }
        if ($subject1['COURSE_ID'] != '') {
            PopTable('header', '' . _unableToDelete . '');
            echo '<CENTER class="m-b-15"><h4>' . _subjectCannotBeDeleted . '</h4> <BR/> <a href=Modules.php?modname=schoolsetup/Courses.php&subject_id=' . $subject_id . ' style="text-decoration:none;" class="btn btn-primary">' . _backToSubject . '</a></CENTER>';
            PopTable('footer');
        } else {
            if (DeletePromptCommon($table)) {
                if (BlockDelete($table)) {
                    $sql[] = "DELETE FROM course_subjects WHERE SUBJECT_ID='$subject_id'";
                    $courses = DBGet(DBQuery("SELECT COURSE_ID FROM courses WHERE SUBJECT_ID='$subject_id'"));
                    if (count($courses)) {
                        foreach ($courses as $course) {
                            $sql[] = "DELETE FROM courses WHERE COURSE_ID='$course[COURSE_ID]'";
                            $extra_sql2 = "SELECT COURSE_PERIOD_ID FROM course_periods WHERE COURSE_ID='$course[COURSE_ID]'";
                            $result_sql2 = DBGet(DBQuery($extra_sql2));
                            $sql[] = "UPDATE course_periods SET PARENT_ID=NULL WHERE PARENT_ID = '" . $result_sql2 . "'";
                            $sql[] = "DELETE FROM course_periods WHERE COURSE_ID='$course[COURSE_ID]'";
                            $sql[] = "DELETE FROM schedule WHERE COURSE_ID='$course[COURSE_ID]'";
                            $sql[] = "DELETE FROM schedule_requests WHERE COURSE_ID='$course[COURSE_ID]'";
                        }
                    }
                    foreach ($sql as $query)
                        DBQuery($query);
                    unset($_REQUEST['modfunc']);
                    unset($_REQUEST['subject_id']);
                }
            }
        }
    }

    if ($_REQUEST['course_period_id']) {
        if (DeletePromptCommon($table)) {
            if (BlockDelete($table)) {
                foreach ($sql as $query)
                    DBQuery($query);
                unset($_REQUEST['modfunc']);
                unset($_REQUEST['course_period_id']);
            }
        }
    }
}


if ($_REQUEST['modfunc'] == 'detail') {
    if ($_POST['button'] == _save && $_REQUEST['mode'] == 'add') {
        $conflict = VerifyBlockedSchedule($_REQUEST['values'], $_REQUEST['course_period_id'], 'cpv');
        $_SESSION['block_schedule_err'] = $conflict;
    }
    if ($_POST['button'] == _save && $_REQUEST['mode'] == 'edit') {
        //print_r($_REQUEST);
        $title_val = DBGet(DBQuery("SELECT PERIOD_ID,ROOM_ID FROM course_period_var WHERE course_period_id='" . $_REQUEST['course_period_id'] . "'"));

        if ($_REQUEST['values']['PERIOD_ID'])
            $col_blk['PERIOD_ID'] = $_REQUEST['values']['PERIOD_ID'];
        else
            $col_blk['PERIOD_ID'] = $title_val[1]['PERIOD_ID'];
        if ($_REQUEST['values']['ROOM_ID'])
            $col_blk['ROOM_ID'] = $_REQUEST['values']['ROOM_ID'];
        else
            $col_blk['ROOM_ID'] = $title_val[1]['ROOM_ID'];

        $conflict = VerifyBlockedSchedule($col_blk, $_REQUEST['course_period_id'], 'cpv', true);
        $_SESSION['block_schedule_err'] = $conflict;
    }

    if ($_POST['button'] == _save && $conflict === true) {

        if ($_REQUEST['mode'] == 'add') {
            $chek_assoc = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM schedule WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id'] . ' AND (START_DATE<=\'' . date('Y-m-d') . '\' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE>=\'' . date('Y-m-d') . '\' ))'));
            if ($chek_assoc[1]['REC_EX'] == 0) {
                $weekday = date('l', strtotime($_REQUEST['meet_date']));
                if ($weekday == 'Sunday')
                    $d = 'U';
                if ($weekday == 'Monday')
                    $d = 'M';
                if ($weekday == 'Tuesday')
                    $d = 'T';
                if ($weekday == 'Wednesday')
                    $d = 'W';
                if ($weekday == 'Thursday')
                    $d = 'H';
                if ($weekday == 'Friday')
                    $d = 'F';
                if ($weekday == 'Saturday')
                    $d = 'S';
                $sql = "INSERT INTO course_period_var ";
                $fields = 'COURSE_PERIOD_ID,COURSE_PERIOD_DATE,DAYS,';
                $values = "'" . $_REQUEST['course_period_id'] . "','" . $_REQUEST['meet_date'] . "','" . $d . "',";
                $go = false;
                foreach ($_REQUEST['values'] as $column => $value) {
                    if (trim($value)) {
                        $value = paramlib_validation($column, $value);
                        $fields .= $column . ',';
                        $values .= '"' . str_replace("", "", trim($value)) . '",';
                        $go = true;
                    }
                }
                $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';
            } else {
                $error = 'Blocked_assoc';
            }
        } elseif ($_REQUEST['mode'] == 'edit') {
            $chek_assoc = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM schedule WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id'] . ' AND (START_DATE<=\'' . date('Y-m-d') . '\' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE>=\'' . date('Y-m-d') . '\' ))'));
            if ($chek_assoc[1]['REC_EX'] == 0) {
                if ($_REQUEST['values']['PERIOD_ID'] != '' && $_REQUEST['values']['DOES_ATTENDANCE'] == '') {
                    $check_sp = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=' . $_REQUEST['values']['PERIOD_ID']));
                    $check_sp = $check_sp[1];
                    $cp_per_id = DBGet(DBQuery('SELECT * FROM course_period_var WHERE ID=' . $_REQUEST['cpv_id']));
                    $cp_per_id = $cp_per_id[1];
                    if ($check_sp['PERIOD_ID'] != $cp_per_id['PERIOD_ID']) {
                        if ($check_sp['ATTENDANCE'] == '' && $cp_per_id['DOES_ATTENDANCE'] == 'Y') {
                            $_REQUEST['values']['DOES_ATTENDANCE'] = '';
                        }
                    }
                } elseif ($_REQUEST['values']['PERIOD_ID'] == '' && $_REQUEST['values']['DOES_ATTENDANCE'] != '') {

                    $cp_per_id = DBGet(DBQuery('SELECT * FROM course_period_var WHERE ID=' . $_REQUEST['cpv_id']));
                    $cp_per_id = $cp_per_id[1];
                    $check_sp = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=' . $cp_per_id['PERIOD_ID']));
                    $check_sp = $check_sp[1];
                    if ($check_sp['ATTENDANCE'] == '') {
                        $_REQUEST['values']['DOES_ATTENDANCE'] = '';
                    }
                }
                if ($_REQUEST['values']['PERIOD_ID'] != '' && $_REQUEST['values']['DOES_ATTENDANCE'] != '') {
                    $check_sp = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=' . $_REQUEST['values']['PERIOD_ID']));
                    $check_sp = $check_sp[1];
                    if ($check_sp['ATTENDANCE'] == '') {
                        $_REQUEST['values']['DOES_ATTENDANCE'] = '';
                    }
                }
                $sql = "UPDATE course_period_var SET ";
                $go = false;
                foreach ($_REQUEST['values'] as $column => $value) {
                    $value = paramlib_validation($column, $value);
                    if ($column == 'DOES_ATTENDANCE' && $value == '') {
                        $sql .= $column . '=null,';
                    } else
                        $sql .= $column . '="' . str_replace("", "", trim($value)) . '",';
                    $go = true;
                }
                $sql = substr($sql, 0, -1);
                $sql .= " WHERE COURSE_PERIOD_ID={$_REQUEST['course_period_id']} AND course_period_date='" . $_REQUEST['meet_date'] . "' AND ID='" . $_REQUEST['cpv_id'] . "'";
                $sql;
            } else {
                $check_take_attn = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL FROM attendance_period WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id'] . ' AND school_date=\'' . $_REQUEST['meet_date'] . '\''));
                $check_miss_attn = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL FROM missing_attendance WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id'] . ' AND school_date=\'' . $_REQUEST['meet_date'] . '\''));
                if ($check_take_attn[1]['TOTAL'] > 0 || $check_miss_attn[1]['TOTAL'] > 0)
                    $error = 'Blocked_assoc';

                else {
                    if ($_REQUEST['values']['PERIOD_ID'] != '' && $_REQUEST['values']['DOES_ATTENDANCE'] == '') {
                        $check_sp = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=' . $_REQUEST['values']['PERIOD_ID']));
                        $check_sp = $check_sp[1];
                        $cp_per_id = DBGet(DBQuery('SELECT * FROM course_period_var WHERE ID=' . $_REQUEST['cpv_id']));
                        $cp_per_id = $cp_per_id[1];
                        if ($check_sp['PERIOD_ID'] != $cp_per_id['PERIOD_ID']) {
                            if ($check_sp['ATTENDANCE'] == '' && $cp_per_id['DOES_ATTENDANCE'] == 'Y' && isset($_REQUEST['values']['DOES_ATTENDANCE'])) {
                                $_REQUEST['values']['DOES_ATTENDANCE'] = '';
                            }
                        }
                    } elseif ($_REQUEST['values']['PERIOD_ID'] == '' && $_REQUEST['values']['DOES_ATTENDANCE'] != '') {

                        $cp_per_id = DBGet(DBQuery('SELECT * FROM course_period_var WHERE ID=' . $_REQUEST['cpv_id']));
                        $cp_per_id = $cp_per_id[1];
                        $check_sp = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=' . $cp_per_id['PERIOD_ID']));
                        $check_sp = $check_sp[1];
                        if ($check_sp['ATTENDANCE'] == '') {
                            $_REQUEST['values']['DOES_ATTENDANCE'] = '';
                        }
                    }
                    if ($_REQUEST['values']['PERIOD_ID'] != '' && $_REQUEST['values']['DOES_ATTENDANCE'] != '') {
                        $cp_per_id = DBGet(DBQuery('SELECT * FROM course_period_var WHERE ID=' . $_REQUEST['cpv_id']));
                        $cp_per_id = $cp_per_id[1];
                        $check_sp = DBGet(DBQuery('SELECT * FROM school_periods WHERE PERIOD_ID=' . $cp_per_id['PERIOD_ID']));
                        $check_sp = $check_sp[1];
                        if ($check_sp['ATTENDANCE'] == '') {
                            $_REQUEST['values']['DOES_ATTENDANCE'] = '';
                        }
                    }
                    $sql = "UPDATE course_period_var SET ";
                    $go = false;
                    foreach ($_REQUEST['values'] as $column => $value) {
                        $value = paramlib_validation($column, $value);
                        if ($column == 'DOES_ATTENDANCE' && $value == '') {
                            $sql .= $column . '=NULL,';
                            $go = true;
                        } elseif ($column == 'DOES_ATTENDANCE' && $value != '') {
                            $sql .= $column . '="' . str_replace("", "", trim($value)) . '",';
                            $go = true;
                        } elseif (($column == 'PERIOD_ID' && $value != '') || ($column == 'ROOM_ID' && $value != ''))
                            $error = 'Blocked_period_room';
                    }
                    $sql = substr($sql, 0, -1);
                    $sql .= " WHERE COURSE_PERIOD_ID={$_REQUEST['course_period_id']} AND course_period_date='" . $_REQUEST['meet_date'] . "' AND ID='" . $_REQUEST['cpv_id'] . "'";
                }
            }
        }
        if ($go) {

            if (!attendanceAssociation($_REQUEST['course_period_id']))
                DBQuery($sql);
            if ($_REQUEST['course_period_id'] != 'new') {
                $schedule_type = DBGet(DBQuery('SELECT SCHEDULE_TYPE FROM course_periods WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id']));
                if ($schedule_type[1]['SCHEDULE_TYPE'] == 'BLOCKED') {
                    $check_does_attendanceRE = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM course_period_var WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id'] . ' AND DOES_ATTENDANCE=\'Y\' '));
                    if ($check_does_attendanceRE[1]['REC_EX'] == 0)
                        DBQuery('UPDATE course_periods SET HALF_DAY=NULL WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id']);
                }
            }
        }
        DBQuery("UPDATE course_period_var cpv,school_periods sp SET cpv.start_time=sp.start_time,cpv.end_time=sp.end_time WHERE sp.period_id=cpv.period_id AND course_period_id=$_REQUEST[course_period_id]");
        unset($_REQUEST['values']);
        unset($_SESSION['_REQUEST_vars']['values']);
        if ($error == 'Blocked_assoc')
            echo '<SCRIPT language=javascript>window.location.href = "Modules.php?modname=' . $_REQUEST['modname'] . '&error=Blocked_assoc&subject_id=' . $_REQUEST['subject_id'] . '&course_id=' . $_REQUEST['course_id'] . '&course_period_id=' . $_REQUEST['course_period_id'] . '&month=' . date(strtotime($_REQUEST['meet_date'])) . '"; window.close();</script>';

        elseif ($error == 'Blocked_period_room') {
            echo '<SCRIPT language=javascript>window.location.href = "Modules.php?modname=' . $_REQUEST['modname'] . '&error=Blocked_period_room&subject_id=' . $_REQUEST['subject_id'] . '&course_id=' . $_REQUEST['course_id'] . '&course_period_id=' . $_REQUEST['course_period_id'] . '&month=' . date(strtotime($_REQUEST['meet_date'])) . '"; window.close();</script>';
        } else
            echo '<SCRIPT language=javascript>window.location.href = "Modules.php?modname=' . $_REQUEST['modname'] . '&subject_id=' . $_REQUEST['subject_id'] . '&course_id=' . $_REQUEST['course_id'] . '&course_period_id=' . $_REQUEST['course_period_id'] . '&month=' . date(strtotime($_REQUEST['meet_date'])) . '"; window.close();</script>';
    }
    //    elseif ($_POST['button'] == 'Clear & Exit') {
    //        
    //        $chek_assoc = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM schedule WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id'] . ' AND (START_DATE<=\'' . date('Y-m-d') . '\' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE>=\'' . date('Y-m-d') . '\' ))'));
    //        if ($chek_assoc[1]['REC_EX'] == 0) {
    //            DBQuery("DELETE FROM course_period_var WHERE course_period_id=$_REQUEST[course_period_id] AND  course_period_date='" . $_REQUEST[meet_date] . "' and id='" . $_REQUEST[cpv_id] . "'");
    //            unset($_REQUEST['values']);
    //            unset($_SESSION['_REQUEST_vars']['values']);
    //            echo '<SCRIPT language=javascript>opener.document.location = "Modules.php?modname=' . $_REQUEST['modname'] . '&subject_id=' . $_REQUEST[subject_id] . '&course_id=' . $_REQUEST[course_id] . '&course_period_id=' . $_REQUEST[course_period_id] . '&month=' . date(strtotime($_REQUEST['meet_date'])) . '"; window.close();</script>';
    //        } else {
    //            echo '<SCRIPT language=javascript>opener.document.location = "Modules.php?modname=' . $_REQUEST['modname'] . '&error=Blocked_assoc&subject_id=' . $_REQUEST[subject_id] . '&course_id=' . $_REQUEST[course_id] . '&course_period_id=' . $_REQUEST[course_period_id] . '&month=' . date(strtotime($_REQUEST['meet_date'])) . '"; window.close();</script>';
    //        }
    //    }
    //    else {
    //        $cpblocked_RET = DBGet(DBQuery("SELECT COURSE_PERIOD_DATE,PERIOD_ID,ROOM_ID,DOES_ATTENDANCE FROM course_period_var where course_period_id=$_REQUEST[course_period_id] AND course_period_date='" . $_REQUEST['meet_date'] . "' AND id='" . $_REQUEST['id'] . "'"));
    //        $cpblocked_RET = $cpblocked_RET[1];
    //        $periods_RET = DBGet(DBQuery("SELECT PERIOD_ID,TITLE FROM school_periods WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
    //        if (count($periods_RET)) {
    //            foreach ($periods_RET as $period)
    //                $periods[$period['PERIOD_ID']] = $period['TITLE'];
    //        }
    //
    //        $room_RET = DBGet(DBQuery("SELECT ROOM_ID,TITLE FROM rooms WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));
    //        if (count($room_RET)) {
    //            foreach ($room_RET as $room)
    //                $rooms[$room['ROOM_ID']] = $room['TITLE'];
    //        }
    //        if (isset($_REQUEST['values'])) {
    //            echo '<div class="alert bg-danger alert-styled-left">' . $conflict . ' on selected date</div>';
    //            unset($_REQUEST['values']);
    //            unset($_SESSION['_REQUEST_vars']['values']);
    //            $_REQUEST['id'] = $_REQUEST['cpv_id'];
    //        }
    //        //PopTableforWindow('header', $title);
    //        echo "<FORM class=form-horizontal name=popform id=popform action=ForWindow.php?modname=$_REQUEST[modname]&meet_date=$_REQUEST[meet_date]&modfunc=detail&mode=$_REQUEST[mode]&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=$_REQUEST[course_period_id]&calendar_id=$_REQUEST[calendar_id] METHOD=POST>";
    //        echo '<div class="panel">';
    //        echo '<div class="tabbable">';
    //        echo '<ul class="nav nav-tabs nav-tabs-bottom no-margin-bottom"><li class="active"><a href="javascript:void(0);">Add Class</a></li></ul>';
    //        
    //        echo '<div class="panel-body">';
    //        echo '<div id="block_error"></div>';
    //        echo '<input type="hidden" name="get_status" id="get_status" value="" />';
    //        echo '<input type="hidden" name="' . $date . '_id" id="' . $date . '_id" value="' . $_REQUEST['course_period_id'] . '"/>';
    //        echo '<input type="hidden" id="run_block_valid" value="block"/>';
    //        
    //        if ($_REQUEST['add'] == 'new')
    //            unset($cpblocked_RET);
    //        if ($_REQUEST['id'] != '')
    //            echo "<input type=hidden name=cpv_id value='$_REQUEST[id]' />";
    //        echo '<div class="form-group"><label class="control-label text-right col-lg-4">Date</label><div class="col-lg-8">' . ProperDate($_REQUEST[meet_date]) . '</div></div>';
    //        echo '<div class="form-group"><label class="control-label text-right col-lg-4">Period</label><div class="col-lg-8">' . SelectInput($cpblocked_RET['PERIOD_ID'], 'values[PERIOD_ID]', '', $periods, 'N/A', 'id=' . $date . '_period class=form-control onchange="formcheck_periods_F2(' . $date . ');"') . '</div></div>';
    //        echo '<input type="hidden" id="hidden_period_block" value="' . $cpblocked_RET['PERIOD_ID'] . '" />';
    //        echo '<div class="form-group"><label class="control-label text-right col-lg-4">Room</label><div class="col-lg-8">' . SelectInput($cpblocked_RET['ROOM_ID'], 'values[ROOM_ID]', '', $rooms, 'N/A', 'id=' . $date . '_room ') . '</div></div>';
    //        echo '<div class="form-group"><label class="control-label text-right col-lg-4">Takes attendance</label><div class="col-lg-8">' . CheckboxInputSwitch($cpblocked_RET['DOES_ATTENDANCE'], 'values[DOES_ATTENDANCE]', '', '', false, 'Yes', 'No', ' id=' . $date . '_does_attendance onclick="formcheck_periods_attendance_F2(' . (($date != '') ? $date : 1) . ',this);"','switch-success') . '<br><div id="ajax_output"></div></div></div>';
    //        
    //        echo '</div>'; //.panel-body
    //        echo '<div class="panel-footer p-l-10 p-r-10"><INPUT type=submit class="btn btn-primary" name=button value=Save onClick="return validate_block_schedule(' . $date . ');">';
    //       // echo '<div class="panel-footer p-l-10 p-r-10"><INPUT type=submit class="btn btn-primary" name=button value=Save onClick="return validate_block_schedule(' . $date . ');">';
    //        echo '&nbsp;';
    //        if ($_REQUEST['mode'] == 'edit')
    //            echo '<INPUT type=submit name=button class="btn btn-primary" value=Clear1 & Exit onclick="formload_ajax(\'popform\');"> &nbsp ';
    //        else
    //            echo ' &nbsp <INPUT type=submit name=button class="btn btn-primary" value=Close onclick="window.close();">';
    //        echo '</div>'; //.panel-footer
    //        
    //        echo '</div>'; //.tabbable
    //        echo '</div>'; //.panel
    //        //PopTableWindow('footer');
    //        echo '</FORM>';
    //    }
    else {
        echo '<SCRIPT language=javascript>window.location.href = "Modules.php?modname=' . $_REQUEST['modname'] . '&subject_id=' . $_REQUEST['subject_id'] . '&course_id=' . $_REQUEST['course_id'] . '&course_period_id=' . $_REQUEST['course_period_id'] . '&month=' . date(strtotime($_REQUEST['meet_date'])) . '"; window.close();</script>';
    }
}

if (!$_REQUEST['modfunc'] && !$_REQUEST['course_modfunc'] && !$_REQUEST['action']) {
    DrawBC("" . _schoolSetup . " > " . ProgramTitle());

    //update filled_seats while clicking the course_period
    $get_det = DBGet(DBQuery('SELECT TOTAL_SEATS,FILLED_SEATS FROM course_periods WHERE COURSE_PERIOD_ID="' . $_REQUEST['course_period_id'] . '" '));
    $total_sch_rec = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) AS TOT_REC FROM schedule WHERE COURSE_PERIOD_ID="' . $_REQUEST['course_period_id'] . '" AND (END_DATE >=  "' . date('Y-m-d') . '" || END_DATE =  \'0000-00-00\' || END_DATE IS NULL)'));
    if ($get_det[1]['FILLED_SEATS'] != $total_sch_rec[1]['TOT_REC'])
        DBQuery('UPDATE course_periods SET FILLED_SEATS="' . $total_sch_rec[1]['TOT_REC'] . '" WHERE COURSE_PERIOD_ID="' . $_REQUEST['course_period_id'] . '" ');

    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);
    $credit_div = 'hide';
    if (AllowEdit())
        $delete_button = "<INPUT type=button class=\"btn btn-default\" value=" . _delete . " onClick='javascript:window.location=\"Modules.php?modname=$_REQUEST[modname]&modfunc=delete&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=$_REQUEST[course_period_id]\"'>";
    // ADDING & EDITING FORM
    if (clean_param($_REQUEST['course_period_id'], PARAM_ALPHANUM)) {
        if ($_REQUEST['course_period_id'] != 'new') {
            $sql = "SELECT PARENT_ID,TITLE,SHORT_NAME,
                                MP,MARKING_PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID,CALENDAR_ID,IF(MARKING_PERIOD_ID IS NULL,BEGIN_DATE,NULL) AS BEGIN_DATE,IF(MARKING_PERIOD_ID IS NULL,END_DATE,NULL) AS END_DATE,
                                TOTAL_SEATS,(TOTAL_SEATS - FILLED_SEATS) AS AVAILABLE_SEATS,
                                GRADE_SCALE_ID,DOES_HONOR_ROLL,DOES_CLASS_RANK,
                                GENDER_RESTRICTION,HOUSE_RESTRICTION,CREDITS,
                                HALF_DAY,DOES_BREAKOFF,COURSE_WEIGHT,DAYS,PERIOD_ID,ROOM_ID,DOES_ATTENDANCE,SCHEDULE_TYPE
                        FROM course_periods cp LEFT JOIN course_period_var cpv ON (cp.course_period_id=cpv.course_period_id)
                        WHERE cp.COURSE_PERIOD_ID='$_REQUEST[course_period_id]'";
            $QI = DBQuery($sql);
            $RET = DBGet($QI);

            $RET = $RET[1];
            $title = $RET['TITLE'];
            $new = false;
            $cpdays_RET = DBGet(DBQuery("SELECT ID,DAYS,PERIOD_ID,ROOM_ID,DOES_ATTENDANCE,START_TIME,END_TIME FROM course_period_var where days IS NOT NULL AND course_period_id=$_REQUEST[course_period_id] ORDER BY DAYS "));

            $cpblocked_RET = DBGet(DBQuery("SELECT COURSE_PERIOD_DATE,PERIOD_ID,ROOM_ID,DOES_ATTENDANCE FROM course_period_var where course_period_date IS NOT NULL AND course_period_id=$_REQUEST[course_period_id]"), array(), array('COURSE_PERIOD_DATE'));
            $div = true;
        } else {
            $sql = "SELECT TITLE
                        FROM courses
                        WHERE COURSE_ID='$_REQUEST[course_id]'";
            $QI = DBQuery($sql);
            $RET = DBGet($QI);
            $RET = $RET[1];
            $title = $RET['TITLE'] . ' - ' . _newCoursePeriod;
            unset($RET);
            if ($not_pass == true) {
                foreach ($_REQUEST['tables']['course_periods'] as $id => $data) {
                    foreach ($data as $key => $val) {
                        $RET[$key] = $val;
                    }
                }
                if ($RET['SCHEDULE_TYPE'] == 'FIXED')
                    $RET = $RET + $columns_var;
                elseif ($RET['SCHEDULE_TYPE'] == 'VARIABLE')
                    $cpdays_RET = $_REQUEST['course_period_variable']['new'];
                $div = false;
            }
            if ($_REQUEST['conflict'] == 'y') {
                if ($_REQUEST['tables']['course_periods']['new']['CREDITS'] != '')
                    $credit_div = 'show';
                foreach ($_REQUEST['tables']['course_periods']['new'] as $id => $data) {
                    if ($id == 'BEGIN_DATE' && $data != '')
                        $RET['MARKING_PERIOD_ID'] = '';
                    $RET[$id] = $data;
                }
                foreach ($_REQUEST['tables']['course_period_var']['new'] as $id => $data) {
                    if ($id == 'DAYS') {
                        foreach ($data as $data_in => $data_drill)
                            $RET[$id] .= $data_in;
                    } else
                        $RET[$id] = $data;
                }
            }
            unset($delete_button);
            $checked = 'CHECKED';
            $new = true;
        }

        echo '<div class="panel panel-default">';
        if (isset($_SESSION['block_schedule_err']) && $_SESSION['block_schedule_err'] != '') {
            if ($_SESSION['block_schedule_err'] != 1)
                echo '<div id="divErr"><div class="alert alert-danger no-border"><i class="fa fa-info-circle"></i> ' . $_SESSION['block_schedule_err'] . '</div></div>';
            unset($_SESSION['block_schedule_err']);
        }
        echo "<FORM name=F2 id=F2 class=form-horizontal action=Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=$_REQUEST[course_period_id] method=POST>";
        echo '<input type="hidden" name="get_status" id="get_status" value="" />';
        echo '<INPUT TYPE="hidden" NAME="course_period_day_checked" id="course_period_day_checked" VALUE="" />';
        echo '<input type="hidden" name="cp_id" id="cp_id" value="' . $_REQUEST['course_period_id'] . '"/>';
        if ($_REQUEST['course_period_id'] != 'new')
            echo '<input type="hidden" id="schedule_type_id" value="' . $RET['SCHEDULE_TYPE'] . '"/>';
        if (UserProfileID() != 2) {
            echo '<div class="clearfix p-20">
                <h6 class="pull-left m-b-0"><b>' . $title . '</b></h6>
                <div class="pull-right">' . $delete_button . SubmitButton(_save, '', 'id=save_cp class="btn btn-primary m-l-5" onclick="return validate_course_period();"') . '</div>
            </div>';
        }
        echo '<hr class="no-margin"/>';

        echo '<div class="panel-body">';

        $header .= '<div class="clearfix">';
        $header .= '<div class="col-sm-6 col-lg-4">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _shortName . '</label><div class="col-md-8">' . TextInput($RET['SHORT_NAME'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][SHORT_NAME]', '', '', $div) . '</div></div>';
        $header .= '</div>'; //.col-sm-6.col-lg-4
        $header .= '<input type="hidden" id="hidden_cp_id" value="' . $_REQUEST['course_period_id'] . '">';
        $cal_RET = DBGet(DBQuery("SELECT TITLE,CALENDAR_ID FROM school_calendars WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' ORDER BY DEFAULT_CALENDAR DESC"));
        $options = array();
        $header .= '<div class="col-sm-6 col-lg-4">';
        foreach ($cal_RET as $option)
            $options[$option['CALENDAR_ID']] = $option['TITLE'];
        if ($_REQUEST['course_period_id'] == 'new')
            $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">Calendar</label><div class="col-md-8">' . SelectInput($RET['CALENDAR_ID'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][CALENDAR_ID]', '', $options, 'N/A', ' id=calendar_id onchange=reset_schedule();', $div) . '</div></div>';
        else {
            $cal_sql = "SELECT TITLE,CALENDAR_ID FROM school_calendars WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' AND CALENDAR_ID='" . $RET['CALENDAR_ID'] . "'";
            $cal_RET = DBGET(DBQuery($cal_sql));
            $cal_RET = $cal_RET[1];
            $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _calendar . '</label><div class="col-md-8">' . SelectInput($cal_RET['CALENDAR_ID'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][CALENDAR_ID]', '', $options, 'N/A', ' id=calendar_id onchange=reset_schedule();', $div) . '</div></div>';
        }
        $header .= '</div>'; //.col-sm-6.col-lg-4
        //echo "SELECT STAFF_ID,LAST_NAME,FIRST_NAME,MIDDLE_NAME FROM staff INNER JOIN staff_school_relationship USING (staff_id) WHERE school_id='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' AND PROFILE='teacher' AND  (ISNULL(IS_DISABLE) OR IS_DISABLE!='Y') AND (END_DATE>=CURDATE() OR END_DATE IS NULL OR END_DATE='0000-00-00') AND (START_DATE<=CURDATE() OR START_DATE='0000-00-00' OR START_DATE IS NULL ) ORDER BY LAST_NAME,FIRST_NAME ";
        //$teachers_RET = DBGet(DBQuery("SELECT STAFF_ID,LAST_NAME,FIRST_NAME,MIDDLE_NAME FROM staff INNER JOIN staff_school_relationship USING (staff_id) WHERE school_id='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' AND PROFILE='teacher' AND  (ISNULL(IS_DISABLE) OR IS_DISABLE!='Y') AND (END_DATE>=CURDATE() OR END_DATE IS NULL OR END_DATE='0000-00-00') AND (START_DATE<=CURDATE() OR START_DATE='0000-00-00' OR START_DATE IS NULL ) ORDER BY LAST_NAME,FIRST_NAME "));

        // $teachers_RET= DBGet(DBQuery("SELECT STAFF_ID,LAST_NAME,FIRST_NAME,MIDDLE_NAME FROM staff s INNER JOIN staff_school_relationship ssr USING(staff_id) ,login_authentication la WHERE (s.PROFILE_ID!=4 OR s.PROFILE_ID IS NULL) AND ssr.SYEAR='".UserSyear()."' AND s.STAFF_ID=la.USER_ID AND la.PROFILE_ID NOT IN (3,4) AND SCHOOL_ID=".UserSchool()." AND (s.IS_DISABLE<>'Y' OR s.IS_DISABLE IS NULL) AND (ssr.END_DATE>='CURDATE()' OR ssr.END_DATE='0000-00-00' OR ssr.END_DATE IS NULL) AND PROFILE='teacher' ORDER BY LAST_NAME,FIRST_NAME")) ;
        $teachers_RET = DBGet(DBQuery("SELECT STAFF_ID,LAST_NAME,FIRST_NAME,MIDDLE_NAME FROM staff s INNER JOIN staff_school_relationship ssr USING(staff_id) ,login_authentication la WHERE (s.PROFILE_ID!=4 OR s.PROFILE_ID IS NULL) AND ssr.SYEAR='" . UserSyear() . "' AND s.STAFF_ID=la.USER_ID AND la.PROFILE_ID NOT IN (3,4) AND SCHOOL_ID=" . UserSchool() . " AND (s.IS_DISABLE<>'Y' OR s.IS_DISABLE IS NULL) AND (ssr.END_DATE>=CURDATE() OR ssr.END_DATE<'0000-01-01' OR ssr.END_DATE IS NULL) AND PROFILE='teacher' ORDER BY LAST_NAME,FIRST_NAME"));


        if (count($teachers_RET)) {
            foreach ($teachers_RET as $teacher) {

                $teachers[$teacher['STAFF_ID']] = trim($teacher['LAST_NAME']) . ', ' . $teacher['FIRST_NAME'] . ' ' . $teacher['MIDDLE_NAME'];
            }
        }

        if ($_REQUEST['course_period_id'] != 'new') {
            $qr = DBGET(DBQuery('SELECT TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id'] . ''));

            $teacher_id = $qr[1]['TEACHER_ID'];
            $qr1 = DBGet(DBQuery('SELECT STAFF_ID,LAST_NAME,FIRST_NAME,MIDDLE_NAME FROM staff WHERE STAFF_ID="' . $qr[1]['TEACHER_ID'] . '"'));
            $k = $qr1[1]['STAFF_ID'];
            $teachers[$k] = trim($qr1[1]['LAST_NAME']) . ', ' . $qr1[1]['FIRST_NAME'] . ' ' . $qr1[1]['MIDDLE_NAME'];
        }
        $header .= '<div class="col-sm-6 col-lg-4">';
        if ($_REQUEST['course_period_id'] != 'new' && $RET['AVAILABLE_SEATS'] != $RET['TOTAL_SEATS'])
            $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _primaryTeacher . '</label><div class="col-md-8">' . SelectInputDisabledMsg($RET['TEACHER_ID'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][TEACHER_ID]', '', $teachers, 'N/A', '', $div, "" . _toChangeTeacherGoToSchoolSetupCoursesTeacherReAssignment . "") . '<input type="hidden" id="hidden_primary_teacher_id" name="hidden_primary_teacher_id" value="' . $RET['TEACHER_ID'] . '"></div></div>';
        else
            $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _primaryTeacher . '</label><div class="col-md-8">' . SelectInput($RET['TEACHER_ID'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][TEACHER_ID]', '', $teachers, 'N/A', 'onchange="validate_cp_teacher_fields()"', $div) . '<input type="hidden" id="hidden_primary_teacher_id" name="hidden_primary_teacher_id" value="' . $RET['TEACHER_ID'] . '"></div></div>';
        $header .= '</div>'; //.col-sm-6.col-lg-4
        $header .= '</div>'; //.row

        $header .= '<div class="clearfix">';
        $header .= '<div class="col-sm-6 col-lg-4">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _secondaryTeacher . '</label><div class="col-md-8">' . SelectInput($RET['SECONDARY_TEACHER_ID'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][SECONDARY_TEACHER_ID]', '', $teachers, 'N/A', 'onchange="validate_cp_teacher_fields()"', $div) . '<input type="hidden" id="hidden_secondary_teacher_id" value="' . $RET['SECONDARY_TEACHER_ID'] . '"></div></div>';
        $header .= '</div>'; //.col-sm-6.col-lg-4
        $header .= '<div class="col-sm-6 col-lg-4">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _seats . '</label><div class="col-md-8"><div class="col-md-4">' . TextInput($RET['TOTAL_SEATS'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][TOTAL_SEATS]', '', 'size=4 class=form-control', $div) . '</div>';
        if ($_REQUEST['course_period_id'] != 'new')
            $header .= '<label class="col-md-4 text-success text-right">' . _availableSeats . '</label><div class="col-md-4"><span class="seat-count text-success">' . $RET['AVAILABLE_SEATS'] . '</span></div>';
        $header .= '</div></div>';
        $header .= '</div>'; //.col-sm-6.col-lg-4


        $options_RET = DBGet(DBQuery("SELECT TITLE,ID FROM report_card_grade_scales WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "'"));
        $options = array();
        foreach ($options_RET as $option)
            $options[$option['ID']] = $option['TITLE'];
        $cp_id_js = ($_REQUEST['course_period_id'] == 'new' ? 0 : $_REQUEST['course_period_id']);
        $header .= '<div class="col-sm-6 col-lg-4">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _gradingScale . '</label><div class="col-md-8">' . SelectInput($RET['GRADE_SCALE_ID'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][GRADE_SCALE_ID]', '', $options, '' . _notGraded . '', 'onChange="toggle_course_weight(this,' . $cp_id_js . ');"', $div) . '</div></div>';
        $header .= '</div>'; //.col-sm-6.col-lg-4
        $header .= '</div>'; //.row


        $header .= '<div class="clearfix">';
        $header .= '<div class="col-sm-6 col-lg-4">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _creditHours . '</label><div class="col-md-8">' . TextInput(sprintf('%0.3f', $RET['CREDITS']), 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][CREDITS]', '', 'size=4') . '</div></div>';
        $header .= '</div>'; //.col-sm-6.col-lg-4
        $header .= '<div class="col-sm-6 col-lg-4">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _genderRestriction . '</label><div class="col-md-8">' . SelectInput($RET['GENDER_RESTRICTION'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][GENDER_RESTRICTION]', '', array('N' => 'None', 'M' => 'Male', 'F' => 'Female'), false, '', $div) . '</div></div>';
        $header .= '</div>'; //.col-sm-6.col-lg-4
        if ($_REQUEST['course_period_id'] != 'new' && $RET['PARENT_ID'] != $_REQUEST['course_period_id']) {
            $parent = DBGet(DBQuery("SELECT cp.TITLE as CP_TITLE,c.TITLE AS C_TITLE FROM course_periods cp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cp.COURSE_PERIOD_ID='" . $RET['PARENT_ID'] . "'"));
            $parent = $parent[1]['C_TITLE'] . ' : ' . $parent[1]['CP_TITLE'];
        } elseif ($_REQUEST['course_period_id'] != 'new') {
            $children = DBGet(DBQuery("SELECT COURSE_PERIOD_ID FROM course_periods WHERE PARENT_ID='" . $_REQUEST['course_period_id'] . "' AND COURSE_PERIOD_ID!='" . $_REQUEST['course_period_id'] . "'"));
            if (count($children))
                $parent = 'N/A';
            else
                $parent = 'None';
        }
        $header .= '<div class="col-sm-6 col-lg-4">';

        //      --------------------------------------------- Temp Coment -------------------------------------------------     //
        if ($_REQUEST['course_period_id'] != 'new' && $RET['PARENT_ID'] != $_REQUEST['course_period_id']) {

            //  $header .= "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">Parent Period</label><div class=\"col-md-8\"><DIV id=course_div class=\"form-control\" disabled=\"disabled\">" . $parent . "</DIV> " . ($parent != 'N/A' && AllowEdit() ? "<A HREF=# onclick='window.open(\"ForWindow.php?modname=miscellaneous/ChooseParentCourse.php\",\"\",\"scrollbars=yes,resizable=yes,width=800,height=400\");'>Choose</A>" . "&nbsp;&nbsp;" . "<INPUT type=checkbox name='parent_cp[" . $_REQUEST['course_period_id'] . "]' value='" . $_REQUEST['course_period_id'] . "' >&nbsp;Remove" . "<BR>" : '') . "</div></div>";
            $header .= "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">" . _parentPeriod . "</label><div class=\"col-md-8\">" . ($parent != 'N/A' && AllowEdit() ? "<A HREF=javascript:void(0) data-toggle='modal' data-target='#modal_default'  onClick='cleanModal(\"course_modal\");cleanModal(\"cp_modal\");' class=\"text-primary\"><i class=\"icon-menu6 m-t-10 pull-right\"></i><DIV id=course_div class=\"form-control\" readonly=\"readonly\">" . ($parent ? $parent : '<span class=text-grey>' . _clickToSelect . '</span>') . "</DIV></A>" . "&nbsp;&nbsp;" . "<INPUT type=checkbox name='parent_cp[" . $_REQUEST['course_period_id'] . "]' value='" . $_REQUEST['course_period_id'] . "' >&nbsp;Remove" . "<BR>" : '') . "</div></div>";
        } else {
            // $header .= "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">Parent Period</label><div class=\"col-md-8\"><DIV id=course_div class=\"form-control\" disabled=\"disabled\">" . $parent . "</DIV> " . ($parent != 'N/A' && AllowEdit() ? "<A HREF=# onclick='window.open(\"ForWindow.php?modname=miscellaneous/ChooseParentCourse.php\",\"\",\"scrollbars=yes,resizable=yes,width=800,height=400\");'>Choose</A><BR>" : '') . "</div></div>";

            $header .= "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">" . _parentPeriod . "</label><div class=\"col-md-8\">" . ($parent != 'N/A' && AllowEdit() ? "<A HREF=javascript:void(0) data-toggle='modal' data-target='#modal_default'  onClick='cleanModal(\"course_modal\");cleanModal(\"cp_modal\");' class=\"text-primary\"><i class=\"icon-menu6 m-t-10 pull-right\"></i><DIV id=course_div class=\"form-control\" readonly=\"readonly\">" . ($parent ? $parent : '<span class=text-grey>' . _clickToSelect . '</span>') . "</DIV></A><BR>" : '') . "</div></div>";
        }
        $header .= '</div>'; //.col-sm-6.col-lg-4
        $header .= '</div>'; //.row

        $header .= '<div class="clearfix">';
        $header .= '<div class="col-sm-12 col-lg-12"><div class="form-inline">';
        //        echo $RET['DOES_BREAKOFF'];
        $header .= CheckboxInputSwitch($RET['DOES_BREAKOFF'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][DOES_BREAKOFF]', '' . _allowTeacherGradescale . '', $checked, $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>', $div . 'id="course_breakoff_id" ' . ($RET['GRADE_SCALE_ID'] == '' ? 'disabled="disabled"' : ''), ' switch-success');
        $header .= CheckboxInputSwitch($RET['COURSE_WEIGHT'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][COURSE_WEIGHT]', '' . _courseIsWeighted . '', $checked, $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>', $div . 'id="course_weight_id" ' . ($RET['GRADE_SCALE_ID'] == '' ? 'disabled="disabled"' : ''), ' switch-success');
        $header .= CheckboxInputSwitch($RET['DOES_HONOR_ROLL'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][DOES_HONOR_ROLL]', _affectsHonorRoll, $checked, $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>', $div, ' switch-success');

        if ($_REQUEST['course_period_id'] != 'new') {
            $get_does_att = DBGet(DBQuery('SELECT count(*) as RET_EX FROM course_period_var WHERE COURSE_PERIOD_ID=' . $_REQUEST['course_period_id'] . ' AND DOES_ATTENDANCE=\'Y\' '));
            if ($get_does_att[1]['RET_EX'] == 0)
                $header .= CheckboxInputSwitch($RET['HALF_DAY'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][HALF_DAY]', _halfDay, $checked, $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>', $div . ' disabled id="half_day"', ' switch-success');
            else
                $header .= CheckboxInputSwitch($RET['HALF_DAY'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][HALF_DAY]', _halfDay, $checked, $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>', $div . ' id="half_day"', ' switch-success');
        } else
            $header .= CheckboxInputSwitch($RET['HALF_DAY'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][HALF_DAY]', _halfDay, $checked, $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>', $div . ' disabled id="half_day"', ' switch-success');

        $header .= CheckboxInputSwitch($RET['DOES_CLASS_RANK'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][DOES_CLASS_RANK]', _affectsClassRank, $checked, $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>', $div, ' switch-success');

        $header .= '</div></div>'; //.col-sm-12.col-lg-12
        $header .= '</div>'; //.row


        unset($options);
        $mp_RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,SHORT_NAME,'2' AS t,SORT_ORDER FROM school_quarters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' UNION SELECT MARKING_PERIOD_ID,SHORT_NAME,'1' AS t,SORT_ORDER FROM school_semesters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' UNION SELECT MARKING_PERIOD_ID,SHORT_NAME,'0' AS t,SORT_ORDER FROM school_years WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY 3,4"));

        if (count($mp_RET)) {
            foreach ($mp_RET as $mp)
                $options[$mp['MARKING_PERIOD_ID']] = $mp['SHORT_NAME'];
        }
        $header .= '<hr/>';
        $header .= '<div class="clearfix">';
        $header .= '<h5>' . _chooseTheDuration . 's</h5>';
        $header .= '<div class="form-group clearfix"><div class="col-md-12"><label class="radio-inline"><input type=radio name=date_range value=mp id=preset onchange=mp_range_toggle(this);  ' . ($RET['MARKING_PERIOD_ID'] ? ' checked' : '') . '> ' . _markingPeriod . '</label> <label class="radio-inline"><input type=radio name=date_range value=dr id=custom onchange=mp_range_toggle(this); ' . ($RET['BEGIN_DATE'] ? ' checked' : '') . '> ' . _customDateRange . '</label></div></div>';

        $header .= '<DIV id=mp_range style=display:' . ($RET['MARKING_PERIOD_ID'] ? 'block' : 'none') . ' class="clearfix"><div class="col-md-4">' . SelectInput($RET['MARKING_PERIOD_ID'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][MARKING_PERIOD_ID]', '', $options, 'N/A', 'id=marking_period', $div) . '</div></DIV>';
        $header .= '<DIV id=date_range style=display:' . ($RET['BEGIN_DATE'] ? 'block' : 'none') . ' class="clearfix"><div class="col-md-4"><div class="form-group"><label class="control-label text-right col-md-4">' . _begins . '</label><div class="col-md-8">' . DateInputAY($RET['BEGIN_DATE'], 'begin', 1, ($_REQUEST['conflict'] == 'y' ? true : false), '') . '</div></div></div><div class="col-md-4"><div class="form-group"><label class="control-label text-right col-md-4">' . _ends . '</label><div class="col-md-8">' . DateInputAY($RET['END_DATE'], 'end', 2, ($_REQUEST['conflict'] == 'y' ? true : false), '') . '</div></div></div></DIV>';
        $header .= '</div>'; //.col-md-12
        $header .= '<hr/>';

        $header .= '<div class="clearfix">';
        $header .= '<h5>' . _chooseScheduleType . '</h5>';
        $header .= '<div class="well">';
        //'tables[course_periods][' . $_REQUEST['course_period_id'] . '][DOES_BREAKOFF]'
        $header .= '<div><label class="radio-inline" for=fixed_schedule><input type=radio name=tables[course_periods][' . $_REQUEST['course_period_id'] . '][SCHEDULE_TYPE] id=fixed_schedule value=fixed onclick=show_cp_meeting_days(this.value,"' . $_REQUEST['course_period_id'] . '"); ' . ($RET['SCHEDULE_TYPE'] == 'FIXED' ? ' checked' : '') . ' ' . disabled() . '>' . _fixedSchedule . '</label><label class="radio-inline" for=variable_schedule><input type=radio name=tables[course_periods][' . $_REQUEST['course_period_id'] . '][SCHEDULE_TYPE] id=variable_schedule value=variable onclick=show_cp_meeting_days(this.value,"' . $_REQUEST['course_period_id'] . '"); ' . ($RET['SCHEDULE_TYPE'] == 'VARIABLE' ? ' checked' : '') . ' ' . disabled() . '>' . _variableSchedule . '</label><label class="radio-inline" for=blocked_schedule><input type=radio name=tables[course_periods][' . $_REQUEST['course_period_id'] . '][SCHEDULE_TYPE] id=blocked_schedule value=blocked onclick=show_cp_meeting_days(this.value,"' . $_REQUEST['course_period_id'] . '");  ' . ($RET['SCHEDULE_TYPE'] == 'BLOCKED' ? ' checked' : '') . ' ' . disabled() . '>' . _enterByCalendarDays . '</label></div>';
        //$header .= '<div><label class="radio-inline" for=fixed_schedule><input type=radio name=schedule_type id=fixed_schedule value=fixed onclick=show_cp_meeting_days(this.value,"' . $_REQUEST[course_period_id] . '"); ' . ($RET['SCHEDULE_TYPE'] == 'FIXED' ? ' checked' : '') . ' ' . disabled() . '>Fixed Schedule</label><label class="radio-inline" for=variable_schedule><input type=radio name=schedule_type id=variable_schedule value=variable onclick=show_cp_meeting_days(this.value,"' . $_REQUEST[course_period_id] . '"); ' . ($RET['SCHEDULE_TYPE'] == 'VARIABLE' ? ' checked' : '') . ' ' . disabled() . '>Variable Schedule</label><label class="radio-inline" for=blocked_schedule><input type=radio name=schedule_type id=blocked_schedule value=blocked onclick=show_cp_meeting_days(this.value,"' . $_REQUEST[course_period_id] . '");  ' . ($RET['SCHEDULE_TYPE'] == 'BLOCKED' ? ' checked' : '') . ' ' . disabled() . '>Enter by Calendar Days</label></div>';

        if ($_REQUEST['conflict'] == 'y') {
            echo "<script>show_cp_meeting_daysError('" . strtolower($RET['SCHEDULE_TYPE']) . "','new'," . $RET['CALENDAR_ID'] . "," . $RET['ROOM_ID'] . "," . $RET['PERIOD_ID'] . ",'" . $RET['DAYS'] . "','" . $RET['DOES_ATTENDANCE'] . "');</script>";
        }
        if ($_REQUEST['course_period_id'] != 'new' || $not_pass == true) {
            $periods_RET = DBGet(DBQuery("SELECT PERIOD_ID,TITLE FROM school_periods WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
            if (count($periods_RET)) {
                foreach ($periods_RET as $period)
                    $periods[$period['PERIOD_ID']] = $period['TITLE'];
            }

            $room_RET = DBGet(DBQuery("SELECT ROOM_ID,TITLE FROM rooms WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));
            if (count($room_RET)) {
                foreach ($room_RET as $room)
                    $rooms[$room['ROOM_ID']] = $room['TITLE'];
            }
            $days_RET = DBGet(DBQuery("SELECT DAYS FROM school_calendars WHERE calendar_id=$RET[CALENDAR_ID]"));
            $cal_days = str_split($days_RET[1]['DAYS']);
            foreach ($cal_days as $day) {
                $caldays[$day] = conv_day($day);
            }
        }
        if ($_REQUEST['course_period_id'] == 'new' && $not_pass == false) {
            $header .= '<hr/><DIV id=meeting_days></DIV>';
        } elseif ($RET['SCHEDULE_TYPE'] == 'VARIABLE') {
            $header .= '<input type=hidden name=tables[course_periods][' . $_REQUEST['course_period_id'] . '][SCHEDULE_TYPE] value=VARIABLE id="variable"/>';
            echo '<input type="hidden" name="get_status" id="get_status" value="" />';

            $header .= '<DIV id=meeting_days><hr/>';

            $header .= '<TABLE  width=100% class="table table-bordered"><TR><TD></TD><TD width="100px" class="subtabs"><strong>' . _days . '</strong></TD><TD width="200px" class="subtabs"><strong>' . _period . '</strong></TD><TD class="subtabs"><strong>' . _time . '</strong></TD><TD width="150px" class="subtabs"><strong>' . _room . '</strong></TD><TD width="130px" align="center" class="subtabs"><strong>' . _takesAttendance . '</strong></TD></TR>';
            $rowcolor = 'even';
            if ($not_pass == true) {
                $cp_var_val = $cpdays_RET;
            }

            ///////////////////  for date order maintain ////////

            $flg = 1;
            foreach ($cpdays_RET as $cp_k => $cp_dt) {
                if ($cp_dt['DAYS'] == 'M') {
                    $new_cpdays_RET[$flg] = $cpdays_RET[$cp_k];
                    $flg++;
                }
            }
            foreach ($cpdays_RET as $cp_k => $cp_dt) {
                if ($cp_dt['DAYS'] == 'T') {
                    $new_cpdays_RET[$flg] = $cpdays_RET[$cp_k];
                    $flg++;
                }
            }
            foreach ($cpdays_RET as $cp_k => $cp_dt) {
                if ($cp_dt['DAYS'] == 'W') {
                    $new_cpdays_RET[$flg] = $cpdays_RET[$cp_k];
                    $flg++;
                }
            }
            foreach ($cpdays_RET as $cp_k => $cp_dt) {
                if ($cp_dt['DAYS'] == 'H') {
                    $new_cpdays_RET[$flg] = $cpdays_RET[$cp_k];
                    $flg++;
                }
            }
            foreach ($cpdays_RET as $cp_k => $cp_dt) {
                if ($cp_dt['DAYS'] == 'F') {
                    $new_cpdays_RET[$flg] = $cpdays_RET[$cp_k];
                    $flg++;
                }
            }
            foreach ($cpdays_RET as $cp_k => $cp_dt) {
                if ($cp_dt['DAYS'] == 'S') {
                    $new_cpdays_RET[$flg] = $cpdays_RET[$cp_k];
                    $flg++;
                }
            }
            foreach ($cpdays_RET as $cp_k => $cp_dt) {
                if ($cp_dt['DAYS'] == 'U') {
                    $new_cpdays_RET[$flg] = $cpdays_RET[$cp_k];
                    $flg++;
                }
            }
            if ($_REQUEST['course_period_id'] != 'new') {
                $does_attendance_ids = array();
                if (count($new_cpdays_RET) > 0) {
                    for ($i = 1; $i <= count($new_cpdays_RET); $i++) {

                        $cp_var_val = $new_cpdays_RET[$i];

                        $header .= '<TR class="' . $rowcolor . '"><TD align="center">' . '<a href=\'Modules.php?modname=' . $_REQUEST['modname'] . '&action=delete&subject_id=' . $_REQUEST['subject_id'] . '&course_id=' . $_REQUEST['course_id'] . '&course_period_id=' . $_REQUEST['course_period_id'] . '&cpv_id=' . $cp_var_val['ID'] . '\' >' . button('remove') . '</a>' . '';
                        $header .= '<input type="hidden" name="course_period_variable[' . $_REQUEST['course_period_id'] . '][' . $cp_var_val['ID'] . '][DAYS]" value=' . $cp_var_val['DAYS'] . '</TD>';
                        $header .= '<TD>' . SelectInput($cp_var_val['DAYS'], 'course_period_variable[' . $_REQUEST['course_period_id'] . '][' . $cp_var_val['ID'] . '][DAYS]' . $i, '', $caldays, 'N/A', 'id=days' . $i) . '</TD>';
                        $header .= '<TD>' . SelectInput($cp_var_val['PERIOD_ID'], 'course_period_variable[' . $_REQUEST['course_period_id'] . '][' . $cp_var_val['ID'] . '][PERIOD_ID]' . $i, '', $periods, 'N/A', 'id=' . $cp_var_val['DAYS'] . $i . '_period class=form-control  onchange=show_period_time(this.value,"' . $cp_var_val['DAYS'] . $i . '","' . $_REQUEST['course_period_id'] . '","' . $cp_var_val['ID'] . '");', $div) . '<input type=hidden name=course_period_variable[' . $_REQUEST['course_period_id'] . '][' . $cp_var_val['ID'] . '][ID] value="' . $cp_var_val['ID'] . '"></TD>';
                        $header .= '<TD><div id=' . $cp_var_val['DAYS'] . $i . '_period_time>' . ($cp_var_val['PERIOD_ID'] ? ProperTime($cp_var_val['START_TIME']) . ' To ' . ProperTime($cp_var_val['END_TIME']) . '<input type=hidden name=course_period_variable[' . $_REQUEST['course_period_id'] . '][' . $cp_var_val['ID'] . '][START_TIME] value="' . $cp_var_val['START_TIME'] . '"><input type=hidden name=course_period_variable[' . $_REQUEST['course_period_id'] . '][' . $cp_var_val['ID'] . '][END_TIME] value="' . $cp_var_val['END_TIME'] . '">' : '') . '</div></TD>';
                        $header .= '<TD>' . SelectInput($cp_var_val['ROOM_ID'], 'course_period_variable[' . $_REQUEST['course_period_id'] . '][' . $cp_var_val['ID'] . '][ROOM_ID]', '', $rooms, 'N/A', 'id=' . $cp_var_val['DAYS'] . '_room ', $div) . '<input type=hidden id=course_period_variable[' . $_REQUEST['course_period_id'] . '][' . $cp_var_val['ID'] . '][ROOM_ID]' . $i . ' value="' . $cp_var_val['ROOM_ID'] . '"></TD>';
                        $header .= '<TD align="left">' . CheckboxInput($cp_var_val['DOES_ATTENDANCE'], 'course_period_variable[' . $_REQUEST['course_period_id'] . '][' . $cp_var_val['ID'] . '][DOES_ATTENDANCE]' . $i, '', '', true, 'Yes', 'No', ($value == 'Y' ? $div : false), ' id=' . $cp_var_val['DAYS'] . $i . '_does_attendance onclick="formcheck_periods_attendance_F2(3,this,' . $i . ');"') . '<br></TD></TR>';
                        $does_attendance_ids[] = $cp_var_val['DAYS'] . $i . '_does_attendance';
                        echo '<input type="hidden" name="cp_id" id="' . $cp_var_val['DAYS'] . $i . '_id" value="' . $_REQUEST['course_period_id'] . '"/>';
                        echo '<input type="hidden" name="fixed_day" id="fixed_day3_' . $i . '" value="' . $cp_var_val['DAYS'] . $i . '" />';
                        echo '<input type="hidden"  id="disabled_option_' . $i . '" value="' . $cp_var_val['PERIOD_ID'] . '" />';
                        echo '<input type="hidden" id="for_editing_room" value="' . $cp_var_val['ID'] . '"/>';
                    }
                }
                $does_attendance_ids[] = 'n_does_attendance';
                $does_attendance_ids = implode(',', $does_attendance_ids);
                echo '<input type="hidden" id="does_attendance_ids" value="' . $does_attendance_ids . '"/>';
            }

            $header .= '<TR class="' . $rowcolor . '"><TD align="center">' . button('add') . '</TD>';
            $header .= '<TD>' . SelectInput('', 'course_period_variable[' . $_REQUEST['course_period_id'] . '][n][DAYS]', '', $caldays, 'N/A', 'id=n') . '</TD>';
            $header .= '<TD>' . SelectInput('', 'course_period_variable[' . $_REQUEST['course_period_id'] . '][n][PERIOD_ID]', '', $periods, 'N/A', 'id=n_period class=form-control ' . $disable . ' onchange=show_period_time(this.value,"n","' . $_REQUEST['course_period_id'] . '","n");', $div) . '</TD>';
            $header .= '<TD><div id=n_period_time></div></TD>';
            $header .= '<TD>' . SelectInput('', 'course_period_variable[' . $_REQUEST['course_period_id'] . '][n][ROOM_ID]', '', $rooms, 'N/A', 'id=n_room ' . $disable, $div) . '</TD>';
            $header .= '<TD align="left">' . CheckboxInput('', 'course_period_variable[' . $_REQUEST['course_period_id'] . '][n][DOES_ATTENDANCE]', '', '', true, 'Yes', 'No', ($value == 'Y' ? $div : false), ' id=n_does_attendance onclick="formcheck_periods_attendance_F2(4,this);"' . $disable) . '<br></TD></TR>';
            $header .= '</TR></TABLE>';
            $header .= '<div id="ajax_output"></div>';
            $header .= '</DIV>';
            echo '<input type="hidden" name="cp_id" id="n_id" value="' . $_REQUEST['course_period_id'] . '"/>';
            echo '<input type="hidden" name="fixed_day4" id="fixed_day4" value="n" />';
        } elseif ($RET['SCHEDULE_TYPE'] == 'FIXED') {
            $header .= '<hr/>';
            $header .= '<input type=hidden name=tables[course_periods][' . $_REQUEST['course_period_id'] . '][SCHEDULE_TYPE] value=FIXED />';
            $header .= '<input type="hidden" name="get_status" id="get_status" value="" />';
            $header .= '<input type="hidden" name="cp_id" id="' . $day . '_id" value="' . $course_period_id . '"/>';
            $header .= '<DIV id=meeting_days>';

            $header .= '<div class="row">';
            $header .= '<div class="col-md-6"><div class="form-group"><label class="control-label text-right col-md-4">' . _room . '</label><div class="col-md-8">' . SelectInput($RET['ROOM_ID'], 'tables[course_period_var][' . $_REQUEST['course_period_id'] . '][ROOM_ID]', '', $rooms, 'N/A', 'id=' . $day . '_room ' . $disable, $div) . '</div></div></div>';
            $header .= '<div class="col-md-6"><div class="form-group"><label class="control-label text-right col-md-4">' . _period . '</label><div class="col-md-8">' . SelectInput($RET['PERIOD_ID'], 'tables[course_period_var][' . $_REQUEST['course_period_id'] . '][PERIOD_ID]', '', $periods, 'N/A', 'id=' . $day . '_period onClick="disable_hidden_field(' . (($day != '') ? 2 : 1) . ');"' . $disable, $div) . '</div></div></div>';
            $header .= '</div>'; //.row

            $header .= '<input type=hidden id="' . $day . '_period" value="' . $RET['PERIOD_ID'] . '" name=fixed_hidden>';
            $header .= '<input type=hidden id="fixed_tag_name" value="' . 'tables[course_period_var][' . $_REQUEST['course_period_id'] . '][PERIOD_ID]' . '">';

            $header .= '<div class="row">';
            $header .= '<div class="col-md-6"><div class="form-group"><label class="control-label text-right col-md-4">' . _meetingDays . '</label><div class="col-md-8">';
            if ($not_pass != true)
                $header .= '<DIV id=days><div class="form-control" onclick=\'addHTML("';
            foreach ($caldays as $day => $short_day) {
                if (strpos($RET['DAYS'], $day) !== false)
                    $value = 'Y';
                else
                    $value = '';

                $header .= CheckboxInput($value, 'tables[course_period_var][' . $_REQUEST['course_period_id'] . '][DAYS][' . $day . ']', ($day == 'U' ? 'U' : $day), $checked, true, '', '', false);
            }
            if ($not_pass != true)
                $header .= '","days",true);\'>' . $RET['DAYS'] . '</div></DIV>';
            $header .= '</div></div></div>';
            $header .= '<div class="col-md-6"><div class="form-group"><label class="col-md-4">&nbsp;</label><div class="col-md-8">' . CheckboxInputSwitch($RET['DOES_ATTENDANCE'], 'tables[course_period_var][' . $_REQUEST['course_period_id'] . '][DOES_ATTENDANCE]', _takesAttendance, $checked, $new, 'Yes', 'No', ' id=' . $day . '_does_attendance onclick="formcheck_periods_attendance_F2(' . (($day != '') ? 2 : 1) . ',this);"', 'switch-success') . '<div id="ajax_output"></div></div></div></div>';
            $header .= '</div>'; //.row
            echo '<input type="hidden" name="fixed_day" id="fixed_day" value="' . $day . '" />';

            $header .= '</div>'; //.panel-body
        } elseif ($RET['SCHEDULE_TYPE'] == 'BLOCKED') {
            $calendar_RET = DBGet(DBQuery('SELECT DATE_FORMAT(SCHOOL_DATE,\'%d-%b-%y\') as SCHOOL_DATE,MINUTES,BLOCK FROM attendance_calendar WHERE  SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND CALENDAR_ID=\'' . $RET['CALENDAR_ID'] . '\''), array(), array('SCHOOL_DATE'));

            if ($RET['MARKING_PERIOD_ID'] != '') {
                $mp_RET = DBGet(DBQuery("SELECT START_DATE,END_DATE FROM marking_periods WHERE marking_period_id=$RET[MARKING_PERIOD_ID]"));
                $mp_RET = $mp_RET[1];
                $begin = $mp_RET['START_DATE'];
                $end = $mp_RET['END_DATE'];
            } elseif ($RET['BEGIN_DATE'] != '' && $RET['END_DATE'] != '') {
                $begin = $RET['BEGIN_DATE'];
                $end = $RET['END_DATE'];
            }
            $header .= '<input type=hidden name=tables[course_periods][' . $_REQUEST['course_period_id'] . '][SCHEDULE_TYPE] value=BLOCKED />';
            //$header .='<TD colspan=6>';
            $header .= '<hr class="m-b-0"/><div class="m-t-10 m-b-15 text-center">' . _makeMonths('Modules.php?modname=' . $_REQUEST['modname'] . '&subject_id=' . $_REQUEST['subject_id'] . '&course_id=' . $_REQUEST['course_id'] . '&course_period_id=' . $_REQUEST['course_period_id'] . '&month=', $begin, $end) . '</div>';
            $header .= "<DIV id=meeting_days class=\"table-responsive\">";
            //$header .="<TABLE border=0 cellpadding=0 cellspacing=0 class=pixel_border><TR><TD>";
            $header .= "<table class=\"table table-bordered table-condensed\"><thead><tr align=center>";
            $header .= "<TD class=white>" . _sunday . "</TD><TD class=white>" . _monday . "</TD><TD class=white>" . _tuesday . "</TD><TD class=white>" . _wednesday . "</TD><TD class=white>" . _thursday . "</TD><TD class=white>" . _friday . "</TD><TD width=99 class=white>" . _saturday . "</TD>";
            $header .= "</tr></thead><tbody><TR>";

            $month = date('m', $_REQUEST['month']);
            $year = date('Y', $_REQUEST['month']);
            $time = mktime(0, 0, 0, $month, 1, $year);
            $last = 31;
            while (!checkdate($month, $last, $year))
                $last--;
            $skip = date("w", $time);

            if ($skip) {
                $header .= "<td colspan=" . $skip . "></td>";
                $return_counter = $skip;
            }

            for ($i = 1; $i <= $last; $i++) {
                $day_time = mktime(0, 0, 0, $month, $i, $year);
                $date = date('Y-m-d', $day_time);
                $date1 = date('d-M-y', $day_time);

                $header .= "<TD  title='" . ProperDate($date) . "' width=\"14.2%\" class=" . ($periods[$cpblocked_RET[$date][1]['PERIOD_ID']] || $calendar_RET[$date1][1]['MINUTES'] == '999' ? 'calendar_active' : 'calendar_holiday') . " valign=top>
                                <table width=100%><tr><td width=5 valign=top>$i</td><td width=95 align=right></TD></TR>";
                $header .= "</td></tr><tr><TD colspan=2 height=40 valign=top>";

                if (in_array(date('D', $day_time), $caldays) && $date >= $begin && $date <= $end) {

                    $block_periods = DBGet(DBQuery("SELECT * FROM course_period_var WHERE course_period_id='" . $_REQUEST['course_period_id'] . "'                                    
                                                              AND course_period_date='" . $date . "'"));
                    $header .= '<p style="font-size: 70%;"><b>' . $periods[$cpblocked_RET[$date][1]['PERIOD_ID']] . '</b></p>';
                    $header .= '<p style="font-size: 70%;">' . $rooms[$cpblocked_RET[$date][1]['ROOM_ID']] . '</p>';

                    if ($cpblocked_RET[$date][1]['PERIOD_ID'] == '' && AllowEdit()) {


                        if ($calendar_RET[$date1][1]['MINUTES'] == '999') {
                            //$header .= '<tr><td valign=bottom align=left>' . button('add', '', "# onclick='javascript:window.open(\"ForWindow.php?modname=$_REQUEST[modname]&modfunc=detail&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=$_REQUEST[course_period_id]&mode=add&calendar_id=$_REQUEST[calendar_id]&meet_date=$date\",\"blank\",\"width=600,height=400\"); return false;'") . "</td></tr>";
                            $header .= '<tr><td valign=bottom align=left> <button type="button" class="btn btn-primary btn-icon btn-xs" data-toggle="modal" onclick="BlockModalPeriod(\'' . $_REQUEST['subject_id'] . '\',' . $_REQUEST['course_id'] . ',\'' . $_REQUEST['course_period_id'] . '\',\'' . $_REQUEST['calendar_id'] . '\',\'' . $date . '\',\'add\'); return false;"><i class=icon-plus3 ></i></button></td></tr>';
                        }
                    } else {
                        foreach ($block_periods as $ind => $data) {
                            $header .= '<table><tr><td style="font-size: 70%;">' . _attendance . ' : ' . ($data['DOES_ATTENDANCE'] == 'Y' ? 'Yes' : 'No') . '</td></tr>';
                            if (AllowEdit()) {
                                // $header .= '<tr><td valign=bottom align=left>' . button('edit', '', "# onclick='javascript:window.open(\"ForWindow.php?modname=$_REQUEST[modname]&modfunc=detail&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=$_REQUEST[course_period_id]&mode=edit&calendar_id=$_REQUEST[calendar_id]&id=$data[ID]&meet_date=$date\",\"blank\",\"width=600,height=400\"); return false;'") . "</td></tr></table>";

                                $header .= '<tr><td valign=bottom align=left> <button type="button" class="btn btn-primary btn-icon btn-xs" data-toggle="modal" onclick="BlockModalPeriod(\'' . $_REQUEST['subject_id'] . '\',' . $_REQUEST['course_id'] . ',\'' . $_REQUEST['course_period_id'] . '\',\'' . $_REQUEST['calendar_id'] . '\',\'' . $date . '\',\'edit\',\'' . $data[ID] . '\'); return false;"><i class=icon-pencil4 ></i></button></td></tr></table>';
                            } else
                                $header .= '</table>';
                        }
                        if (AllowEdit())
                            // $header .= '<tr><td valign=bottom align=left>' . button('add', '', "# onclick='javascript:window.open(\"ForWindow.php?modname=$_REQUEST[modname]&modfunc=detail&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=$_REQUEST[course_period_id]&mode=add&calendar_id=$_REQUEST[calendar_id]&meet_date=$date&add=new\",\"blank\",\"width=600,height=400\"); return false;'") . "</td></tr>";
                            $header .= '<tr><td valign=bottom align=left> <button type="button" class="btn btn-primary btn-icon btn-xs" data-toggle="modal" onclick="BlockModalPeriod(\'' . $_REQUEST['subject_id'] . '\',' . $_REQUEST['course_id'] . ',\'' . $_REQUEST['course_period_id'] . '\',\'' . $_REQUEST['calendar_id'] . '\',\'' . $date . '\',\'add\',\'new\'); return false;"><i class=icon-plus3 ></i></button></td></tr>';
                    }
                }
                $header .= "</td></tr>";
                $header .= "</table></TD>";
                $return_counter++;

                if ($return_counter % 7 == 0)
                    $header .= "</TR><TR>";
            }
            $header .= "</tr></tbody></table>";

            $header .= "</TD></TR></TABLE>";
            $header .= "</DIV>";
            //$header .='</TD>';
        }

        $header .= '</div>';
        $header .= '</div>'; //.well
        $header .= '</div>'; //.clearfix

        DrawHeaderHome($header);
        if (UserProfileID() != 2) {
            echo '<hr class="no-margin">';
            echo '<div class="clearfix p-20">
            <h6 class="pull-left m-b-0"><b>' . $title . '</b></h6>
            <div class="pull-right">' . $delete_button . SubmitButton(_save, '', 'id=save_cps class="btn btn-primary m-l-5" onclick="return validate_course_period();"') . '</div>
            </div>';
        }
        echo '</div>'; //.panel-body
        echo '</FORM>';
        echo '</div>'; //.panel.panel-default
    } elseif (clean_param($_REQUEST['course_id'], PARAM_ALPHANUM)) {
        $grade_level_RET = DBGet(DBQuery("SELECT ID,TITLE FROM school_gradelevels WHERE school_id='" . UserSchool() . "'"));
        if ($_REQUEST['course_id'] != 'new') {
            $sql = "SELECT c.TITLE,sg.TITLE AS GRADE_LEVEL_TITLE,c.SHORT_NAME,GRADE_LEVEL
                        FROM courses c LEFT JOIN school_gradelevels sg ON c.grade_level=sg.id
                        WHERE COURSE_ID='$_REQUEST[course_id]'";
            $QI = DBQuery($sql);
            $RET = DBGet($QI);
            $RET = $RET[1];
            $title = trim($RET['TITLE'] . ' - ' . $RET['GRADE_LEVEL_TITLE'], '- ');
        } else {
            $sql = "SELECT TITLE
                        FROM course_subjects
                        WHERE SUBJECT_ID='$_REQUEST[subject_id]' ORDER BY TITLE";
            $QI = DBQuery($sql);
            $RET = DBGet($QI);
            $title = $RET[1]['TITLE'] . ' - ' . _newCourse . '';
            unset($delete_button);
            unset($RET);
        }

        echo '<div class="panel panel-default">';
        echo "<FORM name=F3 id=F3 class=form-horizontal action=Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id] method=POST>";
        echo '<div class="panel-heading">
                <h6 class="panel-title">' . $title . '</h6>
                <div class="heading-elements">' . $delete_button . SubmitButton(_save, '', 'id="setupCourseBtn" class="btn btn-primary m-l-5" onclick="return formcheck_Timetable_course_F3(this);"') . '</div>
            </div>';
        echo '<hr class="no-margin"/>';
        echo '<div class="panel-body">';
        foreach ($grade_level_RET as $grade_level)
            $grade_levels[$grade_level['ID']] = $grade_level['TITLE'];
        $header .= '<div class="row">';
        $header .= '<div class="col-lg-4">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _title . ' :</label><div class="col-md-8">' . TextInput($RET['TITLE'], 'tables[courses][' . $_REQUEST['course_id'] . '][TITLE]', '', 'id=course_title class=cell_mod_wide') . '</div></div>';
        $header .= '</div>'; //.col-md-8
        $header .= '<div class="col-lg-4">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _shortName . ' :</label><div class="col-md-8">' . TextInput($RET['SHORT_NAME'], 'tables[courses][' . $_REQUEST['course_id'] . '][SHORT_NAME]', '', 'id=short_name class=form-control') . '</div></div>';
        $header .= '</div>'; //.col-md-4
        $header .= '</div>'; //.row

        $header .= '<div class="row">';
        $header .= '<div class="col-lg-4">';
        $header .= "<input type=hidden value=" . $_REQUEST['course_id'] . " id=course_id_div />";
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _gradeLevel . ' :</label><div class="col-md-8">' . SelectInput($RET['GRADE_LEVEL'], 'tables[courses][' . $_REQUEST['course_id'] . '][GRADE_LEVEL]', '', $grade_levels) . '</div></div>';
        $header .= '</div>'; //.col-md-4
        $header .= '<div class="col-lg-4">';
        foreach ($subjects_RET as $type)
            $options[$type['SUBJECT_ID']] = $type['TITLE'];

        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _subject . ' :</label><div class="col-md-8">' . SelectInput($RET['SUBJECT_ID'] ? $RET['SUBJECT_ID'] : $_REQUEST['subject_id'], 'tables[courses][' . $_REQUEST['course_id'] . '][SUBJECT_ID]', '', $options, false) . '</div></div>';
        $header .= '</div>'; //.col-md-4
        $header .= '</div>'; //.row

        // $header .= '<div class="row">';
        // $header .= '<div class="col-lg-8">';
        // $header .= '<div class="form-group"><label class="col-md-2 control-label text-right">Description :</label><div class="col-md-9">' . TextareaInput($RET['DESCRIPTION'], 'tables[courses][' . $_REQUEST['course_id'] . '][DESCRIPTION]', '', 'size=44 class=form-control') . '</div></div>';
        // $header .= '</div>'; //.col-md-4
        // $header .= '</div>'; //.row

        DrawHeaderHome($header);
        echo '</div>'; //.panel-body
        echo '</FORM>';
        echo '</div>'; //.panel.panel-default
    } elseif (clean_param($_REQUEST['subject_id'], PARAM_ALPHANUM)) {
        if ($_REQUEST['subject_id'] != 'new') {
            $sql = "SELECT TITLE
                        FROM course_subjects
                        WHERE SUBJECT_ID='$_REQUEST[subject_id]'";
            $QI = DBQuery($sql);
            $RET = DBGet($QI);
            $RET = $RET[1];
            $title = $RET['TITLE'];
        } else {
            $title = _newSubject;
            unset($delete_button);
        }

        echo '<div class="panel panel-default">';
        echo "<FORM name=F4 id=F4 class=form-horizontal action=Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id] method=POST>";
        echo '<div class="panel-heading">
                <h6 class="panel-title">' . $title . '</h6>
                <div class="heading-elements">' . $delete_button . SubmitButton(_save, '', 'id="setupSubjectBtn" class="btn btn-primary m-l-5" onclick="formcheck_Timetable_course_F4(this);"') . '</div>
            </div>';
        echo '<hr class="no-margin"/>';

        echo '<div class="panel-body">';
        $header .= '<div class="form-group">';
        $header .= '<label class="col-md-1 control-label text-right">' . _title . ':</label>';
        $header .= "<input type=hidden value=" . $_REQUEST['subject_id'] . " id=subject_id_div />";
        $header .= '<div class="col-md-6">' . TextInput($RET['TITLE'], 'tables[course_subjects][' . $_REQUEST['subject_id'] . '][TITLE]') . '</div>';

        $header .= '</div>';
        DrawHeader($header);
        echo '</div>'; //.panel-body
        echo '</FORM>';
        echo '</div>'; //.panel
    }


    // DISPLAY THE MENU
    $LO_options = array('save' => false, 'search' => false);

    if (!$_REQUEST['subject_id']) {
        echo '<div class="panel panel-default">';
        echo "<FORM name=F1 id=F1 action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&course_modfunc=search method=POST>";
        DrawHeader(_courses, '<div class="form-group"><div class="input-group"><INPUT placeholder="' . _searchCourse . '" type=text class=form-control name=search_term value="' . $_REQUEST['search_term'] . '"><span class="input-group-btn"><INPUT type=submit class="btn btn-primary" value=' . _search . ' onclick=\'formload_ajax("F1");\'></span></div></div>');
        echo '</FORM>';
        echo '</div>';
    }


    if (count($subjects_RET)) {
        if (clean_param($_REQUEST['subject_id'], PARAM_ALPHANUM)) {
            foreach ($subjects_RET as $key => $value) {
                if ($value['SUBJECT_ID'] == $_REQUEST['subject_id'])
                    $subjects_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
            }
        }
    }

    echo '<div class="row">';

    $columns = array('TITLE' => _subject);
    $link = array();
    $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]";
    $link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID');
    $link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&subject_id=new";
    echo '<div class="col-md-4">';
    echo '<div class="panel panel-white">';
    ListOutput($subjects_RET, $columns,  _subject, _subjects, $link, array(), $LO_options);
    echo '</div>'; // .panel
    echo '</div>'; // .col-md-4

    if (clean_param($_REQUEST['subject_id'], PARAM_ALPHANUM) && $_REQUEST['subject_id'] != 'new') {
        $sql = "SELECT COURSE_ID,c.TITLE, CONCAT_WS(' - ',c.short_name,c.title) AS GRADE_COURSE FROM courses c LEFT JOIN school_gradelevels sg ON c.grade_level=sg.id WHERE SUBJECT_ID='$_REQUEST[subject_id]' ORDER BY CAST(c.TITLE AS unsigned)";
        $QI = DBQuery($sql);
        $courses_RET = DBGet($QI);

        if (count($courses_RET)) {
            if (clean_param($_REQUEST['course_id'], PARAM_ALPHANUM)) {
                foreach ($courses_RET as $key => $value) {
                    if ($value['COURSE_ID'] == $_REQUEST['course_id'])
                        $courses_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
                }
            }
        }

        $columns = array('GRADE_COURSE' => _course);
        $link = array();
        $link['GRADE_COURSE']['link'] = "Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]";

        $link['GRADE_COURSE']['variables'] = array('course_id' => 'COURSE_ID');
        $link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=new";

        echo '<div class="col-md-4">';
        echo '<div class="panel panel-white">';
        ListOutput($courses_RET, $columns, _course, _courses, $link, array(), $LO_options);
        echo '</div>'; // .panel
        echo '</div>'; // .col-md-4

        if (clean_param($_REQUEST['course_id'], PARAM_ALPHANUM) && $_REQUEST['course_id'] != 'new') {
            $sql = "SELECT COURSE_PERIOD_ID,TITLE,COALESCE(TOTAL_SEATS-FILLED_SEATS,0) AS AVAILABLE_SEATS,BEGIN_DATE,END_DATE,MARKING_PERIOD_ID FROM course_periods WHERE COURSE_ID='$_REQUEST[course_id]' AND (marking_period_id IN(" . GetAllMP(GetMPTable(GetMP(UserMP(), 'TABLE')), UserMP()) . ") OR (MARKING_PERIOD_ID IS NULL)) ORDER BY TITLE";

            $QI = DBQuery($sql);
            $periods_RET = DBGet($QI);

            $count_CP = 1;
            $cp_RET = array();

            foreach ($periods_RET as $one_CP_k => $one_CP_v) {
                if ($one_CP_v['MARKING_PERIOD_ID'] == '') {
                    $mp_DET = DBGet(DBQuery('SELECT GROUP_CONCAT(`marking_period_id`) AS `mp_ids` FROM `marking_periods` WHERE `start_date` >= (SELECT MAX(`start_date`) AS `start_date` FROM `marking_periods` WHERE \'' . $one_CP_v['BEGIN_DATE'] . '\' BETWEEN `start_date` AND `end_date` AND `syear` = \'' . UserSyear() . '\' AND `school_id`=\'' . UserSchool() . '\') AND `end_date` <= (SELECT MIN(`end_date`) AS `end_date` FROM `marking_periods` WHERE \'' . $one_CP_v['END_DATE'] . '\' BETWEEN `start_date` AND `end_date` AND `syear` = \'' . UserSyear() . '\' AND `school_id`=\'' . UserSchool() . '\')'));
                    $mp_DET_combined = explode(",", $mp_DET[1]['MP_IDS']);

                    if (in_array(UserMP(), $mp_DET_combined)) {
                        $cp_RET[$count_CP] = $one_CP_v;
                        $count_CP++;
                    }
                } else {
                    $cp_RET[$count_CP] = $one_CP_v;
                    $count_CP++;
                }
            }

            ## Overwriting the previous periods return with the filtered one
            unset($periods_RET);
            $periods_RET = $cp_RET;

            if (count($periods_RET)) {
                if (clean_param($_REQUEST['course_period_id'], PARAM_ALPHANUM)) {
                    foreach ($periods_RET as $key => $value) {
                        if ($value['COURSE_PERIOD_ID'] == $_REQUEST['course_period_id'])
                            $periods_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
                    }
                }
            }

            $columns = array('TITLE' => _coursePeriod);
            if ($_REQUEST['modname'] == 'Schdeuling/Schedule.php')
                $columns += array('AVAILABLE_SEATS' => 'Available Seats');
            $link = array();
            $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]";
            $link['TITLE']['variables'] = array('course_period_id' => 'COURSE_PERIOD_ID');
            $link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=new";
            echo '<div class="col-md-4">';
            echo '<div class="panel panel-white">';
            ListOutput($periods_RET, $columns,  _coursePeriod, _coursePeriods, $link, array(), $LO_options);
            echo '</div>'; // .panel
            echo '</div>'; // .col-md-4
        }
    }

    unset($_REQUEST['conflict']);
    echo '</div>'; //.row
}

echo '<div id="modal_default_block_cp" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">';
echo '<div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">×</button>
                        </div>';

echo '<div class="modal-body">';

echo '<div id="modal-res">';

echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
if (clean_param($_REQUEST['modname'], PARAM_ALPHAEXT) == 'Schedule/Courses.php' && clean_param($_REQUEST['course_period_id'], PARAM_ALPHANUM)) {
    $course_title = DBGet(DBQuery("SELECT TITLE FROM course_periods WHERE COURSE_PERIOD_ID='" . $_REQUEST['course_period_id'] . "'"));
    $course_title = $course_title[1]['TITLE'] . '<INPUT type=hidden name=tables[parent_id] value=' . $_REQUEST['course_period_id'] . '>';
    echo "<script language=javascript>opener.document.getElementById(\"course_div\").innerHTML = \"$course_title</small>\"; window.close();</script>";
}





echo '<div id="modal_default" class="modal fade">';
echo '<div class="modal-dialog modal-lg">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">' . _chooseCourse . '</h5>';
echo '</div>';

echo '<div class="modal-body">';
echo '<div id="conf_div" class="text-center"></div>';
echo '<div class="row" id="resp_table">';
echo '<div class="col-md-4">';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? '' . _subjectWas . '' : '' . _subjectsWere . '') . ' ' . _found . '.</h6>';
if (count($subjects_RET) > 0) {
    echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>' . _subject . '</th></tr></thead><tbody>';
    foreach ($subjects_RET as $val) {
        echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
    }
    echo '</tbody></table>';
}
echo '</div>';
echo '<div class="col-md-4"><div id="course_modal"></div></div>';
echo '<div class="col-md-4"><div id="cp_modal"></div></div>';
echo '</div>'; //.row
echo '</div>'; //.modal-body

echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal

if ($_REQUEST['course_period_id'] == 'new') {
    if ($_REQUEST['tables']['course_periods']['new']['SCHEDULE_TYPE'] == 'fixed' || $_REQUEST['tables']['course_periods']['new']['SCHEDULE_TYPE'] == 'FIXED') {
        echo "<script>show_cp_meeting_days('fixed','new');</script>";
    }
    if ($_REQUEST['tables']['course_periods']['new']['SCHEDULE_TYPE'] == 'variable' || $_REQUEST['tables']['course_periods']['new']['SCHEDULE_TYPE'] == 'VARIABLE') {
        echo "<script>show_cp_meeting_days('variable','new');</script>";
    }
    if ($_REQUEST['tables']['course_periods']['new']['SCHEDULE_TYPE'] == 'blocked' || $_REQUEST['tables']['course_periods']['new']['SCHEDULE_TYPE'] == 'BLOCKED') {
        echo "<script>show_cp_meeting_days('blocked','new');</script>";
    }
}

function conv_day($short_date, $type = '')
{
    $days = array('U' => 'Sun', 'M' => 'Mon', 'T' => 'Tue', 'W' => 'Wed', 'H' => 'Thu', 'F' => 'Fri', 'S' => 'Sat');
    if ($type == 'key')
        return array_search($short_date, $days);
    else
        return $days[$short_date];
}

function disabled()
{
    if ($_REQUEST['course_period_id'] != 'new')
        return 'disabled';
}

function _makeMonths($link, $begin_date, $end_date)
{
    $begin_date = strtotime($begin_date);
    $end_date = strtotime($end_date);
    $one_day = 86400;
    if (!$_REQUEST['month']) {
        $_REQUEST['month'] = date($begin_date);
    }
    $days = date('t', $_REQUEST['month']);

    if (date('j', $_REQUEST['month']) == date('t', $_REQUEST['month'])) {
        $_REQUEST['month'] = strtotime('first day of next month', $_REQUEST['month']);
    }
    $last_day_end = date('t', $end_date);
    $begin = strtotime(date('Y-m-01', $begin_date)) . "<br>";
    $end = strtotime(date('Y-m-' . $last_day_end, $end_date));
    $prev = $_REQUEST['month'] - $one_day * 30;
    $next = $_REQUEST['month'] + $one_day * $days;
    $prev_month_f = strtotime(date('Y-m-d', $next));
    $prev_month_f = strtotime('Previous Month', $prev_month_f);
    $html = '<ul class="pagination pagination-flat">';
    if ($link != '') {
        if ($prev >= $begin) {
            $prev_month_f = strtotime('Previous Month', $prev_month_f);
            $prev = $prev_month_f;
            $html .= "<li><a href='javascript:void(0);' title='Previous' onclick=\"window.location='" . $link . $prev . "';\"><i class=\"icon-arrow-left12\"></i> " . _prev . "</a></li>";
        }
        $html .= "<li class=\"active\"><a href=\"javascript:void(0);\">" . date('F', $_REQUEST['month']) . "&nbsp;" . date('Y', $_REQUEST['month']) . "</a></li>";
        if ($next <= $end)
            $html .= "<li><a href='javascript:void(0);' title='Next' onclick=\"window.location='" . $link . $next . "';\">" . _next . " <i class=\"icon-arrow-right13\"></i> </a></li>";
    }
    $html .= '</ul>';

    return $html;
}

function makeTextInput($value, $name)
{
    global $THIS_RET;
    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($name != 'TITLE')
        $extra = 'size=5 maxlength=5 class=cell_small';
    else
        $extra = 'class=cell_wide ';


    return $comment . TextInput($value, 'values[' . $id . '][' . $name . ']', '', $extra);
}

function _makeChooseCheckbox($value, $title)
{
    return "<INPUT type=checkbox name=stand_arr[] value=$value checked>";
}
