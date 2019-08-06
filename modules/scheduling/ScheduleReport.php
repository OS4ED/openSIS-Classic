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
DrawBC("Scheduling > " . ProgramTitle());
//echo '<div class="panel panel-default">';
if ($_REQUEST['subject_id']) {
    $RET = DBGet(DBQuery("SELECT TITLE FROM course_subjects WHERE SUBJECT_ID='" . $_REQUEST['subject_id'] . "'"));
    $header .= "<li><A class=\"text-white\" HREF=Modules.php?modname=$_REQUEST[modname]>Top</A></li><li><A class=\"text-white\" HREF=Modules.php?modname=$_REQUEST[modname]&modfunc=courses&subject_id=$_REQUEST[subject_id]>" . $RET[1]['TITLE'] . '</A></li>';
    if ($_REQUEST['course_id']) {
        $header2 = "<li><A HREF=Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]";
        $location = 'courses';
        $RET = DBGet(DBQuery("SELECT TITLE FROM courses WHERE COURSE_ID='" . $_REQUEST['course_id'] . "'"));
        $header .= "<li><A class=\"text-white\" HREF=Modules.php?modname=$_REQUEST[modname]&modfunc=course_periods&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]>" . $RET[1]['TITLE'] . '</A></li>';

        if ($_REQUEST['course_period_id']) {
            $header2 .= "&course_period_id=$_REQUEST[course_period_id]";
            $location = 'course_periods';
            $RET = DBGet(DBQuery("SELECT TITLE FROM course_periods WHERE COURSE_PERIOD_ID='" . $_REQUEST['course_period_id'] . "'"));
            $header .= "<li><A class=\"text-white\" HREF=Modules.php?modname=$_REQUEST[modname]&modfunc=students&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=$_REQUEST[course_period_id]>" . $RET[1]['TITLE'] . '</A></li>';
        }

        $header2 .= "&students=$location&modfunc=$location><i class=\"fa fa-list position-left\"></i> List Students</A></li><li>" . $header2 . "&unscheduled=true&students=$location&modfunc=$location><i class=\"fa fa-user-times position-left\"></i> List Unscheduled Students</A></li>";

        echo '<div class="breadcrumb-line breadcrumb-line-component content-group-lg"><ul class="breadcrumb breadcrumb-white-text">'.$header.'</ul><ul class="breadcrumb-elements">'.$header2.'</ul></div>';
    } else
        echo '<div class="breadcrumb-line breadcrumb-line-component content-group-lg"><ul class="breadcrumb breadcrumb-white-text">'.$header.'</ul></div>';
}
$LO_options = array('save' => false, 'search' => false, 'print' => false);

echo '<div class="row">';
// SUBJECTS ----
if (!$_REQUEST['modfunc'] || ($_REQUEST['modfunc'] == 'courses' && $_REQUEST['students'] != 'courses')) {
    echo '<div class="col-md-6">';
    echo '<div class="panel panel-default">';
    $QI = DBQuery("SELECT s.SUBJECT_ID,s.TITLE FROM course_subjects s WHERE s.SYEAR='" . UserSyear() . "' AND s.SCHOOL_ID='" . UserSchool() . "' ORDER BY s.TITLE");
    $RET = DBGet($QI, array('OPEN_SEATS' => '_calcOpenSeats'));
    if (count($RET) && $_REQUEST['subject_id']) {
        foreach ($RET as $key => $value) {
            if ($value['SUBJECT_ID'] == $_REQUEST['subject_id'])
                $RET[$key]['row_color'] = Preferences('HIGHLIGHT');
        }
    }
    $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=courses";
    $link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID');
    ListOutput($RET, array('TITLE' => 'Subject'), 'Subject', 'Subjects', $link, array(), $LO_options);
    echo '</div>'; //.panel
    echo '</div>'; //.col-md-6
}
// courses ----
if ($_REQUEST['modfunc'] == 'courses' || $_REQUEST['students'] == 'courses') {
    echo '<div class="col-md-6">';
    echo '<div class="panel panel-default">';
    $get_mp_t = DBGet(DBQuery('SELECT MP_TYPE FROM marking_periods WHERE MARKING_PERIOD_ID=' . UserMP()));
    $other_mps = array();
    if ($get_mp_t[1]['MP_TYPE'] != 'year') {
        if ($get_mp_t[1]['MP_TYPE'] == 'semester') {
            $get_mp_ids = DBGet(DBQuery('SELECT PARENT_ID FROM marking_periods WHERE MARKING_PERIOD_ID=' . UserMP()));
            $other_mps[] = $get_mp_ids[1]['PARENT_ID'];
        }
        if ($get_mp_t[1]['MP_TYPE'] == 'quarter') {
            $get_mp_ids = DBGet(DBQuery('SELECT PARENT_ID,GRANDPARENT_ID FROM marking_periods WHERE MARKING_PERIOD_ID=' . UserMP()));
            $other_mps[] = $get_mp_ids[1]['PARENT_ID'];
            $other_mps[] = $get_mp_ids[1]['GRANDPARENT_ID'];
        }
    }
    $QI = "SELECT c.COURSE_ID,c.TITLE,sum(cp.TOTAL_SEATS) as TOTAL_SEATS,sum(cp.FILLED_SEATS) as FILLED_SEATS,NULL AS OPEN_SEATS,(SELECT count(*) FROM schedule_requests sr WHERE sr.COURSE_ID=c.COURSE_ID) AS COUNT_REQUESTS FROM courses c,course_periods cp WHERE c.SUBJECT_ID='$_REQUEST[subject_id]' AND c.COURSE_ID=cp.COURSE_ID AND c.SYEAR='" . UserSyear() . "' AND c.SCHOOL_ID='" . UserSchool() . "' AND " . (count($other_mps) > 0 ? " (cp.MARKING_PERIOD_ID IN (" . UserMp() . "," . implode(',', $other_mps) . ") " : " (cp.MARKING_PERIOD_ID=" . UserMp()) . " OR cp.MARKING_PERIOD_ID IS NULL)  GROUP BY c.COURSE_ID,c.TITLE ORDER BY c.TITLE";

    $QI = DBQuery($QI);
    
    $RET = DBGet($QI, array('OPEN_SEATS' => '_calcOpenSeatsNew'));
    
    if (count($RET) && $_REQUEST['course_id']) {
        foreach ($RET as $key => $value) {
            if ($value['COURSE_ID'] == $_REQUEST['course_id'])
                $RET[$key]['row_color'] = Preferences('HIGHLIGHT');
        }
    }
    $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=course_periods&subject_id=$_REQUEST[subject_id]";
    $link['TITLE']['variables'] = array('course_id' => 'COURSE_ID');
    ListOutput($RET, array('TITLE' => 'Course', 'COUNT_REQUESTS' => 'Requests', 'OPEN_SEATS' => 'Open', 'TOTAL_SEATS' => 'Total'), 'Course', 'Courses', $link, array(), $LO_options);
    echo '</div>'; //.panel
    echo '</div>'; //.col-md-6
}
// COURSE PERIODS ----
if ($_REQUEST['modfunc'] == 'course_periods' || $_REQUEST['students'] == 'course_periods') {
    echo '<div class="col-md-6">';
    echo '<div class="panel panel-default">';
    $get_mp_t = DBGet(DBQuery('SELECT MP_TYPE FROM marking_periods WHERE MARKING_PERIOD_ID=' . UserMP()));
    $other_mps = array();
    if ($get_mp_t[1]['MP_TYPE'] != 'year') {
        if ($get_mp_t[1]['MP_TYPE'] == 'semester') {
            $get_mp_ids = DBGet(DBQuery('SELECT PARENT_ID FROM marking_periods WHERE MARKING_PERIOD_ID=' . UserMP()));
            $other_mps[] = $get_mp_ids[1]['PARENT_ID'];
        }
        if ($get_mp_t[1]['MP_TYPE'] == 'quarter') {
            $get_mp_ids = DBGet(DBQuery('SELECT PARENT_ID,GRANDPARENT_ID FROM marking_periods WHERE MARKING_PERIOD_ID=' . UserMP()));
            $other_mps[] = $get_mp_ids[1]['PARENT_ID'];
            $other_mps[] = $get_mp_ids[1]['GRANDPARENT_ID'];
        }
    }
    $QI = "SELECT cp.COURSE_ID,cp.COURSE_PERIOD_ID,cp.TITLE,sum(cp.TOTAL_SEATS) as TOTAL_SEATS,sum(cp.FILLED_SEATS) as FILLED_SEATS,NULL AS OPEN_SEATS FROM course_periods cp WHERE cp.COURSE_ID='" . $_REQUEST['course_id'] . "' AND cp.SYEAR='" . UserSyear() . "' AND cp.SCHOOL_ID='" . UserSchool() . "' AND " . (count($other_mps) > 0 ? " (cp.MARKING_PERIOD_ID IN (" . UserMp() . "," . implode(',', $other_mps) . ") " : " (cp.MARKING_PERIOD_ID=" . UserMp()) . " OR cp.MARKING_PERIOD_ID IS NULL)  GROUP BY cp.COURSE_ID,cp.COURSE_PERIOD_ID,cp.TITLE ORDER BY cp.TITLE";
  
    $QI = DBQuery($QI);
  
    $RET = DBGet($QI, array('OPEN_SEATS' => '_calcOpenSeats'));

    if (count($RET) && $_REQUEST['course_period_id']) {
        foreach ($RET as $key => $value) {
            if ($value['COURSE_PERIOD_ID'] == $_REQUEST['course_period_id'])
                $RET[$key]['row_color'] = Preferences('HIGHLIGHT');
        }
    }
    $link = array();
    $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=students&students=course_periods&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]";
    $link['TITLE']['variables'] = array('course_period_id' => 'COURSE_PERIOD_ID');
    ListOutput($RET, array('TITLE' => 'Period - Teacher', 'OPEN_SEATS' => 'Open', 'TOTAL_SEATS' => 'Total'), 'Course Period', 'Course Periods', $link, array(), $LO_options);
    echo '</div>'; //.panel
    echo '</div>'; //.col-md-6
}
// LIST STUDENTS ----
if ($_REQUEST['students']) {
    echo '<div class="col-md-6">';
    echo '<div class="panel panel-default">';
    if ($_REQUEST['unscheduled'] == 'true') {
        $sql = "SELECT CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,s.STUDENT_ID,s.BIRTHDATE,ssm.GRADE_ID
				FROM schedule_requests sr,students s,student_enrollment ssm
				WHERE (('" . DBDate() . "' BETWEEN ssm.START_DATE AND ssm.END_DATE OR ssm.END_DATE IS NULL)) AND s.STUDENT_ID=sr.STUDENT_ID AND s.STUDENT_ID=ssm.STUDENT_ID AND ssm.SYEAR='" . UserSyear() . "' AND ssm.SCHOOL_ID='" . UserSchool() . "' ";
        if ($_REQUEST['course_id'])
            $sql .= "AND sr.COURSE_ID='$_REQUEST[course_id]' ";
        $sql .= "AND NOT EXISTS (SELECT '' FROM schedule ss WHERE ss.COURSE_ID=sr.COURSE_ID AND ss.STUDENT_ID=sr.STUDENT_ID AND ('" . DBDate() . "' BETWEEN ss.START_DATE AND ss.END_DATE OR ss.END_DATE IS NULL))";
    }
    else {
//            AND (('".DBDate()."' BETWEEN ss.START_DATE AND ss.END_DATE OR ss.END_DATE IS NULL) or (ss.END_DATE=(SELECT END_DATE from  course_periods where COURSE_PERIOD_ID='$_REQUEST[course_period_id]')))
        $sql = "SELECT CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,s.STUDENT_ID,s.BIRTHDATE,ssm.GRADE_ID
				FROM schedule ss,students s,student_enrollment ssm
				WHERE (('" . DBDate() . "' BETWEEN ss.START_DATE AND ss.END_DATE OR ss.END_DATE IS NULL) or (ss.END_DATE=(SELECT END_DATE from  course_periods where COURSE_PERIOD_ID='$_REQUEST[course_period_id]'))) AND (('" . DBDate() . "' BETWEEN ssm.START_DATE AND ssm.END_DATE OR ssm.END_DATE IS NULL)) AND s.STUDENT_ID=ss.STUDENT_ID AND s.STUDENT_ID=ssm.STUDENT_ID AND ssm.SYEAR='" . UserSyear() . "' AND ssm.SCHOOL_ID='" . UserSchool() . "' ";
        if ($_REQUEST['course_period_id'])
            $sql .= "AND ss.COURSE_PERIOD_ID='$_REQUEST[course_period_id]'";
        elseif ($_REQUEST['course_id'])
            $sql .= "AND ss.COURSE_ID='$_REQUEST[course_id]'";
    }
    $sql .= ' ORDER BY s.LAST_NAME,s.FIRST_NAME';
    $RET = DBGet(DBQuery($sql), array('BIRTHDATE' => 'Birthdate', 'GRADE_ID' => 'GetGrade'));

    $link = array();
    $link['FULL_NAME']['link'] = "Modules.php?modname=scheduling/Schedule.php";
    $link['FULL_NAME']['variables'] = array('student_id' => 'STUDENT_ID');
    ListOutput($RET, array('FULL_NAME' => 'Student', 'GRADE_ID' => 'Grade', 'BIRTHDATE' => 'Birthdate'), 'Student', 'Students', $link, array(), $LO_options);
    echo '</div>'; //.panel
    echo '</div>'; //.col-md-6
}

echo '</div>'; //.row

function _calcOpenSeats($null) {
    global $THIS_RET;
//    print_r($THIS_RET);
    $sql = "SELECT COUNT(*) as TOT
				FROM schedule ss,students s,student_enrollment ssm
				WHERE (('" . DBDate() . "' BETWEEN ss.START_DATE AND ss.END_DATE OR ss.END_DATE IS NULL) or (ss.END_DATE=(SELECT END_DATE from  course_periods where COURSE_PERIOD_ID='$THIS_RET[COURSE_PERIOD_ID]'))) AND (('" . DBDate() . "' BETWEEN ssm.START_DATE AND ssm.END_DATE OR ssm.END_DATE IS NULL)) AND s.STUDENT_ID=ss.STUDENT_ID AND s.STUDENT_ID=ssm.STUDENT_ID AND ssm.SYEAR='" . UserSyear() . "' AND ssm.SCHOOL_ID='" . UserSchool() . "' AND ss.COURSE_PERIOD_ID='$THIS_RET[COURSE_PERIOD_ID]' ";

    $res = DBGet(DBQuery($sql));

    return $THIS_RET['TOTAL_SEATS'] - $res[1]['TOT'];
}
function _calcOpenSeatsNew($null) {
    global $THIS_RET;
    $sql = "SELECT COUNT(*) as TOT
				FROM schedule ss,students s,student_enrollment ssm
				WHERE (('" . DBDate() . "' BETWEEN ss.START_DATE AND ss.END_DATE OR ss.END_DATE IS NULL) or (ss.END_DATE=(SELECT END_DATE from  course_periods where COURSE_ID='$THIS_RET[COURSE_ID]'))) AND (('" . DBDate() . "' BETWEEN ssm.START_DATE AND ssm.END_DATE OR ssm.END_DATE IS NULL)) AND s.STUDENT_ID=ss.STUDENT_ID AND s.STUDENT_ID=ssm.STUDENT_ID AND ssm.SYEAR='" . UserSyear() . "' AND ssm.SCHOOL_ID='" . UserSchool() . "' AND ss.COURSE_ID='$THIS_RET[COURSE_ID]' ";

    $res = DBGet(DBQuery($sql));

    return $THIS_RET['TOTAL_SEATS'] - $res[1]['TOT'];
}

?>