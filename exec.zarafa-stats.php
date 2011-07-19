<?php

define('PHP_MAPI_LIB_DIR', '/usr/share/php/mapi');
$requiredFiles = array('mapi.util.php', 'mapiguid.php', 'mapidefs.php', 'mapitags.php');
foreach ($requiredFiles as $dependency) {
   require(sprintf("%s/%s", PHP_MAPI_LIB_DIR, $dependency));
}

$propertyCache = array();
$constants = get_defined_constants();
foreach ($constants as $tag => $integerValue) {
   if (!is_numeric($integerValue))
      continue;
   $propertyCache[$integerValue] = $tag;
}

$command = sprintf("zarafa-stats %s", $argv[1]);
$rawOutput = shell_exec($command);
$inputLines = split("\n", $rawOutput);

foreach ($inputLines as $line) {
   $line = trim($line);
   $pos = strpos($line, ':');
   if (!$pos) {
      echo $line . "\n";
      continue;
   }

   $mapiPropertyHex = substr($line, 0, $pos);
   $mapiOutput = trim(substr($line, $pos+1));
   $lookupIndex = hexdec(substr($mapiPropertyHex, 2));
   $tag = $propertyCache[$lookupIndex];

   $cmdOutput = (!$tag) ? sprintf("%s: %s\n", $mapiPropertyHex, $mapiOutput) : sprintf("%s: %s\n", $tag, $mapiOutput);
   echo $cmdOutput;

}
?>