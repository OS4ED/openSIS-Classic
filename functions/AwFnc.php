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

function listuser_grid($name, $column_name, $data, $filename, $sid){

	$row_count = count($data);
	$column_count =count($column_name);


	$columns = "var ".$name."_columns = [\n";

foreach ($column_name as $value) {

 $columns .= "\"".$value."\", ";

}

	$columns .= "\n];\n";

	$rows = "var ".$name."_data = [\n";
	

	for ($k=0; $k<$row_count; $k++) {
	for ($j=0; $j<$column_count; $j++) {
	if ($j==0){
	$data[$k][$j] = "<a style='cursor:hand font-size: 10px; text-decoration: none; color: #800000' href='Modules.php?modname=".$filename."&".$sid."=".($data[$k][$j+2])."'><b>".($data[$k][$j])."</b></a>";
	}
	}
	}
	
	
	
	
	
	for ($k=0; $k<$row_count; $k++) {
		$rows .= "[";
	for ($j=0; $j<$column_count; $j++) {
	 if ($j==0){
	$rows .= "\"".($data[$k][$j])."\", ";
	}
	else{
	$rows .= "\"".activewidgets_html($data[$k][$j])."\", ";
	}
	}
	$rows .= "],\n";
	}
	
	
	$rows .= "];\n";
	
	
	
		$html= "<style>
		.active-controls-grid {height: 100%; font: menu;}
		//.active-grid-row{background: #ffffff;}
		
		//.active-column-0 {width: 400px; background-color: #E3F1F9;}
		.active-column-0 {width: 400px; }
		.active-column-3 {width: 188px;}
		
		.active-grid-column {border-right: 1px solid threedlightshadow;}
		.active-grid-row {border-bottom: 1px solid threedlightshadow;}
		

		//row heighlighting
		.active-row-highlight {background-color: #f6f6ef}
    	.active-row-highlight .active-row-cell {background-color: #f6f6ef}

		
		//column header background
		.active-scroll-left .active-box-item {
        background: green;
    	}

    	/* column header background */
    	.active-scroll-top .active-box-item {
        background-image:url('images/column_header.jpg');
        font-weight: bold;

            }

		.active-scroll-left, .active-scroll-corner {display: none} 
    	.active-scroll-top, .active-scroll-data {padding-left: 0px}
    	
    	
    	.active-scroll-data .active-column-3 {
        border-right: 0px double #cccccc;
	    }
		
		.active-scroll-top .active-column-3 {
	    border-right: 0px double #cccccc;
		}




		




	</style>
";
	

	$html .= "<"."script".">\n";
	$html .= $columns;
	$html .= $rows;
	$html .= "try {\n";
	$html .= "  var $name = new Active.Controls.Grid;\n";
	//For Paging
	$html .= "  \n ".$name.".setModel(\"row\", new Active.Rows.Page); \n";
	
	$html .= "  $name.setProperty(\"row/count\",$row_count);\n";
	$html .= "  $name.setProperty(\"column/count\",$column_count);\n";
	$html .= "  $name.setProperty(\"data/text\",function(i, j){return ".$name."_data[i][j]});\n";
	
	$html .= "  $name.setColumnText(function(i){return ".$name."_columns[i]});\n";
	
	
	//For Alternate row
	$html .= " 	\n var alternate = function(){";
	$html .= "  \n return this.getRowProperty('order') % 2 ? '#efefef' : '#ffffff';";
	$html .= "  \n }";
	$html .= "  \n var row = new Active.Templates.Row;";
	$html .= "  \n row.setStyle('background', alternate);";
	$html .= "  \n obj.setTemplate('row', row);";
	
	//For Row hilighting
	$html .= "  \n row.setEvent('onmouseover', \"mouseover(this, 'active-row-highlight')\");";
	$html .= "  \n row.setEvent('onmouseout', \"mouseout(this, 'active-row-highlight')\");";
	$html .= "  \n obj.setTemplate('row', row);";
	
	//For Column-Header Height
	$html .= "  \n obj.setColumnHeaderHeight(24);";
	
	
	//For Paging
	$html .= "  \n obj.setProperty(\"row/pageSize\", 50);";
	
	
	
	$html .= "  document.write($name);\n";
	$html .= "}\n";
	$html .= "catch (error){\n";
	$html .= "  document.write(error.description);\n";
	$html .= "}\n";
	$html .= "</"."script".">\n";
	
	
	
	
	
	$html .="	<script>
	function goToPage(delta){
		var count = obj.getProperty(\"row/pageCount\");
		var number = obj.getProperty(\"row/pageNumber\");
		number += _delta;
		if (number < 0) {number = 0}
		if (number > count-1) {number = count-1}
		document.getElementById('pageLabel').innerHTML = \"Page \" + (number + 1) + \" of \" + count + \" (Total No of Records: ".$row_count.") \";

		obj.setProperty(\"row/pageNumber\", number);
		obj.refresh();
	}

	goToPage(0);
	</script>";
	
	
	
	return $html;
}

function activewidgets_html($msg){

	$msg = addslashes($msg);
	$msg = str_replace("\n", "\\n", $msg);
	$msg = str_replace("\r", "\\r", $msg);
	$msg = htmlspecialchars($msg);

	return $msg;
}

function listuser($sql, $filename){
	$columns=array(_staff, _profile, staffId, lastLogin);
	echo "<div style='position: relative; width: 788px; height: 300px; z-index: 1; border: 1px solid #ADBCC9' id='divUsers'>".listuser_grid("obj", $columns, getUserData($sql), $filename, "staff_id")."</div>";
}

function listStudents($sql, $filename){
	$columns=array(
	 _student,
	 _grade,
	 _studentId,
	 _school,
	);
	echo "<div style='position: relative; width: 788px; height: 300px; z-index: 1; border: 1px solid #ADBCC9' id='divStudents'>".listuser_grid("obj", $columns, getUserData($sql), $filename, "student_id")."</div>";
}

?>