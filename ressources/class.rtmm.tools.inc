<?php
include_once(dirname(__FILE__) . '/class.users.menus.inc');
include_once(dirname(__FILE__) . '/class.mysql.inc');
include_once(dirname(__FILE__) . '/class.user.inc');
include_once(dirname(__FILE__) . '/class.ini.inc');
include_once(dirname(__FILE__) . '/class.templates.inc');

function format_line($line){
	$ini=new Bs_IniHandler();
	if(posix_getuid()<>0){
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
		$ini->loadString($sock->GET_INFO("RTMMailConfig"));
	}else{
		if(is_file("/etc/artica-postfix/settings/Daemons/RTMMailConfig")){$ini->loadFile("/etc/artica-postfix/settings/Daemons/RTMMailConfig");}
	}
	
	if($ini->_params["Discard"]["row_color"]==null){$ini->_params["Discard"]["row_color"]="#D00000";}
	if($ini->_params["Discard"]["text_color"]==null){$ini->_params["Discard"]["text_color"]="#FFFFFF";}
	
	if($ini->_params["Greylisting"]["row_color"]==null){$ini->_params["Greylisting"]["row_color"]="#949494";}
	if($ini->_params["Greylisting"]["text_color"]==null){$ini->_params["Greylisting"]["text_color"]="#FFFFFF";}
	
	if($ini->_params["Relay_access_denied"]["row_color"]==null){$ini->_params["Relay_access_denied"]["row_color"]="#D00000";}
	if($ini->_params["Relay_access_denied"]["text_color"]==null){$ini->_params["Relay_access_denied"]["text_color"]="#FFFFFF";}	
	
	if($ini->_params["User_unknown_in_relay_recipient_table"]["row_color"]==null){$ini->_params["User_unknown_in_relay_recipient_table"]["row_color"]="#D00000";}
	if($ini->_params["User_unknown_in_relay_recipient_table"]["text_color"]==null){$ini->_params["User_unknown_in_relay_recipient_table"]["text_color"]="#FFFFFF";}	
	
	if($ini->_params["RBL"]["row_color"]==null){$ini->_params["RBL"]["row_color"]="#949494";}
	if($ini->_params["RBL"]["text_color"]==null){$ini->_params["RBL"]["text_color"]="#FFFFFF";}	
	
	if($ini->_params["hostname_not_found"]["row_color"]==null){$ini->_params["hostname_not_found"]["row_color"]="#FFECEC";}
	if($ini->_params["hostname_not_found"]["text_color"]==null){$ini->_params["hostname_not_found"]["text_color"]="#000000";}

	if($ini->_params["User_unknown"]["row_color"]==null){$ini->_params["User_unknown"]["row_color"]="#FFECEC";}
	if($ini->_params["User_unknown"]["text_color"]==null){$ini->_params["User_unknown"]["text_color"]="#000000";}	
		
	if($ini->_params["Domain_not_found"]["row_color"]==null){$ini->_params["Domain_not_found"]["row_color"]="#FFECEC";}
	if($ini->_params["Domain_not_found"]["text_color"]==null){$ini->_params["Domain_not_found"]["text_color"]="#000000";}

	if($ini->_params["DNS_Error"]["row_color"]==null){$ini->_params["DNS_Error"]["row_color"]="#D00000";}
	if($ini->_params["DNS_Error"]["text_color"]==null){$ini->_params["DNS_Error"]["text_color"]="#FFFFFF";}		
	
	if($ini->_params["SPAM"]["row_color"]==null){$ini->_params["SPAM"]["row_color"]="#F36C15";}
	if($ini->_params["SPAM"]["text_color"]==null){$ini->_params["SPAM"]["text_color"]="#FFFFFF";}

	if($ini->_params["SPAMMY"]["row_color"]==null){$ini->_params["SPAMMY"]["row_color"]="#FFC59E";}
	if($ini->_params["SPAMMY"]["text_color"]==null){$ini->_params["SPAMMY"]["text_color"]="#000000";}	

	if($ini->_params["Command died"]["text_color"]=$ini->_params["DNS_Error"]["text_color"]);
	if($ini->_params["Command died"]["row_color"]=$ini->_params["DNS_Error"]["row_color"]);
	
	
	$ini->_params["PostScreen"]["row_color"]=$ini->_params["Discard"]["row_color"];
	$ini->_params["PostScreen"]["text_color"]=$ini->_params["Discard"]["text_color"];
	
	$ini->_params["PostScreen_RBL"]["row_color"]=$ini->_params["RBL"]["row_color"];
	$ini->_params["PostScreen_RBL"]["text_color"]=$ini->_params["RBL"]["text_color"];	
	
	$ini->_params["timed_out"]["row_color"]=$ini->_params["DNS_Error"]["row_color"];
	$ini->_params["timed_out"]["text_color"]=$ini->_params["DNS_Error"]["text_color"];		
	
	$ini->_params["blacklisted"]["row_color"]=$ini->_params["Relay_access_denied"]["row_color"];
	$ini->_params["blacklisted"]["text_color"]=$ini->_params["Relay_access_denied"]["text_color"];		
	
	
	
	$Country=$line["Country"];
	$country_img=GetFlags($Country);
	$Region=$line["Region"];
	$smtp_sender=$line["smtp_sender"];
	$SPAM=$line["SPAM"];
	$rcpt=$line["delivery_user"];
	$mailfrom=$line["sender_user"];
	$bounce_error=trim($line["bounce_error"]);
	$time=$line["time_connect"];
	$spammy=$line["spammy"];
	
	
	
	if(trim($mailfrom)==null && trim($rcpt)==null){return null;}
	
	if(preg_match('#User unknown#',$bounce_error)){$bounce_error="User unknown";}
	if(preg_match('#250.+?[OokK]+#',$bounce_error)){$bounce_error="Sended";}
	if(preg_match('#delivered via#',$bounce_error)){$bounce_error="Sended";}
	if(preg_match('#Command died with status\s+#',$bounce_error)){$bounce_error="Command died";}
	
	
	
	
	if($spammy==1){$bounce_error="SPAMMY";}
	if($SPAM==1){$bounce_error="SPAM";}
	
	
	$today=date('Y-m-d');
	$time=str_replace($today,"",$time);
	$flag_infos=trim("$Country ($Region) $smtp_sender");
	$flag_infos=str_replace("()","",$flag_infos);
	if($flag_infos==null){
		$flag_infos="{RTMM_NO_GEOIP_OR_LOOPBACK}<br>$bounce_error<br>$time<br>$mailfrom";
	}
	
	
	if(trim($bounce_error)==null){$bounce_error="Sended";}
	if(preg_match("#(.+?)\s+(.+)#",$time,$re)){
		$time=texttooltip($re[2],"<strong style=font-size:14px>$time</strong>",null);
	}else{
		$time=texttooltip($time,"<strong style=font-size:14px>{today}</strong>",null);
	}
	
	if($mailfrom==null){$mailfrom="&nbsp;";}

	$bounce_error_key=str_replace(" ","_",$bounce_error);
	$bg_color="background-color:{$ini->_params[$bounce_error_key]["row_color"]};color:{$ini->_params[$bounce_error_key]["text_color"]}";
	if($bounce_error=="Mailbox unknown"){$bg_color="background-color:{$ini->_params["User_unknown"]["row_color"]};color:{$ini->_params["User_unknown"]["text_color"]}";}
	
	
	if(strlen($mailfrom)>23){
		$mailfrom =texttooltip(substr($mailfrom,0,20)."...","<strong style=font-size:14px>$mailfrom</strong>");
	}
	if(strlen($rcpt)>23){
		$rcpt =texttooltip(substr($rcpt,0,20)."...","<strong style=font-size:14px>$rcpt</strong>");
	}
	$html="
	<tr style='$bg_color'>
		<td width=1% style='background-color:white'>" . imgtootltip($country_img,"<strong style=font-size:14px>$flag_infos</strong>","Loadjs('postfix.stats.cnx.php?blocked-ip-single=$smtp_sender')")."</td>
		<td width=1% nowrap><strong>$time</strong></td>
		<td><strong>$mailfrom</td>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$rcpt</td>
		<td><strong>&laquo;$bounce_error&raquo;</td>
	</tr>";
return $html;
	
	//Region 	kas 	infected 	spammy 	blacklisted 	whitelisted
}

function GetFlags($Country){
	if($Country==null){return "flags/info.png";}
	$Country=strtolower($Country);
	$array["united states"]="us.png";
	$array["france"]="fr.png";
	$array["vietnam"]="vn.png";
	$array["brazil"]="br.png";
	$array["bulgaria"]="bg.png";
	$array["india"]="in.png";
	$array["korea, republic of"]="kr.png";
	$array["spain"]="es.png";
	$array["slovakia"]="sk.png";
	$array["poland"]="pl.png";
	$array["egypt"]="eg.png";
	$array["china"]="cn.png";
	$array["chile"]="cl.png";
	$array["thailand"]="th.png";
	$array["italy"]="it.png";
	$array["indonesia"]="id.png";
	$array["lebanon"]="lb.png";
	$array["israel"]="il.png";
	$array["japan"]="jp.png";
	$array['venezuela']="ve.png";
	$array["lithuania"]="lt.png";
	$array["argentina"]="ar.png";
	$array["mexico"]="mx.png";
	$array["colombia"]="co.png";
	$array["puerto rico"]="pr.png";
	$array["dominican republic"]="do.png";
	$array["united kingdom"]="uk.png";
	$array["peru"]="pe.png";
	$array["honduras"]="hn.png";
	$array["hungary"]="hu.png";
	$array["turkey"]="tr.png";
	$array["belarus"]="by.png";
	$array["canada"]="ca.png";
	$array["germany"]="de.png";
	$array["albania"]="al.png";
	$array["el salvador"]="els.png";
	$array["qatar"]="qa.png";
	$array["singapore"]="sg.png";
	$array["costa rica"]="cr.png";
	$array["romania"]="ro.png";
	$array["ukraine"]="ua.png";
	$array["portugal"]="pt.png";
	$array["russian federation"]="ru.png";
	$array["oman"]="om.png";
	$array["taiwan"]="tw.png";
	$array["czech republic"]="cs.png";
	$array["greece"]="gr.png";
	$array["morocco"]="ma.png";
	$array["uruguay"]=="uy.png";
	$array["kazakhstan"]="kz.png";
	$array["libyan arab jamahiriya"]="ly.png";
	$array["switzerland"]="ch.png";
	$array["austria"]="at.png";
	$array["philippines"]="ph.png";
	$array["bahrain"]="bh.png";
	$array["kenya"]="kn.png";
	$array["iran, Islamic Republic of"]="ir.png";
	$array["ireland"]="ie.png";
	$array["united arab emirates"]="ae.png";
	$array["macedonia"]="mk.png";
	$array["kuwait"]="kw.png";
	$array["belgium"]="be.png";
	$array["europe"]="eu.png";
	$array["netherlands"]="nl.png";
	$array["sweden"]="se.png";
	$array["pakistan"]="pak.png";
	$array["saudi arabia"]="saua.png";
	$array["uganda"]="ug.png";
	$array["algeria"]="alg.png";
	$array["nigeria"]="ng.png";
	$array["serbia"]="sr.png";
	$array["tunisia"]="tn.png";
	$array["guatemala"]="gt.png";
	$array["azerbaijan"]="az.png";
	$array["australia"]="ai.png";
	$array["bolivia"]="bo.png";
	
	
	
	$flag=$array[$Country];
	if($flag==null){
		if($GLOBALS["VERBOSE"]){echo "No flag for \"$Country\"\n";}
		$flag="info.png";}
	return "flags/$flag";
	}
?>