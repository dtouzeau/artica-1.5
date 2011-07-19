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

if BUILD_STARTUP
STARTUPDIR			= startup
else
STARTUPDIR			=
endif

SUBDIRS				= sbin share etc doc $(STARTUPDIR) php

EXTRA_DIST			= README.MacOSX BUGS TODO config.rrdtool

CLEANFILES			= 

all-am: $(all_targets)
	@echo -n ""
if !BUILD_STARTUP
	@echo -e "\nNo startupItem being built for your OS/Platform..."
endif
	@if [ "`whoami`" == "root" ]; then whoisit=""; else whoisit="sudo "; fi; echo -e "\n$(PACKAGE)-$(VERSION) successfully built, issue '$${whoisit}make install' to complete the installation process.\n"

install-data-am:
	@echo -e "\nInstallation Complete....\n"
