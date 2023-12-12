<?php

/**
 * Day 12: Hot Springs
 */
class Day12 {
	private array $data;

	/**
	 * Constructor to parse the data.
	 *
	 * @param string $test Flag to use test data.
	 * @param int $part Puzzle part, 1 or 2.
	 */
	public function __construct(string $test, int $part) {
		$this->parse_data($test, $part);
	}

	/**
	 * Part 1: Count all the different arrangements of operational and broken springs that meet the given criteria.
	 *         What is the sum of those counts?
	 *
	 * @return int The sum of all the different arrangements.
	 */
	public function part_1(): int {
		$sum = 0;

		foreach ( $this->data as $line ) {
			[ $springs, $group_sizes ] = explode( " ", $line );
			$group_sizes = explode( ',', $group_sizes );
			$sum += $this->find_possible_combinations( $springs, $group_sizes, [ 0, 0, 0 ] );
		}

		return $sum;
	}

	/**
	 * Part 2: Unfold each line by making five copies of each spring and group size.
	 *
	 * @return int The sum of all the different arrangements.
	 */
	public function part_2(): int {
		$sum = 0;

		foreach ( $this->data as $line ) {
			// Unfold the line
			$line = $this->unfold_line( $line );

			[ $springs, $group_sizes ] = explode( ' ', $line );
			$group_sizes = explode( ',', $group_sizes );
			$sum += $this->find_possible_combinations( $springs, $group_sizes, [ 0, 0, 0 ] );
		}

		return $sum;
	}

	/**
	 * Recursively finds the number of valid arrangements of springs.
	 *
	 * @param string $springs The springs
	 * @param array  $sizes   Array of the sizes of each group of springs.
	 * @param array  $state   The current state ( [position, group index, length of the current group].
	 * @param array  $cache   Cache for memoization to store previous results.
	 *
	 * @return int The number of valid arrangements for the given state.
	 */
	private function find_possible_combinations( string $springs, array $sizes, array $state, array &$cache = [] ): int {
		$key = implode( ":", $state );

		if ( isset( $cache[ $key ] ) ) {
			return $cache[ $key ];
		}

		[ $position, $index, $length ] = $state;

		// Check if the end of the string and the group list is reached
		if ( $position == strlen( $springs ) ) {
			if ( $index == count( $sizes ) - 1 && $length == $sizes[ $index ] ) {
				$index ++;
				$length = 0;
			}

			return (int) ( $index == count( $sizes ) && $length == 0 );
		}

		$arrangements = 0;

		// If the current spring is operational or unknown, explore the next state
		if ( str_contains( '.?', $springs[ $position ] ) ) {
			// If the current group length is zero, continue exploring without incrementing the group index
			if ( $length == 0 ) {
				$next_state   = [ $position + 1, $index, 0 ];
				$arrangements += $this->find_possible_combinations( $springs, $sizes, $next_state, $cache );
			} elseif ( $index < count( $sizes ) && $sizes[ $index ] == $length ) {
				// The current group has reached the correct length, move to the next group
				$next_state   = [ $position + 1, $index + 1, 0 ];
				$arrangements += $this->find_possible_combinations( $springs, $sizes, $next_state, $cache );
			}
		}

		// If the current spring is damaged or unknown, extend the current group's length
		if ( str_contains( '#?', $springs[ $position ] ) ) {
			$next_state   = [ $position + 1, $index, $length + 1 ];
			$arrangements += $this->find_possible_combinations( $springs, $sizes, $next_state, $cache );
		}

		return $cache[ $key ] = $arrangements;
	}

	private function unfold_line( string $line ): string {
		[ $springs, $sizes ] = explode( ' ', $line );
		$springs = implode( '?', array_fill( 0, 5, $springs ) );
		$sizes   = implode( ',', array_fill( 0, 5, $sizes ) );

		return $springs . ' ' . $sizes;
	}

	/**
	 * Parses the puzzle data from the file.
	 *
	 * @param string $test Flag to use test data.
	 * @param int $part Puzzle part, 1 or 2.
	 */
	private function parse_data(string $test, int $part): void {
		$file = $test ? '/data/day-12-test.txt' : '/data/day-12.txt';
		$this->data = explode("\n", trim(file_get_contents(__DIR__ . $file)));
	}
}

// Prompt which part to run and if it should use the test data.
while ( true ) {
	$part = trim( readline( 'Which part do you want to run? (1/2)' ) );
	if ( function_exists( "part_$part" ) ) {
		while ( true ) {
			$test = trim( strtolower( readline( 'Do you want to run the test? (y/n)' ) ) );
			if ( in_array( $test, array( 'y', 'n' ), true ) ) {
				$test = 'y' === $test;
				call_user_func( "part_$part", $test );
				break;
			}
			echo 'Please enter y or n' . PHP_EOL;
		}
		break;
	}
	echo 'Please enter 1 or 2' . PHP_EOL;
}

function part_1( $test = false ) {
	$start    = microtime( true );
	$day12    = new Day12( $test, 1 );
	$result   = $day12->part_1();
	$end      = microtime( true );
	$expected = $test ? 21 : 7694;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day12    = new Day12( $test, 2 );
	$result   = $day12->part_2();
	$end      = microtime( true );
	$expected = $test ? 525152 : 5071883216318;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
