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
include('../../RedirectModulesInc.php');
include('lang/language.php');

if (isset($_SESSION['language']) && $_SESSION['language'] == 'fr'){
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

if ($_REQUEST['create_pdf'] == 'true') {
    $handle = PDFStart();
    echo '<!-- MEDIA SIZE 11x8.5in -->';
    echo '<!-- MEDIA TOP 0.5in -->';
    echo '<!-- MEDIA BOTTOM 0.25in -->';
    echo '<!-- MEDIA LEFT 0.25in -->';
    echo '<!-- MEDIA RIGHT 0.25in -->';
    echo '<!-- FOOTER RIGHT "" -->';
    echo '<!-- FOOTER LEFT "" -->';
    echo '<!-- FOOTER CENTER "" -->';
    echo '<!-- HEADER RIGHT "" -->';
    echo '<!-- HEADER LEFT "" -->';
    echo '<!-- HEADER CENTER "" -->';
    echo CreateList($_REQUEST['degree_level_id'], $_REQUEST['prog_level_id'], $_REQUEST['subject_id'], $_REQUEST['course_id'], $_REQUEST['marking_period_id'], $_REQUEST['mp_name']);
    PDFStop($handle, 'sis.pdf');
    exit();
}
if (clean_param($_REQUEST['create_excel'], PARAM_ALPHAMOD) == 'true') {
    echo CreateExcel($_REQUEST['degree_level_id'], $_REQUEST['prog_level_id'], $_REQUEST['subject_id'], $_REQUEST['course_id'], $_REQUEST['marking_period_id'], $_REQUEST['mp_name']);
}
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'choose_course') {
    DrawBC("Courses -> " . $_REQUEST['draw_header']);

    $sql = 'SELECT cp.PARENT_ID,cp.TITLE,cp.SHORT_NAME,cpv.PERIOD_ID,cpv.DAYS,
                                cp.MP,cp.MARKING_PERIOD_ID,cp.TEACHER_ID,cp.CALENDAR_ID,
                                r.TITLE AS ROOM,cp.TOTAL_SEATS,cpv.DOES_ATTENDANCE,
                                cp.GRADE_SCALE_ID,cp.DOES_HONOR_ROLL,cp.DOES_CLASS_RANK,
                                cp.GENDER_RESTRICTION,cp.HOUSE_RESTRICTION,cp.CREDITS,
                                cp.HALF_DAY,cp.DOES_BREAKOFF
                        FROM course_periods cp,course_period_var cpv,rooms r
                        WHERE COURSE_PERIOD_ID=\'' . $_REQUEST[course_period_id] . '\'
                        AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND r.ROOM_ID=cpv.ROOM_ID';

    $QI = DBQuery($sql);
    $RET = DBGet($QI);
    $RET = $RET[1];
    $title = $RET['TITLE'] . " , <b>" . _course . ":</b> " . $C_RET[1]['TITLE'] . ", <b>" . _numberOfCredits . "</b> " . $C_RET[1]['NUMBER_OF_CREDITS'];
    $new = false;

    if (count($RET)) {
        $header .= '<TABLE class="table table-bordered table-striped">';
        $header .= '<TR>';

        $header .= '<TD><b>' . $RET['SHORT_NAME'] . '</b><br>' . _shortName . '</TD>';

        $teachers_RET = DBGet(DBQuery("SELECT concat((COALESCE(LAST_NAME,' '), ', ', COALESCE(FIRST_NAME,' '), ' ', COALESCE(MIDDLE_NAME,' '))) as Teacher FROM staff WHERE (SCHOOLS IS NULL OR strpos(SCHOOLS,'," . UserSchool() . ",')>0) AND SYEAR='" . UserSyear() . "' AND PROFILE='teacher' and STAFF_ID='" . $RET['TEACHER_ID'] . "' ORDER BY LAST_NAME,FIRST_NAME"));

        $header .= '<TD><b>' . $teachers_RET[1]['TEACHER'] . '</b><br>Teacher</TD>';
        $header .= '<TD><b>' . $RET['ROOM'] . '</b><br>' . _location . '</TD>';
        $sql = "SELECT TITLE,START_TIME,END_TIME FROM school_periods WHERE PERIOD_ID='" . $RET['PERIOD_ID'] . "'";
        $periods_RET = DBGet(DBQuery($sql));
        $header .= '<TD><b>' . $periods_RET[1]['TITLE'] . '</b><br>' . _period . '</TD>';
        $header .= '<TD><b>' . $RET['DAYS'] . '</b><br>' . _days . '</td>';
        $header .= '</TR><TR>';
        $mp = $RET['MARKING_PERIOD_ID'];
        $mp_RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,'2' AS TABLE,SORT_ORDER FROM school_quarters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' and marking_period_id='" . $mp . "' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,'1' AS TABLE,SORT_ORDER FROM school_semesters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' and marking_period_id='" . $mp . "' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,'0' AS TABLE,SORT_ORDER FROM school_years WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' and marking_period_id='" . $mp . "' ORDER BY 3,4"));


        $header .= '<TD><b>' . $mp_RET[1]['TITLE'] . '</b><br>' . _markingPeriod . '</TD>';
        $header .= '<TD><b>' . $RET['TOTAL_SEATS'] . '</b><br>' . _totalSeats . '</TD>';

        $header .= '<TD><b>' . gr($RET['GENDER_RESTRICTION']) . '</b><br>' . _genderRestriction . '' . '</TD>';

        if ($RET['GRADE_SCALE_ID'] != '') {
            $sql = "SELECT TITLE,ID FROM report_card_grade_scales WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' and ID='" . $RET['GRADE_SCALE_ID'] . "'";
            $options_RET = DBGet(DBQuery($sql));
            $header .= '<TD><b>' . $options_RET[1]['TITLE'] . '</b><br>' . _gradingScale . '</TD>';
        } else
            $header .= '<TD><b>' . '' . _notGraded . '' . '</b><br>' . _gradingScale . '</TD>';

        if ($RET['CALENDAR_ID'] != '') {
            $sql = "SELECT TITLE,CALENDAR_ID FROM school_calendars WHERE CALENDAR_ID='" . $RET['CALENDAR_ID'] . "'";

            $options_RET = DBGet(DBQuery($sql));
            $header .= '<TD><b>' . $options_RET[1]['TITLE'] . '</b><br>' . _calendar . '' . '</TD>';
        } else
            $header .= '<TD><b>' . '' . _noCalendarSelected . '' . '</b><br>' . _noCalendarSelected . '' . '</TD>';
        $header .= "</TR><TR>";
        $header .= '<TD><b>' . cbr($RET['DOES_ATTENDANCE']) . '</b><br>' . _takesAttendance . '' . '</TD>';
        $header .= '<TD><b>' . cbr($RET['DOES_HONOR_ROLL']) . '</b><br>' . _affectsHonorRoll . '' . '</TD>';
        $header .= '<TD><b>' . cbr($RET['DOES_CLASS_RANK']) . '</b><br>' . _affectsClassRank . '' . '</TD>';
        $header .= '<TD><b>' . cbr($RET['HALF_DAY']) . '</b><br>' . _halfDay . '' . '</TD>';
        $header .= '<TD><b>' . cbr($RET['DOES_BREAKOFF']) . '</b><br>' . _allowTeacherGradescale . '' . '</TD>';
        $header .= "</TR><TR>";

        if ($RET['PARENT_ID'] != '') {
            $sql = 'SELECT TITLE,COURSE_PERIOD_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $RET['PARENT_ID'] . '\'';

            $children = DBGet(DBQuery($sql));
            if (count($children))
                $header .= '<TD colspan=2><b>' . $children[1]['TITLE'] . '</b><br>' . _parentCourse . '' . '</TD>';
            else
                $header .= '<TD><b>' . '' . _noParentCourseSelected . '' . '</b><br>' . _noParentCourseSelected . '' . '</TD>';
        } else
            $header .= '<TD><b>' . '' . _noParentCourseSelected . '' . '</b><br>' . _noParentCourseSelected . '' . '</TD>';
        $header .= '<TD><b>' . $periods_RET[1]['START_TIME'] . '</b><br>' . _startTime . '' . '</TD>';
        $header .= '<TD><b>' . $periods_RET[1]['END_TIME'] . '</b><br>' . _endTime . '' . '</TD><td></td>';
        $header .= '</TR>';
        $header .= '</TABLE>';
        DrawHeaderHome($header);
    }
    echo "</div></div></div><div class='tab_footer'>";
} else {
    unset($_SESSION['_REQUEST_vars']['subject_id']);
    unset($_SESSION['_REQUEST_vars']['course_id']);
    unset($_SESSION['_REQUEST_vars']['course_weight']);
    unset($_SESSION['_REQUEST_vars']['course_period_id']);

    DrawBC("Courses > " . ProgramTitle());
    if ($_REQUEST['print'] != 'list') {
        //echo PopTable('header', 'Quick Search');
    }

    if ($_REQUEST['print'] != 'list') {
        echo "<FORM name=search  class=form-horizontal id=search action=ForExport.php?modname=$_REQUEST[modname]&modfunc=print&marking_period_id=" . $_REQUEST['marking_period_id'] . "&_openSIS_PDF=true&report=true&print=list method=POST target=_blank>";
        echo '<div class="panel panel-default">';
        echo '<div class="panel-heading">
                <h6 class="panel-title">' . _courseCatalog . '</h6>
                <div class="heading-elements">
                    <INPUT type=submit class="btn btn-primary" value=\'' . _print . '\'>
                </div>
            </div>';
        echo '<div class="panel-body">';
        echo '<div class="row">';
        echo '<div class="col-md-4">';
    }
    $mp_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'2\'  FROM school_quarters WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'1\' FROM school_semesters WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'0\' FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY 3,4'));
    unset($options);
    if (count($mp_RET)) {
        foreach ($mp_RET as $key => $value) {
            if ($value['MARKING_PERIOD_ID'] == $_REQUEST['marking_period_id'])
                $mp_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
        }

        if ($_REQUEST['print'] != 'list')
            echo CreateSelect($mp_RET, 'marking_period_id', '' . _all . '', '' . _selectMarkingPeriod . '', 'Modules.php?modname=' . $_REQUEST['modname'] . '&marking_period_id=');
    }
    if ($_REQUEST['print'] != 'list') {
        echo '</div>';
    }
    if (count($mp_RET)) {


        $columns = array('TITLE' => _markingPeriods);
        $link = array();
        $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]";
        $link['TITLE']['variables'] = array('marking_period_id' => 'MARKING_PERIOD_ID', 'mp_name' => 'SHORT_NAME');
        $link['TITLE']['link'] .= "&modfunc=$_REQUEST[modfunc]";
        //        if ($_REQUEST['print'] != 'list')
        //            echo '<div class="col-md-4">'.CreateSelect($mp_RET, 'marking_period_id', 'All', 'Select Marking Period: ', 'Modules.php?modname=' . $_REQUEST['modname'] . '&marking_period_id=').'</div>';
        //echo '</TD>';

        if ($_REQUEST['marking_period_id'] && $_REQUEST['marking_period_id'] != '') {
            $sql = 'SELECT subject_id,TITLE FROM course_subjects WHERE SCHOOL_ID=\'' . UserSchool() . '\' and syear=\'' . UserSyear() . '\' ORDER BY TITLE';
            $QI = DBQuery($sql);
            $subjects_RET = DBGet($QI);

            if (count($subjects_RET)) {
                if ($_REQUEST['subject_id']) {
                    foreach ($subjects_RET as $key => $value) {
                        if ($value['SUBJECT_ID'] == $_REQUEST['subject_id'])
                            $subjects_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
                    }
                }


                echo '<div class="col-md-4 text-left">';
                $columns = array('TITLE' => _subject);
                $link = array();
                $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&marking_period_id=$_REQUEST[marking_period_id]&mp_name=$_REQUEST[mp_name]";
                $link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID');
                $link['TITLE']['link'] .= "&modfunc=$_REQUEST[modfunc]";
                if ($_REQUEST['print'] != 'list')
                    echo CreateSelect($subjects_RET, 'subject_id', '' . _all . '', '' . strtoupper(_selectSubject) . '', 'Modules.php?modname=' . $_REQUEST['modname'] . '&marking_period_id=' . $_REQUEST['marking_period_id'] . '&subject_id=');
                echo '</div>';

                //For Courses
                if ($_REQUEST['subject_id'] && $_REQUEST['subject_id'] != '') {

                    $sql = 'SELECT COURSE_ID,TITLE FROM courses WHERE SUBJECT_ID=\'' . $_REQUEST['subject_id'] . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY TITLE';
                    $QI = DBQuery($sql);
                    $courses_RET = DBGet($QI);

                    if (count($courses_RET)) {
                        if ($_REQUEST['course_id']) {
                            foreach ($courses_RET as $key => $value) {
                                if ($value['COURSE_ID'] == $_REQUEST['course_id'])
                                    $courses_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
                            }
                        }
                        echo '<div class="col-md-4 text-left">';
                        $columns = array('TITLE' => _course);
                        $link = array();
                        $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&marking_period_id=$_REQUEST[marking_period_id]&mp_name=$_REQUEST[mp_name]&subject_id=$_REQUEST[subject_id]";
                        $link['TITLE']['variables'] = array('course_id' => 'COURSE_ID');
                        $link['TITLE']['link'] .= "&modfunc=$_REQUEST[modfunc]";


                        if ($_REQUEST['print'] != 'list')
                            echo CreateSelect($courses_RET, 'course_id', '' . _all . '', '' . strtoupper(_selectCourse) . '', 'Modules.php?modname=' . $_REQUEST['modname'] . '&marking_period_id=' . $_REQUEST['marking_period_id'] . '&subject_id=' . $_REQUEST['subject_id'] . '&course_id=');
                        echo '</div>';
                    } //If subject
                }
            }
        }
    } else
        echo '<div class="alert bg-danger alert-styled-left">' . _noClassListFound . '</div>';
    echo '</div>'; //.row
    echo '</div>'; //.panel-body
    //    if ($_REQUEST['print'] != 'list')
    //        echo '<div class="table-responsive">';
    echo CreateList($_REQUEST['degree_level_id'], $_REQUEST['program_level_id'], $_REQUEST['subject_id'], $_REQUEST['course_id'], $_REQUEST['marking_period_id'], $_REQUEST['mp_name']);
    //echo "<div style=\"page-break-before: always;\"></div>";
    //    if ($_REQUEST['print'] != 'list')
    //        echo "</div>";

    if ($_REQUEST['print'] != 'list') {
        echo '</div>'; //.panel
        //echo PopTable('footer');
    }
    echo '</form>';
}

function CreateList($dli = '', $pli = '', $sli = '', $cli = '', $mp = '', $mp_name = '')
{
    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if ($sli != '')
        $s_ret = DBGet(DBQuery("select title from course_subjects where subject_id='" . $sli . "'"));

    if ($cli != '')
        $c_ret = DBGet(DBQuery("select title from courses where course_id='" . $cli . "'"));

    if ($mp != '') {
        $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'2\'  FROM school_quarters WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' and marking_period_id=\'' . $mp . '\' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'1\' FROM school_semesters WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' and marking_period_id=\'' . $mp . '\' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'0\'  FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' and marking_period_id=\'' . $mp . '\' ORDER BY 3,4';
        $mp_ret1 = DBGet(DBQuery($sql));
        $mp_name = $mp_ret1[1]['TITLE'];
    }

    if ($mp == '') {
        $where = '';
        $heading = "" . _allAvailableClasses . "";
    } else {
        if ($sli == '') {
            $where = 'and marking_period_id=\'' . $mp . '\' and course_id in (select course_id from  courses where subject_id in (select subject_id from course_subjects))';
            $heading = "" . _allAvailableClassesFor . " <font color='black'>" . $mp_name . " -> " . $d_ret[1]['TITLE'] . " -> " . $p_ret[1]['TITLE'] . "</font>";
        } else {
            if ($cli == '') {
                $where = 'and marking_period_id=\'' . $mp . '\' and course_id in (select Course_Id from courses where subject_id = \'' . $_REQUEST['subject_id'] . '\' and School_Id=\'' . UserSchool() . '\')';

                $heading = "" . _allAvailableClassesFor . " <font color='black'>" . $mp_name . " -> " . $d_ret[1]['TITLE'] . " -> " . $p_ret[1]['TITLE'] . " -> " . $s_ret[1]['TITLE'] . "</font>";
            } else {
                $where = 'and marking_period_id=\'' . $mp . '\' and course_id=\'' . $cli . '\'';
                $heading = "" . _allAvailableClassesFor . " <font color='black'>" . $mp_name . " -> " . $d_ret[1]['TITLE'] . " -> " . $p_ret[1]['TITLE'] . " -> " . $s_ret[1]['TITLE'] . " -> " . $c_ret[1]['TITLE'] . "</font>";
            }
        }
    }



    $sql = 'select
    (select title from courses where course_id=course_periods.course_id) as course,
    (select title from course_subjects where subject_id=(select subject_id from courses where course_id=course_periods.course_id)) as subject,
    short_name,(select CONCAT(START_TIME,\' - \',END_TIME,\' \') from school_periods where period_id=course_period_var.period_id) as period_time, (select title from school_periods where period_id=course_period_var.period_id) as period, marking_period_id, course_periods.course_period_id as mp,
    (select CONCAT(LAST_NAME,\' \',FIRST_NAME,\' \') from staff where staff_id=course_periods.teacher_id) as teacher, rooms.title as location,days,course_periods.course_period_id,course_periods.begin_date,course_periods.end_date,course_periods.SCHEDULE_TYPE,course_period_var.ID AS CPV_ID from course_periods,course_period_var,rooms where course_periods.school_id=\'' . UserSchool() . '\' and course_period_var.room_id=rooms.room_id and course_periods.course_period_id=course_period_var.course_period_id and course_periods.syear=\'' . UserSyear() . '\' ' . $where . '  GROUP BY course_period_var.COURSE_PERIOD_ID ORDER BY course_period_var.ID';


    $ret_temp = DBGet(DBQuery($sql), array('MP' => '_makeMarkingPeriod'));
    $ret = array();
    $i = 1;
    $days_arr = array("Monday" => 'M', "Tuesday" => 'T', "Wednesday" => 'W', "Thursday" => 'H', "Friday" => 'F', "Saturday" => 'S', "Sunday" => 'U');

    foreach ($ret_temp as $ri => $rd) {
        $time = explode(' - ', $rd['PERIOD_TIME']);
        $rd['PERIOD_TIME'] = date("g:i A", strtotime($time[0])) . ' - ' . date("g:i A", strtotime($time[1]));
        unset($time);
        if ($rd['SCHEDULE_TYPE'] == 'FIXED') {
            $ret[$i] = $rd;
            $ret[$i]['DAYS'] = _makeDayNames($rd['DAYS']);
        }
        else {
            $get_det = DBGet(DBQuery('SELECT cpv.*,CONCAT(sp.START_TIME,\' - \',sp.END_TIME,\' \') as PERIOD_TIME,sp.TITLE as PERIOD,r.TITLE AS LOCATION FROM course_period_var cpv,school_periods sp,rooms r WHERE cpv.COURSE_PERIOD_ID=' . $rd['COURSE_PERIOD_ID'] . ' AND cpv.PERIOD_ID=sp.PERIOD_ID AND cpv.ROOM_ID=r.ROOM_ID'));
            $ret[$i] = $rd;
            if (count($get_det) > 0) {
                foreach ($get_det as $gi => $gd) {
                    if ($rd['CPV_ID'] != $gd['ID']) {
                        $time = explode(' - ', $gd['PERIOD_TIME']);
                        $gd['PERIOD_TIME'] = date("g:i A", strtotime($time[0])) . ' - ' . date("g:i A", strtotime($time[1]));
                        unset($time);
                        $ret[$i]['PERIOD_TIME'] = $ret[$i]['PERIOD_TIME'] . ' , ' . $gd['PERIOD_TIME'];
                        $ret[$i]['PERIOD'] = $ret[$i]['PERIOD'] . ' , ' . $gd['PERIOD'];
                        $ret[$i]['LOCATION'] = $ret[$i]['LOCATION'] . ' , ' . $gd['LOCATION'];
                        if ($rd['SCHEDULE_TYPE'] == 'VARIABLE')
                            $ret[$i]['DAYS'] = $ret[$i]['DAYS'] . ' , ' . $gd['DAYS'];
                        else
                            $ret[$i]['DAYS'] = $ret[$i]['DAYS'] . ' , ' . $days_arr[$gd['DAYS']];
                    }
                }
                $final_days = explode(' , ', $ret[$i]['DAYS']);
                $final_days = array_unique($final_days);
                $final_days = implode(' , ', $final_days);
                $ret[$i]['DAYS'] = _makeDayNames($final_days);
                $ret[$i]['PERIOD_TIME'] = '<div style="white-space:nowrap;">' . str_replace(', ', '<br/>', $ret[$i]['PERIOD_TIME']) . '</div>';
                $ret[$i]['PERIOD'] = '<div style="white-space:nowrap;">' . str_replace(', ', '<br/>', $ret[$i]['PERIOD']) . '</div>';
                $ret[$i]['LOCATION'] = '<div style="white-space:nowrap;">' . str_replace(', ', '<br/>', $ret[$i]['LOCATION']) . '</div>';
                $ret[$i]['DAYS'] = str_replace(' , ', '', $ret[$i]['DAYS']);
            }
        }

        $i++;
    }
    $html = "<b>" . $heading . "</b><br>
        <A HREF=" . str_replace('Modules.php', 'ForExport.php', $PHP_tmp_SELF) . "&create_excel=true&LO_save=1&_openSIS_PDF=true > <IMG SRC=assets/download.png border=0 vspace=0 hspace=0></A>
        <br>";
    $html .= "<table width=100%  style=\" font-size:12px;\" >";
    $html .= "<tr><td  style=\"font-size:15px; font-weight:bold;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">" . _studentSchedulesReport . "</div></td><td align=right style=\"padding-top:10px;\">" . ProperDate(DBDate()) . "<br />" . _poweredBy . " openSIS</td></tr><tr><td colspan=2 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";

    ########################################List Output Generation ####################################################

    $columns = array('SUBJECT' => '' . _subject . '', 'COURSE' => '' . _course . '', 'MP' => '' . _markingPeriod . '', 'PERIOD_TIME' => '' . _time . '', 'PERIOD' => '' . _period . '', 'DAYS' => '' . _days . '', 'LOCATION' => '' . _location . '', 'TEACHER' => '' . _teacher . '');

    if ($_REQUEST['print'] == 'list') {
        echo '<link rel="stylesheet" type="text/css" href="assets/css/export_print.css" />';
        echo "<table width=100%  style=\" font-size:12px;\" >";
        echo "<tr><td  style=\"font-size:15px; font-weight:bold;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">" . _courseCatalog . "</div></td><td align=right style=\"padding-top:5px;\">" . ProperDate(DBDate()) . "<br />" . _poweredBy . " openSIS</td></tr><tr><td colspan=2 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
    }
    echo '<div class="print-div">';
    ListOutputFloat($ret, $columns, '' . _course . '', '' . _courses . '', '', '', array('search' => false, 'count' => false), false);
    echo '</div>';
    //echo "<div style=\"page-break-before: always;\"></div>";
    ##########################################################################################################
}

function CreateSelect($val, $name, $opt, $cap, $link)
{
    $html .= '<label class="control-label text-uppercase"><b>' . $cap . '</b></label>';
    $html .= "<select class=form-control name=" . $name . " id=" . $name . " onChange=\"window.location='" . $link . "' + this.options[this.selectedIndex].value;\">";
    $html .= "<option value=''>" . $opt . "</option>";

    foreach ($val as $key => $value) {
        if ($value[strtoupper($name)] == $_REQUEST[$name])
            $html .= "<option selected value=" . $value[strtoupper($name)] . ">" . $value['TITLE'] . "</option>";
        else
            $html .= "<option value=" . $value[strtoupper($name)] . ">" . $value['TITLE'] . "</option>";
    }
    $html .= '</select>';
    return $html;
}

function CreateExcel($dli = '', $pli = '', $sli = '', $cli = '', $mp = '', $mp_name = '')
{

    if ($dli != '')
        $d_ret = DBGet(DBQuery('select title from COURSE_DEGREE_LEVEL where degree_level_id=\'' . $dli . '\''));

    if ($pli != '')
        $p_ret = DBGet(DBQuery('select title from COURSE_PROG_LEVEL where prog_level_id=\'' . $pli . '\''));

    if ($sli != '')
        $s_ret = DBGet(DBQuery('select title from course_subjects where subject_id=\'' . $sli . '\''));

    if ($cli != '')
        $c_ret = DBGet(DBQuery('select title from courses where course_id=\'' . $cli . '\''));

    if ($mp != '') {
        $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'2\' AS TABLE,SORT_ORDER FROM school_quarters WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' and marking_period_id=\'' . $mp . '\' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'1\' AS TABLE,SORT_ORDER FROM school_semesters WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' and marking_period_id=\'' . $mp . '\' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'0\' AS TABLE,SORT_ORDER FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' and marking_period_id=\'' . $mp . '\' ORDER BY 3,4';

        $mp_ret1 = DBGet(DBQuery($sql));
        $mp_name = $mp_ret1[1]['TITLE'];
    }

    if ($mp == '') {
        $where = '';
    } else {
        if ($dli == '') {
            $where = ' and marking_period_id=\'' . $mp . '\'';
            $heading = "All available classes for <font color='black'>" . $mp_name . "</font>";
        } else {
            if ($pli == '') {
                $where = 'and marking_period_id=\'' . $mp . '\' and course_id in (select course_id from  courses where subject_id in (select subject_id from course_subjects where degree_level_id=\'' . $dli . '\'))';
                $heading = "" . _allAvailableClassesFor . " <font color='black'>" . $mp_name . " -> " . $d_ret[1]['TITLE'] . "</font>";
            } else {
                if ($sli == '') {
                    $where = 'and marking_period_id=\'' . $mp . '\' and course_id in (select course_id from  courses where subject_id in (select subject_id from course_subjects where prog_level_id=\'' . $pli . '\'))';
                    $heading = "" . _allAvailableClassesFor . "  <font color='black'>" . $mp_name . " -> " . $d_ret[1]['TITLE'] . " -> " . $p_ret[1]['TITLE'] . "</font>";
                } else {
                    if ($cli == '') {
                        $where = 'and marking_period_id=\'' . $mp . '\' and course_id in (select Course_Id from courses where subject_id = \'' . $_REQUEST['subject_id'] . '\' and School_Id=\'' . UserSchool() . '\')';
                        $heading = "" . _allAvailableClassesFor . "  <font color='black'>" . $mp_name . " -> " . $d_ret[1]['TITLE'] . " -> " . $p_ret[1]['TITLE'] . " -> " . $s_ret[1]['TITLE'] . "</font>";
                    } else {
                        $where = 'and marking_period_id=\'' . $mp . '\' and course_id=\'' . $cli . '\'';
                        $heading = "" . _allAvailableClassesFor . "  <font color='black'>" . $mp_name . " -> " . $d_ret[1]['TITLE'] . " -> " . $p_ret[1]['TITLE'] . " -> " . $s_ret[1]['TITLE'] . " -> " . $c_ret[1]['TITLE'] . "</font>";
                    }
                }
            }
        }
    }

    $sql = 'select
                (select title from courses where course_id=course_periods.course_id) as course,
                (select title from course_subjects where subject_id=(select subject_id from courses where 						course_id=course_periods.course_id)) as subject,
                short_name,
                (select title from school_periods where period_id=cpv.period_id) as period,
                marking_period_id,
                (select CONCAT(LAST_NAME,\' \',FIRST_NAME,\' \',MIDDLE_NAME,\' \') from staff where staff_id=course_periods.teacher_id) as teacher,
                r.title as location,cpv.days,course_period_id,cp.begin_date,cp.end_date 
                from course_periods,cours_period_var cpv,rooms r where course_period.school_id=\'' . UserSchool() . '\' and course_period.course_period_id=cpv.course_period_id and r.room_id=cpv.room_id and course_period.syear=\'' . UserSyear() . '\' ' . $where . '';


    $result = DBGet(DBQuery($sql));


    $column_names = array('SUBJECT' => '' . _subject . '', 'COURSE' => '' . _course . '', 'SHORT_NAME' => '' . _className . '', 'PERIOD' => '' . _period . '', 'TEACHER' => '' . _teacher . '', 'LOCATION' => '' . _location . '', 'DAYS' => '' . _days . '', 'BEGIN_DATE' => '' . _startDate . '', 'END_DATE' => '' . _endDate . '');

    if (!$options['save_delimiter'] && Preferences('DELIMITER') == 'CSV')
        $options['save_delimiter'] = 'comma';

    ob_end_clean();
    if ($options['save_delimiter'] != 'xml') {
        foreach ($column_names as $key => $value)
            $output .= str_replace('&nbsp;', ' ', eregi_replace('<BR>', ' ', ereg_replace('<!--.*-->', '', $value))) . ($options['save_delimiter'] == 'comma' ? ',' : "\t");
        $output .= "\n";
    }

    foreach ($result as $item) {
        foreach ($column_names as $key => $value) {
            if ($options['save_delimiter'] == 'comma' && !$options['save_quotes'])
                $item[$key] = str_replace(',', ';', $item[$key]);
            $item[$key] = eregi_replace('<SELECT.*SELECTED\>([^<]+)<.*</SELECT\>', '\\1', $item[$key]);
            $item[$key] = eregi_replace('<SELECT.*</SELECT\>', '', $item[$key]);
            $output .= ($options['save_quotes'] ? '"' : '') . ($options['save_delimiter'] == 'xml' ? '<' . str_replace(' ', '', $value) . '>' : '') . ereg_replace('<[^>]+>', '', ereg_replace("<div onclick='[^']+'>", '', ereg_replace(' +', ' ', ereg_replace('&[^;]+;', '', str_replace('<BR>&middot;', ' : ', str_replace('&nbsp;', ' ', $item[$key])))))) . ($options['save_delimiter'] == 'xml' ? '</' . str_replace(' ', '', $value) . '>' . "\n" : '') . ($options['save_quotes'] ? '"' : '') . ($options['save_delimiter'] == 'comma' ? ',' : "\t");
        }
        $output .= "\n";
    }

    header("Cache-Control: public");
    header("Pragma: ");
    header("Content-Type: application/$extension");
    header("Content-Disposition: inline; filename=\"" . ProgramTitle() . ".xls\"\n");
    if ($options['save_eval'])
        eval($options['save_eval']);
    echo $output;
    exit();
}

function cbr($val)
{
    if ($val == 'Y')
        return '<IMG SRC=assets/check.gif height=15 vspace=0 hspace=0 border=0>';
    else
        return '<IMG SRC=assets/check.gif height=15 vspace=0 hspace=0 border=0>';
}

function gr($val)
{
    if ($val == 'M')
        return 'Male';
    elseif ($val == 'F')
        return 'Female';
    else
        return 'None';
}

function _makeMarkingPeriod($val)
{
    $sql = "SELECT marking_period_id from course_periods where course_period_id=$val";
    $result = DBGet(DBQuery($sql));
    if ($result[1]['MARKING_PERIOD_ID'] != '') {
        $id = $result[1]['MARKING_PERIOD_ID'];
        $sql1 = "SELECT title from marking_periods where marking_period_id=$id";
        $result2 = DBGet(DBQuery($sql1));
        return $result2[1]["TITLE"];
    } else {
        $sql2 = "SELECT begin_date ,end_date from course_periods where course_period_id=$val";
        $result3 = DBGet(DBQuery($sql2));
        return _custom . ' ' . '(' . ProperDate($result3[1]["BEGIN_DATE"]) . ' ' . '-' . ' ' . ProperDate($result3[1]["END_DATE"]) . ')';
    }
}

function _makeDayNames($daynames)
{
    $days_arrr = array("Monday" => 'M', "Tuesday" => 'T', "Wednesday" => 'W', "Thursday" => 'H', "Friday" => 'F', "Saturday" => 'S', "Sunday" => 'U');

    $daystitle = str_split($daynames);
    $daynamearr =[];
    foreach ($daystitle as $dayname) {
        $daynamearr[] = array_search($dayname,$days_arrr);

    }
    $dayname = implode(', ', $daynamearr);

    return $dayname;
}
