<?php

namespace AdventOfCode\Year2018;

/**
 * Day 04: Repose Record
 */
class Day04 {
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
	 * Part 1: Find the guard that has the most minutes asleep, then find the minute that guard spends asleep the most
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$guard_data = $this->process_guard_records();

		// Find guard with most total sleep minutes
		$max_sleep_guard = null;
		$max_sleep_time  = 0;

		foreach ( $guard_data as $guard_id => $data ) {
			$total_sleep = array_sum( $data['minutes'] );
			if ( $total_sleep > $max_sleep_time ) {
				$max_sleep_time  = $total_sleep;
				$max_sleep_guard = $guard_id;
			}
		}

		if ( $max_sleep_guard === null ) {
			return 0;
		}

		// Find the minute this guard is asleep most often
		$minute_frequencies   = $guard_data[ $max_sleep_guard ]['minutes'];
		$most_frequent_minute = array_keys( $minute_frequencies, max( $minute_frequencies ), true )[0];

		return $max_sleep_guard * $most_frequent_minute;
	}

	/**
	 * Part 2: Find the guard/minute combination with the highest frequency across all guards
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$guard_data = $this->process_guard_records();

		$max_frequency = 0;
		$best_guard    = null;
		$best_minute   = null;

		// Check all guard/minute combinations to find the one with highest frequency
		foreach ( $guard_data as $guard_id => $data ) {
			foreach ( $data['minutes'] as $minute => $frequency ) {
				if ( $frequency > $max_frequency ) {
					$max_frequency = $frequency;
					$best_guard    = $guard_id;
					$best_minute   = $minute;
				}
			}
		}

		if ( $best_guard === null ) {
			return 0;
		}

		return $best_guard * $best_minute;
	}

	/**
	 * Processes guard records and returns sleep data per guard.
	 *
	 * @return array Associative array: guard_id => ['minutes' => [minute => frequency], 'total' => total_minutes]
	 */
	private function process_guard_records(): array {
		$guard_data    = [];
		$current_guard = null;
		$sleep_start   = null;

		foreach ( $this->data as $record ) {
			$action = $record['action'];
			$minute = $record['minute'];

			// Check if guard begins shift
			if ( preg_match( '/Guard #(\d+) begins shift/', $action, $guard_matches ) ) {
				$current_guard = (int) $guard_matches[1];
				if ( ! isset( $guard_data[ $current_guard ] ) ) {
					$guard_data[ $current_guard ] = [
						'minutes' => array_fill( 0, 60, 0 ),
					];
				}
			} elseif ( $action === 'falls asleep' ) {
				$sleep_start = $minute;
			} elseif ( $action === 'wakes up' && $sleep_start !== null && $current_guard !== null ) {
				// Mark all minutes from sleep_start to minute-1 as asleep
				// Note: minute is when they wake up, so they're awake at that minute
				for ( $m = $sleep_start; $m < $minute; $m++ ) {
					$guard_data[ $current_guard ]['minutes'][ $m ]++;
				}
				$sleep_start = null;
			}
		}

		return $guard_data;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of parsed records, each with 'timestamp', 'action', and 'minute' keys, sorted chronologically
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-04-test.txt' : '/data/day-04.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$parsed_records = [];
		foreach ( $lines as $line ) {
			// Extract timestamp and action
			if ( preg_match( '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2})\]\s+(.+)/', $line, $matches ) ) {
				$timestamp = $matches[1];
				$action    = $matches[2];
				$minute    = (int) substr( $timestamp, -2 );

				$parsed_records[] = [
					'timestamp' => $timestamp,
					'action'    => $action,
					'minute'    => $minute,
				];
			}
		}

		// Sort chronologically
		usort( $parsed_records, function( $a, $b ) {
			return strcmp( $a['timestamp'], $b['timestamp'] );
		} );

		return $parsed_records;
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
	$day04  = new Day04( $test, $part );
	$result = $day04->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 240,
			'real' => 102688,
		],
		2 => [
			'test' => 4455,
			'real' => 56901,
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
