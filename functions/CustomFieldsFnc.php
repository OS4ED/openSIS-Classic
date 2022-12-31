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
/*
	Call in an SQL statement to select students based on custom fields

	Use in the where section of the query by CustomFIelds('where')
*/
function CustomFields($location, $table_arr = '', $exp = 0)
{
	global $_openSIS, $string;
	if (!empty($_REQUEST['month__cust_begin'])) {
		foreach ($_REQUEST['month__cust_begin'] as $field_name => $month) {
			$_REQUEST['cust_begin'][$field_name] = $_REQUEST['day__cust_begin'][$field_name] . '-' . $_REQUEST['month__cust_begin'][$field_name] . '-' . $_REQUEST['year__cust_begin'][$field_name];
			$_REQUEST['cust_end'][$field_name] = $_REQUEST['day__cust_end'][$field_name] . '-' . $_REQUEST['month__cust_end'][$field_name] . '-' . $_REQUEST['year__cust_end'][$field_name];
			if (!VerifyDate($_REQUEST['cust_begin'][$field_name]) || !VerifyDate($_REQUEST['cust_end'][$field_name])) {
				unset($_REQUEST['cust_begin'][$field_name]);
				unset($_REQUEST['cust_end'][$field_name]);
			}
		}
		unset($_REQUEST['month__cust_begin']);
		unset($_REQUEST['year__cust_begin']);
		unset($_REQUEST['day__cust_begin']);
		unset($_REQUEST['month__cust_end']);
		unset($_REQUEST['year__cust_end']);
		unset($_REQUEST['day__cust_end']);
	}
	if (!empty($_REQUEST['cust'])) {
		foreach ($_REQUEST['cust'] as $key => $value) {
			if ($value == '')
				unset($_REQUEST['cust'][$key]);
		}
	}
	switch ($location) {
		case 'from':
			break;

		case 'where':
			if (!empty($_REQUEST['cust']) || !empty($_REQUEST['cust_begin'])) {
				$fields = DBGet(DBQuery('SELECT TITLE,ID,TYPE,SYSTEM_FIELD FROM custom_fields'), array(), array('ID'));
			}
			if (!empty($_REQUEST['cust'])) {
				foreach ($_REQUEST['cust'] as $id => $value) {
					$field_name = $id;
					$id = substr($id, 7);
					if ($fields[$id][1]['SYSTEM_FIELD'] == 'Y')
						$field_name = strtoupper(str_replace(' ', '_', $fields[$id][1]['TITLE']));
					if ($value != '') {
						switch ($fields[$id][1]['TYPE']) {
							case 'radio':
								$_openSIS['SearchTerms'] .= '<font color=gray><b>' . $fields[$id][1]['TITLE'] . ': </b></font>';
								if ($value == 'Y') {
									$string .= ' and s.' . $field_name . '=\'' . $value . '\' ';
									$_openSIS['SearchTerms'] .= 'Yes';
								} elseif ($value == 'N') {
									$string .= ' and (s.' . $field_name . '!=\'Y\' OR s.' . $field_name . ' IS NULL) ';
									$_openSIS['SearchTerms'] .= 'No';
								}
								$_openSIS['SearchTerms'] .= '<BR>';
								break;

							case 'codeds':
								$_openSIS['SearchTerms'] .= '<font color=gray><b>' . $fields[$id][1]['TITLE'] . ': </b></font>';
								if ($value == '!') {
									$string .= ' and (s.' . $field_name . '=\'\' OR s.' . $field_name . ' IS NULL) ';
									$_openSIS['SearchTerms'] .= 'No Value';
								} else {
									$string .= ' and s.' . $field_name . '=\'' . $value . '\' ';
									$_openSIS['SearchTerms'] .= $value;
								}
								$_openSIS['SearchTerms'] .= '<BR>';
								break;

							case 'select':
								$_openSIS['SearchTerms'] .= '<font color=gray><b>' . $fields[$id][1]['TITLE'] . ': </b></font>';
								if ($value == '!') {
									$string .= ' and (s.' . $field_name . '=\'\' OR s.' . $field_name . ' IS NULL) ';
									$_openSIS['SearchTerms'] .= 'No Value';
								} else {
									$string .= ' and s.' . $field_name . '=\'' . $value . '\' ';
									$_openSIS['SearchTerms'] .= $value;
								}
								$_openSIS['SearchTerms'] .= '<BR>';
								break;

							case 'autos':
								$_openSIS['SearchTerms'] .= '<font color=gray><b>' . $fields[$id][1]['TITLE'] . ': </b></font>';
								if ($value == '!') {
									$string .= ' and (s.' . $field_name . '=\'\' OR s.' . $field_name . ' IS NULL) ';
									$_openSIS['SearchTerms'] .= 'No Value';
								} else {
									$string .= ' and s.' . $field_name . '=\'' . $value . '\' ';
									$_openSIS['SearchTerms'] .= $value;
								}
								$_openSIS['SearchTerms'] .= '<BR>';
								break;

							case 'edits':
								$_openSIS['SearchTerms'] .= '<font color=gray><b>' . $fields[$id][1]['TITLE'] . ': </b></font>';
								if ($value == '!') {
									$string .= ' and (s.' . $field_name . '=\'\' OR s.' . $field_name . ' IS NULL) ';
									$_openSIS['SearchTerms'] .= 'No Value';
								} elseif ($value == '~') {
									$string .= " and position('\n'||s.$field_name||'\r' IN '\n'||(SELECT SELECT_OPTIONS FROM custom_fields WHERE ID='" . $id . "')||'\r')=0 ";
									$_openSIS['SearchTerms'] .= 'Other';
								} else {
									$string .= ' and s.' . $field_name . '=\'' . $value . '\' ';
									$_openSIS['SearchTerms'] .= $value;
								}
								$_openSIS['SearchTerms'] .= '<BR>';
								break;

							case 'text':
								if (substr($value, 0, 2) == '\"' && substr($value, -2) == '\"') {
									$string .= ' and s.' . $field_name . '=\'' . substr($value, 2, -2) . '\' ';
									$_openSIS['SearchTerms'] .= '<font color=gray><b>' . $fields[$id][1]['TITLE'] . ': </b></font>' . substr($value, 2, -2) . '<BR>';
								} else {
									$string .= ' and LOWER(s.' . $field_name . ') LIKE \'' . strtolower($value) . '%\' ';
									if ($exp == 1)
										$_openSIS['Search'] .= '<font color=gray><b>' . $fields[$id][1]['TITLE'] . ' starts with: </b></font>' . $value . '<BR>';
									elseif ($exp == 2) {
										$_openSIS['SearchTerms'] .= '<font color=gray><b>' . $fields[$id][1]['TITLE'] . ' starts with: </b></font>' . $value . '<BR>';
									} else {
										$_openSIS['SearchTerms'] .= '<font color=gray><b>' . $fields[$id][1]['TITLE'] . ' starts with: </b></font>' . $value . '<BR>';
									}
								}
								break;
						}
					}
				}
			}
			if (!empty($_REQUEST['cust_begin'])) {
				foreach ($_REQUEST['cust_begin'] as $id => $value) {
					$field_name = $id;
					$id = substr($id, 7);
					$column_name = $field_name;
					if ($fields[$id][1]['SYSTEM_FIELD'] == 'Y')
						$column_name = strtoupper(str_replace(' ', '_', $fields[$id][1]['TITLE']));
					if ($fields[$id][1]['TYPE'] == 'numeric') {
						$_REQUEST['cust_end'][$field_name] = par_rep('/[^0-9.-]+/', '', $_REQUEST['cust_end'][$field_name]);
						$value = par_rep('/[^0-9.-]+/', '', $value);
					}

					if ($_REQUEST['cust_begin'][$field_name] != '' && $_REQUEST['cust_end'][$field_name] != '') {
						if ($fields[$id][1]['TYPE'] == 'numeric' && $_REQUEST['cust_begin'][$field_name] > $_REQUEST['cust_end'][$field_name]) {
							$temp = $_REQUEST['cust_end'][$field_name];
							$_REQUEST['cust_end'][$field_name] = $value;
							$value = $temp;
						}
						$string .= ' and s.' . $column_name . ' BETWEEN \'' . date('Y-m-d', strtotime($value)) . '\' AND \'' . date('Y-m-d', strtotime($_REQUEST['cust_end'][$field_name])) . '\' ';
						if ($fields[$id][1]['TYPE'] == 'date')
							$_openSIS['SearchTerms'] .= '<font color=gray><b>' . $fields[$id][1]['TITLE'] . ' between: </b></font>' . date('M/d/Y', strtotime($value)) . ' &amp; ' . date('M/d/Y', strtotime($_REQUEST['cust_end'][$field_name])) . '<BR>';
						else
							$_openSIS['SearchTerms'] .= '<font color=gray><b>' . $fields[$id][1]['TITLE'] . ' between: </b></font>' . $value . ' &amp; ' . $_REQUEST['cust_end'][$field_name] . '<BR>';
					}
				}
			}

			break;
	}
	return $string;
}
