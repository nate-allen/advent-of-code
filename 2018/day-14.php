<?php

namespace AdventOfCode\Year2018;

ini_set( 'memory_limit', '1024M' );

/**
 * Day 14: Chocolate Charts
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
	 * Parsed data from the input file (number of recipes).
	 *
	 * @var int
	 */
	private int $data;

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
	 * Part 1: Find the scores of the ten recipes immediately after the input number of recipes.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$recipes = [3, 7];
		$elf1    = 0;
		$elf2    = 1;
		$target  = $this->data;

		// Continue until we have at least target + 10 recipes
		while ( count( $recipes ) < $target + 10 ) {
			// Calculate sum of current recipes
			$sum = $recipes[ $elf1 ] + $recipes[ $elf2 ];

			// Add new recipes based on sum
			if ( $sum >= 10 ) {
				// Add two digits: tens and ones
				$recipes[] = intval( $sum / 10 );
				$recipes[] = $sum % 10;
			} else {
				// Add single digit
				$recipes[] = $sum;
			}

			// Move elves forward
			$elf1 = ($elf1 + 1 + $recipes[$elf1]) % count($recipes);
			$elf2 = ($elf2 + 1 + $recipes[$elf2]) % count($recipes);
		}

		// Extract the 10 recipes starting at position target
		$result_recipes = array_slice($recipes, $target, 10);

		// Convert to integer
		return (int) implode('', $result_recipes);
	}

	/**
	 * Part 2: Find how many recipes appear on the scoreboard before the input sequence.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// Convert input to array of digits
		$target_digits = array_map('intval', str_split( (string) $this->data ));
		$target_len    = count($target_digits);

		$recipes = [3, 7];
		$elf1    = 0;
		$elf2    = 1;

		while (true) {
			// Calculate sum of current recipes
			$sum = $recipes[$elf1] + $recipes[$elf2];

			// Track if we added 2 recipes
			$added_two = false;

			// Add new recipes based on sum
			if ($sum >= 10) {
				// Add two digits: tens and ones
				$recipes[] = intval($sum / 10);
				$recipes[] = $sum % 10;
				$added_two = true;
			} else {
				// Add single digit
				$recipes[] = $sum;
			}

			$recipes_len = count($recipes);

			// Check if target sequence appears at the end
			if ($recipes_len >= $target_len) {
				// Check last N digits
				$last_n = array_slice($recipes, -$target_len);
				if ($last_n === $target_digits) {
					return $recipes_len - $target_len;
				}

				// If we added 2 recipes, also check last N+1 digits excluding the very last one
				if ($added_two && $recipes_len > $target_len) {
					$last_n_minus_one = array_slice($recipes, -$target_len - 1, $target_len);
					if ($last_n_minus_one === $target_digits) {
						return $recipes_len - $target_len - 1;
					}
				}
			}

			// Move elves forward
			$elf1 = ($elf1 + 1 + $recipes[$elf1]) % $recipes_len;
			$elf2 = ($elf2 + 1 + $recipes[$elf2]) % $recipes_len;
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return int
	 */
	private function parse_data( bool $test ): int {
		$file  = $test ? '/data/day-14-test.txt' : '/data/day-14.txt';
		$input = trim( file_get_contents( __DIR__ . $file ) );

		return (int) $input;
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
	$day14  = new Day14( $test, $part );
	$result = $day14->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 5941429882,
			'real' => 2157138126,
		],
		2 => [
			'test' => 86764,
			'real' => 20365081,
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
