<?php

namespace AdventOfCode\Year2019;

/**
 * Day 22: Slam Shuffle
 */
class Day22 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var integer
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

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return integer
	 */
	public function run(): int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Shuffle the deck of 10007 cards and determine the position of card 2019
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$deck_size = $this->is_test ? 10 : 10007;

		// Create a deck of cards.
		$deck = range( 0, $deck_size - 1 );

		// Process each shuffle instruction.
		foreach ( $this->data as $instruction ) {
			$instruction = trim( $instruction );
			if ( $instruction === '' ) {
				continue;
			}

			// "deal into new stack" reverses the deck.
			if ( str_contains( $instruction, 'deal into new stack' ) ) {
				$deck = array_reverse( $deck );
			} // "cut N" moves the top N cards to the bottom (or the bottom N cards to the top if N is negative).
			elseif ( str_starts_with( $instruction, 'cut' ) ) {
				$parts = explode( ' ', $instruction );
				$n     = (int) $parts[1];

				if ( $n >= 0 ) {
					// Cut the top n cards and append them to the bottom.
					$deck = array_merge( array_slice( $deck, $n ), array_slice( $deck, 0, $n ) );
				} else {
					// For negative n, take n cards from the bottom and move them to the top.
					$n    = abs( $n );
					$deck = array_merge( array_slice( $deck, - $n ), array_slice( $deck, 0, - $n ) );
				}
			} // "deal with increment N" deals the cards into a new deck based on the specified increment.
			elseif ( str_starts_with( $instruction, 'deal with increment' ) ) {
				$parts     = explode( ' ', $instruction );
				$increment = (int) end( $parts );
				$new_deck  = array_fill( 0, $deck_size, 0 );
				$position  = 0;
				foreach ( $deck as $card ) {
					$new_deck[ $position ] = $card;
					$position              = ( $position + $increment ) % $deck_size;
				}
				$deck = $new_deck;
			}
		}

		// Return the index of card 2019 (or 6 for test data) in the shuffled deck.
		$position = $this->is_test ? 6 : 2019;

		return array_search( $position, $deck );
	}

	/**
	 * Part 2: Determine which card ends up in position 2020 after 101741582076661 shuffles
	 * on a deck of 119315717514047 cards.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		// Use GMP for large-number arithmetic.
		$deck_size      = gmp_init('119315717514047');
		$shuffles       = gmp_init('101741582076661');
		$target_position = gmp_init('2020');

		$a = gmp_init('1');
		$b = gmp_init('0');

		$instructions = array_reverse( $this->data );
		foreach ( $instructions as $instruction ) {
			$instruction = trim( $instruction );
			if ( $instruction === '' ) {
				continue;
			}

			if ( str_contains( $instruction, 'deal into new stack' ) ) {
				$A = gmp_sub( $deck_size, 1 );
				$B = gmp_sub( $deck_size, 1 );
			} elseif ( str_starts_with( $instruction, 'cut' ) ) {
				$parts = explode( ' ', $instruction );
				$n     = gmp_init( $parts[1] );
				$A     = gmp_init('1');
				$B     = $n;
			} elseif ( str_starts_with( $instruction, 'deal with increment' ) ) {
				$parts = explode( ' ', $instruction );
				$n     = gmp_init( end( $parts ) );
				$inv   = gmp_invert( $n, $deck_size );
				$A     = $inv;
				$B     = gmp_init('0');
			} else {
				continue;
			}

			$a = gmp_mod( gmp_mul( $A, $a ), $deck_size );
			$b = gmp_mod( gmp_add( gmp_mul( $A, $b ), $B ), $deck_size );
		}

		$a_n = gmp_powm( $a, $shuffles, $deck_size );
		if ( gmp_cmp( $a, 1 ) === 0 ) {
			$sum = $shuffles;
		} else {
			$numerator = gmp_sub( $a_n, 1 );
			$denom     = gmp_sub( $a, 1 );
			$denom_inv = gmp_invert( $denom, $deck_size );
			$sum       = gmp_mod( gmp_mul( $numerator, $denom_inv ), $deck_size );
		}

		$result = gmp_mod(
			gmp_add( gmp_mul( $a_n, $target_position ), gmp_mul( $b, $sum ) ),
			$deck_size
		);

		return (int) gmp_strval( $result );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-22-test.txt' : '/data/day-22.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return $lines;
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day22  = new Day22( $test, $part );
	$result = $day22->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 9,
			'real' => 7665,
		],
		2 => [
			'test' => 117607927195067,
			'real' => 41653717360577,
		],
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values[ $part ]['test'] : $expected_values[ $part ]['real'] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for part and test mode
while ( true ) {
	$part = (int) trim( readline( 'Which part do you want to run? (1/2): ' ) );
	if ( ! in_array( $part, [ 1, 2 ], true ) ) {
		echo 'Invalid part. Please enter 1 or 2.' . PHP_EOL;
		continue;
	}

	while ( true ) {
		$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
		if ( in_array( $test, [ 'y', 'n' ], true ) ) {
			$test_mode = $test === 'y';
			run_part( $part, $test_mode );
			break 2;
		}
		echo 'Invalid input. Please enter y or n.' . PHP_EOL;
	}
}
