<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.kas-filter.inc');
if(isset($_POST["ServerMaxFilters"])){SaveSettings();exit;}
pdefault();

function pdefault(){
	$kas=new kas_filter();
	if($kas->error==true){
		$tpl=new templates("{Anti-Spam Engine}","{error_no_socks}");
		echo $tpl->web_page;
		return null;
		}
	$arrayyesno=array("yes"=>"yes","no"=>"no");
	
	$array_ClientOnError=array(
		'accept'=>"{accept message}",
		'reject'=>"{reject message}",
		'tempfail'=>"generate temporary error");
		
	
	$FilterParseMSOffice=Field_array_Hash($arrayyesno,'FilterParseMSOffice',$kas->array_datas["FilterParseMSOffice"]);
	$FilterUDSEnabled=Field_array_Hash($arrayyesno,'FilterUDSEnabled',$kas->array_datas["FilterUDSEnabled"]);
	$ClientOnError=Field_array_Hash($array_ClientOnError,'ClientOnError',$kas->array_datas["ClientOnError"]);
	$html="
	<form name='kas'>
	<table style='margin:0px;padding:0px;border:0px'>
	<tr>
	<td colspan=2 align='right' style='padding-right:10px'><input type='button' OnClick=\"javascript:EditKasSettings();\" value='{save parameters}&nbsp;&raquo;'></td>
	</tr>
	<tr>
	<td width=50% valign='top'>
	<FIELDSET><LEGEND>{Process Server Settings}</LEGEND>
	
		<table width=80%'>
		<tr class='rowA'>
			<td align='right'>{Max. number of filtration processes}:</td>
			<td><input type='text' id='ServerMaxFilters' value='{$kas->array_datas["ServerMaxFilters"]}' style='width:20px'></td>
		</tr>
		<tr class='rowB'>
			<td align='right'>{Number of filtration processes at server start-up}:</td>
			<td><input type='text' id='ServerStartFilters' value='{$kas->array_datas["ServerStartFilters"]}' style='width:20px'></td>
		</tr>	
		<tr class='rowA'>
			<td align='right'>{Number of spare filtration processes}:</td>
			<td><input type='text' id='ServerSpareFilters' value='{$kas->array_datas["ServerSpareFilters"]}' style='width:20px'></td>
		</tr>
		</table>
		</fieldset>

	<FIELDSET><LEGEND>{Check Options}</LEGEND>	
		<table width=80%'>
		<tr class='rowA'>
			<td align='right'>{Number of Received headers to be parsed while retrieving ip address}:</td>
			<td><input type='text' id='FilterReceivedHeadersLimit' value='{$kas->array_datas["FilterReceivedHeadersLimit"]}' style='width:20px'></td>
		</tr>
		<tr class='rowB'>
			<td align='right'>{Overall timeout of all DNS requests}:</td>
			<td><input type='text' id='FilterDNSTimeout' value='{$kas->array_datas["FilterDNSTimeout"]}' style='width:20px'></td>
		</tr>
		<tr class='rowA'>
			<td align='right'>{Check MS Word and RTF files}:</td>
			<td>$FilterParseMSOffice</td>
		</tr>
		<tr class='rowB'>
			<td align='right'>{UDS_enabled}:</td>
			<td>$FilterUDSEnabled</td>
		</tr>
		<tr class='rowA'>
			<td align='right'>{Timeout for receiving response from UDS server}:</td>
			<td><input type='text' id='FilterUDSTimeout' value='{$kas->array_datas["FilterUDSTimeout"]}' style='width:20px'></td>
		</tr>			
		</table>		
	</FIELDSET>
	
	

	</td>
	<td width=50% valign='top'>
		<FIELDSET>
			<LEGEND>{Filtration Process}</LEGEND>
			<table width=80%'>
			<tr class='rowA'>
				<td align='right'>{Max. number of mail messages to be processed}:</td>
				<td><input type='text' id='FilterMaxMessages' value='{$kas->array_datas["FilterMaxMessages"]}' style='width:20px'></td>
			</tr>
			<tr class='rowB'>	
				<td align='right'>{Max. number of mail messages randomization}:</td>
				<td><input type='text' id='FilterRandMessages' value='{$kas->array_datas["FilterRandMessages"]}' style='width:20px'></td>
			</tr>	
			<tr class='rowA'>	
				<td align='right'>{Max_idle_time_in_seconds}:</td>
				<td><input type='text' id='FilterMaxIdle' value='{$kas->array_datas["FilterMaxIdle"]}' style='width:20px'></td>
			</tr>
			<tr class='rowB'>	
				<td align='right'>{Exit_delay_in_seconds}:</td>
				<td><input type='text' id='FilterDelayedExit' value='{$kas->array_datas["FilterDelayedExit"]}' style='width:20px'></td>
			</tr>	
											
		
			
		</table>
		</fieldset>
<FIELDSET>
			<LEGEND>{MTA Clients Settings}</LEGEND>
			<table width=80%'>
			<tr class='rowA'>
				<td align='right'>{Filtering size limit}:<br><i style='font-size:9px'>{ClientFilteringSizeLimit}</i></td>
				<td><input type='text' id='ClientFilteringSizeLimit' value='{$kas->array_datas["ClientFilteringSizeLimit"]}' style='width:20px'></td>
			</tr>
			<tr class='rowB'>
				<td align='right'>{On filtering error}:<br><i style='font-size:9px'>{ClientOnError}</i></td>
				<td>$ClientOnError</td>
			</tr>
			<tr class='rowB'>
				<td align='right'>{Default domain}:<br><i style='font-size:9px'>{ClientDefaultDomain}</i></td>
				<td><input type='text' id='ClientDefaultDomain' value='{$kas->array_datas["ClientDefaultDomain"]}' style='width:80%'></td>
			</tr>	
			<tr class='rowA'>
				<td align='right'>{Connection timeout}:<br><i style='font-size:9px'>{ClientConnectTimeout}</i></td>
				<td><input type='text' id='ClientDataTimeout' value='{$kas->array_datas["ClientConnectTimeout"]}' style='width:80%'></td>
			</tr>							
			<tr class='rowB'>
				<td align='right'>{Data exchange timeout}:<br><i style='font-size:9px'>{ClientDataTimeout}</i></td>
				<td><input type='text' id='ClientDataTimeout' value='{$kas->array_datas["ClientDataTimeout"]}' style='width:80%'></td>
			</tr>							

			
			
					
			</table>
			</FIELDSET>	
		
		
			
		
	</td>	
	</tr>
	</table>	
	</form>
			


	
	
	
	";
	
	$tpl=new templates('{Anti-Spam Engine}',$html);
	echo $tpl->web_page;
}

function SaveSettings(){
	$kas=new kas_filter();
	if($kas->error==true){$html="{error_no_socks}" ;}
	else{
		
		while (list ($num, $val) = each ($_POST) ){$kas->array_datas[$num]=$val;}
 		if($kas->SaveFile()){$html="{success}";}else{$html="{failed}";}
	}
	unset($_POST);
	$tpl=new templates();
	echo $tpl->_parse_body($html);
}


?>