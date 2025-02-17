<?php

namespace AdventOfCode\Year2019;

/**
 * Simulates the hull-painting robot that follows the Intcode program.
 */
class HullPaintingRobot {
	/**
	 * The Intcode computer controlling the robot.
	 *
	 * @var IntcodeComputer
	 */
	private IntcodeComputer $computer;

	/**
	 * The painted grid, storing positions as "x,y" => color.
	 *
	 * @var array<string, int>
	 */
	private array $grid = [];

	/**
	 * The robot's current position [x, y].
	 *
	 * @var array<int>
	 */
	private array $position = [ 0, 0 ];

	/**
	 * The robot's facing direction (0=up, 1=right, 2=down, 3=left).
	 *
	 * @var int
	 */
	private int $direction = 0;

	/**
	 * Movement deltas for each direction.
	 *
	 * @var array<int, array<int>>
	 */
	private array $directions = [ [ 0, - 1 ], [ 1, 0 ], [ 0, 1 ], [ - 1, 0 ] ];


	/**
	 * Initializes the HullPaintingRobot with the given program.
	 *
	 * @param array<int> $program        The Intcode program for the robot.
	 * @param bool       $start_on_white Whether the robot starts on a white panel.
	 */
	public function __construct( array $program, bool $start_on_white = false ) {
		$this->computer = new IntcodeComputer( $program );

		// Start on a white panel for part 2
		if ( $start_on_white ) {
			$this->grid["0,0"] = 1;
		}
	}

	/**
	 * Runs the painting simulation until the Intcode program halts.
	 */
	public function run(): void {
		while ( ! $this->computer->has_halted() ) {
			$color = $this->grid[ $this->position[0] . ',' . $this->position[1] ] ?? 0;
			$this->computer->add_input( $color );
			$paint_color = $this->computer->run_until_output();
			$turn        = $this->computer->run_until_output();

			if ( $paint_color === null || $turn === null ) {
				break;
			}

			// Paint the current panel
			$this->grid[ $this->position[0] . ',' . $this->position[1] ] = $paint_color;

			// Turn and move
			$this->direction   = ( $turn === 0 ) ? ( $this->direction + 3 ) % 4 : ( $this->direction + 1 ) % 4;
			$this->position[0] += $this->directions[ $this->direction ][0];
			$this->position[1] += $this->directions[ $this->direction ][1];
		}
	}

	public function count_painted_panels(): int {
		return count( $this->grid );
	}

	/**
	 * Returns the final painted registration identifier as an ASCII string.
	 *
	 * @return string The painted registration identifier.
	 */
	public function get_painting(): string {
		$xs = array_map( fn( $pos ) => explode( ',', $pos )[0], array_keys( $this->grid ) );
		$ys = array_map( fn( $pos ) => explode( ',', $pos )[1], array_keys( $this->grid ) );

		$min_x = min( $xs );
		$max_x = max( $xs );
		$min_y = min( $ys );
		$max_y = max( $ys );

		$output = PHP_EOL;

		for ( $y = $min_y; $y <= $max_y; $y ++ ) {
			for ( $x = $min_x; $x <= $max_x; $x ++ ) {
				$output .= ( $this->grid["$x,$y"] ?? 0 ) ? '#' : ' ';
			}
			$output .= PHP_EOL;
		}

		return $output;
	}
}
