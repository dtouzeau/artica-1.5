<?php
if($argv[1]==null){$argv[1]="/home/dtouzeau/Téléchargements";}
if(!is_dir($argv[1])){echo "No such dir\n";}
$GLOBALS["SOURCEDIR"]=$argv[1];
if($argv[1]=='--archiver'){$GLOBALS["SOURCEDIR"]="/home/dtouzeau/Téléchargements";zarafa_archiver();die();}


echo "Listing {$GLOBALS["SOURCEDIR"]}/zcp.*.tar.gz\n";
foreach (glob("{$GLOBALS["SOURCEDIR"]}/zcp-*.tar.gz") as $filename) {
		$file=basename($filename);
		echo "Processing $file\n";
		processtgz($filename);
		
		
	}
	
	if(is_dir("{$GLOBALS["SOURCEDIR"]}/zarafa")){
	//shell_exec("rm -rf {$GLOBALS["SOURCEDIR"]}/zarafa");
	}
	
function processtgz($filepath){
	$filename=basename($filepath);
	if(preg_match("#zcp-([0-9\.]+)-([0-9]+)-([a-z]+)-([0-9\.]+)-(.+?)-supported#",$filename,$re)){
	}else{
		echo "No match $filename\n";
		return;
	}
	$originalFileName=str_replace("-supported.tar.gz","",$filename);
	$proc=$re[5];
	$procOrg=$re[5];
	if($proc=="i586"){$proc="i386";}
	if($proc=="i686"){$proc="i386";}
	if($proc=="x86_64"){$proc="x64";}
	$version=$re[1];
	$versionOrg=$version;
	$versiontr=explode(".",$version);
	while (list ($index, $numero) = each ($versiontr) ){
		if($index==0){continue;}
		if(strlen($numero)==1){$versiontr[$index]="{$numero}0";}
	}
	
	$version=@implode(".",$versiontr);
	
	$build=$re[2];
	$linux=$re[3];
	$Linuxorg=$re[3];
	$linux_ver=$re[4];
	
	if($linux=="rhel"){$linux="centos";}
	if($linux=="sles"){$linux="opensuze";}
	
	if($linux=="opensuze"){
		$package_name="zarafa-$proc-opensuse-$version.tar.gz";
	}
	
	if($linux=="centos"){
		$package_name="zarafa-$proc-centos-$version.tar.gz";
	}

	if($linux=="debian"){
		if($linux_ver=="5.0"){
			$package_name="zarafa-debian50-$proc-$version.tar.gz";
		}
		if($linux_ver=="6.0"){
			$package_name="zarafa-debian60-$proc-$version.tar.gz";
		}		
	}

	if($linux=="ubuntu"){
		
		if($linux_ver=="8.04"){
			$package_name="zarafa-ubuntu80-$proc-$version.tar.gz";
		}
		if($linux_ver=="10.04"){
			$package_name="zarafa-ubuntu100-$proc-$version.tar.gz";
		}		
	}	
	
	$TargetDir="{$GLOBALS["SOURCEDIR"]}/zarafa/$proc/$linux/$linux_ver/$version-$build";
	$TargetDirBuild="$TargetDir/build";
	
	
	@mkdir($TargetDirBuild,0755,true);
	echo "extracting $filename tar -xf $filepath -C $TargetDir\n";
	shell_exec("tar -xf $filepath -C $TargetDir");
	
	echo "Search dir $TargetDir/$originalFileName\n";
	if(is_dir("$TargetDir/$originalFileName")){
		
		$TargetDir="$TargetDir/$originalFileName";
	}
	
	

	
	echo "Search $TargetDir/*.rpm\n";
	foreach (glob("$TargetDir/*.rpm") as $debfile) {
		$results=array();
		echo "converting $debfile\n";
		shell_exec("rpm2cpio $debfile | lzma -t -q > /dev/null 2>&1");
		shell_exec("rpm2cpio $debfile | (cd $TargetDirBuild;  cpio --extract --make-directories --no-absolute-filenames --preserve-modification-time)");
		@unlink($debfile);
		
	}	
	echo "Search $TargetDir/*.rpm\n";
	foreach (glob("$TargetDir/*.deb") as $debfile) {
		echo "extracting $debfile\n";
		shell_exec("dpkg-deb -x $debfile $TargetDirBuild/");
		@unlink($debfile);
		
	}
	
	
	if($package_name==null){echo "WARNING uknown package $proc/$linux/$linux_ver";}
	echo "compressing to $package_name\n";
	if(is_file("{$GLOBALS["SOURCEDIR"]}/zarafa-compiled/$package_name")){@unlink("{$GLOBALS["SOURCEDIR"]}/zarafa-compiled/$package_name");}
	shell_exec("cd $TargetDirBuild && tar -czf $package_name *");
	echo "compressing moving $package_name to {$GLOBALS["SOURCEDIR"]}/zarafa-compiled\n";
	@mkdir("{$GLOBALS["SOURCEDIR"]}/zarafa-compiled",0755,true);
	@mkdir("{$GLOBALS["SOURCEDIR"]}/zarafa-sources-packages",0755,true);
	shell_exec("/bin/mv $TargetDirBuild/$package_name {$GLOBALS["SOURCEDIR"]}/zarafa-compiled/$package_name");
	shell_exec("/bin/mv $filepath {$GLOBALS["SOURCEDIR"]}/zarafa-sources-packages/");
	echo "cleaning $TargetDir\n"; 
	shell_exec("/bin/rm -rf $TargetDir");
	shell_exec("chown -R dtouzeau:dtouzeau {$GLOBALS["SOURCEDIR"]}/zarafa*");
	
	
}

function zarafa_archiver(){
	
echo "Listing {$GLOBALS["SOURCEDIR"]}/zarafa-archiver.*.tar.gz\n";
foreach (glob("{$GLOBALS["SOURCEDIR"]}/zarafa-archiver-*.tar.gz") as $filename) {
		$file=basename($filename);
		echo "Processing $file\n";
		processArchiverTgz($filename);
		
		
	}	
	
}

function processArchiverTgz($filepath){
	$filename=basename($filepath);
	//zarafa-archiver-7.0.0-27791-ubuntu-10.04-x86_64.tar.gz
	if(preg_match("#zarafa-archiver-([0-9\.]+)-([0-9]+)-([a-z]+)-([0-9\.]+)-(.+?)\.tar.gz#",$filename,$re)){
	}else{
		echo "No match $filename\n";
		return;
	}
	$originalFileName=str_replace(".tar.gz","",$filename);
	$proc=$re[5];
	$procOrg=$re[5];
	if($proc=="i586"){$proc="i386";}
	if($proc=="i686"){$proc="i386";}
	if($proc=="x86_64"){$proc="x64";}
	$version=$re[1];
	$versionOrg=$version;
	$versiontr=explode(".",$version);
	while (list ($index, $numero) = each ($versiontr) ){
		if($index==0){continue;}
		if(strlen($numero)==1){$versiontr[$index]="{$numero}0";}
	}
	
	$version=@implode(".",$versiontr);
	
	$build=$re[2];
	$linux=$re[3];
	$Linuxorg=$re[3];
	$linux_ver=$re[4];
	
	
if($linux=="rhel"){$linux="centos";}
	if($linux=="sles"){$linux="opensuze";}
	
	if($linux=="opensuze"){$package_name="zarafa-archiver-$proc-opensuse-$version.tar.gz";}
	if($linux=="centos"){$package_name="zarafa-archiver-$proc-centos-$version.tar.gz";}

	if($linux=="debian"){
		if($linux_ver=="5.0"){$package_name="zarafa-archiver-debian50-$proc-$version.tar.gz";}
		if($linux_ver=="6.0"){$package_name="zarafa-archiver-debian60-$proc-$version.tar.gz";}		
	}

	if($linux=="ubuntu"){
		if($linux_ver=="8.04"){$package_name="zarafa-archiver-ubuntu80-$proc-$version.tar.gz";}
		if($linux_ver=="10.04"){$package_name="zarafa-archiver-ubuntu100-$proc-$version.tar.gz";}		
	}	
	
	$TargetDir="{$GLOBALS["SOURCEDIR"]}/zarafa-archiver/$proc/$linux/$linux_ver/$version-$build";
	$TargetDirBuild="$TargetDir/build";	

	echo "Processing zarafa-archiver version: $version build: $build Linux: $linux v.$linux_ver\n";
	
	@mkdir($TargetDirBuild,0755,true);
	echo "extracting $filename tar -xf $filepath -C $TargetDir\n";
	shell_exec("tar -xf $filepath -C $TargetDir");

	echo "Search dir $TargetDir/$originalFileName\n";
	if(is_dir("$TargetDir/$originalFileName")){$TargetDir="$TargetDir/$originalFileName";}
	
	

	echo "Search $TargetDir/*.rpm\n";
	foreach (glob("$TargetDir/*.rpm") as $debfile) {
		$results=array();
		echo "converting $debfile\n";
		shell_exec("rpm2cpio $debfile | lzma -t -q > /dev/null 2>&1");
		shell_exec("rpm2cpio $debfile | (cd $TargetDirBuild;  cpio --extract --make-directories --no-absolute-filenames --preserve-modification-time)");
		@unlink($debfile);
		
	}	

	echo "Search $TargetDir/*.deb\n";
	foreach (glob("$TargetDir/*.deb") as $debfile) {
		echo "extracting $debfile\n";
		shell_exec("dpkg-deb -x $debfile $TargetDirBuild/");
		@unlink($debfile);
		
	}
		
	if($package_name==null){echo "WARNING uknown package $proc/$linux/$linux_ver";}
	echo "compressing to $package_name\n";
	if(is_file("{$GLOBALS["SOURCEDIR"]}/zarafa-compiled/$package_name")){@unlink("{$GLOBALS["SOURCEDIR"]}/zarafa-compiled/$package_name");}
	shell_exec("cd $TargetDirBuild && tar -czf $package_name *");
	echo "compressing moving $package_name to {$GLOBALS["SOURCEDIR"]}/zarafa-compiled\n";
	@mkdir("{$GLOBALS["SOURCEDIR"]}/zarafa-compiled",0755,true);
	@mkdir("{$GLOBALS["SOURCEDIR"]}/zarafa-sources-packages",0755,true);
	shell_exec("/bin/mv $TargetDirBuild/$package_name {$GLOBALS["SOURCEDIR"]}/zarafa-compiled/$package_name");
	shell_exec("/bin/mv $filepath {$GLOBALS["SOURCEDIR"]}/zarafa-sources-packages/");
	if(is_dir($TargetDir)){
		echo "cleaning $TargetDir\n"; 
		shell_exec("/bin/rm -rf $TargetDir");
	}
	if(is_dir("{$GLOBALS["SOURCEDIR"]}/zarafa")){
		echo "Apply permissions on {$GLOBALS["SOURCEDIR"]}/zarafa*\n";
		shell_exec("chown -R dtouzeau:dtouzeau {$GLOBALS["SOURCEDIR"]}/zarafa*");
	}	
	
	
}

