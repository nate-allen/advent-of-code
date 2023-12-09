<?php

/**
 * Day 09: Mirage Maintenance
 */
class Day09 {
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
	 * Part 1: Find the sum of the next values in each sequence.
	 *
	 * @return int The sum of extrapolated values.
	 */
	public function part_1(): int {
		$sum = 0;

		foreach ( $this->data as $values ) {
			$sum += $this->extrapolate( $values );
		}

		return $sum;
	}

	/**
	 * Part 2: Find the sum of the previous values in each sequence.
	 *
	 * @return int The sum of the previous values.
	 */
	public function part_2(): int {
		$sum = 0;

		foreach ( $this->data as $values ) {
			$sum += $this->extrapolate( array_reverse( $values ) );
		}

		return $sum;
	}

	/**
	 * Recursively extrapolates the next value in a sequence.
	 *
	 * @param array $values The input values.
	 *
	 * @return int The extrapolated next value.
	 */
	private function extrapolate( array $values ): int {
		// If the array of differences is all zeros, return 0.
		if ( count( array_unique( $values ) ) === 1 && $values[0] === 0 ) {
			return 0;
		}

		// Calculate the differences
		$differences = $this->calculate_differences( $values );

		// Return the last value plus the extrapolated value.
		return end( $values ) + $this->extrapolate( $differences );
	}

	/**
	 * Calculates the differences between consecutive elements of an array.
	 *
	 * @param array $array The input array.
	 *
	 * @return array An array of differences.
	 */
	private function calculate_differences( array $array ): array {
		$differences = [];

		for ( $i = 0; $i < count( $array ) - 1; $i ++ ) {
			$differences[] = $array[ $i + 1 ] - $array[ $i ];
		}

		return $differences;
	}

	private function parse_data( string $test, int $part ): void {
		$file = $test ? '/data/day-09-test.txt' : '/data/day-09.txt';
		$data = explode( PHP_EOL, file_get_contents( __DIR__ . $file ) );

		$this->data = array_map( function ( $line ) {
			return array_map( 'intval', explode( ' ', $line ) );
		}, $data );
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
	$start    = microtime( true );
	$day09    = new Day09( 1, $test );
	$result   = $day09->part_1();
	$end      = microtime( true );
	$expected = $test ? 114 : 1834108701;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day09    = new Day09( 2, $test );
	$result   = $day09->part_2();
	$end      = microtime( true );
	$expected = $test ? 0 : 993;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
