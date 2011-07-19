#!/usr/bin/perl -w

use Linux::net::dev;
use Data::VarPrint;

VarPrint(Linux::net::dev::info());
