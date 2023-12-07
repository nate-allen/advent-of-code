<?php

/**
 * Day 07: Camel Cards
 */
class Day07 {
	private array $data;

	private string $card_values = '23456789TJQKA';

	private int $part;

	public function __construct( $part, $test ) {
		$this->parse_data( $test, $part );
		$this->part = $part;

		if ( 2 === $part ) {
			$this->card_values = 'J23456789TQKA';
		}
	}

	/**
	 * Part 1: Find the rank of every hand in your set. What are the total winnings?
	 *
	 * @return int The total winnings.
	 */
	public function part_1(): int {
		usort( $this->data, [ $this, 'compare_hands' ] );

		return $this->calculate_winnings();
	}

	/**
	 * Part 2: J's are now wild. What are the total winnings?
	 *
	 * @return int The total winnings.
	 */
	public function part_2(): int {
		usort( $this->data, [ $this, 'compare_hands' ] );

		return $this->calculate_winnings();
	}

	/**
	 * Evaluates the strength of a hand.
	 *
	 * @param string $hand The hand to evaluate.
	 *
	 * @return array
	 */
	private function evaluate_hand_strength( string $hand ): array {
		// Extract the cards and the bid
		[ $cards, $bid ] = explode( ' ', $hand );
		$cards = str_split( $cards );

		// Count the occurrences of each card
		$counts = array_count_values( $cards );

		if ( 2 === $this->part ) {
			$joker_count = $counts['J'] ?? 0;
			unset( $counts['J'] );

			// If there are Jokers, use them to form the strongest hand
			if ( $joker_count > 0 ) {
				$this->apply_jokers( $counts, $joker_count );
			}
		}

		// Determine the hand type based on the counts
		$hand_type = $this->determine_hand_type( $counts );

		return [ 'type' => $hand_type, 'cards' => $cards, 'bid' => (int) $bid ];
	}

	/**
	 * Applies Jokers to the hand to form the strongest hand possible.
	 *
	 * @param array $counts     The counts of each card.
	 * @param int   $joker_count The number of Jokers.
	 */
	private function apply_jokers( array &$counts, int $joker_count ): void {
		// Edge case: If all cards are Jokers
		if ( $joker_count == 5 ) {
			$counts['J'] = 5; // Set Jokers as five of a kind

			return;
		}

		// Find the card with the highest count
		$highest_card_count = max( $counts );

		// If we can use Jokers to create five of a kind, do it
		if ( $joker_count + $highest_card_count >= 5 ) {
			$key            = array_search( $highest_card_count, $counts ); // Get the card with the highest count
			$counts[ $key ] = 5;

			return;
		}

		// Use Jokers to upgrade existing pairs, three-of-a-kinds, or quads
		arsort( $counts ); // Sort by frequency and value
		foreach ( $counts as $card => $count ) {
			while ( $joker_count > 0 && $count < 4 ) {
				$counts[ $card ] ++;
				$joker_count --;
			}

			if ( $joker_count === 0 ) {
				break;
			}
		}

		// If Jokers are still left, use them to create pairs
		if ( $joker_count > 0 ) {
			if ( count( $counts ) + $joker_count <= 5 ) {
				$counts['J'] = min( 2, $joker_count );
			}
		}
	}

	/**
	 * Determines the type of hand based on the counts of each card
	 *
	 * @param array $counts The counts of each card.
	 *
	 * @return int
	 */
	private function determine_hand_type( array $counts ): int {
		$unique_cards = count( $counts );
		$hand_type    = 0;

		switch ( $unique_cards ) {
			case 5:
				$hand_type = 1;
				break; // Five unique cards... just a high card
			case 4:
				$hand_type = 2;
				break; // Four unique cards means one of them is a pair
			case 3:
				$hand_type = ( max( $counts ) === 3 ) ? 4 : 3;
				break; // Three of a kind or Two pair
			case 2:
				$hand_type = ( max( $counts ) === 4 ) ? 6 : 5;
				break; // Four of a kind or Full house
			case 1:
				$hand_type = 7; // Five of a kind!
		}

		return $hand_type;
	}

	/**
	 * Compares two hands and returns 1 if the first hand is stronger, -1 if the second hand is stronger, or 0 if they are equal.
	 *
	 * @param string $hand1 The first hand.
	 * @param string $hand2 The second hand.
	 *
	 * @return int
	 */
	private function compare_hands( string $hand1, string $hand2 ): int {
		$strength1 = $this->evaluate_hand_strength( $hand1 );
		$strength2 = $this->evaluate_hand_strength( $hand2 );

		// Compare based on hand type
		if ( $strength1['type'] !== $strength2['type'] ) {
			return $strength2['type'] <=> $strength1['type'];
		}

		// If hand types are the same, compare based on card values
		foreach ( $strength1['cards'] as $index => $card ) {
			if ( $card !== $strength2['cards'][ $index ] ) {
				return $this->card_value( $strength2['cards'][ $index ] ) <=> $this->card_value( $card );
			}
		}

		return 0;
	}

	/**
	 * Determines the value of a card.
	 *
	 * @param string $card
	 *
	 * @return int
	 */
	private function card_value( string $card ): int {
		$value = strpos( $this->card_values, $card );
		return $value;
	}

	/**
	 * Calculates the total winnings for all hands.
	 *
	 * @return int
	 */
	private function calculate_winnings(): int {
		$total_winnings  = 0;
		$number_of_hands = count( $this->data );

		foreach ( $this->data as $index => $hand ) {
			$strength      = $this->evaluate_hand_strength( $hand );
			$rank          = $number_of_hands - $index;
			$total_winnings += $strength['bid'] * $rank;
		}

		return $total_winnings;
	}

	private function parse_data( string $test, int $part ): void {
		$file       = $test ? '/data/day-07-test.txt' : '/data/day-07.txt';
		$this->data = explode( PHP_EOL, file_get_contents( __DIR__ . $file ) );
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
	$day07  = new Day07( 1, $test );
	$result = $day07->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	if ( $test ) {
		$expected = 6440;
		printf( 'Expected: %s' . PHP_EOL, $expected );
		if ( $result === $expected ) {
			echo 'SUCCESS!' . PHP_EOL;
		} else {
			echo 'FAILURE!' . PHP_EOL;
		}
	}
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start = microtime( true );
	$day07 = new Day07( 2, $test );

	$result = $day07->part_2();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );

	if ( $test ) {
		$expected = 5905;
		printf( 'Expected: %s' . PHP_EOL, $expected );
		if ( $result === $expected ) {
			echo 'SUCCESS!' . PHP_EOL;
		} else {
			echo 'FAILURE!' . PHP_EOL;
		}
	}
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
