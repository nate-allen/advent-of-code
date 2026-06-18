<?php

namespace AdventOfCode\Year2017;

/**
 * Day 25: The Halting Problem
 */
class Day25 {
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

	public function __construct( bool $test ) {
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Run the Turing machine and return the diagnostic checksum.
	 *
	 * @return integer
	 */
	public function run(): int {
		$state  = $this->data['start'];
		$steps  = $this->data['steps'];
		$rules  = $this->data['rules'];
		$tape   = [];
		$cursor = 0;

		for ( $i = 0; $i < $steps; $i++ ) {
			$val  = $tape[ $cursor ] ?? 0;
			$rule = $rules[ $state ][ $val ];

			$tape[ $cursor ] = $rule['write'];
			$cursor         += $rule['move'];
			$state           = $rule['next'];
		}

		return array_sum( $tape );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-25-test.txt' : '/data/day-25.txt';
		$text = file_get_contents( __DIR__ . $file );

		// Parse start state.
		preg_match( '/Begin in state (\w)/', $text, $m );
		$start = $m[1];

		// Parse steps.
		preg_match( '/after (\d+) steps/', $text, $m );
		$steps = (int) $m[1];

		// Parse state rules.
		$rules    = [];
		$sections = preg_split( '/In state (\w):/', $text, -1, PREG_SPLIT_DELIM_CAPTURE );

		// sections: [preamble, stateA, bodyA, stateB, bodyB, ...]
		for ( $i = 1; $i < count( $sections ); $i += 2 ) {
			$state_name = $sections[ $i ];
			$body       = $sections[ $i + 1 ];

			preg_match_all( '/If the current value is (\d):\s*- Write the value (\d)\.\s*- Move one slot to the (\w+)\.\s*- Continue with state (\w)\./', $body, $matches, PREG_SET_ORDER );

			foreach ( $matches as $match ) {
				$rules[ $state_name ][ (int) $match[1] ] = [
					'write' => (int) $match[2],
					'move'  => $match[3] === 'right' ? 1 : -1,
					'next'  => $match[4],
				];
			}
		}

		return [
			'start' => $start,
			'steps' => $steps,
			'rules' => $rules,
		];
	}
}

/**
 * Runs the puzzle and outputs results.
 *
 * @param bool $test Whether to use test data.
 */
function run_part( bool $test ): void {
	$start  = microtime( true );
	$day25  = new Day25( $test );
	$result = $day25->run();
	$end    = microtime( true );

	$expected_values = [
		'test' => 3,
		'real' => 2474,
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values['test'] : $expected_values['real'] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for test mode
while ( true ) {
	$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
	if ( in_array( $test, [ 'y', 'n' ], true ) ) {
		run_part( $test === 'y' );
		break;
	}
	echo 'Invalid input. Please enter y or n.' . PHP_EOL;
}
