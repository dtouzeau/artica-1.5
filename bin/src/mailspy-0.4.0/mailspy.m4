dnl
dnl  mailspy: filter all mail via mailspy socket
dnl
dnl  Put this at the end of your linux.mc file (or sendmail.mc), depending
dnl  on your distribution.
dnl
define(`_FFR_MILTER')dnl
INPUT_MAIL_FILTER(`mailspy', `S=local:/var/run/mailspy/milter, F=T')dnl

