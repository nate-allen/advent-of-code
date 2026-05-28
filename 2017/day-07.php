<?php

namespace AdventOfCode\Year2017;

/**
 * Day 07: Recursive Circus
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
	private array $data;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return string|int
	 */
	public function run(): string|int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Find the name of the bottom program.
	 *
	 * @return string
	 */
	private function solve_part_1(): string {
		return $this->find_root();
	}

	/**
	 * Part 2: Find the correct weight to balance the tower.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$root = $this->find_root();

		return $this->find_corrected_weight( $root, 0 );
	}

	/**
	 * Calculates the total weight of a program and all its children.
	 *
	 * @param string $name The program name.
	 *
	 * @return integer
	 */
	private function total_weight( string $name ): int {
		$weight = $this->data[ $name ]['weight'];

		foreach ( $this->data[ $name ]['children'] as $child ) {
			$weight += $this->total_weight( $child );
		}

		return $weight;
	}

	/**
	 * Recursively finds the program with the wrong weight and returns its corrected weight.
	 *
	 * @param string  $name       The current program name.
	 * @param integer $correction The weight difference to correct.
	 *
	 * @return integer
	 */
	private function find_corrected_weight( string $name, int $correction ): int {
		$children = $this->data[ $name ]['children'];

		if ( empty( $children ) ) {
			return $this->data[ $name ]['weight'] + $correction;
		}

		$child_weights = [];
		foreach ( $children as $child ) {
			$child_weights[ $child ] = $this->total_weight( $child );
		}

		$weight_counts = array_count_values( $child_weights );

		if ( count( $weight_counts ) === 1 ) {
			return $this->data[ $name ]['weight'] + $correction;
		}

		$wrong_weight   = array_search( 1, $weight_counts );
		$correct_weight = array_search( max( $weight_counts ), $weight_counts );
		$odd_child      = array_search( $wrong_weight, $child_weights );
		$diff           = $correct_weight - $wrong_weight;

		return $this->find_corrected_weight( $odd_child, $diff );
	}

	/**
	 * Finds the root program (the one that is never a child of another).
	 *
	 * @return string
	 */
	private function find_root(): string {
		$all_children = [];

		foreach ( $this->data as $name => $info ) {
			foreach ( $info['children'] as $child ) {
				$all_children[ $child ] = true;
			}
		}

		foreach ( $this->data as $name => $info ) {
			if ( ! isset( $all_children[ $name ] ) ) {
				return $name;
			}
		}

		return '';
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file    = $test ? '/data/day-07-test.txt' : '/data/day-07.txt';
		$lines   = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$programs = [];

		foreach ( $lines as $line ) {
			preg_match( '/^(\w+)\s+\((\d+)\)(?:\s+->\s+(.+))?$/', $line, $matches );

			$name     = $matches[1];
			$weight   = (int) $matches[2];
			$children = isset( $matches[3] ) ? array_map( 'trim', explode( ',', $matches[3] ) ) : [];

			$programs[ $name ] = [
				'weight'   => $weight,
				'children' => $children,
			];
		}

		return $programs;
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
			'test' => 'tknk',
			'real' => 'svugo',
		],
		2 => [
			'test' => 60,
			'real' => 1152,
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
