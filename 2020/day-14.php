<?php

namespace AdventOfCode\Year2020;

ini_set('memory_limit', '1024M');

/**
 * Day 14: Docking Data
 */
class Day14 {
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
	 * Part 1: Execute the initialization program. What is the sum of all values left in memory after it completes?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$mem = [];

		foreach ( $this->data as $operation ) {
			$mask = $operation['mask'];
			$address = $operation['address'];
			$value = $operation['value'];

			$binary = str_pad( decbin( $value ), 36, '0', STR_PAD_LEFT );
			$masked = '';

			for ( $i = 0; $i < 36; $i++ ) {
				$masked .= $mask[ $i ] === 'X' ? $binary[ $i ] : $mask[ $i ];
			}

			$mem[ $address ] = bindec( $masked );
		}

		return array_sum( $mem );
	}

	/**
	 * Part 2: Execute the initialization program using an emulator for a version 2 decoder chip. What is the sum of all
	 *         values left in memory after it completes?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$mem = [];

		foreach ( $this->data as $operation ) {
			$mask = $operation['mask'];
			$address = $operation['address'];
			$value = $operation['value'];

			$binary = str_pad( decbin( $address ), 36, '0', STR_PAD_LEFT );
			$masked = '';

			for ( $i = 0; $i < 36; $i++ ) {
				$masked .= $mask[ $i ] === '0' ? $binary[ $i ] : $mask[ $i ];
			}

			$addresses = $this->generate_addresses( $masked, $masked );

			foreach ( $addresses as $addr ) {
				$mem[ bindec( $addr ) ] = $value;
			}
		}

		return array_sum( $mem );
	}

	/**
	 * Recursively generates all possible addresses for the given mask and address.
	 *
	 * This method takes a mask and an address, and generates all possible addresses
	 * by replacing each 'X' in the mask with both '0' and '1'. It uses recursion to
	 * handle multiple 'X' characters in the mask.
	 *
	 * @param string $mask The mask to apply.
	 * @param string $address The address to apply the mask to.
	 *
	 * @return array
	 */
	private function generate_addresses( string $mask, string $address ): array {
		if ( ! str_contains( $mask, 'X' ) ) {
			return [ $address ];
		}

		$index = strpos( $mask, 'X' );

		$mask[ $index ] = '0';
		$address[ $index ] = '0';
		$addresses = $this->generate_addresses( $mask, $address );

		$mask[ $index ] = '1';
		$address[ $index ] = '1';

		return array_merge( $addresses, $this->generate_addresses( $mask, $address ) );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-14-test.txt' : '/data/day-14.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$parsed_data = [];
		$current_mask = '';

		foreach ( $lines as $line ) {
			if ( str_starts_with( $line, 'mask' ) ) {
				$current_mask = explode( ' = ', $line )[1];
			} else {
				preg_match( '/mem\[(\d+)\] = (\d+)/', $line, $matches );
				$address = $matches[1];
				$value   = $matches[2];
				$parsed_data[] = [
					'mask' => $current_mask,
					'address' => $address,
					'value' => $value,
				];
			}
		}

		return $parsed_data;
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
	$day14  = new Day14( $test, $part );
	$result = $day14->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 51,
			'real' => 13865835758282,
		],
		2 => [
			'test' => 208,
			'real' => 4195339838136,
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
