<?php

/**
 * Day 21: Step Counter
 */
class Day21 {
	private array $data;
	private int $part;
	private array $startPosition;
	private bool $is_test;

	public function __construct(string $test, int $part) {
		$this->parse_data($test);
		$this->part = $part;
		$this->is_test = $test;
	}

	public function part_1(): int {
		return $this->bfs(64);
	}

	public function part_2(): int {
		$steps = $this->is_test ? 5000 : 26501365;
		$mapSize = count($this->data);
		$params = [];
		$lens = [1];
		$startPositions = [implode(',', $this->startPosition)];
		$visited = [implode(',', $this->startPosition) => true];

		for ($i = 0; $i < $steps; $i++) {
			$startPositions = $this->bfs_step($startPositions, $visited, $mapSize);
			$lens[] = count($startPositions);

			if ($i % $mapSize === $steps % $mapSize) {
				$len = 0;
				for ($j = 0; $j < count($lens) - 1; $j++) {
					if ($j % 2 === $i % 2) {
						$len += $lens[$j];
					}
				}
				$params[] = $len;
				if (count($params) === 3) {
					break;
				}
			}
		}

		$p1 = $params[0];
		$p2 = $params[1] - $params[0];
		$p3 = $params[2] - $params[1];
		$ip = intdiv($steps, $mapSize);

		return $p1 + $p2 * $ip + intdiv($ip * ($ip - 1), 2) * ($p3 - $p2);
	}

	private function bfs(int $steps): int {
		$queue = [[$this->startPosition[0], $this->startPosition[1], 0]];
		$visited = [];
		$uniquePlots = [];
		$mapSize = count($this->data);

		while ($queue) {
			[$row, $col, $step] = array_shift($queue);

			if ($step === $steps) {
				$uniquePlots["$row,$col"] = true;
				continue;
			}

			foreach ($this->getNeighbors($row, $col, $mapSize) as $neighbor) {
				[$nRow, $nCol] = $neighbor;
				if (!isset($visited["$nRow,$nCol"][$step + 1])) {
					$visited["$nRow,$nCol"][$step + 1] = true;
					array_push($queue, [$nRow, $nCol, $step + 1]);
				}
			}
		}

		return count($uniquePlots);
	}

	private function bfs_step(array $positions, array &$visited, int $mapSize): array {
		$newPositions = [];
		foreach ($positions as $position) {
			[$row, $col] = explode(',', $position);
			foreach ($this->getNeighbors((int)$row, (int)$col, $mapSize) as $neighbor) {
				$key = implode(',', $neighbor);
				if (!isset($visited[$key])) {
					$visited[$key] = true;
					$newPositions[] = $key; // Storing as a string
				}
			}
		}
		return $newPositions;
	}

	private function getNeighbors(int $row, int $col, int $mapSize): array {
		$neighbors = [];
		foreach ([[-1, 0], [1, 0], [0, -1], [0, 1]] as $delta) {
			$newRow = ($row + $delta[0] + $mapSize) % $mapSize;
			$newCol = ($col + $delta[1] + $mapSize) % $mapSize;
			if ($this->data[$newRow][$newCol] !== '#') {
				$neighbors[] = [$newRow, $newCol];
			}
		}
		return $neighbors;
	}

	private function parse_data(bool $test): void {
		$file = $test ? '/data/day-21-test.txt' : '/data/day-21.txt';
		$this->data = explode("\n", trim(file_get_contents(__DIR__ . $file)));

		foreach ($this->data as $rowIndex => $row) {
			if (($colIndex = strpos($row, 'S')) !== false) {
				$this->startPosition = [$rowIndex, $colIndex];
				break;
			}
		}
	}

	private function isValid(int $row, int $col): bool {
		return isset($this->data[$row][$col]) && $this->data[$row][$col] === '.';
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
	$day21    = new Day21( $test, 1 );
	$result   = $day21->part_1();
	$end      = microtime( true );
	$expected = $test ? 0 : 3716;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day21    = new Day21( $test, 2 );
	$result   = $day21->part_2();
	$end      = microtime( true );
	$expected = $test ? 16733044 : 616583483179597;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
