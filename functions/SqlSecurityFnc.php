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

function sqlSecurityFilter($variableName = '')
{
	global $connection;
	// $connection = mysqli_connect("localhost", "root", "", "opensis");
	$variable = $variableName;
	$check_stream = array('union ', 'select ', 'concat',  'concat_ws', 'create ', 'update ', 'insert ', 'delete ', 'extract ', 'drop ', 'truncate ', 'where ', 'trim ', 'format ', 'union%20', 'select%20', 'create%20', 'update%20', 'insert%20', 'delete%20', 'extract%20', 'drop%20', 'truncate%20', 'where%20', 'trim%20', 'format%20', ';', '\'', '--', '../', '..%2f', 'skip-grant-tables');

	if ($variable != '') {
		$checker = 0;
		$checker_k = 0;
		$checker_v = 0;
		if (is_array($variable)) {

			$filter_data = array();
			$neat_key = '';
			$neat_val = '';

			foreach ($variable as $onekey => $oneval) {

				$k_check_1      =   strip_tags($onekey);
				$k_check_2      =   addslashes($k_check_1);
				$k_check_3      =   mysqli_real_escape_string($connection, $k_check_2);
				$k_check_4	    =	strtolower($k_check_3);

				$v_check_1      =   strip_tags($oneval);
				$v_check_2      =   addslashes($v_check_1);
				$v_check_3      =   mysqli_real_escape_string($connection, $v_check_2);
				$v_check_4	    =	strtolower($v_check_3);

				foreach ($check_stream as $one_check) {
					if (strpos($k_check_4, $one_check) !== false)
					{
						$checker_k++;
					}
					
					if(strpos($v_check_4, $one_check) !== false)
					{
						$checker_v++;
					}
				}

				if(is_array($oneval))
				{
					$get_child_ret = sqlSecurityFilter($oneval); // being recursive

					$filter_data[$k_check_3] = $get_child_ret;
				}
				else
				{
					if($checker_k != 0 || $checker_v != 0)
					{
						unset($variable[$onekey]);
					}
					else
					{
						unset($variable[$onekey]);

						// if(is_array($oneval))
						// {
						// 	$get_child_ret = sqlSecurityFilter($oneval); // being recursive

						// 	$filter_data[$k_check_3] = $get_child_ret;
						// }
						// else
						// {
							$filter_data[$k_check_3] = $v_check_3;
						// }
					}
				}

				// $filter_data[] = $variable;
			}

			return $filter_data;

			unset($checker);
			unset($checker_k);
			unset($checker_v);
		} else {
			$check_1    =   strip_tags($variable);
			$check_2    =   addslashes($check_1);
			$check_3    =   mysqli_real_escape_string($connection, $check_2);
			$check_4    =   strtolower($check_3);

			foreach ($check_stream as $one_check) {
				if (strpos($check_4, $one_check) !== false) {
					$checker++;
				}
			}

			if ($checker == 0) {
				return $check_3;
			} else {
				return '';
			}
		}
	} else {
		return $variableName;
	}
}

function sqlSecurityFilterMod($variableName = '')
{
	global $connection;
	// $connection = mysqli_connect("localhost", "root", "", "opensis");
	$variable = $variableName;
	$check_stream = array('union ', 'select ', 'concat',  'concat_ws', 'create ', 'update ', 'insert ', 'delete ', 'extract ', 'drop ', 'truncate ', 'where ', 'trim ', 'format ', 'union%20', 'select%20', 'create%20', 'update%20', 'insert%20', 'delete%20', 'extract%20', 'drop%20', 'truncate%20', 'where%20', 'trim%20', 'format%20', ';', '\'', '--', '../', '..%2f', 'skip-grant-tables');

	if(!empty($variableName))
	{
		$filter_data = array();
		$neat_key = '';
		$neat_val = '';

		foreach ($variable as $onekey => $oneval) {

			$k_check_1      =   strip_tags($onekey);
			$k_check_2      =   addslashes($k_check_1);
			$k_check_3      =   mysqli_real_escape_string($connection, $k_check_2);
			$k_check_4	    =	strtolower($k_check_3);

			$v_check_1      =   strip_tags($oneval);
			$v_check_2      =   addslashes($v_check_1);
			$v_check_3      =   mysqli_real_escape_string($connection, $v_check_2);
			$v_check_4	    =	strtolower($v_check_3);

			foreach ($check_stream as $one_check) {
				if (strpos($k_check_4, $one_check) !== false)
				{
					$checker_k++;
				}
				
				if(strpos($v_check_4, $one_check) !== false)
				{
					$checker_v++;
				}
			}

			if($checker_k != 0 || $checker_v != 0)
			{
				unset($variable[$onekey]);
			}
			else
			{
				unset($variable[$onekey]);
				
				$filter_data[$k_check_3] = $v_check_3;
			}

			// $filter_data[] = $variable;
		}

		return $filter_data;

		unset($checker);
		unset($checker_k);
		unset($checker_v);
	}
	else
	{
		return array();
	}
}

function sqlSecurityFilterChk($variableName = '')
{
    // global $connection;
    // $connection = mysqli_connect("localhost", "root", "", "opensis");
    $variable = $variableName;
    $check_stream = array('union ', 'select ', 'concat',  'concat_ws', 'create ', 'update ', 'insert ', 'delete ', 'extract ', 'drop ', 'truncate ', 'where ', 'trim ', 'format ', 'union%20', 'select%20', 'create%20', 'update%20', 'insert%20', 'delete%20', 'extract%20', 'drop%20', 'truncate%20', 'where%20', 'trim%20', 'format%20', ';', '\'', '--', '../', '..%2f', 'skip-grant-tables');

    if ($variable != '') {
        $checker = 0;
        $checker_k = 0;
        $checker_v = 0;
        if (is_array($variable)) {

            $filter_data = array();
            $neat_key = '';
            $neat_val = '';

            foreach ($variable as $onekey => $oneval) {

                $k_check_1      =   strip_tags($onekey);
                $k_check_2      =   addslashes($k_check_1);
                // $k_check_3      =   mysqli_real_escape_string($connection, $k_check_2);
                $k_check_4      =   strtolower($k_check_2);

                $v_check_1      =   strip_tags($oneval);
                $v_check_2      =   addslashes($v_check_1);
                // $v_check_3      =   mysqli_real_escape_string($connection, $v_check_2);
                $v_check_4      =   strtolower($v_check_2);

                foreach ($check_stream as $one_check) {
                    if (strpos($k_check_4, $one_check) !== false)
                    {
                        $checker_k++;
                    }
                    
                    if(strpos($v_check_4, $one_check) !== false)
                    {
                        $checker_v++;
                    }
                }

                if($checker_k != 0 || $checker_v != 0)
                {
                    unset($variable[$onekey]);
                }
                else
                {
                    unset($variable[$onekey]);

                    if(is_array($oneval))
                    {
                        $get_child_ret = sqlSecurityFilterChk($oneval); // being recursive

                        $filter_data[$k_check_2] = $get_child_ret;
                    }
                    else
                    {
                        $filter_data[$k_check_2] = $v_check_2;
                    }
                }

                // $filter_data[] = $variable;
            }

            return $filter_data;

            unset($checker);
            unset($checker_k);
            unset($checker_v);
        } else {
            $check_1    =   strip_tags($variable);
            $check_2    =   addslashes($check_1);
            // $check_3    =   mysqli_real_escape_string($connection, $check_2);
            $check_4    =   strtolower($check_2);

            foreach ($check_stream as $one_check) {
                if (strpos($check_4, $one_check) !== false) {
                    $checker++;
                }
            }

            if ($checker == 0) {
                return $check_2;
            } else {
                return '';
            }
        }
    } else {
        return $variableName;
    }
}

?>