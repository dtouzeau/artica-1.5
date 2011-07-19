/* mini_sendmail - accept email on behalf of real sendmail
**
** Copyright © 1999 by Jef Poskanzer <jef@mail.acme.com>.
** All rights reserved.
**
** Redistribution and use in source and binary forms, with or without
** modification, are permitted provided that the following conditions
** are met:
** 1. Redistributions of source code must retain the above copyright
**    notice, this list of conditions and the following disclaimer.
** 2. Redistributions in binary form must reproduce the above copyright
**    notice, this list of conditions and the following disclaimer in the
**    documentation and/or other materials provided with the distribution.
**
** THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND
** ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
** IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
** ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE
** FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
** DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
** OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
** HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
** LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
** OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
** SUCH DAMAGE.
*/


/* These defines control some optional features of the program.  If you
** don't want the features you can undef the symbols; some of them mean
** a significant savings in executable size.
*/
#define DO_RECEIVED	/* whether to add a "Received:" header */
#define DO_GETPWUID	/* whether to try a getpwuid() if getlogin() fails */
#define DO_MINUS_SP	/* whether to implement the -s and -p flags */
#define DO_DNS		/* whether to do a name lookup on -s, or just IP# */


#include <unistd.h>
#include <stdlib.h>
#include <stdio.h>
#include <signal.h>
#include <string.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>
#include <fcntl.h>
#include <dirent.h> 

#ifdef DO_RECEIVED
#include <time.h>
#endif /* DO_RECEIVED */
#ifdef DO_GETPWUID
#include <pwd.h>
#endif /* DO_GETPWUID */

#include "version.h"


/* Defines. */
#define SMTP_PORT 25
#define DEFAULT_TIMEOUT 60


/* Globals. */
static char* argv0;
static char* fake_from;
static int parse_message, verbose;
#ifdef DO_MINUS_SP
static char* server;
static short port;
#endif /* DO_MINUS_SP */
static int timeout;
static int sockfd1, sockfd2;
static FILE* sockrfp;
static FILE* sockwfp;
static int got_a_recipient;


/* Forwards. */
static void usage( void );
static char* slurp_message( void );
#ifdef DO_RECEIVED
static char* make_received( char* from, char* username, char* hostname );
#endif /* DO_RECEIVED */
static void parse_for_recipients( char* message );
static void add_recipient( char* recipient, int len );
static int open_client_socket( void );
static int read_response( void );
static int myError( char *str, char *code);
static void send_command( char* command );
static void send_data( char* data );
static void send_done( void );
static void sigcatch( int sig );
static void show_error( char* cause );


int myError( char *str, char *code)
{
   int fd;
   char *string;

   string = (char *)malloc( (strlen(str)+strlen(code)+11)*sizeof(char) );

   strcpy (string, "");
   strcat (string, str);
   strcat (string, " : ");
   strcat (string, code);
   strcat (string, "\n");

   if ((fd = open("/tmp/_articasend.err", O_CREAT|O_APPEND|O_RDWR,0777))==-1){
               perror("Unable to open data");
       }
       if ((fd = write(fd, string, strlen(string)))==-1){
               perror("Unable to write to data");
       }
   close(fd);
   return ( 0);
} 


int
main( int argc, char** argv ){
    int argn;
    char* message;
#ifdef DO_RECEIVED
    char* received;
#endif /* DO_RECEIVED */
    char* username;
    char hostname[500];
    char from[1000];
    int status;
    char buf[2000];

    /* Parse args. */
    argv0 = argv[0];
    fake_from = (char*) 0;
    parse_message = 0;
#ifdef DO_MINUS_SP
    server = "127.0.0.1";
    port = SMTP_PORT;
#endif /* DO_MINUS_SP */
    verbose = 0;
    timeout = DEFAULT_TIMEOUT;
    argn = 1;
    if (access("/tmp/_articasend.err", F_OK)==0){
	if (unlink("/tmp/_articasend.err")<0){
		printf("cannot delete file %s\n", "/tmp/_articasend.err");
	  }
    }


    while ( argn < argc && argv[argn][0] == '-' )
	{
	if ( strncmp( argv[argn], "-f", 2 ) == 0 && argv[argn][2] != '\0' )
	    fake_from = &(argv[argn][2]);
	else if ( strcmp( argv[argn], "-t" ) == 0 )
	    parse_message = 1;
#ifdef DO_MINUS_SP
	else if ( strncmp( argv[argn], "-s", 2 ) == 0 && argv[argn][2] != '\0' )
	    server = &(argv[argn][2]);
	else if ( strncmp( argv[argn], "-p", 2 ) == 0 && argv[argn][2] != '\0' )
	    port = atoi( &(argv[argn][2]) );
#endif /* DO_MINUS_SP */
	else if ( strncmp( argv[argn], "-T", 2 ) == 0 && argv[argn][2] != '\0' )
	    timeout = atoi( &(argv[argn][2]) );
	else if ( strcmp( argv[argn], "-v" ) == 0 )
	    verbose = 1;
	else if ( strcmp( argv[argn], "-i" ) == 0 )
	    ;	/* ignore */
	else if ( strcmp( argv[argn], "-oi" ) == 0 )
	    ;	/* ignore */
	else if ( strcmp( argv[argn], "--" ) == 0 )
	    ;	/* ignore */
	else
	    usage();
	++argn;
	}

    username = getlogin();
    if ( username == (char*) 0 )
	{
#ifdef DO_GETPWUID
	struct passwd* pw = getpwuid( getuid() );
	if ( pw == (struct passwd*) 0 )
	    {
	    (void) fprintf( stderr, "%s: can't determine username\n", argv0 );
	    exit( 1 );
	    }
	username = pw->pw_name;
#else /* DO_GETPWUID */
	(void) fprintf( stderr, "%s: can't determine username\n", argv0 );
	exit( 1 );
#endif /* DO_GETPWUID */
	}

    if ( gethostname( hostname, sizeof(hostname) - 1 ) < 0 )
	show_error( "gethostname" );

    if ( fake_from == (char*) 0 )
	(void) snprintf( from, sizeof(from), "%s@%s", username, hostname );
    else
	if ( strchr( fake_from, '@' ) == (char*) 0 )
	    (void) snprintf( from, sizeof(from), "%s@%s", fake_from, hostname );
	else
	    (void) snprintf( from, sizeof(from), "%s", fake_from );

    /* Strip off any angle brackets in the from address. */
    while ( from[0] == '<' )
	(void) strcpy( from, &from[1] );
    while ( from[strlen(from)-1] == '>' )
	from[strlen(from)-1] = '\0';

    message = slurp_message();
#ifdef DO_RECEIVED
    received = make_received( from, username, hostname );
#endif /* DO_RECEIVED */

    (void) signal( SIGALRM, sigcatch );

    (void) alarm( timeout );
    sockfd1 = open_client_socket();

    sockfd2 = dup( sockfd1 );
    sockrfp = fdopen( sockfd1, "r" );
    sockwfp = fdopen( sockfd2, "w" );

    /* The full SMTP protocol is spelled out in RFC821, available at
    ** http://www.faqs.org/rfcs/rfc821.html
    ** The only non-obvious wrinkles:
    **  - The commands are terminated with CRLF, not newline.
    **  - Newlines in the data file get turned into CRLFs.
    **  - Any data lines beginning with a period get an extra period prepended.
    */

    status = read_response();
    if ( status != 220 )
	{
	(void) fprintf(
	    stderr,  "%s: unexpected initial greeting %d\n", argv0, status );
	    myError( "ERROR", "unexpected initial greeting" );
	exit( 1 );
	}

    (void) snprintf( buf, sizeof(buf), "HELO %s", hostname );
    send_command( buf );
    status = read_response();
    if ( status != 250 )
	{
	(void) fprintf(
	    stderr,  "%s: unexpected response %d to HELO command\n",argv0, status );
	    myError( "ERROR", "unexpected response %d to HELO command" );
	exit( 1 );
	}

    (void) snprintf( buf, sizeof(buf), "MAIL FROM:<%s>", from );
    send_command( buf );
    status = read_response();
    if ( status != 250 )
	{
	(void) fprintf(
	    stderr,  "%s: unexpected response %d to MAIL FROM command\n",argv0, status );
	    myError( "ERROR", "unexpected response to MAIL FROM command" );
	exit( 1 );
	}

    got_a_recipient = 0;
    for ( ; argn < argc; ++argn )
	add_recipient( argv[argn], strlen( argv[argn] ) );
    if ( parse_message )
	parse_for_recipients( message );
    if ( ! got_a_recipient )
	{
	(void) fprintf( stderr,  "%s: no recipients found\n", argv0 );
	 myError( "ERROR", "no recipients found" );
	exit( 1 );
	}

    send_command( "DATA" );
    status = read_response();
    if ( status != 354 )
	{
	(void) fprintf(stderr,  "%s: unexpected response %d to DATA command\n",argv0, status );
	myError( "ERROR", "unexpected response to DATA" );
	exit( 1 );
	}

#ifdef DO_RECEIVED
    send_data( received );
#endif /* DO_RECEIVED */
    send_data( message );
    send_done();
    status = read_response();
    if ( status != 250 )
	{
	(void) fprintf(stderr,  "%s: unexpected response %d to DATA\n", argv0, status );
	myError( "ERROR", "unexpected response to DATA" );
	exit( 1 );
	}

    send_command( "QUIT" );
    status = read_response();
    if ( status != 221 )
	(void) fprintf(
	    stderr,  "%s: unexpected response %d to QUIT command - ignored\n",argv0, status );
	    myError( "INFO", "unexpected response to QUIT command - ignored" );

    (void) close( sockfd1 );
    (void) close( sockfd2 );

    exit( 0 );
    }


static void
usage( void )
    {
#ifdef DO_MINUS_SP
#ifdef DO_DNS
    char* spflag = "[-s<server>] [-p<port>] ";
#else /* DO_DNS */
    char* spflag = "[-s<server_ip>] [-p<port>] ";
#endif /* DO_DNS */
#else /* DO_MINUS_SP */
    char* spflag = "";
#endif /* DO_MINUS_SP */
    (void) fprintf( stderr, "usage:  %s [-f<name>] [-t] %s[-T<timeout>] [-v] [address ...]\n", argv0, spflag );
    exit( 1 );
    }


static char*
slurp_message( void )
    {
    char* message;
    int message_size, message_len;
    int c;

    message_size = 5000;
    message = (char*) malloc( message_size );
    if ( message == (char*) 0 )
	{
	(void) fprintf( stderr, "%s: out of memory\n", argv0 );
	myError( "ERROR", "out of memory" );
	exit( 1 );
	}
    message_len = 0;

    for (;;)
	{
	c = getchar();
	if ( c == EOF )
	    break;
	if ( message_len + 1 >= message_size )
	    {
	    message_size *= 2;
	    message = (char*) realloc( (void*) message, message_size );
	    if ( message == (char*) 0 )
		{
		(void) fprintf( stderr, "%s: out of memory\n", argv0 );
		myError( "ERROR", "out of memory" );
		exit( 1 );
		}
	    }
	message[message_len++] = c;
	}
    message[message_len] = '\0';

    return message;
    }


#ifdef DO_RECEIVED
static char*
make_received( char* from, char* username, char* hostname )
    {
    int received_size;
    char* received;
    time_t t;
    struct tm* tmP;
    char timestamp[100];

    t = time( (time_t*) 0 );
    tmP = localtime( &t );
    (void) strftime( timestamp, sizeof(timestamp), "%a, %d %b %Y %T %Z", tmP );
    received_size =
	500 + strlen( from ) + strlen( hostname ) * 2 + strlen( VERSION ) +
	strlen( timestamp ) + strlen( username );
    received = (char*) malloc( received_size );
    if ( received == (char*) 0 )
	{
	(void) fprintf( stderr, "%s: out of memory\n", argv0 );
	myError( "ERROR", "out of memory" );
	exit( 1 );
	}
    (void) snprintf(
	received, received_size,
	"Received: (from %s)\n\tby %s (%s);\n\t%s\n\t(sender %s@%s)\n",
	from, hostname, VERSION, timestamp, username, hostname );
    return received;
    }
#endif /* DO_RECEIVED */


static void
parse_for_recipients( char* message )
    {
    /* A little finite-state machine that parses the message, looking
    ** for To:, Cc:, and Bcc: recipients.
    */
    int state;
#define ST_BOL		0
#define ST_OTHERHEAD	1
#define ST_T		2
#define ST_C		3
#define ST_B		4
#define ST_BC		5
#define ST_RECIPHEAD	6
#define ST_RECIPS	7
    char* cp;
    char* bcc;
    char* recip;

    state = ST_BOL;
    bcc = (char*) 0;
    for ( cp = message; *cp != '\0'; ++cp )
	{
	switch ( state )
	    {
	    case ST_BOL:
	    switch ( *cp )
		{
		case '\n':
		return;
		case 'T':
		case 't':
		state = ST_T;
		break;
		case 'C':
		case 'c':
		state = ST_C;
		break;
		case 'B':
		case 'b':
		state = ST_B;
		bcc = cp;
		break;
		default:
		state = ST_OTHERHEAD;
		break;
		}
	    break;
	    case ST_OTHERHEAD:
	    switch ( *cp )
		{
		case '\n':
		state = ST_BOL;
		break;
		}
	    break;
	    case ST_T:
	    switch ( *cp )
		{
		case '\n':
		state = ST_BOL;
		break;
		case 'O':
		case 'o':
		state = ST_RECIPHEAD;
		break;
		default:
		state = ST_OTHERHEAD;
		break;
		}
	    break;
	    case ST_C:
	    switch ( *cp )
		{
		case '\n':
		state = ST_BOL;
		break;
		case 'C':
		case 'c':
		state = ST_RECIPHEAD;
		break;
		default:
		state = ST_OTHERHEAD;
		break;
		}
	    break;
	    case ST_B:
	    switch ( *cp )
		{
		case '\n':
		state = ST_BOL;
		bcc = (char*) 0;
		break;
		case 'C':
		case 'c':
		state = ST_BC;
		break;
		default:
		state = ST_OTHERHEAD;
		bcc = (char*) 0;
		break;
		}
	    break;
	    case ST_BC:
	    switch ( *cp )
		{
		case '\n':
		state = ST_BOL;
		bcc = (char*) 0;
		break;
		case 'C':
		case 'c':
		state = ST_RECIPHEAD;
		break;
		default:
		state = ST_OTHERHEAD;
		bcc = (char*) 0;
		break;
		}
	    break;
	    case ST_RECIPHEAD:
	    switch ( *cp )
		{
		case '\n':
		state = ST_BOL;
		bcc = (char*) 0;
		break;
		case ':':
		state = ST_RECIPS;
		recip = cp + 1;
		break;
		default:
		state = ST_OTHERHEAD;
		bcc = (char*) 0;
		break;
		}
	    break;
	    case ST_RECIPS:
	    switch ( *cp )
		{
		case '\n':
		add_recipient( recip, ( cp - recip ) );
		state = ST_BOL;
		if ( bcc != (char*) 0 )
		    {
		    /* Elide the Bcc: line, and reset cp. */
		    (void) strcpy( bcc, cp + 1 );
		    cp = bcc - 1;
		    bcc = (char*) 0;
		    }
		break;
		case ',':
		add_recipient( recip, ( cp - recip ) );
		recip = cp + 1;
		break;
		}
	    break;
	    }
	}
    }


static void
add_recipient( char* recipient, int len )
    {
    char buf[1000];
    int status;

    /* Skip leading whitespace. */
    while ( len > 0 && ( *recipient == ' ' || *recipient == '\t' ) )
	{
	++recipient;
	--len;
	}

    /* Strip off any angle brackets. */
    while ( len > 0 && *recipient == '<' )
	{
	++recipient;
	--len;
	}
    while ( len > 0 && recipient[len-1] == '>' )
	--len;

    (void) snprintf( buf, sizeof(buf), "RCPT TO:<%.*s>", len, recipient );
    send_command( buf );
    status = read_response();
    if ( status != 250  && status != 251 )
	{
	(void) fprintf(
	    stderr,  "%s: unexpected response %d to RCPT TO command\n",argv0, status );
           myError( "ERROR", "unexpected response %d to RCPT TO command" );
	exit( 1 );
	}
    got_a_recipient = 1;
    }


#if defined(AF_INET6) && defined(IN6_IS_ADDR_V4MAPPED)
#define USE_IPV6
#endif

static int
open_client_socket( void )
    {
#ifdef USE_IPV6
    struct sockaddr_in6 sa;
#else /* USE_IPV6 */
    struct sockaddr_in sa;
#endif /* USE_IPV6 */
    int sa_len, sock_family, sock_type, sock_protocol;
    int sockfd;

    sock_type = SOCK_STREAM;
    sock_protocol = 0;
    sa_len = sizeof(sa);
    (void) memset( (void*) &sa, 0, sa_len );

#ifdef USE_IPV6

    {
#ifdef DO_MINUS_SP
    struct sockaddr_in sa4;
    struct addrinfo hints;
    char portstr[10];
    int gaierr;
    struct addrinfo* ai;
    struct addrinfo* ai2;
    struct addrinfo* aiv4;
    struct addrinfo* aiv6;
#endif /* DO_MINUS_SP */

    sock_family = PF_INET6;

#ifdef DO_MINUS_SP
    (void) memset( (void*) &sa4, 0, sizeof(sa4) );
    if ( inet_pton( AF_INET, server, (void*) &sa4.sin_addr ) == 1 )
	{
	sock_family = PF_INET;
	sa4.sin_port = htons( port );
	sa_len = sizeof(sa4);
	(void) memmove( &sa, &sa4, sa_len );
	}
    else if ( inet_pton( AF_INET6, server, (void*) &sa.sin6_addr ) != 1 )
	{
#ifdef DO_DNS
	(void) memset( &hints, 0, sizeof(hints) );
	hints.ai_family = PF_UNSPEC;
	hints.ai_socktype = SOCK_STREAM;
	(void) snprintf( portstr, sizeof(portstr), "%d", port );
	if ( (gaierr = getaddrinfo( server, portstr, &hints, &ai )) != 0 )
	    {
	    (void) fprintf(
		stderr, "%s: getaddrinfo %s - %s\n", argv0, server,gai_strerror( gaierr ) );
		myError( "ERROR: getaddrinfo", server );
	    exit( 1 );
	    }

	/* Find the first IPv4 and IPv6 entries. */
	aiv4 = (struct addrinfo*) 0;
	aiv6 = (struct addrinfo*) 0;
	for ( ai2 = ai; ai2 != (struct addrinfo*) 0; ai2 = ai2->ai_next )
	    {
	    switch ( ai2->ai_family )
		{
		case PF_INET:
		if ( aiv4 == (struct addrinfo*) 0 )
		    aiv4 = ai2;
		break;
		case PF_INET6:
		if ( aiv6 == (struct addrinfo*) 0 )
		    aiv6 = ai2;
		break;
		}
	    }

	/* If there's an IPv4 address, use that, otherwise try IPv6. */
	if ( aiv4 != (struct addrinfo*) 0 )
	    {
	    if ( sizeof(sa) < aiv4->ai_addrlen )
		{
		(void) fprintf(
		    stderr, "%s - sockaddr too small (%lu < %lu)\n",
		    server, (unsigned long) sizeof(sa),
		    (unsigned long) aiv4->ai_addrlen );
		exit( 1 );
		}
	    sock_family = aiv4->ai_family;
	    sock_type = aiv4->ai_socktype;
	    sock_protocol = aiv4->ai_protocol;
	    sa_len = aiv4->ai_addrlen;
	    (void) memmove( &sa, aiv4->ai_addr, sa_len );
	    goto ok;
	    }
	if ( aiv6 != (struct addrinfo*) 0 )
	    {
	    if ( sizeof(sa) < aiv6->ai_addrlen )
		{
		(void) fprintf(
		    stderr, "%s - sockaddr too small (%lu < %lu)\n",
		    server, (unsigned long) sizeof(sa),
		    (unsigned long) aiv6->ai_addrlen );
		exit( 1 );
		}
	    sock_family = aiv6->ai_family;
	    sock_type = aiv6->ai_socktype;
	    sock_protocol = aiv6->ai_protocol;
	    sa_len = aiv6->ai_addrlen;
	    (void) memmove( &sa, aiv6->ai_addr, sa_len );
	    goto ok;
	    }

	(void) fprintf(
	    stderr, "%s: no valid address found for host %s\n", argv0, server );
	   myError( "ERROR", "no valid address found for host" );
	exit( 1 );

	ok:
	freeaddrinfo( ai );
#else /* DO_DNS */
        (void) fprintf(stderr, "%s: bad server IP address %s\n", argv0, server );
	myError( "ERROR: bad server IP address", server  );
	exit( 1 );
#endif /* DO_DNS */
	}
#else /* DO_MINUS_SP */
    sa.sin6_addr = in6addr_any;
    sa.sin6_port = htons( SMTP_PORT );
#endif /* DO_MINUS_SP */

    sa.sin6_family = sock_family;

    }

#else /* USE_IPV6 */

    {
#ifdef DO_MINUS_SP
    struct hostent *he;
#else /* DO_MINUS_SP */
    char local_addr[4] = { 127, 0, 0, 1 };
#endif /* DO_MINUS_SP */

    sock_family = PF_INET;

#ifdef DO_MINUS_SP
    sa.sin_addr.s_addr = inet_addr( server );
    sa.sin_port = htons( port );
    if ( (int32_t) sa.sin_addr.s_addr == -1 )
	{
#ifdef DO_DNS
	he = gethostbyname( server );
	if ( he == (struct hostent*) 0 )
	    {
	    (void) fprintf(
		stderr, "%s: server name lookup of '%s' failed - %s\n",argv0, server, hstrerror( h_errno ) );
		myError( " server name lookup of '%s' failed - %s\n", argv0 );
	    exit( 1 );
	    }
	sock_family = he->h_addrtype;
	(void) memmove( &sa.sin_addr, he->h_addr, he->h_length );
#else /* DO_DNS */
	(void) fprintf(
	    stderr, "%s: bad server IP address %s\n", argv0, server );
	    myError( "ERROR bad server IP address", server );
	exit( 1 );
#endif /* DO_DNS */
	}
#else /* DO_MINUS_SP */
    (void) memmove( &sa.sin_addr, local_addr, sizeof(local_addr) );
    sa.sin_port = htons( SMTP_PORT );
#endif /* DO_MINUS_SP */

    sa.sin_family = sock_family;
    }

#endif /* USE_IPV6 */

    sockfd = socket( sock_family, sock_type, sock_protocol );
    if ( sockfd < 0 )
	show_error( "socket" );

    if ( connect( sockfd, (struct sockaddr*) &sa, sa_len ) < 0 )
	show_error( "connect" );

    return sockfd;
    }


static int
read_response( void )
    {
    char buf[10000];
    char* cp;
    int status;

    for (;;)
	{
	(void) alarm( timeout );
	if ( fgets( buf, sizeof(buf), sockrfp ) == (char*) 0 )
	    {
	    (void) fprintf( stderr, "%s: unexpected EOF\n", argv0 );
	    myError( " unexpected EOF\n", argv0 );
	    exit( 1 );
	    }
	if ( verbose )
	    (void) fprintf( stderr, "<<<< %s", buf );
	for ( status = 0, cp = buf; *cp >= '0' && *cp <= '9'; ++cp )
	    status = 10 * status + ( *cp - '0' );
	if ( *cp == ' ' )
	    break;
	if ( *cp != '-' )
	    {
	    (void) fprintf(stderr,  "%s: bogus reply syntax - '%s'\n", argv0, buf );
		myError( "ERROR", "reply syntax -" );
	    exit( 1 );
	    }
	}
    return status;
    }


static void
send_command( char* command )
    {
    (void) alarm( timeout );
    if ( verbose )
	(void) fprintf( stderr, ">>>> %s\n", command );
    (void) fprintf( sockwfp, "%s\r\n", command );
    (void) fflush( sockwfp );
    }


static void
send_data( char* data )
    {
    int bol;

    for ( bol = 1; *data != '\0'; ++data )
	{
	if ( bol && *data == '.' )
	    putc( '.', sockwfp );
	bol = 0;
	if ( *data == '\n' )
	    {
	    putc( '\r', sockwfp );
	    bol = 1;
	    }
	putc( *data, sockwfp );
	}
    if ( ! bol )
	(void) fputs( "\r\n", sockwfp );
    }


static void
send_done( void )
    {
    (void) fputs( ".\r\n", sockwfp );
    (void) fflush( sockwfp );
    }


static void
sigcatch( int sig )
    {
    (void) fprintf( stderr, "%s: timed out\n", argv0 );
    myError( "ERROR","Timed out");
    exit( 1 );
    }


static void
show_error( char* cause )
    {
    char buf[5000];

    (void) snprintf( buf, sizeof(buf), "%s: %s", argv0, cause );
    myError( "ERROR", cause );
    perror( buf );
    exit( 1 );
    }
