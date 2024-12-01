<?php

/**
 * Day 1: Historian Hysteria
 */
class Day01 {
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

	public function __construct(bool $test, int $part) {
		$this->part = $part;
		$this->is_test = $test;
		$this->data = $this->parse_data($this->is_test);
	}

	/**
	 * Part 1: Calculate total distance between the lists.
	 *
	 * @return int
	 */
	public function part_1(): int {
		$left  = array_column( $this->data, 0 );
		$right = array_column( $this->data, 1 );

		sort( $left );
		sort( $right );

		return array_sum( array_map( fn( $l, $r ) => abs( $l - $r ), $left, $right ) );
	}

	/**
	 * Part 2: Calculate similarity score.
	 *
	 * @return int
	 */
	public function part_2(): int {
		$left  = array_column( $this->data, 0 );
		$right = array_column( $this->data, 1 );

		// Count occurrences of each number in the right list
		$right_counts = array_count_values( $right );

		return array_reduce(
			$left,
			fn( $carry, $number ) => $carry + ( $number * ( $right_counts[ $number ] ?? 0 ) ),
			0
		);
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 */
	private function parse_data( bool $test ): array {
		$file   = $test ? '/data/day-01-test.txt' : '/data/day-01.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( fn( $line ) => array_map( 'intval', preg_split( '/\s+/', $line ) ), $lines );
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

function part_1($test = false) {
	$start = microtime(true);
	$day01 = new Day01($test, 1);
	$result = $day01->part_1();
	$end = microtime(true);
	$expected = $test ? 11 : 2970687;

	printf('Total:    %s' . PHP_EOL, $result);
	printf('Expected: %s' . PHP_EOL, $expected);
	printf('Time:     %s seconds' . PHP_EOL, round($end - $start, 4));
}

function part_2($test = false) {
	$start = microtime(true);
	$day01 = new Day01($test, 2);
	$result = $day01->part_2();
	$end = microtime(true);
	$expected = $test ? 31 : 23963899;

	printf('Total:    %s' . PHP_EOL, $result);
	printf('Expected: %s' . PHP_EOL, $expected);
	printf('Time:     %s seconds' . PHP_EOL, round($end - $start, 4));
}
