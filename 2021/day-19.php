<?php

namespace AdventOfCode\Year2021;

/**
 * Day 19: Beacon Scanner
 */
class Day19 {
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
	 * Array of scanners and their beacon coordinates.
	 *
	 * @var array
	 */
	private array $scanners = [];

	/**
	 * The total count of unique beacons.
	 *
	 * @var int
	 */
	private int $unique_beacons = 0;

	/**
	 * The maximum Manhattan distance between scanners.
	 *
	 * @var int
	 */
	private int $max_distance = 0;

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
	 * Part 1: Count the number of unique beacons.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$this->align_scanners();

		return $this->unique_beacons;
	}

	/**
	 * Part 2: Find the largest Manhattan distance between any two scanners.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$this->align_scanners();

		return $this->max_distance;
	}

	/**
	 * Aligns all scanners to a common rotation.
	 *
	 * @return void
	 */
	private function align_scanners(): void {
		$num_scanners = count( $this->scanners );

		// Build string representations of each beacon
		$beacon_keys = [];
		foreach ( $this->scanners as $i => $data ) {
			$beacon_keys[ $i ] = array_map(
				fn( $coords ) => implode( ',', $coords ),
				$data
			);
		}

		$diffs      = [];
		$diff_pairs = [];
		for ( $i = 0; $i < $num_scanners; $i ++ ) {
			$scan_diff        = [];
			$diff_pairs[ $i ] = [];

			$scanner = $this->scanners[ $i ];
			$size    = count( $scanner );

			for ( $x = 0; $x < $size; $x ++ ) {
				for ( $y = $x + 1; $y < $size; $y ++ ) {
					$a           = $scanner[ $x ];
					$b           = $scanner[ $y ];
					$distance    = ( $b[0] - $a[0] ) ** 2 + ( $b[1] - $a[1] ) ** 2 + ( $b[2] - $a[2] ) ** 2;
					$scan_diff[] = $distance;

					// Keep track of which beacon pair yields this distance.
					$diff_pairs[ $i ][ $distance ] = [ $a, $b ];
				}
			}
			sort( $scan_diff );
			$diffs[ $i ] = $scan_diff;
		}

		$locations    = [];
		$locations[0] = [ 0, 0, 0 ];

		$found  = [ 0 ];
		$toScan = [ 0 ];

		// Use BFS to align each undiscovered scanner to the known set.
		while ( ! empty( $toScan ) ) {
			$i = array_pop( $toScan );

			for ( $j = 0; $j < $num_scanners; $j ++ ) {
				if ( in_array( $j, $found, true ) ) {
					continue;
				}

				// Compare the sets of distances for scanners i and j.
				$intersection = array_intersect( $diffs[ $i ], $diffs[ $j ] );

				if ( count( $intersection ) >= 66 ) {
					// Attempt to find rotation and offset for scanner j, so it aligns with i.
					$temp    = [];
					$offsets = [];

					foreach ( $intersection as $dist ) {
						[ $ia, $ib ] = $diff_pairs[ $i ][ $dist ];
						[ $ja, $jb ] = $diff_pairs[ $j ][ $dist ];

						$key_ia = implode( ',', $ia );
						$key_ib = implode( ',', $ib );
						$key_ja = implode( ',', $ja );
						$key_jb = implode( ',', $jb );

						// Each overlapping distance yields four possible pairings.
						$temp["{$key_ia}|{$key_ja}"] = ( $temp["{$key_ia}|{$key_ja}"] ?? 0 ) + 1;
						$temp["{$key_ib}|{$key_ja}"] = ( $temp["{$key_ib}|{$key_ja}"] ?? 0 ) + 1;
						$temp["{$key_ia}|{$key_jb}"] = ( $temp["{$key_ia}|{$key_jb}"] ?? 0 ) + 1;
						$temp["{$key_ib}|{$key_jb}"] = ( $temp["{$key_ib}|{$key_jb}"] ?? 0 ) + 1;
					}

					// For each potential alignment, try all 24 rotations.
					foreach ( $temp as $pair => $count ) {
						// We need at least 11 matching distances from that pairing
						// to confirm the correct alignment of the beacons.
						if ( $count >= 11 ) {
							[ $key_i, $key_j ] = explode( '|', $pair );
							[ $ix, $iy, $iz ] = array_map( 'intval', explode( ',', $key_i ) );
							$beacon_j  = array_map( 'intval', explode( ',', $key_j ) );
							$rotations = $this->get_rotations( $beacon_j );

							// Check all rotations for a single offset that aligns everything.
							for ( $r = 0; $r < 24; $r ++ ) {
								[ $jx, $jy, $jz ] = $rotations[ $r ];
								$dx = $ix - $jx;
								$dy = $iy - $jy;
								$dz = $iz - $jz;

								// If this key doesn't exist yet, initialize it.
								if ( ! isset( $offsets[ $r ]["{$dx},{$dy},{$dz}"] ) ) {
									$offsets[ $r ]["{$dx},{$dy},{$dz}"] = 0;
								}

								$offsets[ $r ]["{$dx},{$dy},{$dz}"] ++;
							}
						}
					}

					// Figure out which rotation + offset has exactly one solution.
					$chosen_rotation = - 1;
					$chosen_offset   = null;

					foreach ( $offsets as $r => $offset_map ) {
						if ( count( $offset_map ) === 1 ) {
							$chosen_rotation = $r;
							$chosen_offset   = explode( ',', array_key_first( $offset_map ) );
							break;
						}
					}

					if ( $chosen_rotation !== - 1 && $chosen_offset !== null ) {
						$locations[ $j ] = [
							(int) $chosen_offset[0],
							(int) $chosen_offset[1],
							(int) $chosen_offset[2]
						];

						// Apply rotation & offset to all beacons in scanner j.
						foreach ( $this->scanners[ $j ] as $t => $scan_beacon ) {
							$rotated                    = $this->get_rotations( $scan_beacon )[ $chosen_rotation ];
							$new_beacon                 = [
								$rotated[0] + $locations[ $j ][0],
								$rotated[1] + $locations[ $j ][1],
								$rotated[2] + $locations[ $j ][2],
							];
							$this->scanners[ $j ][ $t ] = $new_beacon;
							$beacon_keys[ $j ][ $t ]    = implode( ',', $new_beacon );
						}

						// Rebuild diffs for scanner j with its newly aligned coordinates.
						$new_diffs        = [];
						$diff_pairs[ $j ] = [];

						$size_j = count( $this->scanners[ $j ] );
						for ( $x = 0; $x < $size_j; $x ++ ) {
							for ( $y = $x + 1; $y < $size_j; $y ++ ) {
								$a                             = $this->scanners[ $j ][ $x ];
								$b                             = $this->scanners[ $j ][ $y ];
								$distance                      = ( $b[0] - $a[0] ) ** 2 + ( $b[1] - $a[1] ) ** 2 + ( $b[2] - $a[2] ) ** 2;
								$new_diffs[]                   = $distance;
								$diff_pairs[ $j ][ $distance ] = [ $a, $b ];
							}
						}
						sort( $new_diffs );
						$diffs[ $j ] = $new_diffs;

						$found[]  = $j;
						$toScan[] = $j;
					}
				}
			}
		}

		// Once all scanners are aligned, collect unique beacons.
		$unique_beacons = [];
		foreach ( $beacon_keys as $scan_strings ) {
			foreach ( $scan_strings as $beacon_string ) {
				$unique_beacons[ $beacon_string ] = true;
			}
		}

		$this->unique_beacons = count( $unique_beacons );

		// Compute the largest Manhattan distance between all scanner locations.
		$this->max_distance = 0;
		for ( $a = 0; $a < $num_scanners; $a ++ ) {
			for ( $b = $a + 1; $b < $num_scanners; $b ++ ) {
				// If a scanner wasn't aligned, it won't have a location. Default to [0,0,0].
				$locA = $locations[ $a ] ?? [ 0, 0, 0 ];
				$locB = $locations[ $b ] ?? [ 0, 0, 0 ];

				$manhattan = abs( $locA[0] - $locB[0] )
							 + abs( $locA[1] - $locB[1] )
							 + abs( $locA[2] - $locB[2] );

				if ( $manhattan > $this->max_distance ) {
					$this->max_distance = $manhattan;
				}
			}
		}
	}

	/**
	 * Returns all possible 3D rotations of a beacon coordinate.
	 *
	 * @param array $beacon An array [x,y,z].
	 *
	 * @return array A list of 24 [x,y,z] coordinate rotations.
	 */
	private function get_rotations( array $beacon ): array {
		[ $x, $y, $z ] = $beacon;

		return [
			[ $x, $y, $z ],
			[ $x, $z, - $y ],
			[ $x, - $y, - $z ],
			[ $x, - $z, $y ],

			[ - $x, - $y, $z ],
			[ - $x, - $z, - $y ],
			[ - $x, $y, - $z ],
			[ - $x, $z, $y ],

			[ $y, - $x, $z ],
			[ $y, $z, $x ],
			[ $y, $x, - $z ],
			[ $y, - $z, - $x ],

			[ - $y, $x, $z ],
			[ - $y, - $z, $x ],
			[ - $y, - $x, - $z ],
			[ - $y, $z, - $x ],

			[ $z, $y, - $x ],
			[ $z, $x, $y ],
			[ $z, - $y, $x ],
			[ $z, - $x, - $y ],

			[ - $z, - $y, - $x ],
			[ - $z, - $x, $y ],
			[ - $z, $y, $x ],
			[ - $z, $x, - $y ],
		];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return void
	 */
	private function parse_data( bool $test ): void {
		$file     = $test ? '/data/day-19-test.txt' : '/data/day-19.txt';
		$scanners = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) );

		foreach ( $scanners as $scanner ) {
			$scanner_data = [];
			foreach ( explode( "\n", $scanner ) as $line ) {
				// Ignore the header line (e.g. "--- scanner 0 ---").
				if ( str_starts_with( $line, '---' ) ) {
					continue;
				}

				// Convert the comma-separated string to an array of integers.
				$scanner_data[] = array_map( 'intval', explode( ',', $line ) );
			}
			$this->scanners[] = $scanner_data;
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
	$day19  = new Day19( $test, $part );
	$result = $day19->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 79,
			'real' => 335,
		],
		2 => [
			'test' => 3621,
			'real' => 10864,
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
