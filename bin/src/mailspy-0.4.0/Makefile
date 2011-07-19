# mailspy
#
# Copyright (c) 2001, 2002 Andrew McGill and Leading Edge Business Solutions
# (South Africa).  
#
# Mailspy is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

CFLAGS=-g -Wall -pthread
CC = gcc
LD=$(CC) $(CFLAGS)

TARGETS   = mailspy
MCFILE    = /etc/mail/linux.mc
SENDMAILCF= /etc/sendmail.cf
LIBS      = -lmilter -lsmutil
# For older versions of sendmail (pre 8.12?), you may need sfio linked in ...
# LIBS      = -lmilter -lsmutil -lsfio
BINDIR    = /usr/local/bin

SGMLFILES = mailspy.sgml
HTMLFILES = mailspy.html
TXTFILES  = mailspy.txt
MISCFILES = mailspy.m4 Makefile COPYING Changelog TODO rcmailspy crontab.spy mailspy-procmailrc \
	mailspy.conf
PSFILES   = mailspy.ps
TARGETS   = mailspy
VERSION   = 0.4.0
DISTFILE  = mailspy-$(VERSION).tar.gz
RCDIR     = /etc/init.d
BINFILES  = mailspy
SCRIPTS   = htmlspy.pl cronspy.sh
ETCFILES  = mailspy-procmailrc crontab.spy

MSSOURCE  = mailspy.c
MSOBJECTS = mailspy.o

all: $(TARGETS)

mailspy: $(MSOBJECTS)
	$(LD) -o mailspy $(MSOBJECTS) $(LIBS) 

clean:
	rm -f $(TARGETS) mailspy-$(VERSION).tar.gz $(PSFILES) $(TXTFILES) $(MSOBJECTS)

# Wow! You want to install it?

install: $(TARGETS)
	install -d $(DESTDIR)$(BINDIR)
	install -d $(DESTDIR)$(RCDIR)
	install $(SCRIPTS) $(BINFILES) $(DESTDIR)$(BINDIR)
	install $(ETCFILES) $(DESTDIR)$(BINDIR)
	cp rcmailspy $(DESTDIR)$(RCDIR)/mailspy
	chmod 755 $(DESTDIR)$(RCDIR)/mailspy
	ln -sf $(DESTDIR)$(RCDIR)/mailspy $(DESTDIR)/usr/sbin/rcmailspy
	@echo Enabling mailspy on startup - this may fail...
	-chkconfig mailspy on
	[ -e /etc/mailspy.conf ] || install mailspy.conf /etc

uninstall: $(TARGETS)
	for FILE in $(SCRIPTS); do rm -f $(DESTDIR)$(BINDIR)$$FILE ; done
	for FILE in $(ETCFILES); do rm -f $(DESTDIR)$(BINDIR)$$FILE ; done
	rm -f $(DESTDIR)$(RCDIR)/mailspy
	rm -f $(DESTDIR)$(RCDIR)/rc2.d/S15mailspy
	rm -f $(DESTDIR)$(RCDIR)/rc3.d/S15mailspy
	rm -f $(DESTDIR)/usr/sbin/rcmailspy

# Start it about now ...
start:
	# check that $(MCFILE) exists
	test -w $(MCFILE)
	grep -q '_FFR_MILTER' $(MCFILE) || echo >> $(MCFILE) "define(\`_FFR_MILTER')dnl"
	grep -q 'mailspy' $(MCFILE) || echo >> $(MCFILE) "INPUT_MAIL_FILTER(\`mailspy', \`S=local:/var/run/mailspy/milter, F=T')dnl"
	m4 $(MCFILE) > $(SENDMAILCF)
	killall -HUP sendmail
	-$(RCDIR)/mailspy restart

ps: $(PSFILES)

%.ps: %.sgml
	sgml2latex --papersize=a4 --language=english --output=ps $<

%.txt: %.sgml
	sgml2txt $<

%.html: %.sgml
	sgml2html -s 0 $<

MANIFEST=$(MSSOURCE) \
	$(SGMLFILES) \
	$(MISCFILES) \
	$(ETCFILES) \
	$(SCRIPTS)

doc:	$(TXTFILES) \
	$(HTMLFILES)

mailspy-$(VERSION).tar.gz: $(MANIFEST)
	mkdir -p mailspy-$(VERSION)
	cp $(MANIFEST) mailspy-$(VERSION)
	tar czf mailspy-$(VERSION).tar.gz mailspy-$(VERSION)
	rm -rf mailspy-$(VERSION)
	ln -sf mailspy-$(VERSION).tar.gz mailspy.tar.gz

dist: mailspy-$(VERSION).tar.gz

smtest: testsm

testsm: mailspy
	rm -r log
	mkdir -p log
	tail -200 /var/log/mailspy/mailspy | ./htmlspy.pl log

test: mailspy
	mkdir -p log
	./htmlspy.pl < /var/log/mailspy/mailspy log

anontest: mailspy
	./htmlspy.pl < /var/log/mailspy/mailspy log manglewords

