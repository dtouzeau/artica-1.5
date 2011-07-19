# $Id: Makefile.am,v 1.3 2006/01/25 21:53:27 reho Exp $

# Distribution files
EXTRA_DIST= \
	Makefile.am \
	Makefile.in

# Header files
noinst_HEADERS=	\
	compat.h \
	fts_compat.h

# Libraries
noinst_LIBRARIES= \
	libcompat.a

# libcompat compiling parameters
libcompat_a_SOURCES= \
	read_sock.c \
	write_sock.c
libcompat_a_LIBADD= \
	@LIBOBJS@
libcompat_a_DEPENDENCIES= \
	@LIBOBJS@

maintainer-clean-local:
	-rm -f Makefile.in
