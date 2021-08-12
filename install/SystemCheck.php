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

function _isCurl() {
    return function_exists('curl_version');
}

function apacheVer() {
    $version = explode("/", $_SERVER['SERVER_SOFTWARE']);
    $softNum = explode(" ", $version[1]);
    $num = explode(".", $softNum[0]);
    return $num[0] . '.' . $num[1];
}

$err = 0;
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
                    <div class="panel-heading clearfix">
                        <div class="logo">
                            <img src="assets/images/opensis_logo.png" alt="openSIS">
                        </div>   
                        <h3>openSIS Installation - System Requirements</h3>                 
                    </div>
                    <div class="panel-body">
                        <div class="installation-steps-wrapper">
                            <div class="installation-instructions">
                                <ul class="installation-steps-label">
                                    <li>Choose Package</li>
                                    <li class="active">System Requirements</li>
                                    <li>Database Connection</li>
                                    <li>Database Selection</li>
                                    <li>Site Admin Account Setup</li>
                                    <li>Ready to Go!</li>
                                </ul>
                                <!--<h4 class="no-margin">Installation Prerequisite</h4>
                                <p>Before you install openSIS, you need to have Apache web server, MySQL database server and php scripting language setup in your machine.</p>
                                <p>You can download an all-inclusive package from: <a href="https://www.apachefriends.org/download.html">https://www.apachefriends.org/download.html</a></p>
                                <p>Select the download package for the operating system you are using on your machine. Install that first and then start the openSIS installer.</p>-->
                            </div>
                            <div class="installation-steps valign-top">
                                <h4 class="m-t-0 m-b-25">Let’s check your system configuration:</h4>
                                <div class="row">
                                    <div class="col-xs-8">
                                        PHP Version 5.4 or greater
                                        <?php
                                        if (phpversion() <= 5.4) {
                                            echo '<p class="text-danger">Upgrade PHP version from here <A href="http://php.net/downloads.php.">http://php.net/downloads.php.</a></p>';
                                        }
                                        ?>
                                    </div>
                                    <div class="col-xs-4 text-right">
                                        <?php
                                        if (phpversion() > 5.4) {
                                            echo '<span class="text-success"><b>OK</b></span>';
                                        } else {
                                            echo '<span class="text-danger"><b>FAIL</b></span>';
                                            $err = 1;
                                        }
                                        ?>
                                    </div>
                                </div><br/>
                                <div class="row">
                                    <div class="col-xs-8">
                                        cURL
                                        <?php
                                        if (_isCurl() != 1) {
                                            echo '<p class="text-danger">Upgrade cURL from here <a href="https://curl.haxx.se/download.html">https://curl.haxx.se/download.html</a></p>';
                                        }
                                        ?>
                                    </div>
                                    <div class="col-xs-4 text-right">
                                        <?php
                                        if (_isCurl() == 1) {
                                            echo '<span class="text-success"><b>OK</b></span>';
                                        } else {
                                            echo '<span class="text-warning"><b>Warning</b></span>';
                                            //$err = 1;
                                        }
                                        ?>
                                    </div>
                                </div><br/>
                                <div class="row">
                                    <div class="col-xs-8">
                                        Apache Version 2.4 or greater
                                        <?php
                                       if (intval(apacheVer())>1 && apacheVer() < 2.4) {
                                            echo '<p class="text-danger">Upgrade Apache from here <A href="https://httpd.apache.org/download.cgi">https://httpd.apache.org/download.cgi</a></p>';
                                        }
                                        ?>
                                    </div>
                                    <div class="col-xs-4 text-right">
                                        <?php
                                       if (intval(apacheVer())>1 && apacheVer()< 2.4) {
                                                echo '<span class="text-danger"><b>FAIL</b></span>';
                                        } else {
                                            echo '<span class="text-success"><b>OK</b></span>';
                                           // $err = 1;
                                        }
                                        ?>
                                    </div>
                                </div>

                                <br/>
                                <br/>

                                <?php
                                if ($err == 1) {
                                    echo '<div class="text-danger text-italic"><i class="fa fa-info-circle"></i> It seems like some of the system requirements are not met to continue installation. Please fulfill the pre-requisites and click <b>Check Again</b>.</div>';
                                } else {
                                    echo '<div class="text-success text-italic">Hurray! your system meets the requirements for openSIS installation.</div>';
                                }
                                ?>
                                <hr/>

                                <div class="text-right">
                                    <?php
                                    if ($err == 1) {
                                        echo '<a href="SystemCheck.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Check Again</a> &nbsp; ';
                                        echo '<a href="#" class="btn btn-default" disabled>Continue</a>';
                                    } else {
                                        echo '<a href="Step1.php" class="btn btn-success">Continue</a>';
                                    }
                                    ?>
                                </div>
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

