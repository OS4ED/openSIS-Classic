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
if (!defined('WAREHOUSE_PHP')) {
    define("WAREHOUSE_PHP", 1);
    $staticpath = dirname(__FILE__) . '/';

    require_once($staticpath . "ConfigInc.php");
    require_once("DatabaseInc.php");

    //Create Default Year Picture Folder
    // if (!file_exists($StudentPicturesPath)) {
    //     mkdir($StudentPicturesPath);
    // }

    // Load functions.
    if ($handle = opendir("$openSISPath/functions")) {
        if (!is_array($IgnoreFiles))
            $IgnoreFiles = Array();

        while (false !== ($file = readdir($handle))) {
            // if filename isn't '.' '..' or in the Ignore list... load it.
            if ($file != "." && $file != ".." && !in_array($file, $IgnoreFiles)) {
                if (file_exists("$openSISPath/functions/$file"))
                    require_once("$openSISPath/functions/$file");
            }
        }
    }

    // Start Session.
    session_start();

    if (!$_SESSION['STAFF_ID'] && !$_SESSION['STUDENT_ID'] && strpos($_SERVER['PHP_SELF'], 'index.php') === false) {
        header('Location: index.php');
        exit;
    }

    function Warehouse($mode, $extra = '') {
        global $__SHOW_FOOTER, $_openSIS;

        switch ($mode) {
            case 'header':
                echo "<!DOCTYPE html><html lang=\"en\" ".((langDirection()=='rtl')?'dir="rtl"':'dir="ltr')."><head><meta charset=\"utf-8\"><meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no\">";
                echo "<title>" . Config('TITLE') . "</title><link rel=\"shortcut icon\" href=\"favicon.ico\">";
                //echo '<link href="assets/css/export_print.css" rel="stylesheet" type="text/css">';

                if (basename($_SERVER['PHP_SELF']) != 'index.php' && basename($_SERVER['PHP_SELF']) != 'Ajax.php')
                    echo "<noscript><meta http-equiv=REFRESH content='0;url=index.php?modfunc=logout&reason=javascript' /></noscript>";
                if (basename($_SERVER['PHP_SELF']) == 'index.php')
                    echo "<noscript><meta http-equiv=REFRESH content='0;url=EnableJavascript.php' /></noscript>";

                echo $extra;
                echo "<script language=\"JavaScript\" type=\"text/javascript\">";
                if (basename($_SERVER['PHP_SELF']) == 'index.php')
                    echo "if(parent.frames.length > 0){ parent.location.href = 'index.php?modfunc=logout'; }";
                echo "function newLoad(){ }


							var locked;						

							function putFocus()
							{
								if(document.forms.length > 0)
								{
									document.forms[0].elements[0].focus();
								}
							}

							function addHTML(html,id,replace)
							{
								if(locked!=false)
								{
									if(replace==true)
										document.getElementById(id).innerHTML = html;
									else
										document.getElementById(id).innerHTML = document.getElementById(id).innerHTML + html;
								}
							}

							function changeHTML(show,hide)
							{
								for(key in show)
									document.getElementById(key).innerHTML = document.getElementById(show[key]).innerHTML;
								for(i=0;i<hide.length;i++)
									document.getElementById(hide[i]).innerHTML = '';
							}

                                                        function checkAll(form,value,name_like)
							{
								if(value==true)
									checked = true;
								else
									checked = false;

								for(i=0;i<form.elements.length;i++)
								{
									if(form.elements[i].type=='checkbox' && form.elements[i].name!='controller' && form.elements[i].name.substr(0,name_like.length)==name_like)
										form.elements[i].checked = checked;
								}
							}
						</script>";

//					<link rel='stylesheet' type='text/css' href='styles/Help.css'>";
//                echo "<link rel=stylesheet type=text/css href=styles/Calendar.css>";
                echo "</head>";

                break;
            case "footer":
                echo '</td></tr></table>';

                for ($i = 1; $i <= $_openSIS['PrepareDate']; $i++) {
                    echo '<script type="text/javascript">
				Calendar.setup({
					monthField     :    "monthSelect' . $i . '",
					dayField       :    "daySelect' . $i . '",
					yearField      :    "yearSelect' . $i . '",
					ifFormat       :    "%d-%b-%y",
					button         :    "trigger' . $i . '",
					align          :    "Tl",
					singleClick    :    true
				});
			</script>';
                }
                echo '</body>';
                echo '</html>';
                break;
        }
    }

}

/*
							function checkAll(form,value,name_like)
							{
                                                       
								if(value==true)
									checked = true;
								else
									checked = false;
                                                                
                                                                var arr_len=document.getElementById('res_len').value;
                                                                
                                                                var count = document.getElementById('res_length').value;                                                      

                                                                var stu_list=document.getElementById('res_len').value;

                                                                var  res_list = stu_list.split(',');
                                                                
                                                                for(i=0;i<count;i++)
                                                                {
                                                               
                                                                    var check_id = res_list[i];
                               
                                                                    console.log(check_id);
                                                                    
                                                                    document.getElementById(check_id).checked = true;
                                                                }
                             
							} 

 */
