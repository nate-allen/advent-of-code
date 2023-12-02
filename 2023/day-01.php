<?php

/**
 * Day 01: Trebuchet?!
 */
class Day01 {

	/**
	 * The data.
	 *
	 * @var array
	 */
	private array $data;

	public function __construct( $part, $test ) {
		$this->parse_data( $test );
	}

	/**
	 * Part 1: Combine the first digit and the last digit (in that order) to form a single two-digit number.
	 *
	 * @return int
	 */
	public function part_1(): int {
		$sum = 0;
		foreach ($this->data as $line) {
			// Remove all non-digit characters from the line
			$line = preg_replace('/\D/', '', $line);

			if ($line === '') {
				continue;
			}

			// Get the first and last digits
			$firstDigit = $line[0];
			$lastDigit = $line[strlen($line) - 1];

			// Combine them to form a two-digit number and add to the sum
			$sum += intval($firstDigit . $lastDigit);
		}

		return $sum;
	}

	public function part_2(): int {
		$sum = 0;
		$numberWords = [
			'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'
		];

		foreach ($this->data as $line) {
			$firstDigit = $this->findFirstDigit($line, $numberWords);
			$lastDigit = $this->findLastDigit($line, $numberWords);

			if ($firstDigit !== null && $lastDigit !== null) {
				// Combine them to form a two-digit number and add to the sum
				$sum += intval($firstDigit . $lastDigit);
			}
		}

		return $sum;
	}

	private function findFirstDigit($line, $numberWords) {
		// Regular expression to match a digit or any of the spelled-out numbers
		$regex = '/\d|' . implode('|', $numberWords) . '/';
		echo $regex . PHP_EOL;
		if (preg_match($regex, $line, $matches)) {
			// Return the digit or the corresponding digit for a spelled-out number
			return is_numeric($matches[0]) ? $matches[0] : array_search($matches[0], $numberWords);
		}
		return null;
	}

	private function findLastDigit($line, $numberWords) {
		// Reverse the string to find the last digit using the same method as for the first digit
		$reversedLine = strrev($line);
		$lastDigit = $this->findFirstDigit($reversedLine, array_map('strrev', $numberWords));
		return $lastDigit;
	}

	/**
	 * Parse the data.
	 *
	 * @param string $test The test data.
	 *
	 * @return void
	 */
	private function parse_data( string $test ) {
		$data = $test ? '/data/day-01-test.txt' : '/data/day-01.txt';

		$this->data = explode( PHP_EOL, file_get_contents( __DIR__ . $data ) );
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
	$day01  = new Day01( 1, $test );
	$result = $day01->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}

function part_2( $test = false ) {
	$start = microtime( true );
	$day01 = new Day01( 2, $test );

	$result = $day01->part_2();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}
