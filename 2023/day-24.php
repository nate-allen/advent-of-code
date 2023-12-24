<?php
ini_set('precision', 20);
/**
 * Day 24: Never Tell Me The Odds
 */
class Day24 {
	/**
	 * The puzzle data.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
	 */
	private int $part;

	/**
	 * Whether or not to use the test data.
	 *
	 * @var bool
	 */
	private bool $is_test;

	public function __construct( string $test, int $part ) {
		$this->parse_data( $test );
		$this->part    = $part;
		$this->is_test = $test;
	}

	/**
	 * Part 1: Considering only the X and Y axes, check all pairs of hailstones' future paths for intersections.
	 *         How many of these intersections occur within the test area?
	 *
	 * @return int Cubic meters
	 */
	public function part_1(): int {
		$count    = 0;
		$area_min = $this->is_test ? 7 : 200000000000000;
		$area_max = $this->is_test ? 27 : 400000000000000;

		for ( $i = 0; $i < count( $this->data ) - 1; $i ++ ) {
			for ( $j = $i + 1; $j < count( $this->data ); $j ++ ) {
				[ $px1, $py1, $vx1, $vy1 ] = $this->parse_line( $this->data[ $i ] );
				[ $px2, $py2, $vx2, $vy2 ] = $this->parse_line( $this->data[ $j ] );

				// If paths are not parallel
				if ( $vx1 * $vy2 !== $vx2 * $vy1 ) {
					$t1          = ( $px2 - $px1 ) * $vy2 - ( $py2 - $py1 ) * $vx2;
					$t2          = ( $px2 - $px1 ) * $vy1 - ( $py2 - $py1 ) * $vx1;
					$denominator = $vx1 * $vy2 - $vy1 * $vx2;

					// Avoid division by zero
					if ( $denominator !== 0 ) {
						$t1 = $t1 / $denominator;
						$t2 = $t2 / $denominator;

						// Check if intersection occurs in the future for both hailstones
						if ( $t1 >= 0 && $t2 >= 0 ) {
							$intersection_x = $px1 + $vx1 * $t1;
							$intersection_y = $py1 + $vy1 * $t1;

							if ( $intersection_x >= $area_min && $intersection_x <= $area_max &&
								 $intersection_y >= $area_min && $intersection_y <= $area_max ) {
								$count ++;
								//echo "Hailstone A: {$this->data[$i]}\nHailstone B: {$this->data[$j]}\n";
								//echo "Hailstones' paths will cross inside the test area (at x=$intersectionX, y=$intersectionY).\n\n";
							}
						}
					}
				}
			}
		}

		return $count;
	}

	/**
	 * Part 2:
	 *
	 * @return int The answer.
	 */
	public function part_2(): int {
		return 0;
	}

	private function parse_data( bool $test ): void {
		$file       = $test ? '/data/day-24-test.txt' : '/data/day-24.txt';
		$this->data = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
	}

	private function parse_line( string $line ): array {
		preg_match( '/(\d+), (\d+), \d+ @ ([\-\d]+), ([\-\d]+), [\-\d]+/', $line, $matches );

		return [ (int) $matches[1], (int) $matches[2], (int) $matches[3], (int) $matches[4] ];
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
	$day24    = new Day24( $test, 1 );
	$result   = $day24->part_1();
	$end      = microtime( true );
	$expected = $test ? 0 : 3716;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day24    = new Day24( $test, 2 );
	$result   = $day24->part_2();
	$end      = microtime( true );
	$expected = $test ? 16733044 : 616583483179597;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
