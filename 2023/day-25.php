<?php

/**
 * Day 25:
 */
class Day25 {
	/**
	 * The puzzle data.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
	 */
	private int $part;

	/**
	 * Whether or not to use the test data.
	 *
	 * @var bool
	 */
	private bool $is_test;

	public function __construct(string $test, int $part) {
		$this->parse_data($test);
		$this->part = $part;
		$this->is_test = $test;
	}

	public function part_1(): int {

	}

	public function part_2(): int {

	}

	private function parse_data(bool $test): void {
		$file = $test ? '/data/day-25-test.txt' : '/data/day-25.txt';
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
	$day25    = new Day25( $test, 1 );
	$result   = $day25->part_1();
	$end      = microtime( true );
	$expected = $test ? 0 : 3716;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day25    = new Day25( $test, 2 );
	$result   = $day25->part_2();
	$end      = microtime( true );
	$expected = $test ? 16733044 : 616583483179597;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
