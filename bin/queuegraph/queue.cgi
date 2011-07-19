#!/bin/sh
cat << EOF
Content-Type: text/html

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<HTML>
<HEAD><TITLE>Queue breakdown for mail.charite.de</TITLE></HEAD>
<BODY>
<H1>Postfix Queue Statistics for mail.charite.de</H1>
<UL>
<LI><A HREF="queuegraph.cgi">zur Queue Statistik</A>
<LI><A HREF="mailgraph.cgi">zum Durchsatz</A>
</UL>
<H1>Active Queue (gerade in Bearbeitung befindlich)</H1>
<PRE>
EOF
cat active
cat << EOF
</PRE>
<H1>Deferred Queue (verz&ouml;gert wegen Problemen)</H1>
<PRE>
EOF
cat deferred
cat << EOF
</PRE>
<H1>Errors (Fehler, abgewiesene Mails und sonstiges)</H1>
<PRE>
EOF
sed -e "s/</\&lt;/g" -e "s/>/\&gt;/g" errors
cat << EOF
</PRE>
</BODY>
</HTML>
EOF
