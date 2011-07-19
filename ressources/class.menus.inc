<?php

class leftmenus{
	
	var $html;
	var $menus=array(
		"index.php"=>array(
			"title"=>"Main page"
		),
		
		"domains.php"=>array(
			"title"=>"Maiboxes-domains"
			),
			
		"global-filtering.php"=>array(
			"title"=>"Filters",
			"sub"=>array(
				"global-filtering.php?feature=mime"=>array(
					"title"=>"Attachments rules"
					),
				"global-filtering.php?feature=headers"=>array(
					"title"=>"Subject rules"
					),
				"global-filtering.php?feature=from"=>array(
					"title"=>"Mail From rules"
					)
				),
		 ),
		 
		 "aveserver.php"=>array(
		 	"title"=>"Kaspersky Antivirus",
		 	"cause"=>"kav_mail",
		 	"sub"=>array(
		 		"aveserver.php?viewlogs=yes"=>array(
		 			"title"=>"view logs"
		 			),
		 	
		 	)
		 	),
		 "kas.php"=>array(
		 	"title"=>"Kaspersky Anti-spam",
		 	"cause"=>"kas",
		 	"sub"=>array(
		 		"aveserver.php?viewlogs=yes"=>array(
		 			"title"=>"view logs"
		 			),
		 	
		 	)		 	
		)
		
	);
	
function getcause($cause)	{
	$sock=new sockets();
	if(!file_exists(dirname(__FILE__) . '/settings.inc')){
		writelogs("ERROR: unable to stat " . dirname(__FILE__) . '/settings.inc',__CLASS__ . "=>" . __FUNCTION__,__FILE__);
		return false;}	
	include(dirname(__FILE__) . '/settings.inc');
	switch ($cause) {
		case "kav_mail":
			if(trim($sock->getfile('avestatus'))>-1){return true;}
			break;
		case 'kas':
			if ($_GLOBAL["kas_installed"]==true){return true;}
			break;
		default:
			break;
	}
	
	
}
	
function leftmenus(){
		$mailbox=new MailBoxes();
		$dir=basename($_GET["url"]);
		preg_match('#([a-z\.\-]+)#',$dir,$reg);
		$base=$reg[1];
		$html="<table>";
		while (list ($num, $val) = each ($this->menus) ){
			$a_class='class=lev1';$d=false;
			$add=true;
			if($dir==$num){$a_class='class=lev1_selected';$d=true;}
			if($base==$num){$a_class='class=lev1_selected';$d=true;}
			if($val["cause"]<>null){
				if($this->getcause($val["cause"])==false){$add=false;}
			}
			
		if($add==true){
				$html=$html . "
				<tr>
				<td $a_class>
				<a href='$num'>{$val["title"]}</a></td>
				</tr>";
				if(is_array($val["sub"])){
					if($d==true){
						$html=$html .  $this->subLeft($val["sub"]) ;
						}
				}
			}
		}
		
	$this->html=$html . "\n</table>";
	
	}
	
	function subLeft($array){
		
		$dir=basename($_GET["url"]);
		preg_match('#([a-z\.\-]+)#',$dir,$reg);
		$base=$reg[1];		
		
	while (list ($url, $sub) = each ($array) ){
		$b_class='class=lev1';$d=false;
		if($dir==$url){$b_class='class=lev1_selected';$d=true;}
		if($base==$url){$b_class='class=lev1_selected';$d=true;}
		$html=$html . "<tr>
		<td $b_class>&nbsp;&nbsp;&nbsp;<a href='$url'>{$sub[ "title"]}</a></td>
		</tr>";
		}

		return $html;
	}
}

class top_menus{
	var $html;
	var $menus=array(
		
		"global-settings.php"=>array(
			"title"=>"Basic Configuration"
			),
			
		"mail.log.php"=>array(
			"title"=>"Postfix logs"
		),
		
		"artica.log.php"=>array(
			"title"=>"Artica web logs"
			),
		"services.status.php"=>array(
			"title"=>"Services status"
			),
		"logoff.php"=>array(
			"title"=>"logoff"
		)				
		);

	
	function top_menus(){
		
		$dir=basename($_GET["url"]);
		preg_match('#([a-z\.\-]+)#',$dir,$reg);	
		$html="<table align='right' style='width:10%' class='table'>";
		while (list ($num, $val) = each ($this->menus) ){
			$html=$html . "<td nowrap align='center'><a href='$num'>{$val["title"]}</a></td>";
		}
			$this->html=$html . "\n</tr>\n</table>";
	}
	
	
	
}



?>