package Linux::net::dev;

require 5.000;
use strict;
use warnings;
use Carp;

require Exporter;
use AutoLoader qw(AUTOLOAD);

our @ISA = qw(Exporter);

# Items to export into callers namespace by default. Note: do not export
# names by default without a very good reason. Use EXPORT_OK instead.
# Do not simply export all your public functions/methods/constants.

# This allows declaration	use Linux::net::dev ':all';
# If you do not need this, moving things directly into @EXPORT or @EXPORT_OK
# will save memory.
our %EXPORT_TAGS = ( 'all' => [ qw(
	
) ] );

our @EXPORT_OK = ( @{ $EXPORT_TAGS{'all'} } );

our @EXPORT = qw(
	
);
our $VERSION = '1.00';

# Preloaded methods go here.

sub info {
  return {} unless -r "/proc/net/dev";
  open(DEV, "/proc/net/dev");
  my (@titles, %result);
  while (my $line = <DEV>) {
    chomp($line);
    if ($line =~ /^.{6}\|([^\\]+)\|([^\\]+)$/) {
      my ($rec, $trans) = ($1, $2);
      @titles = (
        (map { "r$_" } split(/\s+/, $rec)),
        (map { "t$_" } split(/\s+/, $trans)),
      );
    } elsif ($line =~ /^\s*([^:]+):\s*(.*)$/) {
      my ($id, @data) = ($1, split(/\s+/, $2));
      $result{$id} = { map {
        $titles[$_] => $data[$_];
      } (0..$#titles) };
    }
  }
  close(DEV);
  return \%result;
}

sub dev {
  croak "Missing parameter" unless @_;
  return info()->{$_[0]};
}

sub devs {
  croak "Missing parameters" unless @_;
  return @{info()}{@_};
}

# Autoload methods go after =cut, and are processed by the autosplit program.

1;
__END__
# Below is stub documentation for your module. You better edit it!

=head1 NAME

Linux::net::dev - Perl extension for parsing /proc/net/dev

=head1 SYNOPSIS

  use Linux::net::dev;
  my $devs = Linux::net::dev::info();
  print "Devices (bytes read):\n";
  foreach (keys %$devs) {
    print "  $_ ($devs->{$_}->{rbytes})\n";
  }

=head1 DESCRIPTION

B<Linux::net::dev> parses B</proc/net/dev> for network devices
statistics. The package contains these functions:

=over 4

=item B<info>

This function returns hash reference. Keys are devices' ids and values
are data hash references. Data hash has resource names for keys and
their respective values as associated values.

=item B<dev>

Takes device id as a single argument and returns that device's data.

=item B<devs>

Takes list of device ids as arguments and returns array with those
devices' data.

=back

Recognized resources (data hash keys): B<rbytes>, B<rcompressed>,
B<rdrop>, B<rerrs>, B<rfifo>, B<rframe>, B<rmulticast>, B<rpackets>,
B<tbytes>, B<tcarrier>, B<tcolls>, B<tcompressed>, B<tdrop>, B<terrs>,
B<tfifo>, B<tpackets>,

Resources begining with "r" are values for read data, and those begining
with "t" are values for transmited data.

Package was built and tested on RedHat 7.2, kernel 2.4.7-10 and might
not work on some other versions. Please report bugs along with your
kernel version (B<uname -r> or B<uname -a>).

=head1 FILES

B</proc/net/dev>

=head1 REQUIRES

Perl 5.000

=head1 SEE ALSO

perl(1).

=head1 AUTHOR

Vedran Sego, vsego@math.hr

=cut
