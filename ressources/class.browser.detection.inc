<?php 
function browser_detection( $which_test=null,$getIeVer=0 ) {
	
	static $dom_browser, $safe_browser, $browser_user_agent, $os, $browser_name, $s_browser, $ie_version, 
	$version_number, $os_number, $b_repeat, $moz_version, $moz_version_number, $moz_rv, $moz_rv_full, $moz_release, 
	$type, $math_version_number;

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
		if(strpos($browser_user_agent,'msie ')>0){
			if($getIeVer==1){
				if(preg_match('#msie\s+(\d+)#',$browser_user_agent,$regs))
				return $regs[1];
				
			}
			
			
		}
		
		
		
		
		if(strpos($browser_user_agent,'msie ')>0){return 'ie';}
		if(strpos($browser_user_agent,'firefox')>0){return 'fire';}
		if(preg_match('#opera#',$browser_user_agent)){return 'op';}
		
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
		$a_browser_types[] = array( 'w3c_validator', false, 'w3c', 'lib' ); // uses libperl, make first
		$a_browser_types[] = array( 'wdg_validator', false, 'wdg', 'lib' ); // 
		$a_browser_types[] = array( 'libwww-perl', false, 'libwww-perl', 'lib' ); 
		$a_browser_types[] = array( 'jakarta commons-httpclient', false, 'jakarta', 'lib' );
		$a_browser_types[] = array( 'python-urllib', false, 'python-urllib', 'lib' ); 
		$a_browser_types[] = array( 'getright', false, 'getright', 'dow' );
		$a_browser_types[] = array( 'wget', false, 'wget', 'dow' );// open source downloader, obeys robots.txt
		$a_browser_types[] = array( 'mozilla/4.', false, 'ns', 'bbro' );
		$a_browser_types[] = array( 'mozilla/3.', false, 'ns', 'bbro' );
		$a_browser_types[] = array( 'mozilla/2.', false, 'ns', 'bbro' );

		
		$moz_types = array( 'firebird', 'phoenix', 'firefox', 'iceweasel', 'galeon', 'k-meleon', 'camino', 'epiphany', 'netscape6', 'netscape', 'multizilla', 'rv' );

		for ($i = 0; $i < count($a_browser_types); $i++)
		{
		
			$s_browser = $a_browser_types[$i][0];// text string to id browser from array

			if (stristr($browser_user_agent, $s_browser)) 
			{
			
				$safe_browser = true;

			
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
						for ( $i = 0; $i < count( $moz_types ); $i++ )
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
							$ie_version = 'ie';
						}
						// this assigns a general ie id to the $ie_version variable
						elseif ( $version_number >= 5 )
						{
							$ie_version = 'ie';
						}
						elseif ( ( $version_number > 3 ) && ( $version_number < 5 ) )
						{
							$dom_browser = false;
							$ie_version = 'ie';
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
					/* 
					case 'omni':
						$s_browser = 'safari';
						$browser_name = 'saf';
						$version_number = browser_version( $browser_user_agent, 'applewebkit' );
						break; 
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
			preg_match('#[^0-9][a-z]*-*\ *[a-z]*\ *[a-z]*#', $s_browser, $r );
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
		if ( preg_match('#[0-9]*\.*[0-9]*#', $version_number, $m ) )
		{
			$math_version_number = $m[0]; 
			//print_r($m);
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
	$a_unix = array( 'unixware', 'solaris', 'sunos', 'sun4', 'sun5', 'suni86', 'sun', 
		'freebsd', 'openbsd', 'bsd' , 'irix5', 'irix6', 'irix', 'hpux9', 'hpux10', 'hpux11', 'hpux', 'hp-ux', 
		'aix1', 'aix2', 'aix3', 'aix4', 'aix5', 'aix', 'sco', 'unixware', 'mpras', 'reliant',
		'dec', 'sinix', 'unix' );
	// only sometimes will you get a linux distro to id itself...
	$a_linux = array( 'kanotix', 'ubuntu', 'mepis', 'debian', 'suse', 'redhat', 'slackware', 'mandrake', 'gentoo', 'linux' );
	$a_linux_process = array ( 'i386', 'i586', 'i686' );// not use currently
	// note, order of os very important in os array, you will get failed ids if changed
	$a_os = array( 'beos', 'os2', 'amiga', 'webtv', 'mac', 'nt', 'win', $a_unix, $a_linux );

	//os tester
	for ( $i = 0; $i < count( $a_os ); $i++ )
	{
		//unpacks os array, assigns to variable
		$s_os = $a_os[$i];

		//assign os to global os variable, os flag true on success
		//!stristr($browser_string, "linux" ) corrects a linux detection bug
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
					elseif ( strstr( $browser_string, 'ce' ) )// windows CE
					{
						$os_version = 'ce';
					}
					break;
				case 'nt':
					if ( strstr( $browser_string, 'nt 5.2' ) )// windows server 2003
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
			for ($j = 0; $j < count($s_os); $j++)
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
			for ($j = 0; $j < count($s_os); $j++)
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

	$os_data = array( $os, $os_version );
	return $os_data;
}

function browser_version( $browser_user_agent, $search_string )
{
	$substring_length = 12;
	$browser_number = '';
	$start_pos = 0;  

	
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
	//$browser_number = strrpos( $browser_user_agent, $search_string );
	return $browser_number;
}


?>