<?php

/**
 * Day 15:
 */
class Day15 {

	/**
	 * The puzzle data.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * Constructor to parse the data.
	 *
	 * @param string $test Flag to use test data.
	 * @param int    $part Puzzle part, 1 or 2.
	 */
	public function __construct(string $test, int $part) {
		$this->parse_data($test, $part);
	}

	/**
	 * Part 1:
	 *
	 * @return int The answer.
	 */
	public function part_1(): int {
		return 0;
	}

	/**
	 * Part 2:
	 *
	 * @return int The answer.
	 */
	public function part_2(): int {
		return 0;
	}

	/**
	 * Parses the puzzle data from the file.
	 *
	 * @param string $test Flag to use test data.
	 * @param int    $part Puzzle part, 1 or 2.
	 */
	private function parse_data(string $test, int $part): void {
		$file       = $test ? '/data/day-15-test.txt' : '/data/day-15.txt';
		$lines      = explode("\n", trim(file_get_contents(__DIR__ . $file)));
		$this->data = array_map(fn($line) => str_split($line), $lines);
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
	$day15    = new Day15( $test, 1 );
	$result   = $day15->part_1();
	$end      = microtime( true );
	$expected = $test ? 0 : 0;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day15    = new Day15( $test, 2 );
	$result   = $day15->part_2();
	$end      = microtime( true );
	$expected = $test ? 0 : 0;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
