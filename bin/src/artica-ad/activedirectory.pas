unit activedirectory;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
Classes, articaldap,SysUtils,strutils,Process,unix,logs,RegExpr in 'RegExpr.pas',IniFiles,BaseUnix,
zsystem;

  type
  tad=class


private
    ldap:tarticaldap;
    logs:Tlogs;

public
      constructor Create();
      procedure Free;
      FUNCTION TEST_CONNECTION(organization:string):boolean;
END;

implementation

constructor tad.Create();
begin
  ldap:=tarticaldap.Create();
  logs:=Tlogs.Create;
end;
//#############################################################################
PROCEDURE tad.Free();
begin
 ldap.Free;

end;
//#############################################################################

FUNCTION tad.TEST_CONNECTION(organization:string):boolean;
var
   f:ou_datas;
   l:TstringList;
   ftmp:string;
   Conf:TiniFile;
   AD_SETTINGS:ActiveDirectoryServer;
begin
    result:=false;
    if not ldap.Logged then begin
        writeln('ERR No connection to LDAP main server');
        exit;
    end;

    if length(organization)=0 then begin
         writeln('ERR No Organization specified');
         exit;
    end;
    f:=ldap.Load_ORGANISATION(organization);
    if length(f.AdLinkerConf)=0 then begin
       writeln('ERR No datas specified');
       exit;
    end;

    l:=TstringList.Create;
    l.Add(f.AdLinkerConf);
    ftmp:=logs.FILE_TEMP();
    l.SaveToFile(ftmp);
    if not FileExists(ftmp) then begin
        writeln('ERR Unable to create temporary file');
        l.free;
        exit;
    end;
    Conf:=TiniFile.Create(ftmp);
    logs.DeleteFile(ftmp);
    AD_SETTINGS.suffix:=Conf.ReadString('LDAP','suffix','');
    AD_SETTINGS.dn_admin:=Conf.ReadString('LDAP','ldap_admin','');
    AD_SETTINGS.password:=Conf.ReadString('LDAP','ldap_password','');
    AD_SETTINGS.server:=Conf.ReadString('LDAP','ldap_host','');
    AD_SETTINGS.server_port:=Conf.ReadString('LDAP','ldap_port','389');
    if not ldap.TestingADConnection(AD_SETTINGS) then exit;
    writeln('OK Connected to ' + AD_SETTINGS.server);

    

end;


end.
