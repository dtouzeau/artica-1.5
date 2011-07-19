<?php
include_once('ressources/class.users.menus.inc');
include_once ("ressources/jpgraph-3/src/jpgraph.php");
include_once ("ressources/jpgraph-3/src/jpgraph_pie.php");
include_once ("ressources/jpgraph-3/src/jpgraph_pie3d.php");
include_once ("ressources/class.templates.inc");
include_once ("ressources/class.user.inc");
include_once ("ressources/class.mysql.inc");
include_once('ressources/class.cyrus.inc');
include_once('ressources/class.ini.inc');

if(isset($_GET["flow"])){mailboxes_flow();exit;}
if(isset($_GET["GraphMailbox"])){pie_single_mailbox_user();}


$tpl=new templates();




function mailboxes_flow(){
		$tpl=new Templates();

$users=new usersMenus();
$users->LoadModulesEnabled();
if($users->EnableAmavisDaemon==0){die();}
if($users->EnableMysqlFeatures==0){die();}
			
		writelogs("user={$_SESSION['uid']}",__FUNCTION__,__FILE__);
		$cachfile="ressources/logs/{$_SESSION['uid']}_stat.ini";
		
		if(!file_exists($cachfile)){
			$build=true;
		}else{
			$Diff = round((time() - filemtime($cachfile))/60,0);
			writelogs("$cachfile=$Diff mn");
			if($Diff>10){$build=true;}else {$build=false;}
			
		}
		
if($build){		
					$user=new user($_SESSION['uid']);
					$AllMails=$user->aliases;
					$AllMails[]=$user->mail;
					
					while (list ($num, $array) = each ($AllMails) ){
						if($array==null){continue;}
							$recieve[]="OR mails_events.rcpt_to='{$array}'";
						
					}		
			$recieve[0]=str_replace("OR ",'',$recieve[0]);		
			$s=new mysql();
			$sql="SELECT count( spam ) as tspam FROM `mails_events` WHERE spam=1 AND (" .implode(" ",$recieve).")";
			$ligne=@mysql_fetch_array($s->QUERY_SQL($sql,"artica_events"));
			$spam=$ligne["tspam"];
			writelogs("{$_SESSION['uid']} SPAM=$spam",__FUNCTION__,__FILE__);
			
			$sql="SELECT count( infected ) as tinfected FROM `mails_events` WHERE infected=1 AND (" .implode(" ",$recieve).")";
			$ligne=@mysql_fetch_array($s->QUERY_SQL($sql,"artica_events"));
			$infected=$ligne["tinfected"];
			
			$sql="SELECT count( banned ) as tbanned FROM `mails_events` WHERE banned=1 AND (" .implode(" ",$recieve).")";
			$ligne=@mysql_fetch_array($s->QUERY_SQL($sql,"artica_events"));
			$banned=$ligne["tbanned"];
			
			$sql="SELECT count(ID) as tot FROM `mails_events` WHERE 1 AND (" .implode(" ",$recieve).")";
			$ligne=@mysql_fetch_array($s->QUERY_SQL($sql,"artica_events"));
			$total=$ligne["tot"];
			$tot=$total-$banned-$spam-$infected;
			
			$ini=new Bs_IniHandler();
			$ini->_params["FLOW"]["SPAM"]=$spam;
			$ini->_params["FLOW"]["INFECTED"]=$infected;
			$ini->_params["FLOW"]["BANNED"]=$banned;
			$ini->_params["FLOW"]["TOTAL"]=$tot;
			$ini->saveFile($cachfile);
			
}else{
	$ini=new Bs_IniHandler($cachfile);
	$spam=$ini->_params["FLOW"]["SPAM"];
	$infected=$ini->_params["FLOW"]["INFECTED"];
	$banned=$ini->_params["FLOW"]["BANNED"];
	$tot=$ini->_params["FLOW"]["TOTAL"];	
	
}

if($tot==0){die();}

$data = array($spam,$infected,$banned,$tot);

// Create the Pie Graph. 
$graph = new PieGraph(350,350,'auto');
//$graph->SetShadow();
$graph->title->Set("$total messages");
$graph->title->SetFont(FF_FONT1,FS_BOLD);


// Create
$p1 = new PiePlot3D($data);
//$p1->SetLegends(array("SPAM","Virus","Bann","Clean"));
$p1->SetLabels(array("SPAM:$spam","Virus:$infected","Bann:$banned","Clean:$tot"),1); 
$p1->SetEdge('black',1); 
$p1->SetAngle(75); 

$p1->SetLabelMargin(2); 
//$p1->SetCSIMTargets($targ,$alts);
// Use absolute labels
//$p1->SetLabelType(0);
//$p1->value->SetFormat("%d kr");
// Move the pie slightly to the left
$p1->SetCenter(0.4,0.5);
$p1->ExplodeAll(10); 
$graph->Add($p1);
$graph->SetFrame(false); 

// Send back the HTML page which will call this script again
// to retrieve the image.
$graph->StrokeCSIM();
}

function pie_single_mailbox_user(){

$tpl=new Templates();	
$users=new usersMenus();
$uid=$_SESSION["uid"];
if($users->cyrus_imapd_installed==0){return null;}
$ldap=new clladp();
$hash=$ldap->UserDatas($_SESSION["uid"]);
if($hash["MailboxActive"]<>'TRUE'){return null;}
$cyrus=new cyrus();
$res=$cyrus->get_quota_array($uid);
$size=$cyrus->MailboxInfosSize($uid);
$free=$cyrus->USER_STORAGE_LIMIT -$cyrus->USER_STORAGE_USAGE;
if(!$cyrus->MailBoxExists($uid)){return null;}
		
    
   

$USER_STORAGE_USAGE=$cyrus->USER_STORAGE_USAGE;
$USER_STORAGE_LIMIT=$cyrus->USER_STORAGE_LIMIT;
$FREE=$free;
writelogs("USER_STORAGE_USAGE=$USER_STORAGE_USAGE",__FUNCTION__,__FILE__);
writelogs("STORAGE_LIMIT=$USER_STORAGE_LIMIT",__FUNCTION__,__FILE__);

if($USER_STORAGE_LIMIT==null){
	$USER_STORAGE_LIMIT=1000000;
	$USER_STORAGE_USAGE=0;
	$FREE=$USER_STORAGE_LIMIT;
}


$USER_STORAGE_RESTANT=$USER_STORAGE_LIMIT-$USER_STORAGE_USAGE;
if($USER_STORAGE_RESTANT>1){
	$reste=round(($USER_STORAGE_RESTANT/1024));
	$data = array($USER_STORAGE_USAGE,$USER_STORAGE_RESTANT);
}else{$data=array($USER_STORAGE_USAGE);}
$title=$tpl->_ENGINE_parse_body("{your mailbox usage} ($reste mb free)");
writelogs("USER_STORAGE_USAGE=$USER_STORAGE_USAGE - USER_STORAGE_LIMIT=$USER_STORAGE_LIMIT FREE=$FREE",__FUNCTION__,__FILE__);


$date=date('Y-m-d');	
$textes=array();
$donnees=array();
$zlabel=array();
$date=date('Y-m-d');
$donnees[] =$FREE;
$textes[] = "$FREE Free";
$donnees[] = $USER_STORAGE_USAGE;
$textes[] = "$USER_STORAGE_USAGE used";	
$data = $donnees;
$graph = new PieGraph(370,350,'auto');
//$graph->SetShadow();
$graph->title->Set($title);
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$p1 = new PiePlot3D($data);
$p1->SetLabels($textes,1); 
$p1->SetEdge('black',0); 
$p1->SetAngle(55); 
$p1->SetLabelMargin(2); 
$p1->SetCenter(0.4,0.5);
$p1->ExplodeAll(10); 
$graph->Add($p1);
$graph->SetFrame(false); 
$graph->StrokeCSIM();


	}