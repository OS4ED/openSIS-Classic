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
include('../../RedirectModulesInc.php');
DrawBC("" . _students . " > " . ProgramTitle());
$_openSIS['allow_edit'] = true;

if ($_REQUEST['tables'] && ($_POST['tables'] || $_REQUEST['ajax'])) {
	$table = $_REQUEST['table'];
	foreach ($_REQUEST['tables'] as $id => $columns) {
		if ($id != 'new') {
			if ($columns['CATEGORY_ID'] && $columns['CATEGORY_ID'] != $_REQUEST['category_id'])
				$_REQUEST['category_id'] = $columns['CATEGORY_ID'];

			$sql = 'UPDATE ' . $table . ' SET ';

			foreach ($columns as $column => $value)
				$sql .= $column . '=\'' . str_replace("\'", "''", $value) . '\',';
			$sql = substr($sql, 0, -1) . ' WHERE ID=\'' . $id . '\'';
			$go = true;
		} else {
			$sql = 'INSERT INTO ' . $table . ' ';

			if ($table == 'people_fields') {
				if ($columns['CATEGORY_ID']) {
					$_REQUEST['category_id'] = $columns['CATEGORY_ID'];
					unset($columns['CATEGORY_ID']);
				}

				// $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'people_fields'"));
				// $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
				// $id = $id[1]['ID'];
				$fields = 'CATEGORY_ID,';
				$values = '\'' . $_REQUEST['category_id'] . '\',';
				// $_REQUEST['id'] = $id;

				// switch ($columns['TYPE']) {
				// 	case 'radio':
				// 		DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(1)');
				// 		break;

				// 	case 'text':
				// 	case 'select':
				// 	case 'autos':
				// 	case 'edits':
				// 		DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(255)');
				// 		break;

				// 	case 'codeds':
				// 		DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(15)');
				// 		break;

				// 	case 'multiple':
				// 		DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(1000)');
				// 		break;

				// 	case 'numeric':
				// 		DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' NUMERIC(10,2)');
				// 		break;

				// 	case 'date':
				// 		DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' DATE');
				// 		break;

				// 	case 'textarea':
				// 		DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(5000)');
				// 		break;
				// }
				// DBQuery('CREATE INDEX PEOPLE_IND' . $id . ' ON people (CUSTOM_' . $id . ')');
			} elseif ($table == 'people_field_categories') {

				// $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'people_field_categories\' '));
				// $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
				// $id = $id[1]['ID'];
				$fields = "";
				$values = "";
				// $_REQUEST['category_id'] = $id;
			}

			$go = false;

			foreach ($columns as $column => $value) {
				if ($value) {
					$fields .= $column . ',';
					$values .= '\'' . str_replace("\'", "''", $value) . '\',';
					$go = true;
				}
			}
			$sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';
		}

		if ($go) {
			DBQuery($sql);
			if ($id == 'new') {
				if ($table == 'people_fields'){
					$id = mysqli_insert_id($connection);
					switch ($columns['TYPE']) {
						case 'radio':
							DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(1)');
							break;
	
						case 'text':
						case 'select':
						case 'autos':
						case 'edits':
							DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(255)');
							break;
	
						case 'codeds':
							DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(15)');
							break;
	
						case 'multiple':
							DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(1000)');
							break;
	
						case 'numeric':
							DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' NUMERIC(10,2)');
							break;
	
						case 'date':
							DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' DATE');
							break;
	
						case 'textarea':
							DBQuery('ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(5000)');
							break;
					}
					DBQuery('CREATE INDEX PEOPLE_IND' . $id . ' ON people (CUSTOM_' . $id . ')');
				} else if($table == 'people_field_categories'){
					$_REQUEST['category_id'] = mysqli_insert_id($connection);
				}
			}
		}
	}
	unset($_REQUEST['tables']);
}

if ($_REQUEST['modfunc'] == 'delete') {
	if ($_REQUEST['id']) {
		if (DeletePrompt(_contactField)) {
			$id = $_REQUEST['id'];
			DBQuery('DELETE FROM people_fields WHERE ID=\'' . $id . '\'');
			DBQuery('ALTER TABLE people DROP COLUMN CUSTOM_\'' . $id . '\'');
			$_REQUEST['modfunc'] = '';
			unset($_REQUEST['id']);
		}
	} elseif ($_REQUEST['category_id']) {
		if (DeletePrompt(_contactFieldCategoryAndAllFieldsInTheCategory)) {
			$fields = DBGet(DBQuery('SELECT ID FROM people_fields WHERE CATEGORY_ID=\'' . $_REQUEST[category_id] . '\''));
			foreach ($fields as $field) {
				DBQuery('DELETE FROM people_fields WHERE ID=\'' . $field[ID] . '\'');
				DBQuery('ALTER TABLE people DROP COLUMN CUSTOM_\'' . $field[ID] . '\'');
			}
			DBQuery('DELETE FROM people_field_categories WHERE ID=\'' . $_REQUEST[category_id] . '\"');
			$_REQUEST['modfunc'] = '';
			unset($_REQUEST['category_id']);
		}
	}
}

if (!$_REQUEST['modfunc']) {
	// CATEGORIES
	$sql = 'SELECT ID,TITLE,SORT_ORDER FROM people_field_categories ORDER BY SORT_ORDER,TITLE';
	$QI = DBQuery($sql);
	$categories_RET = DBGet($QI);

	if (AllowEdit() && $_REQUEST['id'] != 'new' && $_REQUEST['category_id'] != 'new' && ($_REQUEST['id'] || $_REQUEST['category_id']))
		$delete_button = "<INPUT type=button value='._delete.' class='btn btn-primary' onClick='javascript:window.location=\"Modules.php?modname=$_REQUEST[modname]&modfunc=delete&category_id=$_REQUEST[category_id]&id=$_REQUEST[id]\"'>" . "&nbsp;";

	// ADDING & EDITING FORM
	if ($_REQUEST['id'] && $_REQUEST['id'] != 'new') {
		$sql = 'SELECT CATEGORY_ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,SORT_ORDER,REQUIRED,(SELECT TITLE FROM people_field_categories WHERE ID=CATEGORY_ID) AS CATEGORY_TITLE FROM people_fields WHERE ID=\'' . $_REQUEST[id] . '\'';
		$RET = DBGet(DBQuery($sql));
		$RET = $RET[1];
		$title = $RET['CATEGORY_TITLE'] . ' - ' . $RET['TITLE'];
	} elseif ($_REQUEST['category_id'] && $_REQUEST['category_id'] != 'new' && $_REQUEST['id'] != 'new') {
		$sql = 'SELECT TITLE,CUSTODY,EMERGENCY,SORT_ORDER
				FROM people_field_categories
				WHERE ID=\'' . $_REQUEST[category_id] . '\'';
		$RET = DBGet(DBQuery($sql));
		$RET = $RET[1];
		$title = $RET['TITLE'];
	} elseif ($_REQUEST['id'] == 'new')
		$title = _newContactField;
	elseif ($_REQUEST['category_id'] == 'new')
		$title = _newContactFieldCategory;

	if ($_REQUEST['id']) {
		echo "<FORM name=F1 id=F1 action=Modules.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]";
		if ($_REQUEST['id'] != 'new')
			echo "&id=$_REQUEST[id]";
		echo "&table=people_fields method=POST>";

		DrawHeaderHome($title, $delete_button . SubmitButton(_save, '', 'class="btn btn-primary" onclick="formcheck_student_contactField_F1();"')); //'<INPUT type=submit value='._save.'>');
		$header .= '<TABLE cellpadding=3 width=100%>';
		$header .= '<TR>';

		$header .= '<TD>' . TextInput($RET['TITLE'], 'tables[' . $_REQUEST['id'] . '][TITLE]', _fieldName) . '</TD>';

		// You can't change an people field type after it has been created
		// mab - allow changing between select and autos and edits and text
		if ($_REQUEST['id'] != 'new') {
			if ($RET['TYPE'] != 'select' && $RET['TYPE'] != 'autos' && $RET['TYPE'] != 'edits' && $RET['TYPE'] != 'text') {
				$allow_edit = $_openSIS['allow_edit'];
				$AllowEdit = $_openSIS['AllowEdit'][$modname];
				$_openSIS['allow_edit'] = false;
				$_openSIS['AllowEdit'][$modname] = array();
				$type_options = array(
					'select' => _pullDown,
					'autos' => _autoPullDown,
					'edits' => _editPullDown,
					'text' => _text,
					'radio' => _checkbox,
					'codeds' => _codedPullDown,
					'numeric' => _number,
					'multiple' => _selectMultipleFromOptions,
					'date' => _date,
					'textarea' => _longText,
				);
			} else
				$type_options = array(
					'select' => _pullDown,
					'autos' => _autoPullDown,
					'edits' => _editPullDown,
					'text' => _text,
				);
		} else
			$type_options = array(
				'select' => _pullDown,
				'autos' => _autoPullDown,
				'edits' => _editPullDown,
				'text' => _text,
				'radio' => _checkbox,
				'codeds' => _codedPullDown,
				'numeric' => _number,
				'multiple' => _selectMultipleFromOptions,
				'date' => _date,
				'textarea' => _longText,
			);

		$header .= '<TD>' . SelectInput($RET['TYPE'], 'tables[' . $_REQUEST['id'] . '][TYPE]', _dataType, $type_options, false) . '</TD>';
		if ($_REQUEST['id'] != 'new' && $RET['TYPE'] != 'select' && $RET['TYPE'] != 'autos' && $RET['TYPE'] != 'edits' && $RET['TYPE'] != 'text') {
			$_openSIS['allow_edit'] = $allow_edit;
			$_openSIS['AllowEdit'][$modname] = $AllowEdit;
		}
		foreach ($categories_RET as $type)
			$categories_options[$type['ID']] = $type['TITLE'];

		$header .= '<TD>' . SelectInput($RET['CATEGORY_ID'] ? $RET['CATEGORY_ID'] : $_REQUEST['category_id'], 'tables[' . $_REQUEST['id'] . '][CATEGORY_ID]', _contactFieldCategory, $categories_options, false) . '</TD>';

		$header .= '<TD>' . TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['id'] . '][SORT_ORDER]', _sortOrder) . '</TD>';

		$header .= '</TR><TR>';
		$colspan = 2;
		if ($RET['TYPE'] == 'autos' || $RET['TYPE'] == 'edits' || $RET['TYPE'] == 'select' || $RET['TYPE'] == 'codeds' || $RET['TYPE'] == 'multiple' || $_REQUEST['id'] == 'new') {
			$header .= '<TD colspan=2>' . TextAreaInput($RET['SELECT_OPTIONS'], 'tables[' . $_REQUEST['id'] . '][SELECT_OPTIONS]', 'Pull-Down/Auto Pull-Down/Coded Pull-Down/Select Multiple Choices<BR>* ' . _onePerLine . '', 'rows=7 cols=40') . '</TD>';
			$colspan = 1;
		}
		$header .= '<TD valign=bottom colspan=' . $colspan . '>' . TextInput($RET['DEFAULT_SELECTION'], 'tables[' . $_REQUEST['id'] . '][DEFAULT_SELECTION]', 'Default') . '<small><BR>* ' . _forDates . ': YYYY-MM-DD,<BR> ' . _forCheckboxes . ': Y</small></TD>';

		if ($_REQUEST['id'] == 'new')
			$new = true;
		$header .= '<TD>' . CheckboxInput($RET['REQUIRED'], 'tables[' . $_REQUEST['id'] . '][REQUIRED]', 'Required', '', $new) . '</TD>';

		$header .= '</TR>';
		$header .= '</TABLE>';
	} elseif ($_REQUEST['category_id']) {
		echo "<FORM name=F2 id=F2 action=Modules.php?modname=$_REQUEST[modname]&table=people_field_categories";
		if ($_REQUEST['category_id'] != 'new')
			echo "&category_id=$_REQUEST[category_id]";
		echo " method=POST>";
		DrawHeaderHome($title, $delete_button . SubmitButton(_save, '', 'class="btn btn-primary" onclick="formcheck_student_contactField_F2();"')); //'<INPUT type=submit value='._save.'>');
		$header .= '<TABLE cellpadding=3 width=100%>';
		$header .= '<TR>';

		$header .= '<TD>' . TextInput($RET['TITLE'], 'tables[' . $_REQUEST['category_id'] . '][TITLE]', 'Title') . '</TD>';

		$header .= '<TD>' . TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['category_id'] . '][SORT_ORDER]', _sortOrder) . '</TD>';

		if ($_REQUEST['category_id'] == 'new')
			$new = true;
		$header .= '<TD><TABLE><TR>';
		$header .= '<TD>' . CheckboxInput($RET['CUSTODY'], 'tables[' . $_REQUEST['category_id'] . '][CUSTODY]', 'Custody', '', $new, '<IMG SRC=assets/check.gif height=15 vspace=0 hspace=0 border=0>', '<IMG SRC=assets/x.gif height=15 vspace=0 hspace=0 border=0>') . '</TD>';
		$header .= '<TD>' . CheckboxInput($RET['EMERGENCY'], 'tables[' . $_REQUEST['category_id'] . '][EMERGENCY]', 'Emergency', '', $new, '<IMG SRC=assets/check.gif height=15 vspace=0 hspace=0 border=0>', '<IMG SRC=assets/x.gif height=15 vspace=0 hspace=0 border=0>') . '</TD>';
		$header .= '</TR><TR>';
		$header .= '<TD colspan=3><small><FONT color=' . Preferences('TITLES') . '>Note: All unchecked means applies to all contacts</FONT></small></TD>';
		$header .= '</TR></TABLE></TD>';

		$header .= '</TR>';
		$header .= '</TABLE>';
	} else
		$header = false;

	if ($header) {
		DrawHeader($header);
		echo '</FORM>';
		echo '<div class=break_headers></div>';
	}

	// DISPLAY THE MENU
	$LO_options = array('save' => false, 'search' => false, 'add' => true);

	echo '<TABLE><TR>';

	if (count($categories_RET)) {
		if ($_REQUEST['category_id']) {
			foreach ($categories_RET as $key => $value) {
				if ($value['ID'] == $_REQUEST['category_id'])
					$categories_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
			}
		}
	}

	echo '<TD valign=top>';
	$columns = array(
		'TITLE' => _category,
		'SORT_ORDER' => _order,
	);
	$link = array();
	$link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";

	$link['TITLE']['variables'] = array('category_id' => 'ID');

	$link['add']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&category_id=new\");'";

	ListOutput($categories_RET, $columns, _contactFieldCategory, _contactFieldCategories, $link, array(), $LO_options);
	echo '</TD>';

	// FIELDS
	if ($_REQUEST['category_id'] && $_REQUEST['category_id'] != 'new' && count($categories_RET)) {
		$sql = 'SELECT ID,TITLE,TYPE,SORT_ORDER FROM people_fields WHERE CATEGORY_ID=\'' . $_REQUEST['category_id'] . '\' ORDER BY SORT_ORDER,TITLE';
		$fields_RET = DBGet(DBQuery($sql), array('TYPE' => '_makeType'));

		if (count($fields_RET)) {
			if ($_REQUEST['id'] && $_REQUEST['id'] != 'new') {
				foreach ($fields_RET as $key => $value) {
					if ($value['ID'] == $_REQUEST['id'])
						$fields_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
				}
			}
		}

		echo '<TD valign=top>';
		$columns = array(
			'TITLE' => _contactField,
			'SORT_ORDER' => _order,
			'TYPE' => _dataType,
		);
		$link = array();
		$link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]";

		$link['TITLE']['variables'] = array('id' => 'ID');

		$link['add']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]&id=new\");'";

		ListOutput($fields_RET, $columns, _contactField, _contactFields, $link, array(), $LO_options);

		echo '</TD>';
	}

	echo '</TR></TABLE>';
}

function _makeType($value, $name)
{
	$options = array(
		'radio' => _checkbox,
		'text' => _text,
		'autos' => _autoPullDown,
		'edits' => _editPullDown,
		'select' => _pullDown,
		'codeds' => _codedPullDown,
		'date' => _date,
		'numeric' => _number,
		'textarea' => _longText,
		'multiple' => _selectMultiple,
	);
	return $options[$value];
}
