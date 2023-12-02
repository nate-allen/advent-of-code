<?php
// The input data, represented as an array of lines
$monkeys = array();
foreach(explode(PHP_EOL, file_get_contents( __DIR__ . '/data/day-20-test.txt' )) as $line) {
	list($name, $job) = explode(": ", $line);
	$monkeys[$name] = $job;
}

// Evaluate the monkey jobs and update their values until the root monkey has a numerical value
// while(true) {
// 	$done = true;
// 	foreach($monkeys as $name => $job) {
// 		// If the monkey's job is a number, skip it
// 		if(is_numeric($job)) continue;
//
// 		// Split the job into its parts
// 		list($left, $symbol, $right) = explode(" ", $job);
//
// 		// If either part of the job is not a number, skip this monkey
// 		if(!is_numeric($monkeys[$left]) || !is_numeric($monkeys[$right])) {
// 			$done = false;
// 			continue;
// 		}
//
// 		// Evaluate the monkey's job and update its value
// 		switch($symbol) {
// 			case "+":
// 				$monkeys[$name] = $monkeys[$left] + $monkeys[$right];
// 				break;
// 			case "-":
// 				$monkeys[$name] = $monkeys[$left] - $monkeys[$right];
// 				break;
// 			case "*":
// 				$monkeys[$name] = $monkeys[$left] * $monkeys[$right];
// 				break;
// 			case "/":
// 				$monkeys[$name] = $monkeys[$left] / $monkeys[$right];
// 				break;
// 		}
// 	}
//
// 	// If all monkeys have numerical values, break out of the loop
// 	if($done) break;
// }
//
// // Print the value of the root monkey
// echo $monkeys['root'];

// Evaluate the monkey jobs and update their values until the root monkey has a numerical value
while(true) {
	$done = true;
	foreach($monkeys as $name => $job) {
		// If the monkey's job is a number, skip it
		if(is_numeric($job)) continue;

		// Split the job into its parts
		list($left, $op, $right) = explode(" ", $job);

		// If either part of the job is not a number, skip this monkey
		if(!is_numeric($monkeys[$left]) || !is_numeric($monkeys[$right])) {
			$done = false;
			continue;
		}

		// Evaluate the monkey's job and update its value
		switch($op) {
			case "+":
				$monkeys[$name] = $monkeys[$left] + $monkeys[$right];
				break;
			case "-":
				$monkeys[$name] = $monkeys[$left] - $monkeys[$right];
				break;
			case "*":
				$monkeys[$name] = $monkeys[$left] * $monkeys[$right];
				break;
			case "/":
				$monkeys[$name] = $monkeys[$left] / $monkeys[$right];
				break;
		}
	}

	// If all monkeys have numerical values, break out of the loop
	if($done) break;
}

// If root's job is not "=", print its value and exit
// if($monkeys['root'] != "=") {
// 	echo $monkeys['root'];
// 	exit;
// }

// Determine the number you need to yell to pass root's equality test
$left = $monkeys[$left];
$right = $monkeys[$right];

// If the left and right numbers are equal, you don't need to do anything
if($left == $right) {
	echo $left;
	exit;
}

// If the left number is larger, subtract the right number from it
if($left > $right) {
	echo $left - $right;
	exit;
}

// If the right number is larger, subtract the left number from it
echo $right - $left;