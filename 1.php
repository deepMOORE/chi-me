<?php

function main()
{
    $n = 11;
    $initial = [];

    for ($i = 0; $i < $n; $i++) {
        $initial[$i][0] = 1 + $i * 0.1;
        $initial[$i][1] = exp($initial[$i][0]);
    }

    $x = readline('Введите x:');

    echo 'x = ' . $x . PHP_EOL;

    $result = interpolateLagrange($x, $initial, $n);

    echo 'f(x) = ' . $result . PHP_EOL;
}

function interpolateLagrange($x, $initial, $n): float
{
    $l = 0;
    $p = 1;

    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            if ($i !== $j) {
                $p *= ($x - $initial[$j][0]) / ($initial[$i][0] - $initial[$j][0]);
            }
        }

        $l += $initial[$i][1] * $p;
        $p = 1;
    }

    return $l;
}

main();