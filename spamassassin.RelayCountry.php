<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.spamassassin.inc');


if(isset($_GET["RelayCountry-popup"])){RelayCountry_popup();exit;}
if(isset($_GET["CountriesCode"])){RelayCountry_add();exit;}
if(isset($_GET["CountriesCodeDelete"])){RelayCountry_del();exit;}
RelayCountry_js();




function RelayCountry_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	
	$title=$tpl->_ENGINE_parse_body('{deny_countries}');
	
	$html="
	
	function RelayCountry_load(){
		YahooWin3('400','$page?RelayCountry-popup=yes','$title');
	
	}
	
var x_RelayCountry_add= function (obj) {
	var response=obj.responseText;
	if(response){alert(response);}
    RelayCountry_load();
	}		
	
	function RelayCountry_add(){
		var XHR = new XHRConnection();
		XHR.appendData('CountriesCode',document.getElementById('CountriesCode').value);
		XHR.appendData('score',document.getElementById('score').value);
		document.getElementById('RelayCountry_popup').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_RelayCountry_add);	
	}
	
	function RelayCountry_del(CountriesCode){
		var XHR = new XHRConnection();
		XHR.appendData('CountriesCodeDelete',CountriesCode);
		document.getElementById('RelayCountry_popup').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_RelayCountry_add);			
	}
	
	
	RelayCountry_load();
	";
	
	echo $html;
	
}

function RelayCountry_del(){
	$code=$_GET["CountriesCodeDelete"];
	$spam=new spamassassin();
	unset($spam->main_country[$code]);
	$spam->SaveRelayCountry();
}

function RelayCountry_add(){
	$spam=new spamassassin();
	$spam->main_country[$_GET["CountriesCode"]]["score"]=$_GET["score"];
	$spam->main_country[$_GET["CountriesCode"]]["country_name"]=$spam->CountriesCode[$_GET["CountriesCode"]];
	$spam->SaveRelayCountry();
	
}

function RelayCountry_popup(){
	
	$spam=new spamassassin();
	$field=Field_array_Hash($spam->CountriesCode,'CountriesCode');
	
	if(is_array($spam->main_country)){
		$countries="<table style='width:90%'>";
		
		while (list ($country_code, $array) = each ($spam->main_country)){
			if($country_code==null){continue;}
			$countries=$countries."
			<tr " . CellRollOver().">
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td width=1%><code style='font-size:12px'>$country_code</td>
				<td width=99%><code style='font-size:12px'>{$array["country_name"]}</td>
				<td width=1%><code style='font-size:12px'>{$array["score"]}</code></td>
				<td width=1%>" . imgtootltip("ed_delete.gif","{delete}","RelayCountry_del('$country_code')")."</td>
			</tr>
			";
			
		}
		
	$countries=$countries."</table>";}
	$countries=RoundedLightWhite($countries);
	
	$html="
	<h1>{deny_countries}</H1>
	<p class=caption>{deny_countries_text_spam}</p>
	<div id='RelayCountry_popup'>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{country}:</td>
		<td>$field</td>
	</tr>
	<tr>
		<td class=legend>{score}:</td>
		<td>" . Field_text('score',"1.0","width:30px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:RelayCountry_add();\" value='{add}&nbsp;&raquo;'></td>
	</tr>
	</table>
	</div>
	<br>
	<div style='width:100%;height:200px;overflow:auto'>
	$countries
	</div>
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


?>