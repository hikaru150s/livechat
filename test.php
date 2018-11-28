<?php
function f($x, $r) {
	return $r * $x * (1 - $x);
}

function linearSpace($a, $b, $n) {
	$step = ($b - $a) / ($n - 1);
	return range($a, $b, $step);
}

function logisticMap($publicKey) {
	$n = 32;
	$a = 2.0;
	$b = 4;
	$rs = linearSpace($a, $b, $n);
	$m = 100;
	$result = array();
	for ($j = 0; $j < count($rs); $j++) {
		$r = $rs[$j];
		$x = array_fill(0, $m, 0);
		$x[0] = 0.5;
		for ($i = 1; $i < $m; $i++) {
			$x[$i] = $r * $x[$i - 1] * (1 - $x[$i - 1]);
		}
		$result[$j] = round($x[$m - 1] * $publicKey[$j]);
	}
	return $result;
}

function hanonMap($publicKey) {
	$a = 1.4;
	$b = 0.3;
	$iterations = 100;
	$x = array_fill(0, $iterations + 1, 0);
	$y = array_fill(0, $iterations + 1, 0);
	$result = array();
	for ($j = 0; $j < 32; $j++) {
		for ($i = 0; $i < $iterations; $i++) {
			$x[$i + 1] = 1 - ($a * $x[$i] ^ 2) + $y[$i];
			$y[$i + 1] = $b * $x[$i];
		}
		$result[$j] = round($x[$iterations - 1] * $publicKey[$j]);
	}
	return $result;
}

$key = array();
for ($c = 0; $c < 32; $c++) {
	$key[] = rand(0, 255);
}

var_dump([
	'log' => logisticMap($key),
	'han' => hanonMap($key)
]);
