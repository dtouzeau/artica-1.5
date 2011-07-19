<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");

include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_line.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_pie.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_pie3d.php');
$user=new usersMenus();
if(!$user->AsMailBoxAdministrator){
	$tpl=new templates();
	echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
	exit;
}


if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["stats"])){stats();exit;}
if(isset($_GET["ev"])){events();exit;}
if(isset($_GET["daemon"])){daemon();exit;}

js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_FETCHMAIL}:: {events}");
	
	$html="
		function fetchmail_events_start(){
			YahooWin3('600','$page?popup=yes','$title');
		}
	
	
	fetchmail_events_start();";
	
	echo $html;
}


function popup(){
	
	$array["stats"]="{statistics}";
	$array["ev"]="{events}";
	$array["daemon"]="{daemon}";
	$tpl=new templates();
	$page=CurrentPageName();
	while (list ($num, $ligne) = each ($array) ){		
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=1\"><span>$ligne</span></a></li>\n");
		}
	echo "
	<div id=fetchamil_events_config style='width:100%;height:730px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#fetchamil_events_config').tabs({
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

function stats(){
	
	$sql="SELECT COUNT(ID) as tcount FROM fetchmail_events WHERE DATE_FORMAT(zDate,'%Y-%m-%d')=DATE_FORMAT(NOW(),'%Y-%m-%d')";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	$hits_today=$ligne["tcount"];
	if($hits_today==null){$hits_today=0;}
	
	
	$sql="SELECT SUM(size) as tsize FROM fetchmail_events WHERE DATE_FORMAT(zDate,'%Y-%m-%d')=DATE_FORMAT(NOW(),'%Y-%m-%d')";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	$size=$ligne["tsize"];
	if($size==null){$size=0;}	
	$size=FormatBytes($size/1024);
	
	$html="<div style='margin-bottom:5px;text-align:right'>". imgtootltip("refresh-32.png","{refresh}","RefreshTab('fetchamil_events_config')")."</div>
	<table style='width:100%'>
	<tr>
		<td valign='top'><H3>{today}:</H3><hr></td>
		<td valign='top'>
			<table style='width:100%'>
				<tr>
					<td valign='top' class=legend>{nb_fetchmails}</td>
					<td valing='top'><strong>$hits_today</td>
				</tr>
				<tr>
					<td valign='top' class=legend>{nb_fetchmails_size}</td>
					<td valing='top'><strong>$size</td>
				</tr>	
			</table>
		</td>
	</tr>
	</table>		
	<center>
	<img src='". courbe_day()."' style='margin:3px'>	
	<img src='". courbe_month()."' style='margin:3px'>
	</center>
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function courbe_day(){
$tpl=new templates();	

$q=new mysql();		
$sql="SELECT COUNT(ID) as tcount, DATE_FORMAT(zDate,'%H') as thour FROM fetchmail_events WHERE DATE_FORMAT(zDate,'%Y-%m-%d')=DATE_FORMAT(NOW(),'%Y-%m-%d')
 GROUP BY thour;";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$ydata[]=$ligne["tcount"];
	$xdata[] =$ligne["thour"];
}

	$f_name="hourly-fetchmail.png";
	$fileName = "ressources/logs/$f_name";
/*	if(is_file($fileName)){
		if(file_get_time_min($fileName)<120){return $fileName;}
	}*/
@unlink($fileName);
$title="$domain ". $tpl->_ENGINE_parse_body('{today}');
$YNAME=$tpl->_ENGINE_parse_body("{emails_number}");
$XNAME=$tpl->_ENGINE_parse_body("{hours}");

$width = 500; $height = 200;
$graph = new Graph($width,$height);
try{
	$graph->img->SetColor ("red@0.9");
	$graph->SetMarginColor('#FFFFFF');
	$graph->SetScale('textlin');
	$graph->title->Set($title);
	$graph->title->SetColor('#005447');
	$graph->xaxis->title->Set($XNAME);
	$graph->xaxis->SetTickLabels($xdata);
	$graph->yaxis->title->Set($YNAME);
	$graph->yaxis->scale->SetGrace(10);
	//$graph->SetBackgroundGradient('darkred:0.7', 'black', 2, BGRAD_MARGIN);
	//$graph->SetPlotGradient('black','darkred:0.8', 2);
	$graph->SetMargin(55,20,60,20);
	$graph->xaxis->SetColor('black');
	$graph->yaxis->SetColor('black');
	$graph->xgrid->Show();
	
	
	$lineplot=new LinePlot($ydata);
	$lineplot->SetWeight(2);
	$lineplot->SetColor('#005447');
$lineplot->SetFillColor('green@0.5');
	$lineplot->SetFillFromYMin();
	$lineplot->SetWeight (3 ); 
	$lineplot->SetFilled(true);
	$lineplot->SetFillFromYMin(true);
	$graph->Add($lineplot);
	JpGraphError::SetImageFlag(true);
$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
} catch ( JpGraphException $e ) {
    // .. do necessary cleanup
    // Send back error message
   // $e->Stroke();
}
$graph->img->Stream($fileName);
return $fileName;	

}


function courbe_month(){
$tpl=new templates();	

$q=new mysql();		
$sql="SELECT COUNT(ID) as tcount, DATE_FORMAT(zDate,'%d') as tday FROM fetchmail_events WHERE MONTH(zDate)=MONTH(NOW()) 
AND YEAR (zDate)=YEAR(NOW()) GROUP BY tday ORDER BY tday;";

$results=$q->QUERY_SQL($sql,"artica_events");

while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$ydata[]=$ligne["tcount"];
	$xdata[] =$ligne["tday"];
}

	$f_name="month-fetchmail.png";
	$fileName = "ressources/logs/$f_name";
/*	if(is_file($fileName)){
		if(file_get_time_min($fileName)<120){return $fileName;}
	}*/
@unlink($fileName);
$title="$domain ". $tpl->_ENGINE_parse_body('{this_month}');
$YNAME=$tpl->_ENGINE_parse_body("{emails_number}");
$XNAME=$tpl->_ENGINE_parse_body("{days}");

$width = 500; $height = 200;
$graph = new Graph($width,$height);
try{
$graph->img->SetColor ("red@0.9");
$graph->SetMarginColor('#FFFFFF');
$graph->SetScale('textlin');
$graph->title->Set($title);
$graph->title->SetColor('#005447');
$graph->xaxis->title->Set($XNAME);
$graph->xaxis->SetTickLabels($xdata);
$graph->yaxis->title->Set($YNAME);
$graph->yaxis->scale->SetGrace(10);
//$graph->SetBackgroundGradient('darkred:0.7', 'black', 2, BGRAD_MARGIN);
//$graph->SetPlotGradient('black','darkred:0.8', 2);
$graph->SetMargin(55,20,60,20);
$graph->xaxis->SetColor('black');
$graph->yaxis->SetColor('black');
$graph->xgrid->Show();


$lineplot=new LinePlot($ydata);
$lineplot->SetWeight(2);
$lineplot->SetColor('#005447');
$lineplot->SetFillColor('green@0.5');

//$lineplot->SetFillColor('white@0.9');
$lineplot->SetFillFromYMin();
$lineplot->SetWeight (3 ); 
$lineplot->SetFilled(true);
$lineplot->SetFillFromYMin(true);
$graph->Add($lineplot);

JpGraphError::SetImageFlag(false);


	$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
} catch ( JpGraphException $e ) {
    // .. do necessary cleanup
 
    // Send back error message
   // $e->Stroke();
}
$graph->img->Stream($fileName);
return $fileName;	

}

function events(){
	
	$sql="SELECT * FROM fetchmail_events ORDER BY zDate DESC LIMIT 0,300";
	$html="<div style='margin-bottom:5px;text-align:right'>". imgtootltip("refresh-32.png","{refresh}","RefreshTab('fetchamil_events_config')")."</div>
	<table style='width:100%'>
	<tr>
	<th>{date}</th>
	<th>{account}</th>
	<th>{server}</th>
	<th>{size}</th>
	</tr>";
	
	$today=date('Y-m-d');
	$year=date('Y');
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ligne["zDate"]=str_replace($today,'{today}',$ligne["zDate"]);
		$ligne["zDate"]=str_replace("$year-","",$ligne["zDate"]);
		
		$html=$html."
		<tr ". CellRollOver().">
			<td nowrap><strong>{$ligne["zDate"]}</td>
			<td nowrap><strong>{$ligne["account"]}</td>
			<td nowrap><strong>{$ligne["server"]}</td>
			<td nowrap><strong>". FormatBytes($ligne["size"]/1024)."</td>
		</tr>
		";
	}	
	
	$html=$html."</table>";
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function daemon(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?fetchmail-logs=yes")));
	if(!is_array($array)){return null;}
	$html="<div style='margin-bottom:5px;text-align:right'>". imgtootltip("refresh-32.png","{refresh}","RefreshTab('fetchamil_events_config')")."</div>";
	while (list ($key, $line) = each ($array) ){
		$html=$html."<div style='margin:2px;padding:2px><code style='font-size:11px'>$line</code></div>\n";
		
	}
	
	$html="<div style='heigth:250px;overflow:auto'>$html</div>";
	echo $html;
}


?>