<?php

namespace AdventOfCode\Year2024;

ini_set('memory_limit', '1024M');

/**
 * Day 16: Reindeer Maze
 */
class Day16 {
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

	private array $directions = [
		[ 0, 1 ],   // East
		[ 1, 0 ],   // South
		[ 0, - 1 ],  // West
		[ - 1, 0 ],  // North
	];

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
	 * Part 1: Analyze the map and calculate the lowest score a Reindeer could possibly get.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		return $this->find_best_path()[0];
	}

	/**
	 * Part 2: Analyze the map and calculate how many tiles are part of at least one of the best paths through the maze.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		return $this->find_best_path()[1];
	}

	/**
	 * Finds the best path through the maze.
	 *
	 * @return array
	 */
	private function find_best_path(): array {
		$map = $this->data;
		$start = $this->find_tile($map, 'S');
		$end = $this->find_tile($map, 'E');

		$queue = new \SplPriorityQueue();
		$queue->insert([$start[0], $start[1], 0, 0, []], 0);

		$visited = [];
		$paths = [];

		while (!$queue->isEmpty()) {
			[$x, $y, $facing, $current_score, $history] = $queue->extract();

			// If revisited with a worse or equal score, skip
			if (isset($visited["$x,$y,$facing"]) && $visited["$x,$y,$facing"] < $current_score) {
				continue;
			}
			$visited["$x,$y,$facing"] = $current_score;

			// Add the current tile to the history for this path
			$history["$x,$y"] = true;

			// If the end tile is reached, store the path
			if ($x === $end[0] && $y === $end[1]) {
				$paths[$current_score][] = $history;
				continue;
			}

			// Move forward in the current direction
			[$dx, $dy] = $this->directions[$facing];
			$nx = $x + $dx;
			$ny = $y + $dy;

			if ($this->is_valid_tile($map, $nx, $ny)) {
				$queue->insert([$nx, $ny, $facing, $current_score + 1, $history], -($current_score + 1));
			}

			// Rotate clockwise or counterclockwise
			foreach ([1, -1] as $turn) {
				$new_facing = ($facing + $turn + 4) % 4;
				$queue->insert([$x, $y, $new_facing, $current_score + 1000, $history], -($current_score + 1000));
			}
		}

		// Find the best score
		$best_score = min(array_keys($paths));

		// Merge unique tiles from all best paths
		$best_tiles = [];
		foreach ($paths[$best_score] as $path_history) {
			$best_tiles = array_merge($best_tiles, array_keys($path_history));
		}

		$unique_tiles = array_unique($best_tiles);

		return [$best_score, count($unique_tiles)];
	}

	/**
	 * Finds the coordinates of the specified tile in the map.
	 *
	 * @param array  $map  The map to search.
	 * @param string $tile The tile to find.
	 *
	 * @return array
	 */
	private function find_tile( array $map, string $tile ): array {
		foreach ( $map as $x => $row ) {
			$y = strpos( $row, $tile );
			if ( $y !== false ) {
				return [ $x, $y ];
			}
		}
		throw new \InvalidArgumentException( "Tile '$tile' not found in the map." );
	}

	private function is_valid_tile( array $map, int $x, int $y ): bool {
		return isset( $map[ $x ][ $y ] ) && $map[ $x ][ $y ] !== '#';
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-16-test.txt' : '/data/day-16.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return $lines;
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param int $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day16  = new Day16( $test, $part );
	$result = $day16->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 11048,
			'real' => 99488,
		],
		2 => [
			'test' => 64,
			'real' => 516,
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
