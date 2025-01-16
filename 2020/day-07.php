<?php

namespace AdventOfCode\Year2020;

/**
 * Day 07: Handy Haversacks
 */
class Day07 {
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
	private array $rules;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->rules   = $this->parse_data( $this->is_test );
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
	 * Part 1: How many bag colors can eventually contain at least one shiny gold bag?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$can_contain = [];

		// Find all bags that can contain a shiny gold bag
		$can_contain = $this->find_bags( 'shiny gold', $this->rules, $can_contain );

		return count( $can_contain );
	}

	/**
	 * Part 2: How many individual bags are required inside your single shiny gold bag?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->count_bags( 'shiny gold', $this->rules );
	}

	/**
	 * Recursively counts the number of bags inside a specific color.
	 *
	 * @param string $color The color to count bags for.
	 * @param array  $rules The bag rules.
	 *
	 * @return integer
	 */
	private function count_bags( string $color, array $rules ): int {
		$count = 0;
		foreach ( $rules[ $color ] as $bag_color => $bag_count ) {
			$count += $bag_count + $bag_count * $this->count_bags( $bag_color, $rules );
		}

		return $count;
	}

	/**
	 * Recursively finds all bags that can contain a specific color.
	 *
	 * @param string $color       The color to search for.
	 * @param array  $rules       The bag rules.
	 * @param array  $can_contain The bags that can contain the specified color.
	 *
	 * @return array
	 */
	private function find_bags( string $color, array $rules, array &$can_contain ): array {
		foreach ( $rules as $bag_color => $contents ) {
			if ( isset( $contents[ $color ] ) && ! isset( $can_contain[ $bag_color ] ) ) {
				$can_contain[ $bag_color ] = true;
				$this->find_bags( $bag_color, $rules, $can_contain );
			}
		}

		return $can_contain;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data(bool $test): array {
		$file  = $test ? '/data/day-07-test.txt' : '/data/day-07.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$rules = [];
		foreach ( $lines as $line ) {
			$parts = explode( ' bags contain ', $line );
			$color = $parts[0];

			$rules[ $color ] = [];

			if ( $parts[1] === 'no other bags.' ) {
				continue;
			}

			$contents = explode( ', ', $parts[1] );
			foreach ( $contents as $content ) {
				preg_match( '/(\d+) (.+?) bags?/', $content, $matches );
				$rules[ $color ][ $matches[2] ] = (int) $matches[1];
			}
		}

		return $rules;
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
	$day07  = new Day07( $test, $part );
	$result = $day07->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 4,
			'real' => 261,
		],
		2 => [
			'test' => 32,
			'real' => 3765,
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
