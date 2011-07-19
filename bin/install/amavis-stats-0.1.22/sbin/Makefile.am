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

EXTRA_SCRIPTS	= amavis-stats.in

EXTRA_DIST		= $(EXTRA_SCRIPTS)

sbin_SCRIPTS	= amavis-stats

CLEANFILES		= $(sbin_SCRIPTS)

SUFFIXES = .in

install-data-am:
	@echo "Processing daemon...."

.in:
	@echo "  Making $@"
	@@SED@ \
	-e 's^\@SCAN_LOG_FILE\@^$(SCAN_LOG_FILE)^g' \
	-e 's^\@WEB_USER\@^$(WEB_USER)^g' \
	-e 's^\@WEB_GROUP\@^$(WEB_GROUP)^g' \
	-e 's^\@PKGCONFIGDIR\@^$(sysconfdir)^g' \
	-e 's^\@PKGCACHEDIR\@^$(mycachedir)^g' \
	-e 's^\@PKGLIBDIR\@^$(mylibdir)^g' \
	-e 's^\@PKGDATADIR\@^$(pkgdatadir)^g' \
	-e 's^\@PKGNAME\@^$(PACKAGE)^g' \
	-e 's^\@PKGVERSION\@^$(VERSION)^g' \
	-e 's^\@PERL\@^$(PERL)^g' \
	$< > $@

all: $(sbin_SCRIPTS)

