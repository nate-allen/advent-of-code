<?php

namespace AdventOfCode\Year2021;

/**
 * Day 03: Binary Diagnostic
 */
class Day03 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var integer
	 */
	private int $part;

	/**
	 * Whether to use the test data.
	 *
	 * @var bool
	 */
	private bool $is_test;

	/**
	 * Parsed data from the input file.
	 *
	 * @var array
	 */
	private array $data;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return integer
	 */
	public function run(): int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Find the most and least common bits in each position of binary numbers, then multiply the resulting values
	 * in decimal to get the power consumption.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$totals = [];

		foreach ( $this->data as $line ) {
			$binary = str_split( $line );
			foreach ( $binary as $i => $bit ) {
				$totals[ $i ][ $bit ] = ( $totals[ $i ][ $bit ] ?? 0 ) + 1;
			}
		}

		$gamma_rate   = [];
		$epsilon_rate = [];

		foreach ( $totals as $position => $counts ) {
			$gamma_rate[ $position ]   = ( $counts[1] >= $counts[0] ) ? 1 : 0;
			$epsilon_rate[ $position ] = ( $counts[1] >= $counts[0] ) ? 0 : 1;
		}

		return bindec( implode( '', $gamma_rate ) ) * bindec( implode( '', $epsilon_rate ) );
	}

	/**
	 * Part 2: Filter the diagnostic report iteratively to find the oxygen generator rating (using the most common bit
	 * criteria) and the CO2 scrubber rating (using the least common bit criteria). Multiply these two ratings,
	 * converted to decimal, to calculate the submarine's life support rating.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// Calculate the oxygen generator rating
		$oxygenRating = $this->get_rating( $this->data, function ( $zeros, $ones ) {
			return $ones >= $zeros ? '1' : '0'; // Most common bit
		} );

		// Calculate the CO2 scrubber rating
		$co2Rating = $this->get_rating( $this->data, function ( $zeros, $ones ) {
			return $zeros <= $ones ? '0' : '1'; // Least common bit
		} );

		// Convert ratings to decimal
		$oxygenDecimal = bindec( $oxygenRating );
		$co2Decimal    = bindec( $co2Rating );

		// Calculate life support rating
		return $oxygenDecimal * $co2Decimal;
	}

	/**
	 * Filters a list of binary numbers iteratively to find a rating based on a specified criteria.
	 *
	 * This function processes the diagnostic report by iterating through each bit position
	 * and filtering numbers based on the provided criteria, which determines whether to keep
	 * numbers with the most or least common bit in each position. The filtering continues
	 * until only one binary number remains.
	 *
	 * @param array    $report   An array of binary strings representing the diagnostic report.
	 * @param callable $criteria A callback function that determines the target bit based on the counts of 0s and 1s.
	 *
	 * @return string
	 */
	function get_rating( array $report, callable $criteria ): string {
		$filtered = $report;

		for ( $position = 0; $position < strlen( $report[0] ); $position ++ ) {
			if ( count( $filtered ) === 1 ) {
				break; // Stop if only one number remains
			}

			$count = [ '0' => 0, '1' => 0 ];
			foreach ( $filtered as $binary ) {
				$bit = $binary[ $position ];
				$count[ $bit ] ++;
			}

			// Determine the target bit based on the criteria
			$target   = $criteria( $count['0'], $count['1'] );
			$filtered = array_filter( $filtered, fn( $binary ) => $binary[ $position ] === $target );
		}

		return array_pop( $filtered );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-03-test.txt' : '/data/day-03.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return $lines;
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day03  = new Day03( $test, $part );
	$result = $day03->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 198,
			'real' => 2498354,
		],
		2 => [
			'test' => 230,
			'real' => 3277956,
		],
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values[ $part ]['test'] : $expected_values[ $part ]['real'] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for part and test mode
while ( true ) {
	$part = (int) trim( readline( 'Which part do you want to run? (1/2): ' ) );
	if ( ! in_array( $part, [ 1, 2 ], true ) ) {
		echo 'Invalid part. Please enter 1 or 2.' . PHP_EOL;
		continue;
	}

	while ( true ) {
		$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
		if ( in_array( $test, [ 'y', 'n' ], true ) ) {
			$test_mode = $test === 'y';
			run_part( $part, $test_mode );
			break 2;
		}
		echo 'Invalid input. Please enter y or n.' . PHP_EOL;
	}
}
