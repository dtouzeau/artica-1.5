{%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
                       Powtils Sendmail Unix
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

  Most servers have SendMail binary installed, so using these functions for
  email is convenient. If SendMail is not installed, the general purpose mail
  functions in other units which connect to port 25 will work for any standard
  mail server. Since this unit currently uses AssignStream, it only works on
  Unix/Linux servers. See SMTP units for windows email functions

  Note: if sendmail is configured incorrectly on your machine, mail may take
  a while (hesitate) to send. A correctly configured server will not hesitate.

 ------------------------------------------------------------------------------
  Contributors/Authors:
 ------------------------------------------------------------------------------
   Lars (L505)

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%}

unit pwsendmail_unix;

{$mode objfpc}{$H+}

{------------------------------------------------------------------------------}
interface
{------------------------------------------------------------------------------}

uses
 {$IFNDEF DYNWEB}
  pwumain,
 {$ELSE}
  dynpwu,
 {$ENDIF}
  pwuMailPrep, //mail preparation
  unix,
  
  sysutils;

var
  SENDMAIL_PATH: string ='/usr/sbin/sendmail'; // todo: make this a PWU config file var???

type
  TMsgPriority = (mpNormal, mpHigh, mpLow);
  TMsgType = (mtText, mtHTML);
  
  TEmailAttachment = record
    FileName: ansistring; //name of file
    FileType: ansistring; //content/mime type
    Disposition: (aRegular, aInline); //attached separately or inline attachment
  end;
  
  TEmailAttachments = array of TEmailAttachment;



{------------------------------------------------------------------------------}
{--------- PUBLIC PROCEDURE/FUNCTION DECLARATIONS -----------------------------}
{------------------------------------------------------------------------------}

  function SendMail(From: ansistring;
                    SendTo: ansistring;
                    Subject: ansistring;
                    MsgText: ansistring;
                    Kind: TMsgType = mtText
                   ): boolean;



  function SendMail(From: ansistring;
                    SendTo: ansistring;
                    Subject: ansistring;
                    MsgText: ansistring;
                    Kind: TMsgType = mtText;
                    Priority: TMsgPriority = mpNormal
                    ): boolean;




  function SendMail(From: ansistring;
                    SendTo: ansistring;
                    Subject: ansistring;
                    MsgText: ansistring;
                    AttachedFiles: TEmailAttachments;
                    Kind: TMsgType = mtText;
                    Priority: TMsgPriority = mpNormal
                    ): boolean;


//  END OF PUBLIC PROCEDURE/FUNCTION DECLARATIONS
{------------------------------------------------------------------------------}


{------------------------------------------------------------------------------}
implementation
{------------------------------------------------------------------------------}

                               
{------------------------------------------------------------------------------}
{--------- PRIVATE PROCEDURES/FUNCTIONS ---------------------------------------}
{------------------------------------------------------------------------------}

procedure WriteHead(var sOut: text; MsgType: TMsgType; Priority: TMsgPriority);
begin
  writeln(sOut, 'MIME-Version: 1.0');                
  writeln(sOut, 'X-Mailer: PWU SendMail');
  case priority of
    mpNormal: writeln(sOut, 'X-Priority: 3 (Normal)');         
    mpLow: writeln(sOut, 'X-Priority: 5 (Low)');            
    mpHigh: writeln(sOut, 'X-Priority: 1 (High)');           
  end;
  case MsgType of
    mtText: writeln(sOut, 'Content-Type: text/plain');
    mtHTML: writeln(sOut, 'Content-Type: text/html');
  end;
  writeln(sOut); //new line needed
end;



function SendmailExists: boolean;
begin
  result:= FileExists(SENDMAIL_PATH); 
end;



//  END OF PRIVATE PROCEDURES/FUNCTIONS
{------------------------------------------------------------------------------}



{------------------------------------------------------------------------------}
{--------- PUBLIC PROCEDURES/FUNCTIONS ----------------------------------------}
{------------------------------------------------------------------------------}


{ Sends an email by sendmail, with default priority (normal).
  email can be written in plain text or html

  Returns true if the mail was sent for delivery attempt, false if error
  opening sendmail. True does not indicate the mail went through, because
  it does not check for bad addresses or failed deliveries }
function SendMail(From: ansistring;
                  SendTo: ansistring;
                  Subject: ansistring;
                  MsgText:ansistring;
                  Kind: TMsgType = mtText
                 ): boolean;
var
  sIn,
  sOut:text;
  Arg1: ansistring;
begin
  result:= false;
  if SendMailExists = false then exit; 
  Arg1:= '-t';    //tells sendmail to parse for TO, FROM, CC, BCC in text
  if AssignStream(sIn, sOut, SENDMAIL_PATH, [arg1]) <> -1 then
  begin
    writeln(sOut, 'From: ' + From );
    writeln(sOut, 'To: ' + SendTo );
//    writeln(sout, 'Reply-to: ' + From);
    writeln(sOut, 'Subject: ' + Subject);
    WriteHead(sOut, Kind, mpNormal);
    writeln(sOut, MsgText);
    writeln(sOut, '.'); // signals end of email
    writeln(sOut); 
    result:= true;
  end else
  begin
    throwweberror('AssignStream returned error -1');
    result:= false;
  end;
  close(sIn);
  close(sOut);
end;


{ Same as above with the ability to specify priority. }
function SendMail(From: ansistring;
                  SendTo: ansistring;
                  Subject: ansistring;
                  MsgText: ansistring;
                  Kind: TMsgType = mtText;
                  Priority: TMsgPriority = mpNormal
                 ): boolean;
var
  sIn,
  sOut:text;
  Arg1: ansistring;
begin
  result:= false;
  if SendMailExists = false then exit; 
  Arg1:= '-t';    //tells sendmail to parse for TO, FROM, CC, BCC in text
  if AssignStream(SIN, SOUT, SENDMAIL_PATH, [Arg1]) <> -1 then
  begin
    writeln(sOut, 'FROM: ' + From);              
    writeln(sOut, 'TO: ' + SendTo);              
//    writeln(sout, 'Reply-to: ' + From);
    writeln(sOut, 'SUBJECT: ' + Subject);        
    WriteHead(sout, Kind, Priority);
    writeln(sOut, MsgText);                        // message
    writeln(sOut, '.'); // signals end of email
    result:= true;
  end else
  begin
    throwweberror('AssignStream returned error -1');
    result:= false;
  end;
  close(sIn);
  close(sOut);
end;


{ same as above with ability to attach files }
function SendMail(From: ansistring;
                  SendTo: ansistring;
                  Subject: ansistring;
                  MsgText: ansistring;
                  AttachedFiles: TEmailAttachments;
                  Kind: TMsgType = mtText;
                  Priority: TMsgPriority = mpNormal
                 ): boolean;
var
  sIn,
  sOut:text;
  Arg1: ansistring;
  TheDisposition: ansistring;
  iLoc: integer; //local loop integer
  BoundaryDivider: ansistring;
begin
  result:= false;
  if SendMailExists = false then exit; 
  Arg1:= '-t';    //tells sendmail to parse for TO, FROM, CC, BCC in text
  BoundaryDivider:= '_Divider_' + PrepEmailBoundary;
  
  if AssignStream(sIN, sOUT, SENDMAIL_PATH, [Arg1]) <> -1 then
  begin

    writeln(sOut, 'FROM: ' + From);               // from
    writeln(sOut, 'TO: ' + SendTo);                   // to
//    writeln(sout, 'Reply-to: ' + From);
    writeln(sOut, 'SUBJECT: ' + Subject);              // subject                         
    writeln(sOut, 'Content-Type: multipart/mixed;');
    writeln(sOut, ' boundary="----=' + BoundaryDivider + '"');
    writeln(sOut);
    writeln(sOut, 'This is a multi-part message in MIME format.');
    writeln(sOut);
    writeln(sOut, '------=' + BoundaryDivider);
//    writeln(sout, 'Content-Type: text/plain;');
    writeln(sOut, 'charset="iso-8859-1"');
    writeln(sOut, 'Content-Transfer-Encoding: 7bit');
    WriteHead(sOut, Kind, Priority);
//    writeln(sOut); // must have a blank line here 
    writeln(sOut, MsgText);

    { attachments : include them }
    if length(AttachedFiles) > 0 then //if there are any attached files at all
    begin

      for iLoc:= 0 to length(AttachedFiles) - 1 do
      begin
        if fileexists(AttachedFiles[iLoc].Filename) = false then
        begin
          ThrowWebError('A file attachment: ' + AttachedFiles[iLoc].Filename + ', not found.' + #13#10 +
                        'Email aborted.' + #13#10 +
                        'Remove all SetLength calls if you have not attached any files.' + #13#10 +
                        'SetLength must specify number of files attached.');

          halt;
        end;

        if AttachedFiles[iLoc].Disposition = aRegular then
          TheDisposition:= 'attachment';
        if AttachedFiles[iLoc].Disposition = aInline then
          TheDisposition:= 'inline';

        writeln(sOut, '------=' + BoundaryDivider);
        writeln(sOut, 'Content-Type: ' + AttachedFiles[iLoc].FileType + ';');
        writeln(sOut, ' name="' + AttachedFiles[iLoc].FileName + '"');
        writeln(sOut, 'Content-Transfer-Encoding: base64');
        writeln(sOut, 'Content-Disposition: ' + TheDisposition + '; filename="' + AttachedFiles[iLoc].FileName + '"');
        writeln(sOut);      //must have a blank line here 
        
        // write each file attachment's contents
        writeln(sOut, PrepFileContents(AttachedFiles[iLoc].FileName));
        writeln(sOut);        
      end;

    end;
    writeln(sOut, '------=' + BoundaryDivider);

    writeln(sOut, '.'); // signals end of email
    writeln(sOut); 
    result:= true;
  end else
  begin
    ThrowWebError('AssignStream returned error -1');
    result:= false;
  end;

  close(sIn);
  close(sOut);

end;


//  END OF PUBLIC PROCEDURES/FUNCTIONS
{------------------------------------------------------------------------------}




end.

