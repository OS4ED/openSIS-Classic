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
function UpdateAttendanceDaily($student_id,$date='',$comment=false)
{
	if(!$date)
		$date = DBDate();

	$current_mp=GetCurrentMP('QTR',$date);
        $MP_TYPE='QTR';
        if(!$current_mp){
            $current_mp=GetCurrentMP('SEM',$date);
            $MP_TYPE='SEM';
        }
        if(!$current_mp){
            $current_mp=GetCurrentMP('FY',$date);
            $MP_TYPE='FY';
        }
                $sql = 'SELECT
				SUM(IF(cp.HALF_DAY LIKE \'Y\',(SELECT half_day_minute FROM system_preference WHERE school_id='.UserSchool().'),sp.LENGTH)) AS TOTAL
			FROM schedule s,course_periods cp,course_period_var cpv,school_periods sp,attendance_calendar ac
			WHERE
				s.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\'
				AND ac.SCHOOL_DATE=\''.$date.'\' AND (ac.BLOCK=sp.BLOCK OR sp.BLOCK IS NULL)
                                AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
				AND ac.CALENDAR_ID=cp.CALENDAR_ID AND ac.SCHOOL_ID=s.SCHOOL_ID AND ac.SYEAR=s.SYEAR
				AND s.SYEAR = cp.SYEAR AND sp.PERIOD_ID = cpv.PERIOD_ID
				AND IF(cpv.course_period_date is null,position(substring(\'UMTWHFS\' FROM DAYOFWEEK(\''.$date.'\')  FOR 1) IN cpv.DAYS)>0,cpv.course_period_date=\''.$date.'\')
				AND s.STUDENT_ID=\''.$student_id.'\'
				AND s.SYEAR=\''.UserSyear().'\'
				AND (\''.$date.'\' BETWEEN s.START_DATE AND s.END_DATE OR (s.END_DATE IS NULL AND \''.$date.'\'>=s.START_DATE))
				AND (s.MARKING_PERIOD_ID IN ('.GetAllMP($MP_TYPE,$current_mp).') OR (s.MP=\'FY\' AND s.MARKING_PERIOD_ID IS NULL))
			';
	$RET = DBGet(DBQuery($sql));
	$total = $RET[1]['TOTAL'];
        
	if($total==0)
		return;
        $current_RET = DBGet(DBQuery('SELECT MINUTES_PRESENT,STATE_VALUE,COMMENT FROM attendance_day WHERE STUDENT_ID='.$student_id.' AND SCHOOL_DATE=\''.$date.'\''));
        $total=$current_RET['MINUTES_PRESENT'];
        
        $sql = 'SELECT SUM(IF(cp.HALF_DAY LIKE \'Y\',(SELECT half_day_minute FROM system_preference WHERE school_id='.UserSchool().'),sp.LENGTH)) AS TOTAL
			FROM attendance_period ap,school_periods sp,attendance_codes ac,course_periods cp
			WHERE ap.STUDENT_ID=\''.$student_id.'\' AND ap.SCHOOL_DATE=\''.$date.'\' AND ap.PERIOD_ID=sp.PERIOD_ID AND ac.ID = ap.ATTENDANCE_CODE AND ac.STATE_CODE=\'P\'
			AND sp.SYEAR=\''.UserSyear().'\' AND cp.COURSE_PERIOD_ID=ap.COURSE_PERIOD_ID';
	$RET = DBGet(DBQuery($sql));
	$total += $RET[1]['TOTAL'];

	$sql = 'SELECT SUM(sp.LENGTH) AS TOTAL
			FROM attendance_period ap,school_periods sp,attendance_codes ac
			WHERE ap.STUDENT_ID=\''.$student_id.'\' AND ap.SCHOOL_DATE=\''.$date.'\' AND ap.PERIOD_ID=sp.PERIOD_ID AND ac.ID = ap.ATTENDANCE_CODE AND ac.STATE_CODE=\'H\'
			AND sp.SYEAR=\''.UserSyear().'\'';
	$RET = DBGet(DBQuery($sql));
	$total += $RET[1]['TOTAL']*.5;

        if(stripos($_SERVER['SERVER_SOFTWARE'], 'linux')){
          $comment=  singleQuoteReplace("'","\'",$comment);
        }
	$sys_pref = DBGet(DBQuery('SELECT * FROM system_preference WHERE SCHOOL_ID='.UserSchool()));
	$fdm = $sys_pref[1]['FULL_DAY_MINUTE'];
	$hdm = $sys_pref[1]['HALF_DAY_MINUTE'];

	if($total>=$fdm)
		$length = '1.0';
	elseif($total>=$hdm)
		$length = '.5';
	else
		$length = '0.0';

	$current_RET = DBGet(DBQuery('SELECT MINUTES_PRESENT,STATE_VALUE,COMMENT FROM attendance_day WHERE STUDENT_ID=\''.$student_id.'\' AND SCHOOL_DATE=\''.$date.'\''));
        if(count($current_RET) && $current_RET[1]['MINUTES_PRESENT']==$total && $length!=$current_RET[1]['STATE_VALUE'])
		DBQuery('UPDATE attendance_day SET STATE_VALUE=\''.$length.'\' WHERE STUDENT_ID=\''.$student_id.'\' AND SCHOOL_DATE=\''.$date.'\'');
	if(count($current_RET) && $current_RET[1]['MINUTES_PRESENT']!=$total)
		DBQuery('UPDATE attendance_day SET MINUTES_PRESENT=\''.$total.'\',STATE_VALUE=\''.$length.'\''.($comment!=false?',COMMENT=\''.singleQuoteReplace("","",$comment).'\'':'').' WHERE STUDENT_ID=\''.$student_id.'\' AND SCHOOL_DATE=\''.$date.'\'');
        elseif(count($current_RET) && $comment!=false && $current_RET[1]['COMMENT']!=$comment)
		DBQuery('UPDATE attendance_day SET COMMENT=\''.singleQuoteReplace("","",$comment).'\' WHERE STUDENT_ID=\''.$student_id.'\' AND SCHOOL_DATE=\''.$date.'\'');
	elseif(count($current_RET)==0)
        {
                $check_assoc=DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM attendance_period ap,course_periods cp WHERE ap.STUDENT_ID='.$student_id.' AND ap.SCHOOL_DATE=\''.$date.'\' AND cp.COURSE_PERIOD_ID=ap.COURSE_PERIOD_ID AND cp.SCHOOL_ID='.UserSchool().' AND cp.SYEAR='.UserSyear()));
                if($check_assoc[1]['REC_EX']>0)
                DBQuery('INSERT INTO attendance_day (SYEAR,STUDENT_ID,SCHOOL_DATE,MINUTES_PRESENT,STATE_VALUE,MARKING_PERIOD_ID,COMMENT) values(\''.UserSyear().'\',\''.$student_id.'\',\''.$date.'\',\''.$total.'\',\''.$length.'\',\''.$current_mp.'\',\''.singleQuoteReplace("","",$comment).'\')');
        }
}

?>