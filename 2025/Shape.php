<?php

namespace AdventOfCode\Year2025;

/**
 * Represents a shape with all its possible orientations (rotations and flips).
 */
class Shape {
	/**
	 * The original pattern as a 2D array.
	 *
	 * @var array
	 */
	private array $pattern;

	/**
	 * All unique orientations of this shape.
	 * Each orientation is stored as an array with 'coords' (array of [x, y] coordinates),
	 * 'max_x', and 'max_y' (bounding box dimensions).
	 *
	 * @var array
	 */
	private array $orientations;

	/**
	 * Area of the shape (number of # cells).
	 *
	 * @var int
	 */
	private int $area;

	/**
	 * Constructor.
	 *
	 * @param string $pattern The shape pattern from input (lines separated by \n).
	 */
	public function __construct( string $pattern ) {
		$lines         = explode( "\n", trim( $pattern ) );
		$this->pattern = [];
		foreach ( $lines as $line ) {
			$this->pattern[] = str_split( $line );
		}
		$this->area         = substr_count( $pattern, '#' );
		$this->orientations = $this->generate_orientations();
	}

	/**
	 * Generates all 8 unique orientations (4 rotations × 2 flips).
	 *
	 * @return array Array of orientations, each as array of [x, y] coordinates.
	 */
	private function generate_orientations(): array {
		$orientations = [];
		$seen         = [];

		// Generate all 8 combinations: 4 rotations × 2 flips
		for ( $flip = 0; $flip < 2; $flip++ ) {
			$pattern = $this->pattern;
			if ( $flip === 1 ) {
				$pattern = $this->flip_horizontal( $pattern );
			}

			for ( $rot = 0; $rot < 4; $rot++ ) {
				$rotated = $pattern;
				for ( $i = 0; $i < $rot; $i++ ) {
					$rotated = $this->rotate_90( $rotated );
				}

				// Extract coordinates of # cells
				$coords = [];
				foreach ( $rotated as $y => $row ) {
					foreach ( $row as $x => $cell ) {
						if ( $cell === '#' ) {
							$coords[] = [ $x, $y ];
						}
					}
				}

				// Normalize coordinates to start at (0,0)
				$min_x      = min( array_column( $coords, 0 ) );
				$min_y      = min( array_column( $coords, 1 ) );
				$normalized = [];
				foreach ( $coords as $coord ) {
					$normalized[] = [ $coord[0] - $min_x, $coord[1] - $min_y ];
				}

				// Sort and create a unique key
				usort( $normalized, function( $a, $b ) {
					if ( $a[1] !== $b[1] ) {
						return $a[1] <=> $b[1];
					}
					return $a[0] <=> $b[0];
				} );
				$key = serialize( $normalized );

				// Only add if we haven't seen this orientation
				if ( ! isset( $seen[ $key ] ) ) {
					$seen[ $key ]   = true;
					$orientations[] = [
						'coords' => $normalized,
						'max_x' => max( array_column( $normalized, 0 ) ),
						'max_y' => max( array_column( $normalized, 1 ) ),
					];
				}
			}
		}

		return $orientations;
	}

	/**
	 * Rotates a 2D array 90 degrees clockwise.
	 *
	 * @param  array $pattern The pattern to rotate.
	 * @return array The rotated pattern.
	 */
	private function rotate_90( array $pattern ): array {
		$rows    = count( $pattern );
		$cols    = count( $pattern[0] );
		$rotated = [];

		for ( $x = 0; $x < $cols; $x++ ) {
			$rotated[] = [];
			for ( $y = $rows - 1; $y >= 0; $y-- ) {
				$rotated[ $x ][] = $pattern[ $y ][ $x ];
			}
		}

		return $rotated;
	}

	/**
	 * Flips a 2D array horizontally.
	 *
	 * @param  array $pattern The pattern to flip.
	 * @return array The flipped pattern.
	 */
	private function flip_horizontal( array $pattern ): array {
		$flipped = [];
		foreach ( $pattern as $row ) {
			$flipped[] = array_reverse( $row );
		}
		return $flipped;
	}

	/**
	 * Gets all unique orientations of this shape.
	 *
	 * @return array Array of orientations, each with 'coords' (array of [x, y] coordinates),
	 *               'max_x', and 'max_y' (bounding box dimensions).
	 */
	public function getOrientations(): array {
		return $this->orientations;
	}

	/**
	 * Gets the area of the shape.
	 *
	 * @return int Number of # cells.
	 */
	public function getArea(): int {
		return $this->area;
	}
}

