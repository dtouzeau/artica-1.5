<?php

require_once("mapi/mapi.util.php");
require_once("mapi/mapidefs.php");
require_once("mapi/mapitags.php");


class zarafa_contacts{
	var $extension = ".vcf";
	var $mime = "text/x-vcard";
	var $server = "http://localhost:236/zarafa";
	var $zarafa = false;

	var $specialprops = array(
			"fileas" 		=> "PT_STRING8:{00062004-0000-0000-C000-000000000046}:0x8005",
			"email1" 		=> "PT_STRING8:{00062004-0000-0000-C000-000000000046}:0x8083",
			"business_street"	=> "PT_STRING8:{00062004-0000-0000-C000-000000000046}:0x8045",
			"business_postcode"	=> "PT_STRING8:{00062004-0000-0000-C000-000000000046}:0x8048",
			"business_city"		=> "PT_STRING8:{00062004-0000-0000-C000-000000000046}:0x8046",
			"business_state"	=> "PT_STRING8:{00062004-0000-0000-C000-000000000046}:0x8047",
			"business_country"	=> "PT_STRING8:{00062004-0000-0000-C000-000000000046}:0x8049"
			);

	var $etag_salt = "pepper ;-)";


	function connect($type, $user, $pass) {
		$user = str_replace('%', '@', $user); // This fixes a logon problem with Evolution, login with user%domain instead of user@domain which seems to confuse evolution...
		$user = str_replace('$', '@', $user); // Mac OS X Address Book App fix
		$session = mapi_logon_zarafa($user, $pass, $this->server);
		if (false != $session) {
			if($GLOBALS["VERBOSE"]){echo "->open_zarafa()\n";}
			$this->zarafa = $this->open_zarafa($session);
			$this->zarafa['user'] = $user;
			$this->specialprops = getPropIdsFromStrings($this->zarafa["store"], $this->specialprops);

			return true;
		}
		return false;
	}
	
	
	


	/**
	 * PROPFIND method handler
	 *
	 * @param  array  general parameter passing array
	 * @param  array  return array for file properties
	 * @return bool   true on success
	 */
	function PROPFIND(&$options, &$files) 
	{

		$store = $this->zarafa["store"];
		$contacts = $this->zarafa["contacts"];

		$files["files"] = array();
		if ($options["path"] == "/") {
			$global_etag = "";

			$rootOnly = !(($options["depth"] == "infinity") || ($options["depth"] > 0));
			foreach ($contacts as $contact) {
				$message = mapi_msgstore_openentry($store, $contact[PR_ENTRYID]);
				$props = mapi_getprops($message);
				if (!$rootOnly) // If the requested depth was 0, we only compute the global etag for the root
					$files["files"][] = $this->contactinfo($props);
				$etag = $this->get_etag($props);
				$global_etag = $global_etag.$etag;
			}
			$global_etag = sha1($global_etag);

			$info["path"] = $this->zarafa["root"];
			$info["props"] = array();
			$info["props"][] = $this->mkprop("resourcetype", "<collection/><vcard-collection xmlns=\"http://groupdav.org/\"/>");
			$info["props"][] = $this->mkprop("displayname", "ROOT");
			$info["props"][] = $this->mkprop("getetag", '"'.$global_etag.'"');
			$info["props"][] = $this->mkprop("http://calendarserver.org/ns/", "getctag", $global_etag);
			$info["props"][] = $this->mkprop("supported-report-set", 
					"<supported-report><report><sync-collection/></report></supported-report>".
					"<supported-report><report><expand-property/></report></supported-report>"); // We don't actually support this, do we...?

			$files["files"][]  = $info;
		} else {
			$contactprops = $this->get_contact($options["path"]);
			if ($contactprops) {
				$info = $this->contactinfo($contactprops);
				$files["files"][] = $info;
				$global_etag = $this->get_etag($contactprops);
			}
		}
		header("ETAG: \"".$global_etag."\"");

		return true;
	} 

	function REPORT(&$options, &$files)
	{
		$store = $this->zarafa["store"];
		$contacts = $this->zarafa["contacts"];

		if ($options['sync-token'] != '') {
			/**
			 * We don't keep the sync states for each client, thus we enforce a full resync
			 * even if an older state (known by the client) is specified in the request.
			 */
			$options['sync-token-refresh'] = true;
			return false;
		}

		$files["files"] = array();
		if ($options["path"] == "/") {
			$global_etag = "";

			foreach ($contacts as $contact) {
				$message = mapi_msgstore_openentry($store, $contact[PR_ENTRYID]);
				$props = mapi_getprops($message);
				$files["files"][] = $this->contactinfo($props);
				$etag = $this->get_etag($props);
				$global_etag = $global_etag.$etag;
			}

			$global_etag = sha1($global_etag);
		} else {
			$options['path-not-supported'] = true;
			return false;
		}

		$options['sync-token'] = $global_etag;

		header("ETAG: \"".$global_etag."\"");

		return true;

	}


	/**
	 * get_contact
	 *
	 * @param	string	Path to the contact's vcf file.
	 * @return	array	Array of contact properties or false if not found.
	 */
	function get_contact($path) {
		foreach ($this->zarafa["contacts"] as $contact) {
			$message = mapi_msgstore_openentry($this->zarafa["store"], $contact[PR_ENTRYID]);
			$props = mapi_getprops($message);
			if ($this->zarafa["root"].sha1($props[PR_DISPLAY_NAME]).$this->extension == $path) {
				return $props;
			}
		}
		return false;
	}

	/**
	 * open_zarafa
	 *
	 * @param	descriptor	An open Zarafa session id.
	 * @return	array		Array with session and store information.
	 */
	function open_zarafa($session) {
		$ret = array("root" => "/");

		$storesTable = mapi_getmsgstorestable($session);
		$stores = mapi_table_queryallrows($storesTable, array(PR_ENTRYID, PR_MDB_PROVIDER));
		for($i=0;$i<count($stores); $i++){
			if ($stores[$i][PR_MDB_PROVIDER] == ZARAFA_SERVICE_GUID){
				$storeEntryid = $stores[$i][PR_ENTRYID];
				break;
			}
		}

		if (!isset($storeEntryid))
			trigger_error("Default store not found", PR_USER_ERROR);


		$store = mapi_openmsgstore($session, $storeEntryid);
		$root = mapi_msgstore_openentry($store, null);
		$rootProps = mapi_getprops($root, array(PR_IPM_CONTACT_ENTRYID));

		$folder = mapi_msgstore_openentry($store, $rootProps[PR_IPM_CONTACT_ENTRYID]);

		$table = mapi_folder_getcontentstable($folder);

		$contacts = mapi_table_queryallrows($table);

		$ret["session"] = $session;
		$ret["store"] = $store;
		$ret["contacts"] = $contacts;
		return $ret;
	}

	/**
	 * build_vcard
	 *
	 * @param	array	Contact properties.
	 * @return	object	VCard object.
	 */
	function build_vcard($contactprops) {
		$vcard = File_IMC::build('vCard');
		$vcard->setVersion('2.1');
		$charset = 'UTF-8';

		//// GENERAL INFORMATION
		$vcard->setName(
				$this->toUTF8($contactprops[PR_SURNAME]), 
				$this->toUTF8($contactprops[PR_GIVEN_NAME]), 
				$this->toUTF8($contactprops[PR_MIDDLE_NAME]), 
				$this->toUTF8($contactprops[PR_DISPLAY_NAME_PREFIX]), 
				'' // Suffix 
			       ); 
		$vcard->addParam('CHARSET',$charset);
		$vcard->setFormattedName($this->toUTF8($contactprops[PR_DISPLAY_NAME])); 
		$vcard->addParam('CHARSET',$charset);

		$v = $contactprops[PR_BIRTHDAY			]; if ($v)  $vcard->setBirthday		($this->toUTF8(date('Y-m-d', $v)));
		$v = $contactprops[PR_PROFESSION		]; if ($v)  $vcard->setRole		($this->toUTF8($v));
		$v = $contactprops[PR_NICKNAME			]; if ($v)  $vcard->addNickname		($this->toUTF8($v));
		$v = $contactprops[PR_COMPANY_NAME		]; if ($v)  $vcard->addOrganization	($this->toUTF8($v)); //$this->toUTF8($contactprops[PR_DEPARTMENT_NAME] // how to append this as outlook does and prevent escaping of ';' ??
		$v = $contactprops[PR_TITLE			]; if ($v)  $vcard->setTitle		($this->toUTF8($v));
		$v = $contactprops[$this->specialprops["email1"]]; if ($v)  $vcard->addEmail		($this->toUTF8($v));
		$v = $contactprops[PR_BUSINESS_HOME_PAGE	]; if ($v){ $vcard->setUrl		($this->toUTF8($v));	$vcard->addParam('TYPE','WORK'); }
		$v = $contactprops[PR_COMMENT			]; if ($v)  $vcard->setNote		($this->toUTF8($v));

		//// HOME ADDRESS
		$v1 = $contactprops[PR_HOME_ADDRESS_POST_OFFICE_BOX	];
		$v2 = $contactprops[PR_HOME_ADDRESS_STREET		];
		$v3 = $contactprops[PR_HOME_ADDRESS_CITY		];
		$v4 = $contactprops[PR_HOME_ADDRESS_STATE_OR_PROVINCE	];
		$v5 = $contactprops[PR_HOME_ADDRESS_POSTAL_CODE		];
		$v6 = $contactprops[PR_HOME_ADDRESS_COUNTRY		];
		if ($v1 || $v2 || $v3 || $v4 || $v5 || $v6) {
			$vcard->addAddress(
					$this->toUTF8($v1), 
					'', // extended address
					$this->toUTF8($v2), 
					$this->toUTF8($v3), 
					$this->toUTF8($v4), 
					$this->toUTF8($v5), 
					$this->toUTF8($v6)); 
			$vcard->addParam('TYPE', 'HOME');
		}

		//// WORK ADDRESS 
		$v1 = ''; // business post office pox (where to get this from?)
		$v2 = $contactprops[$this->specialprops["business_street"]	];
		$v3 = $contactprops[$this->specialprops["business_city"]	]; 
		$v4 = $contactprops[$this->specialprops["business_state"]	]; 
		$v5 = $contactprops[$this->specialprops["business_postcode"]	]; 
		$v6 = $contactprops[$this->specialprops["business_country"]	];
		if ($v1 || $v2 || $v3 || $v4 || $v5 || $v6) {
			$vcard->addAddress(
					$this->toUTF8($v1), 
					'', // extended address
					$this->toUTF8($v2), 
					$this->toUTF8($v3), 
					$this->toUTF8($v4), 
					$this->toUTF8($v5), 
					$this->toUTF8($v6)); 
			$vcard->addParam('TYPE', 'WORK');
		}

		//// PHONE NUMBERS
		$v = $contactprops[PR_HOME_TELEPHONE_NUMBER	]; if ($v){ $vcard->addTelephone	($this->toUTF8($v));		$vcard->addParam('TYPE', 'HOME');					}
		$v = $contactprops[PR_BUSINESS_TELEPHONE_NUMBER	]; if ($v){ $vcard->addTelephone	($this->toUTF8($v));		$vcard->addParam('TYPE', 'WORK');					}
		$v = $contactprops[PR_HOME2_TELEPHONE_NUMBER	]; if ($v){ $vcard->addTelephone	($this->toUTF8($v));		$vcard->addParam('TYPE', 'HOME');					}
		$v = $contactprops[PR_BUSINESS2_TELEPHONE_NUMBER]; if ($v){ $vcard->addTelephone	($this->toUTF8($v));		$vcard->addParam('TYPE', 'WORK');					}
		$v = $contactprops[PR_MOBILE_TELEPHONE_NUMBER	]; if ($v){ $vcard->addTelephone	($this->toUTF8($v));		$vcard->addParam('TYPE', 'CELL');					}
		$v = $contactprops[PR_HOME_FAX_NUMBER		]; if ($v){ $vcard->addTelephone	($this->toUTF8($v));		$vcard->addParam('TYPE', 'HOME');	$vcard->addParam('TYPE','FAX');	}
		$v = $contactprops[PR_BUSINESS_FAX_NUMBER	]; if ($v){ $vcard->addTelephone	($this->toUTF8($v));		$vcard->addParam('TYPE', 'WORK');	$vcard->addParam('TYPE','FAX');	}
		$v = $contactprops[PR_OTHER_TELEPHONE_NUMBER	]; if ($v){ $vcard->addTelephone	($this->toUTF8($v));											}

		return $vcard;
	}

	/**
	 * toUTF8
	 *
	 * @param	string	A string to encode.
	 * @return	string	Encoded string.
	 */
	function toUTF8($str) {
		return utf8_encode($str);
	}

	/**
	 * Get properties for a single file/resource
	 *
	 * @param  string  resource path
	 * @return array   resource properties
	 */
	function contactinfo($contactprops) 
	{
		// create result array
		$info = array();
		$name = $contactprops[PR_DISPLAY_NAME];
		$info["path"] = $this->zarafa["root"].sha1($name).$this->extension;
		$info["props"] = array();

		// no special beautified displayname here ...
		$info["props"][] = $this->mkprop("displayname", $this->toUTF8($name));

		$info["props"][] = $this->mkprop("resourcetype", "");
		$info["props"][] = $this->mkprop("getcontenttype", $this->mime);
		$etag = $this->get_etag($contactprops);
		$info["props"][] = $this->mkprop("getetag", '"'.$etag.'"');
		$info["status"] = "HTTP/1.1 201 Created";

		return $info;
	}

	/**
	 * get_etag
	 *
	 * @param	array	Contact properties.
	 * @return	string	Unique Etag for contact object.
	 */
	function get_etag($props) {
		return sha1($this->etag_salt.$props[PR_LAST_MODIFICATION_TIME].$props[PR_DISPLAY_NAME]);
	}

	/**
	 * HEAD method handler
	 * 
	 * @param  array  parameter passing array
	 * @return bool   true on success
	 */
	function HEAD(&$options) 
	{
		return false;
	}

	/**
	 * GET method handler
	 * 
	 * @param  array  parameter passing array
	 * @return bool   true on success
	 */
	function GET(&$options) 
	{
		if ($options["path"] == "/") {
			$store = $this->zarafa["store"];
			$contacts = $this->zarafa["contacts"];
			$files["files"] = array();
			$global_etag = "";

			$html_content = "<html><head><title>Zarafa Contacts for ".$this->zarafa['user']."</title></head><body><br/>";
			foreach ($contacts as $contact) {
				$message = mapi_msgstore_openentry($store, $contact[PR_ENTRYID]);
				$props = mapi_getprops($message);
				$name = $props[PR_DISPLAY_NAME];
				$html_content .= "<a href=\"".sha1($name).$this->extension."\">".$name."</a><br/>\n";
			}
			$html_content .= "</body></html>";

			$options['mimetype'] = "text/html";
			$options['data'] = $html_content;
			return true;
		}

		$contactprops = $this->get_contact($options["path"]);

		if ($contactprops === false) 
			return false;

		$options['mimetype'] = $this->mime;
		$vcard = $this->build_vcard($contactprops);
		$vcard = $vcard->fetch();
		$etag = $this->get_etag($contactprops);
		$options['data'] = $vcard;
		header("ETAG: \"".$etag."\"");
		return true;
	}
	
	
	function ParseContacts(){
		$store = $this->zarafa["store"];
		$contacts = $this->zarafa["contacts"];
		$maps=$this->csvMapping();
		$properties=$this->loadProperties();
		
		foreach ($contacts as $contact) {
		$array=array();
		$message = mapi_msgstore_openentry($store, $contact[PR_ENTRYID]);
		$props = mapi_getprops($message);
		if(isset($props[-2129526754])){$array["IM1"]= $props[-2129526754];}
		if(isset($props[-2126315490])){$array["email2"]= $props[-2126315490];}
		if(isset($props[-2126249954])){$array["email2"]= $props[-2126249954];}
		if(isset($props[-2125266914])){$array["email3"]= $props[-2125266914];}
		
		reset($properties);
		
		
		while (list ($num, $ligne) = each ($properties) ){
			if(isset($props[$ligne])){$array[$num]=$props[$ligne];}
			$array["business_street"] = $this->toUTF8($props[$this->specialprops["business_street"]	]);
			$array["business_city"] =  $this->toUTF8($props[$this->specialprops["business_city"]	]); 
			$array["business_state"] =  $this->toUTF8($props[$this->specialprops["business_state"]	]); 
			$array["business_postcode"]= $this->toUTF8($props[$this->specialprops["business_postcode"]	]); 
			$array["business_country"] = $this->toUTF8($props[$this->specialprops["business_country"]	]);
			$array["fileas"] = $props[$this->specialprops["fileas"]];
			$array["email1"] = $this->toUTF8($props[$this->specialprops["email1"]]);		
			
		}
		
		$newcontacts[]=$array;

		}
		
		return $newcontacts;
		
	}
	
	function csvMapping(){
$csv_mapping = array(
                        "given_name" 					=> 0,
                        "middle_name"					=> 1,
                        "surname"						=> 2,
                        "display_name_prefix"			=> 3, //title
                        "webpage"						=> 6,
                        "birthday"						=> 8,
                        "wedding_anniversary"			=> 9,
                        "notes"							=> 13,
                        "email_address_1"				=> 14, //email address only
                        "email_address_2"				=> 15,
                        "email_address_3"				=> 16,
                        "home_telephone_number"			=> 18,
                        "home2_telephone_number"		=> 19,
                        "cellular_telephone_number"		=> 20,
                        "pager_telephone_number"		=> 21,
                        "home_fax_number"				=> 22,
                        "home_address"					=> 23,
                        "home_address_street"			=> 24,
                        "home_address_street2"			=> 25,
                        "home_address_street3"			=> 26,
                        "home_address_pobox"			=> 27,
                        "home_address_city"				=> 28,
                        "home_address_state"			=> 29,
                        "home_address_postal_code"		=> 30,
                        "home_address_country"			=> 31,
                        "spouse_name"					=> 32,
                        "manager_name"					=> 34,
                        "assistant"						=> 35,
                        "company_telephone_number"		=> 37,
                        "office_telephone_number"		=> 38,
                        "business2_telephone_number"	=> 39,
                        "business_fax_number"			=> 40,
                        "assistant_telephone_number"	=> 41,
                        "company_name"					=> 42,
                        "job_title"						=> 43,
                        "department_name"				=> 44,
                        "office_location"		    	=> 45,
                        "profession"					=> 47,
                        "business_address"				=> 49,
                        "business_address_street"		=> 50,
                        "business_address_street2"		=> 51,
                        "business_address_street3"		=> 52,
                        "business_address_pobox"		=> 53,
                        "business_address_city"			=> 54,
                        "business_address_state"		=> 55,
                        "business_address_postal_code"	=> 56,
                        "business_address_country"		=> 57,
                        "other_telephone_number"		=> 58,
                        "other_address"					=> 60,
                        "other_address_street"			=> 61,
                        "other_address_street2"			=> 62,
                        "other_address_street3"			=> 63,
                        "other_address_pobox"			=> 64,
                        "other_address_city"			=> 65,
                        "other_address_state"			=> 66,
                        "other_address_postal_code"		=> 67,
                        "other_address_country"			=> 68,
                        "callback_telephone_number"		=> 69,
                        "car_telephone_number"			=> 70,
                        "isdn_number"					=> 71,
                        "radio_telephone_number"		=> 72,
                        "ttytdd_telephone_number"		=> 73,
                        "telex_telephone_number"		=> 74,
                        "sensitivity"					=> 84,
                        "categories"					=> 87, //semicolon separated string
				);	

				return $csv_mapping;
		
	}
	
function loadProperties() {
	$properties["subject"] = PR_SUBJECT;
	$properties["icon_index"] = PR_ICON_INDEX;
	$properties["message_class"] = PR_MESSAGE_CLASS;
	$properties["display_name"] = PR_DISPLAY_NAME;
	$properties["given_name"] = PR_GIVEN_NAME;
	$properties["middle_name"] = PR_MIDDLE_NAME;
	$properties["surname"] = PR_SURNAME;
	$properties["home_telephone_number"] = PR_HOME_TELEPHONE_NUMBER;
	$properties["cellular_telephone_number"] = PR_CELLULAR_TELEPHONE_NUMBER;
	$properties["office_telephone_number"] = PR_OFFICE_TELEPHONE_NUMBER;
	$properties["business_fax_number"] = PR_BUSINESS_FAX_NUMBER;
	$properties["company_name"] = PR_COMPANY_NAME;
	$properties["title"] = PR_TITLE;
	$properties["department_name"] = PR_DEPARTMENT_NAME;
	$properties["office_location"] = PR_OFFICE_LOCATION;
	$properties["profession"] = PR_PROFESSION;
	$properties["manager_name"] = PR_MANAGER_NAME;
	$properties["assistant"] = PR_ASSISTANT;
	$properties["nickname"] = PR_NICKNAME;
	$properties["display_name_prefix"] = PR_DISPLAY_NAME_PREFIX;
	$properties["spouse_name"] = PR_SPOUSE_NAME;
	$properties["generation"] = PR_GENERATION;
	$properties["birthday"] = PR_BIRTHDAY;
	$properties["wedding_anniversary"] = PR_WEDDING_ANNIVERSARY;
	$properties["sensitivity"] = PR_SENSITIVITY;
	$properties["fileas"] = "PT_STRING8:PSETID_Address:0x8005";
	$properties["fileas_selection"] = "PT_LONG:PSETID_Address:0x8006";
	$properties["email_address_1"] = "PT_STRING8:PSETID_Address:0x8083";
	$properties["email_address_display_name_1"] = "PT_STRING8:PSETID_Address:0x8080";
	$properties["email_address_display_name_email_1"] = "PT_STRING8:PSETID_Address:0x8084";
	$properties["email_address_type_1"] = "PT_STRING8:PSETID_Address:0x8082";
	$properties["email_address_2"] = "PT_STRING8:PSETID_Address:0x8093";
	$properties["email_address_display_name_2"] = "PT_STRING8:PSETID_Address:0x8090";
	$properties["email_address_display_name_email_2"] = "PT_STRING8:PSETID_Address:0x8094";
	$properties["email_address_type_2"] = "PT_STRING8:PSETID_Address:0x8092";
	$properties["email_address_3"] = "PT_STRING8:PSETID_Address:0x80a3";
	$properties["email_address_display_name_3"] = "PT_STRING8:PSETID_Address:0x80a0";
	$properties["email_address_display_name_email_3"] = "PT_STRING8:PSETID_Address:0x80a4";
	$properties["email_address_type_3"] = "PT_STRING8:PSETID_Address:0x80a2";
	$properties["home_address"] = "PT_STRING8:PSETID_Address:0x801a";
	$properties["business_address"] = "PT_STRING8:PSETID_Address:0x801b";
	$properties["other_address"] = "PT_STRING8:PSETID_Address:0x801c";
	$properties["mailing_address"] = "PT_LONG:PSETID_Address:0x8022";
	$properties["im"] = "PT_STRING8:PSETID_Address:0x8062";
	$properties["webpage"] = "PT_STRING8:PSETID_Address:0x802b";
	$properties["business_home_page"] = PR_BUSINESS_HOME_PAGE;
	$properties["email_address_entryid_1"] = "PT_BINARY:PSETID_Address:0x8085";
	$properties["email_address_entryid_2"] = "PT_BINARY:PSETID_Address:0x8095";
	$properties["email_address_entryid_3"] = "PT_BINARY:PSETID_Address:0x80a5";
	$properties["address_book_mv"] = "PT_MV_LONG:PSETID_Address:0x8028";
	$properties["address_book_long"] = "PT_LONG:PSETID_Address:0x8029";
	$properties["oneoff_members"] = "PT_MV_BINARY:PSETID_Address:0x8054";
	$properties["members"] = "PT_MV_BINARY:PSETID_Address:0x8055";
	$properties["private"] = "PT_BOOLEAN:PSETID_Common:0x8506";
	$properties["contacts"] = "PT_MV_STRING8:PSETID_Common:0x853a";
	$properties["contacts_string"] = "PT_STRING8:PSETID_Common:0x8586";
	$properties["categories"] = "PT_MV_STRING8:PS_PUBLIC_STRINGS:Keywords";
	$properties["last_modification_time"] = PR_LAST_MODIFICATION_TIME;

	// Detailed contacts properties
	// Properties for phone numbers
	$properties["assistant_telephone_number"] = PR_ASSISTANT_TELEPHONE_NUMBER;
	$properties["business2_telephone_number"] = PR_BUSINESS2_TELEPHONE_NUMBER;
	$properties["callback_telephone_number"] = PR_CALLBACK_TELEPHONE_NUMBER;
	$properties["car_telephone_number"] = PR_CAR_TELEPHONE_NUMBER;
	$properties["company_telephone_number"] = PR_COMPANY_MAIN_PHONE_NUMBER;
	$properties["home2_telephone_number"] = PR_HOME2_TELEPHONE_NUMBER;
	$properties["home_fax_number"] = PR_HOME_FAX_NUMBER;
	$properties["isdn_number"] = PR_ISDN_NUMBER;
	$properties["other_telephone_number"] = PR_OTHER_TELEPHONE_NUMBER;
	$properties["pager_telephone_number"] = PR_PAGER_TELEPHONE_NUMBER;
	$properties["primary_fax_number"] = PR_PRIMARY_FAX_NUMBER;
	$properties["primary_telephone_number"] = PR_PRIMARY_TELEPHONE_NUMBER;
	$properties["radio_telephone_number"] = PR_RADIO_TELEPHONE_NUMBER;
	$properties["telex_telephone_number"] = PR_TELEX_NUMBER;
	$properties["ttytdd_telephone_number"] = PR_TTYTDD_PHONE_NUMBER;
	// Additional fax properties
	$properties["fax_1_address_type"] = "PT_STRING8:PSETID_Address:0x80B2";
	$properties["fax_1_email_address"] = "PT_STRING8:PSETID_Address:0x80B3";
	$properties["fax_1_original_display_name"] = "PT_STRING8:PSETID_Address:0x80B4";
	$properties["fax_1_original_entryid"] = "PT_BINARY:PSETID_Address:0x80B5";
	$properties["fax_2_address_type"] = "PT_STRING8:PSETID_Address:0x80C2";
	$properties["fax_2_email_address"] = "PT_STRING8:PSETID_Address:0x80C3";
	$properties["fax_2_original_display_name"] = "PT_STRING8:PSETID_Address:0x80C4";
	$properties["fax_2_original_entryid"] = "PT_BINARY:PSETID_Address:0x80C5";
	$properties["fax_3_address_type"] = "PT_STRING8:PSETID_Address:0x80D2";
	$properties["fax_3_email_address"] = "PT_STRING8:PSETID_Address:0x80D3";
	$properties["fax_3_original_display_name"] = "PT_STRING8:PSETID_Address:0x80D4";
	$properties["fax_3_original_entryid"] = "PT_BINARY:PSETID_Address:0x80D5";

	// Properties for addresses
	// Home address
	$properties["home_address_street"] = PR_HOME_ADDRESS_STREET;
	$properties["home_address_city"] = PR_HOME_ADDRESS_CITY;
	$properties["home_address_state"] = PR_HOME_ADDRESS_STATE_OR_PROVINCE;
	$properties["home_address_postal_code"] = PR_HOME_ADDRESS_POSTAL_CODE;
	$properties["home_address_country"] = PR_HOME_ADDRESS_COUNTRY;
	// Other address
	$properties["other_address_street"] = PR_OTHER_ADDRESS_STREET;
	$properties["other_address_city"] = PR_OTHER_ADDRESS_CITY;
	$properties["other_address_state"] = PR_OTHER_ADDRESS_STATE_OR_PROVINCE;
	$properties["other_address_postal_code"] = PR_OTHER_ADDRESS_POSTAL_CODE;
	$properties["other_address_country"] = PR_OTHER_ADDRESS_COUNTRY;
	// Business address
	$properties["business_address_street"] = "PT_STRING8:PSETID_Address:0x8045";
	$properties["business_address_city"] = "PT_STRING8:PSETID_Address:0x8046";
	$properties["business_address_state"] = "PT_STRING8:PSETID_Address:0x8047";
	$properties["business_address_postal_code"] = "PT_STRING8:PSETID_Address:0x8048";
	$properties["business_address_country"] = "PT_STRING8:PSETID_Address:0x8049";
	// Mailing address
	$properties["country"] = PR_COUNTRY;
	$properties["city"] = PR_LOCALITY;
	$properties["postal_address"] = PR_POSTAL_ADDRESS;
	$properties["postal_code"] = PR_POSTAL_CODE;
	$properties["state"] = PR_STATE_OR_PROVINCE;
	$properties["street"] = PR_STREET_ADDRESS;
	// Special Date such as birthday n anniversary appoitment's entryid is store
	$properties["birthday_eventid"] = "PT_BINARY:PSETID_Address:0x804D";
	$properties["anniversary_eventid"] = "PT_BINARY:PSETID_Address:0x804E";

	$properties["notes"] = PR_BODY;
	return $properties;
}	
	
	

	/**
	 * HTTP REPORT Method Wrapper
	 */
	function http_REPORT()
	{
		$options = Array();
		$files   = Array();

		$options["path"] = $this->path;

		// search depth from header (default is "infinity)
		if (isset($this->_SERVER['HTTP_DEPTH'])) {
			$options["depth"] = $this->_SERVER["HTTP_DEPTH"];
		} else {
			$options["depth"] = "infinity";
		}       

		// analyze request payload
		$propinfo = new _parse_report("php://input");
		if (!$propinfo->success) {
			$this->http_status("400 Error");
			return;
		}
		$options['props'] = $propinfo->props;
		error_log(print_r($options['props'],true));
		$options['sync-token'] = $propinfo->sync_token;

		// call user handler
		if (!$this->REPORT($options, $files)) {
			$files = array("files" => array());
			if ($options['sync-token-refresh']) {
				// Client specified a sync-token. We do not allow this.
				// See http://tools.ietf.org/html/draft-daboo-carddav-01#section-2.3 for error specification.
				//$this->http_status("412 Precondition Failed"); 
				$this->http_status("403 Forbidden"); 
				//$this->http_status("409 Conflict"); 
				header('Content-Type: text/xml; charset="utf-8"');
				echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
				echo "<D:error xmlns:D=\"DAV:\"><D:valid-sync-token/></D:error>\n";
			} elseif ($options['path-not-supported']) {
				// REPORT on root path /
				$this->http_status("404 Not Found");
			} else { 
				$this->http_status("400 Error");
			}
			
			return;
		}

		// collect namespaces here
		$ns_hash = array();

		// Microsoft Clients need this special namespace for date and time values
		$ns_defs = "xmlns:ns0=\"urn:uuid:c2f41010-65b3-11d1-a29f-00aa00c14882/\"";    

		// now we loop over all returned file entries
		foreach ($files["files"] as $filekey => $file) {
			// nothing to do if no properties were returend for a file
			if (!isset($file["props"]) || !is_array($file["props"])) {
				continue;
			}

			// now loop over all returned properties
			foreach ($file["props"] as $key => $prop) {
				// as a convenience feature we do not require that user handlers
				// restrict returned properties to the requested ones
				// here we strip all unrequested entries out of the response

				switch($options['props']) {
					case "all":
						// nothing to remove
						break;

					case "names":
						// only the names of all existing properties were requested
						// so we remove all values
						unset($files["files"][$filekey]["props"][$key]["val"]);
					break;

					default:
					$found = false;

					// search property name in requested properties 
					foreach ((array)$options["props"] as $reqprop) {
						if (!isset($reqprop["xmlns"])) {
							$reqprop["xmlns"] = "";
						}
						if (   $reqprop["name"]  == $prop["name"] 
								&& $reqprop["xmlns"] == $prop["ns"]) {
							$found = true;
							break;
						}
					}

					// unset property and continue with next one if not found/requested
					if (!$found) {
						$files["files"][$filekey]["props"][$key]="";
						continue(2);
					}
					break;
				}

				// namespace handling 
				if (empty($prop["ns"])) continue; // no namespace
				$ns = $prop["ns"]; 
				if ($ns == "DAV:") continue; // default namespace
				if (isset($ns_hash[$ns])) continue; // already known

				// register namespace 
				$ns_name = "ns".(count($ns_hash) + 1);
				$ns_hash[$ns] = $ns_name;
				$ns_defs .= " xmlns:$ns_name=\"$ns\"";
			}

			// we also need to add empty entries for properties that were requested
			// but for which no values where returned by the user handler
			if (is_array($options['props'])) {
				foreach ($options["props"] as $reqprop) {
					if ($reqprop['name']=="") continue; // skip empty entries

					$found = false;

					if (!isset($reqprop["xmlns"])) {
						$reqprop["xmlns"] = "";
					}

					// check if property exists in result
					foreach ($file["props"] as $prop) {
						if (   $reqprop["name"]  == $prop["name"]
								&& $reqprop["xmlns"] == $prop["ns"]) {
							$found = true;
							break;
						}
					}

					if (!$found) {
						if ($reqprop["xmlns"]==="DAV:" && $reqprop["name"]==="lockdiscovery") {
							// lockdiscovery is handled by the base class
							$files["files"][$filekey]["props"][] 
								= $this->mkprop("DAV:", 
										"lockdiscovery", 
										$this->lockdiscovery($files["files"][$filekey]['path']));
						} else {
							// add empty value for this property
							$files["files"][$filekey]["noprops"][] =
								$this->mkprop($reqprop["xmlns"], $reqprop["name"], "");

							// register property namespace if not known yet
							if ($reqprop["xmlns"] != "DAV:" && !isset($ns_hash[$reqprop["xmlns"]])) {
								$ns_name = "ns".(count($ns_hash) + 1);
								$ns_hash[$reqprop["xmlns"]] = $ns_name;
								$ns_defs .= " xmlns:$ns_name=\"$reqprop[xmlns]\"";
							}
						}
					}
				}
			}
		}

		// now we generate the reply header ...
		$this->http_status("207 Multi-Status");
		header('Content-Type: text/xml; charset="utf-8"');

		// ... and payload
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		echo "<multistatus xmlns=\"DAV:\">\n";

		foreach ($files["files"] as $file) {
			// ignore empty or incomplete entries
			if (!is_array($file) || empty($file) || !isset($file["path"])) continue;
			$path = $file['path'];                  
			if (!is_string($path) || $path==="") continue;

			echo " <sync-response $ns_defs>\n";

			/* TODO right now the user implementation has to make sure
			   collections end in a slash, this should be done in here
			   by checking the resource attribute */
			$href = $this->_mergePaths($this->_SERVER['SCRIPT_NAME'], $path);

			/* minimal urlencoding is needed for the resource path */
			$href = $this->_urlencode($href);

			echo "  <href>$href</href>\n";

			if (isset($file["status"])) {
				echo "   <status>".$file["status"]."</status>";
			}

			// report all found properties and their values (if any)
			if (isset($file["props"]) && is_array($file["props"])) {
				echo "  <propstat>\n";
				echo "   <prop>\n";

				foreach ($file["props"] as $key => $prop) {

					if (!is_array($prop)) continue;
					if (!isset($prop["name"])) continue;

					if (!isset($prop["val"]) || $prop["val"] === "" || $prop["val"] === false) {
						// empty properties (cannot use empty() for check as "0" is a legal value here)
						if ($prop["ns"]=="DAV:") {
							echo "     <$prop[name]/>\n";
						} else if (!empty($prop["ns"])) {
							echo "     <".$ns_hash[$prop["ns"]].":$prop[name]/>\n";
						} else {
							echo "     <$prop[name] xmlns=\"\"/>";
						}
					} else if ($prop["ns"] == "DAV:") {
						// some WebDAV properties need special treatment
						switch ($prop["name"]) {
							case "getcontenttype":
								echo "     <$prop[name]>$prop[val]</$prop[name]>\n";
							break;
							case "getetag":
								echo "     <$prop[name]>"
								. $this->_prop_encode($prop['val'])
								.     "</$prop[name]>\n";                               
							break;
							default:                                    
							echo "     <$prop[name]>"
								. $this->_prop_encode(htmlspecialchars($prop['val']))
								.     "</$prop[name]>\n";                               
							break;
						}
					} else {
						// properties from namespaces != "DAV:" or without any namespace 
						if ($prop["ns"]) {
							echo "     <" . $ns_hash[$prop["ns"]] . ":$prop[name]>"
								. $this->_prop_encode(htmlspecialchars($prop['val']))
								. "</" . $ns_hash[$prop["ns"]] . ":$prop[name]>\n";
						} else {
							echo "     <$prop[name] xmlns=\"\">"
								. $this->_prop_encode(htmlspecialchars($prop['val']))
								. "</$prop[name]>\n";
						}                               
					}
				}

				echo "   </prop>\n";
				echo "   <status>HTTP/1.1 200 OK</status>\n";
				echo "  </propstat>\n";
			}

			// now report all properties requested but not found
			if (isset($file["noprops"])) {
				echo "  <propstat>\n";
				echo "   <prop>\n";

				foreach ($file["noprops"] as $key => $prop) {
					if ($prop["ns"] == "DAV:") {
						echo "     <$prop[name]/>\n";
					} else if ($prop["ns"] == "") {
						echo "     <$prop[name] xmlns=\"\"/>\n";
					} else {
						echo "     <" . $ns_hash[$prop["ns"]] . ":$prop[name]/>\n";
					}
				}

				echo "   </prop>\n";
				echo "   <status>HTTP/1.1 404 Not Found</status>\n";
				echo "  </propstat>\n";
			}

			echo " </sync-response>\n";
		}
		if (isset($options['sync-token'])) {
			echo "<sync-token>".$options['sync-token']."</sync-token>";
		}

		echo "</multistatus>\n";

	}

	/**
	 * PROPFIND method handler
	 *
	 * @brief  This is almost an identical copy of the http_PROPFIND() function from Server.php of our anchestor class. Changes are marked with '#MOD'
	 * @param  void
	 * @return void
	 */
	function http_PROPFIND() 
	{
		$options = Array();
		$files   = Array();

		$options["path"] = $this->path;

		// search depth from header (default is "infinity)
		if (isset($this->_SERVER['HTTP_DEPTH'])) {
			$options["depth"] = $this->_SERVER["HTTP_DEPTH"];
		} else {
			$options["depth"] = "infinity";
		}       

		// analyze request payload
		$propinfo = new _parse_propfind("php://input");
		if (!$propinfo->success) {
			$this->http_status("400 Error");
			return;
		}
		$options['props'] = $propinfo->props;

		// call user handler
		if (!$this->PROPFIND($options, $files)) {
			$files = array("files" => array());
			if (method_exists($this, "checkLock")) {
				// is locked?
				$lock = $this->checkLock($this->path);

				if (is_array($lock) && count($lock)) {
					$created          = isset($lock['created'])  ? $lock['created']  : time();
					$modified         = isset($lock['modified']) ? $lock['modified'] : time();
					$files['files'][] = array("path"  => $this->_slashify($this->path),
							"props" => array($this->mkprop("displayname",      $this->path),
								$this->mkprop("creationdate",     $created),
								$this->mkprop("getlastmodified",  $modified),
								$this->mkprop("resourcetype",     ""),
								$this->mkprop("getcontenttype",   ""),
								$this->mkprop("getcontentlength", 0))
							);
				}
			}

			if (empty($files['files'])) {
				$this->http_status("404 Not Found");
				return;
			}
		}

		// collect namespaces here
		$ns_hash = array();

		// Microsoft Clients need this special namespace for date and time values
		$ns_defs = "xmlns:ns0=\"urn:uuid:c2f41010-65b3-11d1-a29f-00aa00c14882/\"";    

		// now we loop over all returned file entries
		foreach ($files["files"] as $filekey => $file) {

			// nothing to do if no properties were returend for a file
			if (!isset($file["props"]) || !is_array($file["props"])) {
				continue;
			}

			// now loop over all returned properties
			foreach ($file["props"] as $key => $prop) {
				// as a convenience feature we do not require that user handlers
				// restrict returned properties to the requested ones
				// here we strip all unrequested entries out of the response

				switch($options['props']) {
					case "all":
						// nothing to remove
						break;

					case "names":
						// only the names of all existing properties were requested
						// so we remove all values
						unset($files["files"][$filekey]["props"][$key]["val"]);
					break;

					default:
					$found = false;

					// search property name in requested properties 
					foreach ((array)$options["props"] as $reqprop) {
						if (!isset($reqprop["xmlns"])) {
							$reqprop["xmlns"] = "";
						}
						if (   $reqprop["name"]  == $prop["name"] 
								&& $reqprop["xmlns"] == $prop["ns"]) {
							$found = true;
							break;
						}
					}

					// unset property and continue with next one if not found/requested
					if (!$found) {
						$files["files"][$filekey]["props"][$key]="";
						continue(2);
					}
					break;
				}

				// namespace handling 
				if (empty($prop["ns"])) continue; // no namespace
				$ns = $prop["ns"]; 
				if ($ns == "DAV:") continue; // default namespace
				if (isset($ns_hash[$ns])) continue; // already known

				// register namespace 
				$ns_name = "ns".(count($ns_hash) + 1);
				$ns_hash[$ns] = $ns_name;
				$ns_defs .= " xmlns:$ns_name=\"$ns\"";
			}

			// we also need to add empty entries for properties that were requested
			// but for which no values where returned by the user handler
			if (is_array($options['props'])) {
				foreach ($options["props"] as $reqprop) {
					if ($reqprop['name']=="") continue; // skip empty entries

					$found = false;

					if (!isset($reqprop["xmlns"])) {
						$reqprop["xmlns"] = "";
					}

					// check if property exists in result
					foreach ($file["props"] as $prop) {
						if (   $reqprop["name"]  == $prop["name"]
								&& $reqprop["xmlns"] == $prop["ns"]) {
							$found = true;
							break;
						}
					}

					if (!$found) {
						if ($reqprop["xmlns"]==="DAV:" && $reqprop["name"]==="lockdiscovery") {
							// lockdiscovery is handled by the base class
							$files["files"][$filekey]["props"][] 
								= $this->mkprop("DAV:", 
										"lockdiscovery", 
										$this->lockdiscovery($files["files"][$filekey]['path']));
						} else {
							// add empty value for this property
							$files["files"][$filekey]["noprops"][] =
								$this->mkprop($reqprop["xmlns"], $reqprop["name"], "");

							// register property namespace if not known yet
							if ($reqprop["xmlns"] != "DAV:" && !isset($ns_hash[$reqprop["xmlns"]])) {
								$ns_name = "ns".(count($ns_hash) + 1);
								$ns_hash[$reqprop["xmlns"]] = $ns_name;
								$ns_defs .= " xmlns:$ns_name=\"$reqprop[xmlns]\"";
							}
						}
					}
				}
			}
		}

		// now we generate the reply header ...
		$this->http_status("207 Multi-Status");
		header('Content-Type: text/xml; charset="utf-8"');

		// ... and payload
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		echo "<multistatus xmlns=\"DAV:\">\n";

		foreach ($files["files"] as $file) {
			// ignore empty or incomplete entries
			if (!is_array($file) || empty($file) || !isset($file["path"])) continue;
			$path = $file['path'];                  
			if (!is_string($path) || $path==="") continue;

			echo " <response $ns_defs>\n";

			/* TODO right now the user implementation has to make sure
			   collections end in a slash, this should be done in here
			   by checking the resource attribute */
			$href = $this->_mergePaths($this->_SERVER['SCRIPT_NAME'], $path);

			/* minimal urlencoding is needed for the resource path */
			$href = $this->_urlencode($href);

			echo "  <href>$href</href>\n";

			// report all found properties and their values (if any)
			if (isset($file["props"]) && is_array($file["props"])) {
				echo "  <propstat>\n";
				echo "   <prop>\n";

				foreach ($file["props"] as $key => $prop) {

					if (!is_array($prop)) continue;
					if (!isset($prop["name"])) continue;

					if (!isset($prop["val"]) || $prop["val"] === "" || $prop["val"] === false) {
						// empty properties (cannot use empty() for check as "0" is a legal value here)
						if ($prop["ns"]=="DAV:") {
							echo "     <$prop[name]/>\n";
						} else if (!empty($prop["ns"])) {
							echo "     <".$ns_hash[$prop["ns"]].":$prop[name]/>\n";
						} else {
							echo "     <$prop[name] xmlns=\"\"/>";
						}
					} else if ($prop["ns"] == "DAV:") {
						// some WebDAV properties need special treatment
						switch ($prop["name"]) {
							case "creationdate":
								echo "     <creationdate ns0:dt=\"dateTime.tz\">"
								. gmdate("Y-m-d\\TH:i:s\\Z", $prop['val'])
								. "</creationdate>\n";
							break;
							case "getlastmodified":
								echo "     <getlastmodified ns0:dt=\"dateTime.rfc1123\">"
								. gmdate("D, d M Y H:i:s ", $prop['val'])
								. "GMT</getlastmodified>\n";
							break;
							case "resourcetype":
								echo "     <resourcetype>$prop[val]</resourcetype>\n";
							break;
							case "supportedlock":
								echo "     <supportedlock>$prop[val]</supportedlock>\n";
							break;
							case "lockdiscovery":  
								echo "     <lockdiscovery>\n";
							echo $prop["val"];
							echo "     </lockdiscovery>\n";
							break;
							// the following are non-standard Microsoft extensions to the DAV namespace
							case "lastaccessed":
								echo "     <lastaccessed ns0:dt=\"dateTime.rfc1123\">"
								. gmdate("D, d M Y H:i:s ", $prop['val'])
								. "GMT</lastaccessed>\n";
							break;
							case "ishidden":
								echo "     <ishidden>"
								. is_string($prop['val']) ? $prop['val'] : ($prop['val'] ? 'true' : 'false')
								. "</ishidden>\n";
							break;
							// >> #MOD
							case "getetag":
								echo "     <$prop[name]>"
								. $this->_prop_encode($prop['val'])
								.     "</$prop[name]>\n";                               
							break;
							case "supported-report-set":
								echo "     <$prop[name]>"
								. $prop['val']
								.     "</$prop[name]>\n";
							break;
							// << #MOD
							default:                                    
							echo "     <$prop[name]>"
								. $this->_prop_encode(htmlspecialchars($prop['val']))
								.     "</$prop[name]>\n";                               
							break;
						}
					} else {
						// properties from namespaces != "DAV:" or without any namespace 
						if ($prop["ns"]) {
							echo "     <" . $ns_hash[$prop["ns"]] . ":$prop[name]>"
								. $this->_prop_encode(htmlspecialchars($prop['val']))
								. "</" . $ns_hash[$prop["ns"]] . ":$prop[name]>\n";
						} else {
							echo "     <$prop[name] xmlns=\"\">"
								. $this->_prop_encode(htmlspecialchars($prop['val']))
								. "</$prop[name]>\n";
						}                               
					}
				}

				echo "   </prop>\n";
				echo "   <status>HTTP/1.1 200 OK</status>\n";
				echo "  </propstat>\n";
			}

			// now report all properties requested but not found
			if (isset($file["noprops"])) {
				echo "  <propstat>\n";
				echo "   <prop>\n";

				foreach ($file["noprops"] as $key => $prop) {
					if ($prop["ns"] == "DAV:") {
						echo "     <$prop[name]/>\n";
					} else if ($prop["ns"] == "") {
						echo "     <$prop[name] xmlns=\"\"/>\n";
					} else {
						echo "     <" . $ns_hash[$prop["ns"]] . ":$prop[name]/>\n";
					}
				}

				echo "   </prop>\n";
				echo "   <status>HTTP/1.1 404 Not Found</status>\n";
				echo "  </propstat>\n";
			}

			echo " </response>\n";
		}

		echo "</multistatus>\n";
	}

}


?>
