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
                            <div class="installation-steps">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table style="height:270px; width: 80%; margin: 30px auto;" border="0" cellspacing="12" cellpadding="12" align="center">
                                            <tr>
                                                <td align="center" valign="middle">
                                                    <div class="text-center">
                                                        <img src="assets/images/warning.svg" width="50" />
                                                    </div><br/>
                                                    <p>Please be advised that only openSIS-CE version 7.1 and above can be upgraded to the latest version using this installer. If you are running version 7.1 or above, click Continue to upgrade, otherwise click Go Back and try the New Installation.</p>
                                                    
                                                    <p class="text-danger"><b>Please Note:</b> Remember to backup your current database and keep it in a safe place
                                                        before attempting an upgrade. OS4ED will not be responsible for data
                                                        corruption or data loss if the upgrade is unsuccessful for any reason.</p>
                                                    <br/>
                                                    <div class="text-center">
                                                        <?php
                                                        echo '<a href="Step0.php" class="btn btn-default">Go Back</a> &nbsp; <a href="Step1.php?mod=upgrade" class="btn btn-success">Continue</a>';
                                                        ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <footer>
                    Copyright &copy; Open Solutions for Education, Inc. (<a href="http://www.os4ed.com">OS4Ed</a>).
                </footer>
            </div>
        </section>
    </body>
</html>
