<?php

namespace AdventOfCode\Year2018;

/**
 * Day 17: Reservoir Research
 */
class Day17 {
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
	 * Set of clay positions (keys: "x,y").
	 *
	 * @var array
	 */
	private array $clay = [];

	/**
	 * Map of water positions and their states ('|' for flowing, '~' for settled).
	 *
	 * @var array
	 */
	private array $water = [];

	/**
	 * Minimum y coordinate in the scan.
	 *
	 * @var int
	 */
	private int $min_y;

	/**
	 * Maximum y coordinate in the scan.
	 *
	 * @var int
	 */
	private int $max_y;

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
	 * Part 1: Count all tiles that can be reached by water
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$this->clay  = $this->data['clay'];
		$this->min_y = $this->data['min_y'];
		$this->max_y = $this->data['max_y'];
		$this->water = [];

		// Start water flow from spring at (500, 0)
		$this->flow_down( 500, 0 );

		// Count all water tiles (both | and ~) within y-range
		$count = 0;
		foreach ( $this->water as $pos => $state ) {
			list( $x, $y ) = explode( ',', $pos );
			$y             = (int) $y;
			if ( $y >= $this->min_y && $y <= $this->max_y ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Part 2: Count only settled water tiles that are retained
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$this->clay  = $this->data['clay'];
		$this->min_y = $this->data['min_y'];
		$this->max_y = $this->data['max_y'];
		$this->water = [];

		// Start water flow from spring at (500, 0)
		$this->flow_down( 500, 0 );

		// Count only settled water tiles (~) within y-range
		$count = 0;
		foreach ( $this->water as $pos => $state ) {
			if ( $state === '~' ) {
				list( $x, $y ) = explode( ',', $pos );
				$y             = (int) $y;
				if ( $y >= $this->min_y && $y <= $this->max_y ) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-17-test.txt' : '/data/day-17.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$clay  = [];
		$min_y = PHP_INT_MAX;
		$max_y = PHP_INT_MIN;

		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( empty( $line ) ) {
				continue;
			}

			// Parse format: "x=495, y=2..7" or "y=7, x=495..501"
			if ( preg_match( '/x=(\d+),\s*y=(\d+)\.\.(\d+)/', $line, $matches ) ) {
				$x  = (int) $matches[1];
				$y1 = (int) $matches[2];
				$y2 = (int) $matches[3];

				for ( $y = $y1; $y <= $y2; $y++ ) {
					$clay[ "$x,$y" ] = true;
					$min_y           = min( $min_y, $y );
					$max_y           = max( $max_y, $y );
				}
			} elseif ( preg_match( '/y=(\d+),\s*x=(\d+)\.\.(\d+)/', $line, $matches ) ) {
				$y  = (int) $matches[1];
				$x1 = (int) $matches[2];
				$x2 = (int) $matches[3];

				for ( $x = $x1; $x <= $x2; $x++ ) {
					$clay[ "$x,$y" ] = true;
					$min_y           = min( $min_y, $y );
					$max_y           = max( $max_y, $y );
				}
			}
		}

		return [
			'clay'  => $clay,
			'min_y' => $min_y,
			'max_y' => $max_y,
		];
	}

	/**
	 * Makes water flow down from the given position.
	 *
	 * @param int $x X coordinate.
	 * @param int $y Y coordinate.
	 *
	 * @return void
	 */
	private function flow_down( int $x, int $y ): void {
		// If we're below max_y, we're out of bounds
		if ( $y > $this->max_y ) {
			return;
		}

		$pos = "$x,$y";

		// If this position is clay or already has settled water, stop
		if ( isset($this->clay[$pos]) || (isset($this->water[$pos]) && $this->water[$pos] === '~') ) {
			return;
		}

		// Mark as flowing water if not already marked
		if ( ! isset($this->water[$pos]) ) {
			$this->water[$pos] = '|';
		}

		// Check position below
		$below = "$x," . ($y + 1);

		// If below is empty (not clay, not settled water), continue flowing down
		if ( ! isset($this->clay[$below]) && (! isset($this->water[$below]) || $this->water[$below] !== '~') ) {
			$this->flow_down( $x, $y + 1 );
		}

		// After falling (or being blocked), try to spread sideways
		// Only spread if the position below is clay or settled water
		if ( isset($this->clay[$below]) || (isset($this->water[$below]) && $this->water[$below] === '~') ) {
			$this->spread_sideways( $x, $y );
		}
	}

	/**
	 * Spreads water sideways from the given position.
	 *
	 * @param int $x X coordinate.
	 * @param int $y Y coordinate.
	 *
	 * @return void
	 */
	private function spread_sideways( int $x, int $y ): void {
		$pos   = "$x,$y";
		$below = "$x," . ($y + 1);

		// Can only spread if below is blocked
		if ( ! isset($this->clay[$below]) && (! isset($this->water[$below]) || $this->water[$below] !== '~') ) {
			return;
		}

		// Spread left
		$left_bound = $x;
		$left_wall  = false;
		for ( $lx = $x - 1; ; $lx-- ) {
			$lpos   = "$lx,$y";
			$lbelow = "$lx," . ($y + 1);

			// If we hit clay, we found a wall
			if ( isset($this->clay[$lpos]) ) {
				$left_wall = true;
				break;
			}

			// Mark as flowing water
			if ( ! isset($this->water[$lpos]) ) {
				$this->water[$lpos] = '|';
			}

			// If below is not blocked, water can fall
			if ( ! isset($this->clay[$lbelow]) && (! isset($this->water[$lbelow]) || $this->water[$lbelow] !== '~') ) {
				$this->flow_down( $lx, $y );
				break;
			}

			$left_bound = $lx;
		}

		// Spread right
		$right_bound = $x;
		$right_wall  = false;
		for ( $rx = $x + 1; ; $rx++ ) {
			$rpos   = "$rx,$y";
			$rbelow = "$rx," . ($y + 1);

			// If we hit clay, we found a wall
			if ( isset($this->clay[$rpos]) ) {
				$right_wall = true;
				break;
			}

			// Mark as flowing water
			if ( ! isset($this->water[$rpos]) ) {
				$this->water[$rpos] = '|';
			}

			// If below is not blocked, water can fall
			if ( ! isset($this->clay[$rbelow]) && (! isset($this->water[$rbelow]) || $this->water[$rbelow] !== '~') ) {
				$this->flow_down( $rx, $y );
				break;
			}

			$right_bound = $rx;
		}

		// If both sides have walls, convert all water in this row to settled
		if ( $left_wall && $right_wall ) {
			for ( $sx = $left_bound; $sx <= $right_bound; $sx++ ) {
				$spos = "$sx,$y";
				if ( isset($this->water[$spos]) ) {
					$this->water[$spos] = '~';
				}
			}
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
	$day17  = new Day17( $test, $part );
	$result = $day17->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 57,
			'real' => 33362,
		],
		2 => [
			'test' => 29,
			'real' => 27801,
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
