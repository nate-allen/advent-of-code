<?php

namespace AdventOfCode\Year2024;

/**
 * Day 22: Monkey Market
 */
class Day22 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
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
	 * @return int
	 */
	public function run(): int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Simulate secret number evolution for each buyer and find the sum of the 2000th secret number.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$total = 0;

		// Iterate over each buyer's initial secret number.
		foreach ( $this->data as $initial_secret ) {
			$current_secret = (int) $initial_secret;

			// Simulate the evolution of the secret number 2000 times.
			for ( $iteration = 0; $iteration < 2000; $iteration ++ ) {
				// Step 1: Multiply by 64, mix, prune.
				$current_secret = ( ( $current_secret * 64 ) ^ $current_secret ) % 16777216;
				// Step 2: Divide by 32, mix, prune.
				$current_secret = ( intdiv( $current_secret, 32 ) ^ $current_secret ) % 16777216;
				// Step 3: Multiply by 2048, mix, prune.
				$current_secret = ( ( $current_secret * 2048 ) ^ $current_secret ) % 16777216;
			}

			// Add the 2000th secret number to the total sum.
			$total += $current_secret;
		}

		return $total;
	}

	/**
	 * Part 2: Determine the most profitable sequence of price changes and calculate the maximum bananas obtained.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$price_patterns = [];

		// Iterate over each buyer's initial secret number.
		foreach ( $this->data as $initialSecret ) {
			$current_secret = (int) $initialSecret;
			$last_price     = $current_secret % 10; // Get the initial price.
			$differences    = [];

			// Generate 2000 price differences based on secret number evolution.
			for ( $iteration = 0; $iteration < 2000; $iteration ++ ) {
				// Step 1: Multiply by 64, mix, prune.
				$current_secret = ( ( $current_secret * 64 ) ^ $current_secret ) % 16777216;
				// Step 2: Divide by 32, mix, prune.
				$current_secret = ( intdiv( $current_secret, 32 ) ^ $current_secret ) % 16777216;
				// Step 3: Multiply by 2048, mix, prune.
				$current_secret = ( ( $current_secret * 2048 ) ^ $current_secret ) % 16777216;

				$current_price = $current_secret % 10;
				$differences[] = [ 'difference' => $current_price - $last_price, 'price' => $current_price ];
				$last_price    = $current_price;
			}

			$unique_patterns = [];

			// Look at groups of four price changes in a row.
			for ( $i = 0; $i < count( $differences ) - 3; $i ++ ) {
				$sequence    = array_column( array_slice( $differences, $i, 4 ), 'difference' );
				$final_price = $differences[ $i + 3 ]['price'];
				$key         = implode( ',', $sequence );

				// Keep track of new patterns and add up their prices.
				if ( ! in_array( $key, $unique_patterns, true ) ) {
					$unique_patterns[] = $key;

					if ( ! isset( $price_patterns[ $key ] ) ) {
						$price_patterns[ $key ] = $final_price;
					} else {
						$price_patterns[ $key ] += $final_price;
					}
				}
			}
		}

		// Return the maximum price
		return max( $price_patterns );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-22-test.txt' : '/data/day-22.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return $lines;
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param int $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day22  = new Day22( $test, $part );
	$result = $day22->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 37327623,
			'real' => 14622549304,
		],
		2 => [
			'test' => 24,
			'real' => 1735,
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
