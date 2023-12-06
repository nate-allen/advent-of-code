<?php

/**
 * Day 06: Wait For It
 */
class Day06 {
	/**
	 * The raw puzzle data.
	 *
	 * @var array
	 */
	private array $data;

	public function __construct( $part, $test ) {
		$this->parse_data( $test, $part );
	}

	/**
	 * Part 1: Determine the number of ways you can beat the record in each race and multiply them together.
	 *
	 * @return int The answer.
	 */
	public function part_1(): int {
		$total_ways = 1;

		foreach ( $this->data['time'] as $index => $time ) {
			$total_ways *= $this->calculate_ways_to_win( $time, $this->data['distance'][ $index ] );
		}

		return $total_ways;
	}

	/**
	 * Part 2: How many ways can you beat the record in one much longer race?
	 *
	 * @return int The answer.
	 */
	public function part_2(): int {
		return $this->calculate_ways_to_win( $this->data['time'], $this->data['distance'] );
	}

	/**
	 * Calculates the number of ways to win a single race.
	 *
	 * @param int $time   The total time of the race.
	 * @param int $record The record distance to beat.
	 *
	 * @return int The number of ways to win the race.
	 */
	private function calculate_ways_to_win( int $time, int $record ): int {
		$ways = 0;

		// Iterate up to the midpoint of the total race time
		for ( $hold = 0; $hold <= intdiv( $time, 2 ); $hold ++ ) {
			if ( $hold * ( $time - $hold ) > $record ) {
				$ways ++;
			}
		}

		// Double the count for the second half of the bell curve
		// adjust for even time by subtracting 1 if necessary
		return $time % 2 === 0 ? $ways * 2 - 1 : $ways * 2;
	}

	/**
	 * Parses the puzzle data from a file.
	 *
	 * @param string $test Is this the test data or real data
	 */
	private function parse_data( string $test, int $part ): void {
		$file       = $test ? '/data/day-06-test.txt' : '/data/day-06.txt';
		$lines      = explode( PHP_EOL, file_get_contents( __DIR__ . $file ) );
		$this->data = [
			'time'     => $this->parse_line( $lines[0], $part ),
			'distance' => $this->parse_line( $lines[1], $part ),
		];
	}

	/**
	 * Parses a line of data into an array of integers.
	 *
	 * @param string $line The line of data.
	 * @param int $part The part of the puzzle.
	 *
	 * @return array The parsed data.
	 */
	private function parse_line( string $line, int $part ) {
		if ( 1 === $part ) {
			$line = array_values( array_map( 'intval', array_filter( explode( ' ', preg_replace( '/[^0-9 ]/', '', $line ) ) ) ) );
		} else {
			$line = intval( preg_replace( '/[^0-9]/', '', $line ) );
		}

		return $line;
	}
}

// Prompt which part to run and if it should use the test data.
while ( true ) {
	$part = trim( readline( 'Which part do you want to run? (1/2)' ) );
	if ( function_exists( "part_{$part}" ) ) {
		while ( true ) {
			$test = trim( strtolower( readline( 'Do you want to run the test? (y/n)' ) ) );
			if ( in_array( $test, array( 'y', 'n' ), true ) ) {
				$test = 'y' === $test;
				call_user_func( "part_{$part}", $test );
				break;
			}
			echo 'Please enter y or n' . PHP_EOL;
		}
		break;
	}
	echo 'Please enter 1 or 2' . PHP_EOL;
}

function part_1( $test = false ) {
	$start  = microtime( true );
	$day06  = new Day06( 1, $test );
	$result = $day06->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start = microtime( true );
	$day06 = new Day06( 2, $test );

	$result = $day06->part_2();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
