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
/****************************************** 
this is currently set to accept 11 parameters, although you can add as many as you want:
1. safe - returns true/false, you can determine what makes the browser be safe lower down, 
	currently it's set for ns4 and pre version 1 mozillas not being safe, plus all older browsers
2. ie_version - tests to see what general IE it is, ie5x-6, ie4, or ieMac, returns these values.
3. moz_version - returns array of moz version, version number (includes full version, + etc), rv number (for math
	comparison), rv number (for full rv, including alpha and beta versions), and release date
4. dom - returns true/false if it is a basic dom browser, ie >= 5, opera >= 5, all new mozillas, safaris, konquerors
5. os - returns which os is being used
6. os_number - returns windows versions, 95, 98, me, nt 4, nt 5 [windows 2000], nt 5.1 [windows xp],
	Just added: os x detection[crude] otherwise returns false
7. browser - returns the browser name, in shorthand: ie, ie4, ie5x, op, moz, konq, saf, ns4
8. number - returns the browser version number, if available, otherwise returns '' [not available]
9. full - returns this array: $browser_name, $version_number, $ie_version, $dom_browser, 
	$safe_browser, $os, $os_number, $s_browser [the browser search string from the browser array], $type
10. type - returns whether it's a bot or a browser
11. math_number - returns basic version number, for math comparison, ie. 1.2rel2a becomes 1.2
*******************************************/

// main script, uses two other functions, which_os() and browser_version() as needed
error_reporting(0);
function browser_detection( $which_test ) {
	/*
	uncomment the global variable declaration if you want the variables to be available on a global level
	throughout your php page, make sure that php is configured to support the use of globals first!
	Use of globals should be avoided however, and they are not necessary with this script
	*/

	/*global $dom_browser, $safe_browser, $browser_user_agent, $os, $browser_name, $s_browser, $ie_version, 
	$version_number, $os_number, $b_repeat, $moz_version, $moz_version_number, $moz_rv, $moz_rv_full, $moz_release;*/

	static $dom_browser, $safe_browser, $browser_user_agent, $os, $browser_name, $s_browser, $ie_version, 
	$version_number, $os_number, $b_repeat, $moz_version, $moz_version_number, $moz_rv, $moz_rv_full, $moz_release, 
	$type, $math_version_number;

	/* 
	this makes the test only run once no matter how many times you call it
	since all the variables are filled on the first run through, it's only a matter of returning the
	the right ones 
	*/
	if ( !$b_repeat )
	{
		//initialize all variables with default values to prevent error
		$dom_browser = false;
		$type = 'bot';// default to bot since you never know with bots
		$safe_browser = false;
		$os = '';
		$os_number = '';
		$a_os_data = '';
		$browser_name = '';
		$version_number = '';
		$math_version_number = '';
		$ie_version = '';
		$moz_version = '';
		$moz_version_number = '';
		$moz_rv = '';
		$moz_rv_full = '';
		$moz_release = '';
		$b_success = false;// boolean for if browser found in main test

		//make navigator user agent string lower case to make sure all versions get caught
		// isset protects against blank user agent failure
		$browser_user_agent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : '';
		
		/*
		pack the browser type array, in this order
		the order is important, because opera must be tested first, then omniweb [which has safari data in string],
		same for konqueror, then safari, then gecko, since safari navigator user agent id's with 'gecko' in string.
		note that $dom_browser is set for all  modern dom browsers, this gives you a default to use.

		array[0] = id string for useragent, array[1] is if dom capable, array[2] is working name for browser, 
		array[3] identifies navigator useragent type

		Note: all browser strings are in lower case to match the strtolower output, this avoids possible detection
		errors

		Note: There are currently 5 navigator user agent types: 
		bro - modern, css supporting browser.
		bbro - basic browser, text only, table only, defective css implementation
		bot - search type spider
		dow - known download agent
		lib - standard http libraries
		*/
		// known browsers, list will be updated routinely, check back now and then
		$a_browser_types[] = array( 'opera', true, 'op', 'bro' );
		$a_browser_types[] = array( 'omniweb', true, 'omni', 'bro' );// mac osx browser, now uses khtml engine:
		$a_browser_types[] = array( 'msie', true, 'ie', 'bro' );
		$a_browser_types[] = array( 'konqueror', true, 'konq', 'bro' );
		$a_browser_types[] = array( 'safari', true, 'saf', 'bro' );
		// covers Netscape 6-7, K-Meleon, Most linux versions, uses moz array below
		$a_browser_types[] = array( 'gecko', true, 'moz', 'bro' );
		$a_browser_types[] = array( 'netpositive', false, 'netp', 'bbro' );// beos browser
		$a_browser_types[] = array( 'lynx', false, 'lynx', 'bbro' ); // command line browser
		$a_browser_types[] = array( 'elinks ', false, 'elinks', 'bbro' ); // new version of links
		$a_browser_types[] = array( 'elinks', false, 'elinks', 'bbro' ); // alternate id for it
		$a_browser_types[] = array( 'links ', false, 'links', 'bbro' ); // old name for links
		$a_browser_types[] = array( 'links', false, 'links', 'bbro' ); // alternate id for it
		$a_browser_types[] = array( 'w3m', false, 'w3m', 'bbro' ); // open source browser, more features than lynx/links
		$a_browser_types[] = array( 'webtv', false, 'webtv', 'bbro' );// junk ms webtv
		$a_browser_types[] = array( 'amaya', false, 'amaya', 'bbro' );// w3c browser
		$a_browser_types[] = array( 'dillo', false, 'dillo', 'bbro' );// linux browser, basic table support
		$a_browser_types[] = array( 'ibrowse', false, 'ibrowse', 'bbro' );// amiga browser
		$a_browser_types[] = array( 'icab', false, 'icab', 'bro' );// mac browser 
		$a_browser_types[] = array( 'crazy browser', true, 'ie', 'bro' );// uses ie rendering engine
		$a_browser_types[] = array( 'sonyericssonp800', false, 'sonyericssonp800', 'bbro' );// sony ericsson handheld
		
		// search engine spider bots:
		$a_browser_types[] = array( 'googlebot', false, 'google', 'bot' );// google 
		$a_browser_types[] = array( 'mediapartners-google', false, 'adsense', 'bot' );// google adsense
		$a_browser_types[] = array( 'yahoo-verticalcrawler', false, 'yahoo', 'bot' );// old yahoo bot
		$a_browser_types[] = array( 'yahoo! slurp', false, 'yahoo', 'bot' ); // new yahoo bot 
		$a_browser_types[] = array( 'yahoo-mm', false, 'yahoomm', 'bot' ); // gets Yahoo-MMCrawler and Yahoo-MMAudVid bots
		$a_browser_types[] = array( 'inktomi', false, 'inktomi', 'bot' ); // inktomi bot
		$a_browser_types[] = array( 'slurp', false, 'inktomi', 'bot' ); // inktomi bot
		$a_browser_types[] = array( 'fast-webcrawler', false, 'fast', 'bot' );// Fast AllTheWeb
		$a_browser_types[] = array( 'msnbot', false, 'msn', 'bot' );// msn search 
		$a_browser_types[] = array( 'ask jeeves', false, 'ask', 'bot' ); //jeeves/teoma
		$a_browser_types[] = array( 'teoma', false, 'ask', 'bot' );//jeeves teoma
		$a_browser_types[] = array( 'scooter', false, 'scooter', 'bot' );// altavista 
		$a_browser_types[] = array( 'openbot', false, 'openbot', 'bot' );// openbot, from taiwan
		$a_browser_types[] = array( 'ia_archiver', false, 'ia_archiver', 'bot' );// ia archiver
		$a_browser_types[] = array( 'zyborg', false, 'looksmart', 'bot' );// looksmart 
		$a_browser_types[] = array( 'almaden', false, 'ibm', 'bot' );// ibm almaden web crawler 
		$a_browser_types[] = array( 'baiduspider', false, 'baidu', 'bot' );// Baiduspider asian search spider
		$a_browser_types[] = array( 'psbot', false, 'psbot', 'bot' );// psbot image crawler 
		$a_browser_types[] = array( 'gigabot', false, 'gigabot', 'bot' );// gigabot crawler 
		$a_browser_types[] = array( 'naverbot', false, 'naverbot', 'bot' );// naverbot crawler, bad bot, block
		$a_browser_types[] = array( 'surveybot', false, 'surveybot', 'bot' );// 
		$a_browser_types[] = array( 'boitho.com-dc', false, 'boitho', 'bot' );//norwegian search engine 
		$a_browser_types[] = array( 'objectssearch', false, 'objectsearch', 'bot' );// open source search engine
		$a_browser_types[] = array( 'answerbus', false, 'answerbus', 'bot' );// http://www.answerbus.com/, web questions
		$a_browser_types[] = array( 'sohu-search', false, 'sohu', 'bot' );// chinese media company, search component
		$a_browser_types[] = array( 'iltrovatore-setaccio', false, 'il-set', 'bot' );

		// various http utility libaries 
		$a_browser_types[] = array( 'w3c_validator', false, 'w3c', 'lib' ); // uses libperl, make first
		$a_browser_types[] = array( 'wdg_validator', false, 'wdg', 'lib' ); // 
		$a_browser_types[] = array( 'libwww-perl', false, 'libwww-perl', 'lib' ); 
		$a_browser_types[] = array( 'jakarta commons-httpclient', false, 'jakarta', 'lib' );
		$a_browser_types[] = array( 'python-urllib', false, 'python-urllib', 'lib' ); 
		
		// download apps 
		$a_browser_types[] = array( 'getright', false, 'getright', 'dow' );
		$a_browser_types[] = array( 'wget', false, 'wget', 'dow' );// open source downloader, obeys robots.txt

		// netscape 4 and earlier tests, put last so spiders don't get caught
		$a_browser_types[] = array( 'mozilla/4.', false, 'ns', 'bbro' );
		$a_browser_types[] = array( 'mozilla/3.', false, 'ns', 'bbro' );
		$a_browser_types[] = array( 'mozilla/2.', false, 'ns', 'bbro' );
		
		//$a_browser_types[] = array( '', false ); // browser array template

		/* 
		moz types array
		note the order, netscape6 must come before netscape, which  is how netscape 7 id's itself.
		rv comes last in case it is plain old mozilla 
		*/
		$moz_types = array( 'firebird', 'phoenix', 'firefox', 'iceweasel', 'galeon', 'k-meleon', 'camino', 'epiphany', 'netscape6', 'netscape', 'multizilla', 'swiftfox', 'rv' );

		/*
		run through the browser_types array, break if you hit a match, if no match, assume old browser
		or non dom browser, assigns false value to $b_success.
		Topherbyte pointed out that looping a count counts each time, so that's fixed
		*/
		$i_count = count($a_browser_types);
		for ($i = 0; $i < $i_count; $i++)
		{
			//unpacks browser array, assigns to variables
			$s_browser = $a_browser_types[$i][0];// text string to id browser from array

			if (stristr($browser_user_agent, $s_browser)) 
			{
				// it defaults to true, will become false below if needed
				// this keeps it easier to keep track of what is safe, only 
				//explicit false assignment will make it false.
				$safe_browser = true;

				// assign values based on match of user agent string
				$dom_browser = $a_browser_types[$i][1];// hardcoded dom support from array
				$browser_name = $a_browser_types[$i][2];// working name for browser
				$type = $a_browser_types[$i][3];// sets whether bot or browser

				switch ( $browser_name )
				{
					// this is modified quite a bit, now will return proper netscape version number
					// check your implementation to make sure it works
					case 'ns':
						$safe_browser = false;
						$version_number = browser_version( $browser_user_agent, 'mozilla' );
						break;
					case 'moz':
						/*
						note: The 'rv' test is not absolute since the rv number is very different on 
						different versions, for example Galean doesn't use the same rv version as Mozilla, 
						neither do later Netscapes, like 7.x. For more on this, read the full mozilla numbering 
						conventions here:
						http://www.mozilla.org/releases/cvstags.html
						*/

						// this will return alpha and beta version numbers, if present
						$moz_rv_full = browser_version( $browser_user_agent, 'rv' );
						// this slices them back off for math comparisons
						$moz_rv = substr( $moz_rv_full, 0, 3 );

						// this is to pull out specific mozilla versions, firebird, netscape etc..
						$i_count = count( $moz_types );
						for ( $i = 0; $i < $i_count; $i++ )
						{
							if ( stristr( $browser_user_agent, $moz_types[$i] ) ) 
							{
								$moz_version = $moz_types[$i];
								$moz_version_number = browser_version( $browser_user_agent, $moz_version );
								break;
							}
						}
						// this is necesary to protect against false id'ed moz'es and new moz'es.
						// this corrects for galeon, or any other moz browser without an rv number
						if ( !$moz_rv ) 
						{ 
							$moz_rv = substr( $moz_version_number, 0, 3 ); 
							$moz_rv_full = $moz_version_number; 
							/* 
							// you can use this instead if you are running php >= 4.2
							$moz_rv = floatval( $moz_version_number ); 
							$moz_rv_full = $moz_version_number;
							*/
						}
						// this corrects the version name in case it went to the default 'rv' for the test
						if ( $moz_version == 'rv' ) 
						{
							$moz_version = 'mozilla';
						}
						
						//the moz version will be taken from the rv number, see notes above for rv problems
						$version_number = $moz_rv;
						// gets the actual release date, necessary if you need to do functionality tests
						$moz_release = browser_version( $browser_user_agent, 'gecko/' );
						/* 
						Test for mozilla 0.9.x / netscape 6.x
						test your javascript/CSS to see if it works in these mozilla releases, if it does, just default it to:
						$safe_browser = true;
						*/
						if ( ( $moz_release < 20020400 ) || ( $moz_rv < 1 ) )
						{
							$safe_browser = false;
						}
						break;
					case 'ie':
						$version_number = browser_version( $browser_user_agent, $s_browser );
						// first test for IE 5x mac, that's the most problematic IE out there
						if ( stristr( $browser_user_agent, 'mac') )
						{
							$ie_version = 'ieMac';
						}
						// this assigns a general ie id to the $ie_version variable
						elseif ( $version_number >= 5 )
						{
							$ie_version = 'ie5x';
						}
						elseif ( ( $version_number > 3 ) && ( $version_number < 5 ) )
						{
							$dom_browser = false;
							$ie_version = 'ie4';
							// this depends on what you're using the script for, make sure this fits your needs
							$safe_browser = true; 
						}
						else
						{
							$ie_version = 'old';
							$dom_browser = false;
							$safe_browser = false; 
						}
						break;
					case 'op':
						$version_number = browser_version( $browser_user_agent, $s_browser );
						if ( $version_number < 5 )// opera 4 wasn't very useable.
						{
							$safe_browser = false; 
						}
						break;
					case 'saf':
						$version_number = browser_version( $browser_user_agent, $s_browser );
						break;
					/* 
						Uncomment this section if you want omniweb to return the safari value
						Omniweb uses khtml/safari rendering engine, so you can treat it like
						safari if you want.
					*/
					
					default:
						$version_number = browser_version( $browser_user_agent, $s_browser );
						break;
				}
				// the browser was id'ed
				$b_success = true;
				break;
			}
		}
		
		//assigns defaults if the browser was not found in the loop test
		if ( !$b_success ) 
		{
			/*
				this will return the first part of the browser string if the above id's failed
				usually the first part of the browser string has the navigator useragent name/version in it.
				This will usually correctly id the browser and the browser number if it didn't get
				caught by the above routine.
				If you want a '' to do a if browser == '' type test, just comment out all lines below
				except for the last line, and uncomment the last line. If you want undefined values, 
				the browser_name is '', you can always test for that
			*/
			// delete this part if you want an unknown browser returned
			$s_browser = substr( $browser_user_agent, 0, strcspn( $browser_user_agent , '();') );
			// this extracts just the browser name from the string
			ereg('[^0-9][a-z]*-*\ *[a-z]*\ *[a-z]*', $s_browser, $r );
			$s_browser = $r[0];
			$version_number = browser_version( $browser_user_agent, $s_browser );

			// then uncomment this part
			//$s_browser = '';//deletes the last array item in case the browser was not a match
		}
		// get os data, mac os x test requires browser/version information, this is a change from older scripts
		$a_os_data = which_os( $browser_user_agent, $browser_name, $version_number );
		$os = $a_os_data[0];// os name, abbreviated
		$os_number = $a_os_data[1];// os number or version if available

		// this ends the run through once if clause, set the boolean 
		//to true so the function won't retest everything
		$b_repeat = true;

		// pulls out primary version number from more complex string, like 7.5a, 
		// use this for numeric version comparison
		$m = array();
		if ( ereg('[0-9]*\.*[0-9]*', $version_number, $m ) )
		{
			$math_version_number = $m[0]; 
			
		}
		
	}
	//$version_number = $_SERVER["REMOTE_ADDR"];
	/*
	This is where you return values based on what parameter you used to call the function
	$which_test is the passed parameter in the initial browser_detection('os') for example call
	*/
	switch ( $which_test )
	{
		case 'safe':// returns true/false if your tests determine it's a safe browser
			// you can change the tests to determine what is a safeBrowser for your scripts
			// in this case sub rv 1 Mozillas and Netscape 4x's trigger the unsafe condition
			return $safe_browser; 
			break;
		case 'ie_version': // returns ieMac or ie5x
			return $ie_version;
			break;
		case 'moz_version':// returns array of all relevant moz information
			$moz_array = array( $moz_version, $moz_version_number, $moz_rv, $moz_rv_full, $moz_release );
			return $moz_array;
			break;
		case 'dom':// returns true/fale if a DOM capable browser
			return $dom_browser;
			break;
		case 'os':// returns os name
			return $os; 
			break;
		case 'os_number':// returns os number if windows
			return $os_number;
			break;
		case 'browser':// returns browser name
			return $browser_name; 
			break;
		case 'number':// returns browser number
			return $version_number;
			break;
		case 'full':// returns all relevant browser information in an array
			$full_array = array( $browser_name, $version_number, $ie_version, $dom_browser, $safe_browser, 
				$os, $os_number, $s_browser, $type, $math_version_number );
			return $full_array;
			break;
		case 'type':// returns what type, bot, browser, maybe downloader in future
			return $type;
			break;
		case 'math_number':// returns numerical version number, for number comparisons
			return $math_version_number;
			break;
		default:
			break;
	}
}

// gets which os from the browser string
function which_os ( $browser_string, $browser_name, $version_number  )
{
	// initialize variables
	$os = '';
	$os_version = '';
	/*
	packs the os array
	use this order since some navigator user agents will put 'macintosh' in the navigator user agent string
	which would make the nt test register true
	*/
	$a_mac = array( 'mac68k', 'macppc' );// this is not used currently
	// same logic, check in order to catch the os's in order, last is always default item
	$a_unix = array( 'freebsd', 'openbsd', 'netbsd', 'bsd', 'unixware', 'solaris', 'sunos', 'sun4', 'sun5', 'suni86', 'sun', 'irix5', 'irix6', 'irix', 'hpux9', 'hpux10', 'hpux11', 'hpux', 'hp-ux', 'aix1', 'aix2', 'aix3', 'aix4', 'aix5', 'aix', 'sco', 'unixware', 'mpras', 'reliant', 'dec', 'sinix', 'unix' );
	// only sometimes will you get a linux distro to id itself...
	$a_linux = array( 'ubuntu', 'kubuntu', 'xubuntu', 'mepis', 'xandros', 'linspire', 'sidux', 'kanotix', 'debian', 'opensuse', 'suse', 'fedora', 'redhat', 'slackware', 'slax', 'mandrake', 'mandriva', 'gentoo', 'sabayon', 'linux' );
	$a_linux_process = array ( 'i386', 'i586', 'i686' );// not use currently
	// note, order of os very important in os array, you will get failed ids if changed
	$a_os = array( 'beos', 'os2', 'amiga', 'webtv', 'mac', 'nt', 'win', $a_unix, $a_linux );

	//os tester
	$i_count = count( $a_os );
	for ( $i = 0; $i < $i_count; $i++ )
	{
		//unpacks os array, assigns to variable
		$s_os = $a_os[$i];

		// assign os to global os variable, os flag true on success
		// !stristr($browser_string, "linux" ) corrects a linux detection bug
		if ( !is_array( $s_os ) && stristr( $browser_string, $s_os ) && !stristr( $browser_string, "linux" ) )
		{
			$os = $s_os;

			switch ( $os )
			{
				case 'win':
					if ( strstr( $browser_string, '95' ) )
					{
						$os_version = '95';
					}
					elseif ( ( strstr( $browser_string, '9x 4.9' ) ) || ( strstr( $browser_string, 'me' ) ) )
					{
						$os_version = 'me';
					}
					elseif ( strstr( $browser_string, '98' ) )
					{
						$os_version = '98';
					}
					elseif ( strstr( $browser_string, '2000' ) )// windows 2000, for opera ID
					{
						$os_version = 5.0;
						$os = 'nt';
					}
					elseif ( strstr( $browser_string, 'xp' ) )// windows 2000, for opera ID
					{
						$os_version = 5.1;
						$os = 'nt';
					}
					elseif ( strstr( $browser_string, '2003' ) )// windows server 2003, for opera ID
					{
						$os_version = 5.2;
						$os = 'nt';
					}
					elseif ( strstr( $browser_string, 'vista' ) )// windows vista, for opera ID
					{
						$os_version = 6.0;
						$os = 'nt';
					}
					elseif ( strstr( $browser_string, 'ce' ) )// windows CE
					{
						$os_version = 'ce';
					}
					break;
				case 'nt':
					if ( strstr( $browser_string, 'nt 6.1' ) )// windows 7
					{
						$os_version = 6.1;
						$os = 'nt';
					}
					elseif ( strstr( $browser_string, 'nt 6.0' ) )// windows vista/server 2008
					{
						$os_version = 6.0;
						$os = 'nt';
					}
					elseif ( strstr( $browser_string, 'nt 5.2' ) )// windows server 2003
					{
						$os_version = 5.2;
						$os = 'nt';
					}
					elseif ( strstr( $browser_string, 'nt 5.1' ) || strstr( $browser_string, 'xp' ) )// windows xp
					{
						$os_version = 5.1;//
					}
					elseif ( strstr( $browser_string, 'nt 5' ) || strstr( $browser_string, '2000' ) )// windows 2000
					{
						$os_version = 5.0;
					}
					elseif ( strstr( $browser_string, 'nt 4' ) )// nt 4
					{
						$os_version = 4;
					}
					elseif ( strstr( $browser_string, 'nt 3' ) )// nt 4
					{
						$os_version = 3;
					}
					break;
				case 'mac':
					if ( strstr( $browser_string, 'os x' ) ) 
					{
						$os_version = 10;
					}
					//this is a crude test for os x, since safari, camino, ie 5.2, & moz >= rv 1.3 
					//are only made for os x
					elseif ( ( $browser_name == 'saf' ) || ( $browser_name == 'cam' ) || 
						( ( $browser_name == 'moz' ) && ( $version_number >= 1.3 ) ) || 
						( ( $browser_name == 'ie' ) && ( $version_number >= 5.2 ) ) )
					{
						$os_version = 10;
					}
					break;
				default:
					break;
			}
			break;
		}
		// check that it's an array, check it's the second to last item 
		//in the main os array, the unix one that is
		elseif ( is_array( $s_os ) && ( $i == ( count( $a_os ) - 2 ) ) )
		{
			$i_count = count($s_os);
			for ($j = 0; $j < $i_count; $j++)
			{
				if ( stristr( $browser_string, $s_os[$j] ) )
				{
					$os = 'unix'; //if the os is in the unix array, it's unix, obviously...
					$os_version = ( $s_os[$j] != 'unix' ) ? $s_os[$j] : '';// assign sub unix version from the unix array
					break;
				}
			}
		} 
		// check that it's an array, check it's the last item 
		//in the main os array, the linux one that is
		elseif ( is_array( $s_os ) && ( $i == ( count( $a_os ) - 1 ) ) )
		{
			$i_count = count($s_os);
			for ($j = 0; $j < $i_count; $j++)
			{
				if ( stristr( $browser_string, $s_os[$j] ) )
				{
					$os = 'lin';
					// assign linux distro from the linux array, there's a default
					//search for 'lin', if it's that, set version to ''
					$os_version = ( $s_os[$j] != 'linux' ) ? $s_os[$j] : '';
					break;
				}
			}
		} 
	}

	// pack the os data array for return to main function
	$os_data = array( $os, $os_version );
	return $os_data;
}

// function returns browser number, gecko rv number, or gecko release date
//function browser_version( $browser_user_agent, $search_string, $substring_length )
function browser_version( $browser_user_agent, $search_string )
{
	// 12 is the longest that will be required, handles release dates: 20020323; 0.8.0+
	$substring_length = 12;
	//initialize browser number, will return '' if not found
	$browser_number = '';

	// use the passed parameter for $search_string
	// start the substring slice right after these moz search strings
	// there are some cases of double msie id's, first in string and then with then number
	$start_pos = 0;  
	/* this test covers you for multiple occurrences of string, only with ie though
	 with for example google bot you want the first occurance returned, since that's where the
	numbering happens */
	for ( $i = 0; $i < 4; $i++ )
	{
		//start the search after the first string occurrence
		if ( strpos( $browser_user_agent, $search_string, $start_pos ) !== false )
		{
			//update start position if position found
			$start_pos = strpos( $browser_user_agent, $search_string, $start_pos ) + strlen( $search_string );
			if ( $search_string != 'msie' )
			{
				break;
			}
		}
		else 
		{
			break;
		}
	}

	// this is just to get the release date, not other moz information
	// also corrects for the omniweb 'v'
	if ( $search_string != 'gecko/' ) 
	{ 
		if ( $search_string == 'omniweb' )
		{
			$start_pos += 2;// handles the v in 'omniweb/v532.xx
		}
		else
		{
			$start_pos++; 
		}
	} 

	// Initial trimming
	$browser_number = substr( $browser_user_agent, $start_pos, $substring_length );

	// Find the space, ;, or parentheses that ends the number
	$browser_number = substr( $browser_number, 0, strcspn($browser_number, ' );') );

	//make sure the returned value is actually the id number and not a string
	// otherwise return ''
	if ( !is_numeric( substr( $browser_number, 0, 1 ) ) )
	{ 
		$browser_number = ''; 
	}
	
	return $browser_number;
}

/* 
Here are some typical navigator.userAgent strings so you can see where the data comes from
Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.5) Gecko/20031007 Firebird/0.7 
Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:0.9.4) Gecko/20011128 Netscape6/6.2.1
*/
?>