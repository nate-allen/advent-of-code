<?php

namespace AdventOfCode\Year2020;

/**
 * Day 21: Allergen Assessment
 */
class Day21 {
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
	 * @return integer|string
	 */
	public function run(): int|string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Determine which ingredients cannot possibly contain any of the allergens in the list and count them.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		[ $allergens_map, $all_ingredients ] = $this->data;

		// Determine the "possible ingredients" for each allergen
		$possible = [];
		foreach ( $allergens_map as $allergen => $ingredient_lists ) {
			$possible[ $allergen ] = array_reduce(
				$ingredient_lists,
				fn( $acc, $item ) => array_intersect( $acc, $item ),
				$ingredient_lists[0]
			);
		}

		// Flatten all possible ingredients
		$all_possible_ingredients = array_reduce( $possible, 'array_merge', [] );

		// Ingredients that never appear in the "possible" list
		$impossible = array_diff( $all_ingredients, $all_possible_ingredients );

		// Count how many times those impossible ingredients occur in total
		return count(
			array_filter( $all_ingredients, fn( $ingredient ) => in_array( $ingredient, $impossible ) )
		);
	}

	/**
	 * Part 2: Determine the canonical dangerous ingredient list by sorting the allergens.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		[ $allergens_map, ] = $this->data;

		// Build the initial "possible" set for each allergen
		$possible = [];
		foreach ( $allergens_map as $allergen => $ingredient_lists ) {
			$possible[ $allergen ] = array_reduce(
				$ingredient_lists,
				fn( $acc, $item ) => array_intersect( $acc, $item ),
				$ingredient_lists[0]
			);
		}

		// Convert them to array values
		$allergens_map = array_map( fn( $p ) => array_values( $p ), $possible );

		// Resolve allergens to single ingredients
		$dangerous = [];
		while ( count( $allergens_map ) > 0 ) {
			$keys = array_keys( $allergens_map );

			if ( empty( $keys ) ) {
				break;
			}

			// The allergen with the fewest candidate ingredients
			$allergen = array_reduce(
				$keys,
				function ( $acc, $item ) use ( $allergens_map ) {
					return count( $allergens_map[ $item ] ) < count( $allergens_map[ $acc ] ) ? $item : $acc;
				},
				$keys[0]
			);

			$chosen_ingredients     = array_shift( $allergens_map[ $allergen ] );
			$dangerous[ $allergen ] = $chosen_ingredients;

			unset( $allergens_map[ $allergen ] );

			// Remove that ingredient from all other lists
			foreach ( array_keys( $allergens_map ) as $a ) {
				$allergens_map[ $a ] = array_diff( $allergens_map[ $a ], [ $chosen_ingredients ] );
			}
		}

		// Sort by allergen name
		ksort( $dangerous );

		return implode( ',', $dangerous );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-21-test.txt' : '/data/day-21.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$allergens_map   = [];
		$all_ingredients = [];

		foreach ( $lines as $line ) {
			$matches = [];

			if ( ! preg_match( '/^(.*) \(contains (.*)\)$/', $line, $matches ) ) {
				continue;
			}

			$ingredients   = explode( ' ', $matches[1] );
			$allergen_list = explode( ', ', $matches[2] );

			// Collect all ingredients
			$all_ingredients = array_merge( $all_ingredients, $ingredients );

			foreach ( $allergen_list as $a ) {
				if ( ! isset( $allergens_map[ $a ] ) ) {
					$allergens_map[ $a ] = [];
				}

				$allergens_map[ $a ][] = $ingredients;
			}
		}

		return [ $allergens_map, $all_ingredients ];
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
	$day21  = new Day21( $test, $part );
	$result = $day21->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 5,
			'real' => 2556,
		],
		2 => [
			'test' => 'mxmxvkd,sqjhc,fvjkl',
			'real' => 'vcckp,hjz,nhvprqb,jhtfzk,mgkhhc,qbgbmc,bzcrknb,zmh',
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
