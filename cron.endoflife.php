<?php
include_once(dirname(__FILE__) . '/class.cronldap.inc');
ParseLDAP();

function ParseLDAP(){
	$ldap=new cronldap();
	$today=date('Y-m-d');
	$connect=$ldap->ldap_connection;
	$_GET["suffix"]=$ldap->suffix;
	$pattern="(&(objectClass=UserArticaClass)(FinalDateToLive=*)(!(FinalDateToLive=0)))";
		$attr=array("uid","FinalDateToLive","dn");
		$sr =ldap_search($connect,$_GET["suffix"],$pattern,$attr);
		if($sr){
			$hash=ldap_get_entries($connect,$sr);
			if($hash["count"]>0){
				for($i=0;$i<$hash["count"];$i++){
					$uid=$hash[$i]["uid"][0];
					$dn=$hash[$i]["dn"];
					$FinalDateToLive=$hash[$i][strtolower("FinalDateToLive")][0];
					$diff=DateDiff($today,$FinalDateToLive);
					echo "Analyze $dn: $uid :$FinalDateToLive ($diff day(s))\n";
					if($diff<0){
						echo "This user must be deleted...\n";
						delete_ldap($dn,$connect,true);
						DeleteMBX($uid);
					}
					
				}
			}
		}	
		
	@ldap_unbind($connect);
	unset($GLOBALS["LDAP_BIN_ID"]);
	unset($GLOBALS["LDAP_CONNECT_ID"]);	
	echo "\n";
	
}



function delete_ldap($dn,$connect,$recursive=false){
		  
		    if($recursive == false){
		    	if(!@ldap_delete($connect,$dn)){
		    	 echo("Deleting $dn...\n");
		    	  echo("ERROR: ldap_delete \"$dn\"" . ldap_err2str(ldap_errno($connect))."\n");
		    	  return false;			 
		    	}
		    }
		    $sr=@ldap_list($connect,$dn,"ObjectClass=*");
		        if($sr){
		        	$info =@ldap_get_entries($connect, $sr);
		        		for($i=0;$i<$info['count'];$i++){
		            		$result=delete_ldap($info[$i]['dn'],$connect,$recursive);
		            		if(!$result){return($result);}
		        		}
		        return(delete_ldap($dn,$connect,false));
		    }
	}


function DateDiff($debut, $fin) {
  $tDeb = explode("-", $debut);
  $tFin = explode("-", $fin);

  $t2=mktime(0, 0, 0, $tFin[1], $tFin[2], $tFin[0]);
  $t1=mktime(0, 0, 0, $tDeb[1], $tDeb[2], $tDeb[0]);
  $t=$t1-$t2;
  if($t==0){return 0;};
  
  
  
  $diff = mktime(0, 0, 0, $tFin[1], $tFin[2], $tFin[0]) - 
          mktime(0, 0, 0, $tDeb[1], $tDeb[2], $tDeb[0]);
  
  return(($diff / 86400)+1);

}

function DeleteMBX($uid){
	if(!$_GET["cyrus_imapd_installed"]){return null;}
	$path=dirname(__FILE__) . "/bin/cyrus-admin.pl -u {$_GET["cyrus_ldap_admin"]} -p {$_GET["cyrus_ldap_admin_password"]} -m $uid --delete";
	echo(system($path));
	echo "\n";
	}








?>