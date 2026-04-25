<?php

namespace AdventOfCode\Year2016;

/**
 * Day 08: Two-Factor Authentication
 */
class Day08 {
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
	 * The screen grid.
	 *
	 * @var array
	 */
	private array $screen;

	/**
	 * Screen width.
	 *
	 * @var int
	 */
	private int $width;

	/**
	 * Screen height.
	 *
	 * @var int
	 */
	private int $height;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->width   = $test ? 7 : 50;
		$this->height  = $test ? 3 : 6;
		$this->screen  = array_fill( 0, $this->height, array_fill( 0, $this->width, 0 ) );
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return string
	 */
	public function run(): string {
		return match ( $this->part ) {
			1 => (string) $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Count lit pixels after executing all instructions.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$this->execute();

		$count = 0;
		foreach ( $this->screen as $row ) {
			$count += array_sum( $row );
		}

		return $count;
	}

	/**
	 * Part 2: Read the code displayed on the screen.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		$this->execute();

		$output = '';
		foreach ( $this->screen as $row ) {
			$output .= implode( '', array_map( fn( $p ) => $p ? '#' : '.', $row ) ) . "\n";
		}

		return trim( $output );
	}

	/**
	 * Executes all instructions on the screen.
	 */
	private function execute(): void {
		foreach ( $this->data as $cmd ) {
			if ( $cmd['type'] === 'rect' ) {
				for ( $r = 0; $r < $cmd['b']; $r++ ) {
					for ( $c = 0; $c < $cmd['a']; $c++ ) {
						$this->screen[ $r ][ $c ] = 1;
					}
				}
			} elseif ( $cmd['type'] === 'rotate_row' ) {
				$row = $this->screen[ $cmd['a'] ];
				$new = [];
				for ( $c = 0; $c < $this->width; $c++ ) {
					$new[ ( $c + $cmd['b'] ) % $this->width ] = $row[ $c ];
				}
				ksort( $new );
				$this->screen[ $cmd['a'] ] = $new;
			} elseif ( $cmd['type'] === 'rotate_col' ) {
				$col = [];
				for ( $r = 0; $r < $this->height; $r++ ) {
					$col[] = $this->screen[ $r ][ $cmd['a'] ];
				}
				for ( $r = 0; $r < $this->height; $r++ ) {
					$this->screen[ ( $r + $cmd['b'] ) % $this->height ][ $cmd['a'] ] = $col[ $r ];
				}
			}
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-08-test.txt' : '/data/day-08.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			if ( preg_match( '/rect (\d+)x(\d+)/', $line, $m ) ) {
				return [ 'type' => 'rect', 'a' => (int) $m[1], 'b' => (int) $m[2] ];
			} elseif ( preg_match( '/rotate row y=(\d+) by (\d+)/', $line, $m ) ) {
				return [ 'type' => 'rotate_row', 'a' => (int) $m[1], 'b' => (int) $m[2] ];
			} else {
				preg_match( '/rotate column x=(\d+) by (\d+)/', $line, $m );
				return [ 'type' => 'rotate_col', 'a' => (int) $m[1], 'b' => (int) $m[2] ];
			}
		}, $lines );
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
	$day08  = new Day08( $test, $part );
	$result = $day08->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 6,
			'real' => 119,
		],
		2 => [
			'test' => 0,
			'real' => 'ZFHFSFOGPO',
		],
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	$answer_sep = str_contains( $result, "\n" ) ? PHP_EOL : '';
	printf( $yellow . 'Answer:   ' . $reset . '%s%s' . PHP_EOL, $answer_sep, $result );
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
