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
$dir = 'assets/studentfiles';
if ($_REQUEST['modfunc'] == 'delete' && (User('PROFILE') == 'admin' || User('PROFILE') == 'student')) {
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '</FORM>';
    }
    if (DeletePromptFilesEncoded($_REQUEST['title'], '&include=FilesInc&category_id=7')) {
        unlink($dir.'/'.base64_decode($_REQUEST['removefile']));
        DBQuery('DELETE FROM user_file_upload WHERE ID=' . $_REQUEST['del']);
        unset($_REQUEST['modfunc']);
    }
}

if (isset($_REQUEST['delete_msg']) && $_REQUEST['delete_msg'] == 'yes') {

    unlink($_REQUEST['target_path']);
    unset($_SESSION['grid_msg']);
    unset($_SESSION['dup_file_name']);
}
if (!$_REQUEST['modfunc']) {
    unset($_SESSION['grid_msg']);
    unset($_SESSION['dup_file_name']);
    ###########################File Upload ####################################################

    if (!file_exists($dir)) {
        mkdir($dir, 0777);
    }

    if($_FILES['uploadfile']) {
        $allowFiles = array("jpg", "jpeg", "png", "gif", "bmp", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "pps", "txt", "pdf");

        $msg = '';
        $upload_status = 1;
        $uploadedFiles = 0;
        $rejectedFiles = array("typeError" => 0, "permissionError" => 0, "typeErrorFiles" => '', "permissionErrorFiles" => '');

        $no_of_files = count($_FILES['uploadfile']['name']);

        for($i=0; $i<$no_of_files; $i++) {
            if ($_FILES['uploadfile']['name'][$i]) {
                $_FILES['uploadfile']['name'][$i] = str_replace(" ", "opensis_space_here", $_FILES['uploadfile']['name'][$i]);
                
                $target_path = $dir . '/' . UserStudentID() . '-' . $_FILES['uploadfile']['name'][$i];
                
                if (file_exists($target_path)) {
                    $target_path = $dir . '/' . UserStudentID() . '-' . time() . '-' . $_FILES['uploadfile']['name'][$i];
                    $_SESSION['dup_file_name'] = $target_path;
                    // $_SESSION['grid_msg'] = 'block';
                }

                $fileName = $tmpName = $fileSize = $fileType = '';
        
                $fileName = $_FILES['uploadfile']['name'][$i];
                $tmpName = $_FILES['uploadfile']['tmp_name'][$i];
                $fileSize = $_FILES['uploadfile']['size'][$i];
                $fileType = $_FILES['uploadfile']['type'][$i];

                $destination_path = $dir;
                
                $upload = new upload();
                $upload->target_path = $target_path;
                $upload->destination_path = $destination_path;
                $upload->name = $_FILES["uploadfile"]["name"][$i];
                $upload->setFileExtension();
                $upload->fileExtension;
                $upload->allowExtension = $allowFiles;
                $upload->validateImage();

                if ($upload->wrongFormat == 1) {
                    $_FILES["uploadfile"]["error"][$i] = 1;
                }
                if ($_FILES["uploadfile"]["error"][$i] > 0) {
                    // $msg .= '<span style="color: #C90000; font-family: Arial, Helvetica, sans-serif; font-size: 11px;">Cannot upload file '.$fileName.'. Invalid file type.</span><br>';

                    $rejectedFiles['typeError']++;
                    $rejectedFiles['typeErrorFiles'] .= $fileName.', ';
                } else {
                    if (!move_uploaded_file($_FILES["uploadfile"]["tmp_name"][$i], $upload->target_path))
                    {
                       // $msg = '<span style="color: #C90000; font-family: Arial, Helvetica, sans-serif; font-size: 11px;">Cannot upload file. Invalid Permission</span>';

                        $rejectedFiles['permissionError']++;
                        $rejectedFiles['permissionErrorFiles'] .= $fileName.', ';
                    }
                    else
                    {
                        $target_path1 = $dir . '/' . UserStudentID() . '-' . $_FILES['uploadfile']['name'][$i];
                        // if (file_exists($target_path1) && file_exists($_SESSION['dup_file_name'])) {

                        //     $n = DuplicateFile("duplicate file", $_SESSION['dup_file_name']);
                        // }

                        $fileName = str_replace($dir.'/', '', $target_path);
                        $content = 'IN_DIR';

                        DBQuery('INSERT INTO user_file_upload (USER_ID,PROFILE_ID,SCHOOL_ID,SYEAR,NAME, SIZE, TYPE, CONTENT,FILE_INFO) VALUES (' . UserStudentID() . ',\'3\',' . UserSchool() . ',' . UserSyear() . ',\'' . addslashes($fileName) . '\', \'' . addslashes($fileSize) . '\', \'' . addslashes($fileType) . '\', \'' . addslashes($content) . '\',\'stufile\')');

                        $uploadedFiles++;

                        // $msg = '<span style="color: #669900; font-family: Arial, Helvetica, sans-serif; font-size: 11px;">Successfully uploaded</span>';
                    }
                }
                unset($_FILES['uploadfile'][$i]);
            }
        }
        unset($_FILES['uploadfile']);
        $rejectedFiles['typeErrorFiles'] = rtrim(trim($rejectedFiles['typeErrorFiles']), ',');
        $rejectedFiles['permissionErrorFiles'] = rtrim(trim($rejectedFiles['permissionErrorFiles']), ',');
    }

    if (!isset($_SESSION['grid_msg'])) {
        if($upload_status == 1)
        {
            if($no_of_files == $uploadedFiles) {
                $msg = '<div class="alert alert-success alert-styled-left">'._allFilesAreSuccessfullyUploaded.'</div>';
            } else {
                $msg = '';
                
                if($uploadedFiles != 0) {
                    $msg .= '<div class="alert alert-success alert-styled-left">';
                    if($uploadedFiles == 1) {
                        $msg .= _1File;
                    } elseif($uploadedFiles > 1) {
                        $msg .= $uploadedFiles. ' '._files.'';
                    }
                    $msg .= ' '._successfullyUploaded.'</div>';
                }

                if($rejectedFiles['typeError'] != 0) {
                    $msg .= '<div class="alert alert-danger alert-styled-left">';
                    if($rejectedFiles['typeError'] == 1) {
                        $msg .= _1File;
                    } elseif($rejectedFiles['typeError'] > 1) {
                        $msg .= $rejectedFiles['typeError']. ' '._files.'';
                    }
                    $msg .= ' (' .$rejectedFiles['typeErrorFiles']. ') '._cannotBeUploadedBecauseOfInvalidFileType.'</div>';
                }

                if($rejectedFiles['permissionError'] != 0) {
                    $msg .= '<div class="alert alert-danger alert-styled-left">';
                    if($rejectedFiles['permissionError'] == 1) {
                        $msg .= _1File;
                    } elseif($rejectedFiles['permissionError'] > 1) {
                        $msg .= $rejectedFiles['permissionError']. ' '._files.'';
                    }
                    $msg .= ' (' .$rejectedFiles['permissionErrorFiles']. ') '._cannotBeUploadedBecauseOfInvalidPermission.'</div>';
                }
            }
        }

        if ($msg) {
            echo $msg;
        }
        if ($succmsg) {
            echo $succmsg;
        }


        if (AllowEdit()) {
            echo '<div class="alert alert-info alert-styled-left">'._toUploadAdditionalFilesClickBrowseSelectFileAndClickSave.'</div>';
        } else {
            echo '<div class="alert alert-info alert-styled-left">'._toViewACertainFileClickOnTheNameOfTheFile.'</div>';
        }

        if (AllowEdit()) {
            echo '<script>var fileobj = [];</script>';
            echo '<input type="file" style="display:none;" name="uploadfile[]" size=50 id="upfile" multiple="multiple" onchange="selectedFilesRail(this.id);">';
            echo '<input class="btn btn-default btn-xs" type="button" value="'._browse.'..." onclick="clickOnFileInput();">';

            echo '<div id="areaFileRail"><ul class="p-0 m-t-15" style="list-style-type:none;"></ul></div>';
        }

        echo '<table class="table table-bordered table-striped m-t-15">';
        // $dir = dir($dir);
        // $file_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID=' . UserStudentID() . ' AND PROFILE_ID=3 AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND file_info=\'stufile\''));
        $file_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID=' . UserStudentID() . ' AND PROFILE_ID=3 AND SCHOOL_ID=' . UserSchool() . ' AND file_info=\'stufile\''));
        echo '<tbody>';
        $found = false;
        $gridClass = "";
        $file_no = 1;

        
        foreach ($file_info as $key => $file_val) {
            if ($gridClass == "even") {
                $gridClass = "odd";
            } else {
                $gridClass = "even";
            }
            if ($file_val['NAME']) {
                if ($file_val['NAME'] == '.' || $file_val['NAME'] == '..')
                    continue;

                // $student_id_up = explode('-',$filename);
                // if($student_id_up[0]==UserStudentID())
                // {
                else {
                    $found = true;
                    $sub = $file_val['NAME'];

                    if (strstr($sub, '-_')) {
                        $file_display = substr($sub, 0, strrpos($sub, '-_'));
                    } else {
                        $file_display = $sub;
                    }
                    $file = explode('.', $file_display);
                    $file[1] = '';

                    if ($file[1] == 'jpg' || $file[1] == 'jpeg' || $file[1] == 'png' || $file[1] == 'gif') {
                        $fileIcon = '<i class="fa fa-file-image-o"></i>';
                    } elseif ($file[1] == 'doc' || $file[1] == 'docx') {
                        $fileIcon = '<i class="fa fa-file-word-o"></i>';
                    } elseif ($file[1] == 'xls' || $file[1] == 'xlsx') {
                        $fileIcon = '<i class="fa fa-file-excel-o"></i>';
                    } elseif ($file[1] == 'ppt' || $file[1] == 'pptx') {
                        $fileIcon = '<i class="fa fa-file-powerpoint-o"></i>';
                    } elseif ($file[1] == 'pdf') {
                        $fileIcon = '<i class="fa fa-file-pdf-o"></i>';
                    } else {
                        $fileIcon = '<i class="fa fa-file-o"></i>';
                    }

                    echo '<tr class="' . $gridClass . '">';
                    echo '<td style="vertical-align: middle;">';
                    echo '<a href="DownloadWindow.php?down_id=' . $file_val['DOWNLOAD_ID'] . '&studentfile=Y">' . $fileIcon . ' &nbsp; '. str_replace("opensis_space_here", " ", str_replace(UserStudentID()."-","",$file_display)) . '</a>';
                    echo '</td>';

                    if (AllowEdit()) {
                        echo '<td width="80"><input type="hidden" name="del" value="' . $file_val['ID'] . '"/>';
                        echo '<a href=Modules.php?modname=' . $_REQUEST[modname] . '&removefile=' . base64_encode($file_val['NAME']) . '&title=' . base64_encode(str_replace("opensis_space_here", " ", str_replace(UserStudentID()."-","",$file_val['NAME']))) . '&include=' . $_REQUEST['include'] . '&modfunc=delete&del=' . $file_val['ID'] . ' class="btn btn-danger btn-icon btn-xs" title="'._delete.'"><i class="icon-cross2"></i></a>
                              </td>';
                    }

                    echo ' </tr>';
                }
            }
        }
        // $dir->close();
        echo '</tbody>';
        echo '</table>';
        if ($found != true) {
            echo '<div id="nofiles" class="alert alert-danger">'._noFilesFound.'.</div>';
        }
    }
}
?>
