<?php
include_once(dirname(__FILE__) . '/class.main_cf.inc');
include_once(dirname(__FILE__) . '/class.ldap.inc');
include_once(dirname(__FILE__) . "/class.sockets.inc");

class main_filter_extensions{
	var $array_extension;
	var $local_file;
	
	function main_filter_extensions(){
		$maincf=new main_cf();
		$hashpath=basename($maincf->array_regexp["mime_header_checks"]);
		writelogs("using rexep mime_header_checks=$hashpath",__FUNCTION__,__FILE__);
		$local_file=dirname(__FILE__) . "/conf/$hashpath";
		$this->local_file=$local_file;
		if(!is_file($local_file)){
			writelogs("unable to read $local_file",__FUNCTION__,__FILE__);
			return null;
		}
		$this->Load();		
		
			
	}
	
	
		function Load(){
			$datas=$this->_readfile($this->local_file);
			$datas=str_replace('/^\s*Content-(Disposition|Type).*name\s*=\s*"?(.+\.(','',$datas);
			$datas=str_replace( '))"?\s*$/','',$datas);
			$datas=str_replace( 'REJECT Attachment type not allowed. File "$2" has the unacceptable extension "$3"','',$datas);
			writelogs("main_filter_extensions:: -> $datas");
			$this->array_extension=explode('\|',$datas);
			while (list ($num, $ligne) = each ($this->array_extension) ){
				$ligne=trim($ligne);
				$this->array_extension[$num]=$ligne;
			}
			
		}

		function save_file(){
			
			$ext= implode("|", $this->array_extension);
			writelogs("main_filter_extensions -> $ext");
			$ext=str_replace("\n",'',$ext);
			$commandline='/^\s*Content-(Disposition|Type).*name\s*=\s*"?(.+\.(' . $ext . '))"?\s*$/
			REJECT Attachment type not allowed. File "$2" has the unacceptable extension "$3"'; 
			$fp =@fopen($this->local_file, "w", 0);
			if(!$fp){writelogs('unable to write ' . $this->local_file,__FUNCTION__,__FILE__);}
	  		fputs($fp, $commandline);
	  		fclose($fp);
			
		}
		
		
		function addExtension($extension){
			$this->array_extension[]=trim($extension);
			$this->save_file();
			
		}
		function DeleteExtension($num){
			unset($this->array_extension[$num]);
			$this->save_file();
			
		}		
		
		function _readfile($filepath){
		$file = @fopen($filepath, "r");
		if ($file<>null){
				return @fread($file,filesize($filepath));
				fclose($file); 
				exit;
				}
		}
		
		
		
	}
	
class main_header_check{
	var $main_table;
	var $array_subjects;
	var $array_headers_values;
	var $headers_regex_table;
	var $array_ldap_source;
	var $array_ldap_source_table;
	var $array_rules;
	var $array_from;
	var $file_content;
	var $local_file;
	var $array_human_actions=array(
	""=>"{select}","DISCARD"=>'{discard}',"HOLD"=>'{hold}',"PREPEND"=>'{prepend_text}',
	"REDIRECT"=>'{redirect_to_mail}',
	"REPLACE"=>'{replace_line}',
	"REJECT"=>'{reject}',
	"WARN"=>'{warn}');
	
	
	
	function main_header_check(){
		$datas_headers=explode("\n",file_get_contents(dirname(__FILE__) . "/databases/db.headers.txt"));
		while (list ($num, $ligne) = each ($datas_headers) ){
			$this->array_headers_values[$ligne]=$ligne;
		}
		
		asort($this->array_headers_values);
		$this->LoadHashRules();
			
		
		
		
	}
	
	
	function load_ldap_rules(){
		$ldap=new clladp();
		$dn="cn=artica,$ldap->suffix";
		$filter=array("PostfixHeadersRegex");
		$sr =@ldap_read($ldap->ldap_connection,"cn=artica,$ldap->suffix",'objectClass=ArticaSettings',$filter);
		if(!$sr){return null;}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		
		for($i=0;$i<$hash[0][strtolower("PostfixHeadersRegex")]["count"];$i++){
			$this->main_table[$i]=$hash[0][strtolower("PostfixHeadersRegex")][$i];
			
		}
	}
	
	//db.headers.txt
	function LoadHashRules(){
		
		$this->load_ldap_rules();
		
		
		
	}
	
	function SaveToDaemon(){
		
		if(!is_array($this->main_table)){return null;}
		reset($this->main_table);
		writelogs("regex table has " . count($this->main_table) . " rows",__CLASS__ . '/'.__FUNCTION__ ,__FILE__);
		
		while (list ($num, $ligne) = each ($this->main_table) ){
			$commandline=$commandline . $ligne . "\n";
		}
		
			$ldap=new clladp();	
			$dn="cn=PostfixFilesStorage,cn=artica,$ldap->suffix";
			$upd["PostfixTimeCode"][]=date('YmdhIs');
			$upd["PostFixHeadersRegexFile"][]=$commandline;
			if($ldap->Ldap_modify($dn,$upd)){
					$sock=new sockets();
	  				$sock->getfile('SavePostfixHeaderCheck');
			}
		}
		
	
	function Trasnformtohuman($val){
		$val=str_replace('\.','.',$val);	
		$val=str_replace('\@','@',$val);
		return $val;	
	}
	
	function TransformToregex($val){
		$val=str_replace('.','\.',$val);
		$val=str_replace('@','\@',$val);
		return $val;
	}
	
	
	function delete_from($number){
		unset($this->array_from[$number]);
		$this->save_file();
	}
	
	function delete_subject($number){
		unset($this->array_subjects[$number]);
		$this->save_file();
	}	
	
	function fill_From(){
		$pattern='#From:\(\.\*\)(.+?)/\s+REJECT ([a-zA-Z-_ ,\'\.]+)#s';
		if(preg_match_all($pattern,$this->file_content,$regs)){
			while (list ($num, $ligne) = each ($regs[1]) ){
				$this->array_from[]=array("PATTERN"=>$ligne,"EXPLAIN"=>$regs[2][$num]);
			}
		}
		
	}	
	
	
	function fill_subjects(){
		$pattern='#Subject:\(\.\*\)(.+?)/\s+REJECT ([a-zA-Z-_ ,\'\.]+)#s';
		if(preg_match_all($pattern,$this->file_content,$regs)){
			while (list ($num, $ligne) = each ($regs[1]) ){
				$this->array_subjects[]=array("PATTERN"=>$ligne,"EXPLAIN"=>$regs[2][$num]);
			}
		}
		
	}
	
	function _readfile($filepath){
		$file = @fopen($filepath, "r");
		if ($file<>null){
				return @fread($file,filesize($filepath));
				fclose($file); 
				exit;
				}
		}
		
		
	function add_subject_rule($rule,$postfix_error){
		writelogs(" add_subject_rule($rule,$postfix_error)");
		$rule=$this->TransformToregex($rule);
		writelogs("main_header_check=>rule=$rule");
		$this->array_subjects[]=array("PATTERN"=>$rule,"EXPLAIN"=>$postfix_error);
		$this->save_file();
		
	}
	
	function add_mailfrom_rule($rule,$postfix_error){
		writelogs(" add_mailfrom_rule($rule,$postfix_error)");
		$rule=$this->TransformToregex($rule);
		writelogs("main_header_check=>rule=$rule");
		$this->array_from[]=array("PATTERN"=>$rule,"EXPLAIN"=>$postfix_error);
		$this->save_file();
		
	}	
	
	function save_file(){
		if(is_array($this->array_subjects)){
			while (list ($num, $ligne) = each ($this->array_subjects) ){
				if( $ligne["EXPLAIN"]==null){ $ligne["EXPLAIN"]="rejected match";}
				$text=$text .'/Subject:(.*)' . $ligne["PATTERN"] . "/\tREJECT " . $ligne["EXPLAIN"] . "\n";
				}
			}else{writelogs('array_subjects is empty ' . $this->local_file,__CLASS__ ."=>".__FUNCTION__ ,__FILE__);}
			
		if(is_array($this->array_from)){
			while (list ($num, $ligne) = each ($this->array_from) ){
				if( $ligne["EXPLAIN"]==null){ $ligne["EXPLAIN"]="rejected match";}
				$text=$text .'/From:(.*)' . $ligne["PATTERN"] . "/\tREJECT " . $ligne["EXPLAIN"] . "\n";
				}
			}	
		
		
			$fp =@fopen($this->local_file, "w", 0);
			if(!$fp){writelogs('unable to write ' . $this->local_file,__FUNCTION__,__FILE__);}
	  		fputs($fp, $text);
	  		fclose($fp);
		}
		
	

function import_examples($hasHash=false){
	$datas=explode("\n",file_get_contents(dirname(__FILE__) . "/databases/examples.header_checks.db"));
	if(!is_array($datas)){return null;}
	while (list ($num, $ligne) = each ($datas) ){
		$lin=$this->ParseRegexLine($ligne);
		if(is_array($lin)){
			if($hasHash){$arr[]=$ligne;}else{$this->saverule($ligne);}
		}
		
		
	}
	
	return $arr;

	
}


function ParseRegexLine($line){
	if(substr($line,0,1)=='#'){return null;}
	if(preg_match('#/(.+?)/(.*)#',$line,$re)){
		$regex=$re[1];
		$val=$re[2];
		if(preg_match('#(REDIRECT|DISCARD|HOLD|PREPEND|REPLACE|REJECT|WARN|DROP)(.*)#i',$val,$li)){
			$action=$li[1];
			$infos=$li[2];
			return array(trim($regex),trim($action),trim($infos));
		}else{
			writelogs("unable to parse '$val' in '$line'",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}	
	}
}


function saverule($rule_string,$id=null){
	if($rule_string==null){
		writelogs("Warning, no datas in string...",__CLASS__.'/'.__FUNCTION__,__FILE__);
		return false;
	}
	
	
	$ldap=new clladp();
	$dn="cn=artica,$ldap->suffix";
	if(!is_numeric($id)){
		writelogs("Adding new fresh regex rule wih no id $rule_string",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$upd["PostfixHeadersRegex"]=$rule_string;
		if(!$ldap->Ldap_add_mod($dn,$upd)){
			echo "postfix_regex:failed $ldap->ldap_last_error\nFile:".__FILE__."\nLine:".__LINE__;
			return false;
		}
		return true;
	}else{
		
		writelogs("saving pattern {$this->main_table[$id]} to \"$rule_string\"",__CLASS__ . '/'.__FUNCTION__,__FILE__);
		$this->main_table[$id]=$rule_string;
		reset($this->main_table);
		while (list ($num, $ligne) = each ($this->main_table) ){
			$upd["PostfixHeadersRegex"][]=$ligne;
		}
		if(!$ldap->Ldap_modify($dn,$upd)){echo "postfix_regex:failed $ldap->ldap_last_error\nFile:".__FILE__."\nLine:".__LINE__;return false;}
		return true;
	}
}
	function delete_rule($id){
		$ldap=new clladp();
			$dn="cn=artica,$ldap->suffix";
			writelogs("delete pattern {$this->main_table[$id]}",__CLASS__ . '/'.__FUNCTION__,__FILE__);		
			$upd["PostfixHeadersRegex"]=$this->main_table[$id];
			$ldap->Ldap_del_mod($dn,$upd);
	}
	
	
	function delete_all_rules(){
		reset($this->main_table);
		while (list ($num, $ligne) = each ($this->main_table) ){
			$this->delete_rule($num);
		}
		
	}

}
?>	
