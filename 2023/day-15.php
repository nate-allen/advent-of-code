<?php

/**
 * Day 15: Lens Library
 */
class Day15 {
	/**
	 * The puzzle data.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * Array representing the 256 boxes
	 *
	 * @var array
	 */
	private array $boxes;

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
	 * Part 1: Find the sum of the HASH algorithm results for each step.
	 *
	 * @return int The answer.
	 */
	public function part_1(): int {
		$sum = 0;

		foreach ( $this->data as $step ) {
			$sum += $this->compute_hash( $step );
		}

		return $sum;
	}

	/**
	 * Part 2: Computes the total focusing power of the lens configuration.
	 *
	 * @return int The total focusing power.
	 */
	public function part_2(): int {
		$this->boxes = array_fill( 0, 256, [] );

		foreach ( $this->data as $step ) {
			$this->process_step( $step );
		}

		return $this->calculate_focusing_power();
	}

	/**
	 * Computes the HASH value for a given string
	 *
	 * @param string $string The string to compute the HASH for.
	 *
	 * @return int The HASH value.
	 */
	private function compute_hash( string $string ): int {
		$current_value = 0;

		foreach ( str_split( $string ) as $char ) {
			$ascii         = ord( $char );
			$current_value = ( $current_value + $ascii ) * 17 % 256;
		}

		return $current_value;
	}

	/**
	 * Processes each step in the initialization sequence.
	 *
	 * @param string $step The step to process.
	 */
	private function process_step(string $step): void {
		$label        = substr( $step, 0, str_contains( $step, '=' ) ? strpos( $step, '=' ) : strpos( $step, '-' ) );
		$index        = $this->compute_hash( $label );
		$operation    = $step[ strlen( $label ) ];
		$focal_length = '=' === $operation ? intval( substr( $step, strlen( $label ) + 1 ) ) : null;

		if ( $operation === '-' ) {
			$this->remove_lens( $index, $label );
		} else {
			$this->insert_lens( $index, $label, $focal_length );
		}
	}

	/**
	 * Inserts a lens into the specified box.
	 *
	 * @param int    $index        The index of the box.
	 * @param string $label        The label of the lens.
	 * @param int    $focal_length The focal length of the lens.
	 */
	private function insert_lens( int $index, string $label, int $focal_length ): void {
		$found = false;

		foreach ( $this->boxes[ $index ] as &$lens ) {
			if ( $lens['label'] === $label ) {
				$lens['focal_length'] = $focal_length;
				$found                = true;
				break;
			}
		}

		if ( ! $found ) {
			$this->boxes[ $index ][] = [ 'label' => $label, 'focal_length' => $focal_length ];
		}
	}

	/**
	 * Removes a lens from the specified box.
	 *
	 * @param int    $index The index of the box.
	 * @param string $label The label of the lens to remove.
	 */
	private function remove_lens( int $index, string $label ): void {
		$this->boxes[ $index ] = array_values( array_filter(
			$this->boxes[ $index ],
			fn( $lens ) => $lens['label'] !== $label
		) );
	}

	/**
	 * Calculates the total focusing power of the lens configuration.
	 *
	 * @return int The total focusing power.
	 */
	private function calculate_focusing_power(): int {
		$power = 0;

		foreach ( $this->boxes as $index => $lenses ) {
			foreach ( $lenses as $slotIndex => $lens ) {
				$power += ( $index + 1 ) * ( $slotIndex + 1 ) * $lens['focal_length'];
			}
		}

		return $power;
	}

	/**
	 * Parses the puzzle data from the file.
	 *
	 * @param string $test Flag to use test data.
	 * @param int    $part Puzzle part, 1 or 2.
	 */
	private function parse_data(string $test, int $part): void {
		$file       = $test ? '/data/day-15-test.txt' : '/data/day-15.txt';
		$this->data = explode( ',', trim( file_get_contents( __DIR__ . $file ) ) );
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
	$expected = $test ? 1320 : 517315;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day15    = new Day15( $test, 2 );
	$result   = $day15->part_2();
	$end      = microtime( true );
	$expected = $test ? 145 : 247763;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
