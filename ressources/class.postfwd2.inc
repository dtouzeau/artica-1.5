<?php

class postfwd2{
	
	
function item_to_text($array,$noif=false){
	$f["date"]="{date}";
	$f["time"]="{time}";
	$f["days"]="{days}";
	$f["months"]="{months}";
	$f["rbl"]="{rbl}";
	$f["rblcount"]="{rblcount}";
	$f["helo_address"]="{HELO_ADDRESS}";
	$f["sender_ns_names"]="{servername}";
	$f["sender_ns_addrs"]="{ipaddr}";
	$f["sender_mx_names"]="MX {servername}";
	$f["sender_mx_addrs"]="MX {ipaddr}";
	$f["client_address"]="{client_address}";
	$f["client_name"]="{client_name} regex";
	$f["reverse_client_name"]="{reverse_client_name}";
	$f["helo_name"]="{HELO_SERVERNAME}";
	$f["sender"]="{sender}";
	$f["recipient"]="{recipient}";
	$f["recipient_count"]="{recipient_count}";
	$f["size"]="{message_size}";
	$f["score"]="{score}";	
	$f["object"]="{object}";
		
	
	$ms["eq"]="=";
	$ms["eq2"]="==";
	$ms["no"]="!=";
	$ms["noteq"]="!=";
	$ms["aboveeq"]=">";
	$ms["abovenot"]="!>";	
	$ms["lowereq"]=">";
	$ms["lowernot"]="!>";
	$ms["matches"]="=~";
	$ms["matchesnot"]="!=~";	
	
	if($array["item"]=="size"){
		if($array["item_data"]>=1000000){
			$MB=$array["item_data"]/1000000;
			$array["item_data"]=$MB."MB";
		}
		if($array["item_data"]<=1000000){
			$KB=$array["item_data"]/1000;
			$array["item_data"]=$KB."KB";
		}
		
	}
	if(!$noif){$if="{if} ";}
	return "$if{$f[$array["item"]]} {$ms[$array["operator"]]} {$array["item_data"]}";
	
}	
	
}