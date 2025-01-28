<?php

namespace AdventOfCode\Year2020;

/**
 * Day 19: Monster Messages
 */
class Day19 {
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
	 * The rules for the messages.
	 *
	 * @var array
	 */
	private array $rules = [];

	/**
	 * The literal values for the rules.
	 *
	 * @var array
	 */
	private array $literals = [];

	/**
	 * Cache for storing validation results.
	 *
	 * @var array
	 */
	private array $cache = [];

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
	 * Part 1: Determine the number of messages that completely match rule 0 using the original rules.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->count_matching_messages( false );
	}

	/**
	 * Part 2: Determines the number of messages that completely match rule 0 using recursive rules.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->count_matching_messages( true );
	}

	/**
	 * Parses the puzzle input and determines the number of messages that match rule 0.
	 *
	 * @param bool $use_recursive_rules Whether to use recursive rules for Part 2.
	 *
	 * @return int
	 */
	function count_matching_messages(bool $use_recursive_rules): int {
		$valid_count = 0;

		foreach ( $this->data['rules'] as $line ) {
			[ $rule_id, $rule_content ] = explode( ': ', $line );

			if ( $rule_id === '8' && $use_recursive_rules ) {
				$this->rules[ $rule_id ] = [ '42', '42 8' ];
			} elseif ( $rule_id === '11' && $use_recursive_rules ) {
				$this->rules[ $rule_id ] = [ '42 31', '42 11 31' ];
			} elseif ( str_contains( $rule_content, '"' ) ) {
				$this->literals[ $rule_id ] = trim( $rule_content, '"' );
			} else {
				$this->rules[ $rule_id ] = explode( ' | ', $rule_content );
			}
		}

		// Validate each message
		foreach ($this->data['messages'] as $message) {
			$this->cache = [];
			$is_valid    = $this->validate_message( $message, 0, 0, strlen( $message ) );
			$valid_count += $is_valid ? 1 : 0;
		}

		return $valid_count;
	}

	/**
	 * Validates a segment of a message against a rule.
	 *
	 * @param string  $message The message to validate.
	 * @param integer $rule_id The ID of the rule to validate against.
	 * @param integer $start   The start index of the segment.
	 * @param integer $end     The end index of the segment.
	 *
	 * @return bool
	 */
	function validate_message( string $message, int $rule_id, int $start, int $end ): bool {
		$cache_key = "{$start}-{$end}-{$rule_id}";

		if ( isset( $this->cache[ $cache_key ] ) ) {
			return $this->cache[ $cache_key ];
		}

		$is_valid = false;

		if ( isset( $this->literals[ $rule_id ] ) ) {
			// Literal match
			$is_valid = substr( $message, $start, $end - $start ) === $this->literals[ $rule_id ];
		} else {
			// Rule match
			foreach ( $this->rules[ $rule_id ] as $sub_rule ) {
				$sub_rule_ids = explode( ' ', $sub_rule );
				if ( $this->match_sub_rules( $message, $start, $end, $sub_rule_ids ) ) {
					$is_valid = true;
					break;
				}
			}
		}

		$this->cache[ $cache_key ] = $is_valid;

		return $is_valid;
	}

	/**
	 * Matches a sequence of rules against a segment of a message.
	 *
	 * @param string  $message       The message to validate.
	 * @param integer $start         The start index of the segment.
	 * @param integer $end           The end index of the segment.
	 * @param array   $rule_sequence The sequence of rules to match.
	 *
	 * @return bool
	 */
	function match_sub_rules(string $message, int $start, int $end, array $rule_sequence): bool {
		if ( $start === $end && empty( $rule_sequence ) ) {
			return true;
		}

		if ( $start === $end || empty( $rule_sequence ) ) {
			return false;
		}

		$first_rule = array_shift( $rule_sequence );

		for ( $i = $start + 1; $i <= $end; $i ++ ) {
			if ( $i == $end && count( $rule_sequence ) > 0 ) {
				continue;
			}

			// Validate the message segment against the first rule
			if (
				$this->validate_message( $message, $first_rule, $start, $i ) &&
				$this->match_sub_rules( $message, $i, $end, $rule_sequence )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-19-test.txt' : '/data/day-19.txt';
		[ $rules, $messages ] = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$rules = array_map( 'trim', explode( "\n", $rules ) );

		return [
			'rules'    => $rules,
			'messages' => explode( "\n", $messages ),
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
	$day19  = new Day19( $test, $part );
	$result = $day19->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 3,
			'real' => 144,
		],
		2 => [
			'test' => 12,
			'real' => 260,
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
