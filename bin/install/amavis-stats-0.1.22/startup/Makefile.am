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

EXTRA_DIST		= $(EXTRA_DATA)

EXTRA_DATA		= com.amavis-stats.plist.in \
				com.amavis-stats.plist.setrgid.in \
				AMAVIS-STATS.in \
				StartupParameters.plist.in \
				amavisstats.in

SUFFIXES		= .in

mylibdir		= ${localstatedir}/lib

if BUILD_DARWIN_PANTHER

startupdir		= $(DESTDIR)/System/Library/StartupItems/AMAVIS-STATS

startup_DATA	= AMAVIS-STATS StartupParameters.plist

endif

if BUILD_DARWIN_TIGER

startupdir		= $(DESTDIR)/System/Library/LaunchDaemons

if SETRGID

startup_DATA	= com.amavis-stats.plist.setrgid

else

startup_DATA	= com.amavis-stats.plist

endif

endif

if BUILD_GENTOO

startupdir		= $(DESTDIR)/etc/init.d

startup_DATA	= amavisstats

endif

install-exec-am:
	@echo "Processing startup files...."

installdirs:
	@list='$(startupdir)'; for p in $$list; do \
	if ! test -d "$$p"; then echo "  Creating $$p"; \
	$(INSTALL) -m 0775 -d $$p; fi; \
	done

install-startupDATA: installdirs $(startup_DATA)
	@$(NORMAL_INSTALL)
	@list='$(startup_DATA)'; for p in $$list; do \
	if test "$$p" == "com.amavis-stats.plist.setrgid"; then p="com.amavis-stats.plist"; \
	mv "$(srcdir)/com.amavis-stats.plist.setrgid" "$(srcdir)/com.amavis-stats.plist"; fi; \
	if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	echo "  Installing $(startupdir)/$$p"; \
	$(INSTALL) -m 0755 $$d$$p $(startupdir)/$$p; \
	done

uninstall-startupDATA:
	@$(NORMAL_UNINSTALL)
	@list='$(startup_DATA)'; for f in $$list; do \
	if test "$$f" == "com.amavis-stats.plist.setrgid"; then f="com.amavis-stats.plist"; \
	echo "  Deleting $(startupdir)/$$f"; \
	rm -f $(startupdir)/$$f; \
	done

.in:
	@echo "  Making $@"
	@@SED@ \
	-e 's^\@WEB_USER\@^$(WEB_USER)^g' \
	-e 's^\@WEB_GROUP\@^$(WEB_GROUP)^g' \
	-e 's^\@SBINDIR\@^$(sbindir)^g' \
	-e 's^\@PKGLIBDIR\@^$(mylibdir)^g' \
	-e 's^\@PKGNAME\@^$(PACKAGE)^g' \
	$< > $@

CLEANFILES			= $(startup_DATA)
