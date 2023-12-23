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
error_reporting(0);
session_start();
session_destroy();
echo '<script type="text/javascript">
var page=parent.location.href.replace(/.*\//,"");
if(page && page!="index.php"){
	window.location.href="index.php";
	}

</script>';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>openSIS Installer</title>
        <link href="../assets/css/icons/fontawesome/styles.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/installer.css?v=<?php echo rand(000, 999); ?>" type="text/css" />
        <noscript><META http-equiv=REFRESH content='0;url=../EnableJavascript.php' /></noscript>
    </head>
    <body class="outer-body">
        <section class="login">
            <div class="login-wrapper">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="logo">
                            <img src="assets/images/opensis_logo.png" alt="openSIS">
                        </div>   
                        <h3>openSIS Installation</h3>                 
                    </div>
                    <div class="panel-body">
                        <div class="installation-steps-wrapper">
                            <div class="installation-instructions">
                                <ul class="installation-steps-label">
                                    <li class="active">Choose Package</li>
                                    <li>System Requirements</li>
                                    <li>Database Connection</li>
                                    <li>Database Selection</li>
                                    <li>School Information</li>
                                    <li>Site Admin Account Setup</li>
                                    <li>Ready to Go!</li>
                                </ul>
                                <!--<h4 class="no-margin">Installation Prerequisite</h4>
                                <p>Before you install openSIS, you need to have Apache web server, MySQL database server and php scripting language setup in your machine.</p>
                                <p>You can download an all-inclusive package from: <a href="https://www.apachefriends.org/download.html">https://www.apachefriends.org/download.html</a></p>
                                <p>Select the download package for the operating system you are using on your machine. Install that first and then start the openSIS installer.</p>-->
                            </div>
                            <div class="installation-steps">
                                <table style="height:270px; width: 70%;" border="0" cellspacing="12" cellpadding="12" align="center">
                                    <tr>
                                        <?php
                                        if (isset($_REQUEST['upreq']) && $_REQUEST['upreq'] == 'true') {
                                            echo '<td>You were redirected to this page because an upgrade is needed.<br> Please, proceed using the action below.</td>';
                                            echo '</tr><tr>';
                                            echo '<td valign="middle" align="center"><a href="Step0.1.php?mod=upgrade"><img src="assets/images/icon-upgrade.png" alt="Upgrade OpenSIS" /><br/><h5 class="text-black"><b>Upgrade</b><br/><small>(From ver 4.7 thru 6.0)</small></h5></a></td>';
                                        } else {
                                            echo '<td valign="middle" align="center"><a href="SystemCheck.php"><img src="assets/images/icon-package.png" alt="New Installation" /><br/><h5 class="text-black"><b>New Installation</b><br/><small>(Ver 9.1)</small></h5></a></td>';
                                            echo '<td valign="middle" align="center"><a href="Step0.1.php?mod=upgrade"><img src="assets/images/icon-upgrade.png" alt="Upgrade OpenSIS"/><br/><h5 class="text-black"><b>Upgrade</b><br/><small>(From ver 7.1 onwards)</small></h5></a></td>';
                                        }
                                        ?>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <footer>
                    Copyright &copy; Open Solutions for Education, Inc. (<a href="http://www.os4ed.com">OS4ED</a>).
                </footer>
            </div>
        </section>
    </body>
</html>

