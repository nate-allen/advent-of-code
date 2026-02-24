<?php

namespace AdventOfCode\Year2018;

/**
 * Day 13: Mine Cart Madness
 */
class Day13 {
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

	/**
	 * Flat track string (with newlines removed).
	 *
	 * @var string
	 */
	private string $plan;

	/**
	 * Track width (for position to x,y conversion).
	 *
	 * @var int
	 */
	private int $width;

	/**
	 * Carts array with position (flat integer), direction (char), and lastturn.
	 *
	 * @var array
	 */
	private array $carts;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test, $this->part );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return string
	 */
	public function run(): string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Find the location of the first crash
	 *
	 * @return string
	 */
	private function solve_part_1(): string {
		$this->plan  = $this->data['plan'];
		$this->width = $this->data['width'];
		$this->carts = $this->data['carts'];

		// Movement directions: ^=up, >=right, v=down, <=left
		$directions = [
			'^' => -$this->width,
			'>' => 1,
			'v' => $this->width,
			'<' => -1,
		];

		while ( true ) {
			// Sort carts by position
			usort(
				$this->carts,
				function( $a, $b ) {
					return $a['position'] <=> $b['position'];
				}
			);

			foreach ( $this->carts as $cart_id => &$cart ) {
				// Move cart one step
				$cart['position'] += $directions[ $cart['direction'] ];

				// Check for collision
				foreach ( $this->carts as $other_id => $other_cart ) {
					if ( $cart_id !== $other_id
						&& $cart['position'] === $other_cart['position'] ) {
						// Collision detected! Convert position to x,y
						$x = $cart['position'] % $this->width;
						$y = (int) floor( $cart['position'] / $this->width );
						return $x . ',' . $y;
					}
				}

				// Update direction based on track piece
				$track_piece = $this->plan[ $cart['position'] ];
				$this->update_direction_flat( $cart, $track_piece );
			}
		}
	}

	/**
	 * Part 2: Find the location of the last remaining cart
	 *
	 * @return string Location in "x,y" format
	 */
	private function solve_part_2(): string {
		$this->plan  = $this->data['plan'];
		$this->width = $this->data['width'];
		$this->carts = $this->data['carts'];

		// Movement directions: ^=up, >=right, v=down, <=left
		$directions = [
			'^' => -$this->width,
			'>' => 1,
			'v' => $this->width,
			'<' => -1,
		];

		$cart_count = count( $this->carts );
		while ( $cart_count > 1 ) {
			$killed_carts = [];

			foreach ( $this->carts as $cart_id => &$cart ) {
				// Skip already killed carts
				if ( isset( $killed_carts[ $cart_id ] ) ) {
					continue;
				}

				// Move cart one step
				$cart['position'] += $directions[ $cart['direction'] ];

				// Check collision with ALL other carts
				foreach ( $this->carts as $other_id => $other_cart ) {
					if ( $cart_id !== $other_id
						&& ! isset( $killed_carts[ $other_id ] )
						&& $cart['position'] === $other_cart['position'] ) {
						// Collision detected! Mark both carts as killed
						$killed_carts[ $cart_id ]  = true;
						$killed_carts[ $other_id ] = true;
						continue 2; // Skip rest of processing for this cart
					}
				}

				if ( isset( $killed_carts[ $cart_id ] ) ) {
					continue; // Skip direction update for killed carts
				}

				// Update direction based on track piece
				$track_piece = $this->plan[ $cart['position'] ];
				$this->update_direction_flat( $cart, $track_piece );
			}

			// Remove killed carts
			foreach ( $killed_carts as $cart_id => $dummy ) {
				unset( $this->carts[ $cart_id ] );
			}

			// Sort carts by position
			usort(
				$this->carts,
				function( $a, $b ) {
					return $a['position'] <=> $b['position'];
				}
			);
			
			$cart_count = count( $this->carts );
		}

		// Convert final position to x,y
		$final_pos = $this->carts[0]['position'];
		$x         = $final_pos % $this->width;
		$y         = (int) floor( $final_pos / $this->width );
		return $x . ',' . $y;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 * @param int  $part The puzzle part (1 or 2).
	 *
	 * @return array
	 */
	private function parse_data( bool $test, int $part ): array {
		$file  = $test ? ($part === 1 ? '/data/day-13-test1.txt' : '/data/day-13-test2.txt') : '/data/day-13.txt';
		$input = file_get_contents( __DIR__ . $file );
		$width = strpos( $input, "\n" );
		$plan  = str_replace( "\n", '', $input );

		$carts = [];

		// Get all cart positions and directions, clean up plan
		for ( $i = 0; $i < strlen( $plan ); $i++ ) {
			$cell = $plan[ $i ];

			switch ( $cell ) {
				case '^':
				case 'v':
					$plan[ $i ] = '|';
					break;
				case '>':
				case '<':
					$plan[ $i ] = '-';
					break;
				default:
					continue 2;
			}

			$carts[] = [
				'position'  => $i,
				'direction' => $cell,
				'lastturn'  => 0,
			];
		}

		return [
			'plan'  => $plan,
			'width' => $width,
			'carts' => $carts,
		];
	}

	/**
	 * Updates cart direction based on track piece.
	 *
	 * @param array  $cart        Cart reference (position, direction, lastturn).
	 * @param string $track_piece Track piece: /, \, or +.
	 */
	private function update_direction_flat( array &$cart, string $track_piece ): void {
		switch ( $track_piece ) {
			case '/':
				switch ( $cart['direction'] ) {
					case '^':
						$cart['direction'] = '>';
						break;
					case '>':
						$cart['direction'] = '^';
						break;
					case 'v':
						$cart['direction'] = '<';
						break;
					case '<':
						$cart['direction'] = 'v';
						break;
				}
				break;

			case '\\':
				switch ( $cart['direction'] ) {
					case '^':
						$cart['direction'] = '<';
						break;
					case '>':
						$cart['direction'] = 'v';
						break;
					case 'v':
						$cart['direction'] = '>';
						break;
					case '<':
						$cart['direction'] = '^';
						break;
				}
				break;

			case '+':
				switch ( $cart['direction'] . ($cart['lastturn'] % 3) ) {
					case '^0':
					case 'v2':
						$cart['direction'] = '<';
						break;
					case '^2':
					case 'v0':
						$cart['direction'] = '>';
						break;
					case '>0':
					case '<2':
						$cart['direction'] = '^';
						break;
					case '>2':
					case '<0':
						$cart['direction'] = 'v';
						break;
				}
				$cart['lastturn']++;
				break;
		}
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool    $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day13  = new Day13( $test, $part );
	$result = $day13->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => '7,3',
			'real' => '8,9',
		],
		2 => [
			'test' => '6,4',
			'real' => '73,33',
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
