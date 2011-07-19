<?php

/**
 * This plugin displays an icon showing the status 
 * of dkim verification of the message 
 *
 * @version 0.4.3
 * @author Julien vehent 
 * @mail julien@linuxwall.info
 * 
 * original plugin from Vladimir Mach - wladik@gmail.com
 * http://www.wladik.net
 *
 * Changelog:
 *      20100811 - from Sergio Cambra: SPF fix, image function and spanish translation
 *	20100202 - fix for amavis and add cz translation
 *	20100201 - add control of header.i and header.from to detect third party signature, change icons
 *	20100115 - add 'no information' status with image using x-dkim-authentication-results
 *	20090920 - fixed space in matching status (thanks Pim Pronk for suggestion)
 */
class dkimstatus extends rcube_plugin
{
	public $task = 'mail';
	function init()
	{
		$rcmail = rcmail::get_instance();
		if ($rcmail->action == 'show' || $rcmail->action == 'preview') {
			$this->add_hook('imap_init', array($this, 'imap_init'));
			$this->add_hook('message_headers_output', array($this, 'message_headers'));
		} else if ($rcmail->action == '') {
			// with enabled_caching we're fetching additional headers before show/preview
			$this->add_hook('imap_init', array($this, 'imap_init'));
		}
	}
	
	function imap_init($p)
	{
		$rcmail = rcmail::get_instance();
		$p['fetch_headers'] = trim($p['fetch_headers'].' ' . strtoupper('Authentication-Results').' '. strtoupper('X-DKIM-Authentication-Results'));
		return $p;
	}

	function image($image, $alt, $title)
	{
		return '<img src="plugins/dkimstatus/images/'.$image.'" alt="'.$this->gettext($alt).'" title="'.$this->gettext($alt).$title.'" /> ';
	}
	
	function message_headers($p)
	{
		$this->add_texts('localization');

		/* First, if dkimproxy did not find a signature, stop here
		*/
		if($p['headers']->others['x-dkim-authentication-results'] || $p['headers']->others['authentication-results']){

			$results = $p['headers']->others['x-dkim-authentication-results'];

			if(preg_match("/none/", $results)) {
				$image = 'nosiginfo.png';
				$alt = 'nosignature';
			} else {
				/* Second, check the authentication-results header
				*/
				if($p['headers']->others['authentication-results']) {

					$results = $p['headers']->others['authentication-results'];

					if(preg_match("/dkim=([a-zA-Z0-9]*)/", $results, $m)) {
						$status = ($m[1]);
					}

					if(preg_match("/domainkeys=([a-zA-Z0-9]*)/", $results, $m)) {
						$status = ($m[1]);
					}


					if($status == 'pass') {

						/* Verify if its an author's domain signature or a third party
						*/

						if(preg_match("/[@][a-zA-Z0-9]+([.][a-zA-Z0-9]+)?\.[a-zA-Z]{2,4}/", $p['headers']->from, $m)) {
							$authordomain = $m[0];
							if(preg_match("/header\.i=(([a-zA-Z0-9]+[_\.\-]?)+)?($authordomain)/", $results) ||
								preg_match("/header\.from=(([a-zA-Z0-9]+[_\.\-]?)+)?($authordomain)/", $results)) {
								$image = 'authorsign.png';
								$alt = 'verifiedsender';
								$title = $results;
							} else {
								$image = 'thirdpty.png';
								$alt = 'thirdpartysig';
								$title = $results;
							}
						}

					}
					/* If signature proves invalid, show appropriate warning
					*/ 
					else if ($status) {
						$image = 'invalidsig.png';
						$alt = 'invalidsignature';
						$title = $results;
					}
					/* If no status it can be a spf verification
					*/
					else {
						$image = 'nosiginfo.png';
						$alt = 'nosignature';
					}
				}
			}
		} else {
			$image = 'nosiginfo.png';
			$alt = 'nosignature';
		}
		if ($image && $alt) {
			$p['output']['from']['value'] = $this->image($image, $alt, $title) . $p['output']['from']['value'];
		}
		return $p;
	}
} 
