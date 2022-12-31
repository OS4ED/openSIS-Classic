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
error_reporting(0);


include('RedirectRootInc.php');
include 'Warehouse.php';
include 'Data.php';
   $sql="CREATE TEMPORARY table temp_cum_gpa AS
    SELECT  * FROM student_report_card_grades srcg WHERE credit_attempted=
    (SELECT MAX(credit_attempted) FROM student_report_card_grades srcg1 WHERE srcg.course_period_id=srcg1.course_period_id and srcg.student_id=srcg1.student_id AND srcg1.course_period_id IS NOT NULL) 
        GROUP BY course_period_id,student_id,marking_period_id
     UNION SELECT * FROM student_report_card_grades WHERE course_period_id IS NULL AND report_card_grade_id IS NULL;";   //);
    $sql.="DROP TABLE IF EXISTS tmp;";
        $sql.="SELECT CALC_CUM_GPA_MP('".$_REQUEST['mp']."');";
         $sql.="DROP TABLE IF EXISTS tmp;";
         $sql.="SELECT SET_CLASS_RANK_MP('".$_REQUEST['mp']."');";
         
         if(mysqli_multi_query($connection,$sql))
         {
            echo '<br/><table><tr><td width="38"><img src="assets/icon_ok.png" /></td><td valign="middle"><span style="font-size:14px;">'._theGradesFor.' '.GetMP($_REQUEST['mp']).' '._hasBeenCalculated.'.</span></td></tr></table>';   
         }
        
         unset($_REQUEST['modfunc']);   

?>
