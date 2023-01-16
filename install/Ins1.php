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
include '../functions/ParamLibFnc.php';
require_once("../functions/PragRepFnc.php");
error_reporting(0);
session_start();

$_SESSION['username'] = $_POST["addusername"];
$_SESSION['password'] = $_POST["addpassword"];
$_SESSION['server'] = $_POST["server"];
$_SESSION['port'] = $_POST["port"];
$_SESSION['host'] = $_POST['server'] . ($_POST['port'] != '3306' ? ':' . $_POST['port'] : '');
$err .= '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>openSIS Installer</title>
        <link href="../assets/css/icons/fontawesome/styles.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/installer.css?v='.rand(000, 999).'" type="text/css" />
        <noscript><META http-equiv=REFRESH content="0;url=../EnableJavascript.php" /></noscript>
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
                                <!--<h4 class="no-margin">Possible causes are:</h4>
                                <ol>
                                    <li>MySQL is not installed. Try downloading from <a href="http://dev.mysql.com/downloads/" target=_blank>MySQL Website</a></li>
                                    <li>Username or Password or MySQL Configuration is incorrect</li>
                                    <li>Php.ini is not properly configured. Search for MySQL in php.ini</li>
                                </ol>-->
                                <ul class="installation-steps-label">
                                    <li>Choose Package</li>
                                    <li>System Requirements</li>
                                    <li class="active">Database Connection</li>
                                    <li>Database Selection</li>
                                    <li>Site Admin Account Setup</li>
                                    <li>Ready to Go!</li>
                                </ul>
                            </div><!-- /.installation-instructions -->
                            <div class="installation-steps">

                                <h2 class="text-center">Couldn\'t connect to database server: ' . $_SESSION['host'].'</h2><br/>';
                                if (clean_param($_REQUEST['mod'], PARAM_ALPHAMOD) == 'upgrade') {
                                    $err .= '<p class="text-center"><a href="Step1.php?mod=upgrade" class="btn btn-primary"><i class="fa fa-refresh"></i> Try Again</a></p>';
                                } else {
                                    $err .= '<p class="text-center"><a href="Step1.php" class="btn btn-primary"><i class="fa fa-refresh"></i> Try Again</a></p>';
                                }
                            $err .= '</div>
                        </div><!-- /.installation-steps-wrapper -->
                    </div><!-- /.panel-body -->
                </div><!-- /.panel -->                
                <footer>
                    Copyright &copy; Open Solutions for Education, Inc. (<a href="http://www.os4ed.com">OS4ED</a>).
                </footer>
            </div><!-- /.login-wrapper -->
        </section><!-- /.login -->
    </body>
</html>';
//$dbconn = mysqli_connect($_SESSION['host'],$_SESSION['username'],$_SESSION['password'])
try{
    $dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], '', $_SESSION['port']);
    } catch (Exception $e) {
        // echo 'Caught exception: ', $e->getMessage(), "\n";
        exit($err);
    }
if ($dbconn->connect_errno != 0) {
    exit($err);
} else {
    $qr = $dbconn->query("SHOW VARIABLES LIKE 'sql_mode'");
    $res = $qr->fetch_assoc();
    $res_arr = explode(',', $res['Value']);
    if (in_array('STRICT_TRANS_TABLES', $res_arr)) {
        $err = '<!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>openSIS Installer</title>
                <link href="../assets/css/icons/fontawesome/styles.min.css" rel="stylesheet">
                <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
                <link rel="stylesheet" href="assets/css/installer.css?v='.rand(000, 999).'" type="text/css" />
                <noscript><META http-equiv=REFRESH content="0;url=../EnableJavascript.php" /></noscript>
            </head>
            <body class="outer-body">
                <section class="login">
                    <div class="login-wrapper">
                        <div class="panel" style="width: 50%;">
                            <div class="panel-heading">
                                <div class="logo">
                                    <img src="assets/images/opensis_logo.png" alt="openSIS">
                                </div>
                                <h3>openSIS Installation</h3>
                            </div>
                            <div class="panel-body">
                                <div class="padding-20 class="text-center"">
                                    <h2 class="text-center">Couldn\'t connect to database server: ' . $_SESSION['host'].'</h2><br/>
                                    <h5 class="text-center">Possible causes are:</h5>
                                    <p class="text-center">Strict mode is enabled. Please disable Strict mode and restart your mysql and apache.
                                    </p>
                                </div>
                            </div><!-- /.panel-body -->
                        </div><!-- /.panel -->
                        <footer>
                            Copyright &copy; Open Solutions for Education, Inc. (<a href="http://www.os4ed.com">OS4ED</a>).
                        </footer>
                    </div><!-- /.login-wrapper -->
                </section><!-- /.login -->
            </body>
        </html>';
        

        exit($err);
    }
}

if (clean_param($_REQUEST['mod'], PARAM_ALPHAMOD) == 'upgrade') {
    header('Location: Selectdb.php');
} else {
    header('Location: Step2.php');
}
?>
                    
