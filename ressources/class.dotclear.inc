<?php
include_once(dirname(__FILE__).'/class.mysql.inc');
class dotclear{
	
	var $DotClearHttpListenPort=82;
	var $DotClearExternalWebUri;
	var $EnableDotClearHTTPService;
	var $database="artica_backup";
	
	function dotclear(){
		$sock=new sockets();
		$this->DotClearHttpListenPort=$sock->GET_INFO("DotClearHttpListenPort");
		$this->DotClearExternalWebUri=$sock->GET_INFO("DotClearExternalWebUri");	
		$this->EnableDotClearHTTPService=$sock->GET_INFO("EnableDotClearHTTPService");	
		$this->DotClearExternalAdminUri=$sock->GET_INFO("DotClearExternalAdminUri");
		if($this->DotClearHttpListenPort==null){$this->DotClearHttpListenPort=82;}
		if($this->DotClearExternalWebUri==null){$this->DotClearExternalWebUri="http://{$_SERVER['SERVER_ADDR']}:$this->DotClearHttpListenPort";}
		if($this->DotClearExternalAdminUri==null){$this->DotClearExternalAdminUri="http://{$_SERVER['SERVER_ADDR']}:$this->DotClearHttpListenPort/admin";}
		if($this->EnableDotClearHTTPService==null){$this->EnableDotClearHTTPService=1;}
		
	}
	
	function Save(){
		$sock=new sockets();
		$sock->SET_INFO("DotClearExternalWebUri",$this->DotClearExternalWebUri);
		$sock->SET_INFO("DotClearHttpListenPort",$this->DotClearHttpListenPort);
		$sock->SET_INFO("EnableDotClearHTTPService",$this->EnableDotClearHTTPService);
		$sock->SET_INFO("DotClearExternalAdminUri",$this->DotClearExternalAdminUri);
		$this->ChangeDotClearUris();
		$sock->getfile("CheckDaemon");
		$sock->getfile("DotClearRestart");
	}
	
	
	function ChangeDotClearUris(){
		
		
		$sql="SELECT * FROM `dotclear_blog`";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		while ($ligne =@mysql_fetch_array($results)) { 
			$uri=$ligne["blog_url"];
			$uri=substr($uri,strpos($uri,'/'),strlen($uri));
			$uri=str_replace('//','',$uri);
			$uri=substr($uri,strpos($uri,'/')+1,strlen($uri));
			
			$new_uri="$this->DotClearExternalWebUri/$uri";
			$sql="UPDATE dotclear_blog SET blog_url='$new_uri' WHERE blog_uid='{$ligne["blog_uid"]}'";
			$q->QUERY_SQL($sql,"artica_backup");
			$count=$count+1;
			
			
			
		}	
		$this->RestartLDAP();
		echo "$count blog(s) updated\n";
		
		
		
		
		
	}
	
	function RestartLDAP(){
		$sock=new sockets();
		$sock->getfile('DotClearLdap');
		
	}
	
	function TestsDatabase(){
		$result=true;
		$s=new mysql();
		$tables=array("dotclear_blog","dotclear_category","dotclear_comment","dotclear_link","dotclear_log",
		"dotclear_media","dotclear_meta","dotclear_permissions","dotclear_ping","dotclear_post",
		"dotclear_post_media","dotclear_session","dotclear_setting","dotclear_spamrule","dotclear_user","dotclear_version");
		while (list ($num, $ligne) = each ($tables) ){
			if(!$s->TABLE_EXISTS($ligne,$this->database)){
				writelogs("FAILED ON $ligne",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$result=false;
				break;
			}
			
		}
		
		return $result;
		
	}
	
	
	function AddUserDotClear($uid,$password,$mail,$home){
		$blog_uid=md5($uid);
		$q=new mysql();
		$sql="SELECT blog_id FROM dotclear_blog WHERE blog_uid='$blog_uid'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["blog_id"]==null){
			$sql="INSERT INTO `dotclear_blog` (`blog_id`, `blog_uid`, `blog_creadt`, `blog_upddt`, `blog_url`, `blog_name`, `blog_desc`, `blog_status`) 
			VALUES
			('$uid', '$blog_uid', '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."', '$this->DotClearExternalWebUri/$uid/index.php?', '$uid', '', 1)";
			$q->QUERY_SQL($sql,$this->database);
			$this->AddBlogSettings($uid,'lang','system','string','en','Default blog language');
			$this->AddBlogSettings($uid,'blog_timezone','system','string','Europe/Berlin','Blog timezone');
			$this->AddBlogSettings($uid,'antispam_moderation_ttl','antispam','string','0','');
			$this->AddBlogSettings($uid,'antispam_date_last_purge','antispam','integer','1232927114','Antispam Date Last Purge (unix timestamp)');
			$this->AddBlogSettings($uid,'public_path','system','string',"$home/blog/public",'Path to public directory, begins with a / for a full system path');
			$this->AddBlogSettings($uid,'public_url','system','string',"/$uid/public",'URL to public directory');
			}else{
				$this->SetBlogSettings($uid,'public_path','system',"$home/blog/public");
				$this->SetBlogSettings($uid,'public_url','system',"/$uid/public");
			}
			
		$this->AddUser($uid,$password,$mail);
		$this->RestartLDAP();
		}
	
	function AddBlogSettings($uid,$key,$setting_ns,$setting_type,$value,$setting_label){
		$q=new mysql();
		$sql="SELECT setting_value FROM dotclear_setting WHERE setting_id='$key' AND blog_id='$uid' AND setting_ns='$setting_ns'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["setting_value"]==null){
			$sql="INSERT INTO `dotclear_setting` (`setting_id`, `blog_id`, `setting_ns`, `setting_value`, `setting_type`, `setting_label`) VALUES
			('$key','$uid','$setting_ns','$value','$setting_type','$setting_label');";
			$q->QUERY_SQL($sql,$this->database);
			
		}
	}
	
	function SetBlogSettings($uid,$key,$setting_ns,$value){
		$q=new mysql();
		writelogs("updateing $key=$value for $uid");
		$sql="
		UPDATE `artica_backup`.`dotclear_setting` SET `setting_value` = '$value' WHERE 
		`dotclear_setting`.`setting_id` = '$key' 
		AND `dotclear_setting`.`blog_id` = '$uid' 
		AND `dotclear_setting`.`setting_ns` = '$setting_ns'  LIMIT 1 ;";
		
		writelogs("$sql");
		$q->QUERY_SQL($sql,$this->database);
	}
	
	
	function AddUser($uid,$password,$email){
		$password=crypt::hmac('artica',$password);
		$date=date('Y-m-d H:i:s');
		$q=new mysql();
		$sql="SELECT user_pwd FROM dotclear_user WHERE user_id='$uid'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["user_pwd"]==null){
			$sql="INSERT INTO `dotclear_user` (`user_id`, `user_super`, `user_status`, `user_pwd`, `user_recover_key`,
										   `user_name`, `user_firstname`, `user_displayname`, `user_email`, 
										   `user_url`, `user_desc`, `user_default_blog`, `user_options`, `user_lang`, `user_tz`, `user_post_status`, `user_creadt`, `user_upddt`) VALUES
											('$uid', 0, 1, '$password', NULL, '$uid', '$uid', NULL, '$email', NULL, NULL, '$uid', 
											'a:3:{s:9:\"edit_size\";i:24;s:14:\"enable_wysiwyg\";b:1;s:11:\"post_format\";s:4:\"wiki\";}', 
											'en','Europe/Berlin', -2, '$date', '$date');
											";
			$q->QUERY_SQL($sql,$this->database);
		}else{
			$sql="UPDATE `artica_backup`.`dotclear_user` SET `user_pwd` = '$password' WHERE `dotclear_user`.`user_id` = '$uid' LIMIT 1 ;";
			$q->QUERY_SQL($sql,$this->database);
		}
		

		$sql="SELECT permissions FROM dotclear_permissions WHERE user_id='$uid' AND blog_id='$uid'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["permissions"]==null){
			$sql="INSERT INTO `dotclear_permissions` (`user_id`, `blog_id`, `permissions`) VALUES
				('$uid', '$uid', '|admin|pages|blogroll|');";
			$q->QUERY_SQL($sql,$this->database);
		}
		
		

		
		
	}
	
	

	
	
}

# ***** BEGIN LICENSE BLOCK *****
# This file is part of Clearbricks.
# Copyright (c) 2006 Olivier Meunier and contributors. All rights
# reserved.
#
# Clearbricks is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Clearbricks is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with Clearbricks; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

/**
* Functions to handle passwords (hash, random password generator...)
*
* @package Clearbricks
*/
class crypt
{
	/**
	* Returns an HMAC encoded value of <var>$data</var>, using the said <var>$key</var>
	* and <var>$hashfunc</var> as hash method (sha1 or md5 are accepted.)
	*
	* @param	string	$key			Hash key
	* @param	string	$data		Data
	* @param	string	$hashfunc		Hash function (md5 or sha1)
	* @return string
	*/
	public static function hmac($key,$data,$hashfunc='sha1')
	{
		$blocksize=64;
		if ($hashfunc != 'sha1') {
			$hashfunc = 'md5';
		}
		
		if (strlen($key)>$blocksize) {
			$key=pack('H*', $hashfunc($key));
		}
		
		$key=str_pad($key,$blocksize,chr(0x00));
		$ipad=str_repeat(chr(0x36),$blocksize);
		$opad=str_repeat(chr(0x5c),$blocksize);
		$hmac = pack('H*',$hashfunc(($key^$opad).pack('H*',$hashfunc(($key^$ipad).$data))));
		return bin2hex($hmac);
	}
	
	/**
	* Returns an 8 characters random password.
	*
	* @return	string
	*/
	public static function createPassword()
	{
		$pwd = array();
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$chars2 = '$!@';
		
		foreach (range(0,8) as $i) {
			$pwd[] = $chars[rand(0,strlen($chars)-1)];
		}
		
		$pos1 = array_rand(array(0,1,2,3));
		$pos2 = array_rand(array(4,5,6,7));
		$pwd[$pos1] = $chars2[rand(0,strlen($chars2)-1)];
		$pwd[$pos2] = $chars2[rand(0,strlen($chars2)-1)];
		
		return implode('',$pwd);
	}
}