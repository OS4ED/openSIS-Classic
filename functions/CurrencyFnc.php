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

function Currency($num='', $sign='', $red = false) {

    $original = $num;
    if ($sign == 'before' && $num < 0) {
        $negative = true;
        $num *= -1;
    } elseif ($sign == 'CR' && $num < 0) {
        $cr = true;
        $num *= -1;
    }
    $current_RET = DBGet(DBQuery('SELECT TITLE,VALUE,PROGRAM FROM program_config WHERE PROGRAM=\'Currency\' AND SYEAR =\'' . UserSyear() . '\' AND SCHOOL_ID =\'' . UserSchool() . '\' '));
    $val = $current_RET[1]['VALUE'];

    switch ($val) {
        case '1':
            $sign = '$';
            break;
        case '2':
            $sign = '£';
            break;
        case '3':
            $sign = '€';
            break;
        case'4':
            $sign = 'C$';
            break;
        case '5':
            $sign = '$';
            break;
        case '6':
            $sign = 'R$';
            break;
        case '7':
            $sign = '¥';
            break;
        case '8':
            $sign = 'kr ';
            break;
        case '9':
            $sign = '¥ ';
            break;
        case '10':
            $sign = 'Rs';
            break;
        case '11':
            $sign = 'Rp';
            break;
        case '12':
            $sign = '₩';
            break;
        case '13' :
            $sign = 'RM';
            break;
        case '14':
            $sign = '$';
            break;
        case '15':
            $sign = '$';
            break;
        case '16':
            $sign = 'Kr';
            break;
        case '17':
            $sign = 'Rs';
            break;
        case '18':
            $sign = 'Php';
            break;
        case '19':
            $sign = 'Rs';
            break;
        case '20':
            $sign = 'SR';
            break;
        case '21':
            $sign = 'R';
            break;
        case '22':
            $sign = 'SR';
            break;
        case '23':
            $sign = 'S₣';
            break;
        case '24':
            $sign = '฿';
            break;
        case '25':
            $sign = '฿';
            break;
        case '26':
            $sign = '฿';
            break;
    }


    if ($sign == '') {
        $sign = '$';
    }
    $num = $sign . number_format($num, 2, '.', ',');
    if ($negative) {
        $num = '-' . $num;
    } elseif ($cr) {
        $num = $num . 'CR';
    }
    if ($red && $original < 0) {
        $num = '<span class="text-danger">' . $num . '</span>';
    }
    /*if (strpos($num, '-') == true) {
        $num = str_replace('-', '', $num);
        $num = '-' . $num;
    }*/
    return $num;
}

?>