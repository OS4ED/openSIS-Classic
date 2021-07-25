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

	$count_RET = DBGet(DBQuery('SELECT cs.TITLE as SUBJECT_TITLE,c.TITLE as COURSE_TITLE,sr.COURSE_ID,COUNT(*) AS COUNT,(SELECT (sum(TOTAL_SEATS)-sum(filled_seats)) AS SEATS FROM course_periods cp,course_period_var cpv WHERE IF(sr.COURSE_ID IS NOT NULL AND sr.COURSE_ID<>0,cp.COURSE_ID=sr.COURSE_ID,cp.COURSE_ID) AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND IF(sr.WITH_PERIOD_ID IS NOT NULL AND sr.WITH_PERIOD_ID<>0,cpv.PERIOD_ID =sr.WITH_PERIOD_ID,cpv.PERIOD_ID) ) AS SEATS FROM schedule_requests sr,courses c,course_subjects cs WHERE cs.SUBJECT_ID=c.SUBJECT_ID AND sr.COURSE_ID=c.COURSE_ID AND sr.SYEAR=\''.UserSyear().'\' AND sr.SCHOOL_ID=\''.UserSchool().'\' AND sr.MARKING_PERIOD_ID=\''.UserMP().'\'  GROUP BY sr.COURSE_ID,cs.TITLE,c.TITLE'),array(),array('SUBJECT_TITLE'));
        $columns = array('SUBJECT_TITLE'=>_subject,'COURSE_TITLE'=>_course,'COUNT'=>_numberOfRequests,'SEATS'=>_seats);
	
	DrawBC(""._scheduling." > ".ProgramTitle());
        echo '<div class="panel panel-default">';
	ListOutput($count_RET,$columns,  _request, _requests,array(),array(array('SUBJECT_TITLE')));
        echo '</div>';
?>