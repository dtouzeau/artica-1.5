<?php
$fp=fopen("postix.xml", "r");
$usercount=0;
$userdata=array();
$state='';
if (!($xml_parser = xml_parser_create()))
        die("Couldn't create parser.");

        
        $data = fread($fp, filesize("postix.xml"));
fclose($fp);


xml_parse_into_struct($xml_parser, $data, $vals, $index);        

xml_parser_free($xml_parser);


$params = array();
$level = array();
foreach ($vals as $xml_elem) {
	
	echo $xml_elem['type'] . "\n";
	

	
	
  if ($xml_elem['type'] == 'open') {
    if (array_key_exists('attributes',$xml_elem)) {
      list($level[$xml_elem['level']],$extra) = array_values($xml_elem['attributes']);
    } else {
      $level[$xml_elem['level']] = $xml_elem['tag'];
    }
  }
  if ($xml_elem['type'] == 'complete') {
    $start_level = 1;
    $php_stmt = '$params';
    while($start_level < $xml_elem['level']) {
      $php_stmt .= '[$level['.$start_level.']]';
      $start_level++;
    }
    $php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
    eval($php_stmt);
  }
}

echo "<pre>";
print_r ($params);
echo "</pre>";

?>