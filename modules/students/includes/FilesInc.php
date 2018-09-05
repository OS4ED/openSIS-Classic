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
//        unlink($_REQUEST['file']);
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

//    if (!file_exists($dir)) {
//        mkdir($dir, 0777);
//    }
    if ($_FILES['uploadfile']['name']) {
        $_FILES['uploadfile']['name'] = str_replace(" ", "opensis_space_here", $_FILES['uploadfile']['name']);
        $allowFiles = array("jpg", "jpeg", "png", "gif", "bmp", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "pps", "txt", "pdf");
        $target_path = $dir . '/' . UserStudentID() . '-' . $_FILES['uploadfile']['name'];
        if (file_exists($target_path)) {
            $target_path = $dir . '/' . UserStudentID() . '-' . $_FILES['uploadfile']['name'] . "-_" . time();
            $_SESSION['dup_file_name'] = $target_path;
            $_SESSION['grid_msg'] = 'block';
        }

        $fileName = $_FILES['uploadfile']['name'];
        $tmpName = $_FILES['uploadfile']['tmp_name'];
        $fileSize = $_FILES['uploadfile']['size'];
        $fileType = $_FILES['uploadfile']['type'];
        $destination_path = $dir;
        $upload = new upload();
        $upload->target_path = $target_path;
        $upload->destination_path = $destination_path;
        $upload->name = $_FILES["uploadfile"]["name"];
        $upload->setFileExtension();
        $upload->fileExtension;
        $upload->allowExtension = $allowFiles;
        $upload->validateImage();
        if ($upload->wrongFormat == 1) {
            $_FILES["uploadfile"]["error"] = 1;
        }
        if ($_FILES["uploadfile"]["error"] > 0) {
            $msg = '<span style="color: #C90000; font-family: Arial, Helvetica, sans-serif; font-size: 11px;">Cannot upload file. Invalid file type.</span>';
        } else {

            $fp = fopen($tmpName, 'r');
            $content = fread($fp, filesize($tmpName));
            $content = addslashes($content);
            fclose($fp);

            if (!get_magic_quotes_gpc()) {
                $fileName = addslashes($fileName);
            }

            DBQuery('INSERT INTO user_file_upload (USER_ID,PROFILE_ID,SCHOOL_ID,SYEAR,NAME, SIZE, TYPE, CONTENT,FILE_INFO) VALUES (' . UserStudentID() . ',\'3\',' . UserSchool() . ',' . UserSyear() . ',\'' . $fileName . '\', \'' . $fileSize . '\', \'' . $fileType . '\', \'' . $content . '\',\'stufile\')');

            $msg = '<span style="color: #669900; font-family: Arial, Helvetica, sans-serif; font-size: 11px;">Successfully uploaded</span>';

//            if (!move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $upload->target_path))
//                $msg = '<span style="color: #C90000; font-family: Arial, Helvetica, sans-serif; font-size: 11px;">Cannot upload file. Invalid Permission</span>';
//            else {
//
//                $target_path1 = $dir . '/' . UserStudentID() . '-' . $_FILES['uploadfile']['name'];
//                if (file_exists($target_path1) && file_exists($_SESSION['dup_file_name'])) {
//
//                    $n = DuplicateFile("duplicate file", $_SESSION['dup_file_name']);
//                }
//                $msg = '<span style="color: #669900; font-family: Arial, Helvetica, sans-serif; font-size: 11px;">Successfully uploaded</span>';
//            }
        }
        unset($_FILES['uploadfile']);
    }
    if (!isset($_SESSION['grid_msg'])) {
        if ($msg) {
            echo $msg;
        }



        if (AllowEdit()) {
            echo '<div class="alert bg-primary alert-styled-left">To upload additional files click browse, select file, give it a file name and click save</div>';
        } else {
            echo '<div class="alert bg-primary alert-styled-left">To View a certain file,click on the name of the file</div>';
        }

        if (AllowEdit()) {
            echo '<input type="file" name="uploadfile" size=50 id="upfile">';
        }

        echo '<table class="table table-bordered table-striped m-t-15">';
//        $dir = dir($dir);
        $file_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID=' . UserStudentID() . ' AND PROFILE_ID=3 AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND file_info=\'stufile\''));
        echo '<tbody>';
        $found = false;
        $gridClass = "";
        $file_no = 1;

//        while ($filename = $dir->read()) {
//            
//            if ($filename) {
//                if ($filename == '.' || $filename == '..')
//                    continue;
//
//                $student_id_up = explode('-', $filename);                
//
//                if ($student_id_up[0] == UserStudentID()) {
//                    $found = true;
//                    
//                    $sub = substr($filename, strpos($filename, '-') + 1);
//
//                    if (strstr($sub, '-_')) {
//                        $file_display = substr($sub, 0, strrpos($sub, '-_'));
//                    } else {
//                        $file_display = $sub;
//                    }
//
//                    echo '<tr class="' . $gridClass . '">
//                          <td><a target="new" href="assets/studentfiles/' . $filename . '">' . str_replace("opensis_space_here", " ", $file_display) . '</a></td>
//                          ';
//
//                    if (AllowEdit()) {
//                        echo '<td><input type="hidden" name="del" value="assets/studentfiles/' . $filename . '"/ >
//                          <a href=Modules.php?modname='.$_REQUEST['modname'].'&include=FilesInc&category_id='.$_REQUEST['category_id'].'&file=assets/studentfiles/' . urlencode($filename) . '&modfunc=delete><i class="fa fa-times"></i></a>
//                              </td>';
//                    }
//
//                    echo ' </tr>';
//                }
//            }
//        }
        foreach ($file_info as $key => $file_val) {
            if ($gridClass == "even") {
                $gridClass = "odd";
            } else {
                $gridClass = "even";
            }
            if ($file_val['NAME']) {
                if ($file_val['NAME'] == '.' || $file_val['NAME'] == '..')
                    continue;

//            $student_id_up = explode('-',$filename);
//            if($student_id_up[0]==UserStudentID())
//            {
                else {
                    $found = true;
//                echo "<br>";
//
//                echo "<br>";
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
                    echo '<td>';
                    echo '<a href="DownloadWindow.php?down_id=' . $file_val['ID'] . '">' . $fileIcon . ' &nbsp; '. str_replace("opensis_space_here", " ", $file_display) . '</a>';
                    echo '</td>';

                    if (AllowEdit()) {
                        echo '<td width="80"><input type="hidden" name="del" value="' . $file_val['ID'] . '"/>';
                        echo '<a href=Modules.php?modname=' . $_REQUEST[modname] . '&title=' . base64_encode($file_val['NAME']) . '&include=' . $_REQUEST['include'] . '&modfunc=delete&del=' . $file_val['ID'] . ' class="text-danger"><i class="icon-cross2"></i> Delete</a>
                              </td>';
                    }

                    echo ' </tr>';
                }
            }
        }
//        $dir->close();
        echo '</tbody>';
        echo '</table>';
        if ($found != true) {
            echo '<span class="text-danger">No files found.</span>';
        }
    }
}
?>
