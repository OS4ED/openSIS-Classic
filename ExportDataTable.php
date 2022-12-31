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

 //include('RedirectRootInc.php');
// include'ConfigInc.php';
include 'Warehouse.php';
// include('../../Redirect_modules.php');

if(isset($_SESSION['mainSQL'])) {
	$sql = $_SESSION['mainSQL']['SQL'];
    $functions = $_SESSION['mainSQL']['FUNCTIONS'];
    $extra['group'] = $_SESSION['mainSQL']['EXTRA_GROUP'];

	$result = DBGet(DBQuery($sql), $functions, $extra['group']);
    // echo '<pre>';print_r($_SESSION['PEGI_COLS']);echo '</pre>';exit();

	$output = '';

	if($_SESSION['PROGRAM_TITLE'] != '') {
		$program_title = $_SESSION['PROGRAM_TITLE'];
	} else {
		$program_title = 'openSIS';
	}

	$_REQUEST['LO_save'] = '1';
	$options = $_SESSION['PEGI_OPTION'];
	$column_names = $_SESSION['PEGI_COLS'];

	// HANDLE SAVING THE LIST ---

    if ($_REQUEST['LO_save'] == '1') {
        if (!$options['save_delimiter'] && Preferences('DELIMITER') == 'CSV')
            $options['save_delimiter'] = 'comma';
        switch ($options['save_delimiter']) {
            case 'comma':
                $extension = 'csv';
                break;
            case 'xml':
                $extension = 'xml';
                break;
            default:
                $extension = 'xls';
                break;
        }
        ob_end_clean();

        if ($options['save_delimiter'] != 'xml') {
            $output .= '<table border=\'1\'><tr>';
            foreach ($column_names as $key => $value)
                if ($key != 'CHECKBOX')
                    $output .= '<td>' . str_replace('&nbsp;', ' ', par_rep_cb('/<BR>/', ' ', par_rep_cb('/<!--.*-->/', '', $value))) . '</td>';
            $output .= '</tr>';
            foreach ($result as $item) {
                $output .= '<tr>';
                foreach ($column_names as $key => $value) {
                    if ($key != 'CHECKBOX') {
                        if ($key == 'ATTENDANCE' || $key == 'IGNORE_SCHEDULING')
                            $item[$key] = ($item[$key] == '<IMG SRC=assets/check.gif height=15>' ? 'Yes' : 'No');
                        $output .= '<td>' . par_rep_cb('/<[^>]+>/', '', par_rep_cb("/<div onclick='[^']+'>/", '', par_rep_cb('/ +/', ' ', par_rep_cb('/&[^;]+;/', '', str_replace('<BR>&middot;', ' : ', str_replace('&nbsp;', ' ', $item[$key])))))) . '</td>';
                    }
                }
                $output .= '</tr>';
            }
            $output .= '</table>';
        }

        if ($options['save_delimiter'] == 'xml') {
            foreach ($result as $item) {
                foreach ($column_names as $key => $value) {
                    if ($options['save_delimiter'] == 'comma' && !$options['save_quotes'])
                        $item[$key] = str_replace(',', ';', $item[$key]);
                    $item[$key] = par_rep_cb('/<SELECT.*SELECTED\>([^<]+)<.*</SELECT\>/', '\\1', $item[$key]);
                    $item[$key] = par_rep_cb('/<SELECT.*</SELECT\>/', '', $item[$key]);
                    $output .= ($options['save_quotes'] ? '"' : '') . ($options['save_delimiter'] == 'xml' ? '<' . str_replace(' ', '', $value) . '>' : '') . par_rep_cb('/<[^>]+>/', '', par_rep_cb("/<div onclick='[^']+'>/", '', par_rep_cb('/ +/', ' ', par_rep_cb('/&[^;]+;/', '', str_replace('<BR>&middot;', ' : ', str_replace('&nbsp;', ' ', $item[$key])))))) . ($options['save_delimiter'] == 'xml' ? '</' . str_replace(' ', '', $value) . '>' . "\n" : '') . ($options['save_quotes'] ? '"' : '') . ($options['save_delimiter'] == 'comma' ? ',' : "\t");
                }
                $output .= "\n";
            }
        }
        header("Cache-Control: public");
        header("Pragma: ");
        header("Content-Type: application/$extension");
        header("Content-Disposition: inline; filename=\"" . $program_title . ".$extension\"\n");
        if ($options['save_eval'])
            eval($options['save_eval']);
        echo $output;
        exit();
    }

    // END SAVING THE LIST ---
}

?>