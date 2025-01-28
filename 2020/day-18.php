<?php

namespace AdventOfCode\Year2020;

/**
 * Day 18: Operation Order
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
	 * Part 1: Evaluate the expression on each line of the homework; what is the sum of the resulting values?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$sum = 0;

		foreach ( $this->data as $tokens ) {
			[ $value ] = $this->evaluate_left_to_right( $tokens, 0 );

			$sum += $value;
		}

		return $sum;
	}

	/**
	 * Part 2: Evaluate the expression on each line of the homework; what is the sum of the resulting values?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$sum = 0;

		foreach ( $this->data as $tokens ) {
			[ $value ] = $this->evaluate_advanced_expression( $tokens, 0 );

			$sum += $value;
		}

		return $sum;
	}

	/**
	 * Evaluate a term in the expression (number or parenthesized sub-expression).
	 *
	 * @param array $tokens The tokenized expression.
	 * @param int $index The current index in the tokens.
	 *
	 * @return array
	 */
	private function evaluate_term( array $tokens, int $index ) {
		if ( $tokens[ $index ] === '(' ) {
			// Evaluate the sub-expression inside the parentheses.
			list( $value, $next_index ) = $this->evaluate_advanced_expression( $tokens, $index + 1 );

			return [ $value, $next_index + 1 ];
		} else {
			// Return the integer value of the current token.
			return [ (int) $tokens[ $index ], $index + 1 ];
		}
	}

	/**
	 * Evaluate an expression with addition having higher precedence than multiplication.
	 *
	 * @param array $tokens The tokenized expression.
	 * @param int $index The current index in the tokens.
	 *
	 * @return array
	 */
	private function evaluate_addition_first( array $tokens, int $index ): array {
		// Start by evaluating the first term.
		[ $value, $next_index ] = $this->evaluate_term( $tokens, $index );
		$result = $value;

		while ( true ) {
			// Stop if the end of tokens, a closing parenthesis, or a multiplication operator is reached.
			if ( $next_index === count( $tokens ) || $tokens[ $next_index ] === ')' || $tokens[ $next_index ] === '*' ) {
				return [ $result, $next_index ];
			} else {
				// Evaluate addition.
				list( $next_value, $next_next_index ) = $this->evaluate_term( $tokens, $next_index + 1 );
				$result     += $next_value;
				$next_index = $next_next_index;
			}
		}
	}

	/**
	 * Evaluate an expression with addition evaluated before multiplication.
	 *
	 * @param array $tokens The tokenized expression.
	 * @param int $index The current index in the tokens.
	 *
	 * @return array
	 */
	private function evaluate_advanced_expression( array $tokens, int $index ): array {
		// Start by evaluating addition-precedence expressions.
		[ $value, $next_index ] = $this->evaluate_addition_first( $tokens, $index );
		$result = $value;

		while ( true ) {
			// Stop if the end of tokens or a closing parenthesis is reached.
			if ( $next_index === count( $tokens ) || $tokens[ $next_index ] === ')' ) {
				return [ $result, $next_index ];
			} else {
				// Evaluate multiplication.
				list( $next_value, $next_next_index ) = $this->evaluate_addition_first( $tokens, $next_index + 1 );
				$result     *= $next_value;
				$next_index = $next_next_index;
			}
		}
	}

	/**
	 * Evaluate a simple expression with left-to-right precedence.
	 *
	 * @param array $tokens The tokenized expression.
	 * @param int $index The current index in the tokens.
	 *
	 * @return array
	 */
	private function evaluate_simple_term( array $tokens, int $index ): array {
		if ( $tokens[ $index ] === '(' ) {
			// Evaluate the sub-expression inside the parentheses.
			[ $value, $next_index ] = $this->evaluate_left_to_right( $tokens, $index + 1 );

			return [ $value, $next_index + 1 ];
		} else {
			// Return the integer value of the current token.
			return [ (int) $tokens[ $index ], $index + 1 ];
		}
	}

	/**
	 * Evaluate an expression with left-to-right operator precedence.
	 *
	 * @param array $tokens The tokenized expression.
	 * @param int $index The current index in the tokens.
	 *
	 * @return array
	 */
	private function evaluate_left_to_right( array $tokens, int $index ): array {
		// Start by evaluating the first term.
		[ $value, $next_index ] = $this->evaluate_simple_term( $tokens, $index );

		// Initialize the result with the first term.
		$result = $value;

		while ( true ) {
			// Stop if the end of tokens or a closing parenthesis is reached.
			if ( $next_index === count( $tokens ) || $tokens[ $next_index ] === ')' ) {
				return [ $result, $next_index ];
			} else {
				// Evaluate addition or multiplication.
				$operator = $tokens[ $next_index ];
				list( $next_value, $next_next_index ) = $this->evaluate_simple_term( $tokens, $next_index + 1 );

				if ( $operator === '+' ) {
					$result += $next_value;
				} else {
					$result *= $next_value;
				}
				$next_index = $next_next_index;
			}
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file    = $test ? '/data/day-18-test.txt' : '/data/day-18.txt';
		$lines  = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$tokens = [];

		foreach ( $lines as $key => $line ) {
			$lines[ $key ] = str_replace( [ '(', ')' ], [ '( ', ' )' ], $line );
			$tokens[]        = explode( ' ', $lines[ $key ] );
		}

		return $tokens;
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
	$day18  = new Day18( $test, $part );
	$result = $day18->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 26335,
			'real' => 11297104473091,
		],
		2 => [
			'test' => 693891,
			'real' => 185348874183674,
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
