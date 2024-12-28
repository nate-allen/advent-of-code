<?php

namespace AdventOfCode\Year2021;

/**
 * Day 04: Giant Squid
 */
class Day04 {
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
	 * Array of bingo numbers.
	 *
	 * @var array
	 */
	private array $numbers;

	/**
	 * Array of bingo cards.
	 *
	 * @var array
	 */
	private array $bingo_cards;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->parse_data( $this->is_test );
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
	 * Part 1: Loop over the bingo numbers and mark the numbers on the bingo cards with an X. If a card has a full row or
	 * column (not diagonal) keep track of the winning number that was called when the bingo was called.
	 *
	 * Add up all of the numbers that were not marked on the winning card. Multiply the sum by the winning number.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		// Keep track of marked numbers on each card
		$marked_cards = array_map( fn( $card ) => array_fill( 0, 5, array_fill( 0, 5, false ) ), $this->bingo_cards );

		foreach ( $this->numbers as $number ) {
			foreach ( $this->bingo_cards as $card_index => $card ) {
				// Check if the number is on the card and mark it
				for ( $row = 0; $row < 5; $row ++ ) {
					for ( $col = 0; $col < 5; $col ++ ) {
						if ( $card[ $row ][ $col ] === (int) $number ) {
							$marked_cards[ $card_index ][ $row ][ $col ] = true;
						}
					}
				}

				// Check if the card is a winner.
				if ( $this->is_winner( $marked_cards[ $card_index ] ) ) {
					return $this->calculate_score( $card, $marked_cards[ $card_index ], $number );
				}
			}
		}

		// Should never reach this point lol
		return 0;
	}

	/**
	 * Part 2: Figure out which board will win last. Once it wins, what would its final score be?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// Keep track of marked numbers on each card
		$marked_cards = array_map( fn( $card ) => array_fill( 0, 5, array_fill( 0, 5, false ) ), $this->bingo_cards );

		foreach ( $this->numbers as $number ) {
			foreach ( $this->bingo_cards as $card_index => $card ) {
				// Check if the number is on the card and mark it
				for ( $row = 0; $row < 5; $row ++ ) {
					for ( $col = 0; $col < 5; $col ++ ) {
						if ( $card[ $row ][ $col ] === (int) $number ) {
							$marked_cards[ $card_index ][ $row ][ $col ] = true;
						}
					}
				}

				// Check if the card is a winner.
				if ( $this->is_winner( $marked_cards[ $card_index ] ) ) {
					// Check if this is the last card
					if ( count( $this->bingo_cards ) === 1 ) {
						return $this->calculate_score( $card, $marked_cards[ $card_index ], $number );
					}

					// This isn't the last card so remove it.
					unset( $this->bingo_cards[ $card_index ] );
					unset( $marked_cards[ $card_index ] );
				}
			}
		}
	}

	/**
	 * Check if the card is a winner by checking if any row or column has all numbers marked.
	 *
	 * @param array $marked_card The card with marked numbers.
	 *
	 * @return bool
	 */
	private function is_winner( array $marked_card ): bool {
		// Check rows
		foreach ( $marked_card as $row ) {
			if ( array_sum( $row ) === 5 ) {
				return true;
			}
		}

		// Check columns
		for ( $col = 0; $col < 5; $col ++ ) {
			$column_sum = 0;
			for ( $row = 0; $row < 5; $row ++ ) {
				$column_sum += $marked_card[ $row ][ $col ];
			}
			if ( $column_sum === 5 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Calculate the score of the winning card by multiplying the sum of the unmarked numbers by the last number called.
	 *
	 * @param array   $card        The winning card.
	 * @param array   $marked_card The card with marked numbers.
	 * @param integer $last_number The last number called.
	 *
	 * @return integer
	 */
	private function calculate_score( array $card, array $marked_card, int $last_number ): int {
		$unmarked_sum = 0;

		for ( $row = 0; $row < 5; $row ++ ) {
			for ( $col = 0; $col < 5; $col ++ ) {
				if ( ! $marked_card[ $row ][ $col ] ) {
					$unmarked_sum += $card[ $row ][ $col ];
				}
			}
		}

		return $unmarked_sum * $last_number;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return void
	 */
	private function parse_data( bool $test ): void {
		$file   = $test ? '/data/day-04-test.txt' : '/data/day-04.txt';
		$blocks = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$this->numbers = explode( ',', array_shift( $blocks ) );

		foreach ( $blocks as $block ) {
			$lines = explode( "\n", $block );

			// Initialize an empty array for the bingo card
			$bingo_card_array = [];

			// Loop through each line and split it into numbers
			foreach ( $lines as $line ) {
				// Split the line by spaces and filter out any empty values
				$row = array_values( array_filter( explode( " ", $line ), function ( $value ) {
					return trim( $value ) !== '';
				} ) );

				// Convert values to integers and add the row to the bingo card array
				$bingo_card_array[] = array_map( 'intval', $row );
			}

			// Add the bingo card to the bingo cards array
			$this->bingo_cards[] = $bingo_card_array;
		}
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
	$day04  = new Day04( $test, $part );
	$result = $day04->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 4512,
			'real' => 74320,
		],
		2 => [
			'test' => 1924,
			'real' => 17884,
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
