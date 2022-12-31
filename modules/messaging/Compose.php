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
include_once("fckeditor/fckeditor.php");
include('lang/language.php');

DrawBC(""._messaging." > " . ProgramTitle());

//PopTable('header', 'Compose Message');
if(isset($_SESSION['BODY_EMPTY']) && $_SESSION['BODY_EMPTY']!='')
{
    // echo '<div class="alert bg-danger alert-styled-left">Message body cannot be empty</div>';
    echo '<div class="alert alert-danger alert-bordered"><button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">'._close.'</span></button>'._messageBodyCannotBeEmpty.'</div>';
    unset($_SESSION['BODY_EMPTY']);
}
echo '<div class="panel">';
echo '<div class="tabbable">';
echo '<ul class="nav nav-tabs nav-tabs-bottom no-margin-bottom">';
echo '<li class="active" id="tab[]"><a href="javascript:void(0);">'._compose.'&nbsp;'._message.'</a></li>';
echo '</ul>';
$userName = User('USERNAME');
$_SESSION['course_period_id'] = '';
echo "<FORM name=ComposeMail id=Compose action=Modules.php?modname=messaging/Inbox.php&count=$c  METHOD=POST enctype=multipart/form-data >";
if ($_REQUEST['modfunc'] != 'choose_course') {
    if (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher') {
        echo "<DIV id=course_div>";
    }
    if (isset($_REQUEST['mod']) && $_REQUEST['mod'] == 'draft') {
        $mail_id = $_REQUEST['mail_id'];
        $query = "select * from msg_inbox where mail_id='$mail_id'";
        $result = DBGet(DBQuery($query));

        foreach ($result as $v) {
            $to_user = $v['TO_USER'];
            $to_cc = $v['TO_CC'];
            $to_bcc = $v['TO_BCC'];
            $mail_subject = $v['MAIL_SUBJECT'];
            $mail_id = $v['MAIL_ID'];
            $mail_body = $v['MAIL_BODY'];
        }
    }
    if (!isset($_REQUEST['modto']) && !isset($_REQUEST['mod'])) {
        $to_user = '';
        $to_cc = '';
        $to_bcc = '';
        $mail_subject = '';
        $mail_id = '';
        $mail_body = '';
    }
    if (isset($_REQUEST['modto']) && $_REQUEST['m'] == 'reply') {
        $to_user = $_REQUEST['modto'];
        $mail_subject = base64_decode($_REQUEST['sub']);
    }
    echo '<div class="panel-body">';

    echo '<div class="row">';
    echo '<div class="col-md-8">';

    echo '<div class="form-group">';
    echo '<div class="input-group">';
    echo '<span class="input-group-addon">'._to.'</span>';
    echo TextInput_mail($to_user, 'txtToUser', '', 'onkeyup="nameslist(this.value,1)" autocomplete = "off" class=form-control');
    echo '</div>'; //.input-group
    echo '<ul class="dropdown-menu" id="ajax_response"></ul>';
    echo '</div>'; //.form-group

    echo '</div>'; //.col-md-8
    echo '<div class="col-md-4 form-inline">';
    echo '<div class="input-group">';
    $groupList = DBGet(DBQuery("SELECT GROUP_ID,GROUP_NAME FROM mail_group where user_name='" . $userName . "' AND SCHOOL_ID= '".UserSchool()."'"));
    echo "<SELECT name='groups' class=\"form-control\" onChange=\"list_of_groups(this.options[this.selectedIndex].value);\"><OPTION value=''>"._selectGroup."</OPTION>";
    foreach ($groupList as $groupArr) {
        $option = $groupArr['GROUP_NAME'];
        $value = $groupArr['GROUP_ID'];

        if ($_REQUEST['sel_group'] == $value)
            echo "<OPTION selected='selected' value=\"$value\">$option</OPTION>";
        else
            echo "<OPTION value=\"$option\">$option</OPTION>";
    }
    echo '</SELECT>';
    echo '<span class="input-group-btn">';
    echo '<a href="#" class="btn btn-default" onclick="show_cc()">'._cc.'</a> &nbsp; ';
    echo '<a href="#" class="btn btn-default" onclick="show_bcc()">'._bcc.'</a>';
    if (User('PROFILE') == 'teacher') {
        if (!isset($_REQUEST['modto']) && $_REQUEST['m'] != 'reply') {
            echo "<a href='#' class='btn btn-default' onclick='window.open(\"ForWindow.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=choose_course\",\"\",\"scrollbars=yes,resizable=yes,width=800,height=400\");'>"._messageMyClass."</a>";
        }
    }
    echo '</span>';
    echo '</div>'; //.input-group
    echo '</div>'; //.col-md-4
    echo '</div>'; //.row

    echo '<div id="message_my_class_div"></div>';

    echo '<div class="row">';
    echo '<div class="col-md-6" id="cc" style="display:none">';

    echo '<div class="form-group">';
    echo '<div class="input-group">';
    echo '<span class="input-group-addon">'._cc.'</span>';
    echo TextInput_mail($to_cc, 'txtToCCUser', '', 'onkeyup="nameslist(this.value,2)" class=mail_input');
    echo '</div>'; //.input-group
    echo '</div>'; //.form-group
    echo '<div id=ajax_response_cc></div>';

    echo '</div>'; //.col-md-6
    echo '<div class="col-md-6" id="bcc" style="display:none">';

    echo '<div class="form-group">';
    echo '<div class="input-group">';
    echo '<span class="input-group-addon">'._bcc.'</span>';
    echo TextInput_mail($to_bcc, 'txtToBCCUser', '', 'onkeyup="nameslist(this.value,3)" class=mail_input');
    echo '</div>'; //.input-group
    echo '</div>'; //.form-group
    echo '<div id=ajax_response_bcc></div>';

    echo '</div>'; //.col-md-6
    echo '</div>'; //.row


    echo '<div class="row">';
    echo '<div class="col-md-12">';

    echo '<div class="form-group">';
    echo TextInput_mail($mail_subject, 'txtSubj', '', 'placeholder='._subject.'');
    echo '</div>'; //.form-group
    echo '<div id=ajax_response_cc></div>';

    echo '</div>'; //.col-md-12
    echo '</div>'; //.row


    /* $oFCKeditor = new FCKeditor("txtBody") ;
      $oFCKeditor->BasePath = "modules/messaging/fckeditor/" ;
      $oFCKeditor->Value = '';
      $oFCKeditor->Height = "350px";
      $oFCKeditor->Width = "600px";
      $oFCKeditor->ToolbarSet   = 'Mytoolbar ';
      $oFCKeditor->Create() ; */
    echo '<textarea name="txtBody" id="txtBody" rows="4" cols="100"></textarea>';





    echo '<script type="text/javascript">$(function(){ CKEDITOR.replace(\'txtBody\', { height: \'400px\', extraPlugins: \'forms\'}); });</script>';

    echo '<h5>'._attachFile.'</h5>';
    echo '<div id="append_tab">';
    echo '<div id="tr1" class="form-group clearfix"><div class="col-md-4"><input type="file" name="f[]" id="up1" onchange="attachfile(1);" multiple/></div><div id="del1" class="col-md-8"><input type="button" value="'._clear.'" class="btn btn-danger btn-xs" onclick="clearfile(1)" /></div></div>';
    echo '</div>'; //#append_tab
    echo '<input type="button" style="display:none;" class="btn btn-default"  id="attach1" onclick="appendFile();" value="'._attachAnotherFile.'" />';
    
    echo '</div>'; //.panel-body
    
    echo '<div class="panel-footer"><div class="heading-elements">';
    echo '<input type=hidden id=counter value=1 />';
    echo '<button type="submit" name=button id=button class="btn btn-primary heading-btn pull-right" VALUE="'._send.'" onClick="validate_email(this);">'._send.' <i class="icon-paperplane"></i></button>';
    echo '</div></div>';
    
}
if ($_REQUEST['modfunc'] == 'choose_course') {


    if (!$_REQUEST['course_period_id']) {
        $message_my_class = 'yes';
        include 'modules/scheduling/CoursesforWindow.php';
    } else {
        $_SESSION['MassSchedule.php']['subject_id'] = $_REQUEST['subject_id'];
        $_SESSION['MassSchedule.php']['course_id'] = $_REQUEST['course_id'];
        $_SESSION['MassSchedule.php']['course_period_id'] = $_REQUEST['course_period_id'];

        $course_title = DBGet(DBQuery('SELECT TITLE FROM courses WHERE COURSE_ID=\'' . $_SESSION['MassSchedule.php']['course_id'] . '\''));
        $course_title = $course_title[1]['TITLE'];
        $period_title_RET = DBGet(DBQuery('SELECT COURSE_PERIOD_ID,TITLE,MARKING_PERIOD_ID,GENDER_RESTRICTION FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\''));
        $period_title = $period_title_RET[1]['TITLE'];
        $mperiod = $period_title_RET[1]['MARKING_PERIOD_ID'];
        $course_period_id = $period_title_RET[1]['COURSE_PERIOD_ID'];
        $_SESSION['course_period_id'] = $_REQUEST['course_period_id'];
        $grp = DBGet(DBQuery("select * from mail_group"));
        $title = trim($course_title) . ' ' . trim($period_title);
        echo "<script language=javascript>opener.document.getElementById(\"txtToUser\").value=\"$title\";opener.document.getElementById(\"ajax_response\").innerHTML='';opener.document.getElementById(\"txtToUser\").readOnly='true';opener.document.getElementById(\"message_my_class_div\").innerHTML = \"<input type=hidden name=cp_id id=cp_id value=$course_period_id><INPUT type=checkbox id=list_gpa_student name=list_gpa_student value=Y CHECKED>"._onlyStudents."<INPUT type=checkbox name=list_gpa_parent id=list_gpa_parent value=Y CHECKED>"._onlyParents."" . (User('PROFILE') != 'teacher' ? '<INPUT type=checkbox name=list_gpa_teacher id=list_gpa_teacher value=Y CHECKED>'._onlyTeachers.'' : '') . "&nbsp;&nbsp;<a href='Modules.php?modname=messaging/Compose.php'><font color='red'>"._removeCourse."</font>\";window.close();</script>";
    }
}
echo "</form>";
echo "</div>"; //.panel
?>





