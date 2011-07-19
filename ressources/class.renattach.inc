<?php

include_once(dirname(__FILE__) . '/class.ldap.inc');

class renattach{
	var $group;
	var $dn;
	var $ou;
	var $badlist_array=array();
	var $SourceData;
	var $arrayDatas;
	var $knowExtList;
	
	
	function renattach($group,$ou){
		$this->group=$group;
		$this->ou=$ou;
		$this->GetSourceDatas();
		$this->LoadAttachTable();
		
	}
	
	function DefaultSettings(){
		if($this->arrayDatas["delete_exe"]==null){$this->arrayDatas["delete_exe"]="no";}
		if($this->arrayDatas["kill_exe"]==null){$this->arrayDatas["kill_exe"]="no";}
		if($this->arrayDatas["search_zip"]==null){$this->arrayDatas["search_zip"]="no";}
		if($this->arrayDatas["pass_contentid"]==null){$this->arrayDatas["pass_contentid"]="no";}
		if($this->arrayDatas["full_rename"]==null){$this->arrayDatas["full_rename"]="yes";}
		if($this->arrayDatas["use_syslog"]==null){$this->arrayDatas["use_syslog"]="yes";}
		if($this->arrayDatas["generic_name"]==null){$this->arrayDatas["generic_name"]="bad_filename";}
		if($this->arrayDatas["new_extension"]==null){$this->arrayDatas["new_extension"]="bad";}
		if($this->arrayDatas["new_mime_type"]==null){$this->arrayDatas["new_mime_type"]="application/unknown";}
		if($this->arrayDatas["subj_deleted"]==null){$this->arrayDatas["subj_deleted"]="#";}
		if($this->arrayDatas["subj_renamed"]==null){$this->arrayDatas["subj_renamed"]="#";}
		if($this->arrayDatas["add_subject"]==null){$this->arrayDatas["add_subject"]="#";}
		
		$allowed_key=array(
			"use_syslog"=>"yes",
			"search_zip"=>"yes",
			"delete_exe"=>"yes",
			"kill_exe"=>"yes",
			"pass_contentid"=>"yes",
			"full_rename"=>"yes",
			"generic_name"=>"yes",
			"delete_exe"=>"yes",
			"new_extension"=>"yes",
			"new_mime_type"=>"yes",
			"subj_deleted"=>"yes",
			"subj_renamed"=>"yes",
			"add_subject"=>"yes",
			"subj_exec"=>"yes",
			"subj_banned"=>"yes",
			"generic_name"=>"yes",
			"htmlwarn_pos"=>"yes",
			"banned_files"=>"yes",
			"goodlist"=>"yes",
			"badlist"=>"yes",
			);
			reset($this->arrayDatas);
			while (list ($num, $val) = each ($this->arrayDatas) ){if($allowed_key[$num]<>"yes"){unset($this->arrayDatas[$num]);}}
			reset($this->arrayDatas);
	
		}
	
	function GetSourceDatas(){
		$ldap=new clladp();
		$this->dn="cn=$this->group,cn=forbidden_attachments,ou=$this->ou,$ldap->suffix";
	
	$sr =@ldap_read($ldap->ldap_connection,$this->dn,'objectClass=*',array('FiltersExtensionsSettings'));
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
			
			
			$this->SourceData=$hash[0]["filtersextensionssettings"][0];
			$this->ParseSourceDatas();
		}
		
		
	}
	
	function LoadAttachTable(){
		$datas=file_get_contents(dirname(__FILE__). '/databases/extensions.list.db');
		$tbl=explode("\n",$datas);
		while (list ($num, $val) = each ($tbl) ){
			$tbl1=explode(';',$val);
			if($this->knowExtList[strtoupper($tbl1[0])]<>null){$this->knowExtList[strtoupper($tbl1[0])]=$this->knowExtList[strtoupper($tbl1[0])] . " or " . $tbl1[1];}else{
			$this->knowExtList[strtoupper($tbl1[0])]=$tbl1[1];
			}
		}
		
	}
	
	function ParseSourceDatas(){
		$tbl=explode("\n",$this->SourceData);
		if(!is_array($tbl)){
			writelogs("No datas to split !",__CLASS__ . "/" . __FUNCTION__,__FILE__);
			return null;}
			
			
		while (list ($num, $val) = each ($tbl) ){
			if(preg_match('#([a-zA-Z0-9\s_]+)=([a-zA-Z0-9\-\s,/\#_]+)#',$val,$re)){
				$this->arrayDatas[trim($re[1])]=trim($re[2]);
			}
		}
		
		if($this->arrayDatas["badlist"]<>null){
			writelogs("Explode ->{$this->arrayDatas["badlist"]} ",__CLASS__ . "/" . __FUNCTION__,__FILE__);
			$this->badlist_array=explode(',',$this->arrayDatas["badlist"]);}
		
		if($this->arrayDatas["subj_deleted"]=="#"){$this->arrayDatas["subj_deleted"]="";}
		if($this->arrayDatas["subj_renamed"]=="#"){$this->arrayDatas["subj_renamed"]="";}
		if($this->arrayDatas["add_subject"]=="#"){$this->arrayDatas["add_subject"]="";}			
			
	}
	
	
	function add_badlist($ext){
		$this->badlist_array[]=$ext;
		
	}
	
	function del_badlist($ext){
		
		reset($this->badlist_array);
		while (list ($num, $val) = each ($this->badlist_array) ){
			if(str)
			if(strtoupper($ext)==strtoupper($val)){
				if(strpos($val,'/')>0){$tblO=explode("/",$val);$val=$tblO[0];}
						
				unset($this->badlist_array[$num]);}
		}	
	}
	
	function Save(){
		writelogs("dn=$this->dn",__CLASS__ . "/" . __FUNCTION__,__FILE__);
		$this->DefaultSettings();
		if($this->SourceData==null){
			writelogs("add default value to $this->dn",__CLASS__ . "/" . __FUNCTION__,__FILE__);
			$upd["FiltersExtensionsSettings"][0]="NONE";
			$ldap=new clladp();
			$ldap->Ldap_add_mod($this->dn,$upd);
			}		
		//badlist
		if(is_array($this->badlist_array)){$this->arrayDatas["badlist"]=implode(",",$this->badlist_array);$this->arrayDatas["badlist"]=$this->arrayDatas["badlist"];}
		if(!is_array($this->arrayDatas)){return null;}
		
		

		
		
		while (list ($num, $val) = each ($this->arrayDatas) ){
			if(trim($val)<>null){
				$ini=$ini . "$num = $val\n";
				}
			}
			
			writelogs("save config to $this->dn",__CLASS__ . "/" . __FUNCTION__,__FILE__);
			$upd["FiltersExtensionsSettings"][0]=$ini;
			$ldap=new clladp();
			$ldap->Ldap_modify($this->dn,$upd);
		}
		
		
	
	
	
	
	
	
}