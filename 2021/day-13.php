<?php

namespace AdventOfCode\Year2021;

/**
 * Day 13: Transparent Origami
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
	 * Coordinates of the dots.
	 *
	 * @var array
	 */
	private array $dots;

	/**
	 * Folding instructions.
	 *
	 * @var array
	 */
	private array $fold_instructions;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;

		$this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return integer|string
	 */
	public function run(): int|string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Perform one fold and return the number of unique dots.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$folded_dots = $this->fold_paper( [ $this->fold_instructions[0] ] );

		return count( $folded_dots );
	}

	/**
	 * Part 2: Finish folding the paper to reveal eight capital letters.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		$folded_dots = $this->fold_paper( $this->fold_instructions );

		// Find the boundaries of the folded paper
		$min_x = min( array_column( $folded_dots, 0 ) );
		$max_x = max( array_column( $folded_dots, 0 ) );
		$min_y = min( array_column( $folded_dots, 1 ) );
		$max_y = max( array_column( $folded_dots, 1 ) );

		$output = '';

		for ( $y = $min_y; $y <= $max_y; $y ++ ) {
			$output .= PHP_EOL;
			for ( $x = $min_x; $x <= $max_x; $x ++ ) {
				$output .= isset( $folded_dots["$x,$y"] ) ? '#' : '.';
			}
		}

		return $output;
	}

	/**
	 * Applies the fold instructions to the dots.
	 *
	 * @param array $folds Array of fold instructions to apply.
	 *
	 * @return array
	 */
	private function fold_paper( array $folds ): array {
		$folded_dots = $this->dots;

		foreach ( $folds as $fold ) {
			[ $fold_axis, $fold_position ] = $fold;

			$new_dots = [];
			foreach ( $folded_dots as $dot ) {
				[ $x, $y ] = $dot;

				if ( $fold_axis === 'x' && $x > $fold_position ) {
					$x = $fold_position - ( $x - $fold_position );
				} elseif ( $fold_axis === 'y' && $y > $fold_position ) {
					$y = $fold_position - ( $y - $fold_position );
				}

				$new_dots["$x,$y"] = [ $x, $y ];
			}

			$folded_dots = $new_dots;
		}

		return $folded_dots;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 */
	private function parse_data( bool $test ) {
		$file  = $test ? '/data/day-13-test.txt' : '/data/day-13.txt';
		[ $dots, $folds ] = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) );

		foreach ( explode( "\n", $dots ) as $dot ) {
			[ $x, $y ] = explode( ',', $dot );
			$this->dots[] = [ (int) $x, (int) $y ];
		}

		foreach( explode( "\n", $folds ) as $fold ) {
			[ $axis, $position ] = explode( '=', $fold );
			$this->fold_instructions[] = [ substr( $axis, - 1 ), (int) $position ];
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
			'test' => 17,
			'real' => 788,
		],
		2 => [
			'test' => "\n#####\n#...#\n#...#\n#...#\n#####",
			'real' => "\n#..#...##.###..#..#.####.#..#.###...##.\n#.#.....#.#..#.#.#..#....#..#.#..#.#..#\n##......#.###..##...###..#..#.###..#...\n#.#.....#.#..#.#.#..#....#..#.#..#.#.##\n#.#..#..#.#..#.#.#..#....#..#.#..#.#..#\n#..#..##..###..#..#.####..##..###...###",
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
