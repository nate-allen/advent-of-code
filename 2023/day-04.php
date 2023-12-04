<?php

/**
 * Day 04: Gear Ratios
 */
class Day04 {

	public array $cards = [];

	public function __construct( $part, $test ) {
		$data = $this->parse_data( $test );
		$cards = $this->parse_cards( $data );
	}

	/**
	 * Part 1: How many points are the scratch cards worth in total?
	 *
	 * @return int The answer.
	 */
	public function part_1(): int {
		$total = 0;

		foreach ( $this->cards as $card ) {
			$matches = count( array_intersect( $card[0], $card[1] ) );
			$points  = $matches > 0 ? pow( 2, $matches - 1 ) : 0;

			$total += $points;
		}

		return $total;
	}

	/**
	 * Part 2: Process all of the original and copied scratchcards and return the total.
	 *
	 * @return int The answer.
	 */
	public function part_2(): int {
		$total = count( $this->cards );

		for ( $i = 0; $i < count( $this->cards ); $i ++ ) {
			$total += $this->get_card_copies( $i );
		}

		return $total;
	}

	/**
	 * Returns the number of copies of a given card.
	 *
	 * @param $index int The index of the card to check.
	 *
	 * @return int Total number of copies of the card.
	 */
	private function get_card_copies( int $index = 0 ): int {
		if ( $index >= count( $this->cards ) ) {
			return 0;
		}

		$matches = count( array_intersect( $this->cards[ $index ][0], $this->cards[ $index ][1] ) );
		$copies  = 0;

		for ( $i = 1; $i <= $matches; $i ++ ) {
			$copies += 1 + $this->get_card_copies( $index + $i );
		}

		return $copies;
	}

	private function parse_cards( $data ) {
		foreach ( $data as $line ) {
			// Remove "Card #: " from the beginning of the line.
			$line = substr( $line, 7 );
			// Split by | to get the card numbers and winning numbers.
			$card = explode( '|', $line );
			// Split the card and winning numbers by space.
			$card[0] = array_values( array_filter( explode( ' ', $card[0] ) ) );
			$card[1] = array_values( array_filter( explode( ' ', $card[1] ) ) );

			$this->cards[] = $card;
		}
	}

	private function parse_data(string $test) {
		$data = $test ? '/data/day-04-test.txt' : '/data/day-04.txt';

		return explode(PHP_EOL, file_get_contents(__DIR__ . $data));
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
	$day04  = new Day04( 1, $test );
	$result = $day04->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}

function part_2( $test = false ) {
	$start = microtime( true );
	$day04 = new Day04( 2, $test );

	$result = $day04->part_2();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}
