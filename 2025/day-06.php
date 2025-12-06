<?php

namespace AdventOfCode\Year2025;

/**
 * Day 06: Trash Compactor
 */
class Day06 {
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
	 * Formatted as:
	 * [
	 *   'grid' => [
	 *     ['1', '2', '3'],
	 *     ['4', '5', '6'],
	 *   ],
	 *   'operators' => ['*', '+'],
	 * ]
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
	 * Part 1: Calculate grand total by applying operators to each column
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$grid      = $this->data['grid'];
		$operators = $this->data['operators'];
		$num_rows  = count( $grid );
		$total     = 0;

		foreach ( $operators as $problem_idx => $operator ) {
			$numbers = [];

			for ( $row = 0; $row < $num_rows; $row++ ) {
				$row_str         = implode( '', $grid[ $row ] );
				$row_numbers     = preg_split( '/\s+/', trim( $row_str ) );
				$numbers[ $row ] = (int) $row_numbers[ $problem_idx ];
			}

			if ( $operator === '*' ) {
				$total += array_product( $numbers );
			} else {
				$total += array_sum( $numbers );
			}
		}

		return $total;
	}

	/**
	 * Part 2: Calculate grand total using cephalopod math (right-to-left columns)
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$grid          = $this->data['grid'];
		$operators     = array_reverse( $this->data['operators'] );
		$total         = 0;
		$column_digits = [];

		// Reverse each row to process right-to-left
		foreach ( $grid as $row ) {
			$reversed_row = strrev( implode( '', $row ) );
			$chars        = str_split( $reversed_row );

			foreach ( $chars as $col_idx => $char ) {
				if ( isset( $column_digits[ $col_idx ] ) ) {
					$column_digits[ $col_idx ] = trim( $column_digits[ $col_idx ] . $char );
				} else {
					$column_digits[ $col_idx ] = $char;
				}
			}
		}

		$problems    = [];
		$problem_idx = 0;

		foreach ( $column_digits as $digit_str ) {
			if ( is_numeric( $digit_str ) ) {
				$problems[ $problem_idx ][] = (int) $digit_str;
			} else {
				// Blank column separator... move on to next problem
				$problem_idx++;
			}
		}

		foreach ( $problems as $idx => $numbers ) {
			if ( isset( $operators[ $idx ] ) ) {
				if ( $operators[ $idx ] === '*' ) {
					$total += array_product( $numbers );
				} else {
					$total += array_sum( $numbers );
				}
			}
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
		$file  = $test ? '/data/day-06-test.txt' : '/data/day-06.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$operator_line = array_pop( $lines );
		$operators     = preg_split( '/\s+/', trim( $operator_line ) );

		$max_length = strlen( $operator_line );
		foreach ( $lines as $line ) {
			$max_length = max( $max_length, strlen( $line ) );
		}

		$grid = [];
		foreach ( $lines as $line ) {
			$grid[] = str_split( str_pad( $line, $max_length, ' ' ) );
		}

		return [
			'grid'      => $grid,
			'operators' => $operators,
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
	$day06  = new Day06( $test, $part );
	$result = $day06->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 4277556,
			'real' => 5060053676136,
		],
		2 => [
			'test' => 3263827,
			'real' => 9695042567249,
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
