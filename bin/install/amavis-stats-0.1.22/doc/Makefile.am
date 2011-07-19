#
#  Copyright (C) 2005 Dale Walsh <buildsmart@daleenterprise.com>
#
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2 of the License, or
#  (at your option) any later version.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program; if not, write to the Free Software
#  Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

mylibdir		= ${localstatedir}/lib

mycachedir		= ${localstatedir}/cache

SUFFIXES = .in .pod .1 .man .html .txt .ps

PODSRC = amavis-stats.in

POD = $(PODSRC:.in=.pod) 
HTML = $(POD:.pod=.html) 
MAN = $(POD:.pod=.1) 
TXT = $(MAN:.1=.txt)
PS = $(MAN:.1=.ps)

# what should go into the distribution
EXTRA_DIST= $(PODSRC) 

# some install rules
idocdir = $(prefix)/doc
idoc_DATA = $(POD) $(TXT)
ihtmldir = $(pkgdatadir)
ihtml_DATA = $(HTML)
imandir = $(mandir)/man1
iman_DATA = $(MAN)

all-local: pod txt man html ps

.in.pod:
	@echo "  Making $@"
	@@SED@ \
	-e 's^\@SBINDIR\@^$(sbindir)^g' \
	-e 's^\@PKGCACHEDIR\@^$(mycachedir)^g' \
	-e 's^\@PKGLIBDIR\@^$(mylibdir)^g' \
	-e 's^\@SYSCONFDIR\@^$(sysconfdir)^g' \
	-e 's^\@PKGDATADIR\@^$(pkgdatadir)^g' \
	-e 's^\@PKGNAME\@^$(PACKAGE)^g' \
	-e 's^\@PKGVERSION\@^$(VERSION)^g' \
	$< > $@

.pod.1:
	@echo "  Making $@"
	@pod2man --release=$(VERSION) --center='amavisd-new companion' $<  > $@

.1.txt:
	@echo "  Making $@"
	@@NROFF@ -man -Tlp $< > $@ || echo @NROFF@ failed > $@

.1.ps:
	@echo "  Making $@"
	@@TROFF@ -man $< > $@

.pod.html:
	@echo "  Making $@"
	@pod2html --infile=$< --outfile=$@ --index --htmlroot=. --podpath=. --title=$*

pod: $(POD)

man: $(MAN)

html: $(HTML)

txt: $(TXT)

ps: $(PS)

install-exec-am:
	@echo "Processing document files...."

installdirs:
	$(mkinstalldirs) $(DESTDIR)$(idocdir)
	$(mkinstalldirs) $(DESTDIR)$(ihtmldir)
	$(mkinstalldirs) $(DESTDIR)$(imandir)

idocDATA_INSTALL = $(INSTALL_DATA)
install-idocDATA: installdirs $(idoc_DATA)
	@$(NORMAL_INSTALL)
	@list='$(idoc_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  echo "  Installing $(DESTDIR)$(idocdir)/$$p"; \
	  $(idocDATA_INSTALL) $$d$$p $(DESTDIR)$(idocdir)/$$p; \
	done

uninstall-idocDATA:
	@$(NORMAL_UNINSTALL)
	@list='$(idoc_DATA)'; for f in $$list; do \
	  echo "  Deleting $(DESTDIR)$(idocdir)/$$f"; \
	  rm -f $(DESTDIR)$(idocdir)/$$f; \
	done

ihtmlDATA_INSTALL = $(INSTALL_DATA)
install-ihtmlDATA: installdirs $(ihtml_DATA)
	@$(NORMAL_INSTALL)
	@list='$(ihtml_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  echo "  Installing $(DESTDIR)$(ihtmldir)/$$p"; \
	  $(ihtmlDATA_INSTALL) $$d$$p $(DESTDIR)$(ihtmldir)/$$p; \
	done

uninstall-ihtmlDATA:
	@$(NORMAL_UNINSTALL)
	@list='$(ihtml_DATA)'; for f in $$list; do \
	  echo "  Deleting $(DESTDIR)$(ihtmldir)/$$f"; \
	  rm -f $(DESTDIR)$(ihtmldir)/$$f; \
	done

imanDATA_INSTALL = $(INSTALL_DATA)
install-imanDATA: installdirs $(iman_DATA)
	@$(NORMAL_INSTALL)
	@list='$(iman_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  echo "  Installing $(DESTDIR)$(imandir)/$$p"; \
	  $(imanDATA_INSTALL) $$d$$p $(DESTDIR)$(imandir)/$$p; \
	done

uninstall-imanDATA:
	@$(NORMAL_UNINSTALL)
	@list='$(iman_DATA)'; for f in $$list; do \
	  echo "  Deleting $(DESTDIR)$(imandir)/$$f"; \
	  rm -f $(DESTDIR)$(imandir)/$$f; \
	done

CLEANFILES = *.pod *.1 *.man *.html *.txt *.ps *~ *.tmp
