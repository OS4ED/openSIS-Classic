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
$conn_string = $_SESSION['conn'];
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
                        <h3>openSIS Installation - Database Selection</h3>                 
                    </div>
                    <div class="panel-body">
                        <div class="installation-steps-wrapper">
                            <div class="installation-instructions">
                                <ul class="installation-steps-label">
                                    <li>Choose Package</li>
                                    <li>Database Connection</li>
                                    <li class="active">Database Selection</li>
                                    <li>Site Admin Account Setup</li>
                                    <li>Ready to Go!</li>
                                </ul>
                                <!--<h4 class="no-margin">Installation Prerequisite</h4>
                                <p>Before you install openSIS, you need to have Apache web server, MySQL database server and php scripting language setup in your machine.</p>
                                <p>You can download an all-inclusive package from: <a href="https://www.apachefriends.org/download.html">https://www.apachefriends.org/download.html</a></p>
                                <p>Select the download package for the operating system you are using on your machine. Install that first and then start the openSIS installer.</p>-->
                            </div>
                            <div class="installation-steps valign-top">
                                <h2 class="text-center">Thanks for providing MySQL Connection Information</h2><br/>
                                <h5 class="text-center">Please select the Database from the list<br/>that you want to upgrade from.</h5><br/>

                                <form name='selectdb' class="form-horizontal" id='selectdb' method='post' action="UpgradeProcessingMsg.php">
                                    <?php
                                    $connection = new mysqli($_SESSION['host_mod'], $_SESSION['username'], $_SESSION['password']) or die();

                                    if ($connection->connect_errno > 0)
                                        die('Not connected');
                                    $sql = "show databases;";
                                    $res = $connection->query($sql);
                                    echo '<div class="form-group text-center">';
                                    echo "<select name='sdb' id='sdb' class='form-control' style=\"width: auto; display: inline-block;\">";
                                    while ($row = $res->fetch_row()) {
                                        if ($row[0] != 'information_schema' && $row[0] != 'mysql' && $row[0] != 'performance_schema' && $row[0] != 'phpmyadmin')
                                            echo "<option>" . $row[0] . "</option>";
                                    }
                                    echo "</select>";
                                    echo '</div>';
                                    ?>
                                    <div class="text-center">
                                        <input type="submit" value="Save & Next" class="btn btn-primary" name="Add_DB"  />
                                    </div>
                                    <br/>
                                </form>
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
