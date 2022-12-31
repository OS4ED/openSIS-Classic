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
include_once("../../functions/PasswordHashFnc.php");
DrawBC(""._schoolSetup." > " . ProgramTitle());
if ((clean_param($_REQUEST['action'], PARAM_ALPHAMOD) == 'update') && (clean_param($_REQUEST['button'], PARAM_ALPHAMOD) == 'Save') && (User('PROFILE') == 'parent' || User('PROFILE') == 'student')) {
    $stu_PASS = DBGet(DBQuery('SELECT la.PASSWORD FROM login_authentication la, students s WHERE s.STUDENT_ID=\'' . UserStudentId() . '\' AND la.USER_ID=s.STUDENT_ID AND la.PROFILE_ID=3'));
    $pass_old = $_REQUEST['old'];
    if ($pass_old == "") {
        $error[] = _pleaseTypeThePassword;
        echo ErrorMessage($error, 'Error');
    } else {
        $column_name = _password;
        $pass_old = paramlib_validation($column_name, $_REQUEST['old']);
        $pass_new = paramlib_validation($column_name, $_REQUEST['new']);

        $pass_retype = paramlib_validation($column_name, $_REQUEST['retype']);

        $pass_old = str_replace("\'", "''",($pass_old));
        $pass_new = str_replace("\'", "''",($pass_new));
        $pass_retype = str_replace("\'", "''",($pass_retype));

        //code for match old password
        $student_old_pass =  $stu_PASS[1]['PASSWORD'];
        $old_password_status = VerifyHash($pass_old,$student_old_pass); //match password
        //end 
        
        //code for match new password & retype password
        $pass_new_hash = GenerateNewHash($pass_new); //converted New password in hash
        $pass_verify_status = VerifyHash($pass_retype,$pass_new_hash);
        //end

        /*if ($stu_PASS[1]['PASSWORD'] == $pass_old) */
        if($old_password_status == 1)
        {
            /*if ($pass_new == $pass_retype) */
            if ($pass_verify_status == 1) 
            {
                $sql = 'UPDATE login_authentication SET PASSWORD=\'' . $pass_new_hash . '\' WHERE USER_ID=\'' . UserStudentId() . '\' AND PROFILE_ID=3 ';
                DBQuery($sql);
                $note[] = _passwordSucessfullyChanged;
                echo ErrorMessage($note, 'note');
            } else {
                $error[] = _pleaseRetypePassword;
                echo ErrorMessage($error, 'Error');
            }
        } else {
            $error[] = _oldPasswordIsIncorrect;
            echo ErrorMessage($error, 'Error');
        }
    }
}

echo "<span id='error' name='error'></span>";
echo "<FORM name=change_password class=form-horizontal id=change_password action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname]). "&action=update") . " method=POST>";
PopTable('header',  _changePassword);

echo '<div class="row">';
echo '<div class="col-lg-8">';
echo '<div class="form-group">';
echo '<label class="control-label col-md-3 col-lg-3">'._oldPassword.'</label>';
echo '<div class="col-md-5 col-lg-5">';
echo '<INPUT type="password" class="form-control" name="old" AUTOCOMPLETE="off" placeholder="'._enterOldPassword.'" />';
echo '</div>'; //.col-md-7
echo '</div>'; //.form-group
echo '</div>'; //.col-md-5
echo '</div>'; //.row

echo '<div class="row">';
echo '<div class="col-lg-8">';
echo '<div class="form-group">';
echo '<label class="control-label col-md-3 col-lg-3">'._newPassword.'</label>';
echo '<div class="col-md-5 col-lg-5">';
echo '<INPUT type="password" id="new_pass" class="form-control" name="new" placeholder="'._enterNewPassword.'" AUTOCOMPLETE="off" onkeyup="passwordStrength(this.value);passwordMatch();">';
echo '</div>'; //.col-md-5
echo '<div class="col-md-4 col-lg-4 no-margin-top">';
echo '<p class="help-block mt-10" id=passwordStrength></p>';
echo '</div>'; //.col-md-3
echo '</div>'; //.form-group
echo '</div>'; //.col-md-5
echo '</div>'; //.row

echo '<div class="row">';
echo '<div class="col-lg-8">';
echo '<div class="form-group">';
echo '<label class="control-label col-md-3 col-lg-3">'._retypePassword.'</label>';
echo '<div class=" col-md-5 col-lg-5">';
echo '<INPUT type="password" id="ver_pass" class="form-control" name="retype" placeholder="'._retypeNewPassword.'" AUTOCOMPLETE="off" onkeyup="passwordMatch();">';
echo '</div>'; //.col-md-5
echo '<div class="col-md-4 col-lg-4 no-margin-top">';
echo '<p class="help-block mt-10" id="passwordMatch"></p>';
echo '</div>'; //.col-md-3
echo '</div>'; //.form-group
echo '</div>'; //.col-md-5
echo '</div>'; //.row

PopTable('footer', '<INPUT TYPE="SUBMIT" name="button" id="button" class="btn btn-primary heading-btn pull-right" value="'._save.'" AUTOCOMPLETE="off" onclick="return change_pass();">');
echo "</FORM>";
?>
