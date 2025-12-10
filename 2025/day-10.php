<?php

namespace AdventOfCode\Year2025;

/**
 * Day 10: Factory
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
	 * Part 1: Find minimum button presses to configure indicator lights.
	 *
	 * Uses BFS with XOR bitmask operations to find the shortest path from
	 * all lights off (state 0) to the target configuration.
	 *
	 * @return integer Sum of minimum presses for all machines.
	 */
	private function solve_part_1(): int {
		$total_presses = 0;

		foreach ( $this->data as $machine ) {
			// Convert target pattern to integer bitmask
			// First character in string corresponds to bit 0
			$target_str = $machine['target'];
			$target     = 0;
			$target_len = strlen( $target_str );
			for ( $i = 0; $i < $target_len; $i++ ) {
				if ( $target_str[ $i ] === '#' ) {
					$target |= (1 << $i);
				}
			}

			// Convert each button schematic to integer bitmask
			$button_masks = [];
			foreach ( $machine['buttons'] as $button_indices ) {
				$mask = 0;
				foreach ( $button_indices as $light_idx ) {
					$mask |= (1 << $light_idx);
				}
				$button_masks[] = $mask;
			}

			// Find minimum presses
			$get_min_presses = $this->get_min_presses( 0, $target, $button_masks );
			$total_presses  += $get_min_presses;
		}

		return $total_presses;
	}

	/**
	 * Part 2: Find minimum button presses to configure joltage counters.
	 *
	 * Uses Integer Linear Programming (ILP) to solve Ax = b where:
	 * - A[i][j] = 1 if button j affects counter i, else 0
	 * - b[i] = target joltage for counter i
	 * - x[j] = number of times button j is pressed (non-negative integers)
	 * - Objective: minimize sum(x)
	 *
	 * @return integer Sum of minimum presses for all machines.
	 */
	private function solve_part_2(): int {
		$total_presses = 0;

		foreach ( $this->data as $machine ) {
			$joltages = $machine['joltages'];
			$buttons  = $machine['buttons'];

			// Build constraint matrix A: A[i][j] = 1 if button j affects counter i
			$num_counters = count( $joltages );
			$num_buttons  = count( $buttons );
			$A            = [];

			for ( $i = 0; $i < $num_counters; $i++ ) {
				$A[ $i ] = [];
				for ( $j = 0; $j < $num_buttons; $j++ ) {
					// Check if button j affects counter i
					$A[ $i ][ $j ] = in_array( $i, $buttons[ $j ], true ) ? 1 : 0;
				}
			}

			// Solve ILP: minimize sum(x) subject to Ax = b, x >= 0
			$get_min_presses = $this->solve_ilp( $A, $joltages );
			$total_presses  += $get_min_presses;
		}

		return $total_presses;
	}

	/**
	 * Finds minimum button presses to reach target state.
	 *
	 * @param int   $start_state Starting state (all lights off = 0).
	 * @param int   $target_state Target state bitmask.
	 * @param array $button_masks Array of button bitmasks.
	 *
	 * @return int Minimum number of button presses needed.
	 */
	private function get_min_presses( int $start_state, int $target_state, array $button_masks ): int {
		$queue   = new \SplQueue();
		$visited = [];

		$queue->enqueue([$start_state, 0]);
		$visited[$start_state] = true;

		while ( ! $queue->isEmpty() ) {
			[$current_state, $presses] = $queue->dequeue();

			// Found target state
			if ( $current_state === $target_state ) {
				return $presses;
			}

			// Try pressing each button
			foreach ( $button_masks as $button_mask ) {
				$new_state = $current_state ^ $button_mask;

				// If we haven't seen this state before, add it to queue
				if ( ! isset( $visited[$new_state] ) ) {
					$visited[$new_state] = true;
					$queue->enqueue([$new_state, $presses + 1]);
				}
			}
		}

		// Should never reach here if puzzle input is valid
		return 0;
	}

	/**
	 * Solves ILP using Python's scipy.optimize.linprog.
	 *
	 * @param array $A Constraint matrix (counters × buttons).
	 * @param array $b Target vector (joltage requirements).
	 *
	 * @return int|null Minimum sum of button presses, or null if Python/scipy unavailable.
	 */
	private function solve_ilp( array $A, array $b ): ?int {
		$script_path = __DIR__ . '/solve_ilp.py';

		// Check if Python script exists
		if ( ! file_exists( $script_path ) ) {
			return null;
		}

		// Prepare input data as JSON
		$input_data = [
			'A' => $A,
			'b' => $b,
		];
		$json_input = json_encode( $input_data );

		$descriptorspec = [
			0 => ['pipe', 'r'], // stdin
			1 => ['pipe', 'w'], // stdout
			2 => ['pipe', 'w'], // stderr
		];

		// Execute Python script
		$command = sprintf(
			'python3 %s 2>/dev/null',
			escapeshellarg( $script_path )
		);

		$process = proc_open( $command, $descriptorspec, $pipes );
		if ( ! is_resource( $process ) ) {
			return null;
		}

		// Write input data
		fwrite( $pipes[0], $json_input );
		fclose( $pipes[0] );

		// Read output
		$output = stream_get_contents( $pipes[1] );
		$error  = stream_get_contents( $pipes[2] );
		fclose( $pipes[1] );
		fclose( $pipes[2] );

		$return_code = proc_close( $process );

		// Check if execution was successful
		if ( $return_code !== 0 || empty( $output ) ) {
			return null;
		}

		// Parse result
		$result = trim( $output );
		if ( ! is_numeric( $result ) ) {
			return null;
		}

		return (int) $result;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of machines, each with 'target', 'buttons', and 'joltages'.
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-10-test.txt' : '/data/day-10.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$machines = [];

		foreach ( $lines as $line ) {
			preg_match( '/\[([.#]+)\]/', $line, $target_match );
			$target_str = $target_match[1] ?? '';

			preg_match_all( '/\(([0-9,]+)\)/', $line, $button_matches );
			$buttons = [];
			foreach ( $button_matches[1] as $button_str ) {
				$button_indices = array_map( 'intval', explode( ',', $button_str ) );
				$buttons[]      = $button_indices;
			}

			preg_match( '/\{([0-9,]+)\}/', $line, $joltage_match );
			$joltages = [];
			if ( isset( $joltage_match[1] ) ) {
				$joltages = array_map( 'intval', explode( ',', $joltage_match[1] ) );
			}

			$machines[] = [
				'target'   => $target_str,
				'buttons'  => $buttons,
				'joltages' => $joltages,
			];
		}

		return $machines;
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
			'test' => 7,
			'real' => 455,
		],
		2 => [
			'test' => 33,
			'real' => 0,
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
