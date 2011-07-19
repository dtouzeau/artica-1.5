#!/usr/bin/perl

my @m = get_memory_info();
if (@m && $m[0]) {
print "T=",$m[0]," U=",$m[0]-$m[1];
}
			

sub get_memory_info{
local %m;
if (open(BEAN, "/proc/user_beancounters")) {

	while(<BEAN>) {
		if (/^privvmpages\s+(\d+)\s+(\d+)\s+(\d+)/) {
			return ($3, $3-$1, undef, undef);
			}
		}
	close(BEAN);
	}
open(MEMINFO, "/proc/meminfo") || return ();
while(<MEMINFO>) {
	if (/^(\S+):\s+(\d+)/) {
		$m{lc($1)} = $2;
		}
	}
close(MEMINFO);
return ( $m{'memtotal'}, $m{'cached'} > $m{'memtotal'} ? $m{'memfree'}
				: $m{'memfree'}+$m{'buffers'}+$m{'cached'},
	 $m{'swaptotal'}, $m{'swapfree'} );
}

1;
