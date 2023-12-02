<?php
/**
 * Day 20: Grove Positioning System
 */
class Day20 {

	private array $encrypted_file;

	public bool $use_key;

	private int $encryption_key = 811589153;

	public function __construct( $test, $use_key = false ) {
		$this->use_key        = $use_key;
		$this->encrypted_file = $this->parse_data( $test );
	}

	/**
	 * Part 1: What is the sum of the three numbers that form the grove coordinates?
	 *
	 * The grove coordinates can be found by looking at the 1000th, 2000th, and 3000th numbers after the value 0,
	 * wrapping around the list as necessary.
	 *
	 * @return int
	 */
	public function part_1(): int {
		$count   = count( $this->encrypted_file );
		$indexes = range( 0, $count - 1 );
		$indexes = $this->mix( $indexes );
		$mixed   = array();

		foreach ( $indexes as $key => $index ) {
			$mixed[ $index ] = $this->encrypted_file[ $key ];
		}

		// Find where the zero is.
		$zero = array_search( 0, $this->encrypted_file, true );

		return $this->get_sum( $indexes[ $zero ], $count, $mixed ); // 4224
	}

	/**
	 * Part 2: Multiply each number by the decryption key, mix the list ten times, and then sum the three numbers that
	 * form the grove coordinates.
	 *
	 * Again, the grove coordinates can be found by looking at the 1000th, 2000th, and 3000th numbers after the value 0,
	 * wrapping around the list as necessary.
	 *
	 * @return int
	 */
	public function part_2(): int {
		$count   = count( $this->encrypted_file );
		$indexes = range( 0, $count - 1 );
		$mixed   = array();

		// Mix it 10 times.
		for ( $i = 0; $i < 10; $i ++ ) {
			$indexes = $this->mix( $indexes );
		}

		foreach ( $indexes as $key => $value ) {
			$mixed[ $value ] = $this->encrypted_file[ $key ];
		}

		$zero = array_search( 0, $this->encrypted_file, true );

		return $this->get_sum( $indexes[ $zero ], $count, $mixed ); // 861907680486
	}

	/**
	 * Mixes the encrypted file
	 *
	 * @param array $indexes The indexes to use for mixing.
	 *
	 * @return array The mixed array.
	 */
	private function mix( array $indexes ) {
		$count = count( $this->encrypted_file );

		for ( $i = 0; $i < $count; $i ++ ) {
			$number = $this->encrypted_file[ $i ];

			if ( 0 === $number ) {
				continue;
			}

			$start = $indexes[ $i ];
			$end   = ( $start + $number ) % ( $count - 1 );

			if ( $end < 0 ) {
				$end += $count - 1;
			}

			if ( $start < $end ) {
				$indexes = array_map(
					function ( $index ) use ( $start, $end ) {
						return ( $index >= $start && $index <= $end ) ? $index - 1 : $index;
					},
					$indexes
				);
			} else {
				$indexes = array_map(
					function ( $index ) use ( $start, $end ) {
						return ( $index >= $end && $index <= $start ) ? $index + 1 : $index;
					},
					$indexes
				);
			}
			$indexes[ $i ] = $end;
		}

		return $indexes;
	}

	/**
	 * Parse the data.
	 *
	 * @param string $test The test data.
	 *
	 * @return array
	 */
	private function parse_data( string $test ): array {
		$path = $test ? '/data/day-20-test.txt' : '/data/day-20.txt';

		return array_map(
			function( $line ) {
				return $this->use_key ? (int) ( $line * $this->encryption_key ) : (int) $line;
			},
			explode(
				PHP_EOL,
				file_get_contents( __DIR__ . $path )
			)
		);
	}

	/**
	 * Returns the sum of the three numbers that form the grove coordinates.
	 *
	 * @param $indexes
	 * @param int $count
	 * @param array $mixed
	 *
	 * @return mixed
	 */
	public function get_sum( $indexes, int $count, array $mixed ) {
		$one_thousand   = ( $indexes + 1000 ) % $count;
		$two_thousand   = ( $indexes + 2000 ) % $count;
		$three_thousand = ( $indexes + 3000 ) % $count;

		return $mixed[ $one_thousand ] + $mixed[ $two_thousand ] + $mixed[ $three_thousand ];
	}
}

// Prompt which part to run and if it should use the test data.
while ( true ) {
	$part = trim( readline( 'Which part do you want to run? (1/2)' ) );
	if ( function_exists( "part_{$part}" ) ) {
		while ( true ) {
			$test = trim( strtolower( readline( 'Do you want to run the test? (y/n)' ) ) );
			if ( in_array( $test, array( 'y', 'n' ), true ) ) {
				$test = 'y' === $test;
				call_user_func( "part_{$part}", $test );
				break;
			}
			echo 'Please enter y or n' . PHP_EOL;
		}
		break;
	}
	echo 'Please enter 1 or 2' . PHP_EOL;
}

function part_1( $test = false ) {
	$start  = microtime( true );
	$day20  = new Day20( $test );
	$result = $day20->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}

function part_2( $test = false ) {
	$start  = microtime( true );
	$day20  = new Day20( $test, true );

	$result = $day20->part_2();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}
