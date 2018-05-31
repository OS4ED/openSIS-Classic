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
include('RedirectRootInc.php');
include("Data.php");
include("Warehouse.php");
db_start();
//$connection=mysql_connect($DatabaseServer,$DatabaseUsername,$DatabasePassword);
//mysql_select_db($DatabaseName,$connection);
$log_msg = DBGet(DBQuery("SELECT MESSAGE FROM login_message WHERE DISPLAY='Y'"));

Warehouse('header');
echo '<meta http-equiv="Content-type" content="text/html;charset=UTF-8">';
echo '<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,400italic,600italic" rel="stylesheet" type="text/css">';
echo '<link href="styles/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet">';
echo '<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">';
echo '<link href="assets/css/extras/css-checkbox-switch.css" rel="stylesheet">';
echo '<link rel="stylesheet" type="text/css" href="assets/css/login.css">';
echo '<script type="text/javascript" src="js/Tabmenu.js"></script>';
echo "<script type='text/javascript'>
	function delete_cookie (cookie_name)
		{
  			var cookie_date = new Date ( );
  			cookie_date.setTime ( cookie_date.getTime() - 1 );
			  document.cookie = cookie_name += \"=; expires=\" + cookie_date.toGMTString();
		}
                
</script>";
?>

<BODY onLoad="document.loginform.USERNAME.focus();
        delete_cookie('dhtmlgoodies_tab_menu_tabIndex');">
    <div class="clock">
        <ul>
            <li id="hours"></li>
            <li id="point">:</li>
            <li id="min"></li>
        </ul>
        <div id="Date"></div>
    </div>
    <section class="login">
        <div class="login-wrapper">

            <div class="panel">

                <div class="panel-heading">
                    <div class="logo">
                        <img src="assets/images/opensis_logo.png" alt="openSIS" />
                    </div>                    
                    <h3>Student Information System</h3>
                </div>
                <div class="panel-body">

                    <div class="row">
                        <!--                        <div class="col-md-5 text-center school-logo">
                                                    <img src="assets/images/peach_county_logo.png" width="180" />
                                                </div>-->
                        <div class="col-md-12">
                            <?php
                            if ($_REQUEST['reason'])
                                $note[] = 'You must have javascript enabled to use openSIS.';

                            if ($error[0] != '') {
                                ?>
                                <div class="alert alert-danger" role="alert">   
                                    <i aria-hidden="true" class="fa fa-exclamation-triangle"></i>
                                    <?php
                                    echo $error[0];
                                    ?>
                                </div>   
                                <?php
                            }
                            ?>
                            <form name=loginform method='post' class="text-left" action='index.php'>
                                <?php
                                if ($_REQUEST['maintain'] == 'Y') {
                                    ?>
                                    <div class="form-group">
                                        <label><b>openSIS is under maintenance and login privileges have been turned off. Please log in when it is available again.</b></label>
                                    </div> 
                                    <?php
                                }
                                if (isset($_SESSION['conf_msg']) && $_SESSION['conf_msg'] != '') {
                                    ?>
                                    <div class="form-group">
                                        <label><b><?php echo $_SESSION['conf_msg']; ?></b></label>
                                        <?php
                                        unset($_SESSION['conf_msg']);
                                        ?>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="form-group">
                                    <?php
                                    if (isset($_COOKIE['remember_me_name']))
                                        $name = $_COOKIE['remember_me_name'];
                                    if (isset($_SESSION['fill_username'])) {
                                        $name = $_SESSION['fill_username'];
                                        unset($_SESSION['fill_username']);
                                    }
                                    ?>
                                    <input type="text" class="form-control username" id="username" placeholder="Enter Username" name='USERNAME' value="<?php echo $name; ?>" >
                                </div>
                                <div class="form-group">
                                    <?php
                                    if (isset($_COOKIE['remember_me_pwd']))
                                        $pwd = $_COOKIE['remember_me_pwd'];
                                    ?>
                                    <input type="password" class="form-control password" placeholder="Enter Password" id="password" name='PASSWORD' AUTOCOMPLETE = 'off' value="<?php echo $pwd; ?>">
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <div class="checkbox checkbox-switch switch-success switch-sm">
                                            <label>
                                                <input type="checkbox" name="remember" id="remember" <?php
                                                if (isset($_COOKIE['remember_me_name'])) {
                                                    echo 'checked="checked"';
                                                } else {
                                                    echo '';
                                                }
                                                ?> /><span></span> Remember Me
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <br/>
                                <p>
                                    <button name='log' type="submit" class="btn btn-success btn-lg btn-block" onMouseDown="Set_Cookie('dhtmlgoodies_tab_menu_tabIndex', '', -1)">Login</button>
                                </p>
                                <p class="text-center"><a href="ForgotPass.php">Forgot Password ?</a></p>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="loader-container" style="display: none;">
                    <div class="loader loader1"></div>
                    <div class="loader loader2"></div>
                    <div class="loader loader3"></div>
                    <div class="loader loader4"></div>
                </div>
                <!--<div class="panel-footer">
                <?php //echo $log_msg[1]['MESSAGE']; ?>
                </div>-->
            </div>
            <footer>
                Copyright &copy; Open Solutions for Education, Inc. (<a href="http://www.os4ed.com">OS4Ed</a>).
            </footer>

        </div>
    </section>


    <script src="assets/js/core/libraries/jquery.min.js"></script>

    <script type="text/javascript">
                                        $(document).ready(function () {

                                            var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                                            var dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]


                                            var newDate = new Date();

                                            newDate.setDate(newDate.getDate());

                                            $('#Date').html(dayNames[newDate.getDay()] + ", " + monthNames[newDate.getMonth()] + ' ' + newDate.getDate() + ', ' + newDate.getFullYear());



                                            setInterval(function () {
                                                // Create a newDate() object and extract the minutes of the current time on the visitor's
                                                var minutes = new Date().getMinutes();
                                                // Add a leading zero to the minutes value
                                                $("#min").html((minutes < 10 ? "0" : "") + minutes);
                                            }, 1000);

                                            setInterval(function () {
                                                // Create a newDate() object and extract the hours of the current time on the visitor's
                                                var hours = new Date().getHours();
                                                // Add a leading zero to the hours value
                                                $("#hours").html((hours < 10 ? "0" : "") + hours);
                                            }, 1000);
                                        });
    </script>
    <!--custom script-->
    <script src="js/custom.js"></script>
</body>
