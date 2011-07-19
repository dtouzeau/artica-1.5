<?php
include_once(dirname(__FILE__) . '/class.main_cf.inc');

class domain{
		var $domain_name;
		var $local_file;
		var $_hash;

	function  domain($domain=null){
		$this->domain_name=$domain;
		$maincf=new main_cf();
		$hashpath=basename($maincf->array_hashs["virtual_mailbox_domains"]);
		writelogs("using hash relay_domains=$hashpath",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		$local_file=dirname(__FILE__) . "/conf/$hashpath";
		$this->local_file=$local_file;
		if(!is_file($local_file)){
			writelogs("WARNING:unable to read $local_file",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
			return null;
		}
		
		$this->_hash=$this->GetDomainList();
		
		
	}
	
	function IsLocalDomain($domain){
		if(is_array($this->_hash)){
			reset($this->_hash);
			while (list ($num, $ligne) = each ($this->_hash) ){
				if($ligne["domain"]==$domain){return true;}
			}
		}
		return false;
		
	}
	
	
	function GetDomainList(){
		if($this->local_file==null){
			writelogs("WARNING:local file is empty",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
			return null;}
		$tbl=_readfileArray($this->local_file);
		
		if(is_array($tbl)){
			while (list ($num, $ligne) = each ($tbl) ){
				//writelogs("tbl[$num]=$ligne",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
				if(preg_match('#([a-zA-Z-@0-9\.]+)#i',$ligne,$regs)){
					$hash[$num]["domain"]=$regs[1];
				}
			}
			return $hash;		
		}else{writelogs("INFOS:_readfileArray return no rows",__CLASS__ . "=>". __FUNCTION__ ,__FILE__);}
	}
	
	function Edit(){
		
	}

	function Add(){
	$this->_hash[]["domain"]=$this->domain_name;
	$this->save_file();
	}
	
	function save_file(){
		if(is_array($this->_hash)){
			reset($this->_hash);
			while (list ($num, $ligne) = each ($this->_hash) ){
				if($ligne["domain"]<>null){
					$txt=$txt . $ligne["domain"] . "\tOK\n";
				}
			}
		}
	
		$fp = fopen($this->local_file, "w", 0);
	  	fputs($fp, $txt); #write all of $data to our opened file
	  	fclose($fp); #close the file
	}

	function Delete(){
		if(is_array($this->_hash)){
			while (list ($num, $ligne) = each ($this->_hash) ){
				if($ligne["domain"]==$this->domain_name){
					unset($this->_hash[$num]);
				}
			}
		$this->save_file();
		}
	}
}
	
class alias{
		var $address;
		var $goto;
		var $domain;
		var $created;
		var $modified;
		var $active;
		var $local_file;
		var $_hash;		

	function  alias($address=null){
		//
		
		
		$maincf=new main_cf();
		$hashpath=basename($maincf->array_hashs["virtual_alias_maps"]);
		writelogs("using hash virtual_alias_maps=$hashpath",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		$local_file=dirname(__FILE__) . "/conf/$hashpath";
		$this->local_file=$local_file;
		if(!is_file($local_file)){
			writelogs("unable to read $local_file",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
			return null;
		}		
		
		$this->Load_hash();
		if($address<>null){
			$this->address=$address;
			$this->Load();
		}
		
		
	}
	
	function Load_hash(){
		$datas=_readfile($this->local_file);
		writelogs("class alias length->" . strlen($datas),__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		if(preg_match_all('#([@a-z_\.-0-9A-Z]+)\s+([@a-z_\.-0-9A-Z]+)#i',$datas,$regs)){
			while (list ($num, $ligne) = each ($regs[1])){
				$this->_hash[$ligne]=$regs[2][$num];
			
			}
		}else{writelogs("WARNING:class alias preg_match return null...",__CLASS__ . "=>" . __FUNCTION__,__FILE__);}
		
	}
	
	function Delete_all($domain){
			if(!is_array($this->_hash)){writelogs("INFOS:hash is null!!!",__CLASS__ . "=>" . __FUNCTION__,__FILE__);return null;}
			while (list ($num, $ligne) = each ($this->_hash)){
				if (strpos($num,$domain)>0){
					unset($this->_hash[$num]);
				}
			}
			$this->save_file();
		
	}
	

	function Load(){
		$tbl=explode('@',$this->address);
		$this->goto=$this->_hash[$this->address];
		$this->domain=$tbl[1];
		}

	
	function Edit(){
		$this->_hash[$this->address]=$this->goto;
		$this->save_file();
		}
		
	function save_file(){
		
		writelogs("alias:save_file ->" . count($this->_hash) . " entries for $this->local_file");
		if(is_array($this->_hash)){
			reset($this->_hash);
			while (list ($num, $ligne) = each ($this->_hash) ){
				if($ligne<>null){
					$txt=$txt . "$num\t$ligne\n";
				}
			}
		}
		
		$fp =@fopen($this->local_file, "w", 0);
		if(!$fp){writelogs('WARNING:unable to write ' . $this->local_file,__CLASS__ . "=>" . __FUNCTION__,__FILE__);}
	  	fputs($fp, $txt);
	  	fclose($fp);
	}		
		

	function Add(){
		$this->_hash[$this->address]=$this->goto;
		$this->save_file();
		}

	function Delete(){
		unset($this->_hash[$this->address]);
		$this->save_file();
		}
		
	function hash_list_from_domain($domain){
		if(!is_array($this->_hash)){
			writelogs(" INFOS:hash is null!!!",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
			return null;}
			
			
			while (list ($num, $ligne) = each ($this->_hash)){
				if(strpos($num,$domain)>0){
					$hash[]=array('address'=>$num,'goto'=>$ligne,'domain'=>$domain);
				}
				
			}
			return $hash;
		
	}

}
	
	class transport_maps{
		var $domain;
		var $goto;
		var $local_file;
		var $array_file;

	function  transport_maps($domain=null){
		$this->domain=$domain;
		$maincf=new main_cf();
		writelogs("hash transport_maps={$maincf->array_hashs["transport_maps"]}",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		$hashpath=basename($maincf->array_hashs["transport_maps"]);
		writelogs("using hash transport_maps=$hashpath",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		$local_file=dirname(__FILE__) . "/conf/$hashpath";
		$this->local_file=$local_file;
		if(!is_file($local_file)){
			writelogs("unable to read $local_file",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
			return null;
		}
		
		$datas=_readfile($this->local_file);
		if(preg_match_all('#([a-z_\.-0-9A-Z]+)\s+(smtp:[a-z0-9A-Z\/_\.]+)#is',$datas,$regs)){
			while (list ($num, $ligne) = each ($regs[1]) ){
				$this->array_file[$ligne]=$regs[2][$num];
			}
		}

		
		if($domain<>null){
			$this->domain=$domain;
			$this->Load();
		}
	}

	function Load(){
		$this->goto=$this->array_file[$this->domain];
		}

	
	function Edit(){
		$this->array_file[$this->domain]=$this->goto;
		$this->save_file();
		}

	function Add(){
		$this->array_file[$this->domain]=$this->goto;
		$this->save_file();
		}

	function Delete(){
		{writelogs("Delete $this->domain",__CLASS__ . "=>" . __FUNCTION__,__FILE__);}
		unset($this->array_file[$this->domain]);
		$this->save_file();
		}
		
	function save_file(){
		if(is_array($this->array_file)){
			reset($this->array_file);
			while (list ($num, $ligne) = each ($this->array_file) ){
				if($ligne<>null){
					$txt=$txt . "$num\t$ligne\n";
				}
			}
		}
	
		$fp = @fopen($this->local_file, "w", 0);
		if(!$fp){writelogs('unable to write ' . $this->local_file,__CLASS__ . "=>" . __FUNCTION__,__FILE__);}
	  	fputs($fp, $txt); #write all of $data to our opened file
	  	fclose($fp); #close the file
	}		

	}

	function _readfileArray($filepath){
		if (!$fp = @fopen($filepath,"r")) {
		writelogs('unable to read ' . $filepath,__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		return null;
		}

		else {
		while(!feof($fp)) {
			$line=fgets($fp,255);
			if($line[0]<>'#'){$array[]=$line;}
	
		}
		fclose($fp); 
		return $array;
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
	
class MailBox{
	var $username;
	var $password;
	var $local_file;
	var $array_file;
	var $domain;
	var $maildir;
	var $quota;
	
	function Mailbox($user=null){
		$maincf=new main_cf();
		writelogs("hash virtual_mailbox_maps={$maincf->array_hashs["virtual_mailbox_maps"]}",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		
		$hashpath=basename($maincf->array_hashs["virtual_mailbox_maps"]);
		writelogs("using hash virtual_mailbox_maps=$hashpath",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		$local_file=dirname(__FILE__) . "/conf/$hashpath";
		$this->local_file=$local_file;
		if(!is_file($local_file)){
			writelogs("unable to read $local_file",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
			return null;
		}
		
		$datas=_readfile($this->local_file);
		if(preg_match_all('#([a-z_\.-0-9A-Z\@]+)\s+([a-z0-9A-Z\/_\.]+)#is',$datas,$regs)){
			while (list ($num, $ligne) = each ($regs[1]) ){
				$this->array_file[$ligne]=$regs[2][$num];
			}
		}
		
		
	}
	
	function getPathFromUser($username){
		if (count($this->array_file)==0){
			writelogs("INFOS:global array is empty...",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		}
		return $this->array_file[$username];
		}
		

	function IsExists($username){
		if($this->array_file[$username]==null){return false;}else{return true;}
		}
		
	function Edit(){
		$this->maildir=$this->addslash($this->maildir);
		$this->array_file[$this->username]=$this->maildir;
		$this->save_file_cf();
		$this->save_db();
	}
	
	function save_file_cf(){
		if(is_array($this->array_file)){
			reset($this->array_file);
			while (list ($num, $ligne) = each ($this->array_file) ){
				if($ligne<>null){
					$ligne=$this->addslash($ligne);
					$txt=$txt . "$num\t$ligne\n";
				}
			}
		}
	
		$fp = @fopen($this->local_file, "w", 0);
		if(!$fp){writelogs('WARNING:unable to write ' . $this->local_file,__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		echo "<H1>unable to write ' . $this->local_file</H1>";
		return null;}
	  	fputs($fp, $txt); 
	  	fclose($fp);
	}
	
	function save_db(){
		$md5f=dirname(__FILE__) . '/userdb/' . md5($this->username) . '.tmp';
		writelogs('INFOS:saving in queue ' . $md5f,__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		if(!is_numeric($this->quota)){$this->quota=0;}
		if($this->password==null){
			$txt="<USER>$this->username</USER><MAILDIR>$this->maildir</MAILDIR><DOMAIN>$this->domain</DOMAIN><QUOTA>$this->quota</QUOTA>";
		}else{
			$txt="<USER>$this->username</USER><MAILDIR>$this->maildir</MAILDIR><DOMAIN>$this->domain</DOMAIN><PASSWORD>$this->password</PASSWORD><QUOTA>$this->quota</QUOTA>";
		}
		writelogs('INFOS: write ' . $md5f,__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		$fp = @fopen($md5f, "w", 0);
		if(!$fp){writelogs('WARNING:unable to write ' . $md5f,__CLASS__ . "=>" . __FUNCTION__,__FILE__);}
	  	fputs($fp, $txt); 
	  	fclose($fp);
		
	}
	

	

	function Add(){
		if($this->IsExists($this->username)==true){
			$this->Edit();return null;
		}
		
		$this->maildir=$this->addslash($this->maildir);
		$this->array_file[$this->username]=$this->maildir;
		$this->save_file_cf();
		$this->save_db();
		}
		
		
	function addslash($path){
		if(substr($path,strlen($path)-1,1)<>'/'){
			writelogs('INFOS::Adding slash to the end -> ' . $path);
			return $path . '/';
		}else{return $path;}
	}
	
	
	
	
	
}
?>	
	