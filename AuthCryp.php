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

function dataCreator($char = '', $type = '')
{
	if($char != '')
	{
		$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		$strip_one = substr(str_shuffle($permitted_chars), 0, 1);
		$strip_two = substr(str_shuffle($permitted_chars), 0, 2);

		$encryp_array = array(
			// UPPER CASE LETTERS
			"A"	=>	"v",
			"B"	=>	"l",
			"C"	=>	"x",
			"D"	=>	"m",
			"E"	=>	"w",
			"F"	=>	"q",
			"G"	=>	"p",
			"H"	=>	"a",
			"I"	=>	"r",
			"J"	=>	"y",
			"K"	=>	"b",
			"L"	=>	"u",
			"M"	=>	"z",
			"N"	=>	"c",
			"O"	=>	"k",
			"P"	=>	"e",
			"Q"	=>	"t",
			"R"	=>	"i",
			"S"	=>	"d",
			"T"	=>	"g",
			"U"	=>	"n",
			"V"	=>	"o",
			"W"	=>	"h",
			"X"	=>	"f",
			"Y"	=>	"j",
			"Z"	=>	"s",
			// LOWER CASE LETTERS
			"a"	=>	"M",
			"b"	=>	"K",
			"c"	=>	"T",
			"d"	=>	"Q",
			"e"	=>	"S",
			"f"	=>	"I",
			"g"	=>	"N",
			"h"	=>	"P",
			"i"	=>	"L",
			"j"	=>	"B",
			"k"	=>	"G",
			"l"	=>	"X",
			"m"	=>	"D",
			"n"	=>	"Z",
			"o"	=>	"W",
			"p"	=>	"J",
			"q"	=>	"V",
			"r"	=>	"A",
			"s"	=>	"U",
			"t"	=>	"E",
			"u"	=>	"Y",
			"v"	=>	"C",
			"w"	=>	"H",
			"x"	=>	"F",
			"y"	=>	"R",
			"z"	=>	"O",
			// NUMERICS
			"0"	=>	"6",
			"1"	=>	"4",
			"2"	=>	"7",
			"3"	=>	"8",
			"4"	=>	"2",
			"5"	=>	"9",
			"6"	=>	"3",
			"7"	=>	"0",
			"8"	=>	"1",
			"9"	=>	"5"
		);

		$decryp_array_caps = array(
			// UPPER CASE LETTERS
			"M" => "a",
			"K" => "b",
			"T" => "c",
			"Q" => "d",
			"S" => "e",
			"I" => "f",
			"N" => "g",
			"P" => "h",
			"L" => "i",
			"B" => "j",
			"G" => "k",
			"X" => "l",
			"D" => "m",
			"Z" => "n",
			"W" => "o",
			"J" => "p",
			"V" => "q",
			"A" => "r",
			"U" => "s",
			"E" => "t",
			"Y" => "u",
			"C" => "v",
			"H" => "w",
			"F" => "x",
			"R" => "y",
			"O" => "z"
		);

		$decryp_array_small = array(
			// LOWER CASE LETTERS
			"v" => "A",
			"l" => "B",
			"x" => "C",
			"m" => "D",
			"w" => "E",
			"q" => "F",
			"p" => "G",
			"a" => "H",
			"r" => "I",
			"y" => "J",
			"b" => "K",
			"u" => "L",
			"z" => "M",
			"c" => "N",
			"k" => "O",
			"e" => "P",
			"t" => "Q",
			"i" => "R",
			"d" => "S",
			"g" => "T",
			"n" => "U",
			"o" => "V",
			"h" => "W",
			"f" => "X",
			"j" => "Y",
			"s" => "Z"
		);

		$decryp_array_numbers = array(
			// NUMERICS
			"6" => "0",
			"4" => "1",
			"7" => "2",
			"8" => "3",
			"2" => "4",
			"9" => "5",
			"3" => "6",
			"0" => "7",
			"1" => "8",
			"5" => "9"
		);

		if($type == 'ENC')
		{
			if(in_array($char, $encryp_array))
			{
				return $strip_one.$encryp_array[$char].$strip_two;
			}
			else
			{
				return $strip_one.$char.$strip_two;
			}
		}

		if($type == 'DEC')
		{
			if(in_array($char, $decryp_array_caps))
			{
				// echo '# '.$char.'<br/>';
				return $decryp_array_small[$char];
			}
			elseif(in_array($char, $decryp_array_small))
			{
				// echo '$ '.$char.'<br/>';
				return $decryp_array_caps[$char];
			}
			elseif(in_array($char, $decryp_array_numbers))
			{
				// echo '% '.$char.'<br/>';
				return $decryp_array_numbers[$char];
			}
			else
			{
				return $char;
			}
		}
	}
	else
	{
		return '*';
	}
}

function cryptor($word = '', $type = '', $call = '')
{
	if($word != '')
	{
		if($type == 'ENC')
		{
			$arrayfy = str_split($word);

			$return_word = '';

			foreach($arrayfy as $each_letter)
			{
				$return_word .= dataCreator($each_letter, $type);
			}
		}

		if($type == 'DEC')
		{
			$req_word = trim($word);

			$req_word_len = strlen($req_word);

			if(($req_word_len % 4) == 0)
			{
				$req_segment = ($req_word_len / 4);

				$decode_array = array();

				for($fx = 0; $fx < $req_segment; $fx++)
			    {
			    	array_push($decode_array, substr($word, ($fx * 4), 4));
			    }

			    $return_word = '';

				foreach($decode_array as $one_segment)
				{
					$each_letter = '';

					$arrayfy = str_split($one_segment);

					$each_letter = $arrayfy[1];

					$return_word .= dataCreator($each_letter, $type);
				}
			}
			else
			{
				$return_word = '';
			}
		}

		if($call == '1')
		{
			echo $return_word;
		}
		else
		{
			return $return_word;
		}
	}
	else
	{
		return false;
	}
}

?>
