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

class CSRFSecure {
    public static function CreateToken() {
        // Generating a unique token and it's expiration time
        $token = bin2hex(random_bytes(32));
        $expiresAt = time() + 60; // Expiration time is set to 1 minute

        $_SESSION["_TOKEN"] = $token;
        $_SESSION["_TOKEN_EXPIRY"] = $expiresAt;

        return $token;
    }

    public static function CreateTokenField() {
    	// Generating a unique token and it's expiration time
        $token = bin2hex(random_bytes(32));
        $expiresAt = time() + 60; // Expiration time is set to 1 minute

        $_SESSION["_TOKEN"] = $token;
        $_SESSION["_TOKEN_EXPIRY"] = $expiresAt;
        
    	echo "<input type='hidden' name='TOKEN' value='" . $token . "' />";
    }

    public static function ValidateToken($token) {
	    if (!isset($_SESSION["_TOKEN"]) || !isset($_POST["TOKEN"])) {
	        return false;
	    }
	    else {
	    	if ($_POST["TOKEN"] == $_SESSION["_TOKEN"]) {
	    		if (time() <= $_SESSION["_TOKEN_EXPIRY"]) {
	    			unset($_SESSION["_TOKEN"]);
	    			unset($_SESSION["_TOKEN_EXPIRY"]);

	    			return true;
	    		}
	    		else {
	    			return false;
	    		}
	    	}
	    	else {
	    		return false;
	    	}
	    }
	}
}

?>