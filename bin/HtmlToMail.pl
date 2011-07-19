#!/usr/bin/perl

use MIME::Lite;


sub main{
	my $s,$a,$q,$acl,$res;
	$s = join(' ', @ARGV);

	if($s=~/--help/){print "\n-----------\nDomain:$cydomain\n help:\n";usage();exit;}
	if($s=~/-u\s+([a-zA-Z0-9_\-\@\.]+)/){$cyrus_user=$1;}
	if($s=~/-p\s+([a-zA-Z0-9_\-\.\@]+)/){$cyrus_pass=$1;}
	if($s=~/-m\s+([a-zA-Z0-9_\-\.\@]+)/){$user=$1;}
	if($s=~/-q\s+([0-9]+)/){$q=$1;}
	if($s=~/-a\s+([a-z]+)/){$acl=$1;}

}



my $Message = new MIME::Lite
   From =>'expediteur@site.com',
   To =>'destinataire@site.com',
   Cc =>'copie@site.com, copie2@site.com',
   Bcc =>'copiecachee@site.com',
   Subject =>'Sujet de votre message HTML.',
   Type =>'TEXT',
   Data =>"<B>Contenu de votre message <U>HTML</U></B><BR>\n<I><U>Expediteur</U></I>";
# C'est ici que l'on change le content type, pour permettre au mail d'être interprété comme de l'HTML.
$Message->attr("content-type" => "text/html; charset=iso-8859-1");
$Message->send;
