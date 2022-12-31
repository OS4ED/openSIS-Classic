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
include("UploadClassFnc.php");
DrawBC(""._students." > " . ProgramTitle());
PopTable('header', ''._uploadStudentSPhoto.'');
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'edit') {
    if ($StudentPicturesPath && (($file = @fopen($picture_path = $StudentPicturesPath . '/' . UserStudentID() . '.JPG', 'r')) || ($file = @fopen($picture_path = $StudentPicturesPath . '/' . UserStudentID() . '.JPG', 'r')))) {
        echo '<div align=center><IMG SRC="' . $picture_path . '?id=' . rand(6, 100000) . '" width=150 class=pic></div>';
    }
    unset($_REQUEST['modfunc']);
}
if (UserStudentID()) {
    if (clean_param($_REQUEST['action'], PARAM_ALPHAMOD) == 'upload' && $_FILES['file']['name']) {
//	$target_path=$StudentPicturesPath.'/'.UserStudentID().'.JPG';
//	$destination_path = $StudentPicturesPath;
        $stu_img_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID=' . UserStudentID() . ' AND PROFILE_ID=3 AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND FILE_INFO=\'stuimg\''));

        $fileName = $_FILES['file']['name'];
        $tmpName = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];
        $upload = new upload();
//	$upload->target_path=$target_path;
        if (count($stu_img_info) > 0)
            $upload->deleteOldImage($stu_img_info[1]['ID']);
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
            $msg = "<font color=red><b>"._cannotUploadFileOnlyJpegJpgPngGifFilesAreAllowed."</b></font>";
            echo '
	' . $msg . '
	<form enctype="multipart/form-data" action="Modules.php?modname=students/Upload.php&action=upload" method="POST">';
            echo '<div align=center>'._selectImageFile.': <input name="file" type="file" /><b><span >(Maximum upload file size 10 MB)</span></b><br /><br>
<input type="submit" value="'._upload.'" class="btn btn-primary" />&nbsp;<input type=button class="btn btn-primary" value="'._cancel.'" onclick=\'load_link("Modules.php?modname=students/Student.php");\'></div>
</form>';
            PopTable('footer');
        } else if ($_FILES["file"]["error"] > 0 && $upload->wrongSize == 1) {
            $msg = "<font color=red><b>"._FileExceedsTheAllowableSizeTryAgainWithAFileLessThen10Mb."</b></font>";
            echo '
	' . $msg . '
	<form enctype="multipart/form-data" action="Modules.php?modname=students/Upload.php&action=upload" method="POST">';
            echo '<div align=center>'._selectImageFile.': <input name="file" type="file" /><b><span >(Maximum upload file size 10 MB)</span></b><br /><br>
<input type="submit" value="'._upload.'" class="btn btn-primary" />&nbsp;<input type=button class="btn btn-primary" value="'._cancel.'" onclick=\'load_link("Modules.php?modname=students/Student.php");\'></div>
</form>';
            PopTable('footer');
        } else {
//	  move_uploaded_file($_FILES["file"]["tmp_name"], $upload->target_path);
//	  @fopen($upload->target_path,'r');
//            $fp = fopen($tmpName, 'r');
//            $content = fread($fp, filesize($tmpName));
            $content = base64_decode($_REQUEST['imgblob']);
            $content = addslashes($content);
            //fclose($fp);

            // if (!get_magic_quotes_gpc()) {
            //     $fileName = addslashes($fileName);
            // }
            $fileName = addslashes($fileName);


            DBQuery('INSERT INTO user_file_upload (USER_ID,PROFILE_ID,SCHOOL_ID,SYEAR,NAME, SIZE, TYPE, CONTENT,FILE_INFO) VALUES (' . UserStudentID() . ',\'3\',' . UserSchool() . ',' . UserSyear() . ',\'' . $fileName . '\', \'' . $fileSize . '\', \'' . $fileType . '\', \'' . $content . '\',\'stuimg\')');
            $stu_img_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID=' . UserStudentID() . ' AND PROFILE_ID=3 AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND FILE_INFO=\'stuimg\''));

            echo '<div class="text-center">';
            echo '<div class="alert alert-success alert-bordered inline-block"><span class="text-semibold">'._wellDone.'!</span> '._fileUploadedSuccessfully.'</div>';
            echo '</div>';
            echo '<div align=center><IMG SRC="data:image/jpeg;base64,' . base64_encode($stu_img_info[1]['CONTENT']) . '" width=250 class=pic></div><div class=break></div>';


            PopTable('footer');
        }
    } else {
        echo '
' . $msg . '
<form enctype="multipart/form-data" action="Modules.php?modname=students/Upload.php&action=upload" method="POST">';
        echo '<div align=center>'._selectImageFile.': <input name="file" type="file" onchange="selectFile(this)"/><b><span >('._maximumUploadFileSize_10Mb.')</span></b><br /><br>';
//////////////Modal For Filter Save////////////////////
        echo '<div id="modal_crop_image" class="modal fade">';
        echo '<div class="modal-dialog">';
        echo '<div class="modal-content">';
        echo '<div class="modal-header">';
        echo '<button type="button" class="close" data-dismiss="modal">×</button>';
        echo '<h5 class="modal-title">'._uploadPhoto.'</h5>';
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
        echo '<input type=button class="btn btn-primary" value="'._cancel.'" onclick=\'load_link("Modules.php?modname=students/Student.php");\'></div>
</form>';
        PopTable('footer');
    }
} else {
    echo ''._pleaseSelectAStudentFirst.'! from the <b>"Students"</b> Tab';
    PopTable('footer');
}
?>