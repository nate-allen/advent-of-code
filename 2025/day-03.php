<?php

namespace AdventOfCode\Year2025;

/**
 * Day 03: Lobby
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
	 * Part 1: Find the maximum joltage possible from each bank
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$total = 0;

		foreach ( $this->data as $bank ) {
			$max_joltage = 0;
			$length      = strlen( $bank );

			// Try all pairs of batteries
			for ( $i = 0; $i < $length - 1; $i++ ) {
				for ( $j = $i + 1; $j < $length; $j++ ) {
					$digit1  = (int) $bank[ $i ];
					$digit2  = (int) $bank[ $j ];
					$joltage = $digit1 * 10 + $digit2;

					if ( $joltage > $max_joltage ) {
						$max_joltage = $joltage;
					}
				}
			}

			$total += $max_joltage;
		}

		return $total;
	}

	/**
	 * Part 2: Find maximum joltage by turning on exactly 12 batteries per bank
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$total = 0;

		foreach ( $this->data as $bank ) {
			$length          = strlen( $bank );
			$batteries_keep  = 12;
			$drops_remaining = $length - $batteries_keep;
			$stack           = [];

			// Scan left-to-right and when we find a larger digit, remove smaller ones before it.
			// The largest digits will be in the most leftmost positions.
			for ( $i = 0; $i < $length; $i++ ) {
				$digit = $bank[ $i ];

				// Remove smaller batteries to the left if we find a larger one (and can still drop)
				while ( count( $stack ) > 0 && end( $stack ) < $digit && $drops_remaining > 0 ) {
					array_pop( $stack );
					$drops_remaining--;
				}

				$stack[] = $digit;
			}

			// If we still need to drop more, remove from the end
			while ( $drops_remaining > 0 ) {
				array_pop( $stack );
				$drops_remaining--;
			}

			// Take first 12 digits to form the joltage
			$joltage = (int) implode( '', array_slice( $stack, 0, $batteries_keep ) );
			$total  += $joltage;
		}

		return $total;
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
 * @param bool    $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day03  = new Day03( $test, $part );
	$result = $day03->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 357,
			'real' => 17166,
		],
		2 => [
			'test' => 3121910778619,
			'real' => 169077317650774,
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
