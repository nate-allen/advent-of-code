<?php

namespace AdventOfCode\Year2024;

/**
 * Day 09: Disk Fragmenter
 */
class Day09 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
	 */
	private integer $part;

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
	 * Part 1: Compact the disk by moving blocks and calculate the checksum.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$compacted_blocks = $this->compact_blocks( $this->data );

		return $this->calculate_checksum( $compacted_blocks );
	}

	/**
	 * Part 2: Compact the disk by moving files and calculate the checksum.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$compacted_blocks = $this->compact_files( $this->data );

		return $this->calculate_checksum( $compacted_blocks );
	}


	/**
	 * Compact blocks in an array by moving file blocks to the leftmost free spaces.
	 *
	 * @param array $blocks An array representing blocks
	 *
	 * @return array
	 */
	private function compact_blocks( array $blocks ): array {
		$file_blocks  = [];
		$free_spaces = [];

		// Organize the blocks into file blocks and free spaces.
		foreach ( $blocks as $i => $block ) {
			if ( $block === '.' ) {
				$free_spaces[] = $i;
			} else {
				$file_blocks[] = $i;
			}
		}

		// Process file blocks from right to left.
		while ( ! empty( $file_blocks ) && ! empty( $free_spaces ) ) {
			$file_pos = array_pop( $file_blocks );  // Get the rightmost file block
			$free_pos = array_shift( $free_spaces ); // Get the leftmost free space

			if ( $file_pos > $free_pos ) {
				// Move the file block to the free space.
				$blocks[ $free_pos ] = $blocks[ $file_pos ];
				$blocks[ $file_pos ] = '.';

				// Update the queue with the new free space.
				$free_spaces[] = $file_pos;
			} else {
				// If the file block is to the left of the free space, stop.
				break;
			}
		}

		return $blocks;
	}

	/**
	 * Compacts an array of blocks by shifting file blocks into the leftmost available free space.
	 *
	 * @param array $blocks An array representing the blocks
	 *
	 * @return array
	 */
	private function compact_files(array $blocks): array {
		$files        = []; // [ pos, size ]
		$free_spaces = []; // [ pos, size, file_id]

		// Step 1: Organize blocks into file groups and free space groups.
		foreach ( $blocks as $index => $block ) {
			// It's a file block, not a free space.
			if ( $block !== '.' ) {
				if ( empty( $files ) || $files[ array_key_last( $files ) ]['file_id'] !== $block ) {
					// Start a new file group if it's the first block or a different file ID.
					$files[] = [ 'pos' => $index, 'size' => 1, 'file_id' => $block ];
				} else {
					// Extend the size of the *last* file group.
					$files[ array_key_last( $files ) ]['size'] ++;
				}
			} else {
				// Start a new free space group if it's not bordering the last one.
				// If the current index is not directly adjacent to the end of the last recorded free space group
				// (the last group's starting position + its size does not equal the current index),
				// it means its the start of a new group of free spaces.
				// Otherwise, the current block is part of the existing group, and we extend its size.

				if ( empty( $free_spaces ) || $free_spaces[ array_key_last( $free_spaces ) ]['pos'] + $free_spaces[ array_key_last( $free_spaces ) ]['size'] !== $index ) {
					$free_spaces[] = [ 'pos' => $index, 'size' => 1 ];
				} else {
					$free_spaces[ array_key_last( $free_spaces ) ]['size'] ++;
				}
			}
		}

		// Step 2: Sort file groups in descending order by file ID so that higher file IDs are processed first.
		usort($files, fn($a, $b) => $b['file_id'] <=> $a['file_id']);

		// Step 3: Move files into the leftmost available free spaces.
		foreach ($files as $file) {
			foreach ($free_spaces as &$space) {
				// Check if the free space is to the left of the file and large enough.
				if ($space['pos'] < $file['pos'] && $file['size'] <= $space['size']) {
					// Move file to the free space by swapping the file blocks into the free space.
					for ($i = 0; $i < $file['size']; $i++) {
						$blocks[$file['pos'] + $i] = '.'; // Mark original position as free
						$blocks[$space['pos'] + $i] = $file['file_id']; // Move file to free space
					}

					// Update the free space.
					$space['pos'] += $file['size'];
					$space['size'] -= $file['size'];
					break;
				}
			}
		}

		return $blocks;
	}

	/**
	 * Calculates the checksum by adding up the result of multiplying the blocks' position with the file ID number.
	 *
	 * @param array $blocks
	 *
	 * @return int
	 */
	private function calculate_checksum(array $blocks): int {
		$checksum = 0;

		foreach ($blocks as $position => $block) {
			if ( '.' === $block ) {
				continue;
			}

			$checksum += $position * (int) $block;
		}

		return $checksum;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-09-test.txt' : '/data/day-09.txt';

		$blocks  = [];
		$file_id = 0;
		$lengths = str_split( trim( file_get_contents( __DIR__ . $file ) ) );

		foreach ( $lengths as $index => $length ) {
			$length = (int) $length;
			if ( $index % 2 === 0 ) {
				// File blocks
				for ( $i = 0; $i < $length; $i ++ ) {
					$blocks[] = $file_id;
				}
				$file_id ++;
			} else {
				// Free space
				for ( $i = 0; $i < $length; $i ++ ) {
					$blocks[] = '.';
				}
			}
		}

		return $blocks;
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
	$day09  = new Day09( $test, $part );
	$result = $day09->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 1928,
			'real' => 6279058075753,
		],
		2 => [
			'test' => 2858,
			'real' => 6301361958738,
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
