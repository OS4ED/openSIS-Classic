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
include('../../../RedirectIncludes.php');
require("modules/students/UploadClassFnc.php");
$dir='assets/studentfiles';

        
if($_REQUEST['modfunc']=='delete' && User('PROFILE')=='admin')
{
	if(!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel'])
		echo '</FORM>';
	if(DeletePromptCommon($_REQUEST['title']))
	{
                        unlink($_REQUEST['file']);
                        unset($_REQUEST['modfunc']);
	}
}

if(!$_REQUEST['modfunc'])
{
    ###########################File Upload ####################################################

if(!file_exists($dir))
{
    mkdir($dir,0777);
}
if($_FILES['uploadfile']['name'])
{
                  $allowFiles=array("jpg","jpeg","png","gif","bmp","doc","docx","xls","xlsx","ppt","pptx","pps","txt","pdf");
                  $target_path=$dir.'/'.UserStudentID().'-'.$_FILES['uploadfile']['name'];
                  $destination_path=$dir;
                  $upload= new upload();
	$upload->target_path=$target_path;
	$upload->deleteOldImage();
	$upload->destination_path=$destination_path;
	$upload->name=$_FILES["uploadfile"]["name"];
	$upload->setFileExtension();
	$upload->fileExtension;
                  $upload->allowExtension=$allowFiles;
	$upload->validateImage();
	if($upload->wrongFormat==1){
                        $_FILES["uploadfile"]["error"]=1;
	}
                if ($_FILES["uploadfile"]["error"] > 0)
                {
                        $msg = '<span style="color: #C90000; font-family: Arial, Helvetica, sans-serif; font-size: 11px;">'._cannotUploadFileInvaliedFileType.'</span>';
                }
                else
                {
                        if(!move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $upload->target_path))
                                $msg= '<span style="color: #C90000; font-family: Arial, Helvetica, sans-serif; font-size: 11px;">'._cannotUploadFileInvalidPermission.'</span>';
                        else
                                $msg='<span style="color: #669900; font-family: Arial, Helvetica, sans-serif; font-size: 11px;">'._successfullyUploaded.'</span>';
                }
                unset ($_FILES['uploadfile']);
 
}
        if($msg)
                echo $msg;
        echo '<table width="100%" class="grid" cellpadding="4" cellspacing="1">';
        if(AllowEdit ())
        {
        echo '<thead><tr><td colspan=2 class="subtabs">'._toUploadAdditionalFilesClickBrowseSelectFileGiveItAFileNameAndClickSave.'</td></tr></thead>';
        }
        else {
        echo '<thead><tr><td colspan=2 class="subtabs">'._toViewACertainFileClickOnTheNameOfTheFile.'</td></tr></thead>';
        }

        echo '<tr class="odd">';
        if(AllowEdit ())
        {
        echo 	'<td width="50%" colspan=2><input type="file" name="uploadfile" size=50 id="upfile"></td>';
        }
        echo '</tr>';

         $dir=dir($dir);
         echo '<tbody>';
         $found= false;
        $gridClass = "odd";
        while($filename=$dir->read()) {
            if($gridClass=="even")
            {
                $gridClass="odd";
            }
            else
            {
                $gridClass="even";
            }
          if($filename)
          {
            if($filename=='.' || $filename=='..')
            continue;

            $student_id_up = explode('-',$filename);

            if($student_id_up[0]==UserStudentID())
            {
                $found= true;
                 echo '<tr class="'.$gridClass.'">
                          <td><a href="assets/studentfiles/'.$filename.'">'.substr($filename,strpos($filename,'-')+1).'</a></td>
                          ';

                  if(AllowEdit ())
                     {
                      echo '<td><input type="hidden" name="del" value="assets/studentfiles/'.$filename.'"/>
                          <a href=Modules.php?modname='.$_REQUEST[modname].'&include=Files&file=assets/studentfiles/'.urlencode($filename).'&modfunc=delete class="text-danger"><i class="icon-cross2"></i> Delete</a>
                              </td>';
                     }
                   
                     echo ' </tr>';

            }
        }
        }
         $dir->close();
         echo '</tbody>';
         echo '</table>';
        if($found!=true)
        {
            echo '<span class="alert_msg">'._noFilesWereFound.'</span>';
        }
}


?>
