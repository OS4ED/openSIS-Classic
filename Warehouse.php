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
if (WAREHOUSE_PHP == 0) {
    define("WAREHOUSE_PHP", 1);
    $staticpath = dirname(__FILE__) . '/';

    require_once($staticpath . "ConfigInc.php");
    require_once("DatabaseInc.php");

    //Create Default Year Picture Folder
    if (!file_exists($StudentPicturesPath)) {
        mkdir($StudentPicturesPath);
    }

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

    function Warehouse($mode) {
        global $__SHOW_FOOTER, $_openSIS;

        switch ($mode) {
            case 'header':
                echo "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"utf-8\"><meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no\">";
                echo "<title>" . Config('TITLE') . "</title><link rel=\"shortcut icon\" href=\"favicon.ico\">";
                echo '<link href="assets/css/export_print.css" rel="stylesheet" type="text/css">';

                if (basename($_SERVER['PHP_SELF']) != 'index.php' && basename($_SERVER['PHP_SELF']) != 'Ajax.php')
                    echo "<noscript><meta http-equiv=REFRESH content='0;url=index.php?modfunc=logout&reason=javascript' /></noscript>";
                if (basename($_SERVER['PHP_SELF']) == 'index.php')
                    echo "<noscript><meta http-equiv=REFRESH content='0;url=EnableJavascript.php' /></noscript>";


                echo "<script language=\"JavaScript\" type=\"text/javascript\">";
                if (basename($_SERVER['PHP_SELF']) == 'index.php')
                    echo "if(parent.frames.length > 0){ parent.location.href = 'index.php?modfunc=logout'; }";
                echo "function newLoad(){ itMenu(); doOnresize(); }


							var locked;
							function doOnload()
							{
							 
							 try
							 {
								var layer;
								if(layer = document.getElementById('LOx_layer'))
								{
								 
								 	var right,left = getLeft(1);
									layer.style.left = left;
									layer.style.top = 0;
									layer.style.visible = hidden;
									document.getElementById('LOx_table').left = left;
									for(i=2;column = document.getElementById('LOx'+i);i++)
									{
									 
										right = getLeft(i);
										document.getElementById('LO_col'+(i-1)).width = right-left-14;
										left = right;
									}
									document.getElementById('LO_col'+(i-2)).width = left-getLeft(i-2)-10;									
									total_width = getLeft(i);
									document.getElementById('LOx_table').width = total_width;
									if(navigator.userAgent.indexOf('Safari')==-1&&navigator.userAgent.indexOf('Microsoft Internet Explorer')==-1)
										window.setInterval('doOnscroll()',50);
									else
										window.onscroll = doOnscroll;
									window.onresize = doOnresize;
								}

								if(layer = document.getElementById('LOy_layer'))
								{
								 
									var bottom,top = getTop(1);
									layer.style.left = getLeft(1);
									layer.style.top = top;
									document.getElementById('LOy_table').left = left;

									for(i=2;row = document.getElementById('LOy'+i);i++)
									{
										bottom = getTop(i);
										document.getElementById('LO_row'+(i-1)).height = bottom-top-2;
										top = bottom;
									}
									document.getElementById('LOy_table').width = getLeft(2)-getLeft(1);
									if(navigator.userAgent.indexOf('Safari')==-1&&navigator.userAgent.indexOf('Microsoft Internet Explorer')==-1)
										window.setInterval('doOnscroll()',50);
									else
										window.onscroll = doOnscroll;
									window.onresize = doOnresize;
								}
								
								
								}
								catch(err)
								{
								
								}
							}

							function doOnresize()
							{
								doOnload();
								doOnscroll();
							}

							function doOnscroll()
							{
								var layer;
								if(layer = document.getElementById(\"LOx_layer\"))
								{
									var pY = (window.document.body.scrollTop ? window.document.body.scrollTop : window.pageYOffset);
									if(pY > getListTop())
										layer.style.visibility = \"visible\";
									else if(pY < getListTop())
										layer.style.visibility = \"hidden\";
									if(pY)
										layer.style.top = (window.document.body.scrollTop ? window.document.body.scrollTop : window.pageYOffset);
								}

								if(layer = document.getElementById(\"LOy_layer\"))
								{
									var pX = (window.document.body.scrollLeft ? window.document.body.scrollLeft : window.pageXOffset);
									if(pX > 0)
										layer.style.visibility = \"visible\";
									else if(pX == 0)
										layer.style.visibility = \"hidden\";
									if(pX)
										layer.style.left = (window.document.body.scrollLeft ? window.document.body.scrollLeft : window.pageXOffset);
								}
							}

							function getLeft(column_number)
							{
								var x = 0;
								if(document.layers)
									x = document.layers['LOx'+column_number].pageX;
								else if(document.all || document.getElementById)
								{
									var cell = document.all ? document.all['LOx'+column_number] : document.getElementById('LOx'+column_number);
									while(cell)
									{
										x += cell.offsetLeft;
										cell = cell.offsetParent;
									}
								}
								return x-8;
							}

							function getTop(row_number)
							{
								var y = 0;
								if(document.layers)
									y = document.layers['LOy'+row_number].pageY;
								else if(document.all || document.getElementById)
								{
									var cell = document.all ? document.all['LOy'+row_number] : document.getElementById('LOy'+row_number);
									while(cell)
									{
										y += cell.offsetTop;
										cell = cell.offsetParent;
									}
								}
								return y-8;
							}

							function getListTop()
							{
								var y = 0;
								if(document.layers)
									y = document.layers['LOx1'].pageY;
								else if(document.all || document.getElementById)
								{
									var cell = document.all ? document.all['LOx1'] : document.getElementById('LOx1');
									while(cell)
									{
									  y += cell.offsetTop;
									  cell = cell.offsetParent;
									}
								}
								return y;
							}

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
						</script>
					
					<link rel='stylesheet' type='text/css' href='styles/Help.css'>";
                echo "<link rel=stylesheet type=text/css href=styles/Calendar.css>";
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
?>
