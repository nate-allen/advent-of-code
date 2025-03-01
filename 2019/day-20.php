<?php

namespace AdventOfCode\Year2019;

/**
 * Day 20: Donut Maze
 */
class Day20 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
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
	 * Constructor.
	 *
	 * @param bool $test Whether to use test data.
	 * @param int $part Which part of the puzzle to run.
	 */
	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return int
	 */
	public function run(): int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Find the shortest path from AA to ZZ.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$maze  = $this->data['maze'];
		$start = $this->data['start'];
		$end   = $this->data['end'];

		$queue             = [];
		$visited           = [];
		$queue[]           = [ 'coord' => $start, 'steps' => 0 ];
		$visited[ $start ] = true;

		while ( ! empty( $queue ) ) {
			$current = array_shift( $queue );
			$coord   = $current['coord'];
			$steps   = $current['steps'];

			if ( $coord === $end ) {
				return $steps;
			}

			[ $x, $y ] = array_map( 'intval', explode( ',', $coord ) );
			$directions = [
				[ 1, 0 ],
				[ - 1, 0 ],
				[ 0, 1 ],
				[ 0, - 1 ],
			];
			foreach ( $directions as $dir ) {
				$nx     = $x + $dir[0];
				$ny     = $y + $dir[1];
				$ncoord = "$nx,$ny";
				if ( ! isset( $maze[ $ncoord ] ) ) {
					continue;
				}
				if ( isset( $visited[ $ncoord ] ) ) {
					continue;
				}

				$visited[ $ncoord ] = true;
				$queue[]            = [ 'coord' => $ncoord, 'steps' => $steps + 1 ];
			}

			// Teleport move.
			if ( isset( $maze[ $coord ]['teleport'] ) ) {
				$teleport_coord = $maze[ $coord ]['teleport']['dest'];
				if ( ! isset( $visited[ $teleport_coord ] ) ) {
					$visited[ $teleport_coord ] = true;
					$queue[]                    = [ 'coord' => $teleport_coord, 'steps' => $steps + 1 ];
				}
			}
		}

		return - 1;
	}

	/**
	 * Part 2: Solve the recursive maze.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$maze  = $this->data['maze'];
		$start = $this->data['start'];
		$end   = $this->data['end'];

		$queue   = [];
		$visited = [];

		// Each state: [ 'coord' => string, 'level' => int, 'steps' => int ]
		$queue[]                  = [ 'coord' => $start, 'level' => 0, 'steps' => 0 ];
		$visited[ $start . '|0' ] = true;

		while ( ! empty( $queue ) ) {
			$current = array_shift( $queue );
			$coord   = $current['coord'];
			$level   = $current['level'];
			$steps   = $current['steps'];

			// Found the end at the outermost level.
			if ( $coord === $end && $level === 0 ) {
				return $steps;
			}

			[ $x, $y ] = array_map( 'intval', explode( ',', $coord ) );

			$directions = [
				[ 1, 0 ],
				[ - 1, 0 ],
				[ 0, 1 ],
				[ 0, - 1 ],
			];

			foreach ( $directions as $dir ) {
				$nx     = $x + $dir[0];
				$ny     = $y + $dir[1];
				$ncoord = "$nx,$ny";
				if ( ! isset( $maze[ $ncoord ] ) ) {
					continue;
				}
				$state_key = $ncoord . '|' . $level;
				if ( isset( $visited[ $state_key ] ) ) {
					continue;
				}
				$visited[ $state_key ] = true;
				$queue[]               = [ 'coord' => $ncoord, 'level' => $level, 'steps' => $steps + 1 ];
			}

			// If this cell has a teleport link, use it.
			if ( isset( $maze[ $coord ]['teleport'] ) ) {
				$teleport  = $maze[ $coord ]['teleport'];
				$new_level = $level + $teleport['level_change'];
				// Only allow teleport if new level is non-negative.
				if ( $new_level < 0 ) {
					continue;
				}
				$dest      = $teleport['dest'];
				$state_key = $dest . '|' . $new_level;
				if ( isset( $visited[ $state_key ] ) ) {
					continue;
				}
				$visited[ $state_key ] = true;
				$queue[]               = [ 'coord' => $dest, 'level' => $new_level, 'steps' => $steps + 1 ];
			}
		}

		return - 1;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file      = $test ? '/data/day-20-test.txt' : '/data/day-20.txt';
		$raw_lines = explode( "\n", rtrim( file_get_contents( __DIR__ . $file ), "\n" ) );

		// Build grid: each line becomes an array of characters.
		$grid = [];
		foreach ( $raw_lines as $y => $line ) {
			$grid[ $y ] = str_split( $line );
		}

		$maze    = [];
		$portals = [];

		$max_y = count( $grid );
		$max_x = max( array_map( 'count', $grid ) );

		// Record only open tiles.
		for ( $y = 0; $y < $max_y; $y ++ ) {
			for ( $x = 0; $x < $max_x; $x ++ ) {
				$char = $grid[ $y ][ $x ] ?? ' ';
				if ( $char === '.' ) {
					$coord          = "$x,$y";
					$maze[ $coord ] = [
						'x'    => $x,
						'y'    => $y,
						'char' => $char,
					];
				}
			}
		}

		$directions = [
			[ 1, 0 ],
			[ - 1, 0 ],
			[ 0, 1 ],
			[ 0, - 1 ],
		];

		// For each open tile, check if an adjacent cell contains an uppercase letter.
		foreach ( $maze as $coord => &$cell ) {
			$x = $cell['x'];
			$y = $cell['y'];

			foreach ( $directions as $dir ) {
				$dx    = $dir[0];
				$dy    = $dir[1];
				$adj_x = $x + $dx;
				$adj_y = $y + $dy;
				if ( $adj_y < 0 || $adj_y >= $max_y || $adj_x < 0 || $adj_x >= $max_x ) {
					continue;
				}

				$adj_char = $grid[ $adj_y ][ $adj_x ] ?? ' ';
				if ( ctype_upper( $adj_char ) ) {
					// Look one more step in the same direction.
					$adj2_x = $adj_x + $dx;
					$adj2_y = $adj_y + $dy;
					if ( $adj2_y < 0 || $adj2_y >= $max_y || $adj2_x < 0 || $adj2_x >= $max_x ) {
						continue;
					}
					$adj2_char = $grid[ $adj2_y ][ $adj2_x ] ?? ' ';
					if ( ! ctype_upper( $adj2_char ) ) {
						continue;
					}

					// Reverse letter order if moving left or up.
					if ( $dx < 0 || $dy < 0 ) {
						$portal_label = $adj2_char . $adj_char;
					} else {
						$portal_label = $adj_char . $adj2_char;
					}

					$cell['portal'] = $portal_label;
					// Mark as outer if near the edge (threshold of 3 characters).
					$cell['outer']              = ( $x < 3 || $y < 3 || $x >= $max_x - 3 || $y >= $max_y - 3 );
					$portals[ $portal_label ][] = $coord;
					break;
				}
			}
		}
		unset( $cell );

		$start = null;
		$end   = null;

		// For each portal label, record start/end or link portal pairs.
		foreach ( $portals as $label => $coords ) {
			if ( $label === 'AA' ) {
				$start = $coords[0];
			} elseif ( $label === 'ZZ' ) {
				$end = $coords[0];
			} elseif ( count( $coords ) === 2 ) {
				[ $a, $b ] = $coords;
				$maze[ $a ]['teleport'] = [
					'dest'         => $b,
					'level_change' => $maze[ $a ]['outer'] ? - 1 : 1,
				];
				$maze[ $b ]['teleport'] = [
					'dest'         => $a,
					'level_change' => $maze[ $b ]['outer'] ? - 1 : 1,
				];
			}
		}

		return [
			'maze'  => $maze,
			'start' => $start,
			'end'   => $end,
		];
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param int $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start_time = microtime( true );
	$day20      = new Day20( $test, $part );
	$result     = $day20->run();
	$end_time   = microtime( true );

	$expected_values = [
		1 => [
			'test' => 77,
			'real' => 686,
		],
		2 => [
			'test' => 396,
			'real' => 8384,
		],
	];

	$yellow = "\033[33m";
	$reset  = "\033[0m";

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values[ $part ]['test'] : $expected_values[ $part ]['real'] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end_time - $start_time, 4 ) );
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
