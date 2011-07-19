<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.mailboxes.inc');
include_once('ressources/class.main_cf.inc');




Status();


function status(){
	$sock=new sockets();
	$datas=explode("\n",$sock->getfile('?psprocesses'));

	
	
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match('#([0-9]+)\s+([0-9]+)\s+([a-zA-Z\-\.0-9]+)#',$ligne,$regs)){
			
			$proc=$regs[3];
			
			$mem1=$regs[1]+ $regs[2];
			$follow_arr[$proc]["MEM"]=$follow_arr[$proc]["MEM"]+$mem1;
			$follow_arr[$proc]["PROC"]=$follow_arr[$proc]["PROC"]+1;
				
			}

		}
	
	if(!is_array($follow_arr)){
		$tpl=new templates('{global_services_status}','{error_no_socks}');
		echo $tpl->web_page;
		exit;
	}
			
$html="<h2>{services_status}</h2>
	<table >
	<tr class=rowA>
	<td><strong>{service_name}</td>
	<td><strong>{global_memory}</td>
	<td><strong>{process_number}</td>
	</tr>";
while (list ($num, $ligne) = each ($follow_arr) ){
	if(array_stats($num)){
	if($class=='rowA'){$class='rowB';}else{$class="rowA";}
	$mem=round($ligne["MEM"]/1024);
	$html=$html . "
	<tr class=$class>
	<td>$num</td>
	<td>$mem mb</td>
	<td>{$ligne["PROC"]}</td>
	</tr>";
	}
	}
	
$html=$html . "</table>";

$tpl=new templates('{global_services_status}',$html);
echo $tpl->web_page;
}

function array_stats($service){
	$array_process=array("saslauthd","apache2","master","qmgr","artica-postfix","cyrmaster","slapd","aveserver");
	while (list ($num, $ligne) = each ($array_process) ){
		if($ligne==$service){return true;}
	}
	
	return false;
}

?>