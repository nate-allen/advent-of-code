<?php

namespace AdventOfCode\Year2021;

/**
 * Day 10: Syntax Scoring
 */
class Day10 {
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
	 * Part 1: Identify the first illegal character in each corrupted line and calculate the total syntax error score
	 *         using the given point values.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$points = [
			')' => 3,
			']' => 57,
			'}' => 1197,
			'>' => 25137,
		];

		$pairs = [
			'(' => ')',
			'[' => ']',
			'{' => '}',
			'<' => '>',
		];

		$total_score = 0;

		foreach ( $this->data as $line ) {
			$stack = [];

			foreach ( $line as $char ) {
				// Check if this is an opening character.
				if ( isset( $pairs[ $char ] ) ) {
					$stack[] = $char;
				} else {
					// It's a closing character. Check if it matches the top of the stack.
					$last = array_pop( $stack );
					if ( $pairs[ $last ] !== $char ) {
						// Corrupted line, add score for the illegal character.
						$total_score += $points[ $char ];
						break;
					}
				}
			}
		}

		return $total_score;
	}

	/**
	 * Part 2: Calculate the completion score for incomplete lines by determining the missing closing characters,
	 *         scoring the completion strings, and returning the middle score from the sorted list of scores.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$points = [
			')' => 1,
			']' => 2,
			'}' => 3,
			'>' => 4,
		];

		$pairs = [
			'(' => ')',
			'[' => ']',
			'{' => '}',
			'<' => '>',
		];

		$scores = [];

		foreach ( $this->data as $line ) {
			$stack = [];
			$corrupted = false;

			foreach ( $line as $char ) {
				if ( isset( $pairs[ $char ] ) ) {
					// Opening character, push to stack
					$stack[] = $char;
				} else {
					// Closing character, check if it matches the stack
					$last = array_pop( $stack );
					if ( $pairs[ $last ] !== $char ) {
						// Corrupted line, skip
						$corrupted = true;
						break;
					}
				}
			}

			if ( ! $corrupted ) {
				// Incomplete line: calculate the completion string
				$completion_score = 0;
				while ( $stack ) {
					$completion_char = $pairs[ array_pop( $stack ) ];
					$completion_score = $completion_score * 5 + $points[ $completion_char ];
				}
				$scores[] = $completion_score;
			}
		}

		// Sort scores and return the middle value
		sort( $scores );
		$middle_index = intdiv( count( $scores ), 2 );

		return $scores[ $middle_index ];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-10-test.txt' : '/data/day-10.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map('str_split', $lines);
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
	$day10  = new Day10( $test, $part );
	$result = $day10->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 26397,
			'real' => 321237,
		],
		2 => [
			'test' => 288957,
			'real' => 2360030859,
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
