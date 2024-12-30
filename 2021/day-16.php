<?php

namespace AdventOfCode\Year2021;

/**
 * Day 16: Packet Decoder
 */
class Day16 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var integer
	 */
	private int $part;

	/**a
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
	 * Part 1: Decode the packet and calculate the sum of version numbers.
	 *
	 * The input hexadecimal transmission is converted into binary, and the packet hierarchy is parsed to extract
	 * the version numbers from each packet. The sum of all version numbers is returned as the result.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$binary = $this->hex_to_binary(implode('', $this->data));
		[$version_sum, ] = $this->parse_packet($binary);

		return $version_sum;
	}

	/**
	 * Part 2: Evaluate the expression represented by the packet hierarchy.
	 *
	 * The input hexadecimal transmission is converted into binary, and the packet hierarchy is parsed to evaluate
	 * the expressions based on the type ID of each packet. The result of the outermost packet's expression is returned.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$binary = $this->hex_to_binary(implode('', $this->data));
		[, $value] = $this->parse_packet($binary);

		return $value;
	}

	/**
	 * Converts a hexadecimal string into a 4-bit binary string.
	 *
	 * @param string $hex The input hexadecimal string.
	 *
	 * @return string
	 */
	private function hex_to_binary(string $hex): string {
		$binary = '';

		foreach ( str_split( $hex ) as $char ) {
			// Convert each hex character to binary
			$bin_char = base_convert( $char, 16, 2 );

			// Pad with leading zeros
			$binary .= str_pad( $bin_char, 4, '0', STR_PAD_LEFT );
		}

		return $binary;
	}

	/**
	 * Parses a binary packet recursively and computes its value or version sum.
	 *
	 * @param string $binary   The binary string representing the packet.
	 * @param integer &$offset A reference to the current parsing offset within the binary string.
	 *
	 * @return array
	 */
	private function parse_packet( string $binary, int &$offset = 0 ): array {
		$version_sum = 0;

		// Read the version and type ID from the packet header
		$version = bindec( substr( $binary, $offset, 3 ) );
		$type_id = bindec( substr( $binary, $offset + 3, 3 ) );
		$offset  += 6;

		$version_sum += $version;

		if ( $type_id === 4 ) { // Literal value packet
			$value = '';
			while ( true ) {
				// Read 5 bits at a time for the literal value
				$group  = substr( $binary, $offset, 5 );
				$value  .= substr( $group, 1 );
				$offset += 5;

				// Stop when the leading bit is 0
				if ( $group[0] === '0' ) {
					break;
				}
			}

			return [ $version_sum, bindec( $value ) ];
		} else { // Operator packet
			$length_type_id = $binary[ $offset ];
			$offset ++;
			$values = [];

			if ( $length_type_id === '0' ) {
				// Read the total length of sub-packets in bits
				$total_length = bindec( substr( $binary, $offset, 15 ) );
				$offset       += 15;

				$end = $offset + $total_length;
				while ( $offset < $end ) {
					// Recursively parse each sub-packet
					[ $sub_version_sum, $sub_value ] = $this->parse_packet( $binary, $offset );
					$version_sum += $sub_version_sum;
					$values[]    = $sub_value;
				}
			} else {
				// Read the number of sub-packets
				$num_sub_packets = bindec( substr( $binary, $offset, 11 ) );
				$offset          += 11;

				for ( $i = 0; $i < $num_sub_packets; $i ++ ) {
					// Recursively parse each sub-packet
					[ $sub_version_sum, $sub_value ] = $this->parse_packet( $binary, $offset );
					$version_sum += $sub_version_sum;
					$values[]    = $sub_value;
				}
			}

			// Evaluate the operator packet based on its type ID
			$value = match ( $type_id ) {
				0 => array_sum( $values ),
				1 => array_product( $values ),
				2 => min( $values ),
				3 => max( $values ),
				5 => $values[0] > $values[1] ? 1 : 0,
				6 => $values[0] < $values[1] ? 1 : 0,
				7 => $values[0] === $values[1] ? 1 : 0,
			};

			return [ $version_sum, $value ];
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
		$file  = $test ? '/data/day-16-test.txt' : '/data/day-16.txt';

		return str_split( trim( file_get_contents( __DIR__ . $file ) ) );
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
	$day16  = new Day16( $test, $part );
	$result = $day16->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 31,
			'real' => 951,
		],
		2 => [
			'test' => 54,
			'real' => 902198718880,
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
