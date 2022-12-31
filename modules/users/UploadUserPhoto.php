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
PopTable('header',  _uploadStaffSPhoto);
$UserPicturesPath = 'assets/userphotos/';

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'edit') {
    if ($UserPicturesPath && (($file = @fopen($picture_path = $UserPicturesPath . '/' . UserStaffID() . '.JPG', 'r')) || ($file = @fopen($picture_path = $UserPicturesPath . '/' . UserStaffID() . '.JPG', 'r')))) {
        echo '<div align=center><IMG SRC="' . $picture_path . '?id=' . rand(6, 100000) . '" width=150 class=pic></div><div class=break></div>';
    }
    unset($_REQUEST['modfunc']);
}

if (UserStaffID()) {
    $profile = DBGet(DBQuery('SELECT * FROM staff WHERE STAFF_ID=\'' . UserStaffID() . '\' '));
    if ($profile[1]['PROFILE'] != 'parent') {
        if (clean_param($_REQUEST['action'], PARAM_ALPHAMOD) == 'upload' && $_FILES['file']['name']) {

            $fileName = $_FILES['file']['name'];
            $tmpName = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];
            $fileType = $_FILES['file']['type'];
//	$target_path=$UserPicturesPath.'/'.UserStaffID().'.JPG';
//	$destination_path = $UserPicturesPath;	
            $upload = new upload();

//	$upload->target_path=$target_path;
            if ($profile[1]['IMG_NAME'] != '')
                $upload->deleteOldImage(UserStaffID());
//	$upload->destination_path=$destination_path;
            $upload->name = $_FILES["file"]["name"];
            $upload->fileSize = $fileSize;
            $upload->setFileExtension();
            $upload->fileExtension;
            $upload->validateImage();
            $upload->validateImageSize();
            if ($upload->wrongSize == 1) {
                $_FILES["file"]["error"] = 1;
            }
            if ($upload->wrongFormat == 1) {
                $_FILES["file"]["error"] = 1;
            }

            if ($_FILES["file"]["error"] > 0 && $upload->wrongFormat == 1) {

                $msg = "<font color=red><b>Cannot upload file. Only jpeg, jpg, png, gif files are allowed.</b></font>";
                echo '
            ' . $msg . '
            <form enctype="multipart/form-data" action="Modules.php?modname=users/UploadUserPhoto.php&action=upload" method="POST">';
                echo '<div align=center>Select image file: <input name="file" type="file" /><b><span >(Maximum upload file size 10 MB)</span></b><br /><br>
    <input type="submit" value="'._upload.'" class="btn btn-primary" />&nbsp;<input type=button class="btn btn-default" value="'._cancel.'" onclick=\'load_link("Modules.php?modname=users/User.php");\'></div>
    </form>';
                PopTable('footer');
            } else if ($_FILES["file"]["error"] > 0 && $upload->wrongSize == 1) {

                $msg = "<font color=red><b>"._FileExceedsTheAllowableSizeTryAgainWithAFileLessThen10Mb."</b></font>";
                echo '
            ' . $msg . '
            <form enctype="multipart/form-data" action="Modules.php?modname=users/UploadUserPhoto.php&action=upload" method="POST">';
                echo '<div align=center>Select image file: <input name="file" type="file" /><b><span >(Maximum upload file size 10 MB)</span></b><br /><br>
    <input type="submit" value="'._upload.'" class="btn btn-primary" />&nbsp;<input type=button class="btn btn-default" value="'._cancel.'" onclick=\'load_link("Modules.php?modname=users/User.php");\'></div>
    </form>';
                PopTable('footer');
            } else {
                $content = base64_decode($_REQUEST['imgblob']);
                $content = addslashes($content);

                // if (!get_magic_quotes_gpc()) {
                //     $fileName = addslashes($fileName);
                // }
                $fileName = addslashes($fileName);
                DBQuery('UPDATE staff SET IMG_NAME=\'' . $fileName . '\',IMG_CONTENT=\'' . $content . '\' WHERE STAFF_ID=' . UserStaffID());
                $stf_photo = DBGet(DBQuery('SELECT * FROM staff WHERE STAFF_ID=\'' . UserStaffID() . '\' '));
                echo '<div class="alert alert-success alert-bordered">Staff Photo Uploaded Successfully.</div>';
                echo '<div align=center><IMG SRC="data:image/jpeg;base64,' . base64_encode($stf_photo[1]['IMG_CONTENT']) . '" class=pic></div><div class=break></div>';


                PopTable('footer');
                               

                echo '<script>';
                if(UserStaffID() == $_SESSION['STAFF_ID']){
                    echo '$(".sidebar-user-material-content img").attr("src","data:image/jpeg;base64,' . base64_encode($stf_photo[1]['IMG_CONTENT']) . '");';
                }
                echo '</script>';
            }
        } else {
            echo '
' . $msg . '
<form enctype="multipart/form-data" action="Modules.php?modname=users/UploadUserPhoto.php&action=upload" method="POST">';
            echo '<div align=center>Select image file: <input name="file" type="file" onchange="selectFile(this)"/><b><span >(Maximum upload file size 10 MB)</span></b><br /><br>';
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
            echo '<input type=hidden name="imgblob" id="imgblob" value="">';
            echo '<input type="submit" class="btn btn-primary legitRipple" name="upbtn" value="'._upload.'">';
            echo '</div>'; //.modal-body

            echo '</div>'; //.modal-content
            echo '</div>'; //.modal-dialog
            echo '</div>'; //.modal

            echo '<input type=button class="btn btn-default" value="'._cancel.'" onclick=\'load_link("Modules.php?modname=users/User.php");\'></div>
</form>';
            PopTable('footer');
        }
    } else {
        echo 'Cannot upload parent\'s picture.';
        PopTable('footer');
    }
} else {
    echo 'Please select a staff first! from the <b>"Staff"</b> Tab';
    PopTable('footer');
}

class upload {

    var $target_path;
    var $destination_path;
    var $name;
    var $fileExtension;
    var $allowExtension = array("jpg", "jpeg", "png", "gif", "bmp");
    var $wrongFormat = 0;
    var $wrongSize = 0;

    function deleteOldImage($id = '') {
//if(file_exists($this->target_path))
//	unlink($this->target_path);
        if ($id != '') {
            DBQuery('UPDATE staff SET IMG_NAME=NULL,IMG_CONTENT=NULL WHERE STAFF_ID=' . $id);
        }
    }

    function setFileExtension() {
        $this->fileExtension = strtolower(substr($this->name, strrpos($this->name, ".") + 1));
    }

    function validateImage() {
        if (!in_array($this->fileExtension, $this->allowExtension)) {
            $this->wrongFormat = 1;
        }
    }

    function validateImageSize() {
        if ($this->fileSize > 10485760) {
            $this->wrongSize = 1;
        }
    }

    function get_file_extension($file_name) {
        return end(explode('.', $file_name));
    }

}

?>