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
        <script type="text/javascript" src="js/Validator.js"></script>
        <script type="text/javascript">
            function showAlert() {
                var divAlert = document.getElementById('divAlert');
                var divConnInfo = document.getElementById('divConnInfo');

                divAlert.style.display = '';
                divConnInfo.style.display = 'none';
            }
            function hideAlert() {
                var divAlert = document.getElementById('divAlert');
                var divConnInfo = document.getElementById('divConnInfo');

                divAlert.style.display = 'none';
                divConnInfo.style.display = '';
            }
        </script>
        <?php
        echo '<script type="text/javascript">
var page=parent.location.href.replace(/.*\//,"");
if(page && page!="index.php" ){
	window.location.href="index.php";
	}

</script>';
        ?>
    </head>
    <body class="outer-body">
        <section class="login">
            <div class="login-wrapper">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="logo">
                                    <img src="assets/images/opensis_logo.png" alt="openSIS">
                                </div>
                                <?php if (isset($_REQUEST['mod']) && $_REQUEST['mod']!='upgrade') { ?>
                                    <h3>openSIS Installation - Database Connection</h3>
                                <?php } else { ?>
                                    <h3>openSIS Installation - Database Connection</h3>
                                <?php } ?>
                            </div>
                            <!--                            <div class="col-xs-4 text-center" style="padding: 30px 20px 0;">
                            <?php
//                                if ($_SESSION['mod'] != 'upgrade') {
//                                    echo 'Installation progress';
//                                    echo '<div class="progress no-margin">';
//                                    echo '<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%">';
//                                    echo '</div>';
//                                    echo '</div>';
//                                } else {
//                                    echo 'Installation progress';
//                                    echo '<div class="progress no-margin">';
//                                    echo '<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: 25%">';
//                                    echo '</div>';
//                                    echo '</div>';
//                                }
                            ?>
                                                        </div>-->
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="installation-steps-wrapper">
                            <div class="installation-instructions">

                                <?php if (isset($_REQUEST['mod']) && $_REQUEST['mod']!='upgrade') { ?>
                                    <ul class="installation-steps-label">
                                        <li>Choose Package</li>
                                        <li>System Requirements</li>
                                        <li class="active">Database Connection</li>
                                        <li>Database Selection</li>
                                        <li>School Information</li>
                                        <li>Site Admin Account Setup</li>
                                        <li>Ready to Go!</li>
                                    </ul>
                                <?php } else { ?>
                                    <ul class="installation-steps-label">
                                        <li>Choose Package</li>
                                        <li class="active">Database Connection</li>
                                        <li>Database Selection</li>
                                        <li>School Information</li>
                                        <li>Site Admin Account Setup</li>
                                        <li>Ready to Go!</li>
                                    </ul>
                                    <?php
                                }
//                                if ($_REQUEST['mod'] == 'upgrade') {
//                                    echo '<h4 class = "no-margin">You have chosen upgrade</h4>';
//                                    $_SESSION['mod'] = 'upgrade';
//                                } else {
//                                    echo '<h4 class = "no-margin">Beginning new openSIS installation</h4>';
//                                }
//
//                                if ($ver_comp == 'true') {
//                                    echo '<p class="text-success"><i class="fa fa-check-circle"></i> Your php version is ' . $version . '. You can install this system</p>';
//                                    echo '<p>Provide MySQL server connection information. </p>';
//                                    echo '<p>You must also have the MySQL administrative username and password to continue.</p>';
//                                } else {
//                                    echo '<p class="text-danger"><i class="fa fa-exclamation-circle"></i> Your php version is ' . $version . '. But your system must have php version ' . $version_allow . ' to install this system</p>';
//                                }
                                ?>

                            </div>
                            <div class="installation-steps">
                                <?php
                                error_reporting(0);
                                session_start();
                                $version = phpversion();
                                $version_allow = '5.0.0';
                                if (!version_compare($version, $version_allow, '>=')) {
                                    $ver_comp = 'false';
                                } else {
                                    $ver_comp = 'true';
                                }



                                echo '<div id="divAlert" style="display:none;">';

                                $myFile = "../Data.php";
                                $fh1 = fopen($myFile, 'w');

                                if ($fh1 == FALSE) {
                                    echo '<h4 class="m-t-0 m-b-15 text-danger"><i class="fa fa-exclamation-circle text-danger"></i> This install has no rights to create or update file <b>Data.php</b>.</h4>';
                                    echo '<p class="m-b-15">You may proceed with this installation, but database access information will not be saved and the install process will restart when trying to use this system again.</p>';
                                }
                                fclose($fh1);

//                                $myFile = "../assets/studentphotos/dummy.txt";
//                                $fh2 = fopen($myFile, 'w');
//
//                                if ($fh2 == FALSE) {
//                                    echo '<br />';
//                                    echo '<br />Unable to write inside assets/studentphotos directory.';
//                                    echo '<br />Student photos will fail to be saved until this permission issue is solved';
//                                } else {
//                                    unlink($myFile);
//                                }
//
//
//                                fclose($fh2);
//
//                                $myFile = "../assets/userphotos/dummy.txt";
//                                $fh3 = fopen($myFile, 'w');
//
//                                if ($fh3 == FALSE) {
//                                    echo '<br />';
//                                    echo '<br />Unable to write inside assets/userphotos directory.';
//                                    echo '<br />User photos will fail to be saved until this permission issue is solved';
//                                } else {
//                                    unlink($myFile);
//                                }
//                                fclose($fh3);

                                echo '<p class="m-b-25">It is recommended to solve all permission issue before performing the installation.</p>';
                                echo '<hr/>';
                                echo '<div class="text-right">';
                                echo '<a href="Step0.php" class="btn btn-default">Cancel</a> &nbsp; ';
                                echo '<input type="button" value="Continue" class="btn btn-success" onclick="hideAlert()" />';
                                echo '</div>';
                                echo '</div>';

                                if ($fh1)
                                // show Connection information fields
                                    echo '<div id="divConnInfo" style="">';
                                else
                                // hide Connection information fields
                                    echo '<div id="divConnInfo" style="display:none;">';
				if ($ver_comp == 'true') {
				 $action='Ins1.php';
				}




                                if(isset($_REQUEST['mod']))
                                {
                                echo '<form name="step1" id="step1" method="post" action="'.$action.'?mod=upgrade">';

					
                                }
                                else
                                {
                                    echo '<form name="step1" id="step1" method="post" action="'.$action.'">';
                                }
                                ?> 

                                    <h4 class="m-t-0 m-b-25">Please Enter MySQL Connection Information</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Server</label>
                                                <input type="text" name="server" size="20" value="localhost" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Port</label>
                                                <input type="text" name="port" size="20" value="3306" class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">MySQL Username</label>
                                                <input type="text" name="addusername" size="20" value="root" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">MySQL Password</label>
                                                <input type="password" name="addpassword" size="20" class="form-control" />
                                            </div>
                                        </div>
                                    </div>

                                    <hr/>
                                    <div class="text-right">
                                        <input type="submit" value="Save & Next" class="btn btn-success" name="DB_Conn" />
                                    </div>

                                    <script
                                        type="text/javascript">
                                            <?php
                                            if ($fh1)
                                                echo 'hideAlert();';
                                            else
                                                echo 'showAlert();';
                                            ?>
                                            var frmvalidator = new Validator("step1");
                                            frmvalidator.addValidation("server", "req", "Please enter the Server Name");
                                            frmvalidator.addValidation("port", "req", "Please enter the Port");
                                            frmvalidator.addValidation("addusername", "req", "Please enter the MySQL Admin Username");
                                    </script>
                                </form>
                                <?php echo ($fh1) ? '</div>' : '</div>'; ?>
                            </div>

                        </div>
                    </div>
                </div>
                <footer>
                    Copyright © Open Solutions for Education, Inc. (<a href="http://www.os4ed.com">OS4ED</a>).
                </footer>
            </div>
        </section>
    </body>
</html>
