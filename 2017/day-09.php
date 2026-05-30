<?php

namespace AdventOfCode\Year2017;

/**
 * Day 09: Stream Processing
 */
class Day09 {
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
	 * @var string
	 */
	private string $data;

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
	 * Part 1: Find the total score of all groups.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->process_stream()['score'];
	}

	/**
	 * Part 2: Count the non-canceled characters within garbage.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->process_stream()['garbage'];
	}

	/**
	 * Processes the stream, returning both the group score and garbage count.
	 *
	 * @return array{score: int, garbage: int}
	 */
	private function process_stream(): array {
		$stream  = $this->data;
		$score   = 0;
		$garbage = 0;
		$depth   = 0;
		$in_garbage = false;
		$len     = strlen( $stream );

		for ( $i = 0; $i < $len; $i++ ) {
			$c = $stream[ $i ];

			if ( $c === '!' ) {
				$i++;
			} elseif ( $in_garbage ) {
				if ( $c === '>' ) {
					$in_garbage = false;
				} else {
					$garbage++;
				}
			} elseif ( $c === '<' ) {
				$in_garbage = true;
			} elseif ( $c === '{' ) {
				$depth++;
			} elseif ( $c === '}' ) {
				$score += $depth;
				$depth--;
			}
		}

		return [ 'score' => $score, 'garbage' => $garbage ];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return string
	 */
	private function parse_data( bool $test ): string {
		$file = $test ? '/data/day-09-test.txt' : '/data/day-09.txt';

		return trim( file_get_contents( __DIR__ . $file ) );
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
	$day09  = new Day09( $test, $part );
	$result = $day09->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 3,
			'real' => 9662,
		],
		2 => [
			'test' => 17,
			'real' => 4903,
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
