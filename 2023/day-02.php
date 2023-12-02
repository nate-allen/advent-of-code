<?php

/**
 * Day 02: Cube Conundrum
 */
class Day02 {

	/**
	 * The data.
	 *
	 * @var array
	 */
	private array $data;

	public function __construct( $part, $test ) {
		$this->parse_data( $test );
	}

	/**
	 * Part 1
	 *
	 * @return int
	 */
	public function part_1(): int {
		$sumOfGameIds = 0;
		$availableCubes = ['red' => 12, 'green' => 13, 'blue' => 14];

		foreach ($this->data as $game) {
			if ($this->isGamePossible($game, $availableCubes)) {
				preg_match('/Game (\d+):/', $game, $matches);
				$sumOfGameIds += (int)$matches[1];
			}
		}

		return $sumOfGameIds;
	}

	/**
	 * Part 2
	 *
	 * @return int
	 */
	public function part_2(): int {
		$totalPower = 0;

		foreach ($this->data as $game) {
			$minCubes = $this->findMinimumCubes($game);
			$totalPower += array_product($minCubes);
		}

		return $totalPower;
	}

	/**
	 * Check if a game is possible with the given cube configuration.
	 *
	 * @param string $game
	 * @param array $availableCubes
	 *
	 * @return bool
	 */
	private function isGamePossible(string $game, array $availableCubes): bool {
		$reveals = explode(';', str_replace('Game', '', $game));
		foreach ($reveals as $reveal) {
			$cubesInReveal = $this->parseReveal($reveal);
			foreach ($cubesInReveal as $color => $count) {
				if ($count > $availableCubes[$color]) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Parse a reveal string to get the cube counts.
	 *
	 * @param string $reveal
	 *
	 * @return array
	 */
	private function parseReveal(string $reveal): array {
		$cubes = ['red' => 0, 'green' => 0, 'blue' => 0];
		$parts = explode(',', $reveal);
		foreach ($parts as $part) {
			if (preg_match('/(\d+) (red|green|blue)/', $part, $matches)) {
				$cubes[$matches[2]] += (int)$matches[1];
			}
		}

		return $cubes;
	}

	/**
	 * Find the minimum number of cubes of each color needed for a game.
	 *
	 * @param string $game
	 *
	 * @return array
	 */
	private function findMinimumCubes(string $game): array {
		$minCubes = ['red' => 0, 'green' => 0, 'blue' => 0];

		$reveals = explode(';', str_replace('Game', '', $game));
		foreach ($reveals as $reveal) {
			$cubesInReveal = $this->parseReveal($reveal);
			foreach ($cubesInReveal as $color => $count) {
				if ($count > $minCubes[$color]) {
					$minCubes[$color] = $count;
				}
			}
		}

		return $minCubes;
	}

	/**
	 * Parse the data.
	 *
	 * @param string $test The test data.
	 *
	 * @return void
	 */
	private function parse_data( string $test ) {
		$data = $test ? '/data/day-02-test.txt' : '/data/day-02.txt';

		$this->data = explode( PHP_EOL, file_get_contents( __DIR__ . $data ) );
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
	$day02  = new Day02( 1, $test );
	$result = $day02->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}

function part_2( $test = false ) {
	$start = microtime( true );
	$day02 = new Day02( 2, $test );

	$result = $day02->part_2();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}
