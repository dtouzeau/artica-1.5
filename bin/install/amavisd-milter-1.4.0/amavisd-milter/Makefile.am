# $Id: Makefile.am,v 1.4 2006/10/04 20:46:55 reho Exp $

# Distribution files
EXTRA_DIST= \
	amavisd-milter.8 \
	Makefile.am \
	Makefile.in

# Header files
noinst_HEADERS=	\
	amavisd-milter.h

# Manual pages
man_MANS= \
	amavisd-milter.8

# Binaries
sbin_PROGRAMS=	\
	amavisd-milter

# amavisd-milter compiling parameters
amavisd_milter_SOURCES= \
	amavisd.c \
	log.c \
	main.c \
	mlfi.c
amavisd_milter_LDADD= \
	../compat/libcompat.a
amavisd_milter_CPPFLAGS= \
	-I../compat

maintainer-clean-local:
	-rm -f Makefile.in
