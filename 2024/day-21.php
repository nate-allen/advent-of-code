<?php

namespace AdventOfCode\Year2024;

/**
 * Day 21: Keypad Conundrum
 */
class Day21 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
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
	 * The positions of the keys on the keypad.
	 *
	 * @var array
	 */
	private array $positions = [
		'7' => [ 0, 0 ],
		'8' => [ 0, 1 ],
		'9' => [ 0, 2 ],
		'4' => [ 1, 0 ],
		'5' => [ 1, 1 ],
		'6' => [ 1, 2 ],
		'1' => [ 2, 0 ],
		'2' => [ 2, 1 ],
		'3' => [ 2, 2 ],
		'0' => [ 3, 1 ],
		'A' => [ 3, 2 ],
		'^' => [ 0, 1 ],
		'a' => [ 0, 2 ],
		'<' => [ 1, 0 ],
		'v' => [ 1, 1 ],
		'>' => [ 1, 2 ],
	];

	/**
	 * The directions to move on the keypad.
	 *
	 * @var array
	 */
	private array $directions = [
		'^' => [ - 1, 0 ],
		'v' => [ 1, 0 ],
		'<' => [ 0, - 1 ],
		'>' => [ 0, 1 ],
	];

	/**
	 * Cache.
	 *
	 * @var array
	 */
	private array $cache = [];

	public function __construct(bool $test, int $part) {
		$this->part = $part;
		$this->is_test = $test;
		$this->data = $this->parse_data($this->is_test);
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return int
	 */
	public function run(): int {
		return match ($this->part) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException('Invalid part specified.'),
		};
	}

	/**
	 * Part 1: Find the shortest sequence of button presses needed to keypad codes using two layers of remote-controlled robots.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		return $this->calculate_complexity(2);
	}

	/**
	 * Part 2: Find the shortest sequence of button presses needed to keypad codes using 25 layers of remote-controlled robots.
	 * @return int
	 */
	private function solve_part_2(): int {
		return $this->calculate_complexity(25);
	}

	/**
	 * Calculates the total complexity for all the codes.
	 *
	 * @param int $limit The maximum depth of the robot chain
	 *
	 * @return int
	 */
	private function calculate_complexity(int $limit): int {
		$complexity = 0;

		foreach ($this->data as $code) {
			// Get the numeric part of the code ("029A" becomes 29).
			$numeric = (int) substr($code, 0, 3);

			$complexity += $numeric * $this->min_length($code, $limit);
		}

		return $complexity;
	}

	/**
	 * Generates all valid moves between two positions while avoiding a specific position.
	 *
	 * @param array $start The starting position.
	 * @param array $end   The target position.
	 * @param array $avoid Position to avoid during the moves.
	 *
	 * @return array
	 */
	private function find_moves_between_positions( array $start, array $end, array $avoid = [ 0, 0 ] ): array {
		// Calculate the difference (delta) in row and column coordinates.
		$difference = [ $end[0] - $start[0], $end[1] - $start[1] ];
		$string     = '';

		// Vertical movement.
		if ( $difference[0] < 0 ) {
			$string .= str_repeat( '^', abs( $difference[0] ) );
		} else {
			$string .= str_repeat( 'v', $difference[0] );
		}

		// Horizontal movement.
		if ( $difference[1] < 0 ) {
			$string .= str_repeat( '<', abs( $difference[1] ) );
		} else {
			$string .= str_repeat( '>', $difference[1] );
		}

		// Generate all combinations of the combined move sequence.
		$combinations = $this->get_combinations( str_split( $string ) );
		$result       = [];

		foreach ( $combinations as $combination ) {
			$positions = [ $start ];

			// Simulate the sequence to track positions and ensure the "avoid" position is not crossed.
			foreach ( $combination as $dir ) {
				$last        = end( $positions );
				$positions[] = [ $last[0] + $this->directions[ $dir ][0], $last[1] + $this->directions[ $dir ][1] ];
			}

			// Add valid sequences that do not pass through the "avoid" position.
			if ( ! in_array( $avoid, $positions, true ) ) {
				$result[] = implode( '', $combination ) . 'a'; // Append 'a' to press the button.
			}
		}

		// Default to a single "a" press if no valid paths are found.
		return $result ?: [ 'a' ];
	}

	/**
	 * Recursively calculates the minimum length of the moves to type a code on the keypad.
	 *
	 * @param string  $code  The code to type on the numeric keypad.
	 * @param integer $limit The maximum depth of the robot chain.
	 * @param integer $depth The current robot depth in the chain (default: 0).
	 *
	 * @return int
	 */
	private function min_length( string $code, int $limit, int $depth = 0 ): int {
		$key = $code . $depth . $limit;

		// Return cached result if already computed.
		if ( isset( $this->cache[ $key ] ) ) {
			return $this->cache[ $key ];
		}

		$avoid  = $depth === 0 ? [ 3, 0 ] : [ 0, 0 ];
		$cur    = $depth === 0 ? $this->positions['A'] : $this->positions['a'];
		$length = 0;

		foreach ( str_split( $code ) as $char ) {
			// Get the next position on the keypad.
			$next_cur = $this->positions[ $char ];

			// Get all valid move sequences between the current and next positions.
			$movesets = $this->find_moves_between_positions( $cur, $next_cur, $avoid );

			if ( $depth === $limit ) {
				// If at the last robot, use the shortest move directly.
				$length += strlen( $movesets[0] );
			} else {
				// Else, recursively compute the shortest sequence for all move options.
				$length += min( array_map( fn( $moveset ) => $this->min_length( $moveset, $limit, $depth + 1 ), $movesets ) );
			}

			// Update the current position.
			$cur = $next_cur;
		}

		// Cache the result.
		$this->cache[ $key ] = $length;

		return $length;
	}

	/**
	 * Generates all combinations of a given set of items.
	 *
	 * @param array $items The items to get combinations.
	 *
	 * @return array
	 */
	private function get_combinations(array $items): array {
		// A single item has only one combination.
		if ( count( $items ) === 1 ) {
			return [ $items ];
		}

		$combinations = [];

		foreach ( $items as $key => $item ) {
			// Remove the current item from the list.
			$remaining = $items;
			unset( $remaining[ $key ] );

			// Generate combinations for the remaining items.
			foreach ( $this->get_combinations( $remaining ) as $combination ) {
				$combinations[] = array_merge( [ $item ], $combination );
			}
		}

		return $combinations;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data(bool $test): array {
		$file = $test ? '/data/day-21-test.txt' : '/data/day-21.txt';
		$lines = explode("\n", trim(file_get_contents(__DIR__ . $file)));

		return array_filter($lines, fn($line) => preg_match('/\d{3}A/', $line));
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param int $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day21  = new Day21( $test, $part );
	$result = $day21->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 126384,
			'real' => 205160,
		],
		2 => [
			'test' => 154115708116294,
			'real' => 252473394928452,
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
