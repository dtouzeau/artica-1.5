<?php
include_once(dirname(__FILE__) . '/class.ldap.inc');
include_once(dirname(__FILE__) . '/class.ini.inc');

class procmail{
	var $header_database=array();
	var $array_rule=array();
	var $action_array=array(0=>"{deliver_imap}",1=>"{delete_mail}",2=>"{put_user_quarantine}");
	var $condition_array=array(1=>'{match}',0=>'{not_match}');
	var $cyr_deliver_path;
	var $procmail_quarantine_path;
	function procmail(){
		$this->load_header_database();
		$this->Loadrules();
		$settings_inc=dirname(__FILE__) . "/settings.inc";
		include($settings_inc);
		$this->cyr_deliver_path=$_GLOBAL["cyr_deliver_path"];
		$this->procmail_quarantine_path=$_GLOBAL["procmail_quarantine_path"];
		$this->AutomaticConfig=$_GLOBAL["AutomaticConfig"];
	}
	
	function load_header_database(){
			$datas_headers=explode("\n",file_get_contents(dirname(__FILE__) . "/databases/db.headers.txt"));
		while (list ($num, $ligne) = each ($datas_headers) ){
			$this->header_database[$ligne]=$ligne;
		}
		
		asort($this->header_database);
	}
	
	function save_rules(){
		$ini=new Bs_IniHandler();
		$ini->_params=$this->array_rule;
		$datas=$ini->toString();
		$ldap=new clladp();
		$hash=$ldap->ArticaDatas();
		$array_update["ProcMailRcConfig"][0]=$datas;
		if($hash["ProcMailRcConfig"]==null){
			$ldap->Ldap_add_mod('cn=artica,'.$ldap->suffix,$array_update);
		}else{
			$ldap->Ldap_modify('cn=artica,'.$ldap->suffix,$array_update);
		}
			
		if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;return false;}
		if($this->AutomaticConfig==true){$this->SaveItToDisk();}
		return true;
	}
	
	function Loadrules(){
		$ldap=new clladp();
		$hash=$ldap->ArticaDatas();
		$ini=new Bs_IniHandler();
		$ini->loadString($hash["ProcMailRcConfig"]);
		$this->array_rule=$ini->_params;
	}
	
	function SaveItToDisk(){
		include_once(dirname(__FILE__) . '/class.sockets.inc');
		while (list ($num, $ligne) = each ($this->array_rule) ){
			$file=$file . $this->TransformLineToProcmail($ligne);
			
		}
		$header="DELIVERTO=\"$this->cyr_deliver_path\"\nLOGFILE=\"/var/log/procmail/procmail.log\"\nUSERINBOX=\"$"."DELIVERTO -e -a $"."USER -m user/$"."USER\"\nVERBOSE=yes\nLOGABSTRACT=all\n\n";
		$file=$header.$file . "\n:0 w\n| $" ."USERINBOX";
		$tempfile=dirname(__FILE__) . "/conf/procmailrc";
		$fp =@fopen($tempfile, "w", 0);
			if(!$fp){writelogs('unable to write ' . $this->local_file,__FUNCTION__,__FILE__);return null;}
	  		fputs($fp, $file);
	  		fclose($fp);
	  		$sock=new sockets();
	  		$sock->getfile('SaveProcMailRules:'. $tempfile);		
		}
	function TransformLineToProcmail($line){
		$pattern=$line["string"];
		$header_name=$line["header_name"];
		$rule_name=$line["name"];
		$rule_action=$line["action"];
		
		if($line["condition"]==0){$condition="!";}else{$condition=null;}
		
		$pattern=str_replace('.','\.',$pattern);
		$pattern=str_replace('*','.*',$pattern);
		$pattern=str_replace('[','\[',$pattern);
		$pattern=str_replace(']','\]',$pattern);

		if($header_name<>null){
			switch ($header_name) {
				case 'Subject':$line="* ^$header_name:.*$pattern";break;
				default:$line="* ^$header_name: $pattern";break;
			}
			
		}else{
			$line="* $pattern\n";
		}
		
		switch ($rule_action) {
			case '0':$action='| $USERINBOX';break;
			case '1':$action ='/dev/null';break;
			case '2':$action=$this->procmail_quarantine_path.'/$USER/';break;
			default:$action='| $USERINBOX';break;
		}
		
		return "#$rule_name\n:0\n$line\n$action\n";
		
	}
	
	
}