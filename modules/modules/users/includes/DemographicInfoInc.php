<?php

#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include staff demographic info, scheduling, grade book, attendance,
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
#########################################################ENROLLMENT##############################################

echo '<div class="row">';

echo '<div class="col-md-10">';

$_SESSION['staff_selected'] = $staff['STAFF_ID'];

if ($_REQUEST['staff_id'] == 'new') {
    echo '<div class="form-group">';
    echo '<div class=col-md-2><label>'._salutation.'</label>' . SelectInput($staff['TITLE'], 'staff[TITLE]', '', array('Mr.' => 'Mr.', 'Mrs.' => 'Mrs.', 'Ms.' => 'Ms.', 'Miss' => 'Miss', 'Dr' => 'Dr', 'Rev' => 'Rev'), '') . '</div><div class="col-md-3"><label>'._firstName.'</label>' . TextInput($staff['FIRST_NAME'], 'staff[FIRST_NAME]', '', 'maxlength=50') . '</div><div class="col-md-3"><label>'._middleName.'</label>' . TextInput($staff['MIDDLE_NAME'], 'staff[MIDDLE_NAME]', '', 'maxlength=50') . '</div><div class="col-md-3"><label>'._lastName.'</label>' . TextInput($staff['LAST_NAME'], 'staff[LAST_NAME]', '', 'maxlength=50 class=cell_floating') . '</div><div class="col-md-1"><label>Suffix</label>' . SelectInput($staff['NAME_SUFFIX'], 'staff[NAME_SUFFIX]', '', array('Jr.' => 'Jr.', 'Sr.' => 'Sr.', 'II' => 'II', 'III' => 'III', 'IV' => 'IV', 'V' => 'V'), '', '') . '</div>';
    echo '</div>'; //.form-group    
} else {
    echo '<div class="form-group" id="user_name">';
    echo '<div onclick=\'addHTML("<div class=col-md-2><div><label for=staff[TITLE]>'._salutation.'</label>' . str_replace('"', '\"', SelectInput($staff['TITLE'], 'staff[TITLE]', '', array('Mr.' => 'Mr.', 'Mrs.' => 'Mrs.', 'Ms.' => 'Ms.', 'Miss' => 'Miss', 'Dr' => 'Dr', 'Rev' => 'Rev'), '', '', false)) . '</div></div><div class=col-md-3><label for=staff[FIRST_NAME]>' . (!$staff['FIRST_NAME'] ? '<span class="text-danger">' : '') . _firstName . (!$staff['FIRST_NAME'] ? '</span>' : '') . '</label>' . str_replace('"', '\"', TextInput(trim($staff['FIRST_NAME']), 'staff[FIRST_NAME]', '', 'maxlength=50', false)) . '</div><div class=col-md-3><label for=staff[MIDDLE_NAME]>'._middleName.'</label>' . str_replace('"', '\"', TextInput($staff['MIDDLE_NAME'], 'staff[MIDDLE_NAME]', '', 'size=3 maxlength=50', false)) . '</div><div class=col-md-3><label for=staff[LAST_NAME]>' . (!$staff['LAST_NAME'] ? '<span class="text-danger">' : '') . 'Last Name' . (!$staff['LAST_NAME'] ? '</span>' : '') . '</label>' . str_replace('"', '\"', TextInput(trim($staff['LAST_NAME']), 'staff[LAST_NAME]', '', 'maxlength=50', false)) . '</div><div class=col-md-1><label for=staff[NAME_SUFFIX]>Suffix</label>' . str_replace('"', '\"', SelectInput($staff['NAME_SUFFIX'], 'staff[NAME_SUFFIX]', '', array('Jr.' => 'Jr.', 'Sr.' => 'Sr.', 'II' => 'II', 'III' => 'III', 'IV' => 'IV', 'V' => 'V'), '', '', false)) . '</div>","user_name",true);\'><div class="col-md-12"><div class=row><label class="col-md-2 control-label text-right">'._name.' <span class="text-danger">*</span></label><div class="col-md-10 pt-10">' . (!$staff['TITLE'] && !$staff['FIRST_NAME'] && !$staff['MIDDLE_NAME'] && !$staff['LAST_NAME'] && !$staff['NAME_SUFFIX'] ? '-' : $staff['TITLE'] . ' ' . $staff['FIRST_NAME'] . ' ' . $staff['MIDDLE_NAME'] . ' ' . $staff['LAST_NAME']) . ' ' . $staff['NAME_SUFFIX'] . '</div></div></div></div>';
    echo '</div>'; //.row
}

echo '<div class="row">';
echo '<div class="col-md-6">';
echo '<div class="form-group">' . NoInput($staff['STAFF_ID'], _staffId) . '</div>';
echo '</div><div class="col-md-6">';
echo '<div class="form-group">' . TextInput($staff['ALTERNATE_ID'], 'staff[ALTERNATE_ID]', _alternateId, 'size=12 maxlength=100 ') . '</div>';
echo '</div>'; //.col-md-4
echo '</div>'; //.row

echo '<div class="row">';
echo '<div class="col-md-6">';
echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._gender.'</label><div class="col-lg-8">' . SelectInput($staff['GENDER'], 'staff[GENDER]', '', array('Male' => 'Male', 'Female' => 'Female'), 'N/A', '') . '</div></div>';
echo '</div><div class="col-md-6">';
echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._dateOfBirth.'</label><div class="col-lg-8">';
echo DateInputAY($staff['BIRTHDATE'] != "" && $staff['BIRTHDATE'] != "0000-00-00" ? $staff['BIRTHDATE'] : "", 'staff[BIRTHDATE]', 1) . '</div></div>';
echo '</div>'; //.col-md-4
echo '</div>'; //.row

$options = array('Dr.' => 'Dr.', 'Mr.' => 'Mr.', 'Ms.' => 'Ms.', 'Rev.' => 'Rev.', 'Miss.' => 'Miss.');
$ETHNICITY_RET = DBGet(DBQuery("SELECT ETHNICITY_ID,ETHNICITY_NAME FROM ethnicity ORDER BY SORT_ORDER"));
foreach ($ETHNICITY_RET as $ethnicity_array) {
    $ethnicity[$ethnicity_array['ETHNICITY_ID']] = $ethnicity_array['ETHNICITY_NAME'];
}
echo '<div class="row">';
echo '<div class="col-md-6">';
echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._ethnicity.'</label><div class="col-lg-8">' . SelectInput($staff['ETHNICITY_ID'], 'staff[ETHNICITY_ID]', '', $ethnicity, 'N/A', '') . '</div></div>';
echo '</div><div class="col-md-6">';
$LANGUAGE_RET = DBGet(DBQuery("SELECT LANGUAGE_ID,LANGUAGE_NAME FROM language ORDER BY SORT_ORDER"));
foreach ($LANGUAGE_RET as $language_array) {
    $language[$language_array['LANGUAGE_ID']] = $language_array['LANGUAGE_NAME'];
}
echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._primaryLanguage.'</label><div class="col-lg-8">' . SelectInput($staff['PRIMARY_LANGUAGE_ID'], 'staff[PRIMARY_LANGUAGE_ID]', '', $language, 'N/A', '') . '</div></div>';
echo '</div>'; //.col-md-4
echo '</div>'; //.row


echo '<div class="row">';
echo '<div class="col-md-6">';
echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._secondLanguage.'</label><div class="col-lg-8">' . SelectInput($staff['SECOND_LANGUAGE_ID'], 'staff[SECOND_LANGUAGE_ID]', '', $language, 'N/A', '') . '</div></div>';
echo '</div><div class="col-md-6">';
echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._thirdLanguage.'</label><div class="col-lg-8">' . SelectInput($staff['THIRD_LANGUAGE_ID'], 'staff[THIRD_LANGUAGE_ID]', '', $language, 'N/A', '') . '</div></div>';
echo '</div>'; //.col-md-6
echo '</div>'; //.row

if ($_REQUEST['staff_id'] == 'new') {
    $id_sent = 0;
} else {
    if ($_REQUEST['staff_id'] != '')
        $id_sent = $_REQUEST['staff_id'];
    else
        $id_sent = UserStaffID();
}

echo '<div class="row">';
echo '<div class="col-md-6">';
echo '<div class="form-group"><label class="control-label text-right col-lg-4">'._email.' <span class="text-danger">*</span></label><div class="col-lg-8">' . TextInput($staff['EMAIL'], 'staff[EMAIL]', '', 'autocomplete=off id=email_id class=cell_medium onkeyup=check_email(this,' . $id_sent . ',2); onblur=check_email(this,' . $id_sent . ',2) ') . '<p id="email_error" class="help-block"></p></div></div>';
echo '</div>'; //.ocl-md-6
echo '<div class="col-md-6">';
echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._physicalDisability.'</label><div class="col-lg-8">' . SelectInput($staff['PHYSICAL_DISABILITY'], 'staff[PHYSICAL_DISABILITY]', '', array('N' => 'No', 'Y' => 'Yes'), false, 'onchange=show_span("span_disability_desc",this.value)') . '</div></div>';
echo '</div>'; //.ocl-md-6
echo '</div>'; //.row

echo '<div class="row">';
echo '<div class="col-md-6">';
echo '</div>';
echo '<div class="col-md-6">';
if ($staff['PHYSICAL_DISABILITY'] == 'Y') {
    echo '<div class="form-group" id="span_disability_desc"><label class="control-label col-lg-4 text-right">'._disabilityDescription.'</label><div class="col-lg-8">' . TextAreaInput($staff['DISABILITY_DESC'], 'staff[DISABILITY_DESC]', '', '', 'true') . '</div></div>';
} else {
    echo '<div class="form-group" id="span_disability_desc" style="display:none"><label class="control-label col-lg-4 text-right">'._disabilityDescription.'</label><div class="col-lg-8">' . TextAreaInput('', 'staff[DISABILITY_DESC]', '', '', 'true') . '</div></div>';
}
echo '</div>';
echo '</div>';


// IMAGE
$_REQUEST['category_id'] = 1;
$_REQUEST['custom'] = 'staff';

include('modules/users/includes/OtherInfoInc.php');


echo '</div>'; //.col-md-10
echo '<div class="col-md-2">';
if($_REQUEST['staff_id'] != 'new' && $staff['IMG_NAME'] != '')
{
    echo '<div width=150 align="center"><IMG SRC="data:image/jpeg;base64,' . base64_encode($staff['IMG_CONTENT']) . '"  width=150 class=pic>';
    if ((User('PROFILE') == 'admin' || User('PROFILE') == 'teacher') && User('PROFILE') != 'student' && User('PROFILE') != 'parent')
        echo '<br><a href=Modules.php?modname=users/UploadUserPhoto.php?modfunc=edit  style="text-decoration:none"><b>'._updateStaffSPhoto.'</b></a></div>';
    else
        echo '';
}
else {

    if ($_REQUEST['staff_id'] != 'new') {
        echo '<div align="center"><h6>'._uploadStaffSPhoto.':</h6><IMG SRC="assets/noimage.jpg?id=' . rand(6, 100000) . '" class="upload-pic">';
        if ((User('PROFILE') == 'admin' || User('PROFILE') == 'teacher') && User('PROFILE') != 'student' && User('PROFILE') != 'parent') {
            echo '<div align=center>'
            //. '<div class="fileUpload btn btn-primary btn-sm">'
            . '<label class="fileUpload btn btn-primary btn-xs btn-block mt-15">'
            . '<span>Upload</span>'
            . '<input id="uploadBtn" type="file" name="file" class="upload" onchange="selectFile(this)" />'
//                    . '</div>'
            . '</label>'
            . '</div>';
            echo '<div id="uploadFile"></div>';
        }
    } else {

        echo '<div align="center"><h6>'._uploadStaffSPhoto.':</h6><IMG SRC="assets/noimage.jpg?id=' . rand(6, 100000) . '" class="upload-pic">';
        if ((User('PROFILE') == 'admin' || User('PROFILE') == 'teacher') && User('PROFILE') != 'student' && User('PROFILE') != 'parent')
            echo '<div align=center><label class="fileUpload btn btn-primary btn-sm btn-block mt-15">'._upload.'<input type="file" id="uploadBtn"  name="file" class="upload" onchange="selectFile(this)" /></label></div>';
        echo '<div id="uploadFile"></div>';
    }
}

echo '</div>'; //.col-md-2
echo '</div>'; //.row

//////////////Modal For Filter Save////////////////////
echo '<div id="modal_crop_image" class="modal fade">';
echo '<div class="modal-dialog">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">Upload Photo</h5>';
echo '</div>';

echo '<div class="modal-body">';
echo '<div class="image-cropper-container content-group" id=div_img style="height: 400px;">
          <img src="" alt="" class="cropper" id="demo-cropper-image">
          
      </div>';
echo '<input type=hidden name=imgblob id=imgblob value=>';
echo '<input type="submit" class="btn btn-primary legitRipple" name="upbtn" value="'._upload.'">';
echo '</div>'; //.modal-body

echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal
?>
