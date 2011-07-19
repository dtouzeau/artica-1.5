#!/bin/sh
#
# $Id: autoconf.sh.in,v 1.1 2008/07/01 17:10:35 reho Exp $
#

aclocal -I aclocal &&
autoheader &&
autoconf &&
automake --add-missing --copy
