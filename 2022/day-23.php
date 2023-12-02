<?php
/**
 * Day 23: Unstable Diffusion
 */
class Day23 {

	private array $grid;

	private array $directions;

	private array $direction_groups;

	public function __construct( $test ) {
		$this->grid = $this->parse_data( $test );

		$this->directions = array(
			'N'  => array( 0, -1 ),
			'NE' => array( 1, -1 ),
			'E'  => array( 1, 0 ),
			'SE' => array( 1, 1 ),
			'S'  => array( 0, 1 ),
			'SW' => array( -1, 1 ),
			'W'  => array( -1, 0 ),
			'NW' => array( -1, -1 ),
		);

		$this->direction_groups = array(
			array( 'N', 'NE', 'NW' ),
			array( 'S', 'SE', 'SW' ),
			array( 'W', 'NW', 'SW' ),
			array( 'E', 'NE', 'SE' ),
		);
	}

	/**
	 * Part 1: Simulate the process and find the smallest rectangle that contains the Elves after 10 rounds. How many
	 *         empty tiles are within that rectangle?
	 *
	 * @return int
	 */
	public function part_1(): int {
		return $this->start( 10 );
	}

	/**
	 * Part 2: What is the number of the first round where no Elf moves?
	 *
	 * @return int
	 */
	public function part_2(): int {
		return $this->start( PHP_INT_MAX );
	}

	/**
	 * This function simulates the movement of Elves on a grid over a given number of rounds.
	 *
	 * Basically, in each iteration, if an Elf isn't in any adjacent cell, it's added to the new map. If there is an Elf
	 * in an adjacent cell, it tries to move in one of the directions. If it can't move, it's added to the new map in
	 * its original position. After all the cells have been processed, the new map becomes the current map and the next
	 * round starts.
	 *
	 * @param $rounds
	 *
	 * @return float|int
	 */
	private function start( $rounds ) {
		$min = array( 0, 0 );
		$max = array( 0, 0 );
		$map = array();

		foreach ( $this->grid as $y => $row ) {
			foreach ( $row as $x => $value ) {
				// If it's a '#', add it to the map
				if ( '#' === $value ) {
					$map[ $y ][ $x ] = true;

					// Update the min/max coordinates.
					$max[0] = max( $max[0], $x );
					$max[1] = max( $max[1], $y );
				}
			}
		}

		// Run the simulation for the given number of rounds.
		$this->render($map, 0);
		for ( $i = 0; $i < $rounds; $i ++ ) {
			$new_map     = array();
			$map_changed = false;

			foreach ( $map as $y => $row ) {
				foreach ( $row as $x => $cell ) {
					// If the cell is empty, skip it.
					if ( ! $cell ) {
						continue;
					}

					$adjacent     = array();
					$has_adjacent = false;

					// Iterate over the possible directions.
					foreach ( $this->directions as $key => $direction ) {
						// Check if the adjacent cell in this direction is present in the map
						$occurring        = ! empty( $map[ $y + $direction[1] ][ $x + $direction[0] ] );
						$has_adjacent     = $has_adjacent || $occurring;
						$adjacent[ $key ] = $occurring;
					}

					// If the cell has no adjacent cells, add it to the new map
					if ( ! $has_adjacent ) {
						$new_map[ $y ][ $x ] = true;
						continue;
					}

					// Map changed.
					$map_changed = true;

					// Try to move in each of the 4 direction groups.
					$has_moved = false;
					for ( $dg = 0; $dg < 4; $dg ++ ) {
						// Use modulo because the directions to check change every round.
						$direction_groups = $this->direction_groups[ ( $i + $dg ) % 4 ];
						foreach ( $direction_groups as $direction_group ) {
							// If there is an adjacent cell in this direction, move on to the next direction group
							if ( $adjacent[ $direction_group ] ) {
								continue 2; // Continue the outer loop.
							}
						}

						// If no adjacent cells were found in any of the directions, move.
						$has_moved     = true;
						$direction     = $this->directions[ $direction_groups[0] ];
						$next_position = array( $x + $direction[0], $y + $direction[1] );

						// If the next position is already occupied, add the current cell to the new map and remove the occupied cell
						if ( isset( $new_map[ $next_position[1] ][ $next_position[0] ] ) ) {
							$new_map[ $y ][ $x ] = true;

							$old = $new_map[ $next_position[1] ][ $next_position[0] ];

							if ( is_array( $old ) ) {
								$new_map[ $old[1] ][ $old[0] ] = true;
							}
							$new_map[ $next_position[1] ][ $next_position[0] ] = false;
						} else {
							$new_map[ $next_position[1] ][ $next_position[0] ] = array( $x, $y );

							// Update the minimum x and y coordinates if necessary.
							foreach ( array( 0, 1 ) as $axis ) {
								if ( $direction[ $axis ] < 0 ) {
									$min[ $axis ] = min( $min[ $axis ], $next_position[ $axis ] );
								} elseif ( $direction[ $axis ] > 0 ) {
									$max[ $axis ] = max( $max[ $axis ], $next_position[ $axis ] );
								}
							}
						}

						// Stop trying to move.
						break;
					}

					// If the cell couldn't be moved, add it to the new map
					if ( ! $has_moved ) {
						$new_map[ $y ][ $x ] = true;
					}
				}
			}
			$map = array_filter( $new_map );

			$this->render($map, $i + 1);
			// Map hasn't changed. We're done.
			if ( ! $map_changed ) {
				return $i + 1;
			}
		}

		$width  = $max[0] - $min[0] + 1;
		$height = $max[1] - $min[1] + 1;
		$total  = 0;

		foreach ( $map as $row ) {
			foreach ( $row as $cell ) {
				$total += $cell ? 1 : 0;
			}
		}

		return ( $width * $height ) - $total;
	}

	/**
	 * Parses the data and returns an array representing a grid.
	 *
	 * @param string $test The test data.
	 *
	 * @return array
	 */
	private function parse_data( string $test ) {
		$path = $test ? '/data/day-23-test.txt' : '/data/day-23.txt';

		return array_map(
			function ( $line ) {
				return str_split( trim( $line ) );
			},
			explode( PHP_EOL, file_get_contents( __DIR__ . $path ) )
		);
	}

	public function render( $map, $step ) {
		$bounds = ['l' => -15, 't' => -14, 'r' => 127, 'b' => 127];
		$completeMap = $this->createCompleteMap($map, $bounds, '.');
		$filename = __DIR__ . '/output/day-23/' . str_pad($step, 10, '0', STR_PAD_LEFT) . '.png';
		$this->map($completeMap, $filename, 4, [
			'#' => [0, 0, 0],
			'.' => [30, 140, 100],
		]);
	}

	public function createCompleteMap(
		array $partialMap,
		array $bounds,
		string $void = ' ',
		?array $extra = null,
		bool $reverseY = false
	): array {
		$l = $bounds['l'] ?? $bounds['x'] ?? 0;
		$r = $bounds['r'] ?? $l + $bounds['w'];
		$w = $bounds['w'] ?? $r - $l + 1;
		$t = $bounds['t'] ?? $bounds['y'] ?? 0;
		$b = $bounds['b'] ?? $t + $bounds['h'];
		$h = $bounds['h'] ?? $b - $t + 1;
		$map = array_fill($t, $h, array_fill($l, $w, $void));
		foreach ($partialMap as $y => $row) {
			foreach ($row as $x => $value) {
				$map[$y][$x] = is_string($value) ? $value : ($value ? '#' : '.');
			}
		}
		foreach ($extra ?? [] as $char => $extraInfo) {
			if (is_numeric(key($extraInfo))) {
				$extraInfo = ['coords' => $extraInfo];
			}
			$char = $extraInfo['char'] ?? $char;
			$coords = $extraInfo['coords'];
			$offset = $extraInfo['offset'] ?? [0, 0];
			foreach ($coords as $crds) {
				$map[$crds[1] + $offset[1]][$crds[0] + $offset[0]] = $char;
			}
		}
		if ($reverseY) {
			$map = array_reverse($map);
		}
		return $map;
	}

	private function visualizer_to_file( $map, $step ) {
		$min = array( PHP_INT_MAX, PHP_INT_MAX );
		$max = array( PHP_INT_MIN, PHP_INT_MIN );

		foreach ( $map as $y => $row ) {
			foreach ( $row as $x => $cell ) {
				if ( ! $cell ) {
					continue;
				}

				foreach ( array( 0, 1 ) as $axis ) {
					$min[ $axis ] = min( $min[ $axis ], $x );
					$max[ $axis ] = max( $max[ $axis ], $x );
				}
			}
		}

		$width  = $max[0] - $min[0] + 1;
		$height = $max[1] - $min[1] + 1;

		$visualizer = array_fill( 0, $height, array_fill( 0, $width, '.' ) );

		foreach ( $map as $y => $row ) {
			foreach ( $row as $x => $cell ) {
				if ( ! $cell ) {
					continue;
				}

				$visualizer[ $y - $min[1] ][ $x - $min[0] ] = $cell ? '#' : '.';
			}
		}

		$visualizer = array_map(
			function ( $row ) {
				return implode( '', $row );
			},
			$visualizer
		);

		$colors = array(
			'#' => [60,60,60],
			'.'  => [117,204,223],
		);
		$filename = __DIR__ . '/output/day-23/' . str_pad($step, 10, '0', STR_PAD_LEFT) . '.png';
		$this->map($map, $filename, 4, $colors);


		// file_put_contents( __DIR__ . '/output/day-23/' . str_pad( $step, 10, '0', STR_PAD_LEFT ) . '.txt', implode( PHP_EOL, $visualizer ) );
		//
		// // Convert each text file to a png file.
		// $cmd = 'convert -size 1000x1000 xc:white -font Courier -pointsize 10 -fill black -draw "text 0,10 \'Step ' . $step . '\'" -draw "text 0,20 \'' . implode( '\'" -draw "text 0,' . ( $step * 10 + 20 ) . ' \'', $visualizer ) . '\'" ' . __DIR__ . '/output/day-23/' . str_pad( $step, 10, '0', STR_PAD_LEFT ) . '.png';
		// exec( $cmd );

	}

	/**
	 * Takes text files from the output directory and creates a gif.
	 *
	 * @return void
	 */
	public function create_gif_from_output_files() {
		$dir = __DIR__ . '/output/day-23';
		$filename = 'day-23.gif';
		$files = glob("$dir/*.png");
		$last = end($files);
		exec("convert -delay 10 $dir/*.png -delay 100 $last $dir/$filename");
	}

	public static function map(array $map, string $filename, int $pixelSize, array $colorMap): void
	{
		if (!file_exists(dirname($filename))) {
			mkdir(dirname($filename), 0777, true);
		}
		$width = count(reset($map));
		$height = count($map);
		$img = imagecreate($width * $pixelSize, $height * $pixelSize);
		$colors = [];

		// Find x & y offset
		$xOffset = $yOffset = 0;
		foreach ($map as $y => $row) {
			$yOffset = -$y;
			foreach ($row as $x => $value) {
				$xOffset -= $x;
				break 2;
			}
		}

		foreach ($map as $y => $row) {
			foreach ($row as $x => $value) {
				if (isset($colors[$value])) {
					$color = $colors[$value];
				} else {
					if (isset($colorMap[$value])) {
						$color = imagecolorallocate($img, $colorMap[$value][0], $colorMap[$value][1], $colorMap[$value][2]);
					} else {
						throw new Exception("Unmapped color value '$value' found");
					}
					$colors[$value] = $color;
				}
				imagefilledrectangle(
					$img,
					($x + $xOffset) * $pixelSize,
					($y + $yOffset) * $pixelSize,
					($x + $xOffset) * $pixelSize + $pixelSize,
					($y + $yOffset) * $pixelSize + $pixelSize,
					$color
				);
			}
		}
		imagepng($img, $filename);
	}

	public static function strtoimg(string $string, string $filename, int $pixelSize = 5, array $colorMap = []): void
	{
		if (!file_exists(dirname($filename))) {
			mkdir(dirname($filename), 0777, true);
		}
		$string = trim($string);
		$lines = explode("\n", $string);
		if (!$lines) {
			throw new Exception('strtoimg: no input given');
		}
		$img = imagecreate(strlen($lines[0]) * $pixelSize, count($lines) * $pixelSize);
		$colors = [];
		foreach ($lines as $y => $line) {
			$line = str_split($line);
			foreach ($line as $x => $colorIndex) {
				if (isset($colors[$colorIndex])) {
					$color = $colors[$colorIndex];
				} else {
					if (isset($colorMap[$colorIndex])) {
						$color = imagecolorallocate($img, $colorMap[$colorIndex][0], $colorMap[$colorIndex][1], $colorMap[$colorIndex][2]);
					} else {
						$color = imagecolorallocate($img, round($colorIndex * 25), round($colorIndex * 25), round($colorIndex * 25));
					}
					$colors[$colorIndex] = $color;
				}
				imagefilledrectangle($img, $x * $pixelSize, $y * $pixelSize, $x * $pixelSize + $pixelSize, $y * $pixelSize + $pixelSize, $color);
			}
		}
		imagepng($img, $filename);
	}
}

// Prompt which part to run and if it should use the test data.
while ( true ) {
	$part = trim( readline( 'Which part do you want to run? (1/2)' ) );
	if ( function_exists( "part_{$part}" ) ) {
		while ( true ) {
			$test = trim( strtolower( readline( 'Do you want to run the test? (y/n)' ) ) );
			if ( in_array( $test, array( 'y', 'n' ), true ) ) {
				$test = 'y' === $test;
				call_user_func( "part_{$part}", $test );
				break;
			}
			echo 'Please enter y or n' . PHP_EOL;
		}
		break;
	}
	echo 'Please enter 1 or 2' . PHP_EOL;
}

function part_1( $test = false ) {
	$start  = microtime( true );
	$day23  = new Day23( $test );
	$result = $day23->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}

function part_2( $test = false ) {
	$start  = microtime( true );
	$day23  = new Day23( $test );

	$result = $day23->part_2();
	$end    = microtime( true );

	$day23->create_gif_from_output_files();

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}
