<?php

namespace AdventOfCode\Year2016;

/**
 * Day 10: Balance Bots
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

	/**
	 * The bot that compared the target values.
	 *
	 * @var int
	 */
	private int $target_bot = -1;

	/**
	 * Output bins.
	 *
	 * @var array
	 */
	private array $outputs = [];

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
		$this->simulate();
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
	 * Part 1: Find the bot that compares the target values.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->target_bot;
	}

	/**
	 * Part 2: Multiply values in outputs 0, 1, and 2.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->outputs[0] * $this->outputs[1] * $this->outputs[2];
	}

	/**
	 * Simulates the bots passing chips around.
	 */
	private function simulate(): void {
		$bots    = [];
		$rules   = $this->data['rules'];
		$target  = $this->is_test ? [ 2, 5 ] : [ 17, 61 ];

		foreach ( $this->data['values'] as [ $val, $bot ] ) {
			$bots[ $bot ][] = $val;
		}

		$changed = true;
		while ( $changed ) {
			$changed = false;

			foreach ( $rules as $bot_id => $rule ) {
				if ( ! isset( $bots[ $bot_id ] ) || count( $bots[ $bot_id ] ) < 2 ) {
					continue;
				}

				$changed = true;
				sort( $bots[ $bot_id ] );
				$low  = $bots[ $bot_id ][0];
				$high = $bots[ $bot_id ][1];
				$bots[ $bot_id ] = [];

				if ( $low === $target[0] && $high === $target[1] ) {
					$this->target_bot = $bot_id;
				}

				if ( $rule['low_type'] === 'bot' ) {
					$bots[ $rule['low_id'] ][] = $low;
				} else {
					$this->outputs[ $rule['low_id'] ] = $low;
				}

				if ( $rule['high_type'] === 'bot' ) {
					$bots[ $rule['high_id'] ][] = $high;
				} else {
					$this->outputs[ $rule['high_id'] ] = $high;
				}
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
		$file   = $test ? '/data/day-10-test.txt' : '/data/day-10.txt';
		$lines  = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$values = [];
		$rules  = [];

		foreach ( $lines as $line ) {
			if ( preg_match( '/value (\d+) goes to bot (\d+)/', $line, $m ) ) {
				$values[] = [ (int) $m[1], (int) $m[2] ];
			} elseif ( preg_match( '/bot (\d+) gives low to (bot|output) (\d+) and high to (bot|output) (\d+)/', $line, $m ) ) {
				$rules[ (int) $m[1] ] = [
					'low_type'  => $m[2],
					'low_id'    => (int) $m[3],
					'high_type' => $m[4],
					'high_id'   => (int) $m[5],
				];
			}
		}

		return [ 'values' => $values, 'rules' => $rules ];
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
			'test' => 2,
			'real' => 86,
		],
		2 => [
			'test' => 30,
			'real' => 22847,
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
