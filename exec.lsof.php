<?php

$port=$argv[1];


$cmd="lsof |grep -E \"IPv4.+?$port\"";

while (true) {
	system($cmd);
}
?>