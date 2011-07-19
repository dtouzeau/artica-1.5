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

configdir		= $(sysconfdir)

config_DATA		= amavis-stats.conf

EXTRA_SCRIPTS	= amavis-stats.conf.in

EXTRA_DIST		= $(EXTRA_SCRIPTS)

SUFFIXES = .in

install-exec-am:
	@echo "Processing daemon configuration file...."

install-configDATA: $(config_DATA)
	@$(mkinstalldirs) $(DESTDIR)$(configdir)
	@$(NORMAL_INSTALL)
	@list='$(config_DATA)'; for p in $$list; do \
	test -f $(DESTDIR)$(configdir)/$$p && echo "  $(DESTDIR)$(configdir)/$$p already exists...skipping" || \
	echo "  Installing $(DESTDIR)$(configdir)/$$p" && \
	$(configDATA_INSTALL) $$p $(DESTDIR)$(configdir)/$$p; \
	echo "  Installing $(DESTDIR)$(configdir)/$$p.default" && \
	$(configDATA_INSTALL) $$p $(DESTDIR)$(configdir)/$$p.default; \
	done

uninstall-configDATA:
	@$(NORMAL_UNINSTALL)
	@list='$(config_DATA)'; for f in $$list; do \
	  echo "  Deleting $(DESTDIR)$(configdir)/$$f"; \
	  rm -f $(DESTDIR)$(configdir)/$$f; \
	done

.in:
	@echo "  Making $@"
	@@SED@ \
	-e 's^\@SCAN_LOG_FILE\@^$(SCAN_LOG_FILE)^g' \
	-e 's^\@WEB_USER\@^$(WEB_USER)^g' \
	-e 's^\@WEB_GROUP\@^$(WEB_GROUP)^g' \
	-e 's^\@PKGCACHEDIR\@^$(mycachedir)^g' \
	-e 's^\@PKGLIBDIR\@^$(mylibdir)^g' \
	-e 's^\@PKGDATADIR\@^$(pkgdatadir)^g' \
	-e 's^\@PKGNAME\@^$(PACKAGE)^g' \
	-e 's^\@PKGVERSION\@^$(VERSION)^g' \
	$< > $@

CLEANFILES		= $(config_DATA)

all: $(config_DATA)
