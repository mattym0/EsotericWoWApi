<?php 
/**
 * Convert a raw AP into an easily readable value
 */
function convertAp( $ap ) {
	$newApString = $ap;
	$apPrefixes = [ '', 'K', 'M', 'B', 'T' ];

	if( $ap < 1000 ) {
		return $ap;
	}

	$apPrefix = 0;
	do {
		$ap = $ap / 1000;
		$apPrefix++;
	} while ( $ap >= 1000 );

	$ap = number_format( $ap, 2 );
	$newApString = $ap . $apPrefixes[$apPrefix];
	
	return $newApString;
} ?>