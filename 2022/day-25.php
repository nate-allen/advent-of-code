<?php
/**
 * Day 25: Full of Hot Air
 */
class Day25 {

	private array $snafu_conversion = array(
		-2 => '=',
		-1 => '-',
		0  => 0,
		1  => 1,
		2  => 2,
	);

	private array $data;

	public function __construct( $test ) {
		$this->parse_data( $test );
	}

	/**
	 * Part 1: Convert SNAFU numbers to int and sum them. Return the result as a SNAFU number.
	 *
	 * @return string
	 */
	public function part_1(): string {
		$total = 0;

		foreach ( $this->data as $snafu ) {
			$total += $this->snafu_to_int( $snafu );
		}

		return $this->int_to_snafu( $total );
	}

	/**
	 * Converts a snafu to a number.
	 *
	 * Basically, the 1's place is multiplied by 1 (5^0), the next place is multiplied by 5 (5^1), etc. Seems to work.
	 *
	 * @param $snafu string The snafu to convert.
	 *
	 * @return float|int
	 */
	private function snafu_to_int( $snafu ) {
		// Reverse the numbers so the 1's place is first
		$snafu  = array_reverse( str_split( $snafu ) );
		$number = 0;

		// Loop over each character.
		foreach ( $snafu as $place => $value ) {
			// Need to flip the array so the integers are the values.
			$snafu_conversion = array_flip( $this->snafu_conversion );

			// Get the int version of the snafu character.
			$int = $snafu_conversion[ $value ];

			// Multiply the int by 5 to the power of the place.
			$result = $int * pow( 5, $place );

			// Add the result to the total.
			$number += $result;
		}

		return $number;
	}

	/**
	 * Converts a number to a snafu.
	 *
	 * @param $int int The number to convert.
	 *
	 * @return string
	 */
	private function int_to_snafu( $int ) {
		$snafu = '';

		// Store the carry over from the division.
		$carry = 0;

		// Loop until the int is reduced to 0.
		while ( $int > 0 ) {
			// Modulo 5 and get the remainder.
			$remainder = $int % 5 + $carry;

			// Remainder is 0, 1, 2, 3, or 4.
			// If it's greater than 2, we need to carry over 1 to the next place, because the snafu numbers are
			// -2, -1, 0, 1, 2.
			$carry = $remainder > 2 ? 1 : 0;

			// And if there's a carry, we need to subtract 5 to get it to the right number.
			// Example: 3-5 = -2
			// Example: 4-5 = -1
			if ( $carry ) {
				$remainder -= 5;
			}

			// Now just add the snafu character to the snafu string.
			$snafu .= $this->snafu_conversion[ $remainder ];

			// And set the int to the result of dividing it by 5.
			$int = intdiv( $int, 5 );
		}

		// If there's still a carry over, append it to the snafu.
		if ( $carry ) {
			$snafu .= "1";
		}

		// Reverse the order.
		return strrev( $snafu );
	}

	/**
	 * Parse the data.
	 *
	 * @param string $test The test data.
	 *
	 * @return void
	 */
	private function parse_data( string $test ) {
		$path = $test ? '/data/day-25-test.txt' : '/data/day-25.txt';

		$this->data = explode( PHP_EOL, file_get_contents( __DIR__ . $path ) );
	}
}

// Prompt if test data should be used.
while ( true ) {
	$test = trim( strtolower( readline( 'Do you want to run the test? (y/n)' ) ) );
	if ( in_array( $test, array( 'y', 'n' ), true ) ) {
		$test = 'y' === $test;
		call_user_func( "part_1", $test );
		break;
	}
	echo 'Please enter y or n' . PHP_EOL;
}

function part_1( $test = false ) {
	$start  = microtime( true );
	$day25  = new Day25( $test );
	$result = $day25->part_1();
	$end    = microtime( true );

	printf( 'SNAFU: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}
