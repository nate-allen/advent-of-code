<?php

/**
 * Day 1: Secret Entrance
 */
class Day01 {
	/**
	 * Whether to use the test data.
	 *
	 * @var bool
	 */
	private bool $is_test;

	/**
	 * Parsed data from the input file.
	 *
	 * [
	 *   'dir'  => 'L' or 'R',
	 *   'dist' => int
	 * ]
	 *
	 * @var array
	 */
	private array $data;

	public function __construct( bool $test, int $part ) {
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Part 1: Count how many times the dial is at 0 AFTER a rotation.
	 *
	 * @return int
	 */
	public function part_1(): int {
		$position = 50;
		$count    = 0;

		foreach ( $this->data as $rotation ) {
			$dir  = $rotation['dir'];
			$dist = $rotation['dist'];

			if ( $dir === 'L' ) {
				$position = ( $position - $dist ) % 100;
			} else {
				$position = ( $position + $dist ) % 100;
			}

			if ( $position < 0 ) {
				$position += 100;
			}

			if ( $position === 0 ) {
				$count ++;
			}
		}

		return $count;
	}

	/**
	 * Part 2: Count how many times ANY CLICK lands on 0.
	 *
	 * @return int
	 */
	public function part_2(): int {
		$position = 50;
		$count    = 0;

		foreach ( $this->data as $rotation ) {
			$dir  = $rotation['dir'];
			$dist = $rotation['dist'];

			// Simulate each click
			for ( $i = 0; $i < $dist; $i ++ ) {
				if ( $dir === 'R' ) {
					$position ++;
					if ( $position === 100 ) {
						$position = 0;
					}
				} else {
					$position --;
					if ( $position === - 1 ) {
						$position = 99;
					}
				}

				if ( $position === 0 ) {
					$count ++;
				}
			}
		}

		return $count;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-01-test.txt' : '/data/day-01.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$parsed = [];

		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( $line === '' ) {
				continue;
			}

			// First character = direction, rest = distance
			$dir  = $line[0];
			$dist = (int) substr( $line, 1 );

			$parsed[] = [
				'dir'  => $dir,
				'dist' => $dist,
			];
		}

		return $parsed;
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
	$day01    = new Day01( $test, 1 );
	$result   = $day01->part_1();
	$end      = microtime( true );
	$expected = $test ? 3 : 1150;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day01    = new Day01( $test, 2 );
	$result   = $day01->part_2();
	$end      = microtime( true );
	$expected = $test ? 6 : 6738;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
