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

EXTRA_DATA		= ChalkboardBold.ttf \
				tab_center_white.png \
				tab_left_white.png \
				tab_right_white.png \
				tab_center_gray.png \
				tab_left_gray.png \
				tab_right_gray.png \
				tab_center_blue.png \
				tab_left_blue.png \
				tab_right_blue.png \
				bullet_center_blue.png \
				bullet_left_blue.png \
				bullet_right_blue.png  \
				includes/index.html \
				includes/page_header.php \
				includes/page_tail.php \
				includes/template.php \
				templates/index.html \
				templates/business1/index.html \
				templates/business1/business1.css \
				templates/business1/images/banner.gif \
				templates/business1/images/bkgnd.gif \
				templates/business1/images/index_02.gif \
				templates/business1/images/index_14.gif \
				templates/business1/images/index_48.gif \
				templates/business1/images/index_51.gif \
				templates/business1/images/lines.gif \
				templates/business1/images/logo_phpsite_big.gif \
				templates/business1/images/spacer.gif \
				templates/business1/index_body.tpl \
				templates/business1/overall_footer.tpl \
				templates/business1/overall_header.tpl \
				templates/standard/index.html \
				templates/standard/index_body.tpl \
				templates/standard/overall_footer.tpl \
				templates/standard/overall_header.tpl \
				templates/standard/standard.css

pkgdata_DATA	= amavis-stats.php \
				amavis-stats.alias.conf \
				amavis-stats.php.conf \
				$(EXTRA_DATA)

install-exec-am:
	@echo "Processing web files...."

installdirs:
	$(INSTALL) -o $(WEB_USER) -g $(WEB_GROUP) -d -m 0775 $(DESTDIR)$(mylibdir)/$(PACKAGE)
	$(INSTALL) -o $(WEB_USER) -g $(WEB_GROUP) -d -m 0775 $(DESTDIR)$(mycachedir)/$(PACKAGE)
	$(INSTALL) -o $(WEB_USER) -g $(WEB_GROUP) -d -m 0775 $(DESTDIR)$(pkgdatadir)
	$(INSTALL) -o $(WEB_USER) -g $(WEB_GROUP) -d -m 0775 $(DESTDIR)$(pkgdatadir)/includes
	$(INSTALL) -o $(WEB_USER) -g $(WEB_GROUP) -d -m 0775 $(DESTDIR)$(pkgdatadir)/templates/standard
	$(INSTALL) -o $(WEB_USER) -g $(WEB_GROUP) -d -m 0775 $(DESTDIR)$(pkgdatadir)/templates/business1/images


install-data-am: install-pkgdataDATA
	@@RM@ -f $(DESTDIR)$(pkgdatadir)/index.php
	@@RM@ -f $(DESTDIR)$(pkgdatadir)/img
	@cd $(DESTDIR)$(pkgdatadir) && @LN@ -sf amavis-stats.php index.php
	@cd $(DESTDIR)$(pkgdatadir) && @LN@ -sf $(mycachedir)/$(PACKAGE) img 

uninstall: uninstall-am
	cd $(DESTDIR)$(pkgdatadir) && @RM@ -rf index.php

install-pkgdataDATA: installdirs $(pkgdata_DATA)
	@$(NORMAL_INSTALL)
	@list='$(pkgdata_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  echo "  Installing $(DESTDIR)$(pkgdatadir)/$$p"; \
	  $(pkgdataDATA_INSTALL) -o $(WEB_USER) -g $(WEB_GROUP) $$d$$p $(DESTDIR)$(pkgdatadir)/$$p; \
	done

uninstall-pkgdataDATA:
	@$(NORMAL_UNINSTALL)
	@list='$(pkgdata_DATA)'; for f in $$list; do \
	  echo "  Deleteing $(DESTDIR)$(pkgdatadir)/$$f"; \
	  rm -f $(DESTDIR)$(pkgdatadir)/$$f; \
	done

EXTRA_SCRIPTS	= amavis-stats.php.in amavis-stats.alias.conf.in amavis-stats.php.conf.in

EXTRA_DIST		= $(EXTRA_SCRIPTS) $(EXTRA_DATA)

SUFFIXES = .in

.in:
	@echo "  Making $@"
	@@SED@ \
	-e 's^\@PKGCONFIGDIR\@^$(sysconfdir)^g' \
	-e 's^\@PKGCACHEDIR\@^$(mycachedir)^g' \
	-e 's^\@PKGLIBDIR\@^$(mylibdir)^g' \
	-e 's^\@PKGDATADIR\@^$(pkgdatadir)^g' \
	-e 's^\@PKGNAME\@^$(PACKAGE)^g' \
	-e 's^\@PKGVERSION\@^$(VERSION)^g' \
	$< > $@

CLEANFILES		= amavis-stats.php amavis-stats.alias.conf amavis-stats.php.conf

