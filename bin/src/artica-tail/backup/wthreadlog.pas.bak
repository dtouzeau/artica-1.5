{   $Id$

    Copyright (C) 2000-2002 OpenXP team (www.openxp.de) and Markus Kaemmerer

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
}

{ XP - Linux - Supportroutinen }
{$I xpdefine.inc }

unit xplinux;

{$IFNDEF unix}
  {$FATAL Die Unit XPLINUX kann nur unter Linux compiliert werden }
{$ENDIF }

interface

{$IFDEF FPC }
  { C default packing is dword }
  {$linklib c}
  {$PACKRECORDS 4}
{$ENDIF }

uses
  {$IFDEF fpc }
  {$IFDEF unix }
  unix,baseunix,
  {$ENDIF }
  {$ELSE }
  Libc,
  {$ENDIF }
  SysUtils,
  xpglobal;

{$IFDEF Kylix}
const
  STAT_IWUSR            = S_IWUSR;
  STAT_IWGRP            = S_IWGRP;
  STAT_IWOTH            = S_IWOTH;
  STAT_IRUSR            = S_IRUSR;
  STAT_IRGRP            = S_IRGRP;
  STAT_IROTH            = S_IROTH;
  STAT_IXUSR            = S_IXUSR;
  STAT_IXGRP            = S_IXGRP;
  STAT_IXOTH            = S_IXOTH;
  STAT_IRWXU            = S_IRWXU;
  STAT_IRWXG            = S_IRWXG;
  STAT_IRWXO            = S_IRWXO;
{$ENDIF}

const                           { Common Environment-Vars }
  envHome               = 'HOME';
  envShell              = 'SHELL';
  envXPHome             = 'XPHOME'; { XP-Basis-Verzeichnis }

  A_USER                = STAT_IRUSR or STAT_IWUSR;     { User lesen/schreiben }
  A_USERX               = A_USER or STAT_IXUSR;         { dito + ausfuehren }

{$ifdef UseSysLog}
  LOG_EMERG             = 0;
  LOG_ALERT             = 1;
  LOG_CRIT              = 2;
  LOG_ERR               = 3;
  LOG_WARNING           = 4;
  LOG_NOTICE            = 5;
  LOG_INFO              = 6;
  LOG_DEBUG             = 7;
{$endif}

type
  TTestAccess           = (             { Zugriffsrechte (wird nach Bedarf erweitert) }
                            taUserR,
                            taUserW,
                            taUserRW,
                            taUserX,
                            taUserRX,
                            taUserRWX
                          );

function SysExec(const Path, CmdLine: String): Integer;


{ Verzeichnis-/Datei-Routinen ------------------------------------------ }

function MakeDir(p: string; a: longint): boolean;
function TestAccess(p: string; ta: TTestAccess): boolean;
function ResolvePathName(const p: string): string;
procedure SetAccess(p: string; ta: TTestAccess);
{$IFDEF Kylix }
function Diskfree(drive: byte): LongInt;
function DiskSize(drive: byte): LongInt;
function FileGetAttr(filename: string): Integer;
function FileSetAttr(filename: string; Attr: Integer): Integer;
{$ENDIF}

{ Zugriffe ueber /proc/* ----------------------------------------------- }

function GetShortVersion: string;

{$ifdef UseSysLog}

{ XPLog gibt eine Logmeldung im Syslog aus }
procedure XPLog(Level: integer; format_s: string; args: array of const);
procedure XPLogMsg(Level: integer; logmsg: string);
procedure XPDebugLog(logmsg: string);
procedure XPInfoLog(logmsg: string);
procedure XPNoticeLog(logmsg: string);
procedure XPWarningLog(logmsg: string);
procedure XPErrorLog(logmsg: string);

{$endif}

function SysOutputRedirected: boolean;

implementation

uses
  typeform,
  fileio;


{$ifdef UseSysLog}

const
  LOG_PRIMASK           = $07;
  LOG_KERN              = 0 shl 3;
  LOG_USER              = 1 shl 3;
  LOG_MAIL              = 2 shl 3;
  LOG_DAEMON            = 3 shl 3;
  LOG_AUTH              = 4 shl 3;
  LOG_SYSLOG            = 5 shl 3;
  LOG_LPR               = 6 shl 3;
  LOG_NEWS              = 7 shl 3;
  LOG_UUCP              = 8 shl 3;
  LOG_CRON              = 9 shl 3;
  LOG_AUTHPRIV          = 10 shl 3;
  LOG_FTP               = 11 shl 3;
  LOG_LOCAL0            = 16 shl 3;
  LOG_LOCAL1            = 17 shl 3;
  LOG_LOCAL2            = 18 shl 3;
  LOG_LOCAL3            = 19 shl 3;
  LOG_LOCAL4            = 20 shl 3;
  LOG_LOCAL5            = 21 shl 3;
  LOG_LOCAL6            = 22 shl 3;
  LOG_LOCAL7            = 23 shl 3;
  LOG_NFACILITIES       = 24;
  LOG_FACMASK           = $03f8;
  INTERNAL_NOPRI        = $10;
  INTERNAL_MARK         = LOG_NFACILITIES shl 3;

type
  TSysLogCode = record
                  name  : string[10];
                  value : longint;
                end;
const
  PrioNames: array[1..12] of TSysLogCode = (
        (name : 'alert';        value: LOG_ALERT ),
        (name : 'crit';         value: LOG_CRIT ),
        (name : 'debug';        value: LOG_DEBUG ),
        (name : 'emerg';        value: LOG_EMERG ),
        (name : 'err';          value: LOG_ERR ),
        (name : 'error';        value: LOG_ERR ),
        (name : 'info';         value: LOG_INFO ),
        (name : 'none';         value: INTERNAL_NOPRI ),
        (name : 'notice';       value: LOG_NOTICE ),
        (name : 'panic';        value: LOG_EMERG ),
        (name : 'warn';         value: LOG_WARNING ),
        (name : 'warning';      value: LOG_WARNING ) );

   FacNames: array[1..22] of TSysLogCode = (
        (name : 'auth';         value: LOG_AUTH ),
        (name : 'authpriv';     value: LOG_AUTHPRIV ),
        (name : 'cron';         value: LOG_CRON ),
        (name : 'daemon';       value: LOG_DAEMON ),
        (name : 'ftp';          value: LOG_FTP ),
        (name : 'kern';         value: LOG_KERN ),
        (name : 'lpr';          value: LOG_LPR ),
        (name : 'mail';         value: LOG_MAIL ),
        (name : 'mark';         value: INTERNAL_MARK ),
        (name : 'news';         value: LOG_NEWS ),
        (name : 'security';     value: LOG_AUTH ),
        (name : 'syslog';       value: LOG_SYSLOG ),
        (name : 'user';         value: LOG_USER ),
        (name : 'uucp';         value: LOG_UUCP ),
        (name : 'local0';       value: LOG_LOCAL0 ),
        (name : 'local1';       value: LOG_LOCAL1 ),
        (name : 'local2';       value: LOG_LOCAL2 ),
        (name : 'local3';       value: LOG_LOCAL3 ),
        (name : 'local4';       value: LOG_LOCAL4 ),
        (name : 'local5';       value: LOG_LOCAL5 ),
        (name : 'local6';       value: LOG_LOCAL6 ),
        (name : 'local7';       value: LOG_LOCAL7 ));

const
  LOG_PID       = $01;
  LOG_CONS      = $02;
  LOG_ODELAY    = $04;
  LOG_NDELAY    = $08;
  LOG_NOWAIT    = $10;
  LOG_PERROR    = $20;

  log_installed: boolean = false;

{$endif}

const
  fnProcVersion         = '/proc/version';      { Versionsinfos }

var
  SavedExitProc: Pointer;
{$ifdef UseSysLog}
  LogPrefix: array[0..255] of char;
  LogString: array[0..1024] of char;
{$endif}


{ Verzeichnis-Routinen ------------------------------------------------- }

function MakeDir(p: string; a: longint): boolean;
begin
  mkdir(p);
  if (ioresult<>0) then
    MakeDir:= false
  else
{$IFDEF Kylix}
    MakeDir:= chmod(PChar(p), a) = 0;
{$ELSE}
    MakeDir:= fpchmod(PChar(p), a) = 0;
{$ENDIF}
end;

function TestAccess(p: String; ta: TTestAccess): boolean;
var
{$IFDEF Kylix}
  info: TStatBuf;
{$ELSE}
  info: stat;
{$ENDIF}
begin
  TestAccess := false;
{$IFDEF FPC }
  if (fpstat(p, info) <> 0 ) then
{$ELSE }
  if fstat(PChar(p), info) <> 0 then
{$ENDIF }
    TestAccess:= false
  else with info do begin
    case ta of


{$IFDEF FPC}
      taUserR:   TestAccess:= (mode and STAT_IRUSR) <> 0;
      taUserW:   TestAccess:= (mode and STAT_IWUSR) <> 0;
      taUserRW:  TestAccess:= (mode and (STAT_IRUSR or STAT_IWUSR)) <> 0;
      taUserRWX: TestAccess:= (mode and STAT_IRWXU) <> 0;
      taUserX:   TestAccess:= (mode and STAT_IXUSR) <> 0;
      taUserRX:  TestAccess:= (mode and (STAT_IRUSR or STAT_IXUSR)) <> 0;
 {$ELSE}
      taUserR:   TestAccess:= (st_mode and S_IRUSR) <> 0;
      taUserW:   TestAccess:= (st_mode and S_IWUSR) <> 0;
      taUserRW:  TestAccess:= (st_mode and (S_IRUSR or S_IWUSR)) <> 0;
      taUserRWX: TestAccess:= (st_mode and S_IRWXU) <> 0;
      taUserX:   TestAccess:= (st_mode and S_IXUSR) <> 0;
      taUserRX:  TestAccess:= (st_mode and (S_IRUSR or S_IXUSR)) <> 0;
{$ENDIF}

      taUserR:   TestAccess:= (st_mode and S_IRUSR) <> 0;
      taUserW:   TestAccess:= (st_mode and S_IWUSR) <> 0;
      taUserRW:  TestAccess:= (st_mode and (S_IRUSR or S_IWUSR)) <> 0;
      {      taUserRWX: TestAccess:= (st_mode and S_IRWXU) <> 0; }
      taUserX:   TestAccess:= (st_mode and S_IXUSR) <> 0;
      taUserRX:  TestAccess:= (st_mode and (S_IRUSR or S_IXUSR)) <> 0;
    end;
  end;
end;

procedure SetAccess(p: string; ta: TTestAccess);
begin
{$IFDEF Kylix}
  case ta of
    taUserR:   chmod(PChar(p), STAT_IRUSR);
    taUserW:   chmod(PChar(p), STAT_IWUSR);
    taUserRW:  chmod(PChar(p), STAT_IRUSR or STAT_IWUSR);
    taUserRWX: chmod(PChar(p), STAT_IRWXU);
    taUserX:   chmod(PChar(p), STAT_IXUSR);
    taUserRX:  chmod(PChar(p), STAT_IRUSR or STAT_IXUSR);
  end;
{$ELSE}
  case ta of
    taUserR:   fpchmod(PChar(p), STAT_IRUSR);
    taUserW:   fpchmod(PChar(p), STAT_IWUSR);
    taUserRW:  fpchmod(PChar(p), STAT_IRUSR or STAT_IWUSR);
    taUserRWX: fpchmod(PChar(p), STAT_IRWXU);
    taUserX:   fpchmod(PChar(p), STAT_IXUSR);
    taUserRX:  fpchmod(PChar(p), STAT_IRUSR or STAT_IXUSR);
  end;
{$ENDIF}
end;

function ResolvePathName(const p: string): string;
begin
  Result := iifs(FirstChar(p) ='~', fpgetenv(envHome)+mid(p, 2), p);
{$ifdef UseSysLog}
  if (Result <>'') then
    XPDebugLog('Resolved: "'+p+'" -> "' + Result + '"');
{$ENDIF}
end;

{ Zugriffe ueber /proc/* ----------------------------------------------- }

function GetShortVersion: string;
var
  f: text;
  s: string;
begin
  assign(f,fnProcVersion);
  reset(f);
  if (ioresult=0) then begin
    readln(f,s);
    close(f);
    GetShortVersion:= copy(s,1,cpos('(',s)-2);
  end else
    GetShortVersion:= '?.??';
end;

{ SysLog-Interface ----------------------------------------------------- }
{$ifdef UseSysLog}

{$IFDEF FPC }
procedure closelog;cdecl;external;
procedure openlog(__ident:pchar; __option:longint; __facilit:longint);cdecl;external;
function setlogmask(__mask:longint):longint;cdecl;external;
procedure syslog(__pri:longint; __fmt:pchar; args:array of const);cdecl;external;
{$ELSE }
procedure closelog;begin end;
procedure openlog(__ident:pchar; __option:longint; __facilit:longint);begin end;
function setlogmask(__mask:longint):longint;begin end;
procedure syslog(__pri:longint; __fmt:pchar; args:array of const);begin end;
{$ENDIF }

{ Log-Proceduren ------------------------------------------------------- }

procedure XPLog(Level: integer; format_s: string; args: array of const);
var
  s: string;
begin
  if not (log_installed) then           { Kann beim Init der Unit's vorkommen }
    exit;
  { Da FPC einen Internal Error bei syslog(level,p,args) erzeugt,
    muessen wir die Wandelung selbst vornehmen }
  s:= Format(format_s, args);
  StrPCopy(LogString, s);
  syslog(Level, LogString, [0]);
end;

procedure XPLogMsg(Level: integer; logmsg: string);
begin
  if not (log_installed) then
    exit;
  StrPCopy(LogString, logmsg);
  syslog(Level, LogString, [0]);
end;

procedure XPDebugLog(logmsg: string);
begin
  XPLogMsg(LOG_DEBUG, logmsg);
end;

procedure XPInfoLog(logmsg: string);
begin
  XPLogMsg(LOG_INFO, logmsg);
end;

procedure XPNoticeLog(logmsg: string);
begin
  XPLogMsg(LOG_NOTICE, logmsg);
end;

procedure XPWarningLog(logmsg: string);
begin
  XPLogMsg(LOG_WARNING, logmsg);
end;

procedure XPErrorLog(logmsg: string);
begin
  XPLogMsg(LOG_ERR, logmsg);
end;
{$endif}
{ Exit-Proc ------------------------------------------------------------ }

procedure XPLinuxExit;
begin
  ExitProc:= SavedExitProc;
{$ifdef UseSysLog}
  { Log-File schliessen }
  SysLog(LOG_INFO, 'Ends', [0]);
  CloseLog;
{$endif}
end;

{$ifdef UseSysLog}
procedure InitLogStream;
var
  s: string;
begin
{$IFDEF DEBUG }
  if (log_installed) then begin
    XPErrorLog('Panic: Unit initilized twice!');
    WriteLn('Zweite Initialisierung der Unit XPLinux!');
    halt;
  end;
{$ENDIF }
  s:= ExtractFileName(ParamStr(0));
  s:= s+'['+IntToStr(GetPid)+']';
  StrPCopy(LogPrefix, s);
{$IFDEF DEBUG }
  OpenLog(LogPrefix, LOG_NOWAIT, LOG_DEBUG);
{$ELSE }
{$IFDEF Beta }
  OpenLog(LogPrefix, LOG_NOWAIT, LOG_INFO);
{$ELSE }
  OpenLog(LogPrefix, LOG_NOWAIT, LOG_NOTICE);
{$ENDIF }
{$ENDIF }
  SysLog(LOG_INFO, 'Starting', [1]);
  log_installed:= true;
  { In die Exit-Kette einhaengen }
  SavedExitProc:= ExitProc;
  ExitProc:= @XPLinuxExit;
end;
{$endif}
function SysOutputRedirected: boolean;
begin
  // ToDo
  Result := false;
end;

function SysExec(const Path, CmdLine: String): Integer;
begin
{$IFDEF Kylix}
  result:= libc.System(PChar(AddDirSepa(path)+CmdLine));
{$ELSE}
  result:= unix.Shell(AddDirSepa(path)+CmdLine);
{$ENDIF}
end;

{$IFDEF Kylix }
function Diskfree(drive: byte): LongInt;
begin
  {Todo1: get free diskspace in Kylix !!!!!!!!!}
  Result := 1000000000;
end;

function DiskSize(drive: byte): LongInt;
begin
  {Todo1: get diskspace in Kylix !!!!!!!!!}
  Result := 1000000000;
end;

function FileGetAttr(filename: string): Integer;
begin
  {Todo1: get FileGetAttr in Kylix !!!!!!!!!}
  Result := 0
end;

function FileSetAttr(filename: string; Attr: Integer): Integer;
begin
  {Todo1: get FileSetAttr in Kylix !!!!!!!!!}
  Result := 0;
end;

{$ENDIF}
{$ifdef UseSysLog}

begin
  InitLogStream;
{$endif}

{$Warnings OFF}
end.

