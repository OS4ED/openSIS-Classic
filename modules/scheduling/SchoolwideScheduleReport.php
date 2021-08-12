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
include('RedirectRootInc.php');
include('Warehouse.php');
include('modules/students/configInc.php');
ini_set('memory_limit', '1200000000M');
ini_set('max_execution_time', '500000');

session_start();

DrawBC(""._scheduling." > "._reports." > " . ProgramTitle());

$this_mp = UserMP();

$this_mp_det = DBGet(DBQuery("SELECT * FROM marking_periods WHERE marking_period_id = '".$this_mp."'"));

$mp_list = '';

if(isset($this_mp_det) && $this_mp_det != '')
{
    $this_mp_det = $this_mp_det[1];

    if($this_mp_det['MP_TYPE'] == 'quarter')
    {
        $sem_parent = GetParentMP('SEM', $this_mp);

        $fy_parent = GetParentMP('FY', $sem_parent);

        $mp_list = "'".$this_mp."'";

        if($sem_parent != '')
        {
            $mp_list .= ",'".$sem_parent."'";
        }

        if($fy_parent != '')
        {
            $mp_list .= ",'".$fy_parent."'";
        }
    }

    if($this_mp_det['MP_TYPE'] == 'semester')
    {
        $fy_parent = GetParentMP('FY', $this_mp);

        $mp_list = $this_mp;

        if($fy_parent != '')
        {
            $mp_list .= ",'".$fy_parent."'";
        }
    }

    if($this_mp_det['MP_TYPE'] == 'year')
    {
        $mp_list = "'".$this_mp."'";
    }
}
else
{
    $mp_list = "'".$this_mp."'";
}

// echo $mp_list;
// echo "<pre>";print_r($_REQUEST);echo "</pre>";

$todays_fixed_date  =   new DateTime(date("Y-m-d"));
// $this_week_number   =   $todays_fixed_date->format("W");
// $this_year          =   $todays_fixed_date->format("Y");
$this_day           =   $todays_fixed_date->format("l");

// $this_week_dates    =   getStartAndEndDate($this_week_number, $this_year);

// $todays_date_letter = GetDaysShortNames(date('l', strtotime($todays_date)));

$get_school_days = DBGet(DBQuery("SELECT days FROM `school_calendars` WHERE `syear` = '".UserSyear()."' AND `school_id` = '".UserSchool()."' AND `default_calendar` = 'Y'"));

$schooling_days = $get_school_days[1]['DAYS'];
$schooling_days_arr = str_split($schooling_days);

$first_day_name =   GetDaysNames(array_values($schooling_days_arr)[0]);
$end_day_name   =   GetDaysNames(end($schooling_days_arr));
$this_week      =   GetCurrentWeekDates($first_day_name, $end_day_name, $this_day);

$first_day_of_week = array_values($this_week)[0];


if(isset($_REQUEST['modfunc']) && isset($_REQUEST['take_date']) && $_REQUEST['take_date'] != '')
{
    $changed_date = $_REQUEST['take_date'];
    $todays_date = date("Y-m-d", strtotime($changed_date));
}
else
{
    $todays_date = date("Y-m-d", strtotime($first_day_of_week));
}

$todays_date_letter = GetDaysShortNames(date('l', strtotime($todays_date)));
// echo $todays_date.' - '.$first_day_name.' - '.$end_day_name;
// echo "<pre>";print_r($this_week);echo "</pre>";


$get_periods = DBGet(DBQuery("SELECT period_id, title, short_name, start_time, end_time FROM `school_periods` WHERE `syear` = '".UserSyear()."' AND `school_id` = '".UserSchool()."' ORDER BY `sort_order`"));

$get_subjects = DBGet(DBQuery("SELECT subject_id, title FROM `course_subjects` WHERE `school_id` = '".UserSchool()."' AND syear = '".UserSyear()."' ORDER BY `subject_id`"));

$subject_count  =   count($get_subjects);

$main_RET = array();
$empty_subject_flag = 0;

foreach($get_subjects as $one_sub)
{
    $main_REPACK = array(
        "subject"   =>  $one_sub['TITLE'],
        "teachers"  =>  array()
    );

	$uniq_teachers = DBGet(DBQuery("SELECT DISTINCT(cp.teacher_id) AS teacher_id FROM `course_periods` cp LEFT JOIN `courses` c ON cp.course_id = c.course_id LEFT JOIN `course_period_var` cpv ON cp.course_period_id = cpv.course_period_id WHERE c.subject_id = '".$one_sub['SUBJECT_ID']."' AND cp.begin_date <= '".$todays_date."' AND (cp.end_date IS NULL OR cp.end_date >= '".$todays_date."') AND cpv.days LIKE '%".$todays_date_letter."%' AND (cp.marking_period_id IN (".$mp_list.") OR (cp.marking_period_id IS NULL AND (cp.begin_date BETWEEN '".$this_mp_det['START_DATE']."' AND '".$this_mp_det['END_DATE']."' OR cp.end_date BETWEEN '".$this_mp_det['START_DATE']."' AND '".$this_mp_det['END_DATE']."')))"));

    // echo "<pre>";print_r($uniq_teachers);echo "</pre>";

    foreach($uniq_teachers as $one_teacher)
    {
        $this_teacher_id = $one_teacher['TEACHER_ID'];

        $teacher_det = DBGet(DBQuery("SELECT GROUP_CONCAT(first_name, ' ', last_name) AS teacher_name FROM `staff` WHERE staff_id = '".$this_teacher_id."'"));

        $teacher_REPACK = array(
            "teacher_id" => $this_teacher_id,
            "teacher_name" => $teacher_det[1]['TEACHER_NAME'],
            "schedules" =>  array()
        );
        
        // echo "<pre>";print_r($main_REPACK);echo "</pre>";

        foreach($get_periods as $one_period)
        {
            $get_schedules = DBGet(DBQuery("SELECT cp.course_period_id, cp.title, c.title, c.short_name, cpv.period_id FROM `course_periods` cp LEFT JOIN `courses` c ON cp.course_id = c.course_id LEFT JOIN `course_period_var` cpv ON cp.course_period_id = cpv.course_period_id WHERE c.subject_id = '".$one_sub['SUBJECT_ID']."' AND cp.begin_date <= '".$todays_date."' AND (cp.end_date IS NULL OR cp.end_date >= '".$todays_date."') AND cpv.days LIKE '%".$todays_date_letter."%' AND cpv.period_id = '".$one_period['PERIOD_ID']."' AND cp.teacher_id = '".$this_teacher_id."' AND (cp.marking_period_id IN (".$mp_list.") OR (cp.marking_period_id IS NULL AND (cp.begin_date BETWEEN '".$this_mp_det['START_DATE']."' AND '".$this_mp_det['END_DATE']."' OR cp.end_date BETWEEN '".$this_mp_det['START_DATE']."' AND '".$this_mp_det['END_DATE']."')))"));

            $teacher_REPACK['schedules'][] = $get_schedules;
        }

        if(!empty($teacher_REPACK) && $teacher_REPACK != '')
        {
            $empty_subject_flag++;
        }

        $main_REPACK['teachers'][] = $teacher_REPACK;
    }

    array_push($main_RET, $main_REPACK);
}

// echo "<pre>";print_r($subject_count);echo "</pre>";

if($subject_count == 0)
{
    $show_subject_count = _noSubjectWasFound;
    $pb = "";
}
else if($subject_count == 1)
{
    $show_subject_count = _oneSubjectWasFound;
    $pb = "p-b-0";
}
else if($subject_count > 1)
{
    $show_subject_count = $subject_count.' ' ._subjectsWereFound.'';
    $pb = "p-b-0";
}

if($schooling_days != '')
{
    $pb = "p-b-0";
}
else
{
    $pb = "";
}

echo '<div class="panel">';
echo '<form id="ssrfrm" action="Modules.php?modname='.$_REQUEST['modname'].'&modfunc=date_changed" method="post">';

echo '<input id="take_date" name="take_date" type="hidden" value="'.$first_day_of_week.'">';

echo '<ul class="nav nav-tabs nav-tabs-bottom no-margin-bottom"><li class="active"><a href="javascript:void(0);">'._schoolwideScheduleReport.'</a></li></ul>';

echo '<div class="panel-heading '.$pb.'"><h6 class="panel-title">';
echo '<span class="heading-text">'.$show_subject_count.'</span>';

if($subject_count != 0)
{
    echo ' &nbsp; <a onclick="exportAsSpreadsheet(\'Schoolwide-Schedule-Report-'.$todays_date.'\')" href="javascript:void(0);" class="btn btn-success btn-xs btn-icon text-white" data-popup="tooltip" data-placement="top" data-container="body" title="" data-original-title="Download Spreadsheet"><i class="icon-file-excel"></i></a> &nbsp; <a onclick="exportAsPDF(\'Schoolwide-Schedule-Report-'.$todays_date.'\');" href="javascript:void(0);" class="btn btn-danger btn-xs btn-icon text-white" data-popup="tooltip" data-placement="top" data-container="body" title="" data-original-title="Download PDF"><i class="icon-file-pdf"></i></a> &nbsp; <a onclick="exportAsPrint(\'Schoolwide-Schedule-Report-'.$todays_date.'\')" href="javascript:void(0);" class="btn btn-primary btn-xs btn-icon text-white" data-popup="tooltip" data-placement="top" data-container="body" title="" data-original-title="Print"><i class="fa fa-print f-s-16"></i></a>';
}

echo '</h6></div>';

echo '<ul class="nav nav-tabs nav-tabs-bottom no-margin-bottom">';

$day_inc = 0;
foreach($this_week as $each_day)
{
	$this_active = '';

	// if($day_inc == 0)
    if(strtotime($each_day) == strtotime($todays_date))
	{
		$this_active = 'active';
	}

    $this_day_shortname = GetDaysShortNames(date("l", strtotime($each_day)));

    if(in_array($this_day_shortname, $schooling_days_arr))
    {
    	echo '<li class="'.$this_active.'"><a onclick="getSchoolScheduleReport(\''.$day_inc.'\');" href="javascript:void(0);">'.date('l', strtotime($each_day)).'</a></li>';

        echo '<input id="week_date_'.$day_inc.'" type="hidden" value="'.$each_day.'">';
    }

	$day_inc++;
}

echo '</ul>';

echo '<div class="panel-body">';

if(!empty($get_school_days) && $get_school_days != '')
{
    if(!empty($get_periods) && $get_periods != '')
    {
        echo '<div id="ssr-table" class="table-responsive">
                <table class="table table-bordered" id="schedule-markup">
                    <thead>
                        <tr>
                            <th scope="col" class="table-black-bg">'._subjectTeacher.'</th>';

                            foreach($get_periods as $one_period)
                            {
                                echo '<th scope="col" class="table-sky-blue-bg">'.$one_period['TITLE'].'</th>';
                            }

                    echo '</tr>
                    </thead>';

        echo '<tbody>
                    <tr>
                        <td>Time</td>';

                        foreach($get_periods as $one_period)
                        {
                            $this_period_duration = date("H:i", strtotime($one_period['START_TIME'])).' - '.date("H:i", strtotime($one_period['END_TIME']));

                            echo '<th scope="col" class="table-sky-bg" style="white-space:nowrap;">'.$this_period_duration.'</th>';
                        }

                    echo '</tr>';
                    
                    // if(!empty($main_RET) && $main_RET != '')
                    // if($empty_subject_flag != 0)
                    if(!empty($get_subjects) && $get_subjects != '')
                    {
                        foreach($main_RET as $one_res)
                        {
                            echo '<tr><td colspan="'.(1 + count($get_periods)).'" class="table-black-bg">'.$one_res['subject'].'</td></tr>';

                            if(!empty($one_res['teachers']) && $one_res['teachers'] != '')
                            {
                                foreach($one_res['teachers'] as $one_teacher_res)
                                {
                                    echo '<tr>';
                                    echo '<td>'.$one_teacher_res['teacher_name'].'</td>';

                                    foreach($one_teacher_res['schedules'] as $one_teacher_sch)
                                    {
                                        $this_schedule_record = $one_teacher_sch[1];

                                        if(!empty($this_schedule_record) || $this_schedule_record != '')
                                        {
                                            echo '<td class="table-pink-bg">'.$this_schedule_record['TITLE'].'</td>';
                                        }
                                        else
                                        {
                                            echo '<td class="table-green-bg"></td>';
                                        }
                                    }

                                    echo '</tr>';
                                }
                            }
                            else
                            {
                                echo '<tr><td style="text-align:center;" colspan="'.(1 + count($get_periods)).'" class="text-center text-danger">'._noCourseFound.'</td></tr>';
                            }
                        }
                    }
                    else
                    {
                        echo '<tr><td colspan="'.(1 + count($get_periods)).'" class="text-center text-danger">'._noRecordFound.'</td></tr>';
                    }
                    
                echo '</tbody>
            </table>
        </div>';
    }
    else
    {
        echo '<div class="alert alert-danger alert-bordered">'._noRecordFound.'</div>';
    }
}
else
{
    echo '<div class="alert alert-danger alert-bordered">'._schoolCalendarIsNotSet.'</div>';
}

echo '</div>';
echo '</form>';
echo '</div>';

// echo $startdate = date('Y-m-d', strtotime('Sunday next week'));
// echo "<br>";
// echo $startdate = date('Y-m-d', strtotime('Friday next week'));

function GetCurrentWeekDates($firstDay, $lastDay, $toDay)
{
    // if (date('D') != 'Mon') {
    //     $startdate = date('Y-m-d', strtotime('last Monday'));
    // } else {
    //     $startdate = date('Y-m-d');
    // }

	//always next saturday
    // if (date('D') != 'Sat') {
    //     $enddate = date('Y-m-d', strtotime('next Saturday'));
    // } else {
    //     $enddate = date('Y-m-d');
    // }

    // if($firstDay == 'Sunday' && $lastDay != 'Saturday')
    // {
    //     $startdate = date('Y-m-d', strtotime($firstDay.' last week'));
    // }
    // else
    // {
    //     $startdate = date('Y-m-d', strtotime($firstDay.' this week'));
    // }

    // if($firstDay == 'Sunday' && $lastDay == 'Saturday')
    // {
    //     $enddate = date('Y-m-d', strtotime($lastDay.' next week'));
    // }
    // else
    // {
    //     $enddate = date('Y-m-d', strtotime($lastDay.' this week'));
    // }

    if($toDay == 'Sunday')
    {
        if($firstDay != 'Sunday')
        {
            $startdate = date('Y-m-d', strtotime($firstDay.' next week'));
        }
        else
        {
            $startdate = date('Y-m-d', strtotime($firstDay.' this week'));
        }

        $enddate = date('Y-m-d', strtotime($lastDay.' next week'));
    }
    else
    {
        if($firstDay != 'Sunday')
        {
            $startdate = date('Y-m-d', strtotime($firstDay.' this week'));
        }
        else
        {
            $startdate = date('Y-m-d', strtotime($firstDay.' last week'));
        }

        $enddate = date('Y-m-d', strtotime($lastDay.' this week'));
    }

    $DateArray = array();
    $timestamp = strtotime($startdate);
    while ($startdate < $enddate) {
        $startdate = date('Y-m-d', $timestamp);
        $DateArray[] = $startdate;
        $timestamp = strtotime('+1 days', strtotime($startdate));
    }
    return $DateArray;
}


function getStartAndEndDate($week, $year)
{
    $dto = new DateTime();
    $dto->setISODate($year, $week);
    $ret['week_start'] = $dto->format('Y-m-d');
    $dto->modify('+6 days');
    $ret['week_end'] = $dto->format('Y-m-d');
    
    return $ret;
}


function GetDaysNames($dayShort)
{
	$ret = '';

    if($dayShort == 'U')
    {
        $ret = 'Sunday';
    }
	if($dayShort == 'M')
	{
		$ret = 'Monday';
	}
	if($dayShort == 'T')
	{
		$ret = 'Tuesday';
	}
	if($dayShort == 'W')
	{
		$ret = 'Wednesday';
	}
	if($dayShort == 'H')
	{
		$ret = 'Thursday';
	}
	if($dayShort == 'F')
	{
		$ret = 'Friday';
	}
    if($dayShort == 'S')
    {
        $ret = 'Saturday';
    }

	return $ret;
}


function GetDaysShortNames($dayFull)
{
    $ret = '';

    if($dayFull == 'Sunday')
    {
        $ret = 'U';
    }
    if($dayFull == 'Monday')
    {
        $ret = 'M';
    }
    if($dayFull == 'Tuesday')
    {
        $ret = 'T';
    }
    if($dayFull == 'Wednesday')
    {
        $ret = 'W';
    }
    if($dayFull == 'Thursday')
    {
        $ret = 'H';
    }
    if($dayFull == 'Friday')
    {
        $ret = 'F';
    }
    if($dayFull == 'Saturday')
    {
        $ret = 'S';
    }

    return $ret;
}

?>
