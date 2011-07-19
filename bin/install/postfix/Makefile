CFLAGS = -O2 -Wall
DEST = mysql-logger
OBJS = mysql-logger.o
DEST_DIR = /bin
LIBS = -lcrypt -lmysqlclient -ldotconf

all: mysqmail-postfix-logger

mysqmail-postfix-logger: mysqmail-postfix-logger.c
	gcc $(LIBS) $(CFLAGS) -Wall -o mysqmail-postfix-logger mysqmail-postfix-logger.c

clean:
	rm -rf mysqmail-postfix-logger *~

install: all
	strip mysqmail-postfix-logger
	cp mysqmail-postfix-logger /var/qmail/bin
	chmod 755 /var/qmail/bin/mysqmail-logger
#	cp ../doc/mysqmail.conf /etc/mysqmail.conf.sample
