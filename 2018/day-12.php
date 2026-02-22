<?php

namespace AdventOfCode\Year2018;

/**
 * Day 12: Subterranean Sustainability
 */
class Day12 {
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
	 * Part 1: Sum of pot indices after 20 generations
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$initial_state = $this->data['initial'];
		$rules         = $this->data['rules'];

		// Initialize pots with plants
		$pots   = [];
		$length = strlen( $initial_state );
		for ( $i = 0; $i < $length; $i++ ) {
			if ( $initial_state[ $i ] === '#' ) {
				$pots[ $i ] = true;
			}
		}

		// Simulate 20 generations
		for ( $generation = 0; $generation < 20; $generation++ ) {
			$pots = $this->next_generation( $pots, $rules );
		}

		return $this->sum_pots( $pots );
	}

	/**
	 * Part 2: Sum of pot indices after 50 billion generations using pattern detection
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$initial_state = $this->data['initial'];
		$rules         = $this->data['rules'];
		$target_gen    = 50000000000;

		// Initialize pots with plants
		$pots   = [];
		$length = strlen( $initial_state );
		for ( $i = 0; $i < $length; $i++ ) {
			if ( $initial_state[ $i ] === '#' ) {
				$pots[ $i ] = true;
			}
		}

		// Track seen patterns
		$seen_patterns = [];

		// Simulate generations until pattern stabilization is detected
		$generation = 1;
		while ( true ) {
			$pots = $this->next_generation( $pots, $rules );

			$current_sum = $this->sum_pots( $pots );

			// Convert pots to string pattern to detect stabilization
			$pattern = $this->pots_to_string( $pots );

			// Check if we've seen this pattern before (pattern stabilized)
			if ( isset( $seen_patterns[ $pattern ] ) ) {
				$prev_gen            = $seen_patterns[ $pattern ]['gen'];
				$prev_sum_at_pattern = $seen_patterns[ $pattern ]['sum'];

				// Calculate increment per generation
				$gen_diff            = $generation - $prev_gen;
				$sum_diff_at_pattern = $current_sum - $prev_sum_at_pattern;
				$increment_per_gen   = $sum_diff_at_pattern / $gen_diff;

				// Calculate the remaining generations
				$remaining_gens = $target_gen - $generation;

				return (int)($current_sum + ($increment_per_gen * $remaining_gens));
			}

			// Store this pattern
			$seen_patterns[ $pattern ] = [
				'gen' => $generation,
				'sum' => $current_sum,
			];

			$generation++;
		}
	}

	/**
	 * Computes the next generation of pots.
	 *
	 * @param array $pots Current set of pot indices with plants (sparse representation).
	 * @param array $rules Array of patterns that produce plants.
	 *
	 * @return array Next generation's set of pot indices with plants.
	 */
	private function next_generation( array $pots, array $rules ): array {
		if ( empty( $pots ) ) {
			return [];
		}

		$min_index = min( array_keys( $pots ) );
		$max_index = max( array_keys( $pots ) );

		$next_pots = [];

		// Check all pots from min - 2 to max + 2 (need 2 pots on each side for pattern)
		for ( $i = $min_index - 2; $i <= $max_index + 2; $i++ ) {
			$pattern = '';
			for ( $offset = -2; $offset <= 2; $offset++ ) {
				$pos      = $i + $offset;
				$pattern .= isset( $pots[ $pos ] ) ? '#' : '.';
			}

			// Check if this pattern matches any rule
			if ( in_array( $pattern, $rules, true ) ) {
				$next_pots[ $i ] = true;
			}
		}

		return $next_pots;
	}

	/**
	 * Sums the indices of all pots containing plants.
	 *
	 * @param array $pots Set of pot indices with plants (sparse representation).
	 *
	 * @return int Sum of all pot indices.
	 */
	private function sum_pots( array $pots ): int {
		return array_sum( array_keys( $pots ) );
	}

	/**
	 * Converts pots to a string representation.
	 *
	 * @param array $pots Set of pot indices with plants 
	 *
	 * @return string Pattern string.
	 */
	private function pots_to_string( array $pots ): string {
		if ( empty( $pots ) ) {
			return '';
		}

		$min_index = min( array_keys( $pots ) );
		$max_index = max( array_keys( $pots ) );

		// Build string from min to max
		$pattern = '';
		for ( $i = $min_index; $i <= $max_index; $i++ ) {
			$pattern .= isset( $pots[ $i ] ) ? '#' : '.';
		}

		return $pattern;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-12-test.txt' : '/data/day-12.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		// Extract initial state from first line
		$initial_state = '';
		if ( preg_match( '/initial state: ([#.]+)/', $lines[0], $matches ) ) {
			$initial_state = $matches[1];
		}

		// Parse rules - only store rules that produce plants (#)
		$rules       = [];
		$lines_count = count( $lines );
		for ( $i = 1; $i < $lines_count; $i++ ) {
			$line = trim( $lines[ $i ] );
			if ( empty( $line ) ) {
				continue;
			}

			if ( preg_match( '/^([#.]{5}) => ([#.])$/', $line, $matches ) ) {
				$pattern = $matches[1];
				$result  = $matches[2];

				// Only store rules that produce plants
				if ( $result === '#' ) {
					$rules[] = $pattern;
				}
			}
		}

		return [
			'initial' => $initial_state,
			'rules'   => $rules,
		];
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
	$day12  = new Day12( $test, $part );
	$result = $day12->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 325,
			'real' => 1733,
		],
		2 => [
			'test' => 999999999374,
			'real' => 1000000000508,
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
