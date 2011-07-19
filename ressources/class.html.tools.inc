<?php
function DAEMON_STATUS_ROUND($key,$bsini,$textoadd=null,$noenable=0){
if($bsini->_params["$key"]["service_name"]==null){return null;}
if($bsini->_params["$key"]["service_disabled"]==null){$bsini->_params["$key"]["service_disabled"]=-1;}
$app=$bsini->_params["$key"]["service_name"];
$monit=$bsini->_params["$key"]["monit"];
$change_name=$bsini->_params["$key"]["change-name"];

	if(trim($bsini->_params[$key]["queue_num"])>0){	
		$emailqueue="
				<tr>
					<td align='right' colspan=2 style='background-color:#D5EED9;font-size:11px'><strong>".$bsini->_params[$key]["queue_num"]." {emails}</td>
				</tr>	
			";	
	}

if($monit==1){
		if(!isset($GLOBALS["MONIT_ARRAY"])){$sock=new sockets();$GLOBALS["MONIT_ARRAY"]=unserialize(base64_decode($sock->getFrameWork("cmd.php?monit-status=yes")));}
		$monit_status=$GLOBALS["MONIT_ARRAY"][$app]["status"];
		$monit_uptime=$GLOBALS["MONIT_ARRAY"][$app]["uptime"];
		$bsini->_params["$key"]["master_memory"]=$GLOBALS["MONIT_ARRAY"][$app]["memory kilobytes total"];
		$memory_percent_total=$GLOBALS["MONIT_ARRAY"][$app]["memory percent total"];
		$cpu_percent_total=$GLOBALS["MONIT_ARRAY"][$app]["cpu percent total"];
		$application_installed=1;
		$bsini->_params["$key"]["master_pid"]=$GLOBALS["MONIT_ARRAY"][$app]["pid"];
		$uptime=$GLOBALS["MONIT_ARRAY"][$app]["uptime"];
		$service_disabled=0;
		$application_installed=1;
		
		
		switch ($monit_status) {
			case "monitored":$bsini->_params[$key]["running"]=1;$status="sleeping";break;
			case "not monitored":$bsini->_params[$key]["running"]=0;$status="stopped";break;
			default:
				;
			break;
		}	
}


if($noenable==1){if($bsini->_params["$key"]["service_disabled"]==0){return null;}}
$tpl=new templates();
$prod=$bsini->_params[$key]["service_name"];
$title=$tpl->_ENGINE_parse_body("{{$prod}}");


if($bsini->_params[$key]["running"]==0){
		$img="42-red.png";
		$status="{stopped}";
		$js_service="javascript:StartStopService('".$bsini->_params["$key"]["service_cmd"]."','1','$title');";
		if($bsini->_params[$key]["service_croned"]==1){
			$img="42-green.png";
			$status="Croned";
		}
	}else{
		$img="42-green.png";
		$status="running";
		$js_service="StartStopService('".$bsini->_params["$key"]["service_cmd"]."','0','$title');";
		
	}	

	
	if($bsini->_params["$key"]["running"]==1){
	
	$button="<input type='button' OnClick=\"$js_service\" value='{stop}&nbsp;&raquo;'>";
	}else{
	$js_service="StartStopService('".$bsini->_params["$key"]["service_cmd"]."','1','$title');";	
	$button="<input type='button' OnClick=\"$js_service\" value='{start}&nbsp;&raquo;'>";
	}
	
	
if($bsini->_params[$key]["pattern_version"]<>null && $bsini->_params[$key]["pattern_date"]==null){
	$bsini->_params[$key]["pattern_date"]=$bsini->_params[$key]["pattern_version"];
}
	
if($bsini->_params["$key"]["service_cmd"]==null){$js_service=null;}

if($bsini->_params[$key]["pattern_date"]<>null){
	$date=date('Y-m-d');
	$bsini->_params[$key]["pattern_date"]=str_replace($date,"{today},",$bsini->_params[$key]["pattern_date"]);
	$date=date('Y-d-m');
	$bsini->_params[$key]["pattern_date"]=str_replace($date,"{today},",$bsini->_params[$key]["pattern_date"]);	
	$pattern=
	"<tr>
	<td align='right' valign='top'><strong>{pattern_ver}:</strong></td>
	<td align='left' valing='top'><strong>{$bsini->_params[$key]["pattern_date"]}</strong></td>
	</tr>";
}

if($bsini->_params["$key"]["service_disabled"]=="0"){	
	$img="warning42.png";
	$js_service="StartStopService('".$bsini->_params["$key"]["service_cmd"]."','1','$title');";
	$disabled="<tr><td colspan=2 align='right'><strong style='font-size:11px;line-height:auto'><i>{service_disabled}</i></strong></td></tr>";
}

if($bsini->_params["$key"]["master_pid"]<>null){

	$pid_text="";
		$pid="
		<tr>
			<td align='right' nowrap style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>{pid}:</strong></td>
			<td style='background-color:#D5EED9;font-size:11px'><strong>{$bsini->_params["$key"]["master_pid"]}</strong></td>
		</tr>			
		<tr>
			<td align='right' style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>{memory}:</strong></td>
			<td style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>".FormatBytes($bsini->_params["$key"]["master_memory"])."</strong></td>
		</tr>		
		";
			
		if(trim($bsini->_params[$key]["master_cached_memory"])<>null){	
			$pid=$pid."
				<tr>
					<td align='right' style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>{virtual_memory}:</strong></td>
					<td style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>".FormatBytes($bsini->_params["$key"]["master_cached_memory"])."</strong></td>
				</tr>	
			";		
			}

		}

	if(trim($bsini->_params[$key]["processes_number"])<>null){
		if($bsini->_params[$key]["processes_number"]>0){
			$processes="<tr>
						<td align='right' style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>{processes}:</strong></td>
						<td style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>".$bsini->_params["$key"]["processes_number"]."</strong></td>
					</tr>";
		}
	}


	if(trim($bsini->_params[$key]["license_error"])<>null){
		$img="warning42.png";
		$license_error=
		"<tr>
		<td align='right' valign='top' style='background-color:#D5EED9;font-size:11px'><strong>{license_error}:</strong></td>
		<td align='left' style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>". texttooltip(substr($bsini->_params[$key]["license_error"],0,13)."...",$bsini->_params[$key]["license_error"],null,nulll,1)."</strong></td>
		</tr>";
	}

	

	if(strlen($bsini->_params["$key"]["master_version"])>6){
		$bsini->_params["$key"]["master_version"]=substr($bsini->_params["$key"]["master_version"],0,6)."...";
	}
	
$version="
		<tr>
			<td align='right' nowrap valign='top' style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>{version}:</strong></td>
			<td valign='top' style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>{$bsini->_params["$key"]["master_version"]}</strong></td>
		</tr>";
	
	$bsini->_params["$key"]["master_version"]=str_replace('/DEBUG','',$bsini->_params["$key"]["master_version"]);
	if($bsini->_params["$key"]["master_version"]==null){$version=null;}

	if($bsini->_params["$key"]["why"]<>null){
		$img="warning42.png";
		$textoadd=
		"<tr>
		<td align='right' valign='top' style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>{info}:</strong></td>
		<td align='left' style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong style='color:red'>{$bsini->_params["$key"]["why"]}</strong></td>
		</tr>";
	}
	if($bsini->_params["$key"]["other"]<>null){
		$textoadd2=
		"<tr>
		<td align='right' valign='top' style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>{info}:</strong></td>
		<td align='left' style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong style='color:black'>{$bsini->_params["$key"]["other"]}</strong></td>
		</tr>";
	}

	if($textoadd==null){
		if($bsini->_params["$key"]["uptime"]<>null){
			$textoadd="<div style='text-align:right'><i>{since}: {$bsini->_params["$key"]["uptime"]}</i></div>";
		}
		
	}

	if(preg_match("#(.+?):(.+)#",$bsini->_params["$key"]["service_name"],$rz)){$change_name="{{$rz[1]}}";}

	$master_title="{{$bsini->_params["$key"]["service_name"]}}";
	if($change_name<>null){$master_title=$change_name;}

$html="<table style='width:100%;background-color:#D5EED9;font-size:11px;line-height:auto' class=TableRemove>
<tr>
<td width=1% valign='top' style='background-color:#D5EED9;font-size:11px;line-height:auto'>" . imgtootltip($img,'{start_stop}',$js_service)."</td>
<td style='background-color:#D5EED9;font-size:11px;border:0px;line-height:auto'>
	<table style='width:100%;background:transparent;border:0px;padding:0px;background-color:#D5EED9;font-size:11px;line-height:auto' class=TableRemove>
		<tr>
		<td align='right' nowrap style='background-color:#D5EED9;font-size:11px;line-height:auto'><strong>$master_title:</td>
		<td style='background-color:#D5EED9;font-size:11px;border:0px;line-height:auto'>
			<strong><span style='font-size:12px'>$status</span>$pid_text</strong></td>
		</tr>
		$pid
		$processes
		$emailqueue
		$license_error
		$version
		$icap$pattern$textoadd2$disabled
	</table>
	</td>
</table>
$textoadd";		
	
return RoundedLightGreen($html)."<br>";
}

function RoundedLightGreen($text){
	
return "<div>
  <b class=\"RLightGreen\">
  <b class=\"RLightGreen1\"><b></b></b>
  <b class=\"RLightGreen2\"><b></b></b>
  <b class=\"RLightGreen3\"></b>
  <b class=\"RLightGreen4\"></b>
  <b class=\"RLightGreen5\"></b></b>

  <div class=\"RLightGreenfg\" style='padding:7px;'>
   $text
  </div>

  <b class=\"RLightGreen\">
  <b class=\"RLightGreen5\"></b>
  <b class=\"RLightGreen4\"></b>
  <b class=\"RLightGreen3\"></b>
  <b class=\"RLightGreen2\"><b></b></b>
  <b class=\"RLightGreen1\"><b></b></b></b>
</div>
";	
}

function Field_array_Hash($Hash,$FieldName,$default=null,$java=null,$class='',$maxlength=0,$style_of_field=null,$disabled=false){
	if(!is_array($Hash)){return "<input type='hidden' name='$FieldName' value='0' id='$FieldName'>";}
	if(preg_match("#style:(.+)#",$java,$re)){
		$java=null;
		$style_of_field=$re[1];
	}
	
		if($java<>null){$java="OnChange=\"javascript:$java\"";}	
		
if(!preg_match("#font-size#",$style_of_field)){$style_of_field=$style_of_field.";font-size:12px";}
		
	$class="class='$class'  style='$style_of_field'";
	if($disabled){$DISABLED_TOKEN="DISABLED";}
	$html="
	<!--Field_array_Hash value=\"$default\" -->
	<select name=\"$FieldName\" id='$FieldName' $java $class $DISABLED_TOKEN>";	
	reset ($Hash);
	while (list($num,$val)=each($Hash)){	
	$Default_value=$num;
	if($Default_value==$default){$def="selected";}else{$def=null;}
	
	
	if($maxlength>0){
		if (strlen($val)>$maxlength){
			$val=substr($val,0,$maxlength-3) . "...";
		}
	}
	
	
	
	$html=$html ."
		<option value=\"$Default_value\" $def>$val</option>\n";
	}
		
	$html=$html ."</select>	";
return $html;}


function url_decode_special_tool($path){
	$path=str_replace("%E9","é",$path);
	$path=str_replace("%E0","à",$path);
	$path=str_replace("%F9","ù",$path);
	$path=str_replace("%20"," ",$path);
	$path=str_replace("%E8","è",$path);
	$path=str_replace("%E7","ç",$path);
	$path=str_replace("%26","&",$path);
	$path=str_replace("%FC","ü",$path);
	$path=str_replace("%F6","ö",$path);
	$path=str_replace("%EB","ë",$path);
	$path=str_replace("%EF","ï",$path);
	$path=str_replace("%EE","î",$path);
	$path=str_replace("%EA","ê",$path);
	$path=str_replace("%E2","â",$path);
	$path=str_replace("%FB","û",$path);
	$path=str_replace("%u20AC","€",$path);
	$path=str_replace("%24","$",$path);
	$path=str_replace("%21","!",$path);
	$path=str_replace("%23","#",$path);
	$path=str_replace("%5C",'\\',$path);
	$path=str_replace("%7E",'~',$path);
	$path=str_replace("%22",'"',$path);
	$path=str_replace("%22",'"',$path);
	$path=str_replace("%27","'",$path);
	$path=str_replace("%F8","ø",$path);
	$path=str_replace("%2C",",",$path);
	$path=str_replace("%3A",":",$path);
	$path=str_replace("%A1","¡",$path);
	$path=str_replace("%A7","§",$path);
	$path=str_replace("%B2","²",$path);
	$path=str_replace("%3B",";",$path);
	$path=str_replace("%3C","<",$path);
	$path=str_replace("%3E",">",$path);
	$path=str_replace("%B5","µ",$path);
	$path=str_replace("%B0","°",$path);
	$path=str_replace("%7C","|",$path);
	$path=str_replace("%5E","^",$path);
	$path=str_replace("%60","`",$path);
	$path=str_replace("%25","%",$path);
	$path=str_replace("%A3","£",$path);
	$path=str_replace("%3D","=",$path);
	$path=str_replace("%3F","?",$path);
	$path=str_replace("%3F","€",$path);
	return $path;
}


class htmltools_inc{
	
	function replace_accents($s) {
	    $s = htmlentities($s);
	    $s = preg_replace ('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil|ring);/', '$1', $s);
	    $s=str_replace("&Ntilde;","N",$s);
	    $s=str_replace("&ntilde;","n",$s);
	    $s=str_replace("&Oacute;","O",$s);
	    $s=str_replace("&oacute;","O",$s);
	    $s=str_replace("&Ograve;","O",$s);
	    $s=str_replace("&ograve;","o",$s);
	    $s=str_replace("&Ocirc;","O",$s);
	    $s=str_replace("&ocirc;","o",$s);
	    $s=str_replace("&Ouml;","O",$s);
	    $s=str_replace("&ouml;","o",$s);
	    $s=str_replace("&Otilde;","O",$s);
	    $s=str_replace("&otilde;","o",$s);
	    $s=str_replace("&Oslash;","O",$s);
	    $s=str_replace("&oslash;","o",$s);
	    $s=str_replace("&szlig;","b",$s);
	    $s=str_replace("&Thorn;","T",$s);
	    $s=str_replace("&thorn;","t",$s);
	    $s=str_replace("&Uacute;","U",$s);
	    $s=str_replace("&uacute;","u",$s);
	    $s=str_replace("&Ugrave;","U",$s);
	    $s=str_replace("&ugrave;","u",$s);
	    $s=str_replace("&Ucirc;","U",$s);
	    $s=str_replace("&ucirc;","u",$s);
	    $s=str_replace("&Uuml;","U",$s);
	    $s=str_replace("&uuml;","u",$s);
	    $s=str_replace("&Yacute;","Y",$s);
	    $s=str_replace("&yacute;","y",$s);
	    $s=str_replace("&yuml;","y",$s);
	    $s=str_replace("&Icirc;","I",$s);
	    $s=str_replace("&icirc;","i",$s);  
	    
	    
	      
	    $s = html_entity_decode($s);
	    
	    return $s;		
	}	
	
	function StripSpecialsChars($pattern){
		
		$pattern=$this->replace_accents($pattern);
		$pattern=str_replace(" ","_",$pattern);
		$pattern=str_replace("-","_",$pattern);
		$pattern=str_replace("&","_",$pattern);
		$pattern=str_replace(",","_",$pattern);
		$pattern=str_replace(";","_",$pattern);
		$pattern=str_replace("%","_",$pattern);
		$pattern=str_replace("*","_",$pattern);
		$pattern=str_replace("ø","_",$pattern);
		$pattern=str_replace("$","_",$pattern);
		$pattern=str_replace("/","_",$pattern);
		$pattern=str_replace("\\","_",$pattern);
		$pattern=str_replace("?","_",$pattern);
		$pattern=str_replace("µ","_",$pattern);
		$pattern=str_replace("£","_",$pattern);
		$pattern=str_replace(")","_",$pattern);
		$pattern=str_replace("(","_",$pattern);
		$pattern=str_replace("[","_",$pattern);
		$pattern=str_replace("]","_",$pattern);
		$pattern=str_replace("#","_",$pattern);
		$pattern=str_replace("'","_",$pattern);
		$pattern=str_replace("\"","_",$pattern);
		$pattern=str_replace("+","_",$pattern);
		$pattern=substr($pattern,0,20);		
		return $pattern;
		
	}

	function LanguageArray(){
		$lang["en"]="English";
		$lang["fr"]="Francais";
		$lang["po"]="Portugues";
		$lang["br"]="Brazilian";
		$lang["es"]="Espanol";
		$lang["it"]="Italiano";
		$lang["de"]="Deutsch";
		$lang["pol"]="Polish";
		return $lang;
	}
	
	
	
}


?>