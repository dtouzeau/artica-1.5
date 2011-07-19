<?php
/**
* Create a vCard file for download
*
* $vCard = new vCard($lang,$download_dir);
* $vCard->setLastName('Mustermann');
* ...
* $vCard->writeCardFile();
* header('Location:' . $vCard->getCardFilePath());
*
* @access public
* @author Michael Wimmer <flaimo@gmx.net>
* @copyright Michael Wimmer
* @link http://www.flaimo.com/  flaimo.com
* @package vCard
* @version 1.001
*/
class vCard
  {
  
  /*-------------------*/
  /* V A R I A B L E S */
  /*-------------------*/
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */   
  var $first_name;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */   
  var $middle_name;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $last_name;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $edu_title;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $addon;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $nickname;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $company;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $organisation;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $department;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $job_title;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $note;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_work1_voice;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_work2_voice;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_home1_voice;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_home2_voice;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_cell_voice;
 
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_car_voice;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_pager_voice;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_additional;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_work_fax;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_home_fax;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_isdn;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_preferred;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $tel_telex;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $work_street;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $work_zip;
 
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $work_city;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $work_region;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $work_country;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $home_street;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $home_zip;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $home_city;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $home_region;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $home_country;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $postal_street;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $postal_zip;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */   
  var $postal_city;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $postal_region;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $postal_country;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $url_work;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $role;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $birthday;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $email;
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */   
  var $rev; 
  
  /**
  * No information available
  *
  * @var string
  * @access private
  */ 
  var $lang;
  

  /*-----------------------*/
  /* C O N S T R U C T O R */
  /*-----------------------*/

  /**
  * Constructor
  *
  * Only job is to set all the variablesnames
  *
  * @param (string) $downloaddir
  * @param (string) $lang
  * @return (void)
  * @access private
  * @since 1.000 - 2002/10/10   
  */
  function vCard($downloaddir = '', $lang = '')
    {
    $this->download_dir = (string) ((strlen(trim($downloaddir)) > 0) ? $downloaddir : 'vcarddownload');
    $this->card_filename = (string) time() . '.vcf';
    $this->rev = (string) date('Ymd\THi00\Z',time());
    $this->setLanguage($lang);
    if ($this->checkDownloadDir() == false)
      {
      die('error creating download directory');
      } // end if
    } // end function
  
  
  /*-------------------*/    
  /* F U N C T I O N S */
  /*-------------------*/
  
  /**
  * Checks if the download directory exists, else trys to create it
  *
  * @return (boolean)
  * @access private
  * @since 1.000 - 2002/10/10   
  */
  function checkDownloadDir()
    {
    if (!is_dir($this->download_dir))
      {
      if (!mkdir($this->download_dir, 0700))
        {
        return (boolean) false;
        }
      else
        {
        return (boolean) true;
        } // end if
      }
    else
      {
      return (boolean) true;
      } // end if
    } // end function  
  
  /**
  * Set Language (iso code) for the Strings in the vCard file
  *
  * @param (string) $isocode
  * @return (void)
  * @access private
  * @since 1.000 - 2002/10/10   
  */
  function setLanguage($isocode = '')
    {
    if ($this->isValidLanguageCode($isocode) == true)
      {
      $this->lang = (string) ';LANGUAGE=' . $isocode;
      }
    else
      {
      $this->lang = (string) '';
      } // end if
    } // end function
  
  /**
  * Encodes a string for QUOTE-PRINTABLE
  *
  * @param (string) $quotprint  String to be encoded
  * @return (string)  Encodes string
  * @access private
  * @since 1.000 - 2002/10/20 
  * @author Harald Huemer <harald.huemer@liwest.at>
  */
  function quotedPrintableEncode($quotprint)
    { 
    /*
    //beim Mac Umlaute nicht kodieren !!!! sonst Fehler beim Import
    if ($progid == 3)
      {
      $quotprintenc = preg_replace("~([\x01-\x1F\x3D\x7F-\xBF])~e", "sprintf('=%02X', ord('\\1'))", $quotprint);  
      return($quotprintenc);
      }
    //bei Windows und Linux alle Sonderzeichen kodieren
    else
      {*/
    return (string) preg_replace("~([\x01-\x1F\x3D\x7F-\xFF])~e", "sprintf('=%02X', ord('\\1'))", $quotprint);  
    } // end function

  /**
  * Checks if a given string is a valid iso-language-code
  *
  * @param (string) $code  String that should validated
  * @return (boolean) $isvalid  If string is valid or not
  * @access private
  * @since 1.000 - 2002/10/20 
  */
  function isValidLanguageCode($code)  // PHP5: protected
    {
    $isvalid = (boolean) false;
    if (preg_match('(^([a-z]{2})$|^([a-z]{2}_[a-z]{2})$|^([a-z]{2}-[a-z]{2})$)',trim($code)) > 0)
      {
      $isvalid = (boolean) true;
      } // end if
    return (boolean) $isvalid;  
    } // end function
  

  /**
  * Set the persons first name
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setFirstName($input)
    {
    $this->first_name = (string) $input;
    } // end function
  
  /**
  * Set the persons middle name(s)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setMiddleName($input)
    {
    $this->middle_name = (string) $input;
    } // end function
    
  /**
  * Set the persons last name
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setLastName($input)
    {
    $this->last_name = (string) $input;
    } // end function
    
  /**
  * Set the persons title (Doctor,...)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */  
  function setEducationTitle($input)
    {
    $this->edu_title = (string) $input;
    } // end function
    
  /**
  * Set the persons addon (jun., sen.,...)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setAddon($input)
    {
    $this->addon = (string) $input;
    } // end function
    
  /**
  * Set the persons nickname
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setNickname($input)
    {
    $this->nickname = (string) $input;
    } // end function
  
  /**
  * Set the company name for which the person works for
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setCompany($input)
    {
    $this->company = (string) $input;
    } // end function
  
  /**
  * Set the organisations name for which the person works for
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setOrganisation($input)
    {
    $this->organisation = (string) $input;
    } // end function
    
  /**
  * Set the department name of company for which the person works for
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setDepartment($input)
    {
    $this->department = (string) $input;
    } // end function
    
  /**
  * Set the persons job title
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setJobTitle($input)
    {
    $this->job_title = (string) $input;
    } // end function
    
  /**
  * Set additional notes for that person
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setNote($input)
    {
    $this->note = (string) $input;
    } // end function
  
  /**
  * Set telephone number (Work 1)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setTelephoneWork1($input)
    {
    $this->tel_work1_voice = (string) $input;
    } // end function
    
  /**
  * Set telephone number (Work 2)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setTelephoneWork2($input)
    {
    $this->tel_work2_voice = (string) $input;
    } // end function
  
  /**
  * Set telephone number (Home 1)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */  
  function setTelephoneHome1($input)
    {
    $this->tel_home1_voice = (string) $input;
    } // end function  
  
  /**
  * Set telephone number (Home 2)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setTelephoneHome2($input)
    {
    $this->tel_home2_voice = (string) $input;
    } // end function
  
  /**
  * Set cellphone number
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setCellphone($input)
    {
    $this->tel_cell_voice = (string) $input;
    } // end function
  
  
  /**
  * Set carphone number
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setCarphone($input)
    {
    $this->tel_car_voice = (string) $input;
    } // end function
  
  /**
  * Set pager number
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setPager($input)
    {
    $this->tel_pager_voice = (string) $input;
    } // end function  
    
  /**
  * Set additional phone number
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setAdditionalTelephone($input)
    {
    $this->tel_additional = (string) $input;
    } // end function  
    
  /**
  * Set fax number (Work)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setFaxWork($input)
    {
    $this->tel_work_fax = (string) $input;
    } // end function  
  
  /**
  * Set fax number (Home)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setFaxHome($input)
    {
    $this->tel_work_home = (string) $input;
    } // end function  
  
  
  /**
  * Set ISDN (phone) number
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setISDN($input)
    {
    $this->tel_isdn = (string) $input;
    } // end function  
  
  /**
  * Set preferred phone number
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setPreferredTelephone($input)
    {
    $this->tel_preferred = (string) $input;
    } // end function  
  
  /**
  * Set telex number
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setTelex($input)
    {
    $this->tel_telex = (string) $input;
    } // end function  
  
  
  /**
  * Set streetname (Work Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setWorkStreet($input)
    {
    $this->work_street = (string) $input;
    } // end function  
  
  /**
  * Set ZIP code (Work Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setWorkZIP($input)
    {
    $this->work_zip = (string) $input;
    } // end function  
  
  /**
  * Set city (Work Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setWorkCity($input)
    {
    $this->work_city = (string) $input;
    } // end function  
  
  /**
  * Set region (Work Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setWorkRegion($input)
    {
    $this->work_region = (string) $input;
    } // end function  
  
  /**
  * Set country (Work Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setWorkCountry($input)
    {
    $this->work_country = (string) $input;
    } // end function  
  
  
  /**
  * Set streetname (Home Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setHomeStreet($input)
    {
    $this->home_street = (string) $input;
    } // end function  
  
  /**
  * Set ZIP code (Home Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setHomeZIP($input)
    {
    $this->home_zip = (string) $input;
    } // end function  
  
  /**
  * Set city (Home Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setHomeCity($input)
    {
    $this->home_city = (string) $input;
    } // end function  
  
  /**
  * Set region (Home Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setHomeRegion($input)
    {
    $this->home_region = (string) $input;
    } // end function  
  
  /**
  * Set country (Home Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setHomeCountry($input)
    {
    $this->home_country = (string) $input;
    } // end function  
    
  
  /**
  * Set streetname (Postal Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setPostalStreet($input)
    {
    $this->postal_street = (string) $input;
    } // end function  
  
  /**
  * Set ZIP code (Postal Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */  
  function setPostalZIP($input)
    {
    $this->postal_zip = (string) $input;
    } // end function  
  
  /**
  * Set city (Postal Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setPostalCity($input)
    {
    $this->postal_city = (string) $input;
    } // end function  
  
  /**
  * Set region (Postal Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setPostalRegion($input)
    {
    $this->postal_region = (string) $input;
    } // end function  
  
  /**
  * Set country (Postal Address)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setPostalCountry($input)
    {
    $this->postal_country = (string) $input;
    } // end function  
  
  
  /**
  * Set URL (Work)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setURLWork($input)
    {
    $this->url_work = (string) $input;
    } // end function  
  
  /**
  * Set role (Student,...)
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setRole($input)
    {
    $this->role = (string) $input;
    } // end function  
  
  
  /**
  * Set birthday
  *
  * @param (int) $timestamp
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setBirthday($timestamp)
    {
    $this->birthday = (int) date('Ymd',$timestamp);
    } // end function  
  
  
  /**
  * Set eMail address
  *
  * @param (string) $input
  * @return (void)
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function setEMail($input)
    {
    $this->email = (string) $input;
    } // end function  
  
  /**
  * Generates the string to be written in the file later on
  *
  * @return (void)
  * @see getCardOutput(), writeCardFile()
  * @access public
  * @since 1.000 - 2002/10/10   
  */
  function generateCardOutput()
    {
    $this->output  = (string) "BEGIN:VCARD\r\n";
    $this->output .= (string) "VERSION:2.1\r\n";
    $this->output .= (string) "N;ENCODING=QUOTED-PRINTABLE:" . $this->quotedPrintableEncode($this->last_name . ";" . $this->first_name . ";" . $this->middle_name . ";" . $this->addon) . "\r\n";
    $this->output .= (string) "FN;ENCODING=QUOTED-PRINTABLE:" . $this->quotedPrintableEncode($this->first_name . " " . $this->middle_name . " " . $this->last_name . " " . $this->addon) . "\r\n";
    
    if (strlen(trim($this->nickname)) > 0)
      {    
      $this->output .= (string) "NICKNAME;ENCODING=QUOTED-PRINTABLE:" . $this->quotedPrintableEncode($this->nickname) . "\r\n";
      } // end if
    
    $this->output .= (string) "ORG" . $this->lang . ";ENCODING=QUOTED-PRINTABLE:" . $this->quotedPrintableEncode($this->organisation) . ";" . $this->quotedPrintableEncode($this->department) . "\r\n";
    
    if (strlen(trim($this->job_title)) > 0)
      {
      $this->output .= (string) "TITLE" . $this->lang . ";ENCODING=QUOTED-PRINTABLE:" . $this->quotedPrintableEncode($this->job_title) . "\r\n";
      } // end if
    
    if (strlen(trim($this->note)) > 0)
      {
      $this->output .= (string) "NOTE" . $this->lang . ";ENCODING=QUOTED-PRINTABLE:" . $this->quotedPrintableEncode($this->note) . "\r\n";
      } // end if

    if (strlen(trim($this->tel_work1_voice)) > 0)
      {
      $this->output .= (string) "TEL;WORK;VOICE:" . $this->tel_work1_voice . "\r\n";
      } // end if
    
    if (strlen(trim($this->tel_work2_voice)) > 0)
      {
      $this->output .= (string) "TEL;WORK;VOICE:" . $this->tel_work1_voice . "\r\n";
      } // end if
    
    if (strlen(trim($this->tel_home1_voice)) > 0)
      {
      $this->output .= (string) "TEL;HOME;VOICE:" . $this->tel_home1_voice . "\r\n";
      } // end if
    
    if (strlen(trim($this->tel_cell_voice)) > 0)
      {
      $this->output .= (string) "TEL;CELL;VOICE:" . $this->tel_cell_voice . "\r\n";
      } // end if

    if (strlen(trim($this->tel_car_voice)) > 0)
      {
      $this->output .= (string) "TEL;CAR;VOICE:" . $this->tel_car_voice . "\r\n";
      } // end if

    if (strlen(trim($this->tel_additional)) > 0)
      {
      $this->output .= (string) "TEL;VOICE:" . $this->tel_additional . "\r\n";
      } // end if

    if (strlen(trim($this->tel_pager_voice)) > 0)
      {
      $this->output .= (string) "TEL;PAGER;VOICE:" . $this->tel_pager_voice . "\r\n";
      } // end if
      
    if (strlen(trim($this->tel_work_fax)) > 0)
      {
      $this->output .= (string) "TEL;WORK;FAX:" . $this->tel_work_fax . "\r\n";
      } // end if

    if (strlen(trim($this->tel_home_fax)) > 0)
      {
      $this->output .= (string) "TEL;HOME;FAX:" . $this->tel_home_fax . "\r\n";
      } // end if

    if (strlen(trim($this->tel_home2_voice)) > 0)
      {
      $this->output .= (string) "TEL;HOME:" . $this->tel_home2_voice . "\r\n";
      } // end if

    if (strlen(trim($this->tel_isdn)) > 0)
      {
      $this->output .= (string) "TEL;ISDN:" . $this->tel_isdn . "\r\n";
      } // end if
      
    if (strlen(trim($this->tel_preferred)) > 0)
      {
      $this->output .= (string) "TEL;PREF:" . $this->tel_preferred . "\r\n";
      } // end if      

    $this->output .= (string) "ADR;WORK:;" . $this->company . ";" . $this->work_street . ";" . $this->work_city . ";" . $this->work_region . ";" . $this->work_zip . ";" . $this->work_country . "\r\n";
    $this->output .= (string) "LABEL;WORK;ENCODING=QUOTED-PRINTABLE:" . $this->quotedPrintableEncode($this->company) . "=0D=0A" . $this->quotedPrintableEncode($this->work_street) . "=0D=0A" . $this->quotedPrintableEncode($this->work_city) . ", " . $this->quotedPrintableEncode($this->work_region) . " " . $this->quotedPrintableEncode($this->work_zip) . "=0D=0A" . $this->quotedPrintableEncode($this->work_country) . "\r\n";
    $this->output .= (string) "ADR;HOME;;" . $this->home_street . ";" . $this->home_city . ";" . $this->home_region . ";" . $this->home_zip . ";" . $this->home_country . "\r\n";
    $this->output .= (string) "LABEL;WORK;ENCODING=QUOTED-PRINTABLE:" . $this->quotedPrintableEncode($this->home_street) . "=0D=0A" . $this->quotedPrintableEncode($this->home_city) . ", " . $this->quotedPrintableEncode($this->home_region) . " " . $this->quotedPrintableEncode($this->home_zip) . "=0D=0A" . $this->quotedPrintableEncode($this->home_country) . "\r\n";
    $this->output .= (string) "ADR;POSTAL;;" . $this->postal_street . ";" . $this->postal_city . ";" . $this->postal_region . ";" . $this->postal_zip . ";" . $this->postal_country . "\r\n";
    $this->output .= (string) "LABEL;POSTAL;ENCODING=QUOTED-PRINTABLE:" . $this->quotedPrintableEncode($this->postal_street) . "=0D=0A" . $this->quotedPrintableEncode($this->postal_city) . ", " . $this->quotedPrintableEncode($this->postal_region) . " " . $this->quotedPrintableEncode($this->postal_zip) . "=0D=0A" . $this->quotedPrintableEncode($this->postal_country) . "\r\n";

    if (strlen(trim($this->url_work)) > 0)
      {
      $this->output .= (string) "URL;WORK:" . $this->url_work . "\r\n";
      } // end if    

    if (strlen(trim($this->role)) > 0)
      {
      $this->output .= (string) "ROLE" . $this->lang . ":" . $this->role . "\r\n";
      } // end if  
    
    if (strlen(trim($this->birthday)) > 0)
      {
      $this->output .= (string) "BDAY:" . $this->birthday . "\r\n";
      } // end if  
    
    if (strlen(trim($this->email)) > 0)
      {
      $this->output .= (string) "EMAIL;PREF;INTERNET:" . $this->email . "\r\n";
      } // end if  

    if (strlen(trim($this->tel_telex)) > 0)
      {
      $this->output .= (string) "EMAIL;TLX:" . $this->tel_telex . "\r\n";
      } // end if  
    
    $this->output .= (string) "REV:" . $this->rev . "\r\n";
    $this->output .= (string) "END:VCARD\r\n";
    } // end function  
  
  /**
  * Loads the string into the variable if it hasn't been set before
  *
  * @return (string) $output
  * @see generateCardOutput(), writeCardFile()
  * @access public
  * @since 1.000 - 2002/10/10   
  */
  function getCardOutput()
    {
    if (!isset($this->output))
      {
      $this->generateCardOutput();
      } // end if
    return (string) $this->output;
    } // end function  
  
  /**
  * Writes the string into the file and saves it to the download directory
  *
  * @return (void)
  * @see generateCardOutput(), getCardOutput()
  * @access public
  * @since 1.000 - 2002/10/10   
  */
  function writeCardFile()
    {
    if (!isset($this->output))
      {
      $this->generateCardOutput();
      } // end if
    $handle = fopen($this->download_dir . '/' . $this->card_filename, 'w');
    fputs($handle, $this->output);
    fclose($handle);
    $this->deleteOldFiles(30);
    if (isset($handle)) { unset($handle); }
    } // end function      
  
  /**
  * Writes the string into the file and saves it to the download directory
  *
  * @param (int) $time  Minimum age of the files (in seconds) before files get deleted
  * @return (void)
  * @see writeCardFile()
  * @access private
  * @since 1.000 - 2002/10/20   
  */
  function deleteOldFiles($time = 300)
    {
    if (!is_int($time) || $time < 1)
      {
      $time = (int) 300;
      } // end if
    $handle = opendir($this->download_dir);
    while ($file = readdir($handle))
      {
      if (!preg_match("#^\.{1,2}$#i",$file) && !is_dir($this->download_dir . '/' . $file) && preg_match("#\.vcf#",$file) && ((time() - filemtime($this->download_dir . '/' . $file)) > $time))
        {
        unlink($this->download_dir . '/' . $file);
        } // end if
      } // end while
    closedir($handle);
    if (isset($handle)) { unset($handle); } 
    if (isset($file)) { unset($file); } 
    } // end function        
  
  /**
  * Returns the full path to the saved file where it can be downloaded.
  *
  * Can be used for "header(Location:..."
  *
  * @return (string)  Full http path
  * @access public
  * @since 1.000 - 2002/10/20   
  */
  function getCardFilePath()
    {
    $path_parts = pathinfo($_SERVER['SCRIPT_NAME']);
    $port = (string) (($_SERVER['SERVER_PORT'] != 80) ? ':' . $_SERVER['SERVER_PORT'] : '' );
    return (string) 'http://' . $_SERVER['SERVER_NAME'] . $port . $path_parts["dirname"] . '/' . $this->download_dir . '/' . $this->card_filename;
    } // end function    
  } // end class vCard
?>
