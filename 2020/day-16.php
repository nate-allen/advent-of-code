<?php

namespace AdventOfCode\Year2020;

/**
 * Day 16: Ticket Translation
 */
class Day16 {
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
	 * Part 1: Scan all nearby tickets and sum the values of invalid fields.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		[ $rules, $my_ticket, $nearby_tickets ] = $this->data;
		$invalid_values = [];

		foreach ( $nearby_tickets as $ticket ) {
			foreach ( $ticket as $value ) {
				$valid = false;
				foreach ( $rules as $rule ) {
					if ( ( $value >= $rule[0][0] && $value <= $rule[0][1] ) || ( $value >= $rule[1][0] && $value <= $rule[1][1] ) ) {
						$valid = true;
						break;
					}
				}
				if ( ! $valid ) {
					$invalid_values[] = $value;
				}
			}
		}

		return array_sum( $invalid_values );
	}

	/**
	 * Part 2: Find the product of the six fields on your ticket that start with "departure".
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		[ $rules, $my_ticket, $nearby_tickets ] = $this->data;

		// Get valid tickets
		$valid_tickets = $this->get_valid_tickets( $rules, $nearby_tickets );

		// Get possible fields for each rule
		$possible_fields = $this->get_possible_fields( $rules, $valid_tickets );

		// Determine field order
		$field_order = [];
		while ( count( $field_order ) < count( $rules ) ) {
			foreach ( $possible_fields as $field => $fields ) {
				// If only one field is possible, add it to the order
				if ( count( $fields ) === 1 ) {
					$field_order[ $field ] = array_values( $fields )[0];

					// Remove the field from other possibilities.
					foreach ( $possible_fields as $f => $fs ) {
						$possible_fields[ $f ] = array_diff( $fs, $fields );
					}
				}
			}
		}

		// Calculate product of "departure" fields
		$product = 1;

		// Multiply the values of the fields on my ticket that start with "departure"
		foreach ( $field_order as $field => $index ) {
			if ( strpos( $field, 'departure' ) === 0 ) {
				$product *= $my_ticket[ $index ];
			}
		}

		return $product;
	}

	/**
	 * Get valid tickets based on the given rules.
	 *
	 * @param array $rules          The rules for valid fields.
	 * @param array $nearby_tickets The nearby tickets to check.
	 *
	 * @return array
	 */
	private function get_valid_tickets( array $rules, array $nearby_tickets ): array {
		$valid_tickets = [];
		foreach ( $nearby_tickets as $ticket ) {
			$valid = true;
			foreach ( $ticket as $value ) {
				$valid_field = false;
				foreach ( $rules as $rule ) {
					if ( ( $value >= $rule[0][0] && $value <= $rule[0][1] ) || ( $value >= $rule[1][0] && $value <= $rule[1][1] ) ) {
						$valid_field = true;
						break;
					}
				}
				if ( ! $valid_field ) {
					$valid = false;
					break;
				}
			}
			if ( $valid ) {
				$valid_tickets[] = $ticket;
			}
		}

		return $valid_tickets;
	}

	/**
	 * Get possible fields for each rule based on the valid tickets.
	 *
	 * @param array $rules         The rules for valid fields.
	 * @param array $valid_tickets The valid tickets to check.
	 *
	 * @return array
	 */
	private function get_possible_fields(array $rules, array $valid_tickets): array {
		$possible_fields = [];
		$ticket_count    = count( $valid_tickets );
		$field_count     = count( $valid_tickets[0] );

		foreach ( $rules as $field => $rule ) {
			for ( $i = 0; $i < $field_count; $i ++ ) {
				$valid_field = true;
				for ( $j = 0; $j < $ticket_count; $j ++ ) {
					$value = $valid_tickets[ $j ][ $i ];
					if ( ! ( $value >= $rule[0][0] && $value <= $rule[0][1] ) && ! ( $value >= $rule[1][0] && $value <= $rule[1][1] ) ) {
						$valid_field = false;
						break;
					}
				}
				if ( $valid_field ) {
					$possible_fields[ $field ][] = $i;
				}
			}
		}

		return $possible_fields;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data(bool $test): array {
		$file = $test ? '/data/day-16-test.txt' : '/data/day-16.txt';
		$sections = explode("\n\n", trim(file_get_contents(__DIR__ . $file)));

		// Parse rules
		$rules = [];
		foreach (explode("\n", $sections[0]) as $line) {
			[$field, $ranges] = explode(': ', $line);
			$rules[$field] = array_map(
				fn($range) => array_map('intval', explode('-', $range)),
				explode(' or ', $ranges)
			);
		}

		// Parse my ticket
		$my_ticket = array_map('intval', explode(',', explode("\n", $sections[1])[1]));

		// Parse nearby tickets
		$nearby_tickets = array_map(
			fn($ticket) => array_map('intval', explode(',', $ticket)),
			array_slice(explode("\n", $sections[2]), 1)
		);

		return [$rules, $my_ticket, $nearby_tickets];
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
			'test' => 71,
			'real' => 26026,
		],
		2 => [
			'test' => 1,
			'real' => 1305243193339,
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
