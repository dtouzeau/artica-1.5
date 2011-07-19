<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');
include_once('ressources/class.users.menus.inc');


		$usersmenus=new usersMenus();
		if(!$usersmenus->AsPostfixAdministrator){
			$tpl=new templates();
			echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
			die();
		}
		
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["php5DisableMagicQuotesGpc"])){save();exit;}
	if(isset($_GET["options"])){popup_options();exit;}
	if(isset($_GET["modules"])){popup_modules();exit;}
	if(isset($_GET["load-module"])){load_module();exit;}
		
	js();
	
function js(){
$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{advanced_options}');
	
	
$html="
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	


	function {$prefix}LoadPage(){
		RTMMail(650,'$page?popup=yes','$title');
	}
	
	
	

	
	
var x_SavePHP5AdvancedSettings=function (obj) {
	{$prefix}LoadPage();
	}	
	
	function SavePHP5AdvancedSettings(){
    	var XHR = new XHRConnection();
    	var php5DisableMagicQuotesGpc='';
    	var SSLStrictSNIVHostCheck='';
    	if(document.getElementById('php5DisableMagicQuotesGpc').checked){php5DisableMagicQuotesGpc=1;}else{php5DisableMagicQuotesGpc=0;}
		if(document.getElementById('php5FuncOverloadSeven').checked){php5FuncOverloadSeven=1;}else{php5FuncOverloadSeven=0;}
		if(document.getElementById('SSLStrictSNIVHostCheck').checked){SSLStrictSNIVHostCheck=1;}else{SSLStrictSNIVHostCheck=0;}
		XHR.appendData('php5DefaultCharset',document.getElementById('php5DefaultCharset').value);
		XHR.appendData('php5DisableMagicQuotesGpc',php5DisableMagicQuotesGpc);
		XHR.appendData('php5FuncOverloadSeven',php5FuncOverloadSeven);				
		XHR.appendData('SSLStrictSNIVHostCheck',SSLStrictSNIVHostCheck);
 		document.getElementById('php5div').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_SavePHP5AdvancedSettings);
	}

	{$prefix}LoadPage();";

	echo $html;
	}
	
function save(){
	$sock=new sockets();
	$sock->SET_INFO("php5DefaultCharset",$_GET["php5DefaultCharset"]);
	$sock->SET_INFO("php5FuncOverloadSeven",$_GET["php5FuncOverloadSeven"]);
	$sock->SET_INFO("php5DisableMagicQuotesGpc",$_GET["php5DisableMagicQuotesGpc"]);
	$sock->SET_INFO("SSLStrictSNIVHostCheck",$_GET["SSLStrictSNIVHostCheck"]);
	$sock->getFrameWork("cmd.php?php-rewrite=yes");
	$sock->getFrameWork("cmd.php?restart-web-server=yes");
	
	
}

function popup(){
		$tpl=new templates();
		$array["options"]="{options}";
		$array["modules"]="{loaded_modules}";
		$page=CurrentPageName();

	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_phpadv style='width:100%;height:450px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_phpadv').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";	
}

function popup_modules(){
	$array=parsePHPModules();
	$page=CurrentPageName();
	
	
	while (list ($module, $array_f) = each ($array) ){$array_fi[$module]=$module;}
	
	
	
	$array_fi[null]="{select}";
	
	krsort($array_fi);
	
	$table=Field_array_Hash($array_fi,'modules-choose',null,"PhpLoadModule()",null,0,"font-size:16px;padding:3px;font-weight:bold");
	
	$html="
	$table
	<div id='show-module'></div>
	<script>
		function PhpLoadModule(module){
			LoadAjax('show-module','$page?load-module='+document.getElementById('modules-choose').value);
		}
	</script>
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}

function load_module(){
	$array=parsePHPModules();
	$module=$array[$_GET["load-module"]];
	
	$html[]="<table style='width:100%;padding:4px;margin:5px;border:1px solid #005447'>";
	
	while (list ($index, $data) = each ($module) ){
		$html[]="<tr>";
		$html[]="<td class=legend valign='top' style='font-size:14px'>$index:</td>";
		$html[]="<td><strong style='font-size:12px'>";
		if(is_array($data)){
			while (list ($a, $b) = each ($data) ){
				$html[]="<li style='font-size:12px'>$a:$b</li>";
			}
		}else{
			$html[]=$data;
		}
		$html[]="</strong></td>";
		$html[]="</tr>";
	}
	
	$html[]="</table>";
	echo implode("\n",$html);
	
}
function Charsets(){
	
	$arr=array("Arabic (ASMO 708) "=>"ASMO-708",
"Arabic (DOS) "=>"DOS-720",
"Arabic (ISO) "=>"iso-8859-6",
"Arabic (Mac) "=>"x-mac-arabic",
"Arabic (Windows) "=>"windows-1256",
"Baltic (DOS) "=>"ibm775",
"Baltic (ISO) "=>"iso-8859-4",
"Baltic (Windows) "=>"windows-1257",
"Central European (DOS) "=>"ibm852",
"Central European (ISO) "=>"iso-8859-2",
"Central European (Mac) "=>"x-mac-ce",
"Central European (Windows) "=>"windows-1250",
"Chinese Simplified (EUC) "=>"EUC-CN",
"Chinese Simplified (GB2312) "=>"gb2312",
"Chinese Simplified (HZ) "=>"hz-gb-2312",
"Chinese Simplified (Mac) "=>"x-mac-chinesesimp",
"Chinese Traditional (Big5) "=>"big5",
"Chinese Traditional (CNS) "=>"x-Chinese-CNS",
"Chinese Traditional (Eten) "=>"x-Chinese-Eten",
"Chinese Traditional (Mac) "=>"x-mac-chinesetrad",
"Cyrillic (DOS) "=>"cp866",
"Cyrillic (ISO) "=>"iso-8859-5",
"Cyrillic (KOI8-R) "=>"koi8-r",
"Cyrillic (KOI8-U) "=>"koi8-u",
"Cyrillic (Mac) "=>"x-mac-cyrillic",
"Cyrillic (Windows) "=>"windows-1251",
"Europa "=>"x-Europa",
"German (IA5) "=>"x-IA5-German",
"Greek (DOS) "=>"ibm737",
"Greek (ISO) "=>"iso-8859-7",
"Greek (Mac) "=>"x-mac-greek",
"Greek (Windows) "=>"windows-1253",
"Greek, Modern (DOS) "=>"ibm869",
"Hebrew (DOS) "=>"DOS-862",
"Hebrew (ISO-Logical) "=>"iso-8859-8-i",
"Hebrew (ISO-Visual) "=>"iso-8859-8",
"Hebrew (Mac) "=>"x-mac-hebrew",
"Hebrew (Windows) "=>"windows-1255",
"IBM EBCDIC (Arabic) "=>"x-EBCDIC-Arabic",
"IBM EBCDIC (Cyrillic Russian) "=>"x-EBCDIC-CyrillicRussian",
"IBM EBCDIC (Cyrillic Serbian-Bulgarian) "=>"x-EBCDIC-CyrillicSerbianBulgarian",
"IBM EBCDIC (Denmark-Norway) "=>"x-EBCDIC-DenmarkNorway",
"IBM EBCDIC (Denmark-Norway-Euro) "=>"x-ebcdic-denmarknorway-euro",
"IBM EBCDIC (Finland-Sweden) "=>"x-EBCDIC-FinlandSweden",
"IBM EBCDIC (Finland-Sweden-Euro) "=>"x-ebcdic-finlandsweden-euro",
"IBM EBCDIC (Finland-Sweden-Euro) "=>"x-ebcdic-finlandsweden-euro",
"IBM EBCDIC (France-Euro) "=>"x-ebcdic-france-euro",
"IBM EBCDIC (Germany) "=>"x-EBCDIC-Germany",
"IBM EBCDIC (Germany-Euro) "=>"x-ebcdic-germany-euro",
"IBM EBCDIC (Greek Modern) "=>"x-EBCDIC-GreekModern",
"IBM EBCDIC (Greek) "=>"x-EBCDIC-Greek",
"IBM EBCDIC (Hebrew) "=>"x-EBCDIC-Hebrew",
"IBM EBCDIC (Icelandic) "=>"x-EBCDIC-Icelandic",
"IBM EBCDIC (Icelandic-Euro) "=>"x-ebcdic-icelandic-euro",
"IBM EBCDIC (International-Euro) "=>"x-ebcdic-international-euro",
"IBM EBCDIC (Italy) "=>"x-EBCDIC-Italy",
"IBM EBCDIC (Italy-Euro) "=>"x-ebcdic-italy-euro",
"IBM EBCDIC (Japanese and Japanese Katakana) "=>"x-EBCDIC-JapaneseAndKana",
"IBM EBCDIC (Japanese and Japanese-Latin) "=>"x-EBCDIC-JapaneseAndJapaneseLatin",
"IBM EBCDIC (Japanese and US-Canada) "=>"x-EBCDIC-JapaneseAndUSCanada",
"IBM EBCDIC (Japanese katakana) "=>"x-EBCDIC-JapaneseKatakana",
"IBM EBCDIC (Korean and Korean Extended) "=>"x-EBCDIC-KoreanAndKoreanExtended",
"IBM EBCDIC (Korean Extended) "=>"x-EBCDIC-KoreanExtended",
"IBM EBCDIC (Multilingual Latin-2) "=>"CP870",
"IBM EBCDIC (Simplified Chinese) "=>"x-EBCDIC-SimplifiedChinese",
"IBM EBCDIC (Spain) "=>"X-EBCDIC-Spain",
"IBM EBCDIC (Spain-Euro) "=>"x-ebcdic-spain-euro",
"IBM EBCDIC (Thai) "=>"x-EBCDIC-Thai",
"IBM EBCDIC (Traditional Chinese) "=>"x-EBCDIC-TraditionalChinese",
"IBM EBCDIC (Turkish Latin-5) "=>"CP1026",
"IBM EBCDIC (Turkish) "=>"x-EBCDIC-Turkish",
"IBM EBCDIC (UK) "=>"x-EBCDIC-UK",
"IBM EBCDIC (UK-Euro) "=>"x-ebcdic-uk-euro",
"IBM EBCDIC (US-Canada) "=>"ebcdic-cp-us",
"IBM EBCDIC (US-Canada-Euro) "=>"x-ebcdic-cp-us-euro",
"Icelandic (DOS) "=>"ibm861",
"Icelandic (Mac) "=>"x-mac-icelandic",
"ISCII Assamese "=>"x-iscii-as",
"ISCII Bengali "=>"x-iscii-be",
"ISCII Devanagari "=>"x-iscii-de",
"ISCII Gujarathi "=>"x-iscii-gu",
"ISCII Kannada "=>"x-iscii-ka",
"ISCII Malayalam "=>"x-iscii-ma",
"ISCII Oriya "=>"x-iscii-or",
"ISCII Panjabi "=>"x-iscii-pa",
"ISCII Tamil "=>"x-iscii-ta",
"ISCII Telugu "=>"x-iscii-te",
"Japanese (EUC) "=>"euc-jp","Japanese (EUC)"=>"x-euc-jp",
"Japanese (JIS) "=>"iso-2022-jp",
"Japanese (JIS-Allow 1 byte Kana - SO/SI) "=>"iso-2022-jp",
"Japanese (JIS-Allow 1 byte Kana) "=>"csISO2022JP",
"Japanese (Mac) "=>"x-mac-japanese",
"Japanese (Shift-JIS) "=>"shift_jis",
"Korean "=>"ks_c_5601-1987",
"Korean (EUC) "=>"euc-kr",
"Korean (ISO) "=>"iso-2022-kr",
"Korean (Johab) "=>"Johab",
"Korean (Mac) "=>"x-mac-korean",
"Latin 3 (ISO) "=>"iso-8859-3",
"Latin 9 (ISO) "=>"iso-8859-15",
"Norwegian (IA5) "=>"x-IA5-Norwegian",
"OEM United States "=>"IBM437",
"Swedish (IA5) "=>"x-IA5-Swedish",
"Thai (Windows) "=>"windows-874",
"Turkish (DOS) "=>"ibm857",
"Turkish (ISO) "=>"iso-8859-9",
"Turkish (Mac) "=>"x-mac-turkish",
"Turkish (Windows) "=>"windows-1254",
"Unicode "=>"unicode",
"Unicode (Big-Endian) "=>"unicodeFFFE",
"Unicode (UTF-7) "=>"utf-7",
"Unicode (UTF-8) "=>"utf-8",
"US-ASCII "=>"us-ascii",
"Vietnamese (Windows) "=>"windows-1258",
"Western European (DOS) "=>"ibm850",
"Western European (IA5) "=>"x-IA5",
"Western European (ISO) "=>"iso-8859-1",
"Western European (Mac) "=>"macintosh",
"Western European (Windows) "=>"Windows-1252");	
	
	while (list ($index, $data) = each ($arr) ){
		$newar[trim($data)]=strtoupper(trim($data));
	}
	ksort($newar);
	$newar[null]="{select}";
	return $newar;
}

	
function popup_options(){
	
	$sock=new sockets();
	$php5FuncOverloadSeven=$sock->GET_INFO("php5FuncOverloadSeven");
	$php5DefaultCharset=$sock->GET_INFO("php5DefaultCharset");
	
	
	$php5FuncOverloadSeven=Field_checkbox("php5FuncOverloadSeven",1,$php5FuncOverloadSeven);
	
	
	$DisableMagicQuotesGpc=$sock->GET_INFO("php5DisableMagicQuotesGpc");
	$DisableMagicQuotesGpc=Field_checkbox("php5DisableMagicQuotesGpc",1,$DisableMagicQuotesGpc);
	
	$SSLStrictSNIVHostCheck=$sock->GET_INFO("SSLStrictSNIVHostCheck");
	$SSLStrictSNIVHostCheck=Field_checkbox("SSLStrictSNIVHostCheck",1,$SSLStrictSNIVHostCheck);	
	
	$html="
	<div id='php5div'>
	<table width=100% class=form>
	<tr>
		<td valign='top' class=legend nowrap>{php5FuncOverloadSeven}:</td>
		<td valign='top'>$php5FuncOverloadSeven</td>
		<td width=1%>". help_icon("{php5FuncOverloadSeven_text}")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend nowrap>{DisableMagicQuotesGpc}:</td>
		<td valign='top'>$DisableMagicQuotesGpc</td>
		<td  width=1%>". help_icon("{DisableMagicQuotesGpc_text}")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend nowrap>{SSLStrictSNIVHostCheck}:</td>
		<td valign='top'>$SSLStrictSNIVHostCheck</td>
		<td  width=1%>". help_icon("{SSLStrictSNIVHostCheck_text}")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend nowrap>Default charset:</td>
		<td valign='top'>".Field_array_Hash(Charsets(),"php5DefaultCharset",$php5DefaultCharset,null,"style:font-size:13px:padding:3px")."</td>
		<td  width=1%>&nbsp;</td>
	</tr>
	
	<tr>
		<td colspan=3 align='right'>
		<hr>". button('{edit}','SavePHP5AdvancedSettings()')."
		
		</td>
	</tr> 
	</table>
	</div>
";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}




function parsePHPModules() {
 ob_start();
 phpinfo(INFO_MODULES);
 $s = ob_get_contents();
 ob_end_clean();

 $s = strip_tags($s,'<h2><th><td>');
 $s = preg_replace('/<th[^>]*>([^<]+)<\/th>/',"<info>\\1</info>",$s);
 $s = preg_replace('/<td[^>]*>([^<]+)<\/td>/',"<info>\\1</info>",$s);
 $vTmp = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/',$s,-1,PREG_SPLIT_DELIM_CAPTURE);
 $vModules = array();
 for ($i=1;$i<count($vTmp);$i++) {
  if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/',$vTmp[$i],$vMat)) {
   $vName = trim($vMat[1]);
   $vTmp2 = explode("\n",$vTmp[$i+1]);
   foreach ($vTmp2 AS $vOne) {
   $vPat = '<info>([^<]+)<\/info>';
   $vPat3 = "/$vPat\s*$vPat\s*$vPat/";
   $vPat2 = "/$vPat\s*$vPat/";
   if (preg_match($vPat3,$vOne,$vMat)) { // 3cols
     $vModules[$vName][trim($vMat[1])] = array(trim($vMat[2]),trim($vMat[3]));
   } elseif (preg_match($vPat2,$vOne,$vMat)) { // 2cols
     $vModules[$vName][trim($vMat[1])] = trim($vMat[2]);
   }
   }
  }
 }
 return $vModules;
}

?>