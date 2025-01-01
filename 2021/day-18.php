<?php

namespace AdventOfCode\Year2021;

/**
 * Day 18: Snailfish
 */
class Day18 {
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
	 * Part 1: Find the magnitude of the final sum of snailfish numbers.
	 *
	 * This adds all snailfish numbers from the input, reduces them, and calculates the magnitude of the final sum.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		// Initialize the sum with the first snailfish number
		$sum = $this->data[0];

		// Loop through the rest of the numbers, adding and reducing them
		for ( $i = 1; $i < count( $this->data ); $i ++ ) {
			$sum = $this->reduce( $this->add( $sum, $this->data[ $i ] ) );
		}

		// Calculate and return the magnitude.
		return $this->magnitude( $sum );
	}

	/**
	 * Part 2: Find the largest magnitude achievable by adding any two snailfish numbers.
	 *
	 * This loops over all pairs of snailfish numbers, adds them in both orders, reduces the sums, and calculates their
	 * magnitudes to find the maximum.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$max = 0;

		// Compare every pair of snailfish numbers
		for ( $i = 0; $i < count( $this->data ); $i ++ ) {
			for ( $j = 0; $j < count( $this->data ); $j ++ ) {
				if ( $i !== $j ) {
					// Add and reduce the pair in both orders
					$sum       = $this->reduce( $this->add( $this->data[ $i ], $this->data[ $j ] ) );
					$magnitude = $this->magnitude( $sum );

					// Update the maximum magnitude if this one is larger
					$max = max( $max, $magnitude );
				}
			}
		}

		return $max;
	}

	/**
	 * Adds two snailfish numbers by creating a new pair.
	 *
	 * @param string $a The first snailfish number.
	 * @param string $b The second snailfish number.
	 *
	 * @return string
	 */
	private function add( string $a, string $b ): string {
		return "[{$a},{$b}]";
	}

	/**
	 * Reduces a snailfish number by repeatedly applying explode and split operations.
	 *
	 * @param string $number The snailfish number to reduce.
	 *
	 * @return string
	 */
	private function reduce( string $number ): string {
		do {
			// Try to explode the number
			$exploded = $this->explode( $number );

			if ( $exploded !== $number ) {
				$number = $exploded;
				continue;
			}

			// If no explosion, try to split the number
			$split = $this->split( $number );

			if ( $split !== $number ) {
				$number = $split;
				continue;
			}

			// Stop if no changes were made
			break;
		} while ( true );

		return $number;
	}

	/**
	 * Explodes the leftmost nested pair in a snailfish number.
	 *
	 * @param string $number The snailfish number to process.
	 *
	 * @return string
	 */
	private function explode( string $number ): string {
		$stack = $this->build_stack( $number );
		$depth = 0;

		// Traverse the stack to find a deeply nested pair
		for ( $i = 0; $i < count( $stack ); $i ++ ) {
			if ( $stack[ $i ] === '[' ) {
				$depth ++;
			} elseif ( $stack[ $i ] === ']' ) {
				$depth --;
			} elseif ( is_numeric( $stack[ $i ] ) && isset( $stack[ $i + 1 ] ) && $stack[ $i + 1 ] === ',' && is_numeric( $stack[ $i + 2 ] ) && $depth > 4 ) {
				$leftValue  = $stack[ $i ];
				$rightValue = $stack[ $i + 2 ];

				// Add the left value to the nearest number on the left
				for ( $j = $i - 1; $j >= 0; $j -- ) {
					if ( is_numeric( $stack[ $j ] ) ) {
						$stack[ $j ] += $leftValue;
						break;
					}
				}

				// Add the right value to the nearest number on the right
				for ( $j = $i + 3; $j < count( $stack ); $j ++ ) {
					if ( is_numeric( $stack[ $j ] ) ) {
						$stack[ $j ] += $rightValue;
						break;
					}
				}

				// Replace the exploded pair with 0
				array_splice( $stack, $i - 1, 5, [ 0 ] );

				return $this->collapse_stack( $stack );
			}
		}

		return $number;
	}

	/**
	 * Splits the leftmost number >= 10 into a pair of two numbers.
	 *
	 * @param string $number The snailfish number to process.
	 *
	 * @return string
	 */
	private function split( string $number ): string {
		$stack = $this->build_stack( $number );

		// Traverse the stack to find a number >= 10
		for ( $i = 0; $i < count( $stack ); $i ++ ) {
			if ( is_numeric( $stack[ $i ] ) && $stack[ $i ] >= 10 ) {
				$left  = floor( $stack[ $i ] / 2 );
				$right = ceil( $stack[ $i ] / 2 );

				// Replace the number with a new pair
				array_splice( $stack, $i, 1, [ '[', $left, ',', $right, ']' ] );

				return $this->collapse_stack( $stack );
			}
		}

		return $number;
	}

	/**
	 * Calculates the magnitude of a snailfish number.
	 *
	 * The magnitude of a pair is 3 times the magnitude of its left element plus 2 times the magnitude of its right element.
	 *
	 * @param string $number The snailfish number.
	 *
	 * @return integer
	 */
	private function magnitude( string $number ): int {
		// Replace pairs with their magnitudes until only a single number remains
		while ( preg_match( '/\[(\d+),(\d+)\]/', $number, $matches ) ) {
			$number = preg_replace(
				'/\[(\d+),(\d+)\]/',
				3 * $matches[1] + 2 * $matches[2],
				$number,
				1
			);
		}

		return (int) $number;
	}

	/**
	 * Converts a snailfish number into a stack of tokens.
	 *
	 * @param string $number The snailfish number.
	 *
	 * @return array
	 */
	private function build_stack( string $number ): array {
		// Split the snailfish number into individual tokens
		preg_match_all( '/\[|\]|,|\d+/', $number, $matches );

		return $matches[0];
	}

	/**
	 * Converts a stack back into a snailfish number string.
	 *
	 * @param array $stack The stack representation of the snailfish number.
	 *
	 * @return string
	 */
	private function collapse_stack( array $stack ): string {
		// Join the stack elements into a single string
		return implode( '', $stack );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-18-test.txt' : '/data/day-18.txt';
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
	$day18  = new Day18( $test, $part );
	$result = $day18->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 4140,
			'real' => 2541,
		],
		2 => [
			'test' => 3993,
			'real' => 4647,
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
