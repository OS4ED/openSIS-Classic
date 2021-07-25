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
function encode_url($url)
{   //echo $url;
    $encoded_url = '';
    $encoded_parameter = '';
    
    if ((0 === strpos(strtolower($url),  'modules.php')) || (0 === strpos(strtolower($url),  'forexport.php')))
    {
       if (0 === strpos(strtolower($url),  'modules.php'))
        $encoded_url = 'Modules.php?';
       elseif (0 === strpos(strtolower($url),  'forexport.php'))
         $encoded_url = 'ForExport.php?';  
       
    /*first encription*/    
    $str_special = '.,=,/,&,_,;,?,[,],-,+';
    $arr_special = explode(',', $str_special);    
    $arr_main = array_merge($arr_special, range('A', 'Z'), range('a', 'z'), range('0', '9'));
    //echo '<pre>';    print_r($arr_main); echo '</pre>';
    
    $str_encode = 'N,E,+,j,7,a,W,T,$,I,e,l,3,;,Y,i,0,B,K,@,n,t,6,x,J,P,-,C,8,D,h,Z,%,d,A,o,r,m,],F,Q,5,G,V,_,q,v,1,z,k,L,O,[,M,g,w,R,X,H,#,4,u,p,2,b,f,9,c,s,y,!,U,S';
    $arr_encode = explode(',', $str_encode);
    
    $url = ltrim($url,$encoded_url) ;
    $url = trim($url);
    $url = str_replace(' ', '', $url);
    $arr_url = str_split($url);

    foreach ($arr_url as $key => $value) 
    {
       $value = trim($value);
        if(in_array($value, $arr_main))
        {
            $pos =  array_search($value,$arr_main);
            $encoded_parameter .= $arr_encode[$pos];
        }
        else
        {
           $encoded_parameter .= trim($value); 
        }
    }
    //echo $encoded_parameter;
   /**/ 
    /*second encoding*/
        if($encoded_parameter!='')
        {
            $key = pack('H*', "b0a04b7e103a0cd8b54763151cef08bc55abec29fdebae5e1d417e2feb2a06a3");
            $key_size =  strlen($key);
            $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            
            $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key,
                                 $encoded_parameter, MCRYPT_MODE_CBC, $iv);
            $ciphertext = $iv . $ciphertext;
            $encoded_parameter = base64_encode($ciphertext);
             $encoded_url .= $encoded_parameter;
        }
    /**/
    }
    else
    {
        $encoded_url  = $url;
    }
    
    
   //echo $encoded_url;
    
    return $encoded_url;
        
}

function decode_url()
{
   $decode_url = '';
   $array_in_parameter = array();
//   print_r($_SERVER['QUERY_STRING']);
   
   if(isset($_SERVER['QUERY_STRING']))
   {
       /*first decription*/
       $key = pack('H*', "b0a04b7e103a0cd8b54763151cef08bc55abec29fdebae5e1d417e2feb2a06a3");
       $key_size =  strlen($key);
       $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
       
       
       
        $ciphertext_dec = base64_decode($_SERVER['QUERY_STRING']);      
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);  
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);
  
        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key,
                                    $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
      
       //
       
       /*second decription*/
       $str_special = '.,=,/,&,_,;,?,[,],-,+';
       $arr_special = explode(',', $str_special);    
       $arr_main = array_merge($arr_special, range('A', 'Z'), range('a', 'z'), range('0', '9'));
       $str_encode = 'N,E,+,j,7,a,W,T,$,I,e,l,3,;,Y,i,0,B,K,@,n,t,6,x,J,P,-,C,8,D,h,Z,%,d,A,o,r,m,],F,Q,5,G,V,_,q,v,1,z,k,L,O,[,M,g,w,R,X,H,#,4,u,p,2,b,f,9,c,s,y,!,U,S';
       $arr_encode = explode(',', $str_encode);
       
       //foreach($_GET as $index => $index_val)
       //{
           $arr_plaintext = str_split($plaintext_dec);
           
           foreach ($arr_plaintext as $key => $value) 
            {
                if(in_array($value, $arr_encode))
                {
                    $pos =  array_search($value,$arr_encode);
                    $decode_url .= $arr_main[$pos];
                }
                else
                {
                   $decode_url .= $value; 
                }
            }
       //}
       //echo $decode_url;
       if($decode_url!='')
       {
           unset($_GET);
          
         $decode_url = str_replace('&amp;','&', $decode_url);  
        //echo '<br>';
         $arr_parameter = explode('&', $decode_url);
//         echo '<pre>';
//         print_r($arr_parameter);
//         echo '</pre>';
         foreach ($arr_parameter as $key_parameter => $value_parameter) 
         {
             $arr_parameter_val = explode('=', $value_parameter,2);
           //  print_r($arr_parameter_val); echo '<br>';
             //echo $arr_parameter_val['0']; echo '<br>';
             
             if (strpos($arr_parameter_val['0'], '[') !== false && strpos($arr_parameter_val['0'], ']') !== false) 
                {
                   $arr_key = get_string_between($arr_parameter_val['0'],'[',']');  
                   $arr_index = str_replace('[', '', $arr_parameter_val['0']);
                   $arr_index = str_replace($arr_key.']', '', $arr_index);  
                   $arr_value = $arr_parameter_val['1'];
                   
                   if($arr_key!='')
                    $array_in_parameter[$arr_index][$arr_key] = trim($arr_value);   
                   else    
                    $array_in_parameter[$arr_index] = array(trim($arr_value));
                }
             else
                { 
                      $_GET[$arr_parameter_val['0']] = $_REQUEST[$arr_parameter_val['0']] = trim($arr_parameter_val['1']);
                }
                
              $_GET =  array_merge($_GET,$array_in_parameter);
              $_REQUEST = array_merge($_REQUEST,$array_in_parameter);
         }
       }
    
   }
  
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

?>

