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
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,400italic,600italic" rel="stylesheet" type="text/css">
        <link href="../styles/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/installer.css?v=<?php echo rand(000, 999); ?>" type="text/css" />
        <noscript><META http-equiv=REFRESH content='0;url=../EnableJavascript.php' /></noscript>
    </head>
    <body class="outer-body">
        <section class="login">
            <div class="login-wrapper">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-8 text-left">
                                <div class="logo">
                                    <img src="assets/images/opensis_logo.png" alt="openSIS">
                                </div>
                                <h3>Step 2 of 4</h3>
                            </div>
                            <div class="col-xs-4 text-center" style="padding: 30px 20px 0;">
                                Installation Progress
                                <div class="progress no-margin">
                                    <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="width: 50%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="installation-steps-wrapper">
                            <div class="installation-steps">
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
                            <div class="installation-instructions">
                                <h4 class="no-margin">Installation Instructions</h4>
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
