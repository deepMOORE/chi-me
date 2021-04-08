<?php

const EPS = 1e-9;

main();

function main() {
	$table = [
	    ['x' => 0, 'y' => 2],
        ['x' => 0.1, 'y' => 2.105],
        ['x' => 0.2, 'y' => 2.221],
        ['x' => 0.3, 'y' => 2.349],
        ['x' => 0.4, 'y' => 2.491],
        ['x' => 0.5, 'y' => 2.648],
        ['x' => 0.6, 'y' => 2.822],
        ['x' => 0.7, 'y' => 3.013],
        ['x' => 0.8, 'y' => 3.225],
        ['x' => 0.9, 'y' => 3.459],
        ['x' => 1, 'y' => 3.718],
    ];


    $key_value_table = [];

    foreach ($table as $item) {
        $key_value_table[] = [$item['x'], $item['y']];
    }


	$x = 2;

	$coefficients = compute_coefficients($key_value_table);

    echo 'Коэфициэнты: ' . json_encode($coefficients, JSON_THROW_ON_ERROR) . PHP_EOL;

	$sInt = spline_interpolation($key_value_table, $x, $coefficients);

    echo 'Сплайны: ' . json_encode($sInt, JSON_THROW_ON_ERROR) . PHP_EOL;
}

function spline_interpolation(array $key_value_table, float $x, array $coefficients) {
    $count = count($key_value_table);
    
    for ($i = 1; $i < $count; $i++) {
        if ($x - $key_value_table[$i][0] < EPS && $key_value_table[$i - 1][0] - $x < EPS) {
            $a = $coefficients[$i][0];
			$b = $coefficients[$i][1];
			$c = $coefficients[$i][2];
			$d = $coefficients[$i][3];

			$diff = $x - $key_value_table[$i][0];

			return $a + $b * $diff + $c / 2 * $diff * $diff + $d / 6 * $diff * $diff * $diff;
		}
    }

	return -1e9;
}

function compute_coefficients(array $key_value_table) {
    $n = count($key_value_table);

	$system_matrix = [];
 	$vector = [];

	$system_matrix[0][0] = $system_matrix[$n - 1][$n - 1] = 1;
	for ($i = 1; $i < $n - 1; $i++) {
        $h_cnt =  $key_value_table[$i][0] -  $key_value_table[$i - 1][0];
		$h_next =  $key_value_table[$i + 1][0] -  $key_value_table[$i][0];

		$system_matrix[$i][$i - 1] = $h_cnt;
		$system_matrix[$i][$i] = 2 * ($h_cnt + $h_next);
		$system_matrix[$i][$i + 1] = $h_next;

		$f_cnt =  $key_value_table[$i][1] -  $key_value_table[$i - 1][1];
		$f_next =  $key_value_table[$i + 1][1] -  $key_value_table[$i][1];

		$vector[$i] = 6 * ($f_next / $h_next - $f_cnt / $h_cnt);
	}

	$solution = solveSystem($system_matrix, $vector);

	$result = [];

	for ($i = 1; $i < $n - 1; $i++) {
        $h =  $key_value_table[$i][0] -  $key_value_table[$i - 1][0];

		$c = $solution[$i];
		$a =  $key_value_table[$i][1];
		$d = ($c - $solution[$i - 1]) / 3 / $h;
		$b = ($a -  $key_value_table[$i - 1][1]) / $h + (2 * $c + $solution[$i - 1]) / 3 * $h;

		$result[$i][0] = $a;
		$result[$i][1] = $b;
		$result[$i][2] = $c;
		$result[$i][3] = $d;
	}

	$h =  $key_value_table[$n - 1][0] -  $key_value_table[$n - 2][0];
	$result[$n - 1][0] =  $key_value_table[$n - 1][1];
	$result[$n - 1][1] = ( $key_value_table[$n - 1][1] -  $key_value_table[$n - 2][1]) / $h + $solution[$n - 2] / 3 * $h;
	$result[$n - 1][2] = 0;
	$result[$n - 1][3] = -$solution[$n - 2] / 3 / $h;

	return $result;
}

function solveSystem(array $system_matrix, array $vector) {
    $n = count($system_matrix);

	$alpha = [];
	$beta = [];

	$alpha[0] = $system_matrix[0][1] / $system_matrix[0][0];
	$beta[0] = $vector[0] / $system_matrix[0][0];
	for ($i = 1; $i < $n - 1; $i++)	{
        $alpha[$i] = $system_matrix[$i][$i + 1] / ($system_matrix[$i][$i] - $system_matrix[$i][$i - 1] * $alpha[$i - 1]);
        $beta[$i] = ($vector[$i] - $system_matrix[$i][$i - 1] * $beta[$i - 1]) / ($system_matrix[$i][$i] - $system_matrix[$i][$i - 1] * $alpha[$i - 1]);
    }
	$beta[$n - 1] = ($vector[$n - 1] - $system_matrix[$n - 1][$n - 1 - 1] * $beta[$n - 1 - 1]) / ($system_matrix[$n - 1][$n - 1] - $system_matrix[$n - 1][$n - 1 - 1] * $alpha[$n - 1 - 1]);

	$solution = [];

    $solution[$n - 1] = $beta[$n - 1];
	for ($i = $n - 2; $i >= 0; $i--) {
        $solution[$i] = $beta[$i] - $alpha[$i] * $solution[$i + 1];
    }

	return $solution;
}
