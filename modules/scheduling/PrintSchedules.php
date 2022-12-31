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

if(isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
{
    $_REQUEST['search_modfunc'] = 'list';
    $_REQUEST['stuid'] = $_SESSION['student_id'];
    $_REQUEST['sql_save_session'] = 'true';
}

if ($_REQUEST['modfunc'] == 'save') {
    if (is_countable($_REQUEST['st_arr']) && count($_REQUEST['st_arr'])) {

        if ($_REQUEST['_search_all_schools'] == 'Y') {

            $_REQUEST['_search_all_schools'] = 'Y';
        }
        
        $st_list = '\'' . implode('\',\'', $_REQUEST['st_arr']) . '\'';
        $extra['WHERE'] = " AND s.STUDENT_ID IN ($st_list)";

        if ($_REQUEST['day_include_active_date'] && $_REQUEST['month_include_active_date'] && $_REQUEST['year_include_active_date']) {
            $date = $_REQUEST['day_include_active_date'] . '-' . $_REQUEST['month_include_active_date'] . '-' . $_REQUEST['year_include_active_date'];
            $date_extra = 'OR (\'' . $date . '\' >= sr.START_DATE AND sr.END_DATE IS NULL)';
        } else {
            $date = DBDate();
            $date_extra = 'OR sr.END_DATE IS NULL';
        }

        if (isset($_REQUEST['date1']) && $_REQUEST['date1'] != '') {
            $new_date = $_REQUEST['date1'];
        } else {
            $new_date = DBDate();
        }

        $columns = array('COURSE' => _course, 'MARKING_PERIOD_ID' => _term, 'DAYS' => _days, 'PERIOD_TITLE' => _periodTeacher, 'DURATION' => _time, 'ROOM' => _room);

        $extra['SELECT'] .= ',p_cp.COURSE_PERIOD_ID,r.TITLE as ROOM,CONCAT(sp.START_TIME,\'' . ' to ' . '\', sp.END_TIME) AS DURATION,sp.TITLE AS PERIOD,cpv.DAYS,cpv.COURSE_PERIOD_DATE,p_cp.SCHEDULE_TYPE,c.TITLE AS COURSE,p_cp.TITLE AS PERIOD_TITLE,sr.MARKING_PERIOD_ID';
        $extra['FROM'] .= ' LEFT OUTER JOIN schedule sr ON (sr.STUDENT_ID=ssm.STUDENT_ID),courses c,course_periods p_cp,course_period_var cpv,school_periods sp,rooms r ';

//        if($_REQUEST['include_inactive']!='Y')
//        {
//            if(isset($_REQUEST['date1']) && $_REQUEST['date1']!='')
//            {
//                $extra['WHERE'].=' AND (p_cp.MARKING_PERIOD_ID IN ('.GetAllMP_mod(GetMPTable(GetMP($_REQUEST['mp_id'],'TABLE')),$_REQUEST['mp_id']).') OR (p_cp.MARKING_PERIOD_ID IS NULL AND p_cp.BEGIN_DATE<=\''.date('Y-m-d',strtotime($_REQUEST['date1'])).'\' AND p_cp.END_DATE>=\''.date('Y-m-d',strtotime($_REQUEST['date1'])).'\')) ' ;   
//            }
//            else
//            {
//                $extra['WHERE'].=' AND (p_cp.MARKING_PERIOD_ID IN ('.GetAllMP_mod(GetMPTable(GetMP($_REQUEST['mp_id'],'TABLE')),$_REQUEST['mp_id']).') OR (p_cp.MARKING_PERIOD_ID IS NULL AND p_cp.BEGIN_DATE<=\''.date('Y-m-d',strtotime($date)).'\' AND p_cp.END_DATE>=\''.date('Y-m-d',strtotime($date)).'\')) ' ;   
//            }
//        }

        if ((isset($_REQUEST['date1']) && $_REQUEST['date1'] != '')) {
            $extra['WHERE'] .= ' AND POSITION(\'' . get_db_day(date('l', strtotime($_REQUEST['date1']))) . '\' IN cpv.days)>0 AND (cpv.COURSE_PERIOD_DATE=\'' . date('Y-m-d', strtotime($_REQUEST['date1'])) . '\' OR cpv.COURSE_PERIOD_DATE IS NULL)';
        }

        if(isset($_REQUEST['include_inactive']) && $_REQUEST['include_inactive'] == 'Y')
        {
            $extra['WHERE'] .= ' AND cpv.ROOM_ID=r.ROOM_ID AND ssm.SYEAR=sr.SYEAR AND sr.COURSE_ID=c.COURSE_ID AND sr.COURSE_PERIOD_ID=p_cp.COURSE_PERIOD_ID AND cpv.COURSE_PERIOD_ID=p_cp.COURSE_PERIOD_ID AND sp.PERIOD_ID = cpv.PERIOD_ID ';
        }
        else
        {
            $extra['WHERE'] .= ' AND sr.END_DATE>=\'' . date('Y-m-d') . '\' AND cpv.ROOM_ID=r.ROOM_ID AND ssm.SYEAR=sr.SYEAR AND sr.COURSE_ID=c.COURSE_ID AND sr.COURSE_PERIOD_ID=p_cp.COURSE_PERIOD_ID AND cpv.COURSE_PERIOD_ID=p_cp.COURSE_PERIOD_ID AND sp.PERIOD_ID = cpv.PERIOD_ID ';
        }

//        if($_REQUEST['include_inactive']!='Y')
//        {
//                if((isset($_REQUEST['date1']) && $_REQUEST['date1']!=''))
//                {                
//                $extra['WHERE'] .= '   AND (\''.date('Y-m-d',strtotime($_REQUEST['date1'])).'\' BETWEEN sr.START_DATE AND sr.END_DATE  OR (sr.END_DATE IS NULL AND sr.START_DATE<=\''.date('Y-m-d',strtotime($_REQUEST['date1'])).'\')) ';
//                }
//                else
//                {
//                $extra['WHERE'] .= ' AND (\''.date('Y-m-d',strtotime($date)).'\' BETWEEN sr.START_DATE AND sr.END_DATE OR (sr.END_DATE IS NULL AND sr.START_DATE<=\''.date('Y-m-d',strtotime($date)).'\')) ';
//                }
//        }
//                  if($_REQUEST['mp_id'] && (User('PROFILE_ID')!=0 && User('PROFILE_ID')!=3 && User('PROFILE_ID')!=4) ){
//                      $extra['WHERE'] .= ' AND sr.MARKING_PERIOD_ID='.$_REQUEST['mp_id'].'';
//                          }
//                  else
//                  {
        if ($_REQUEST['_search_all_schools'] == 'Y') {
            $extra['WHERE'] .= ' AND (sr.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM marking_periods WHERE SYEAR='. UserSyear().') or sr.MARKING_PERIOD_ID is NULL)';
        }
        else
        $extra['WHERE'] .= ' AND (sr.MARKING_PERIOD_ID IN (' . GetAllMP_mod(GetMPTable(GetMP($_REQUEST['mp_id']
        , 'TABLE')), $_REQUEST['mp_id']
        ) . ') or sr.MARKING_PERIOD_ID is NULL)';
       if ($_REQUEST['_search_all_schools'] == 'Y')
        $extra['functions'] = array('MARKING_PERIOD_ID' => 'GetMPAllSchool');
        else
            $extra['functions'] = array('MARKING_PERIOD_ID' => 'GetMP');
        $extra['group'] = array('STUDENT_ID');
//        $extra['order']=array('SORT_ORDER','MARKING_PERIOD_ID');
//        $extra['ORDER'] .= ',p_cp.COURSE_PERIOD_ID,cpv.days';
        $extra['ORDER'] .= ',sp.SORT_ORDER,sr.MARKING_PERIOD_ID';
        $RET = GetStuList($extra);

        foreach ($RET as $ri => $rd) {
            foreach ($rd as $rdi => $rdd) {

                $get_det = DBGet(DBQuery('SELECT cpv.DAYS,cpv.COURSE_PERIOD_DATE,CONCAT(sp.START_TIME,\'' . ' to ' . '\', sp.END_TIME) AS DURATION,r.TITLE as ROOM FROM course_period_var cpv,school_periods sp,rooms r WHERE sp.PERIOD_ID=cpv.PERIOD_ID AND cpv.ROOM_ID=r.ROOM_ID AND cpv.COURSE_PERIOD_ID=' . $rdd['COURSE_PERIOD_ID']));

                if ($rdd['SCHEDULE_TYPE'] == 'FIXED') {
                    $RET[$ri][$rdi]['DAYS'] = _makeDays($get_det[1]['DAYS']);
                    $time = explode(' to ', $get_det[1]['DURATION']);
                    $get_det[1]['DURATION'] = date("g:i A", strtotime($time[0])) . ' to ' . date("g:i A", strtotime($time[1]));
                    unset($time);
                    $RET[$ri][$rdi]['DURATION'] = $get_det[1]['DURATION'];
                    $RET[$ri][$rdi]['ROOM'] = $get_det[1]['ROOM'];
                }elseif ($rdd['SCHEDULE_TYPE'] == 'BLOCKED'){
                    $columns = array('COURSE' => _course, 'MARKING_PERIOD_ID' => _term, 'DAYS' => _days, 'PERIOD_TITLE' => _periodTeacher, 'DURATION' => _time, 'ROOM' => _room, 'COURSE_PERIOD_DATE' => _date);
                    $RET[$ri][$rdi]['COURSE_PERIOD_DATE'] = ProperDate($RET[$ri][$rdi]['COURSE_PERIOD_DATE']);
                }
//                else
//                {  
//                    $temp_days=array();
//                    $temp_duration=array();
//                    $temp_room=array();
//               
//                    foreach($get_det as $gi=>$gd)
//                    {
//                       if($rdd['SCHEDULE_TYPE']=='VARIABLE')
//                       $temp_days[$gd['DAYS']]=$gd['DAYS'];
//                       elseif($rdd['SCHEDULE_TYPE']=='BLOCKED')
//                       $temp_days[$gd['DAYS']]=DaySname(date('l',$gd['COURSE_PERIOD_DATE']));
//                       
//                       $time=explode(' to ',$gd['DURATION']);
//                       $gd['DURATION']=date("g:i A", strtotime($time[0])).' to '.date("g:i A", strtotime($time[1]));
//                       unset($time);
//                       $temp_duration[$gd['DURATION']]=$gd['DURATION'];
//                       $temp_room[$gd['ROOM']]=$gd['ROOM'];
//                        
//                    }
//                    $RET[$ri][$rdi]['DAYS']=_makeDays(implode('',$temp_days));
//                    $RET[$ri][$rdi]['DURATION']=implode(',',$temp_duration);
//                    $RET[$ri][$rdi]['ROOM']=implode(',',$temp_room);
//                }
            }
        }
//      
//        foreach($RET as $student_id =>$RET1)
//        {
//            $ex_cp_arr=array();
//            foreach($RET1 as $key => $val)
//            {
//                if(!in_array($val['COURSE_PERIOD_ID'],$ex_cp_arr))
//                {
//                    $ex_cp_arr[]=$val['COURSE_PERIOD_ID'];
//                }
// else {
//
//     unset($RET[$student_id][$key]);
// }
//            }
//        }
//        $RET_new=array();
//        foreach($RET as $student_id =>$RET1)
//        {
//            $k=1;
//            $ex_cp_arr=array();
//            foreach($RET1 as $key => $val)
//            {
//               $RET_new[$student_id][$k]=$val;
//               $k++;
//
//            }
//        }
        // $RET=$RET_new;

        if (is_countable($RET) && count($RET)) {
            $handle = PDFStart();

            foreach ($RET as $student_id => $courses) {
                echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
                echo "<tr><td width=105>" . DrawLogo() . "</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">"._studentSchedulesReport."</div></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br />"._poweredByOpenSis."</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";

                unset($_openSIS['DrawHeader']);
                echo '<br>';
                echo '<table  border=0>';
                echo '<tr><td>'._studentId.':</td>';
                echo '<td>' . $courses[1]['STUDENT_ID'] . '</td></tr>';
                echo '<tr><td>'._studentName.':</td>';
                echo '<td>' . $courses[1]['FULL_NAME'] . '</td></tr>';
                echo '<tr><td>'._studentGrade.':</td>';
                echo '<td>' . $courses[1]['GRADE_ID'] . '</td></tr>';
                if ($_REQUEST['mailing_labels'] == 'Y') {
                    $mail_address = DBGet(DBQuery('SELECT STREET_ADDRESS_1,STREET_ADDRESS_2,CITY,STATE,ZIPCODE FROM student_address WHERE TYPE=\'Mail\' AND  STUDENT_ID=' . $courses[1]['STUDENT_ID']));
                    $mail_address = $mail_address[1]['STREET_ADDRESS_1'] . ($mail_address[1]['STREET_ADDRESS_2'] != '' ? ' ' . $mail_address[1]['STREET_ADDRESS_2'] : ' ') . '<br>' . $mail_address[1]['CITY'] . ', ' . $mail_address[1]['STATE'] . ' ' . $mail_address[1]['ZIPCODE'];
                    echo '<tr><td>'._mailingDetails.'</td>';
                    echo '<td>' . ($mail_address != '' ? $mail_address : 'N/A') . '</td></tr>';
                }
                echo '</table>';

                ListOutputPrint_sch($courses, $columns, _course, _courses, array(), array(), array('center' =>false, 'print' =>false));
                echo '<div style="page-break-before: always;">&nbsp;</div><!-- NEW PAGE -->';
            }
            PDFStop($handle);
        } else
            BackPrompt(_noStudentsWereFound);
    } else
        BackPrompt(_youMustChooseAtLeastOneStudent);
}

if (!$_REQUEST['modfunc']) {
    DrawBC(""._scheduling." > " . ProgramTitle());

    if ($_REQUEST['search_modfunc'] == 'list') {
        $mp_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SORT_ORDER,1 AS TBL FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' UNION SELECT MARKING_PERIOD_ID,TITLE,SORT_ORDER,2 AS TBL FROM school_semesters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' UNION SELECT MARKING_PERIOD_ID,TITLE,SORT_ORDER,3 AS TBL FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY TBL,SORT_ORDER'));
        $mp_select = '<SELECT class="form-control" name=mp_id><OPTION value="">'._NA.'';
        foreach ($mp_RET as $mp)
            $mp_select .= '<OPTION value=' . $mp['MARKING_PERIOD_ID'] . '>' . $mp['TITLE'];
        $mp_select .= '</SELECT>';

        echo "<FORM class=form-horizontal name=sch id=sch action=ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&head_html=Student+Schedules+Report&modfunc=save&include_inactive=$_REQUEST[include_inactive]&_openSIS_PDF=true method=POST target=_blank>";

        Widgets('mailing_labels', true);
        $extra['extra_header_left'] = '<div class="row">';
        $extra['extra_header_left'] .= '<div class="col-md-3"><label class="control-label col-md-4">'._markingPeriod.'</label><div class="col-md-8">' . $mp_select . '</div></div>';
        $extra['extra_header_left'] .= '<div class="col-md-3"><label class="control-label col-md-4">'._includeOnlyCoursesActiveAsOf.'</label><div class="col-md-8">' . DateInputAY('', 'include_active_date', 1) . '</div></div>';
        $extra['extra_header_left'] .= '<div class="col-md-3">' . $extra['search'] . '</div>';
        $extra['search'] = '';
        $extra['extra_header_left'] .= '</div>';
    }

    $extra['link'] = array('FULL_NAME' =>false);
    $extra['SELECT'] = ',s.STUDENT_ID AS CHECKBOX';
    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
//    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller checked onclick="checkAll(this.form,this.form.controller.checked,\'st_arr\');"><A>');
    // $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'st_arr\');"><A>');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'st_arr\');"><A>');

    $extra['options']['search'] = false;
    $extra['new'] = true;

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('request_mod');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('course');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row

    Search('student_id', $extra);

    if ($_REQUEST['search_modfunc'] == 'list') {
        if ($_SESSION['count_stu'] != 0)
            echo '<div class="text-right p-r-20 p-b-20"><INPUT type=submit class="btn btn-primary" value="'._printSchedulesForSelectedStudents.'"></div>';
        echo "</FORM>";
    }
}

function _makeDays($value, $column = '') {
    $return='';
    foreach (array('U', 'M', 'T', 'W', 'H', 'F', 'S') as $day)
        if (strpos($value, $day) !== false)
            $return .= $day;
        else
            $return .= '-';
    return $return;
}

function _makeChooseCheckbox($value, $title) {
    global $THIS_RET;
    // return '<INPUT type=checkbox name=st_arr[] value=' . $value . ' checked>';
    
    return "<input name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET['STUDENT_ID'] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);' />";
}

// function _makeChooseCheckbox($value, $title) {
//     global $THIS_RET;

//     return '<INPUT type=checkbox name=st_arr[] value=' . $value . '>';
    
// //   return "<input name=unused[$THIS_RET[STUDENT_ID]]  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckbox(\"values[STUDENTS][$THIS_RET[STUDENT_ID]]\",this,$THIS_RET[STUDENT_ID]);' />";

// //   return "<input name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET[STUDENT_ID] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);' />";
   
// }

function get_db_day($day) {
    switch ($day) {
        case 'Sunday':
            $return = 'U';
            break;
        case 'Monday':
            $return = 'M';
            break;
        case 'Tuesday':
            $return = 'T';
            break;
        case 'Wednesday':
            $return = 'W';
            break;
        case 'Thursday':
            $return = 'H';
            break;
        case 'Friday':
            $return = 'F';
            break;
        case 'Saturday':
            $return = 'S';
            break;
    }
    return $return;
}
?>

