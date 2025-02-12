<?php

namespace AdventOfCode\Year2019;

/**
 * Day 07: Amplification Circuit
 */
class Day07 {
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
	 * Part 1: Find the largest output signal from the amplifier circuit with the given phase settings.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$max_signal     = 0;
		$phase_settings = [ 0, 1, 2, 3, 4 ];
		$permutations   = $this->get_permutations( $phase_settings );

		// Try every permutation of phase settings.
		foreach ( $permutations as $sequence ) {
			$input_signal = 0;
			// Process the five amplifiers in series.
			foreach ( $sequence as $phase ) {
				// Each amplifier gets its own copy of the program.
				$program = $this->data;
				// Provide the phase setting (first input) and the input signal (second input).
				$inputs  = [ $phase, $input_signal ];
				$outputs = $this->run_program( $program, $inputs );
				// The amplifier's output becomes the next amplifier's input.
				$input_signal = $outputs[0];
			}
			// Track the maximum output signal.
			if ( $input_signal > $max_signal ) {
				$max_signal = $input_signal;
			}
		}

		return $max_signal;
	}

	/**
	 * Part 2: Using the amplifier feedback loop, run all five amplifiers concurrently until they halt,
	 *         then return the highest output signal.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$max_signal     = 0;
		$phase_settings = [ 5, 6, 7, 8, 9 ];
		$permutations   = $this->get_permutations( $phase_settings );

		// Try every permutation of phase settings.
		foreach ( $permutations as $sequence ) {
			// Create an array of amplifier generators.
			$amps = [];
			foreach ( $sequence as $phase ) {
				$amp = $this->run_program_generator( $this->data );
				// Prime the generator.
				$amp->rewind();
				// Send the phase setting as the first input.
				$amp->send( $phase );
				$amps[] = $amp;
			}

			$input_signal = 0;
			$last_output  = 0;
			$current_amp  = 0;

			// Continue the feedback loop until amplifier E (index 4) halts.
			while ( true ) {
				// If the current amplifier has halted, break out.
				if ( ! $amps[ $current_amp ]->valid() ) {
					break;
				}

				// Resume the amplifier by sending it the input signal.
				$output = $amps[ $current_amp ]->send( $input_signal );

				// If an output was produced, update the input for the next amp.
				if ( $output !== null ) {
					$input_signal = $output;
					if ( $current_amp === 4 ) {
						$last_output = $output;
					}
				}

				$current_amp = ( $current_amp + 1 ) % count( $amps );
			}

			// Track the maximum output signal.
			if ( $last_output > $max_signal ) {
				$max_signal = $last_output;
			}
		}

		return $max_signal;
	}

	/**
	 * Runs the provided Intcode program with the given inputs.
	 *
	 * @param array $program The Intcode program.
	 * @param array $inputs The list of input values.
	 *
	 * @return array
	 */
	private function run_program( array $program, array $inputs ): array {
		$output  = [];
		$pointer = 0; // Instruction pointer.
		$index   = 0;

		while ( true ) {
			$instruction = $program[ $pointer ];
			$opcode      = $instruction % 100;
			$mode1       = intdiv( $instruction, 100 ) % 10;
			$mode2       = intdiv( $instruction, 1000 ) % 10;

			switch ( $opcode ) {
				case 1:
					$param1           = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$param2           = ( $mode2 === 1 ) ? $program[ $pointer + 2 ] : $program[ $program[ $pointer + 2 ] ];
					$dest             = $program[ $pointer + 3 ];
					$program[ $dest ] = $param1 + $param2;
					$pointer          += 4;
					break;
				case 2:
					$param1           = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$param2           = ( $mode2 === 1 ) ? $program[ $pointer + 2 ] : $program[ $program[ $pointer + 2 ] ];
					$dest             = $program[ $pointer + 3 ];
					$program[ $dest ] = $param1 * $param2;
					$pointer          += 4;
					break;
				case 3:
					$dest             = $program[ $pointer + 1 ];
					$program[ $dest ] = $inputs[ $index ++ ];
					$pointer          += 2;
					break;
				case 4:
					$param1   = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$output[] = $param1;
					$pointer  += 2;
					break;
				case 5:
					$param1 = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$param2 = ( $mode2 === 1 ) ? $program[ $pointer + 2 ] : $program[ $program[ $pointer + 2 ] ];
					if ( $param1 !== 0 ) {
						$pointer = $param2;
					} else {
						$pointer += 3;
					}
					break;
				case 6:
					$param1 = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$param2 = ( $mode2 === 1 ) ? $program[ $pointer + 2 ] : $program[ $program[ $pointer + 2 ] ];
					if ( $param1 === 0 ) {
						$pointer = $param2;
					} else {
						$pointer += 3;
					}
					break;
				case 7:
					$param1           = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$param2           = ( $mode2 === 1 ) ? $program[ $pointer + 2 ] : $program[ $program[ $pointer + 2 ] ];
					$dest             = $program[ $pointer + 3 ];
					$program[ $dest ] = ( $param1 < $param2 ) ? 1 : 0;
					$pointer          += 4;
					break;
				case 8:
					$param1           = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$param2           = ( $mode2 === 1 ) ? $program[ $pointer + 2 ] : $program[ $program[ $pointer + 2 ] ];
					$dest             = $program[ $pointer + 3 ];
					$program[ $dest ] = ( $param1 === $param2 ) ? 1 : 0;
					$pointer          += 4;
					break;
				case 99:
					return $output;
			}
		}
	}

	/**
	 * Converts the Intcode program into a generator that suspends on input instructions and yields output values.
	 *
	 * @param array $program The Intcode program.
	 */
	private function run_program_generator( array $program ) {
		$pointer = 0;

		while ( true ) {
			$instruction = $program[ $pointer ];
			$opcode      = $instruction % 100;
			$mode1       = intdiv( $instruction, 100 ) % 10;
			$mode2       = intdiv( $instruction, 1000 ) % 10;

			switch ( $opcode ) {
				case 1:
					$param1           = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$param2           = ( $mode2 === 1 ) ? $program[ $pointer + 2 ] : $program[ $program[ $pointer + 2 ] ];
					$dest             = $program[ $pointer + 3 ];
					$program[ $dest ] = $param1 + $param2;
					$pointer          += 4;
					break;
				case 2:
					$param1           = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$param2           = ( $mode2 === 1 ) ? $program[ $pointer + 2 ] : $program[ $program[ $pointer + 2 ] ];
					$dest             = $program[ $pointer + 3 ];
					$program[ $dest ] = $param1 * $param2;
					$pointer          += 4;
					break;
				case 3:
					$dest = $program[ $pointer + 1 ];
					// Yield to wait for an input value.
					$input            = yield;
					$program[ $dest ] = $input;
					$pointer          += 2;
					break;
				case 4:
					$param1  = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$pointer += 2;
					yield $param1;
					break;
				case 5:
					$param1 = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$param2 = ( $mode2 === 1 ) ? $program[ $pointer + 2 ] : $program[ $program[ $pointer + 2 ] ];
					if ( $param1 !== 0 ) {
						$pointer = $param2;
					} else {
						$pointer += 3;
					}
					break;
				case 6:
					$param1 = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$param2 = ( $mode2 === 1 ) ? $program[ $pointer + 2 ] : $program[ $program[ $pointer + 2 ] ];
					if ( $param1 === 0 ) {
						$pointer = $param2;
					} else {
						$pointer += 3;
					}
					break;
				case 7:
					$param1           = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$param2           = ( $mode2 === 1 ) ? $program[ $pointer + 2 ] : $program[ $program[ $pointer + 2 ] ];
					$dest             = $program[ $pointer + 3 ];
					$program[ $dest ] = ( $param1 < $param2 ) ? 1 : 0;
					$pointer          += 4;
					break;
				case 8:
					$param1           = ( $mode1 === 1 ) ? $program[ $pointer + 1 ] : $program[ $program[ $pointer + 1 ] ];
					$param2           = ( $mode2 === 1 ) ? $program[ $pointer + 2 ] : $program[ $program[ $pointer + 2 ] ];
					$dest             = $program[ $pointer + 3 ];
					$program[ $dest ] = ( $param1 === $param2 ) ? 1 : 0;
					$pointer          += 4;
					break;
				case 99:
					return;
			}
		}
	}

	/**
	 * Recursively generates all permutations of the given array.
	 *
	 * @param array $items The array of items.
	 *
	 * @return array An array of permutations.
	 */
	private function get_permutations( array $items ): array {
		if ( count( $items ) <= 1 ) {
			return [ $items ];
		}

		$permutations = [];

		foreach ( $items as $key => $item ) {
			$remaining = $items;
			unset( $remaining[ $key ] );
			$remaining = array_values( $remaining );
			foreach ( $this->get_permutations( $remaining ) as $perm ) {
				$permutations[] = array_merge( [ $item ], $perm );
			}
		}

		return $permutations;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-07-test.txt' : '/data/day-07.txt';

		return array_map( 'intval', explode( ",", trim( file_get_contents( __DIR__ . $file ) ) ) );
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
	$day07  = new Day07( $test, $part );
	$result = $day07->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 65210,
			'real' => 21760,
		],
		2 => [
			'test' => 76543,
			'real' => 69816958,
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
