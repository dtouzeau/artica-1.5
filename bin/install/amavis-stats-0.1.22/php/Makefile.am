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
#
#  Copyright (C) 2006 Dale Walsh <buildsmart@daleenterprise.com>
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


EXTRA_DIST = rrdtool.c rrdtool_logo.h php_rrdtool.h


if BUILD_PHP

PHP_SHLIB_SUFFIX	= .@SHLIB_DL_SUFFIX_NAME@
AM_CPPFLAGS			= @CPPFLAGS@ -I$(top_srcdir) @PHP_INCLUDES@
AM_LDFLAGS			= @LDFLAGS@ @LIBS@

rrdtooldir =  @PHP_EXTENSION_DIR@
rrdtool_DATA = rrdtool$(PHP_SHLIB_SUFFIX)

all-local: rrdtool$(PHP_SHLIB_SUFFIX)

rrdtool$(PHP_SHLIB_SUFFIX): rrdtool.lo
	@$(LIBTOOL) --mode=link $(CC) $(LINK_CMD) -DCOMPILE_DL_RRDTOOL -DPHP_ATOM_INC  $< -o $@  $(AM_LDFLAGS) $(EXTRA_LDFLAGS)
	@$(ECHO) "  PHP module successfully built."

rrdtool.lo: $(top_srcdir)/php/rrdtool.c
	@$(LIBTOOL) --mode=compile $(CC) -I. -I.. -DPHP_ATOM_INC $(CFLAGS) $(COMMON_CFLAGS) $(PHP_INCLUDES) $(INCLUDES) -c $< -DCOMPILE_DL_RRDTOOL -DHAVE_CONFIG_H $(AM_CPPFLAGS) -o $@

install-rrdtoolDATA: $(rrdtool_DATA)
	@$(NORMAL_INSTALL)
	$(mkinstalldirs) $(DESTDIR)$(rrdtooldir)
	@list='$(rrdtool_DATA)'; for p in $$list; do \
	  if test -f "$$p"; then d=; else d="$(srcdir)/"; fi; \
	  f="`echo $$p | sed -e 's|^.*/||'`"; \
	  echo " $(rrdtoolDATA_INSTALL) $$d$$p $(DESTDIR)$(rrdtooldir)/$$f"; \
	  $(rrdtoolDATA_INSTALL) $$d$$p $(DESTDIR)$(rrdtooldir)/$$f; \
	done

uninstall-rrdtoolDATA:
	@$(NORMAL_UNINSTALL)
	@list='$(rrdtool_DATA)'; for p in $$list; do \
	  f="`echo $$p | sed -e 's|^.*/||'`"; \
	  echo " rm -f $(DESTDIR)$(rrdtooldir)/$$f"; \
	  rm -f $(DESTDIR)$(rrdtooldir)/$$f; \
	done

clean:
	rm -rf *.*o

endif
