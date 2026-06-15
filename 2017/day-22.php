<?php

namespace AdventOfCode\Year2017;

/**
 * Day 22: Sporifica Virus
 */
class Day22 {
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
	 * Set of initially infected nodes as "r,c" keys.
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
	 * Part 1: Count infections after 10000 bursts.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$infected   = $this->data['infected'];
		$r          = $this->data['start_r'];
		$c          = $this->data['start_c'];
		$dir        = 0; // 0=up, 1=right, 2=down, 3=left
		$dr         = [ -1, 0, 1, 0 ];
		$dc         = [ 0, 1, 0, -1 ];
		$infections = 0;

		for ( $i = 0; $i < 10000; $i++ ) {
			$key = "$r,$c";

			if ( isset( $infected[ $key ] ) ) {
				$dir = ( $dir + 1 ) % 4; // Turn right.
				unset( $infected[ $key ] );
			} else {
				$dir = ( $dir + 3 ) % 4; // Turn left.
				$infected[ $key ] = true;
				$infections++;
			}

			$r += $dr[ $dir ];
			$c += $dc[ $dir ];
		}

		return $infections;
	}

	/**
	 * Part 2: Evolved virus with 4 states, count infections after 10M bursts.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// States: 0=clean, 1=weakened, 2=infected, 3=flagged
		$nodes      = [];
		foreach ( $this->data['infected'] as $key => $v ) {
			$nodes[ $key ] = 2;
		}
		$r          = $this->data['start_r'];
		$c          = $this->data['start_c'];
		$dir        = 0; // 0=up, 1=right, 2=down, 3=left
		$dr         = [ -1, 0, 1, 0 ];
		$dc         = [ 0, 1, 0, -1 ];
		$infections = 0;

		for ( $i = 0; $i < 10000000; $i++ ) {
			$key   = "$r,$c";
			$state = $nodes[ $key ] ?? 0;

			// Turn based on state.
			switch ( $state ) {
				case 0: $dir = ( $dir + 3 ) % 4; break; // Clean: turn left.
				case 1: break;                           // Weakened: no turn.
				case 2: $dir = ( $dir + 1 ) % 4; break; // Infected: turn right.
				case 3: $dir = ( $dir + 2 ) % 4; break; // Flagged: reverse.
			}

			// Advance state.
			$new_state = ( $state + 1 ) % 4;
			if ( $new_state === 0 ) {
				unset( $nodes[ $key ] );
			} else {
				$nodes[ $key ] = $new_state;
			}

			if ( $new_state === 2 ) {
				$infections++;
			}

			$r += $dr[ $dir ];
			$c += $dc[ $dir ];
		}

		return $infections;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-22-test.txt' : '/data/day-22.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$infected = [];
		foreach ( $lines as $r => $line ) {
			for ( $c = 0; $c < strlen( $line ); $c++ ) {
				if ( $line[ $c ] === '#' ) {
					$infected["$r,$c"] = true;
				}
			}
		}

		return [
			'infected' => $infected,
			'start_r'  => intdiv( count( $lines ), 2 ),
			'start_c'  => intdiv( strlen( $lines[0] ), 2 ),
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
	$day22  = new Day22( $test, $part );
	$result = $day22->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 5587,
			'real' => 5450,
		],
		2 => [
			'test' => 2511944,
			'real' => 2511957,
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
