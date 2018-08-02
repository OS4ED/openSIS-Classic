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
DrawBC("Students > ".ProgramTitle());
PopTable ('header','Upload Student\'s Photo');
if(clean_param($_REQUEST['modfunc'],PARAM_ALPHAMOD)=='edit')
{
	if($StudentPicturesPath && (($file = @fopen($picture_path=$StudentPicturesPath.'/'.UserStudentID().'.JPG','r')) || ($file = @fopen($picture_path=$StudentPicturesPath.'/'.UserStudentID().'.JPG','r'))))
	{
	echo '<div align=center><IMG SRC="'.$picture_path.'?id='.rand(6,100000).'" width=150 class=pic></div>';
	}
	unset($_REQUEST['modfunc']);
}
if(UserStudentID())
{
if(clean_param($_REQUEST['action'],PARAM_ALPHAMOD)=='upload' && $_FILES['file']['name'])
{
//	$target_path=$StudentPicturesPath.'/'.UserStudentID().'.JPG';
//	$destination_path = $StudentPicturesPath;
    $stu_img_info= DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID='.UserStudentID().' AND PROFILE_ID=3 AND SCHOOL_ID='. UserSchool().' AND SYEAR='.UserSyear().' AND FILE_INFO=\'stuimg\''));
        
        $fileName=$_FILES['file']['name'];
        $tmpName  = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];
	$upload= new upload();
//	$upload->target_path=$target_path;
	if(count($stu_img_info)>0)
            $upload->deleteOldImage($stu_img_info[1]['ID']);
//	$upload->destination_path=$destination_path;
	$upload->name=$_FILES["file"]["name"];
        $upload->fileSize=$fileSize;
	$upload->setFileExtension();
	$upload->fileExtension;
	$upload->validateImage();
        $upload->validateImageSize();
        if($upload->wrongSize==1){
	$_FILES["file"]["error"]=1;
	}
	if($upload->wrongFormat==1){
	$_FILES["file"]["error"]=1;
	}
	
	if ($_FILES["file"]["error"] > 0 && $upload->wrongFormat==1)
    {
    $msg = "<font color=red><b>Cannot upload file. Only jpeg, jpg, png, gif files are allowed.</b></font>";
    echo '
	'.$msg.'
	<form enctype="multipart/form-data" action="Modules.php?modname=students/Upload.php&action=upload" method="POST">';
echo '<div align=center>Select image file: <input name="file" type="file" /><b><span >(Maximum upload file size 10 MB)</span></b><br /><br>
<input type="submit" value="Upload" class="btn btn-primary" />&nbsp;<input type=button class="btn btn-primary" value=Cancel onclick=\'load_link("Modules.php?modname=students/Student.php");\'></div>
</form>';
PopTable ('footer');
    }
    else if ($_FILES["file"]["error"] > 0 && $upload->wrongSize==1)
    {
    $msg = "<font color=red><b>File too large. Maximum upload file size limit 10 MB.</b></font>";
    echo '
	'.$msg.'
	<form enctype="multipart/form-data" action="Modules.php?modname=students/Upload.php&action=upload" method="POST">';
echo '<div align=center>Select image file: <input name="file" type="file" /><b><span >(Maximum upload file size 10 MB)</span></b><br /><br>
<input type="submit" value="Upload" class="btn btn-primary" />&nbsp;<input type=button class="btn btn-primary" value=Cancel onclick=\'load_link("Modules.php?modname=students/Student.php");\'></div>
</form>';
PopTable ('footer');
    }
  	else
    {
//	  move_uploaded_file($_FILES["file"]["tmp_name"], $upload->target_path);
//	  @fopen($upload->target_path,'r');
            $fp = fopen($tmpName, 'r');
            $content = fread($fp, filesize($tmpName));
            $content = addslashes($content);
            fclose($fp);

            if(!get_magic_quotes_gpc())
            {
                $fileName = addslashes($fileName);
            }

            DBQuery('INSERT INTO user_file_upload (USER_ID,PROFILE_ID,SCHOOL_ID,SYEAR,NAME, SIZE, TYPE, CONTENT,FILE_INFO) VALUES ('.UserStudentID().',\'3\','.UserSchool().','.UserSyear().',\''.$fileName.'\', \''.$fileSize.'\', \''.$fileType.'\', \''.$content.'\',\'stuimg\')');
            $stu_img_info= DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID='.UserStudentID().' AND PROFILE_ID=3 AND SCHOOL_ID='. UserSchool().' AND SYEAR='.UserSyear().' AND FILE_INFO=\'stuimg\''));
      
	  echo '<div align=center><IMG SRC="data:image/jpeg;base64,'.base64_encode($stu_img_info[1]['CONTENT']).'" width=150 class=pic></div><div class=break></div>';

      echo "<b>File Uploaded Successfully.</b><p>";

	  PopTable ('footer');
    }    
}
else
{
echo '
'.$msg.'
<form enctype="multipart/form-data" action="Modules.php?modname=students/Upload.php&action=upload" method="POST">';
echo '<div align=center>Select image file: <input name="file" type="file" /><b><span >(Maximum upload file size 10 MB)</span></b><br /><br>
<input type="submit" value="Upload" class="btn btn-primary" />&nbsp;<input type=button class="btn btn-primary" value=Cancel onclick=\'load_link("Modules.php?modname=students/Student.php");\'></div>
</form>';
PopTable ('footer');
}
}
else
{
	echo 'Please select a student first! from the <b>"Students"</b> Tab';
	PopTable ('footer');
}
?>