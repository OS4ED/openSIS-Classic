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

include 'lang/supportedLanguages.php';

if(isset($_REQUEST['language'])){
    if(in_array($_REQUEST['language'], array_keys($supportedLanguages))){
    $langCode2 = $_REQUEST['language'];
    }
} else if (isset($_COOKIE['remember_me_lang'])){
    if(in_array($_COOKIE['remember_me_lang'], array_keys($supportedLanguages))){
        $langCode2 = $_COOKIE['remember_me_lang'];
}
}

if(!isset($langCode2)){
    $langCode2 = 'en';
}

$_SESSION['language'] = $langCode2;

include "lang/lang_" . $_SESSION['language'] . ".php";

include 'RedirectRootInc.php';
include "Data.php";
include "Warehouse.php";

$cont = db_start();
$log_msg = DBGet(DBQuery("SELECT MESSAGE FROM login_message WHERE DISPLAY='Y'"));
$maintain_qr = DBGet(DBQuery('select system_maintenance_switch from system_preference_misc where system_maintenance_switch=\'Y\''));
$extra_header  = '';
$extra_header .= '<meta http-equiv="Content-type" content="text/html;charset=UTF-8">';
$extra_header .= '<link href="assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">';
$extra_header .= '<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">';
$extra_header .= '<link href="assets/css/extras/css-checkbox-switch.css" rel="stylesheet">';
$extra_header .= '<link rel="stylesheet" type="text/css" href="assets/css/login.css">';
$extra_header .= '<script type="text/javascript" src="js/Tabmenu.js"></script>';
$extra_header .= "<script type='text/javascript'>
	function delete_cookie (cookie_name)
		{
  			var cookie_date = new Date ( );
  			cookie_date.setTime ( cookie_date.getTime() - 1 );
			  document.cookie = cookie_name += \"= _; expires=\" + cookie_date.toGMTString();
		}
                
</script>";
Warehouse('header', $extra_header);
require_once('functions/langFnc.php');
// var_dump(langDirection());
// var_dump($supportedLanguages[$_SESSION['language']]['direction']);
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
                    <h3><?= _studentInformationSystem ?></h3>
                </div>
                <div class="panel-body">

                    <div class="row">
                        <!-- <div class="col-md-5 text-center school-logo">
                            <img src="assets/images/peach_county_logo.png" width="180" />
                        </div> -->
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
                                if ($maintain_qr[1]['SYSTEM_MAINTENANCE_SWITCH'] == 'Y') {
                                    ?>
                                    <div class="form-group">
                                        <h4 class="text-center text-danger"><i class="icon-warning22" style="font-size: 50px;"></i><br/><br/>openSIS is under maintenance and login privileges have been turned off. Please log in when it is available again.</h4>
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
                                    include_once './AuthCryp.php';
                                    if (isset($_COOKIE['remember_me_name']))
                                        $name = mysqli_real_escape_string($cont, strip_tags(trim(cryptor($_COOKIE['remember_me_name'], 'DEC'))));
                                    if (isset($_SESSION['fill_username'])) {
                                        $name = $_SESSION['fill_username'];
                                        unset($_SESSION['fill_username']);
                                    }
                                    ?>
                                    <input type="text" class="form-control username" id="username" placeholder="<?=_enterUsername?>" name='USERNAME' value="<?php echo $name; ?>">
                                </div>
                                <div class="form-group">
                                    <?php
                                    if (isset($_COOKIE['remember_me_pwd']))
                                        $pwd = mysqli_real_escape_string($cont, strip_tags(trim(cryptor($_COOKIE['remember_me_pwd'], 'DEC'))));
                                    ?>
                                    <input type="password" class="form-control password" placeholder="<?=_enterPassword?>" id="password" name='PASSWORD' AUTOCOMPLETE='off' value="<?php echo $pwd; ?>">
                                </div>
                                <div class="language-selection">
                                    <i class="icon-earth"></i>
                                        <select class="select-search" name="language" id="language" onchange="window.location = 'index.php?language='+this.value">
                                        <?php
                                            foreach ($supportedLanguages as $code => $value) {
                                                echo "<option value='$code' ".($langCode2 == $code ? 'selected' : '')." >$value[name]</option>";
                                            }
                                        ?>
                                        </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group text-center">
                                        <div class="checkbox checkbox-switch switch-success switch-sm">
                                            <label>
                                                <input type="checkbox" name="remember" id="remember" <?php echo (isset($_COOKIE['remember_me_name'])) ? 'checked="checked"' : ''; ?> /><span></span> <?=_rememberMe?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 text-center">
                                    <p>
                                        <a href="ForgotPass.php" id="forgotPass"><?=_forgotUsernamePassword?>?</a>
                                    </p>
                                                                    </div>
                                </div>
                                <?php
                                    CSRFSecure::CreateTokenField();
                                ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button name='log' type="submit" class="btn btn-success btn-lg btn-block" onMouseDown="set_ck();
                                            Set_Cookie('dhtmlgoodies_tab_menu_tabIndex', '', -1)"><?=_login?></button>
                                
                                    </div>
                                </div>
                                <script type="text/javascript">
                                    document.getElementById("forgotPass").onclick = function() {
                                        var link = document.getElementById("forgotPass");
                                        var language = document.getElementById("language");
                                        // console.log(language);
                                        var href = link.href;
                                        link.setAttribute("href", href+"?language="+language.value);
                                        // return false;
                                    }
                                </script>
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
                <?php //echo $log_msg[1]['MESSAGE'];  ?>
                </div>-->
            </div>
            <footer>
                <!-- openSisIsAProductOfOpenSolutionsForEducationInc. (<a href="http://www.os4ed.com">OS4ED</a>) and is licensed under the <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GPL license</a>. -->
                <?= _footerText ?>
            </footer>

        </div>
    </section>


    <script src="assets/js/core/libraries/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/plugins/forms/selects/select2.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            $('.select-search').select2({
                dropdownParent: $('.language-selection')
            });


            var monthNames = ["<?= _january ?>", "<?= _february ?>", "<?= _march ?>", "<?= _april ?>", "<?= _may ?>", "<?= _june ?>", "<?= _july ?>", "<?= _august ?>", "<?= _september ?>", "<?= _october ?>", "<?= _november ?>", "<?= _december ?>"];
            var dayNames = ["<?= _sunday ?>", "<?= _monday ?>", "<?= _tuesday ?>", "<?= _wednesday ?>", "<?= _thursday ?>", "<?= _friday ?>", "<?= _saturday ?>"]


                                            var newDate = new Date();

                                            newDate.setDate(newDate.getDate());

                                            $('#Date').html(dayNames[newDate.getDay()] + ", " + monthNames[newDate.getMonth()] + ' ' + newDate.getDate() + ', ' + newDate.getFullYear());



            setInterval(function() {
                                                // Create a newDate() object and extract the minutes of the current time on the visitor's
                                                var minutes = new Date().getMinutes();
                                                // Add a leading zero to the minutes value
                                                $("#min").html((minutes < 10 ? "0" : "") + minutes);
                                            }, 1000);

            setInterval(function() {
                                                // Create a newDate() object and extract the hours of the current time on the visitor's
                                                var hours = new Date().getHours();
                                                // Add a leading zero to the hours value
                                                $("#hours").html((hours < 10 ? "0" : "") + hours);
                                            }, 1000);
                                        });
    </script>
    <!--custom script-->
    <script src="js/Custom.js"></script>
</body>
