<?php
/**
 * Day 7: No Space Left On Device
 */

/**
 * Part 1: Find all the directories with a total size of at most 100000 and get the sum those directories.
 *
 * @return void
 */
function part_1() {
	$directory_totals = get_directory_totals();
	$size             = 0;

	// Loop through the directory totals and add the values to the total sum if it is no more than 100000
	foreach ( $directory_totals as $directory_total ) {
		if ( $directory_total <= 100000 ) {
			$size += $directory_total;
		}
	}

	printf( 'Answer: %s', $size . PHP_EOL );
}

/**
 * Part 2: Choose a directory to delete.
 *
 * @return void
 */
function part_2() {
	$directories      = get_directories();
	$directory_totals = get_directory_totals();

	$unused_size = 70000000 - array_sum( $directories['/'] );
	$needed_size = 30000000 - $unused_size;
	$answer      = 0;

	// Sort the directory totals in descending order
	sort( $directory_totals );

	// Return the first value that is greater than the needed size
	foreach ( $directory_totals as $directory_total ) {
		if ( $directory_total >= $needed_size ) {
			$answer = $directory_total;
			break;
		}
	}

	printf( 'Answer: %s', $answer . PHP_EOL );
}

/**
 * Returns the directories and their sizes.
 *
 * @return array
 */
function get_directories() {
	$data        = file_get_contents( __DIR__ . '/data/day-07.txt' );
	$lines       = explode( "\n", $data );
	$directories = array(
		'/' => array(),
	);
	$path        = '/';

	foreach ( $lines as $line ) {
		// Skip lines that are just "$ ls"
		if ( '$ ls' === $line ) {
			continue;
		}

		// If the line starts with a "$", handle the change directory command
		if ( '$' === $line[0] ) {
			// Extract the directory we're changing to
			$directory = explode( ' ', $line )[2];

			// Ignore the root directory
			if ( '/' === $directory ) {
				continue;
			}

			// If the directory we're changing to is "..", set the path to the parent directory
			// Otherwise, append the directory to the current path.
			if ( '..' === $directory ) {
				$path = dirname( $path );
			} else {
				$path .= "/{$directory}";
			}

			// If the current path is not in the directories array, add it.
			if ( ! isset( $directories[ $path ] ) ) {
				$directories[ $path ] = array();
			}
		} else {
			// The line contains a file or directory
			$file = explode( ' ', $line );

			// Skip directories. We only care about files.
			if ( 'dir' === $file[0] ) {
				continue;
			}

			// Add the file size to the directory and every parent directory.
			// This is done by looping over each directory in the directories array
			// and checking if the current path is a substring of the directory key.
			foreach ( $directories as $k => $d ) {
				if ( strpos( $path, $k ) !== false ) {
					$directories[ $k ][] = $file[0];
				}
			}
		}
	}

	return $directories;
}

/**
 * Returns the total size of each directory.
 *
 * @return array
 */
function get_directory_totals() {
	$directories = get_directories();
	$totals      = array();

	foreach ( $directories as $directory ) {
		$totals[] = array_sum( $directory );
	}

	return $totals;
}