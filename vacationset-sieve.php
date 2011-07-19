<?php
  /*

$Id: vacationset-sieve.php,v 1.1 2003/11/06 11:25:30 avel Exp $

vacationset-sieve.phtml is a PHP script that sets up a vacation message 
via Cyrus sieve

Note: This script was written as part of work done for the Cooperative 
Housing Federation of Canada <http://www.chfc.ca/> where I help manage 
email and other related LAN services.

Copyright (C) 2003 Russell McOrmond <http://www.flora.ca/russell/>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

http://www.gnu.org/copyleft/gpl.html


---cut---

This script uses a library from
  http://sourceforge.net/projects/sieve-php/

And interacts with the sieve server part of Cyrus IMAPD
  http://asg.web.cmu.edu/cyrus/imapd/

*/

  // sieve-php library mentioned above
  include "./sieve-php.lib";

  // Domain this script is being used on.
  $domain = "chfc.ca";
  $sieveserver = "localhost";
  $sieveport = 2000;

  // Directory writeable by HTTP server, stores vacation messages/etc.
  $tempdir = "temp-sieve/";

  function  authenticate()  {
    Header("WWW-Authenticate: Basic realm=\"Email Username/password\"");
    Header("HTTP/1.0 401 Unauthorized");
    echo "You didn't authenticate - sorry\n";
    exit;
  }

  function setvacation() {
    global $userid, $password, $message, $aliases, $sieve;

    $alias = preg_split ("/[\s,]+/", $aliases);

    $scriptaliases="";
    foreach ($alias as $result) {
      if ($result == "") continue;
      if ($scriptaliases != "") $scriptaliases .=", ";
      $scriptaliases .= "\"$result\"";
    }
    if ($scriptaliases != "") 
      $scriptalias = ":addresses [$scriptaliases]";
    $vacationscript="# Set by vacationset-sieve.phtml
require \"vacation\";
vacation $scriptalias
\"" . addslashes($message) . "\";";

//  echo "<PRE>$vacationscript</PRE>";

    return $sieve->sieve_sendscript("vacationset",$vacationscript);
  }

  function read_aliases() {
    global $aliases, $userid, $tempdir;

    $aliases="";

    $filename = "$tempdir/$userid.vacation.aliases";
    if ($fd = @fopen ($filename, "r")) {
      while ($fd && !feof ($fd)) {
        $aliases .= fgets($fd, 4096);
      }
      fclose ($fd);
    }
    cleanup_aliases();
  }

  function cleanup_aliases() {
    global $aliases, $userid, $emailaddr;

    if ($aliases == "") {
      $aliases="$emailaddr\n";
    } else if (preg_match ("/\S/",$aliases)) {
      $alias = preg_split ("/[\s,]+/", $aliases);
      reset ($alias);
      $aliases="$emailaddr\n";
      while (list(, $email) = each ($alias)) {
        // Ignore main email address if it is already in the list.
        if($email == $emailaddr) continue;
        // Ignore lines that don't look like Email addresses
        if(eregi(
"^[a-z0-9]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$",$email))
          $aliases .= "$email\n";
      }
    }
  }

  function save_aliases() {
    global $aliases, $userid, $tempdir;

    cleanup_aliases();
    $filename = "$tempdir/$userid.vacation.aliases";
    if ($fd = @fopen ($filename, "w")) {
      @fwrite($fd,$aliases);
      fclose($fd);
    }
  }

  function html_head() {
?>

<HTML>
<HEAD>
<TITLE>CHF Canada: auto-responder for e-mail</TITLE>
</HEAD>
<BODY BGCOLOR="#FFFFFF">
<H1>CHF Canada e-mail auto-responder</H1>

<B>Note:</B><BR>
This is the new vacation system that is part of the new email server 
software.  Your old vacation messages may need to be reset.


<?php

  }

  function html_foot() {
?>
</BODY>
</HTML>
<?php
  }

  function ask_active_script($scriptname) {
    global $deactivatescript;

    html_head();
?><H2>Other script is active</H2>

Another script named "<?php echo $scriptname;?>" is currently active.  
This version of the vacation system does not modify existing scripts, 
it just sets a new script.  If you wish to use this vacation system you 
must deactivate current script.

<P>
<A HREF="<?php echo $PHP_SELF . "?deactivatescript=yes"; ?>">Deactivate 
script?</A>

<?php
    html_foot();
    exit;
  }



  // Start of Body of code...

  // Make sure user is authenticated...
  if(!isset($PHP_AUTH_USER)) {
    authenticate();
  }

  $userid=$PHP_AUTH_USER;
  $password=$PHP_AUTH_PW;
  $emailaddr = "$userid@$domain";

  // Connect to sieve server
  $sieve=new sieve($sieveserver, $sieveport, $userid, $password);

  // Right password for this user?
  if(!$sieve->sieve_login()) {
    authenticate();
  }

  // If the user requested, deactivate current script
  if($deactivatescript) {
    $sieve->sieve_setactivescript("");
  }

  // Verify that other script is not active
  if($sieve->sieve_listscripts())
    if(isset($sieve->response["ACTIVE"]))
      if($sieve->response["ACTIVE"] != "vacationset")
        ask_active_script($sieve->response["ACTIVE"]);

  if(!isset($message)) {
    read_aliases();
    html_head();
?>
<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF;?>">

<OL TYPE="A">


<LI><H2>Enable/Disable</H2>

<blockquote>
<INPUT TYPE="radio" NAME="mode" VALUE="set" checked>
       Enable auto-response message
<BR>Field_text()
<INPUT TYPE="radio" NAME="mode" VALUE="unset">
       Disable auto-response message
</blockquote>



<LI><H2>Message</H2>

<?php
  $filename="$tempdir/$userid.vacation.msg";
  if($fd = @fopen ($filename, "r")) {
    $vacationmsg = fread ($fd, filesize ($filename));
    fclose ($fd);
?>

<P>
Here is your existing outgoing message.
<BR>
<TEXTAREA NAME="message" ROWS="12" COLS="70">
<?php echo htmlspecialchars($vacationmsg);?>
</TEXTAREA>
<?php

  } else {
?>

You had no outgoing message.  Here is the default to start with.
<BR>
<TEXTAREA NAME="message" ROWS="12" COLS="70">
Hello,

I am gone for a while and will read your message upon my return.

</TEXTAREA>
<?php
  }


?>
<p>You should edit the body of the message to indicate when
   you will return and perhaps pass on other instructions.

<P>The subject of your reply will be "Re " and whatever they put as the 
subject.

<BR><HR><BR>

<INPUT TYPE="reset"  VALUE="Reset">
   If you have made any changes to the above text and wish to start over,
press reset to clear your changes.

<P>

<LI><H2>Aliases</H2>

<P> If you have other e-mail accounts that you have forwarded to your main
CHF address &lt;<?php echo $emailaddr; ?>&gt;, you may want to have the
auto-responder reply to messages addressed to those accounts as well.

<P>

Please add to the box below any addresses that are forwarded to this mailbox, for 
which you wish the auto-responder program to also send replies.

<P>

<TEXTAREA NAME="aliases" ROWS="5" COLS="40">
<?php echo htmlspecialchars($aliases);?>
</TEXTAREA>


<P>
<LI><H2>Finished</H2>


When you are satisfied with the above choices, select Submit.

<P>
<INPUT TYPE="submit" VALUE="Submit"> 

</OL>

</FORM>


<?php
    // $message was set
  } else {
    save_aliases();

    $filename="$tempdir/$userid.vacation.msg";
    if($fd = @fopen ($filename, "w")) {
      @fwrite($fd,stripslashes(chop($message))."\n");
      fclose($fd);
    }

    html_head();

    if (setvacation()) {
      if ($mode == "set") $sieve->sieve_setactivescript("vacationset");
      else $sieve->sieve_setactivescript("");
?>
<P>
<H2>Your choices have been saved.</H2>
<?php

    } else {

?>
<P>
<H2>Error setting vacation.</H2>

<?php
      echo "Error: "  . $sieve->error . "<BR>\n";
      foreach($sieve->error_raw as $errorline)
        print "errorline: $errorline<br>";

    }

?>
<P>
<A HREF="/">Back to server homepage</A><BR>
<A HREF="<?php echo $PHP_SELF;?>">Look at your 
auto-responder settings again</A>

<?php
  }

  html_foot();
?>
