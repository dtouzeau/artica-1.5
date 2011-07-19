unit mandriva;
     {$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,Process,strutils,IniFiles,RegExpr,oldlinux,BaseUnix,dos,confiles;

  type
  Tmandriva=class


private
    function ReadFileIntoString(path:string):string;
      GLOBAL_INI:TIniFile;
      function Install_2007:boolean;
      function Install_9_1:boolean;
      function Install_9_2:boolean;
public
      constructor Create;
      function Install:boolean;
      REPOS:string;

END;

implementation

constructor Tmandriva.Create;
begin
     forcedirectories('/etc/artica-postfix');
     GLOBAL_INI:=TIniFile.Create('/etc/artica-postfix/artica-postfix.conf');
end;

function Tmandriva.Install:boolean;
var
   filedatas:string;
   RegExpr:TRegExpr;
begin
    if FileExists('/etc/mandriva-release') then begin
       filedatas:=ReadFileIntoString('/etc/mandriva-release');
       RegExpr:=TRegExpr.Create;
       RegExpr.expression:='Mandriva Linux release\s+([0-9]+)';
       if not RegExpr.Exec(filedatas) then begin
              writeln('Failed to read Mandriva release version, perhpas is not supported by installer.');
       end;
    
       if RegExpr.Match[1]='2007' then Result:=Install_2007();
       RegExpr.Free;
    end;
    
    if FileExists('/etc/redhat-release') then begin
       filedatas:=ReadFileIntoString('/etc/redhat-release');
       RegExpr:=TRegExpr.Create;
       RegExpr.expression:='Mandrake Linux release ([0-9\.]+)';
       if not RegExpr.Exec(filedatas) then begin
              writeln('Failed to read Mandrake release version, perhpas is not supported by installer.');
       end;

       if RegExpr.Match[1]='9.1' then Result:=Install_9_1();
       RegExpr.Free;
    end;
    
    if FileExists('/etc/redhat-release') then begin
       filedatas:=ReadFileIntoString('/etc/redhat-release');
       RegExpr:=TRegExpr.Create;
       RegExpr.expression:='Mandrake Linux release ([0-9\.]+)';
       if not RegExpr.Exec(filedatas) then begin
              writeln('Failed to read Mandrake release version, perhpas is not supported by installer.');
       end;

       if RegExpr.Match[1]='9.2' then Result:=Install_9_2();
       RegExpr.Free;
    end;
    

    

end;

//######################################################################################################
function Tmandriva.Install_2007:boolean;
  var
     update_yes:string;

begin
     writeln('You are using Mandriva 2007');
     writeln('The installer should update your urpmi configuration in order');
     writeln('to get repositories');
     writeln('Do you want to execute this operation (yes/no) ?:');
     readln(update_yes);
     
     if update_yes='yes' then begin
          writeln('Adding sources repositories... ');
          Shell('/usr/sbin/urpmi.addmedia main ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/official/2007.0/i586/media/main/release with media_info/hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia --update main_updates http://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/official/2007.0/i586/media/main/updates with media_info/hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia main_backports http://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/official/2007.0/i586/media/main/backports with media_info/hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia contrib ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/official/2007.0/i586/media/contrib/release with media_info/hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia --update contrib_updates ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/official/2007.0/i586/media/contrib/updates with media_info/hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia contrib_backports ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/official/2007.0/i586/media/contrib/backports with media_info/hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia plf-free ftp://ftp.easynet.fr/plf/mandriva/2007.0/free/release/binary/i586/ with hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia plf-free_backports ftp://ftp.easynet.fr/plf/mandriva/2007.0/free/backports/binary/i586/ with hdlist.cz');
          writeln('update sources repositories... waiting please...');
          Shell('/usr/sbin/urpmi.update -a');
     end;
       writeln('Do you want to start installing required packages ?(yes/no)');
       readln(update_yes);
        if update_yes='yes' then Shell('/usr/sbin/urpmi ' + REPOS + ' --auto');
     
end;
//######################################################################################################
function Tmandriva.Install_9_1:boolean;
  var
     update_yes:string;

begin
     writeln('You are using Mandrake 9.1');
     writeln('The installer should update your urpmi configuration in order');
     writeln('to get repositories');
     writeln('Do you want to execute this operation (yes/no) ?:');
     readln(update_yes);

     if update_yes='yes' then begin
          writeln('Adding sources repositories... ');
          Shell('/usr/sbin/urpmi.addmedia main ftp://ftp.u-picardie.fr/mirror/ftp.mandriva.com/MandrivaLinux/official/9.1/i586/Mandrake/RPMS/ with ../base/hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia contrib ftp://ftp.u-picardie.fr/mirror/ftp.mandriva.com/MandrivaLinux/official/9.1/contrib/i586/ with ../../i586/Mandrake/base/hdlist2.cz');
          Shell('/usr/sbin/urpmi.addmedia plf-free ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/plf/mandriva/free/9.1/i586 with hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia plf-nonfree ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/plf/mandriva/non-free/9.1/i586 with hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia --update updates ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/old/updates/9.1/RPMS/ with ../base/hdlist.cz');
          writeln('update sources repositories... waiting please...');
          Shell('/usr/sbin/urpmi.update -a');
     end;
       writeln('Do you want to start installing required packages ?(yes/no)');
       readln(update_yes);
        if update_yes='yes' then Shell('/usr/sbin/urpmi ' + REPOS + ' --auto');

end;
//######################################################################################################
function Tmandriva.Install_9_2:boolean;
  var
     update_yes:string;

begin
     writeln('You are using Mandrake 9.1');
     writeln('The installer should update your urpmi configuration in order');
     writeln('to get repositories');
     writeln('Do you want to execute this operation (yes/no) ?:');
     readln(update_yes);

     if update_yes='yes' then begin
          writeln('Adding sources repositories... ');
          Shell('/usr/sbin/urpmi.addmedia main ftp://ftp.u-picardie.fr/mirror/ftp.mandriva.com/MandrivaLinux/official/9.2/i586/Mandrake/RPMS/ with ../base/hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia contrib ftp://ftp.u-picardie.fr/mirror/ftp.mandriva.com/MandrivaLinux/official/9.2/contrib/i586/ with ../../i586/Mandrake/base/hdlist2.cz');
          Shell('/usr/sbin/urpmi.addmedia plf-free ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/plf/mandriva/free/9.2/i586 with hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia plf-nonfree ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/plf/mandriva/non-free/9.2/i586 with hdlist.cz');
          Shell('/usr/sbin/urpmi.addmedia --update updates ftp://distrib-coffee.ipsl.jussieu.fr/pub/linux/MandrivaLinux/old/updates/9.2/RPMS/ with ../base/hdlist.cz');
          writeln('update sources repositories... waiting please...');
          Shell('/usr/sbin/urpmi.update -a');
     end;
       writeln('Do you want to start installing required packages ?(yes/no)');
       readln(update_yes);
        if update_yes='yes' then Shell('/usr/sbin/urpmi ' + REPOS + ' --auto');

end;
//######################################################################################################
function Tmandriva.ReadFileIntoString(path:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   Afile:text;
   i:integer;
   datas:string;
   datas_file:string;
begin

      if not FileExists(path) then begin
        writeln('Error:thProcThread.ReadFileIntoString -> file not found (' + path + ')');
        exit;

      end;
      TRY
     assign(Afile,path);
     reset(Afile);
     while not EOF(Afile) do
           begin
           readln(Afile,datas);
           datas_file:=datas_file + datas +CRLF;
           end;

close(Afile);
             EXCEPT
              writeln('Error:thProcThread.ReadFileIntoString -> unable to read (' + path + ')');
           end;
result:=datas_file;


end;





end.
