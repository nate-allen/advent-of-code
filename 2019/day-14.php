<?php

namespace AdventOfCode\Year2019;

/**
 * Day 14: Space Stoichiometry
 */
class Day14 {
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
	 * Part 1: Determine the minimum amount of ORE required to produce exactly 1 FUEL.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$recipes    = $this->data;
		$surplus    = [];
		$ore_needed = $this->calculate_ore_required( 'FUEL', 1, $recipes, $surplus );

		return $ore_needed;
	}

	/**
	 * Part 2: Determine the maximum amount of FUEL that can be produced with 1 trillion ORE.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$recipes  = $this->data;
		$trillion = 1000000000000;

		// Find an upper bound for fuel production.
		$fuel_amount = 1;
		while ( true ) {
			$local_surplus = [];
			$ore_needed    = $this->calculate_ore_required( 'FUEL', $fuel_amount, $recipes, $local_surplus );

			if ( $ore_needed > $trillion ) {
				break;
			}

			$fuel_amount *= 2;
		}

		// Search between half of the upper bound and the upper bound.
		$low  = (int) ( $fuel_amount / 2 );
		$high = $fuel_amount;

		while ( $low < $high ) {
			$mid           = (int) floor( ( $low + $high + 1 ) / 2 );
			$local_surplus = [];
			$ore_needed    = $this->calculate_ore_required( 'FUEL', $mid, $recipes, $local_surplus );

			if ( $ore_needed <= $trillion ) {
				$low = $mid;
			} else {
				$high = $mid - 1;
			}
		}

		return $low;
	}

	/**
	 * Recursively calculates the amount of ORE needed for a given chemical and quantity.
	 *
	 * @param string $chemical The chemical to produce.
	 * @param int    $quantity The quantity of the chemical required.
	 * @param array  $recipes  The list of reaction recipes.
	 * @param array  $surplus  An array tracking any surplus chemicals.
	 *
	 * @return int
	 */
	private function calculate_ore_required( string $chemical, int $quantity, array $recipes, array &$surplus ): int {
		// ORE is the raw material.
		if ( $chemical === 'ORE' ) {
			return $quantity;
		}

		// Use any surplus we have for this chemical.
		if ( isset( $surplus[ $chemical ] ) && $surplus[ $chemical ] > 0 ) {
			$used                 = min( $quantity, $surplus[ $chemical ] );
			$quantity             -= $used;
			$surplus[ $chemical ] -= $used;
		}

		// If no more is needed, return 0 ORE.
		if ( $quantity === 0 ) {
			return 0;
		}

		// Look up the reaction for this chemical.
		$recipe     = $recipes[ $chemical ];
		$output_qty = $recipe['quantity'];

		// Calculate how many times the reaction must run.
		$times = (int) ceil( $quantity / $output_qty );

		// Calculate any surplus produced.
		$surplus[ $chemical ] = ( $surplus[ $chemical ] ?? 0 ) + ( $times * $output_qty - $quantity );

		$ore_needed = 0;

		// Recursively calculate the ore required for each ingredient.
		foreach ( $recipe['ingredients'] as $ingredient ) {
			$ore_needed += $this->calculate_ore_required(
				$ingredient['chemical'],
				$ingredient['quantity'] * $times,
				$recipes,
				$surplus
			);
		}

		return $ore_needed;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * Output format:
	 * [
	 *   'A' => [
	 *     'quantity'    => 10,
	 *     'ingredients' => [
	 *       0 => [
	 *         'chemical' => 'ORE',
	 *         'quantity' => 10
	 *       ]
	 *     ]
	 *   ],
	 *   // etc.
	 * ]
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file    = $test ? '/data/day-14-test.txt' : '/data/day-14.txt';
		$lines   = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$recipes = [];

		foreach ( $lines as $line ) {
			// Split the line into ingredients and result parts.
			list( $ingredients_part, $result_part ) = explode( "=>", $line );

			// Parse the result.
			preg_match( '/(\d+)\s+(\w+)/', trim( $result_part ), $result_matches );
			$result_quantity = (int) $result_matches[1];
			$result_chemical = $result_matches[2];

			// Parse the input ingredients.
			$ingredients = [];
			$parts       = explode( ",", $ingredients_part );

			foreach ( $parts as $part ) {
				preg_match( '/(\d+)\s+(\w+)/', trim( $part ), $matches );
				$ingredients[] = [
					'chemical' => $matches[2],
					'quantity' => (int) $matches[1]
				];
			}

			// Store the result in the recipes array.
			$recipes[ $result_chemical ] = [
				'quantity'    => $result_quantity,
				'ingredients' => $ingredients
			];
		}

		return $recipes;
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
	$day14  = new Day14( $test, $part );
	$result = $day14->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 2210736,
			'real' => 720484,
		],
		2 => [
			'test' => 460664,
			'real' => 1993284,
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
