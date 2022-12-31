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
DrawBC(""._users." > " . ProgramTitle());
$menu = array();
include 'Menu.php';
if (is_numeric(clean_param($_REQUEST['profile_id'], PARAM_INT))) {
    $exceptions_RET = DBGet(DBQuery('SELECT PROFILE_ID,MODNAME,CAN_USE,CAN_EDIT FROM profile_exceptions WHERE PROFILE_ID=\'' . $_REQUEST['profile_id'] . '\''), array(), array('MODNAME'));
    $profile_RET = DBGet(DBQuery('SELECT PROFILE FROM user_profiles WHERE ID=\'' . $_REQUEST['profile_id'] . '\''));
    $xprofile = $profile_RET[1]['PROFILE'];
    if ($xprofile == 'student') {
        $xprofile = 'parent';
        unset($menu['users']);
    }
}
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'delete' && AllowEdit()) {
    $profile_RET = DBGet(DBQuery('SELECT TITLE FROM user_profiles WHERE ID=\'' . $_REQUEST['profile_id'] . '\''));
    $profile = $profile_RET[1]['TITLE'];
    if (DeletePromptBigString("profile - <i>$profile</i>,<br/>users of that profile will retain their permissions as a custom set which can be modified on a per-user basis through the User Permissions program")) {
        $existStaff = DBGet(DBQuery("select * from staff where profile_id=$_REQUEST[profile_id]"));
        if (count($existStaff) == 0) {
            DBQuery('DELETE FROM user_profiles WHERE ID=\'' . $_REQUEST['profile_id'] . '\'');

            DBQuery('DELETE FROM profile_exceptions WHERE PROFILE_ID=\'' . $_REQUEST['profile_id'] . '\'');
            unset($_REQUEST['modfunc']);
            unset($_REQUEST['profile_id']);
        } else {
            echo '<BR>';
            PopTable('header',  _alertMessage);
            echo "<CENTER><h4>Cannot delete because profile is associated with staff.</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=button class='btn btn-primary' name=delete_cancel value="._ok." onclick='window.location=\"Modules.php?modname=$_REQUEST[modname] \"'></FORM></CENTER>";
            PopTable('footer');
            return false;
        }
    }
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'update' && AllowEdit() && !$_REQUEST['new_profile_title']) {
    $tmp_menu = $menuprof;
    $categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM student_field_categories'));
    foreach ($categories_RET as $category) {
        $file = 'students/Student.php&category_id=' . $category['ID'];
        $tmp_menu['students'][$xprofile][$file] = ' &nbsp; &nbsp; &rsaquo; ' . $category['TITLE'];
    }
    $categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM people_field_categories'));
    foreach ($categories_RET as $category) {
        $file = 'users/User.php&category_id=' . $category['ID'];
        $tmp_menu['users'][$xprofile][$file] = ' &nbsp; &nbsp; &rsaquo; ' . $category['TITLE'];
    }
    $categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM  staff_field_categories'));
    foreach ($categories_RET as $category) {
        $file = 'users/Staff.php&category_id=' . $category['ID'];
        $tmp_menu['users'][$xprofile][$file] = ' &nbsp; &nbsp; &rsaquo; ' . $category['TITLE'];
    }

    foreach ($tmp_menu as $modcat => $profiles) {
        $values = $profiles[$xprofile];
        foreach ($values as $modname => $title) {
            if (!is_numeric($modname)) {
            $expvalarrcnt = (is_countable($exceptions_RET[$modname])) ? count($exceptions_RET[$modname]) : 0;
                if (!$expvalarrcnt && ($_REQUEST['can_edit'][str_replace('.', '_', $modname)] || $_REQUEST['can_use'][str_replace('.', '_', $modname)])) {
                    DBQuery('INSERT INTO profile_exceptions (PROFILE_ID,MODNAME) values(\'' . $_REQUEST['profile_id'] . '\',\'' . $modname . '\')');
                } elseif (is_countable($exceptions_RET[$modname]) && count($exceptions_RET[$modname]) && !$_REQUEST['can_edit'][str_replace('.', '_', $modname)] && !$_REQUEST['can_use'][str_replace('.', '_', $modname)])
                    DBQuery('DELETE FROM profile_exceptions WHERE PROFILE_ID=\'' . $_REQUEST['profile_id'] . '\' AND MODNAME=\'' . $modname . '\'');

                if ($_REQUEST['can_edit'][str_replace('.', '_', $modname)] || $_REQUEST['can_use'][str_replace('.', '_', $modname)]) {
                    $update = 'UPDATE profile_exceptions SET ';
                    if ($_REQUEST['can_edit'][str_replace('.', '_', $modname)])
                        $update .= 'CAN_EDIT=\'' . 'Y' . '\',';
                    else
                        $update .= 'CAN_EDIT=NULL,';
                    if ($_REQUEST['can_use'][str_replace('.', '_', $modname)])
                        $update .= 'CAN_USE=\'' . 'Y' . '\'';
                    else
                        $update .= 'CAN_USE=NULL';
                    $update .= ' WHERE PROFILE_ID=\'' . $_REQUEST['profile_id'] . '\' AND MODNAME=\'' . $modname . '\';';
                    DBQuery($update);
                }
            }
        }
    }
    $exceptions_RET = DBGet(DBQuery('SELECT MODNAME,CAN_USE,CAN_EDIT FROM profile_exceptions WHERE PROFILE_ID=\'' . $_REQUEST['profile_id'] . '\''), array(), array('MODNAME'));
    unset($tmp_menu);
    unset($_REQUEST['modfunc']);
    unset($_REQUEST['can_edit']);
    unset($_REQUEST['can_use']);
}

if (clean_param($_REQUEST['new_profile_title'], PARAM_NOTAGS) && AllowEdit()) {

    // $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'' . 'user_profiles' . '\''));
    // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
    // $id = $id[1]['ID'];
    $exceptions_RET = array();
    $_REQUEST['new_profile_title'] = str_replace("'", "''", $_REQUEST['new_profile_title']);
    DBQuery('INSERT INTO user_profiles (TITLE,PROFILE) values(\'' . clean_param($_REQUEST['new_profile_title'], PARAM_NOTAGS) . '\',\'' . clean_param($_REQUEST['new_profile_type'], PARAM_ALPHA) . '\')');
    $_REQUEST['profile_id'] = mysqli_insert_id($connection);
    $xprofile = $_REQUEST['new_profile_type'];
    unset($_REQUEST['new_profile_title']);
    unset($_REQUEST['new_profile_type']);
    unset($_SESSION['_REQUEST_vars']['new_profile_title']);
    unset($_SESSION['_REQUEST_vars']['new_profile_type']);
}

if ($_REQUEST['modfunc'] != 'delete') {

    PopTable('header',  _permissions);
    echo "<FORM class=\"form-horizontal\" name=pref_form id=pref_form action=Modules.php?modname=$_REQUEST[modname]&modfunc=update&profile_id=$_REQUEST[profile_id] method=POST>";
    echo '<p class="text-muted">'._selectTheProgramsThatUsersOfThisProfileCanUseAndWhichProgramsThoseUsersCanUseToSaveInformation.'.</p>';

    echo '<div class="row"><div class="col-md-3">';
    
    if (AllowEdit()) {
        echo '<a  href="javascript:void(0);" class="btn btn-success btn-block m-b-15" onclick="$(\'#new_id_content\').toggle();"><i class="icon-plus3"></i> '._addAUserProfile.'</a>';
    }
    echo '<table class="table bg-primary">';
    $style = '';
    $style1 = '';

    if (User('PROFILE_ID') == 1)
        $where_ex = ' WHERE ID<>0 ';
    $profiles_RET = DBGet(DBQuery('SELECT ID,TITLE,PROFILE,IF(ID=4,-1,ID) as ORDER_ID FROM user_profiles ' . $where_ex . ' ORDER BY ORDER_ID'), array(), array('PROFILE', 'ID'));
    echo '<thead><tr><th colspan=3 class="text-uppercase">'._profiles.'</th></tr></thead>';
    echo '<tbody>';
    foreach (array('admin', 'teacher', 'parent', 'student') as $profiles) {
        foreach ($profiles_RET[$profiles] as $id => $profile) {
            if ($_REQUEST['profile_id'] != '' && $id == $_REQUEST['profile_id'])
             echo '<TR id=selected_tr class="bg-primary-800"\'><TD width=20 class="p-10" align=right'.$style.'>'.(AllowEdit()&&$id>4&&$id!=0?'<a href="Modules.php?modname=$_REQUEST[modname]&modfunc=delete&profile_id=$id" class="btn  btn-default btn-xs p-5"><i class="icon-cross2"></i></a>':'').'</TD><TD '.$style.' onclick="document.location.href=\'Modules.php?modname='.$_REQUEST['modname'].'&profile_id='.$id.'\';">';
		
            else
                echo '<TR><TD width=20 align=right class="p-10">' . (AllowEdit() && $id > 4 && $id != 0 ? '<a href="Modules.php?modname='.$_REQUEST['modname'].'&modfunc=delete&profile_id='.$id.'" class="btn btn-default btn-xs p-5"><i class="icon-cross2"></i></a>' : '') . '</TD><TD onclick="document.location.href=\'Modules.php?modname=' . $_REQUEST['modname'] . '&profile_id=' . $id . '\';">';
                
            if($profile[1]['TITLE'] == "Super Administrator")
            {
            $profile[1]['TITLE'] = _superAdministrator;
            
            }elseif($profile[1]['TITLE'] == "Administrator")
            {
                $profile[1]['TITLE'] = _administrator;
                
            }elseif($profile[1]['TITLE'] == "Teacher")
            {
                $profile[1]['TITLE'] = _teacher;
                
            }elseif($profile[1]['TITLE'] == "Student")
            {
                $profile[1]['TITLE'] = _student;
                    
            }elseif($profile[1]['TITLE'] == "Parent"){
                        $profile[1]['TITLE'] = _parent;
                        
            }elseif($profile[1]['TITLE'] == "Admin Asst")
            {
            $profile[1]['TITLE'] = _adminAsst;
                        
            }
            echo '<a href="javascript:void(0);" class="text-white">' . ($id > 4 ? '' : '<b>') . $profile[1]['TITLE'] . ($id > 4 ? '' : '</b>') . '</a>';
            echo '</TD>';
            echo '<TD><A href="javascript:void(0);" class="text-white"><i class="fa fa-caret-right"></i></A></TD>';
            echo '</TR>';
        }
    }
	//if ($_REQUEST['profile_id'] == '')
		//echo '<TR id=selected_tr><TD height=0></TD></TR>';

//	    if (AllowEdit()) {
//		echo '<TR id=new_tr><TD colspan=3' . $style1 . '>';
//		echo '<a href="javascript:void(0)" onclick="document.getElementById(\'selected_tr\').onmouseover=\'this.style.backgroundColor=' . Preferences('HIGHLIGHT') . '; this.style.color=\'white\'; document.getElementById(\'selected_tr\').onmouseout=this.style.cssText=\'background-color:transparent; color:black;\'; document.getElementById(\'selected_tr\').style.cssText=\'background-color:transparent; color:black;\'; changeHTML({\'new_id_div\':\'new_id_content\'},[\'main_div\']);document.getElementById(\'new_tr\').onmouseover=\'\';document.getElementById(\'new_tr\').onmouseout=\'\';this.onclick=\'\';">'._addAUserProfile.'</a>';
//		echo '</TD>';
//
//		echo '</TR>';
//	    }	
	    echo '</tbody>';
	    echo '</table>';
	    //if ($_REQUEST['profile_id'] == '')
	    //echo '<TR id=selected_tr><TD height=0></TD></TR>';

    
    if ($_REQUEST['profile_id'] == 3) {
        unset($menuprof['users']);
    }

    echo '</div>'; //.col-md-3
    echo '<div class="col-md-9">';
    
    echo '<DIV id=new_id_content style="display:none;"><div class="row"><div class="col-md-8 col-md-offset-2">';
    echo '<div class="panel panel-default">';
    echo '<div class="panel-heading"><h4 class="panel-title">'._addAUserProfile.'</h4></div>';
    echo '<div class="panel-body">';
    echo '<div class="form-group">';
    echo '<label class="control-label col-md-3 text-right">'._title.' </label>';
    echo '<div class="col-md-9">';
    echo '<INPUT type=text name=new_profile_title maxlength=20 class="form-control">';
    echo '</div>';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label class="control-label col-md-3 text-right">Type </label>';
    echo '<div class="col-md-9">';
    echo '<SELECT name=new_profile_type class=form-control><OPTION value=admin>Administrator<OPTION value=teacher>Teacher<OPTION value=parent>Parent</SELECT>';
    echo '</div>';
    echo '</div>';
    echo '<div class="text-right"><input type=submit value='._save.' class="btn btn-primary" onclick="self_disable(this);"></div>';
    echo '</div>'; //.panel-body
    echo '</div>'; //.panel
    echo '</div></div></DIV>';
    
    echo '<DIV id=main_div>';
    if ($_REQUEST['profile_id'] != '') {

        echo '<TABLE class="table">';
        echo '<thead><TR><TD colspan=5 class="text-uppercase"><b>'._permissions.'</b></TD></TR></thead>';
        foreach ($menuprof as $modcat => $profiles) {
            $values = $profiles[$xprofile];
            if ($modcat == 'eligibility')
                $modcat = 'Extracurricular';
            echo '<TR><TD valign=top class="bg-primary">';
            $modname = $modcat;
            switch ($modcat) {
                case 'schoolsetup':
                    $modname = 'schoolSetup';
                    break;
                
                case 'Extracurricular':
                    $modname = 'extracurricular';
                    break;

                default:
                    break;
            }
            echo "<b>" . ucwords(str_replace('_', ' ', constant("_".$modname))) . "</b></TD><TD width=3 class=\"bg-primary\">&nbsp;</TD>";
            if ($modcat == 'Extracurricular')
                echo "<td class=\"bg-primary\" style='white-space: nowrap; padding-top:2px; padding-bottom: 2px;'><label class=\"checkbox checkbox-inline checkbox-switch switch-warning switch-xs\">" . (AllowEdit() ? "<INPUT type=checkbox name=can_use_eligibility onclick='checkAll(this.form,this.form.can_use_eligibility.checked,\"can_use[eligibility\");'>" : '') . "<span></span>"._canUse."</label></td>";
            else
                echo "<td class=\"bg-primary\" style='white-space: nowrap; padding-top:2px; padding-bottom: 2px;'><label class=\"checkbox checkbox-inline checkbox-switch switch-warning switch-xs\">" . (AllowEdit() ? "<INPUT type=checkbox name=can_use_$modcat onclick='checkAll(this.form,this.form.can_use_$modcat.checked,\"can_use[$modcat\");'>" : '') . "<span></span>"._canUse."</label></td>";
            $profile_id = $_REQUEST['profile_id'];
            $sql_profile = "SELECT PROFILE FROM user_profiles WHERE ID='$profile_id'";
            $res = DBGet(DBQuery($sql_profile));

            if (($xprofile == 'admin' || $modcat == 'students') && $_REQUEST['profile_id'] != 4 && $res[1]['PROFILE'] != 'parent') {
                if ($modcat == 'Extracurricular')
                    echo"<td class=\"bg-primary\" style='white-space: nowrap; padding-top:2px; padding-bottom: 2px;' ><label class=\"checkbox checkbox-inline checkbox-switch switch-warning switch-xs\">" . (AllowEdit() ? "<INPUT type=checkbox name=can_edit_eligibility onclick='checkAll(this.form,this.form.can_edit_eligibility.checked,\"can_edit[eligibility\");'>" : '') . "<span></span>"._canEdit."</label></td>";
                else
                    echo"<td class=\"bg-primary\" style='white-space: nowrap; padding-top:2px; padding-bottom: 2px;' ><label class=\"checkbox checkbox-inline checkbox-switch switch-warning switch-xs\">" . (AllowEdit() ? "<INPUT type=checkbox name=can_edit_$modcat onclick='checkAll(this.form,this.form.can_edit_$modcat.checked,\"can_edit[$modcat\");'>" : '') . "<span></span>"._canEdit."</label></td>";
            } else
                echo"<td class=\"bg-primary\"></td>";
            echo "<td class=\"bg-primary\"></td></TR>";
            if (is_countable($values) && count($values)) {
                foreach ($values as $file => $title) {
                    
                    if ($_REQUEST['profile_id'] != 0 && $xprofile == 'admin' && $modcat == 'tools' && ($title == 'Backup Database' || $title == 'Reports' || $title == 'At a Glance' || $title == 'Institute Reports' || $title == 'Institute Custom Field Reports'))
                        continue;
                    if (!is_numeric($file)) {
                        $can_use = $exceptions_RET[$file][1]['CAN_USE'];
                        $can_edit = $exceptions_RET[$file][1]['CAN_EDIT'];

                        echo "<TR><TD colspan=2></TD>";

                        echo "<TD><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_use[" . str_replace('.', '_', $file) . "] value=true" . ($can_use == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";
                        if ($xprofile == 'admin')
                            echo "<TD><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_edit[" . str_replace('.', '_', $file) . "] value=true" . ($can_edit == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";
                        elseif ($xprofile == 'parent' && $file == 'scheduling/Requests.php')
                            echo "<TD><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_edit[" . str_replace('.', '_', $file) . "] value=true" . ($can_edit == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";
                        else
                            echo "<TD align=center></TD>";

                        echo "<TD>$title</TD></TR>";

                        if ($modcat == 'students' && $file == 'students/Student.php') {
                            $categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM student_field_categories ORDER BY SORT_ORDER,TITLE'));

                            foreach ($categories_RET as $category) {

                                $file = 'students/Student.php&category_id=' . $category['ID'];
                                $title = ' &nbsp; &nbsp; &rsaquo; ' . $category['TITLE'];

                                $can_use = $exceptions_RET[$file][1]['CAN_USE'];
                                $can_edit = $exceptions_RET[$file][1]['CAN_EDIT'];

                                echo "<TR><TD colspan=2></TD>";
                                echo "<TD align=left><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_use[" . str_replace('.', '_', $file) . "] value=true" . ($can_use == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";
                                $profile_id = $_REQUEST['profile_id'];
                                $sql_profile = "SELECT PROFILE FROM user_profiles WHERE ID='$profile_id'";
                                $res = DBGet(DBQuery($sql_profile));

                                if (($_REQUEST['profile_id'] != 4 || $res[1]['PROFILE'] != 'parent') && ($category['ID'] != 4 && $category['ID'] != 5 && $category['ID'] != 6)) { //&& $category['ID']!=5
                                    if ($_REQUEST['profile_id'] != 2)
                                        echo "<TD align=left><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_edit[" . str_replace('.', '_', $file) . "] value=true" . ($can_edit == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";
                                    else {
                                        $ar = array(1, 2, 3, 6);
                                        if (!in_array($category['ID'], $ar)) {

                                            echo "<TD align=left><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_edit[" . str_replace('.', '_', $file) . "] value=true" . ($can_edit == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";
                                        } else
                                            echo "<TD></TD>";
                                    }
                                }
                                else if (($_REQUEST['profile_id'] != 4 || ($res[1]['PROFILE'] != 'parent' && $res[1]['PROFILE'] != 'admin')) && ($category['ID'] == 4 || $category['ID'] == 5)) { //&& $category['ID']!=5
                                    if ($_REQUEST['profile_id'] == 2 || $res[1]['PROFILE'] == 'admin')
                                        echo "<TD align=left><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_edit[" . str_replace('.', '_', $file) . "] value=true" . ($can_edit == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";
                                    else
                                        echo "<TD></TD>";
                                }
                                else if ($_REQUEST['profile_id'] == 4) {

                                    $ar = array(1, 2, 3, 7);
                                    if (in_array($category['ID'], $ar)) {

                                        echo "<TD align=left><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_edit[" . str_replace('.', '_', $file) . "] value=true" . ($can_edit == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";
                                    } else
                                        echo "<TD></TD>";
                                }



                                else {
                                    if ($res[1]['PROFILE'] == 'admin')
                                        echo "<TD align=left><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_edit[" . str_replace('.', '_', $file) . "] value=true" . ($can_edit == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";
                                    else
                                        echo "<TD></TD>";
                                }


                                echo "<TD >$title</TD></TR>";
                            }
                        }
                        elseif ($modcat == 'users' && $file == 'users/User.php') {
                            $categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM people_field_categories ORDER BY SORT_ORDER,TITLE'));
                            foreach ($categories_RET as $category) {
                                $file = 'users/User.php&category_id=' . $category['ID'];
                                $title = ' &nbsp; &nbsp; &rsaquo; ' . $category['TITLE'];
                                $can_use = $exceptions_RET[$file][1]['CAN_USE'];
                                $can_edit = $exceptions_RET[$file][1]['CAN_EDIT'];

                                echo "<TR><TD colspan=2></TD>";
                                echo "<TD><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_use[" . str_replace('.', '_', $file) . "] value=true" . ($can_use == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";

                                echo "<TD><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_edit[" . str_replace('.', '_', $file) . "] value=true" . ($can_edit == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";
                                echo "<TD style='white-space: nowrap;'>$title</TD></TR>";
                            }
                        } elseif ($modcat == 'users' && $file == 'users/Staff.php') {
                            $categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM  staff_field_categories ORDER BY SORT_ORDER,TITLE'));
                            foreach ($categories_RET as $category) {
                                $file = 'users/Staff.php&category_id=' . $category['ID'];
                                $title = ' &nbsp; &nbsp; &rsaquo; ' . $category['TITLE'];
                                $can_use = $exceptions_RET[$file][1]['CAN_USE'];
                                $can_edit = $exceptions_RET[$file][1]['CAN_EDIT'];

                                echo "<TR><TD></TD><TD></TD>";
                                echo "<TD align=left><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_use[" . str_replace('.', '_', $file) . "] value=true" . ($can_use == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";
                                if (($category['ID'] == '3' || $category['ID'] == '5' ) && $res[1]['PROFILE'] != 'admin')
                                    echo "<TD align=left style='padding:0px 0px 0px 47px'>&nbsp;</TD>";
                                else
                                    echo "<TD align=left><label class=\"checkbox checkbox-inline checkbox-switch switch-success switch-xs\"><INPUT type=checkbox name=can_edit[" . str_replace('.', '_', $file) . "] value=true" . ($can_edit == 'Y' ? ' CHECKED' : '') . (AllowEdit() ? '' : ' DISABLED') . "><span></span></label></TD>";
                                echo "<TD style='white-space: nowrap;'> &nbsp; &nbsp;$title</TD></TR>";
                            }
                        }
                    } else
                        echo '<TR class="alpha-primary"><TD colspan=2></TD><TD colspan=3 class="text-right"><b> ' . $title . ' </b></TD></TR>';
                }
            }
        }
        echo '</TABLE>';

        echo '<div class="panel-footer text-right">' . SubmitButton(_save, '', 'class="btn btn-primary" onclick="self_disable(this);"') . '</div>';
    }
    echo '</DIV>';

    echo '</div>'; //.col-md-9
    echo '</div>'; //.row
    echo '</FORM>';
    PopTable('footer');
    
    
}
?>
