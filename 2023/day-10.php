<?php

/**
 * Day 10:Pipe Maze
 */
class Day10 {
	/**
	 * The raw puzzle data.
	 *
	 * @var string
	 */
	private string $data;

	private int $columns = 0;

	/**
	 * The grid representation of the puzzle data.
	 *
	 * @var array
	 */
	private array $grid;

	/**
	 * Mapping of pipe characters to their respective movement offsets.
	 *
	 * @var array
	 */
	private array $pipes;

	/**
	 * Visited positions in the grid.
	 *
	 * @var array
	 */
	private array $visited = [];

	/**
	 * Day10 constructor.
	 * Initializes the puzzle data and pipes configuration.
	 *
	 * @param string $part Indicates the puzzle part.
	 * @param bool $test Indicates whether to use test data.
	 */
	public function __construct( $part, $test ) {
		$this->parse_data( $test, $part );

		$this->columns = $test ? 20 : 140;

		$this->pipes = [
			"|" => [ - $this->columns, $this->columns ],
			"-" => [ - 1, 1 ],
			"L" => [ - $this->columns, 1 ],
			"F" => [ $this->columns, 1 ],
			"7" => [ $this->columns, - 1 ],
			"J" => [ - $this->columns, - 1 ],
			"S" => [ $this->columns, - $this->columns, - 1, 1 ]
		];

		$this->grid = str_split( str_replace( "\n", '', $this->data ) );
	}

	/**
	 * Part 1: Calculates the number of steps along the loop from the start position to the farthest point.
	 *
	 * @return int The number of steps.
	 */
	public function part_1(): int {
		$position = strpos( $this->data, 'S' ) - 1;
		$distance = 0;
		$this->visited = [ $position => $distance ];

		// Loop to find the farthest point from the starting position.
		while ( ( $position = $this->get_next( $position ) ) !== null ) {
			// Increment the distance and add the new position as visited.
			$this->visited[ $position ] = ++ $distance;
		}

		// Return the farthest point.
		return count( $this->visited ) / 2;
	}

	/**
	 * Part 2: How many tiles are enclosed by the loop?
	 *
	 * @return int The answer.
	 */
	public function part_2(): int {
		return 0;
	}

	/**
	 * Determines the next position in the grid based on current position and seen positions.
	 *
	 * @param int $position Current position in the grid.
	 *
	 * @return int|null The next position or null if none found.
	 */
	private function get_next( int $position ): ?int {
		$next_positions = [];

		foreach ( $this->pipes[ $this->grid[ $position ] ] as $move ) {
			$next_positions[] = $position + $move;
		}

		if ( 'S' === $this->grid[ $position ] ) {
			foreach ( $next_positions as $next_position ) {
				foreach ( $this->pipes[ $this->grid[ $next_position ] ] as $move ) {
					if ( $next_position + $move === $position ) {
						return $next_position;
					}
				}
			}
		}

		// Return the first unvisited position as the next move.
		foreach ( $next_positions as $next_position ) {
			if ( ! isset( $this->visited[ $next_position ] ) ) {
				return $next_position;
			}
		}

		// No more unvisited positions.
		return null;
	}

	/**
	 * Parses puzzle data from the file.
	 *
	 * @param string $test Flag to use test data.
	 * @param int    $part Puzzle part, 1 or 2.
	 */
	private function parse_data( string $test, int $part ): void {
		$file       = $test ? '/data/day-10-test.txt' : '/data/day-10.txt';
		$this->data = file_get_contents( __DIR__ . $file );
	}
}

// Prompt which part to run and if it should use the test data.
while ( true ) {
	$part = trim( readline( 'Which part do you want to run? (1/2)' ) );
	if ( function_exists( "part_$part" ) ) {
		while ( true ) {
			$test = trim( strtolower( readline( 'Do you want to run the test? (y/n)' ) ) );
			if ( in_array( $test, array( 'y', 'n' ), true ) ) {
				$test = 'y' === $test;
				call_user_func( "part_$part", $test );
				break;
			}
			echo 'Please enter y or n' . PHP_EOL;
		}
		break;
	}
	echo 'Please enter 1 or 2' . PHP_EOL;
}

function part_1( $test = false ) {
	$start    = microtime( true );
	$day10    = new Day10( 1, $test );
	$result   = $day10->part_1();
	$end      = microtime( true );
	$expected = $test ? 80 : 6897;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day10    = new Day10( 2, $test );
	$result   = $day10->part_2();
	$end      = microtime( true );
	$expected = $test ? 10 : 367;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
