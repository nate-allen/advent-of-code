<?php

namespace AdventOfCode\Year2020;

/**
 * Day 20: Jurassic Jigsaw
 */
class Day20 {
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
	 * Part 1: Assemble the tiles into an image and multiply the IDs of the four corner tiles.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$edges = $this->get_edges( $this->data );

		// Count how often each edge appears across tiles (both edge and its reverse).
		$edge_counts = [];
		foreach ( $edges as $id => $edge_set ) {
			foreach ( $edge_set as $side ) {
				$rev_side                 = strrev( $side );
				$edge_counts[ $side ]     = ( $edge_counts[ $side ] ?? 0 ) + 1;
				$edge_counts[ $rev_side ] = ( $edge_counts[ $rev_side ] ?? 0 ) + 1;
			}
		}

		// Find the tiles with exactly two "unique" edges (corners).
		$corner_tiles = [];
		foreach ( $edges as $id => $edge_set ) {
			$unique_edges = 0;
			foreach ( $edge_set as $side ) {
				$rev_side = strrev( $side );
				if ( $edge_counts[ $side ] === 1 && $edge_counts[ $rev_side ] === 1 ) {
					$unique_edges ++;
				}
			}
			if ( $unique_edges === 2 ) {
				$corner_tiles[] = $id;
			}
		}

		// Multiply the IDs of the four corner tiles
		return array_product( $corner_tiles );
	}

	/**
	 * Part 2: Assemble the full image, find sea monsters, and compute the water roughness.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// 1) Assemble the puzzle into one big image (2D array).
		$assembled = $this->assemble_puzzle();

		// 2) Remove tile borders and join the puzzle into a single large 2D array.
		$joined = $this->remove_borders_and_join( $assembled );

		// 3) Search for sea monsters in all orientations.
		$sea_monster = [
			"                  # ",
			"#    ##    ##    ###",
			" #  #  #  #  #  #   "
		];

		$monster_count = $this->count_monsters_in_any_orientation( $joined, $sea_monster );

		// 4) Compute the water roughness = number of '#' in the final image minus
		$total_hashes      = $this->count_hashes( $joined );
		$hashes_in_monster = $this->count_hashes_in_pattern( $sea_monster );
		$roughness         = $total_hashes - ( $monster_count * $hashes_in_monster );

		return $roughness;
	}

	/**
	 * Return an associative array of tile edges.
	 *
	 * @param array $data The tile data.
	 *
	 * @return array
	 */
	private function get_edges( array $data ): array {
		$edges = [];

		foreach ( $data as $id => $tile ) {
			// top row
			$top = implode( '', $tile[0] );
			// bottom row
			$bottom = implode( '', $tile[ count( $tile ) - 1 ] );
			// left column
			$left = implode( '', array_column( $tile, 0 ) );
			// right column
			$right = implode( '', array_column( $tile, count( $tile[0] ) - 1 ) );

			$edges[ $id ] = [ $top, $right, $bottom, $left ];
		}

		return $edges;
	}

	/**
	 * Assembles all tiles into a 2D "grid" of oriented tiles.
	 *
	 * @return array
	 */
	private function assemble_puzzle(): array {
		$edge_to_tiles = [];
		$edge_count    = [];

		foreach ( $this->data as $id => $tile ) {
			$tile_edges = $this->compute_edges_array( $tile );

			foreach ( $tile_edges as $edge ) {
				$k                = $this->normalize_edge( $edge );
				$edge_count[ $k ] = ( $edge_count[ $k ] ?? 0 ) + 1;

				if ( ! isset( $edge_to_tiles[ $k ] ) ) {
					$edge_to_tiles[ $k ] = [];
				}
				$edge_to_tiles[ $k ][] = $id;
			}
		}

		// 1) Find a corner tile and orient it so that top & left edges are unique
		$corner_id = null;
		foreach ( $this->data as $id => $tile ) {
			$tile_edges   = $this->compute_edges_array( $tile );
			$unique_count = 0;

			foreach ( $tile_edges as $edge ) {
				if ( ( $edge_count[ $this->normalize_edge( $edge ) ] ?? 0 ) === 1 ) {
					$unique_count ++;
				}
			}

			if ( $unique_count === 2 ) {
				$corner_id = $id;
				break;
			}
		}

		// Store tile usage and the final puzzle grid.
		$used     = [];
		$assembly = [ [] ];

		// 2) Orient the corner tile so that top & left edges are "unique".
		$oriented_corner    = $this->orient_corner_tile( $this->data[ $corner_id ], $edge_count );
		$assembly[0][]      = [ 'id' => $corner_id, 'tile' => $oriented_corner ];
		$used[ $corner_id ] = true;

		// 3) Fill the first row: match the right edge of the last placed tile to the left edge of the next.
		while ( true ) {
			$last_tile_info = end( $assembly[0] );
			$current_tile   = $last_tile_info['tile'];
			$right_edge     = $this->get_right_edge( $current_tile );

			// The next tile must have a left edge matching $right_edge
			$tile_id = $this->find_matching_tile( $right_edge, $edge_to_tiles, $used );
			if ( $tile_id === null ) {
				// No more tiles can attach => we’re at the end of the row
				break;
			}

			// Orient it so its left edge matches $right_edge
			$oriented         = $this->orient_to_match_left( $this->data[ $tile_id ], $right_edge );
			$assembly[0][]    = [ 'id' => $tile_id, 'tile' => $oriented ];
			$used[ $tile_id ] = true;
		}

		// 4) Fill subsequent rows: for each tile in the previous row, attach a tile below it
		while ( true ) {
			$prev_row  = end( $assembly );
			$new_row   = [];
			$any_found = false;

			foreach ( $prev_row as $cell ) {
				$below_edge = $this->get_bottom_edge( $cell['tile'] );
				$tile_id    = $this->find_matching_tile( $below_edge, $edge_to_tiles, $used );

				if ( $tile_id === null ) {
					// If we can't find a match for even one tile, we stop filling downward
					continue;
				}

				$oriented         = $this->orient_to_match_top( $this->data[ $tile_id ], $below_edge );
				$new_row[]        = [ 'id' => $tile_id, 'tile' => $oriented ];
				$used[ $tile_id ] = true;
				$any_found        = true;
			}

			// If we found no tiles for the entire row, we stop
			if ( ! $any_found ) {
				break;
			}

			$assembly[] = $new_row;
		}

		return $assembly;
	}

	/**
	 * Remove borders from each tile in the assembly (the outermost row/col of each tile)
	 * and then join them into one large 2D array.
	 *
	 * @param array $assembly The assembled tiles.
	 *
	 * @return array
	 */
	private function remove_borders_and_join( array $assembly ): array {
		$big_image = [];

		foreach ( $assembly as $row_index => $row_tiles ) {
			// We'll accumulate them horizontally.
			$tile_height = count( $row_tiles[0]['tile'] ) - 2; // if each tile is n rows

			// Prepare empty arrays in $big_image for these tile-height rows
			for ( $i = 0; $i < $tile_height; $i ++ ) {
				if ( ! isset( $big_image[ $row_index * $tile_height + $i ] ) ) {
					$big_image[ $row_index * $tile_height + $i ] = [];
				}
			}

			foreach ( $row_tiles as $tile_info ) {
				$tile = $tile_info['tile'];
				$n    = count( $tile ); // e.g., 10 if original, after orientation

				// skip first and last row
				for ( $r = 1; $r < $n - 1; $r ++ ) {
					// skip first and last col
					$inner_row = array_slice( $tile[ $r ], 1, $n - 2 );

					$big_image[ $row_index * $tile_height + ( $r - 1 ) ] = array_merge(
						$big_image[ $row_index * $tile_height + ( $r - 1 ) ],
						$inner_row
					);
				}
			}
		}

		return $big_image;
	}

	/**
	 * Count how many sea monsters appear in any of the 8 orientations of the final image (flip + rotate).
	 *
	 * @param array $image 2D array of chars
	 * @param array $monster 2D array of chars
	 *
	 * @return int
	 */
	private function count_monsters_in_any_orientation( array $image, array $monster ): int {
		$orientations = [];
		$current      = $image;

		// Build up to 8 transformations (4 rotations, each possibly flipped).
		for ( $i = 0; $i < 4; $i ++ ) {
			$orientations[] = $current;
			$flipped        = $this->flip_horizontal( $current );
			$orientations[] = $flipped;

			// Rotate the original (unflipped) for the next iteration
			$current = $this->rotate90( $current );
		}

		$max_count = 0;
		foreach ( $orientations as $candidate ) {
			$count = $this->count_sea_monsters( $candidate, $monster );
			if ( $count > $max_count ) {
				$max_count = $count;
			}
		}

		return $max_count;
	}

	/**
	 * Count how many sea monsters appear in $image.
	 *
	 * @param array $image 2D array of chars
	 * @param array $monster 2D array of chars
	 *
	 * @return int
	 */
	private function count_sea_monsters( array $image, array $monster ): int {
		$count        = 0;
		$rows_image   = count( $image );
		$cols_image   = count( $image[0] ?? [] );
		$rows_monster = count( $monster );
		$cols_monster = strlen( $monster[0] );

		for ( $r = 0; $r <= $rows_image - $rows_monster; $r ++ ) {
			for ( $c = 0; $c <= $cols_image - $cols_monster; $c ++ ) {
				if ( $this->matches_monster( $image, $monster, $r, $c ) ) {
					$count ++;
				}
			}
		}

		return $count;
	}

	/**
	 * Check if the monster pattern matches the image at the given position.
	 *
	 * @param array $image   2D array of chars
	 * @param array $monster 2D array of chars
	 * @param int   $start_r Row index to start checking from
	 * @param int   $start_c Column index to start checking from
	 *
	 * @return bool
	 */
	private function matches_monster( array $image, array $monster, int $start_r, int $start_c ): bool {
		$rows_monster = count( $monster );
		$cols_monster = strlen( $monster[0] );

		for ( $mr = 0; $mr < $rows_monster; $mr ++ ) {
			for ( $mc = 0; $mc < $cols_monster; $mc ++ ) {
				if ( $monster[ $mr ][ $mc ] === '#' ) {
					// If the monster has a '#', the image must also have a '#' at this position
					if ( $image[ $start_r + $mr ][ $start_c + $mc ] !== '#' ) {
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Count total number of '#' in a 2D array of chars.
	 *
	 * @param array $image 2D array of chars
	 *
	 * @return int
	 */
	private function count_hashes( array $image ): int {
		$total = 0;

		foreach ( $image as $row ) {
			foreach ( $row as $ch ) {
				if ( $ch === '#' ) {
					$total ++;
				}
			}
		}

		return $total;
	}

	/**
	 * Count total number of '#' in an array of strings (the monster pattern).
	 *
	 * @param array $monster 2D array of chars
	 *
	 * @return int
	 */
	private function count_hashes_in_pattern( array $monster ): int {
		$total = 0;

		foreach ( $monster as $line ) {
			$total += substr_count( $line, '#' );
		}

		return $total;
	}

	/**
	 * Return the 4 edges of a tile (2D array) in the order:
	 *   [ left-edge, top-edge, right-edge, bottom-edge ]
	 *
	 * @param array $tile 2D array of chars
	 *
	 * @return array
	 */
	private function compute_edges_array( array $tile ): array {
		$size  = count( $tile );
		$left  = [];  // top->down
		$right = []; // top->down

		for ( $r = 0; $r < $size; $r ++ ) {
			$left[]  = $tile[ $r ][0];
			$right[] = $tile[ $r ][ $size - 1 ];
		}

		$top    = $tile[0];            // left->right
		$bottom = $tile[ $size - 1 ];  // left->right

		return [
			implode( '', $left ),
			implode( '', $top ),
			implode( '', $right ),
			implode( '', $bottom ),
		];
	}

	/**
	 * Normalize an edge string
	 *
	 * @param string $edge Edge string
	 *
	 * @return string
	 */
	private function normalize_edge( string $edge ): string {
		$rev = strrev( $edge );

		return ( strcmp( $edge, $rev ) <= 0 ) ? $edge : $rev;
	}

	/**
	 * Orient a corner tile so that the top and left edges are "unique" edges
	 *
	 * @param array $tile       2D array of chars
	 * @param array $edge_count map of canonical-edge => frequency
	 *
	 * @return array
	 */
	private function orient_corner_tile( array $tile, array $edge_count ): array {
		for ( $r = 0; $r < 4; $r ++ ) {
			// Check top (index=1 in compute_edges_array) and left (index=0).
			$edges     = $this->compute_edges_array( $tile );
			$left_edge = $edges[0];
			$top_edge  = $edges[1];

			if (
				( $edge_count[ $this->normalize_edge( $left_edge ) ] ?? 0 ) === 1 &&
				( $edge_count[ $this->normalize_edge( $top_edge ) ] ?? 0 ) === 1
			) {
				return $tile; // oriented correctly
			}

			// Try flipping horizontally
			$tile      = $this->flip_horizontal( $tile );
			$edges     = $this->compute_edges_array( $tile );
			$left_edge = $edges[0];
			$top_edge  = $edges[1];

			if (
				( $edge_count[ $this->normalize_edge( $left_edge ) ] ?? 0 ) === 1 &&
				( $edge_count[ $this->normalize_edge( $top_edge ) ] ?? 0 ) === 1
			) {
				return $tile; // oriented correctly
			}

			// Flip back, then rotate
			$tile = $this->flip_horizontal( $tile );
			$tile = $this->rotate90( $tile );
		}

		return $tile;
	}

	/**
	 * Find a tile that can match $needed_edge on its left side (index=0 in compute_edges_array).
	 *
	 * @param string $needed_edge   Edge string
	 * @param array  $edge_to_tiles map of edge => tile-ids
	 * @param array  $used          map of tile-id => true
	 *
	 * @return int|null
	 */
	private function find_matching_tile( string $needed_edge, array $edge_to_tiles, array $used ): ?int {
		$normalized = $this->normalize_edge( $needed_edge );

		if ( ! isset( $edge_to_tiles[ $normalized ] ) ) {
			return null;
		}

		$matching = $edge_to_tiles[ $normalized ];

		foreach ( $matching as $tile_id ) {
			if ( ! isset( $used[ $tile_id ] ) ) {
				return $tile_id;
			}
		}

		return null;
	}

	/**
	 * Orient the given $tile so that its left edge matches $needed_edge exactly.
	 *
	 * @param array  $tile         2D array of chars
	 * @param string $needed_edge  Edge string
	 *
	 * @return array
	 */
	private function orient_to_match_left( array $tile, string $needed_edge ): array {
		for ( $r = 0; $r < 4; $r ++ ) {
			$edges = $this->compute_edges_array( $tile );

			if ( $edges[0] === $needed_edge ) {
				return $tile;
			}

			// Try flipping horizontally
			$tile  = $this->flip_horizontal( $tile );
			$edges = $this->compute_edges_array( $tile );

			if ( $edges[0] === $needed_edge ) {
				return $tile;
			}

			// Flip back and rotate
			$tile = $this->flip_horizontal( $tile );
			$tile = $this->rotate90( $tile );
		}

		return $tile;
	}

	/**
	 * Orient the given $tile so that its top edge matches $neededEdge exactly.
	 *
	 * @param array  $tile         2D array of chars
	 * @param string $needed_edge  Edge string
	 *
	 * @return array
	 */
	private function orient_to_match_top( array $tile, string $needed_edge ): array {
		for ( $r = 0; $r < 4; $r ++ ) {
			$edges = $this->compute_edges_array( $tile );

			if ( $edges[1] === $needed_edge ) {
				return $tile;
			}

			// Try flipping horizontally
			$tile  = $this->flip_horizontal( $tile );
			$edges = $this->compute_edges_array( $tile );

			if ( $edges[1] === $needed_edge ) {
				return $tile;
			}

			// Flip back and rotate
			$tile = $this->flip_horizontal( $tile );
			$tile = $this->rotate90( $tile );
		}

		return $tile;
	}

	/**
	 * Get right edge of the tile as a string (top->down).
	 *
	 * @param array $tile 2D array of chars
	 *
	 * @return string
	 */
	private function get_right_edge( array $tile ): string {
		$size = count( $tile );
		$edge = '';

		for ( $r = 0; $r < $size; $r ++ ) {
			$edge .= $tile[ $r ][ $size - 1 ];
		}

		return $edge;
	}

	/**
	 * Get bottom edge of the tile as a string (left->right).
	 *
	 * @param array $tile 2D array of chars
	 *
	 * @return string
	 */
	private function get_bottom_edge( array $tile ): string {
		$last_row = $tile[ count( $tile ) - 1 ];

		return implode( '', $last_row );
	}

	/**
	 * Rotate a 2D array 90 degrees clockwise.
	 *
	 * @param array $tile 2D array of chars
	 *
	 * @return array
	 */
	private function rotate90( array $tile ): array {
		$count  = count( $tile );
		$result = [];

		for ( $r = 0; $r < $count; $r ++ ) {
			$result[ $r ] = array_fill( 0, $count, '' );
		}

		for ( $r = 0; $r < $count; $r ++ ) {
			for ( $c = 0; $c < $count; $c ++ ) {
				$result[ $c ][ $count - 1 - $r ] = $tile[ $r ][ $c ];
			}
		}

		return $result;
	}

	/**
	 * Flip (mirror) a 2D array horizontally (left<->right).
	 *
	 * @param array $tile 2D array of chars
	 *
	 * @return array
	 */
	private function flip_horizontal( array $tile ): array {
		$count  = count( $tile );
		$result = [];

		for ( $r = 0; $r < $count; $r ++ ) {
			$result[ $r ] = array_reverse( $tile[ $r ] );
		}

		return $result;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-20-test.txt' : '/data/day-20.txt';
		$tiles = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$data  = [];

		foreach ( $tiles as $tile ) {
			$lines = explode( "\n", $tile );
			$id    = (int) filter_var( array_shift( $lines ), FILTER_SANITIZE_NUMBER_INT );

			foreach ( $lines as $line ) {
				$data[ $id ][] = str_split( $line );
			}
		}

		return $data;
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
	$day20  = new Day20( $test, $part );
	$result = $day20->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 20899048083289,
			'real' => 17712468069479,
		],
		2 => [
			'test' => 273,
			'real' => 2173,
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
