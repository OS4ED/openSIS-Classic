<?php
error_reporting(0);

/// CONSTANTS (Encased in phpdoc proper comments)/////////////////////////

/// Date and time constants ///
/**
 * Time constant - the number of seconds in a year
 */

define('YEARSECS', 31536000);

/**
 * Time constant - the number of seconds in a week
 */
define('WEEKSECS', 604800);

/**
 * Time constant - the number of seconds in a day
 */
define('DAYSECS', 86400);

/**
 * Time constant - the number of seconds in an hour
 */
define('HOURSECS', 3600);

/**
 * Time constant - the number of seconds in a minute
 */
define('MINSECS', 60);

/**
 * Time constant - the number of minutes in a day
 */
define('DAYMINS', 1440);

/**
 * Time constant - the number of minutes in an hour
 */
define('HOURMINS', 60);

/// Parameter constants - every call to optional_param(), required_param()  ///
/// or clean_param() should have a specified type of parameter.  //////////////

/**
 * PARAM_RAW specifies a parameter that is not cleaned/processed in any way;
 * originally was 0, but changed because we need to detect unknown
 * parameter types and swiched order in clean_param().
 */
define('PARAM_RAW', 666);

/**
 * PARAM_CLEAN - obsoleted, please try to use more specific type of parameter.
 * It was one of the first types, that is why it is abused so much ;-)
 */
define('PARAM_CLEAN',    0x0001);

/**
 * PARAM_INT - integers only, use when expecting only numbers.
 */
define('PARAM_INT',      0x0002);

/**
 * PARAM_INTEGER - an alias for PARAM_INT
 */
define('PARAM_INTEGER',  0x0002);

/**
 * PARAM_NUMBER - a real/floating point number.
 */
define('PARAM_NUMBER',  0x000a);


define('PARAM_PERCENT', 0x0006);
/**
 * PARAM_ALPHA - contains only english letters.
 */
define('PARAM_ALPHA',    0x0004);

/**
 * PARAM_ALPHA - contains only english letters and _
 */
define('PARAM_ALPHAMOD',    0x00014);

/**
 * PARAM_ALPHA - contains only english letters and space.
 */
define('PARAM_ALPHASPACE',    0x0040);

/**
 * PARAM_ACTION - an alias for PARAM_ALPHA, use for various actions in formas and urls
 * @TODO: should we alias it to PARAM_ALPHANUM ?
 */
define('PARAM_ACTION',   0x0004);

/**
 * PARAM_FORMAT - an alias for PARAM_ALPHA, use for names of plugins, formats, etc.
 * @TODO: should we alias it to PARAM_ALPHANUM ?
 */
define('PARAM_FORMAT',   0x0004);

/**
 * PARAM_NOTAGS - all html tags are stripped from the text. Do not abuse this type.
 */
define('PARAM_NOTAGS',   0x0008);

/**
 * PARAM_MULTILANG - alias of PARAM_TEXT.
 */
define('PARAM_MULTILANG',  0x0009);

/**
 * PARAM_TEXT - general plain text compatible with multilang filter, no other html tags.
 */
define('PARAM_TEXT',  0x0009);

/**
 * PARAM_FILE - safe file name, all dangerous chars are stripped, protects against XSS, SQL injections and directory traversals
 */
define('PARAM_FILE',     0x0010);

/**
 * PARAM_TAG - one tag (interests, blogs, etc.) - mostly international alphanumeric with spaces
 */
define('PARAM_TAG',   0x0011);

/**
 * PARAM_TAGLIST - list of tags separated by commas (interests, blogs, etc.)
 */
define('PARAM_TAGLIST',   0x0012);

/**
 * PARAM_PATH - safe relative path name, all dangerous chars are stripped, protects against XSS, SQL injections and directory traversals
 * note: the leading slash is not removed, window drive letter is not allowed
 */
define('PARAM_PATH',     0x0020);

/**
 * PARAM_HOST - expected fully qualified domain name (FQDN) or an IPv4 dotted quad (IP address)
 */
define('PARAM_HOST',     0x0040);

/**
 * PARAM_URL - expected properly formatted URL. Please note that domain part is required, http://localhost/ is not acceppted but http://localhost.localdomain/ is ok.
 */
define('PARAM_URL',      0x0080);

/**
 * PARAM_LOCALURL - expected properly formatted URL as well as one that refers to the local server itself. (NOT orthogonal to the others! Implies PARAM_URL!)
 */
define('PARAM_LOCALURL', 0x0180);

/**
 * PARAM_CLEANFILE - safe file name, all dangerous and regional chars are removed,
 * use when you want to store a new file submitted by students
 */
define('PARAM_CLEANFILE', 0x0200);

/**
 * PARAM_ALPHANUM - expected numbers and letters only.
 */
define('PARAM_ALPHANUM', 0x0400);

/**
 * PARAM_USERNAME - expected numbers,_ and letters only.
 */
define('PARAM_USERNAME', 0x1200);

/**
 * PARAM_PROFICIENCY - expected numbers,-and % only.
 */
define('PARAM_PROFICIENCY', 0x1400);

/**
 * PARAM_BOOL - converts input into 0 or 1, use for switches in forms and urls.
 */
define('PARAM_BOOL',     0x0800);

/**
 * PARAM_CLEANHTML - cleans submitted HTML code and removes slashes
 * note: do not forget to addslashes() before storing into database!
 */
define('PARAM_CLEANHTML', 0x1000);


define('PARAM_DATA', 0x1800);

/**
 * PARAM_ALPHAEXT the same contents as PARAM_ALPHA plus the chars in quotes: "/-_" allowed,
 * suitable for include() and require()
 * @TODO: should we rename this function to PARAM_SAFEDIRS??
 */
define('PARAM_ALPHAEXT', 0x2000);

/**
 * PARAM_ALPHAEXT the same contents as PARAM_ALPHA plus the chars in quotes: " -," allowed,
 * suitable for include() and require()
 * @TODO: should we rename this function to PARAM_SAFEDIRS??
 */
define('PARAM_ALPHAEXTS', 0x2200);

/**
 * PARAM_ALPHAEXT the same contents as PARAM_ALPHA plus the chars in quotes: " -," allowed,
 * suitable for include() and require()
 * @TODO: should we rename this function to PARAM_SAFEDIRS??
 */
define('PARAM_ALPHANUMS', 0x2300);
/**
 * PARAM_ALPHAEXT the same contents as PARAM_ALPHA plus the chars in quotes: "@." allowed,
 * suitable for include() and require()
 * @TODO: should we rename this function to PARAM_SAFEDIRS??
 */
define('PARAM_MAIL', 0x2400);

define('PARAM_CAL_TITLE', 0x2600);

define('PARAM_TIME', 0x2700);
/**
 * PARAM_PHONE - expects numbers like 8 to 1,5,6,4,6,8,9.  Numbers, comma,-,+ only.
 */
define('PARAM_PHONE', 0x2500);

/**
 * PARAM_SAFEDIR - safe directory name, suitable for include() and require()
 */
define('PARAM_SAFEDIR',  0x4000);

/**
 * PARAM_SEQUENCE - expects a sequence of numbers like 8 to 1,5,6,4,6,8,9.  Numbers and comma only.
 */
define('PARAM_SEQUENCE',  0x8000);

/**
 * PARAM_PEM - Privacy Enhanced Mail format
 */
define('PARAM_PEM',      0x10000);

/**
 * PARAM_BASE64 - Base 64 encoded format
 */
define('PARAM_BASE64',   0x20000);

define('PARAM_SPCL',   0x0111);
/// Debug levels ///
/** no warnings at all */
define('DEBUG_NONE', 0);
/** E_ERROR | E_PARSE */
define('DEBUG_MINIMAL', 5);
/** E_ERROR | E_PARSE | E_WARNING | E_NOTICE */
define('DEBUG_NORMAL', 15);
/** E_ALL without E_STRICT for now, do show recoverable fatal errors */
define('DEBUG_ALL', 6143);


/**
 * Tag constanst
 */
//To prevent problems with multibytes strings, this should not exceed the
//length of "varchar(255) / 3 (bytes / utf-8 character) = 85".
define('TAG_MAX_LENGTH', 50);

/**
 * Password policy constants
 */
define('PASSWORD_LOWER', 'abcdefghijklmnopqrstuvwxyz');
define('PASSWORD_UPPER', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
define('PASSWORD_DIGITS', '0123456789');
define('PASSWORD_NONALPHANUM', '.,;:!?_-+/*@#&$');

if (!defined('SORT_LOCALE_STRING')) { // PHP < 4.4.0 - TODO: remove in 2.0
   define('SORT_LOCALE_STRING', SORT_STRING);
}


/// PARAMETER HANDLING ////////////////////////////////////////////////////

/**
 * Returns a particular value for the named variable, taken from
 * POST or GET.  If the parameter doesn't exist then an error is
 * thrown because we require this variable.
 *
 * This function should be used to initialise all required values
 * in a script that are based on parameters.  Usually it will be
 * used like this:
 *    $id = required_param('id');
 *
 * @param string $parname the name of the page parameter we want
 * @param int $type expected type of parameter
 * @return mixed
 */
function required_param($parname, $type = PARAM_CLEAN)
{
   if (isset($_POST[$parname])) {       // POST has precedence
      $param = $_POST[$parname];
   } else if (isset($_GET[$parname])) {
      $param = $_GET[$parname];
   } else {
      echo 'A required parameter (' . $parname . ') was missing';
   }

   return clean_param($param, $type);
}

/**
 * Returns a particular value for the named variable, taken from
 * POST or GET, otherwise returning a given default.
 *
 * This function should be used to initialise all optional values
 * in a script that are based on parameters.  Usually it will be
 * used like this:
 *    $name = optional_param('name', 'Fred');
 *
 * @param string $parname the name of the page parameter we want
 * @param mixed  $default the default value to return if nothing is found
 * @param int $type expected type of parameter
 * @return mixed
 */
function optional_param($parname, $default = NULL, $type = PARAM_CLEAN)
{



   if (isset($_POST[$parname])) {       // POST has precedence
      $param = $_POST[$parname];
   } else if (isset($_GET[$parname])) {
      $param = $_GET[$parname];
   } else {
      return $default;
   }

   return clean_param($param, $type);
}

/**
 * Used by {@link optional_param()} and {@link required_param()} to
 * clean the variables and/or cast to specific types, based on
 * an options field.
 *
 * @uses PARAM_RAW
 * @uses PARAM_CLEAN
 * @uses PARAM_CLEANHTML
 * @uses PARAM_INT
 * @uses PARAM_NUMBER
 * @uses PARAM_ALPHA
 * @uses PARAM_ALPHANUM
 * @uses PARAM_ALPHAEXT
 * @uses PARAM_SEQUENCE
 * @uses PARAM_BOOL
 * @uses PARAM_NOTAGS
 * @uses PARAM_TEXT
 * @uses PARAM_SAFEDIR
 * @uses PARAM_CLEANFILE
 * @uses PARAM_FILE
 * @uses PARAM_PATH
 * @uses PARAM_HOST
 * @uses PARAM_URL
 * @uses PARAM_LOCALURL
 * @uses PARAM_PEM
 * @uses PARAM_BASE64
 * @uses PARAM_TAG
 * @uses PARAM_SEQUENCE
 * @param mixed $param the variable we are cleaning
 * @param int $type expected format of param after cleaning.
 * @return mixed
 */
function clean_param($param, $type)
{


   if (is_array($param)) {              // Let's loop
      $newparam = array();
      foreach ($param as $key => $value) {
         $newparam[$key] = clean_param($value, $type);
      }
      return $newparam;
   }

   switch ($type) {
      case PARAM_RAW:          // no cleaning at all
         return $param;

      case PARAM_CLEAN:        // General HTML cleaning, try to use more specific type if possible

         if (is_numeric($param)) {
            return $param;
         }
         $param = stripslashes($param);   // Needed for kses to work fine
         $param = clean_text($param);     // Sweep for scripts, etc

         return addslashes($param);       // Restore original request parameter slashes

      case PARAM_CLEANHTML:    // prepare html fragment for display, do not store it into db!!
         $param = stripslashes($param);   // Remove any slashes
         $param = clean_text($param);     // Sweep for scripts, etc
         return trim($param);

      case PARAM_INT:
         return (int)$param;  // Convert to integer

      case PARAM_NUMBER:
         return (float)$param;  // Convert to integer

      case PARAM_PERCENT:
         // return par_rep('/[^0-9%.]/i', '', $param);  // Remove everything not 0-9.%
         return replace_croatain($param);

      case PARAM_ALPHA:        // Remove everything not a-z
         return par_rep('/[^a-zA-Z#&]/i', '', $param);
         //  return replace_croatain($param);

      case PARAM_ALPHAMOD:        // Remove everything not a-z_
         return par_rep('/[^a-zA-Z_#&]/i', '', $param);
         //return replace_croatain($param);

      case PARAM_ALPHASPACE:        // Remove everything not a-z
         return par_rep('/[^\sa-zA-Z#&]/i', '', $param);
         // return replace_croatain($param);

      case PARAM_ALPHANUM:     // Remove everything not a-zA-Z0-9
         return par_rep('/[^A-Za-z0-9#&]/i', '', $param);
         // return replace_croatain($param);

      case PARAM_USERNAME:
         return replace_croatain($param);


      case PARAM_PROFICIENCY:     // Remove everything not 0-9%-
         return par_rep('/[^0-9%-]/i', '', $param);


      case PARAM_DATA:     // Remove everything not a-zA-Z0-9_-
         return par_rep('/[^a-zA-Z0-9_-]/i', '', $param);
         // return replace_croatain($param);

         // case PARAM_NAME:     // Remove everything not a-zA-Z/_-
         //    return par_rep('/[^a-zA-Z0-9-#&._\']/i', '', $param);

      case PARAM_ALPHAEXT:     // Remove everything not a-zA-Z/_-
         return par_rep('/[^a-zA-Z\/_#-&]/i', '', $param);
         // return replace_croatain($param);

      case PARAM_ALPHAEXTS:     // Remove everything not a-zA-Z -,
         return par_rep('/[^\sa-zA-Z-,.#&]/i', '', $param);
         // return replace_croatain($param);

      case PARAM_ALPHANUMS:     // Remove everything not a-zA-Z -,
         return par_rep('/[^\sa-zA-Z0-9-,.#&]/i', '', $param);
         // return replace_croatain($param);

      case PARAM_PHONE:     // Remove everything not 0-9-+,
         return par_rep('/[^0-9-+,().#&]/i', '', $param);

      case PARAM_MAIL:     // Remove everything not a-zA-Z -,
         return par_rep('/[^a-z0-9.@_#&-]/i', '', $param);

      case PARAM_CAL_TITLE:     // Remove everything not a-zA-Z0-9._'"-,
         return par_rep('/[^\sa-zA-Z0-9-,#&._\'\"]/i', '', $param);
         // return replace_croatain($param);


      case PARAM_TIME:     // Remove everything not a-zA-Z0-9._'"-,
         return par_rep('/[^\sa-zA-Z0-9:#&]/i', '', $param);
         //return replace_croatain($param);


      case PARAM_SEQUENCE:     // Remove everything not 0-9,
         return par_rep('/[^0-9,]/i', '', $param);

      case PARAM_BOOL:         // Convert to 1 or 0
         $tempstr = strtolower($param);
         if ($tempstr == 'on' or $tempstr == 'yes') {
            $param = 1;
         } else if ($tempstr == 'off' or $tempstr == 'no') {
            $param = 0;
         } else {
            $param = empty($param) ? 0 : 1;
         }
         return $param;

      case PARAM_NOTAGS:       // Strip all tags
         $param = strip_tags($param);
         $param = str_replace('>', '', $param);
         return str_replace('<', '', $param);

      case PARAM_TEXT:    // leave only tags needed for multilang
         return clean_param(strip_tags($param, '<lang><span>'), PARAM_CLEAN);

      case PARAM_SAFEDIR:      // Remove everything not a-zA-Z0-9_-
         return par_rep('/[^a-zA-Z0-9_-]/i', '', $param);

      case PARAM_SPCL:      // Remove *@#\/<>]/
         $param = par_rep('/[*@#\/<>!$?:;~`^%]/', '', $param);
         $param = stripslashes($param);
         return $param;

      case PARAM_CLEANFILE:    // allow only safe characters
         return clean_filename($param);

      case PARAM_FILE:         // Strip all suspicious characters from filename
         $param = par_rep('~[[:cntrl:]]|[&<>"`\|\':\\\\/]~u', '', $param);
         $param = par_rep('~\.\.+~', '', $param);
         if ($param === '.') {
            $param = '';
         }
         return $param;

      case PARAM_PATH:         // Strip all suspicious characters from file path
         $param = str_replace('\\', '/', $param);
         $param = par_rep('~[[:cntrl:]]|[&<>"`\|\':]~u', '', $param);
         $param = par_rep('~\.\.+~', '', $param);
         $param = par_rep('~//+~', '/', $param);
         return par_rep('~/(\./)+~', '/', $param);

      case PARAM_HOST:         // allow FQDN or IPv4 dotted quad
         $param = par_rep('/[^\.\d\w-]/', '', $param); // only allowed chars
         // match ipv4 dotted quad
         if (preg_match('/(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})/', $param, $match)) {
            // confirm values are ok
            if (
               $match[0] > 255
               || $match[1] > 255
               || $match[3] > 255
               || $match[4] > 255
            ) {
               // hmmm, what kind of dotted quad is this?
               $param = '';
            }
         } elseif (
            preg_match('/^[\w\d\.-]+$/', $param) // dots, hyphens, numbers
            && !preg_match('/^[\.-]/',  $param) // no leading dots/hyphens
            && !preg_match('/[\.-]$/',  $param) // no trailing dots/hyphens
         ) {
            // all is ok - $param is respected
         } else {
            // all is not ok...
            $param = '';
         }
         return $param;

      case PARAM_URL:          // allow safe ftp, http, mailto urls

         if (!empty($param) && validateUrlSyntax($param, 's?H?S?F?E?u-P-a?I?p?f?q?r?')) {
            // all is ok, param is respected
         } else {
            $param = ''; // not really ok
         }
         return $param;

      case PARAM_LOCALURL:     // allow http absolute, root relative and relative URLs within wwwroot
         $param = clean_param($param, PARAM_URL);
         if (!empty($param)) {
            if (preg_match(':^/:', $param)) {
               // root-relative, ok!
            } else {
               // relative - let's make sure there are no tricks
               if (validateUrlSyntax($param, 's-u-P-a-p-f+q?r?')) {
                  // looks ok.
               } else {
                  $param = '';
               }
            }
         }
         return $param;


      case PARAM_PEM:
         $param = trim($param);
         // PEM formatted strings may contain letters/numbers and the symbols
         // forward slash: /
         // plus sign:     +
         // equal sign:    =
         // , surrounded by BEGIN and END CERTIFICATE prefix and suffixes
         if (preg_match('/^-----BEGIN CERTIFICATE-----([\s\w\/\+=]+)-----END CERTIFICATE-----$/', trim($param), $matches)) {
            list($wholething, $body) = $matches;
            unset($wholething, $matches);
            $b64 = clean_param($body, PARAM_BASE64);
            if (!empty($b64)) {
               return "-----BEGIN CERTIFICATE-----\n$b64\n-----END CERTIFICATE-----\n";
            } else {
               return '';
            }
         }
         return '';

      case PARAM_BASE64:
         if (!empty($param)) {
            // PEM formatted strings may contain letters/numbers and the symbols
            // forward slash: /
            // plus sign:     +
            // equal sign:    =
            if (0 >= preg_match('/^([\s\w\/\+=]+)$/', trim($param))) {
               return '';
            }
            $lines = preg_split('/[\s]+/', $param, -1, PREG_SPLIT_NO_EMPTY);
            // Each line of base64 encoded data must be 64 characters in
            // length, except for the last line which may be less than (or
            // equal to) 64 characters long.
            for ($i = 0, $j = count($lines); $i < $j; $i++) {
               if ($i + 1 == $j) {
                  if (64 < strlen($lines[$i])) {
                     return '';
                  }
                  continue;
               }

               if (64 != strlen($lines[$i])) {
                  return '';
               }
            }
            return implode("\n", $lines);
         } else {
            return '';
         }

      case PARAM_TAG:
         //as long as magic_quotes_gpc is used, a backslash will be a
         //problem, so remove *all* backslash.
         $param = str_replace('\\', '', $param);
         //convert many whitespace chars into one
         $param = par_rep('/\s+/', ' ', $param);
         $textlib = textlib_get_instance();
         $param = $textlib->substr(trim($param), 0, TAG_MAX_LENGTH);
         return $param;


      case PARAM_TAGLIST:
         $tags = explode(',', $param);
         $result = array();
         foreach ($tags as $tag) {
            $res = clean_param($tag, PARAM_TAG);
            if ($res != '') {
               $result[] = $res;
            }
         }
         if ($result) {
            return implode(',', $result);
         } else {
            return '';
         }

      default:                 // throw error, switched parameters in optional_param or another serious problem
         echo "Unknown parameter type: $type";
   }
}

/**
 * Return true if given value is integer or string with integer value
 *
 * @param mixed $value String or Int
 * @return bool true if number, false if not
 */
function is_number($value)
{
   if (is_int($value)) {
      return true;
   } else if (is_string($value)) {
      return ((string)(int)$value) === $value;
   } else {
      return false;
   }
}

/**
 * This function is useful for testing whether something you got back from
 * the HTML editor actually contains anything. Sometimes the HTML editor
 * appear to be empty, but actually you get back a <br> tag or something.
 *
 * @param string $string a string containing HTML.
 * @return boolean does the string contain any actual content - that is text,
 * images, objcts, etc.
 */
function html_is_blank($string)
{
   return trim(strip_tags($string, '<img><object><applet><input><select><textarea><hr>')) == '';
}


function clean_filename($string)
{

   //clean only ascii range
   $string = par_rep("/[\\000-\\x2c\\x2f\\x3a-\\x40\\x5b-\\x5e\\x60\\x7b-\\177]/s", '_', $string);

   $string = par_rep("/_+/", '_', $string);
   $string = par_rep("/\.\.+/", '.', $string);
   return $string;
}


function validateUrlSyntax($urladdr, $options = "")
{

   // Force Options parameter to be lower case
   // DISABLED PERMAMENTLY - OK to remove from code
   //    $options = strtolower($options);

   //Check Options Parameter
   if (!par_rep_all('/^([sHSEFuPaIpfqr][+?-])*$/', $options)) {
      trigger_error("Options attribute malformed", E_USER_ERROR);
   }

   // Set Options Array, set defaults if options are not specified
   // Scheme
   if (strpos($options, 's') === false) $aOptions['s'] = '?';
   else $aOptions['s'] = substr($options, strpos($options, 's') + 1, 1);
   // http://
   if (strpos($options, 'H') === false) $aOptions['H'] = '?';
   else $aOptions['H'] = substr($options, strpos($options, 'H') + 1, 1);
   // https:// (SSL)
   if (strpos($options, 'S') === false) $aOptions['S'] = '?';
   else $aOptions['S'] = substr($options, strpos($options, 'S') + 1, 1);
   // mailto: (email)
   if (strpos($options, 'E') === false) $aOptions['E'] = '-';
   else $aOptions['E'] = substr($options, strpos($options, 'E') + 1, 1);
   // ftp://
   if (strpos($options, 'F') === false) $aOptions['F'] = '-';
   else $aOptions['F'] = substr($options, strpos($options, 'F') + 1, 1);
   // User section
   if (strpos($options, 'u') === false) $aOptions['u'] = '?';
   else $aOptions['u'] = substr($options, strpos($options, 'u') + 1, 1);
   // Password in user section
   if (strpos($options, 'P') === false) $aOptions['P'] = '?';
   else $aOptions['P'] = substr($options, strpos($options, 'P') + 1, 1);
   // Address Section
   if (strpos($options, 'a') === false) $aOptions['a'] = '+';
   else $aOptions['a'] = substr($options, strpos($options, 'a') + 1, 1);
   // IP Address in address section
   if (strpos($options, 'I') === false) $aOptions['I'] = '?';
   else $aOptions['I'] = substr($options, strpos($options, 'I') + 1, 1);
   // Port number
   if (strpos($options, 'p') === false) $aOptions['p'] = '?';
   else $aOptions['p'] = substr($options, strpos($options, 'p') + 1, 1);
   // File Path
   if (strpos($options, 'f') === false) $aOptions['f'] = '?';
   else $aOptions['f'] = substr($options, strpos($options, 'f') + 1, 1);
   // Query Section
   if (strpos($options, 'q') === false) $aOptions['q'] = '?';
   else $aOptions['q'] = substr($options, strpos($options, 'q') + 1, 1);
   // Fragment (Anchor)
   if (strpos($options, 'r') === false) $aOptions['r'] = '?';
   else $aOptions['r'] = substr($options, strpos($options, 'r') + 1, 1);


   // Loop through options array, to search for and replace "-" to "{0}" and "+" to ""
   foreach ($aOptions as $key => $value) {
      if ($value == '-') {
         $aOptions[$key] = '{0}';
      }
      if ($value == '+') {
         $aOptions[$key] = '';
      }
   }

   // DEBUGGING - Unescape following line to display to screen current option values
   // echo '<pre>'; print_r($aOptions); echo '</pre>';


   // Preset Allowed Characters
   $alphanum    = '[a-zA-Z0-9]';  // Alpha Numeric
   $unreserved  = '[a-zA-Z0-9_.!~*' . '\'' . '()-]';
   $escaped     = '(%[0-9a-fA-F]{2})'; // Escape sequence - In Hex - %6d would be a 'm'
   $reserved    = '[;/?:@&=+$,]'; // Special characters in the URI

   // Beginning Regular Expression
   // Scheme - Allows for 'http://', 'https://', 'mailto:', or 'ftp://'
   $scheme            = '(';
   if ($aOptions['H'] === '') {
      $scheme .= 'http://';
   } elseif ($aOptions['S'] === '') {
      $scheme .= 'https://';
   } elseif ($aOptions['E'] === '') {
      $scheme .= 'mailto:';
   } elseif ($aOptions['F'] === '') {
      $scheme .= 'ftp://';
   } else {
      if ($aOptions['H'] === '?') {
         $scheme .= '|(http://)';
      }
      if ($aOptions['S'] === '?') {
         $scheme .= '|(https://)';
      }
      if ($aOptions['E'] === '?') {
         $scheme .= '|(mailto:)';
      }
      if ($aOptions['F'] === '?') {
         $scheme .= '|(ftp://)';
      }
      $scheme = str_replace('(|', '(', $scheme); // fix first pipe
   }
   $scheme            .= ')' . $aOptions['s'];
   // End setting scheme

   // User Info - Allows for 'username@' or 'username:password@'. Note: contrary to rfc, I removed ':' from username section, allowing it only in password.
   //   /---------------- Username -----------------------\  /-------------------------------- Password ------------------------------\
   $userinfo          = '((' . $unreserved . '|' . $escaped . '|[;&=+$,]' . ')+(:(' . $unreserved . '|' . $escaped . '|[;:&=+$,]' . ')+)' . $aOptions['P'] . '@)' . $aOptions['u'];

   // IP ADDRESS - Allows 0.0.0.0 to 255.255.255.255
   $ipaddress         = '((((2(([0-4][0-9])|(5[0-5])))|([01]?[0-9]?[0-9]))\.){3}((2(([0-4][0-9])|(5[0-5])))|([01]?[0-9]?[0-9])))';

   // Tertiary Domain(s) - Optional - Multi - Although some sites may use other characters, the RFC says tertiary domains have the same naming restrictions as second level domains
   $domain_tertiary   = '(' . $alphanum . '(([a-zA-Z0-9-]{0,62})' . $alphanum . ')?\.)*';

   /* MDL-9295 - take out domain_secondary here and below, so that URLs like http://localhost/ and lan addresses like http://host/ are accepted.
                       // Second Level Domain - Required - First and last characters must be Alpha-numeric. Hyphens are allowed inside.
    $domain_secondary  = '(' . $alphanum . '(([a-zA-Z0-9-]{0,62})' . $alphanum . ')?\.)';
*/


   // Top Level Domain - First character must be Alpha. Last character must be AlphaNumeric. Hyphens are allowed inside.
   $domain_toplevel   = '([a-zA-Z](([a-zA-Z0-9-]*)[a-zA-Z0-9])?)';
   /*                       // Top Level Domain - Required - Domain List Current As Of December 2004. Use above escaped line to be forgiving of possible future TLD's
    $domain_toplevel   = '(aero|biz|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|post|pro|travel|ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|az|ax|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)';
*/

   // Address can be IP address or Domain
   if ($aOptions['I'] === '{0}') {       // IP Address Not Allowed
      $address       = '(' . $domain_tertiary . /* MDL-9295 $domain_secondary . */ $domain_toplevel . ')';
   } elseif ($aOptions['I'] === '') {  // IP Address Required
      $address       = '(' . $ipaddress . ')';
   } else {                            // IP Address Optional
      $address       = '((' . $ipaddress . ')|(' . $domain_tertiary . /* MDL-9295 $domain_secondary . */ $domain_toplevel . '))';
   }
   $address = $address . $aOptions['a'];

   // Port Number - :80 or :8080 or :65534 Allows range of :0 to :65535
   //    (0-59999)         |(60000-64999)   |(65000-65499)    |(65500-65529)  |(65530-65535)
   $port_number       = '(:(([0-5]?[0-9]{1,4})|(6[0-4][0-9]{3})|(65[0-4][0-9]{2})|(655[0-2][0-9])|(6553[0-5])))' . $aOptions['p'];

   // Path - Can be as simple as '/' or have multiple folders and filenames
   $path              = '(/((;)?(' . $unreserved . '|' . $escaped . '|' . '[:@&=+$,]' . ')+(/)?)*)' . $aOptions['f'];

   // Query Section - Accepts ?var1=value1&var2=value2 or ?2393,1221 and much more
   $querystring       = '(\?(' . $reserved . '|' . $unreserved . '|' . $escaped . ')*)' . $aOptions['q'];

   // Fragment Section - Accepts anchors such as #top
   $fragment          = '(#(' . $reserved . '|' . $unreserved . '|' . $escaped . ')*)' . $aOptions['r'];


   // Building Regular Expression
   $regexp = '^' . $scheme . $userinfo . $address . $port_number . $path . $querystring . $fragment . '$';

   // DEBUGGING - Uncomment Line Below To Display The Regular Expression Built
   // echo '<pre>' . htmlentities(wordwrap($regexp,70,"\n",1)) . '</pre>';

   // Running the regular expression
   if (par_rep($regexp, $urladdr)) {
      return true; // The domain passed
   } else {
      return false; // The domain didn't pass the expression
   }
} // END Function validateUrlSyntax()

function clean_text($text, $format = 'FORMAT_SIS')
{

   global $ALLOWED_TAGS;

   if (empty($text) or is_numeric($text)) {
      return (string)$text;
   }

   switch ($format) {
      case 'FORMAT_PLAIN':
      case 'FORMAT_MARKDOWN':
         return $text;

      default:


         /// Fix non standard entity notations
         $text = par_rep('/&#0*([0-9]+);?/', "&#\\1;", $text);
         $text = par_rep('/&#x0*([0-9a-fA-F]+);?/', "&#x\\1;", $text);

         /// Remove tags that are not allowed
         $text = strip_tags($text, $ALLOWED_TAGS);

         /// Clean up embedded scripts and , using kses
         $text = cleanAttributes($text);

         /// Again remove tags that are not allowed
         $text = strip_tags($text, $ALLOWED_TAGS);
   }

   /// Remove potential script events - some extra protection for undiscovered bugs in our code
   $text = preg_filter("([^a-z])language([[:space:]]*)=", "\\1Xlanguage=", $text);
   $text = preg_filter("([^a-z])on([a-z]+)([[:space:]]*)=", "\\1Xon\\2=", $text);

   return $text;
}

function cleanAttributes($str)
{
   $search = array('~[^\\pL_]+~u',  '~[^-a-zA-Z0-9_]+~', '@[^>]*?>.*?@si');                    // evaluate as php
   $replace = array('', '', '');
   $result = par_rep($search, $replace, $str);
   return  $result;
}




function paramlib_validation($feild, $value)
{
   if ($feild == 'FIRST_NAME') {
      $val = clean_param($value, PARAM_USERNAME);
   } elseif ($feild == 'MIDDLE_NAME') {
      $val = clean_param($value, PARAM_USERNAME);
   } elseif ($feild == 'LAST_NAME') {
      $val = clean_param($value, PARAM_USERNAME);
   } elseif ($feild == 'GENDER' || $feild == 'gender') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'ETHNICITY' || $feild == 'ethnicity') {
      $val = clean_param($value, PARAM_ALPHAEXTS);
   } elseif ($feild == 'COMMON_NAME' || $feild == 'common_name') {
      $val = clean_param($value, PARAM_USERNAME);
   } elseif ($feild == 'LANGUAGE' || $feild == 'language') {
      $val = clean_param($value, PARAM_ALPHAEXTS);
   } elseif ($feild == 'EMAIL' || $feild == 'email') {
      $val = clean_param($value, PARAM_MAIL);
   } elseif ($feild == 'PHONE' || $feild == 'phone') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'ALT_ID') {
      $val = clean_param($value, PARAM_ALPHANUM);
   } elseif ($feild == 'USERNAME') {
      $val = clean_param($value, PARAM_RAW);
   } elseif ($feild == 'PASSWORD') {
      $val = clean_param($value, PARAM_RAW);
   } elseif ($feild == 'ESTIMATED_GRAD_DATE') {
      $val = $value;
   } elseif ($feild == 'BIRTHDATE') {
      $val = $value;
   } elseif ($feild == 'GRADE_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'IS_DISABLE') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'GOAL_TITLE') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'GOAL_DESCRIPTION') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'COURSE_PERIOD_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'PROGRESS_NAME') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'PROFICIENCY') {
      $val = clean_param($value, PARAM_PROFICIENCY);
   } elseif ($feild == 'PROGRESS_DESCRIPTION') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'COMMENT') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'PHYSICIAN' || $feild == 'physician') {
      $val = clean_param($value, PARAM_USERNAME);
   } elseif ($feild == 'PHYSICIAN_PHONE' || $feild == 'physician_phone') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'PREFERRED_HOSPITAL' || $feild == 'preferred_hospital') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'DOCTORS_NOTE_COMMENTS') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'COMMENTS') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'TITLE') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'TIME_IN') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'TIME_OUT') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'REASON') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'RESULT') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'ADDRESS') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'STREET') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'CITY') {
      $val = clean_param($value, PARAM_ALPHANUMS);
   } elseif ($feild == 'STATE') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'ZIPCODE') {
      $val = clean_param($value, PARAM_ALPHANUMS);
   } elseif ($feild == 'BUS_PICKUP') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'BUS_DROPOFF') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'BUS_NO') {
      $val = clean_param($value, PARAM_ALPHANUMS);
   } elseif ($feild == 'MAIL_ADDRESS') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'MAIL_STREET') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'MAIL_CITY') {
      $val = clean_param($value, PARAM_ALPHANUMS);
   } elseif ($feild == 'MAIL_STATE') {
      $val = clean_param($value, PARAM_ALPHAEXTS);
   } elseif ($feild == 'MAIL_ZIPCODE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'PRIM_STUDENT_RELATION') {
      $val = clean_param($value, PARAM_RAW);
   } elseif ($feild == 'PRI_FIRST_NAME') {
      $val = clean_param($value, PARAM_USERNAME);
   } elseif ($feild == 'PRI_LAST_NAME') {
      $val = clean_param($value, PARAM_USERNAME);
   } elseif ($feild == 'HOME_PHONE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'WORK_PHONE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'MOBILE_PHONE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'PRIM_CUSTODY') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'PRIM_ADDRESS') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'PRIM_STREET') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'PRIM_CITY') {
      $val = clean_param($value, PARAM_ALPHANUMS);
   } elseif ($feild == 'PRIM_STATE') {
      $val = clean_param($value, PARAM_ALPHAEXTS);
   } elseif ($feild == 'PRIM_ZIPCODE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'SEC_STUDENT_RELATION') {
      $val = clean_param($value, PARAM_RAW);
   } elseif ($feild == 'SEC_FIRST_NAME') {
      $val = clean_param($value, PARAM_USERNAME);
   } elseif ($feild == 'SEC_LAST_NAME') {
      $val = clean_param($value, PARAM_USERNAME);
   } elseif ($feild == 'SEC_HOME_PHONE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'SEC_WORK_PHONE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'SEC_MOBILE_PHONE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'SEC_EMAIL') {
      $val = clean_param($value, PARAM_MAIL);
   } elseif ($feild == 'SEC_CUSTODY') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'SEC_ADDRESS') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'SEC_STREET') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'SEC_CITY') {
      $val = clean_param($value, PARAM_ALPHANUMS);
   } elseif ($feild == 'SEC_STATE') {
      $val = clean_param($value, PARAM_ALPHAEXTS);
   } elseif ($feild == 'SEC_ZIPCODE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'EMERGENCY') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'STUDENT_RELATION') {
      $val = clean_param($value, PARAM_RAW);
   } elseif ($feild == 'ADDN_HOME_PHONE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'ADDN_WORK_PHONE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'ADDN_MOBILE_PHONE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'ADDN_EMAIL') {
      $val = clean_param($value, PARAM_MAIL);
   } elseif ($feild == 'CUSTODY') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'ADDN_ADDRESS') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'ADDN_STREET') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'ADDN_CITY') {
      $val = clean_param($value, PARAM_ALPHANUMS);
   } elseif ($feild == 'ADDN_STATE') {
      $val = clean_param($value, PARAM_ALPHAEXTS);
   } elseif ($feild == 'ADDN_ZIPCODE') {
      $val = clean_param($value, PARAM_PHONE);
   } elseif ($feild == 'ADDN_BUS_PICKUP') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'ADDN_BUS_DROPOFF') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'ADDN_BUSNO') {
      $val = clean_param($value, PARAM_ALPHANUMS);
   } elseif ($feild == 'INCLUDE') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'TYPE') {
      $val = clean_param($value, PARAM_ALPHAEXTS);
   } elseif ($feild == 'SELECT_OPTIONS') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'REQUIRED') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'HIDE') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'DEFAULT_SELECTION') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'PROFILE') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'SCHOOLS') {
      $val = clean_param($value, PARAM_SEQUENCE);
   } elseif ($feild == 'CONTENT') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'SORT_ORDER') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'SHORT_NAME') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'DOES_GRADES') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'DOES_EXAM') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'DOES_COMMENTS') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'DESCRIPTION') {
      $val = clean_param($value, PARAM_NOTAGS);
   } elseif ($feild == 'Calender_Title') {
      $val = clean_param($value, PARAM_CAL_TITLE);
   } elseif ($feild == 'EVENT_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'ATTENDANCE') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'IGNORE_SCHEDULING') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'START_TIME') {
      $val = clean_param($value, PARAM_TIME);
   } elseif ($feild == 'END_TIME') {
      $val = clean_param($value, PARAM_TIME);
   } elseif ($feild == 'NEXT_GRADE_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'PRINCIPAL') {
      $val = clean_param($value, PARAM_CAL_TITLE);
   } elseif ($feild == 'REPORTING_GP_SCALE') {
      $val = clean_param($value, PARAM_NUMBER);
   } elseif ($feild == 'E_MAIL') {
      $val = clean_param($value, PARAM_MAIL);
   } elseif ($feild == 'CEEB') {
      $val = clean_param($value, PARAM_ALPHANUM);
   } elseif ($feild == 'WWW_ADDRESS') {
      $val = clean_param($value, PARAM_URL);
   } elseif ($feild == 'FULL_DAY_MINUTE') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'HALF_DAY_MINUTE') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'TEACHER_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'ROOM') {
      $val = clean_param($value, PARAM_ALPHANUMS);
   } elseif ($feild == 'PERIOD_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'DAYS') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'MARKING_PERIOD_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'TOTAL_SEATS') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'DOES_ATTENDANCE') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'DOES_HONOR_ROLL') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'DOES_CLASS_RANK') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'GENDER_RESTRICTION') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'GRADE_SCALE_ID') {
      $val = clean_param($value, PARAM_ALPHANUMS);
   } elseif ($feild == 'CREDITS') {
      $val = clean_param($value, PARAM_NUMBER);
   } elseif ($feild == 'CALENDAR_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'HALF_DAY') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'DOES_BREAKOFF') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'PARENT_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'MP') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'Calender_Id') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'WITH_TEACHER_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'NOT_TEACHER_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'WITH_PERIOD_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'NOT_PERIOD_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'SCHEDULER_LOCK') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'POINTS') {
      $val = clean_param($value, PARAM_PERCENT);
   } elseif ($feild == 'COURSE_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'ASSIGNMENT_TYPE_ID') {
      $val = clean_param($value, PARAM_INT);
   } elseif ($feild == 'GP_SCALE') {
      $val = clean_param($value, PARAM_NUMBER);
   } elseif ($feild == 'BREAK_OFF') {
      $val = clean_param($value, PARAM_NUMBER);
   } elseif ($feild == 'GPA_VALUE') {
      $val = clean_param($value, PARAM_NUMBER);
   } elseif ($feild == 'UNWEIGHTED_GP') {
      $val = clean_param($value, PARAM_NUMBER);
   } elseif ($feild == 'ADMIN') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'PARENT') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'TEACHER') {
      $val = clean_param($value, PARAM_ALPHA);
   } elseif ($feild == 'NONE') {
      $val = clean_param($value, PARAM_ALPHA);
   } else {
      $val = clean_param($value, PARAM_RAW);
   }


   return $val;
}

function replace_croatain($str)
{
   $search  = array("[", "*", "@", "/", "<", ">", "!", "$", "?", ":", ";", "~", "`", "^", "%", "]", "'", '"');
   $VAL = str_replace($search, "", $str);
   return $VAL;
}

function curPageURL()
{
   $pageURL = 'http';
   if ($_SERVER["HTTPS"] == "on") {
      $pageURL .= "s";
   }
   $pageURL .= "://";
   if ($_SERVER["SERVER_PORT"] != "80") {
      $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
   } else {
      $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
   }

   return $pageURL;
   //
   // echo $pageURL;
   // exit;
}

function validateQueryString($queryString)
{
   $query = strip_tags($queryString);
   //$query1=utf8_decode($query);
   $query2 = urldecode($query);
   if (strpos($query2, 'Transcripts.php') === false && strpos($query2, 'medical alert') === false && !($_REQUEST['modname'] == 'students/Student.php' && $_REQUEST['search_modfunc'] == 'list')) {

      $search  = array("..//", "*", "../", "/.", "<", ">", "alert", "(", ")", "script", "javascript", "///", "union", "%3dalert", "{", "}", "\n", "%22", "%27", " ' ", "%23", "%3C", "%2F", "%", "%3E", "%3D", "%7B", "%7D", "%3F", "%3B", "%25", "%28", "%29", "%2A", "%26");
      $VAL = str_replace($search, "#", $queryString);
      $ddd = preg_match("/([\#\'\%\*])/ ", $VAL);
      if ($ddd == 1) {
         return false;
      }
   }
   return true;
}
