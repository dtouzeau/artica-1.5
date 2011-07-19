<?php
include_once(dirname(__FILE__).'/class.templates.inc');
include_once(dirname(__FILE__).'/class.ldap.inc');
include_once(dirname(__FILE__).'/class.images.inc');

/*
 *dn: uid=prenom.nom,ou=lestat,ou=addressbook,dc=lan
objectClass: top
objectClass: person
objectClass: organizationalPerson
objectClass: inetOrgPerson
objectClass: mozillaOrgPerson
cn: prenom nom
uid: prenom.nom
description: Commentaires
facsimileTelephoneNumber: FAX
displayName: Prenom Nom
givenName: Prenom
homePhone: Telephone personel
homePostalAddress: Adresse personnelle
mobile: Telephone GSM
pager: Numero de pager
sn: Nom
telephoneNumber: Telephone professionel
xmozillanickname: Pseudonyme
nsAIMid: Identifiant AIM
mozillaSecondEmail: Autre adresse mail
mozillaHomeLocalityName: Ville
mozillaHomeCountryName: Pays
homeurl: Page web perso
mozillaHomePostalCode: CP
mozillaHomeState: Etat
o: Societe
ou: Travail - Departement
postOfficeBox: Adresse professionnelle
l: Travail - Ville
st: Travail - Etat
c: Travail - Pays
title: Travail - Titre
postalCode: Travail - CP
workurl: Travail - Site web
custom1: Personnalise 1
custom2: Personnalise 2
custom3: Personnalise 3
custom4: Personnalise 4
mozillaHomePostalAddress2: Adresse personnelle suite
mozillaPostalAddress2: Adresse professionnelle suite
mail: prenom.nom@email.com
 * 
 */

class contacts{
	var $ou;
	var $uid;
	var $dn;
	var $attributes=array();
	var $UseridOU;
	var $img_identity;
	var $displayName;
	var $jpegPhoto;
	var $gender_array=array(0 => "{not_known}", 1 =>"{male}", 2 =>"{female}",9 =>"{not_applicable}");
	var $gender;
	var $givenName; // First Name;
	var $telephoneNumber;
	var $homePhone;
	var $homePostalAddress; //personal address
	var $street;
	var $postOfficeBox;//Professional address
	var $postalCode;
	var $postalAddress;
	var $l;   //	Business city
	var $st;  //	Business state/province
	var $c;  // 	Country
	var $mail;
	var $mobile;
	var $labeledURI; // personal URL
	var $modifyTimestamp; //when entry was modified
	var $department;
	var $o;
	var $note;
	var $mozillaSecondEmail=array();
	var $mozillaNickname;
	var $facsimileTelephoneNumber;
	var $Fax;
	var $nsAIMid;
	var $nsICQid;
	var $nsMSNid;
	var $nsYahooid;
	var $employeeNumber;
	var $businessRole;
	var $managerName;
	var $assistantName;
	var $roomNumber;
	var $birthDate;
	var $spouseName;
	var $anniversary;
	var $jpegPhoto_datas;
	var $NoExport=false;
	var $uidNumber;
	var $uidUser;
	var $null=null;
	var $cn=null;
	var $title; //Working title, as opposed to personell title. e.g. "Project leader", etc. 
	var $fileAs;
	var $sn; // Last Name;
	var $personalTitle;
	
	//ICQ LIKE
	
	var $ERROR;
		
	
	function contacts($uid=null,$employeeNumber=null,$uidUser=null){
		$this->img_identity="img/contact-unknown-user.png";
		$this->employeeNumber=$employeeNumber;
		$this->uidUser=$uidUser;
		$this->uid=$uid;
		$this->LoadContact();
		
		if($this->dn==null){
			$ct=new user($uid);
			$this->UseridOU=$ct->ou;
			$this->uid=$uid;
			
		}
		
		
		
	}
	
		function _Get_dn_(){
			if($this->employeeNumber<>null){
				writelogs("Search DN for employeeNumber:$this->employeeNumber",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$ldap=new clladp();
				$sr =@ldap_search($ldap->ldap_connection,"dc=NAB,$ldap->suffix","(employeeNumber=$this->employeeNumber)");
			}
		if($this->uidUser<>null){
				writelogs("Search DN for uid:$this->uidUser",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$ldap=new clladp();
				$sr =@ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix","(uid=$this->uidUser)");
			}			
			
				if($sr){
					$result = ldap_get_entries($ldap->ldap_connection, $sr);
					return trim($result[0]["dn"]);
				}else{
					writelogs("Search DN Failed",__CLASS__.'/'.__FUNCTION__,__FILE__);
				}
		}	
		
		
		public function employeeNumberByEmail($mail){
			$ldap=new clladp();
			$dn="ou=$this->uid,ou=People,dc=$this->UseridOU,dc=NAB,$ldap->suffix";
			$filter="(&(objectClass=inetOrgPerson)(|(mozillaSecondEmail=$mail)(mail=$mail)))";
			$attrs=array("employeeNumber");
			$hash=$ldap->Ldap_search($dn,$filter,$attrs,5);
			if($hash["count"]==0){return null;}
			return $hash[0][strtolower("employeeNumber")][0];
		}
	
	
private	function LoadContact(){
		$ldap=new clladp();
		$AsContact=false;
		if($this->uidUser<>null){
			writelogs("Find local user $this->uidUser",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$sr =@ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix","(uid=$this->uidUser)");
			if(!$sr){writelogs("Unable to find : $this->uidUser",__CLASS__.'/'.__FUNCTION__,__FILE__);return null;}
		}else{
			if($this->employeeNumber==null){return;}
			$sr=@ldap_search($ldap->ldap_connection,"dc=NAB,$ldap->suffix","(employeeNumber=$this->employeeNumber)");
			if(!$sr){writelogs("Unable to find : $this->employeeNumber",__CLASS__.'/'.__FUNCTION__,__FILE__);return null;}
			$AsContact=true;
		}
		
		$entry_id =ldap_get_entries($ldap->ldap_connection,$sr);
		if(!$entry_id){
			writelogs( "INFOS: bad value \$entry_id: ($entry_id )  find: (employeeNumber=$this->employeeNumber) OR (uid=$this->uidUser)-> aborting function search engine doesn`t found the pattern",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return null;
		}
		
		$attrs =$entry_id[0];
		
		$this->attributes=$attrs;
		$this->dn=$entry_id[0]["dn"];	
		writelogs( "Loading $this->dn line: ".__LINE__,__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->givenName=$attrs[strtolower("givenName")][0];
		$this->sn=$attrs["sn"][0];
		$this->displayName=$attrs[strtolower("displayName")][0];
		$this->mail=$attrs[strtolower("mail")][0];
		if(isset($attrs[strtolower("telephoneNumber")][0])){$this->telephoneNumber=$attrs[strtolower("telephoneNumber")][0];}
		$this->fileAs=$attrs[strtolower("fileAs")][0];
		if(isset($attrs[strtolower("mozillaNickname")][0])){$this->mozillaNickname=$attrs[strtolower("mozillaNickname")][0];}
		if(isset($attrs[strtolower("ou")][0])){$this->ou=$attrs[strtolower("ou")][0];}
		if(isset($attrs[strtolower("homePhone")][0])){$this->homePhone=$attrs[strtolower("homePhone")][0];}
		if(isset($attrs[strtolower("mobile")][0])){$this->mobile=$attrs[strtolower("mobile")][0];}
		if(isset($attrs[strtolower("facsimiletelephonenumber")][0])){$this->Fax=$attrs['facsimiletelephonenumber'][0];}
		if(isset($attrs[strtolower("facsimiletelephonenumber")][0])){$this->facsimileTelephoneNumber=$attrs['facsimiletelephonenumber'][0];}
		if(isset($attrs[strtolower("nsAIMid")][0])){$this->nsAIMid=$attrs[strtolower('nsAIMid')][0];}
		if(isset($attrs[strtolower("nsICQid")][0])){$this->nsICQid=$attrs[strtolower('nsICQid')][0];}
		if(isset($attrs[strtolower("nsMSNid")][0])){$this->nsMSNid=$attrs[strtolower('nsMSNid')][0];}
		if(isset($attrs[strtolower("nsYahooid")][0])){$this->nsYahooid=$attrs[strtolower('nsYahooid')][0];}
		if(isset($attrs[strtolower("labeledURI")][0])){$this->labeledURI=$attrs[strtolower('labeledURI')][0];}
		if(isset($attrs[strtolower("businessRole")][0])){$this->businessRole=$attrs[strtolower("businessRole")][0];}
		if(isset($attrs[strtolower("title")][0])){$this->title=$attrs[strtolower("title")][0];}
		if(isset($attrs[strtolower("o")][0])){$this->o=$attrs[strtolower("o")][0];}
		if(isset($attrs[strtolower("managerName")][0])){$this->managerName=$attrs[strtolower("managerName")][0];}
		if(isset($attrs[strtolower("assistantName")][0])){$this->assistantName=$attrs[strtolower("assistantName")][0];}
		if(isset($attrs[strtolower("roomNumber")][0])){$this->roomNumber=$attrs[strtolower("roomNumber")][0];}
		if(isset($attrs[strtolower("birthDate")][0])){$this->birthDate=$attrs[strtolower("birthDate")][0];}
		if(isset($attrs[strtolower("spouseName")][0])){$this->spouseName=$attrs[strtolower("spouseName")][0];}
		if(isset($attrs[strtolower("anniversary")][0])){$this->anniversary=$attrs[strtolower("anniversary")][0];}
		if(isset($attrs[strtolower("note")][0])){$this->note=$attrs[strtolower("note")][0];}
		if(isset($attrs[strtolower("homePostalAddress")][0])){$this->homePostalAddress=$attrs[strtolower("homePostalAddress")][0];}
		if(isset($attrs[strtolower("postalAddress")][0])){$this->postalAddress=$attrs[strtolower("postalAddress")][0];}
		if(isset($attrs[strtolower("l")][0])){$this->l=$attrs[strtolower("l")][0];}
		if(isset($attrs[strtolower("postalCode")][0])){$this->postalCode=$attrs[strtolower("postalCode")][0];}
		if(isset($attrs[strtolower("st")][0])){$this->st=$attrs[strtolower("st")][0];}
		if(isset($attrs[strtolower("c")][0])){$this->c=$attrs[strtolower("c")][0];}
		if(isset($attrs[strtolower("gender")][0])){$this->gender=$attrs[strtolower("gender")][0];}
		if(isset($attrs[strtolower("modifyTimestamp")][0])){$this->modifyTimestamp=$attrs[strtolower("modifyTimestamp")][0];}
		if(isset($attrs[strtolower("jpegPhoto")][0])){$this->jpegPhoto=$attrs[strtolower("jpegPhoto")][0];}
		$this->draw_jpeg_photos();
		if(isset($attrs[strtolower("uidNumber")][0])){$this->uidNumber=$attrs[strtolower("uidNumber")][0];}
		
		if(isset($attrs[strtolower("mozillaSecondEmail")]["count"])){
			for($i=0;$i<$attrs[strtolower("mozillaSecondEmail")]["count"];$i++){
				$this->mozillaSecondEmail[$i]=$attrs[strtolower("mozillaSecondEmail")][$i];
			}
		}
		
		for($i=0;$i<$attrs[strtolower("objectClass")]["count"];$i++){
			$class=$attrs[strtolower("objectClass")][$i];
			$this->objectClass_array[$class]=true;
		}
		
		if(!$this->objectClass_array["organizationalPerson"]){
			$upd["objectClass"]="organizationalPerson";
			if(!$ldap->Ldap_add_mod($this->dn,$upd)){
				writelogs("Class organizationalPerson failed",__CLASS__.'/'.__FUNCTION__,__FILE__);
				writelogs("$ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			}
		}
		
		
		if($AsContact){
			if(!$this->objectClass_array["zarafa-contact"]){
				$upd["objectClass"]="zarafa-contact";
				if(!$ldap->Ldap_add_mod($this->dn,$upd)){
					writelogs("Class zarafa-contact failed",__CLASS__.'/'.__FUNCTION__,__FILE__);
					writelogs("$ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__);
				}
			}
		}
		
		$this->BuildDefault();
		
		
	}
	
function BuildDefault(){
		if($this->sn==null){$this->sn="Unknown";}
		if($this->givenName==null){$this->givenName="Unknown";}
		if($this->displayName==null){$this->displayName="$this->givenName $this->sn";}

		
		if(trim($this->img_identity)==null){$this->img_identity="img/contact-unknown-user.png";}
		
		if($this->cn==null){
			
			if(($this->sn<>null) && ($this->givenName<>null)){
				$this->cn="$this->givenName $this->sn";
			}
			}else{
				if($this->uidUser<>null){
					$this->cn=$this->uidUser;
				}else{
					$this->cn="$this->employeeNumber";
				}	
			}
			
			if($this->nsAIMid==null){$this->nsAIMid="none.1";}	
			if($this->nsMSNid==null){$this->nsMSNid="none.3";}
			if($this->nsYahooid==null){$this->nsYahooid="none.4";}
		}
	
	
	function Save(){
		$this->BuildDefault();
		$ldap=new clladp();
		$evolutionPerson=true;
		$asContact=false;
		if($this->dn==null){
			if($this->uidUser<>null){
				$ct=new user($this->uidUser);
				$this->dn=$ct->dn;
				
			}else{
				$this->dn="cn=$this->cn,ou=$this->uid,ou=People,dc=$this->UseridOU,dc=NAB,$ldap->suffix";
				$asContact=true;
				//zarafa-contact
			}
		}
		
		writelogs("Cheking dn \"$this->dn\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		if(!$ldap->ExistsDN($this->dn)){
			if($this->uidUser<>null){
				echo "DN \"$this->dn\" does not exists !!\n";
				return false;
			}
			
			if(!$this->AddMinimal()){
				echo "$this->dn \n$this->ERROR\n";
				return false;
			}
		}else{
			writelogs("Cheking dn \"$this->dn\" Already exists",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		
		$upd["sn"][0]=$this->sn;
		$upd["givenName"][0]=$this->givenName;
		$upd["displayName"][0]="$this->sn $this->givenName";
		$upd["fileAs"][0]="$this->sn, $this->givenName";
		
		
		if($this->uidUser==null){if($this->mail==null){$this->DeleteAttribute('mail');}}
		
		
		writelogs("ou=$this->ou",__CLASS__.'/'.__FUNCTION__,__FILE__);
		writelogs("Fax=$this->Fax",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		
		if($this->telephoneNumber<>null){$upd["telephoneNumber"][0]=$this->telephoneNumber;}else{$this->DeleteAttribute('telephoneNumber');}
		if($this->mozillaNickname<>null){$upd["mozillaNickname"][0]=$this->mozillaNickname;}else{$this->DeleteAttribute('mozillaNickname');}
		if($this->ou<>null){$upd["ou"][0]=$this->ou;}else{$this->DeleteAttribute('ou');}
		if($this->homePhone<>null){$upd["homePhone"][0]=$this->homePhone;}else{$this->DeleteAttribute('homePhone');}
		if($this->mobile<>null){$upd["mobile"][0]=$this->mobile;}else{$this->DeleteAttribute('mobile');}
		if($this->Fax<>null){$upd["fax"][0]=$this->Fax;}else{$this->DeleteAttribute('fax');}
		if($this->labeledURI<>null){
			$upd["labeledURI"][0]=$this->labeledURI;
			$upd["mozillaHomeUrl"]=$this->labeledURI;
			}else{
				$this->DeleteAttribute('labeledURI');
				$this->DeleteAttribute('mozillaHomeUrl');
				}
		
		if($this->businessRole<>null){$upd["businessRole"][0]=$this->businessRole;}else{$this->DeleteAttribute('businessRole');}
		if($this->title<>null){$upd["title"][0]=$this->title;}else{$this->DeleteAttribute('title');}
		if($this->o<>null){$upd["o"][0]=$this->o;}else{$this->DeleteAttribute('o');}
		if($this->managerName<>null){$upd["managerName"][0]=$this->managerName;}else{$this->DeleteAttribute('managerName');}
		if($this->assistantName<>null){$upd["assistantName"][0]=$this->assistantName;}else{$this->DeleteAttribute('assistantName');}
		if($this->roomNumber<>null){$upd["roomNumber"][0]=$this->roomNumber;}else{$this->DeleteAttribute('roomNumber');}
		if($this->birthDate<>null){$upd["birthDate"][0]=$this->birthDate;}else{$this->DeleteAttribute('birthDate');}
		if($this->spouseName<>null){$upd["spouseName"][0]=$this->spouseName;}else{$this->DeleteAttribute('spouseName');}
		if($this->anniversary<>null){$upd["anniversary"][0]=$this->anniversary;}else{$this->DeleteAttribute('anniversary');}
		if($this->note<>null){$upd["note"][0]=$this->note;}else{$this->DeleteAttribute('note');}
		if($this->postalAddress<>null){$upd["postalAddress"][0]=$this->postalAddress;}else{$this->DeleteAttribute('postalAddress');}
		if($this->l<>null){$upd["l"][0]=$this->l;}else{$this->DeleteAttribute('l');}
		if($this->postalCode<>null){$upd["postalCode"][0]=$this->postalCode;}else{$this->DeleteAttribute('postalCode');}
		if($this->st<>null){$upd["st"][0]=$this->st;}else{$this->DeleteAttribute('st');}
		if($this->c<>null){$upd["c"][0]=$this->c;}else{$this->DeleteAttribute('c');}
		if($this->gender<>null){$upd["gender"][0]=$this->gender;}else{$this->DeleteAttribute('gender');}				
		if(strlen($this->jpegPhoto_datas)>0){$upd["jpegPhoto"][0]=$this->jpegPhoto_datas;}
		if($this->nsICQid<>null){$upd["nsICQid"][0]=$this->nsICQid;}else{$this->DeleteAttribute('nsICQid');}
		if($this->nsMSNid<>null){$upd["nsMSNid"][0]=$this->nsMSNid;}else{$this->DeleteAttribute('nsMSNid');}
		if($this->nsYahooid<>null){$upd["nsYahooid"][0]=$this->nsYahooid;}else{$this->DeleteAttribute('nsYahooid');}
		if($this->nsAIMid<>null){$upd["nsAIMid"][0]=$this->nsAIMid;}else{$this->DeleteAttribute('nsAIMid');}
		if($this->mail<>null){$upd["mail"][0]=$this->mail;}else{$this->DeleteAttribute('mail');}
		if($this->homePostalAddress<>null){$upd["homePostalAddress"][0]=$this->homePostalAddress;}else{$this->DeleteAttribute('homePostalAddress');}
		
		
		writelogs("homePostalAddress \"$this->homePostalAddress\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
		writelogs("displayName \"$this->displayName\"",__CLASS__.'/'.__FUNCTION__,__FILE__);

		writelogs("Updating uidUser=$this->uidUser",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if($this->uidUser<>null){$evolutionPerson=false;}
		
		if(!$evolutionPerson){
			writelogs("Deleting evolutionPerson attributes",__CLASS__.'/'.__FUNCTION__,__FILE__);
			unset($upd["fileAs"]);
			unset($upd["primaryPhone"]);
			unset($upd["carPhone"]);
			unset($upd["homeFacsimileTelephoneNumber"]);
			unset($upd["otherPhone"]);
			unset($upd["businessRole"]);
			unset($upd["managerName"]);
			unset($upd["assistantName"]);
			unset($upd["assistantPhone"]);
			unset($upd["otherPostalAddress"]);
			unset($upd["mailer"]);
			unset($upd["birthDate"]);
			unset($upd["anniversary"]);
			unset($upd["spouseName"]);
			unset($upd["note"]);
			unset($upd["companyPhone"]);
			unset($upd["callbackPhone"]);
			unset($upd["otherFacsimileTelephoneNumber"]);
			unset($upd["radio"]);
			unset($upd["telex"]);
			unset($upd["tty"]);
			unset($upd["categories"]);
			unset($upd["category"]);
			unset($upd["calendarURI"]);
			unset($upd["freeBusyURI"]);
			unset($upd["gender"]);
			unset($upd["nsICQid"]);
			unset($upd["nsMSNid"]);
			unset($upd["nsYahooid"]);
			}
		
		
		writelogs("modify ". $this->dn,__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		
		
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			writelogs("[ERROR Num $ldap->ldap_last_error_num]:Ldap_modify failed, $ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->buildDN($upd);
			$this->UpDateUnity($upd);
			
		}else{
			writelogs("Success editing \"$this->dn\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		
		$rename="$this->sn $this->givenName";
		writelogs("$this->sn $this->givenName => $rename",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$this->RenameDN($rename);
		
		if(!$this->NoExport){
			$users=new usersMenus();
			if($users->OBM2_INSTALLED){
				if($this->employeeNumber<>null){
					writelogs("[$this->uid]:: Export user $this->sn $this->givenName to obm ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$sock=new sockets();
					$sock->getfile("OBMContactExport:$this->employeeNumber");
				}
			}
			
			if($users->ZARAFA_INSTALLED){
				if($this->employeeNumber<>null){
					writelogs("[$this->uid]:: Export addressBook to zarafa",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$sock=new sockets();
					$sock->getFrameWork("zarafa.php?csv-export=$this->uid");
				}
			}
		}		
		
		
	}
	
	function ContactTocsvArray(){
			$csv["First Name"]=$this->sn;
			$csv["Middle Name"]=$this->null;
			$csv["Last Name"]=$this->givenName;
			$csv["Title"]=$this->title;
			$csv["Suffix"]=$this->null;
			$csv["Initials"]=$this->null;
			$csv["Web Page"]=$this->labeledURI;
			$csv["Gender"]=$this->gender;
			$csv["Birthday"]=$this->birthDate;
			$csv["Anniversary"]=$this->anniversary;
			$csv["Location"]=$this->null;
			$csv["Language"]=$this->null;
			$csv["Internet Free Busy"]=$this->null;
			$csv["Notes"]=$this->note;
			$csv["E-mail Address"]=$this->mail;
			$csv["E-mail 2 Address"]=$this->mozillaSecondEmail[0];
			$csv["E-mail 3 Address"]=$this->mozillaSecondEmail[1];
			$csv["Primary Phone"]=$this->telephoneNumber;
			$csv["Home Phone"]=$this->homePhone;
			$csv["Home Phone 2"]=$this->null;
			$csv["Mobile Phone"]=$this->mobile;
			$csv["Pager"]=$this->null;
			$csv["Home Fax"]=$this->Fax;
			$csv["Home Address"]=$this->homePostalAddress;
			$csv["Home Street"]=$this->null;
			$csv["Home Street 2"]=$this->null;
			$csv["Home Street 3"]=$this->null;
			$csv["Home Address PO Box"]=$this->null;
			$csv["Home City"]=$this->null;
			$csv["Home State"]=$this->null;
			$csv["Home Postal Code"]=$this->null;
			$csv["Home Country"]=$this->null;
			$csv["Spouse"]=$this->spouseName;
			$csv["Children"]=$this->null;
			$csv["Manager's Name"]=$this->managerName;
			$csv["Assistant's Name"]=$this->assistantName;
			$csv["Referred By"]=$this->null;
			$csv["Company Main Phone"]=$this->telephoneNumber;
			$csv["Business Phone"]=$this->telephoneNumber;
			$csv["Business Phone 2"]=$this->null;
			$csv["Business Fax"]=$this->facsimileTelephoneNumber;
			$csv["Assistant's Phone"]=$this->null;
			$csv["Company"]=$this->o;
			$csv["Job Title"]=$this->null;
			$csv["Department"]=$this->ou;
			$csv["Office Location"]=$this->null;
			$csv["Organizational ID Number"]=$this->null;
			$csv["Profession"]=$this->null;
			$csv["Account"]=$this->employeeNumber;
			$csv["Business Address"]=$this->postalAddress;
			$csv["Business Street"]=$this->null;
			$csv["Business Street 2"]=$this->null;
			$csv["Business Street 3"]=$this->null;
			$csv["Business Address PO Box"]=$this->postOfficeBox;
			$csv["Business City"]=$this->l;
			$csv["Business State"]=$this->st;
			$csv["Business Postal Code"]=$this->postalCode;
			$csv["Business Country"]=$this->c;
			$csv["Other Phone"]=$this->null;
			$csv["Other Fax"]=$this->null;
			$csv["Other Address"]=$this->null;
			$csv["Other Street"]=$this->null;
			$csv["Other Street 2"]=$this->null;
			$csv["Other Street 3"]=$this->null;
			$csv["Other Address PO Box"]=$this->null;
			$csv["Other City"]=$this->null;
			$csv["Other State"]=$this->null;
			$csv["Other Postal Code"]=$this->null;
			$csv["Other Country"]=$this->null;
			$csv["Callback"]=$this->null;
			$csv["Car Phone"]=$this->null;
			$csv["ISDN"]=$this->null;
			$csv["Radio Phone"]=$this->null;
			$csv["TTY/TDD Phone"]=$this->null;
			$csv["Telex"]=$this->null;
			$csv["User 1"]=$this->null;
			$csv["User 2"]=$this->null;
			$csv["User 3"]=$this->null;
			$csv["User 4"]=$this->null;
			$csv["Keywords"]=$this->null;
			$csv["Mileage"]=$this->null;
			$csv["Hobby"]=$this->null;
			$csv["Billing Information"]=$this->null;
			$csv["Directory Server"]=$this->null;
			$csv["Sensitivity"]=$this->null;
			$csv["Priority"]=$this->null;
			$csv["Private"]=$this->null;
			$csv["Categories"]=$this->null;
			return $csv;
		
	}
	
	
	
	function UpDateUnity($upd){
		$ldap=new clladp();
		while (list ($num, $ligne) = each ($upd) ){
			$newarray[$num]=$ligne;
			if(!$ldap->Ldap_modify($this->dn,$newarray)){
				writelogs("ERROR => $num",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
			unset($newarray);
		}
		
	}
	
	function buildDN($array){
			$conf="dn: $this->dn";
			
					if(is_array($array)){
   				 	while (list ($num, $ligne) = each ($array) ){
	   					 if(is_array($ligne)){
	   					 	while (list ($a, $b) = each ($ligne) ){
	   							$conf=$conf . "$num: $b\n"; 		
	   					 	}
	   					 }
	   					}
	   			 }
	   writelogs("$conf",__CLASS__.'/'.__FUNCTION__,__FILE__);  
		
	}
	
	function RenameDN($cn){
		$actualdn=$this->dn;
		if(preg_match('#cn=(.+?),(.+)#',$actualdn,$re)){
			$branch=$re[2];
		}
		$newdn="cn=$cn";
		$newdn2="$newdn,$branch";
		$ldap=new clladp();
		if($ldap->ExistsDN($newdn2)){return null;}
		writelogs("Rename $actualdn to $newdn",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		
		
		if(!$ldap->Ldap_rename_dn($newdn,$actualdn,$branch)){
			writelogs("Rename failed $ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		
		
	}
	
	function DeleteAttribute($attributeName,$index=0){
		
		$dn=$this->dn;
		if(!isset($this->attributes[$attributeName][$index])){$this->attributes[$attributeName][$index]=null;}
		if($this->attributes[$attributeName][$index]<>null){
			writelogs("Delete attribute in \"$dn\" $attributeName ({$this->attributes[$attributeName][$index]})",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$upd["$attributeName"][0]=$this->attributes[$attributeName][$index];
			$ldap=new clladp();
			$ldap->Ldap_del_mod($dn,$upd);
		}
		
	}
	
	function mozillaSecondEmailAdd($email){
		
		
		$upd["mozillaSecondEmail"]=$email;
		$ldap=new clladp();
		$uid=$ldap->find_users_by_mail($email);
		if(count($uid)>0){
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body("{error_alias_exists} \n\"". implode(",",$uid)."\"");
			exit;
		}
		
		
		return $ldap->Ldap_add_mod($this->dn,$upd);
		
	}
	function mozillaSecondEmailDel($num){
		$upd["mozillaSecondEmail"]=$this->mozillaSecondEmail[$num];
		$ldap=new clladp();
		return $ldap->Ldap_del_mod($this->dn,$upd);
		
	}
	
	function AddMinimal(){
		$ldap=new clladp();
		$time=time();
		if($this->employeeNumber==null){$this->employeeNumber=$time."-". rand(0,10000);}
		$orginal_emplnb=$this->employeeNumber;
		$cn=trim($this->cn);
		if($cn==null){$cn=$this->employeeNumber;}
		
		$this->dn="cn=$cn,ou=$this->uid,ou=People,dc=$this->UseridOU,dc=NAB,$ldap->suffix";
		
		writelogs("Cheking dn \"$this->dn\" does not exists",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$this->BuildDefault();
		if($cn<>$this->cn){$cn=$this->cn;$this->dn="cn=$cn,ou=$this->uid,ou=People,dc=$this->UseridOU,dc=NAB,$ldap->suffix";}
		writelogs("[$cn]: employeeNumber = $this->employeeNumber ($orginal_emplnb)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$contact=new user();
		$upd=array();
		$upd["objectClass"][]="top";
		$upd["objectClass"][]="person";
		$upd["objectClass"][]="organizationalPerson";
		$upd["objectClass"][]="inetOrgPerson";
		$upd["objectClass"][]="evolutionPerson";
		$upd["objectClass"][]="mozillaOrgPerson";
		$upd["objectClass"][]="posixAccount";
		$upd["objectClass"][]="zarafa-contact";
		$upd["cn"][0]=$this->cn;
		$upd["sn"][0]=$this->sn;
		if($this->givenName<>null){$upd["givenName"][0]=$this->givenName;}
		$upd["employeeNumber"][0]=$this->employeeNumber;
		$upd["uidNumber"][0]=$contact->LastUidNumber();
		$upd["gidNumber"][0]=546;
		$upd["uid"][0]=$this->employeeNumber;
		$upd["homeDirectory"][0]="/dev/null";
		//$upd["modifyTimestamp"][0]=date('Y-m-d H:i:s');
		
		
		if(!$ldap->ldap_add($this->dn,$upd) ){
			$this->ERROR=$ldap->ldap_last_error;
			writelogs("$this->ERROR",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return false;
		}else{
			writelogs("Success add $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->LoadContactDN();
		}
		return true;
		
	}
	
function LoadContactDN(){
		$ldap=new clladp();
		$sr =@ldap_search($ldap->ldap_connection,"dc=NAB,$ldap->suffix","(employeeNumber=$this->employeeNumber)");
		if(!$sr){writelogs("Unable to find : $this->employeeNumber",__CLASS__.'/'.__FUNCTION__,__FILE__);return null;}
		$entry_id = ldap_first_entry($ldap->ldap_connection,$sr);
		if(!$entry_id){
			writelogs( "INFOS: bad value \$entry_id: ($entry_id )  find: (uid=$this->employeeNumber) -> aborting function search engine doesn`t found the pattern",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			return null;
		}
		$this->dn=$this->_Get_dn_($this->employeeNumber);
		}		
	
	
	function BuildBranch(){
		
		$ldap=new clladp();
		$dn="dc=NAB,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="organization";
			$upd["objectClass"][]="dcObject";
			$upd["o"][]="NAB";
			$upd["dc"][]="NAB";
			if(!$ldap->ldap_add($dn,$upd)){
				return false;
			}
			unset($upd);
		}
		
		$dn="dc=$this->UseridOU,dc=NAB,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="organization";
			$upd["objectClass"][]="dcObject";
			$upd["o"][]="$this->UseridOU";
			$upd["dc"][]="$this->UseridOU";
			if(!$ldap->ldap_add($dn,$upd)){
				return false;
			}
			unset($upd);
		}
			
		$dn="ou=users,dc=$this->UseridOU,dc=NAB,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="organizationalUnit";
			$upd["ou"]="users";
			if(!$ldap->ldap_add($dn,$upd)){
				return false;
			}
			unset($upd);			
		}
		
		$dn="ou=People,dc=$this->UseridOU,dc=NAB,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="organizationalUnit";
			$upd["ou"]="People";
			if(!$ldap->ldap_add($dn,$upd)){
				return false;
			}
			unset($upd);			
		}	

		$dn="ou=$this->uid,ou=People,dc=$this->UseridOU,dc=NAB,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="organizationalUnit";
			$upd["ou"]="$this->uid";
			if(!$ldap->ldap_add($dn,$upd)){
				return false;
			}
			unset($upd);			
		}		
				
		
	}
	
function draw_jpeg_photos(){
	
	if(strlen($this->jpegPhoto)==0){
		$this->img_identity="img/contact-unknown-user.png";
		return null;
	}
	$uid=md5($this->employeeNumber);
	
	include_once(dirname(__FILE__).'/class.images.inc');
	$jpeg_filename="/opt/artica/share/www/jpegPhoto/{$uid}-origin.tmp";
	
	if(file_exists($jpeg_filename)){unlink($jpeg_filename);}
	
	if(!file_exists($filename)){
		writelogs("Create temporary image $jpeg_filename",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$outjpeg = @fopen($jpeg_filename, "wb");
		@fwrite($outjpeg, $this->jpegPhoto);
		@fclose ($outjpeg);
	}
	
	
	$image=new images($jpeg_filename);
	if($image->mustInstall){
		writelogs("Return to default image $image->missing_module",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->jpegPhotoError=$image->missing_module;
		return null; 
	}
	$jpeg_dimensions=@getimagesize($jpeg_filename);
	$img_type=array(1=>"gif",2=>'jpg',3=>'png',4=>'swf',5=>'psd',6=>'bmp',7=>'tiff',8=>'tiff',9=>'jpc',10=>'jp2',11=>'jpx');
	
	$thumbnail_path="/opt/artica/share/www/jpegPhoto/thumbnail-60-{$uid}.{$img_type[$jpeg_dimensions[2]]}";
	writelogs("Image type={$img_type[$jpeg_dimensions[2]]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
	writelogs("Image Path=$thumbnail_path",__CLASS__.'/'.__FUNCTION__,__FILE__);
	
	
	$this->thumbnail_path=$thumbnail_path;
	
	if($image->thumbnail(60,60,$thumbnail_path)){
		$this->img_identity="images.listener.php?uri=jpegPhoto/thumbnail-60-{$uid}.{$img_type[$jpeg_dimensions[2]]}";
		return null;
	}else{
		$this->img_identity="img/contact-unknown-user.png";
	}
	
	
}	
	
	
	
}
?>