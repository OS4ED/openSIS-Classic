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
include('RedirectRootInc.php'); 

	include 'Warehouse.php';
	include 'Data.php';
        if(isset($_REQUEST['email']) && $_REQUEST['email']!='')
        {
            if($_REQUEST['type']=='3')
            {
                if($_REQUEST['id']==0)
                $result_stu=DBGet(DBQuery('SELECT COUNT(1) as EMAIL_EX FROM students WHERE EMAIL=\''.$_REQUEST['email'].'\''));
                else
                $result_stu=DBGet(DBQuery('SELECT COUNT(1) as EMAIL_EX FROM students WHERE EMAIL=\''.$_REQUEST['email'].'\' AND STUDENT_ID!='.$_REQUEST['id']));    

                $result_pe=DBGet(DBQuery('SELECT COUNT(1) as EMAIL_EX FROM people WHERE EMAIL=\''.$_REQUEST['email'].'\''));
                $result_stf=DBGet(DBQuery('SELECT COUNT(1) as EMAIL_EX FROM staff WHERE EMAIL=\''.$_REQUEST['email'].'\''));
            }
            if($_REQUEST['type']=='2')
            {
                if($_REQUEST['id']==0)
                $result_stf=DBGet(DBQuery('SELECT COUNT(1) as EMAIL_EX  FROM staff WHERE EMAIL=\''.$_REQUEST['email'].'\''));
                else
                $result_stf=DBGet(DBQuery('SELECT COUNT(1) as EMAIL_EX  FROM staff WHERE EMAIL=\''.$_REQUEST['email'].'\' AND STAFF_ID!='.$_REQUEST['id']));    
                
                $result_pe=DBGet(DBQuery('SELECT COUNT(1) as EMAIL_EX FROM people WHERE EMAIL=\''.$_REQUEST['email'].'\''));
                $result_stu=DBGet(DBQuery('SELECT COUNT(1) as EMAIL_EX FROM students WHERE EMAIL=\''.$_REQUEST['email'].'\''));
            }
            
            if($_REQUEST['type']=='4')
            {
                if($_REQUEST['id']==0)
                $result_stf=DBGet(DBQuery('SELECT COUNT(1) as EMAIL_EX  FROM people WHERE EMAIL=\''.$_REQUEST['email'].'\''));
                else
                $result_stf=DBGet(DBQuery('SELECT COUNT(1) as EMAIL_EX  FROM people WHERE EMAIL=\''.$_REQUEST['email'].'\' AND STAFF_ID!='.$_REQUEST['id']));    
                
                $result_pe=DBGet(DBQuery('SELECT COUNT(1) as EMAIL_EX FROM students WHERE EMAIL=\''.$_REQUEST['email'].'\''));
                $result_stu=DBGet(DBQuery('SELECT COUNT(1) as EMAIL_EX FROM staff WHERE EMAIL=\''.$_REQUEST['email'].'\''));
            }
            
            if($result_stf[1]['EMAIL_EX']==0 && $result_pe[1]['EMAIL_EX']==0 && $result_stu[1]['EMAIL_EX']==0 )
            {
                echo '0_'.$_REQUEST['type'];
            }
            else
            {
                echo '1_'.$_REQUEST['type'];
            }
            exit;
        }
?>
