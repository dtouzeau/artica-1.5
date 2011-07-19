#!/bin/bash

LC_ALL=C
export LC_ALL

#export LANGUAGE="en"

if [[ $1 == "-d" ]]; then
  echo "name: simple_shell.sh";
  echo "version: 1.0";
  echo "description_french: Un script shell tout bête";
  echo "description_english: A very stupid shell script";
  echo "password_required: No";
  echo "default_password: ";
  echo "rank: 1";
  exit 0;
fi

if [[ $1 == "-u" ]]; then
  echo "ignoring user name";
  shift 2;
fi

if [[ $1 == "-p" ]]; then
  echo "ignoring password";
  shift 2;
fi

if [[ $1 == "-h" ]]; then
  echo "ignoring port number";
  shift 2;
fi

if [[ $1 == "" ]]; then 
  INTF=`/sbin/ifconfig | grep Link | cut -d ' ' -f 1`; else
  INTF=$1;
fi

for i in $INTF; do
  if [[ `/sbin/ifconfig $i` == "" ]]; then exit 1; else
    IP=`/sbin/ifconfig $i | grep inet | cut -d ':' -f 2 | cut -d ' ' -f 1`;
    IN=`/sbin/ifconfig $i | grep RX | grep bytes | cut -d ':' -f 2 | cut -d ' ' -f 1`;
    OUT=`/sbin/ifconfig $i | grep RX | grep bytes | cut -d ':' -f 3 | cut -d ' ' -f 1`;
    if [[ $IP != "" ]]; then echo $i $IP $IN $OUT; 
    fi; 
  fi;
done
