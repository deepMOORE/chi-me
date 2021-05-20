<?php declare(strict_types=1);

$iterationsCount = (int)($GLOBALS['argv'][1] ?? 2);

$integral = new Integral(
    fn ($x) => sin(1 / ($x * $x)),
    1,
    2,
    'sin( 1 / x^2 )'
);

echo "Подсчет интеграла: " . PHP_EOL;
echo "Интеграл функции $integral->friendlyDescription от $integral->from до $integral->to." . PHP_EOL;
echo "Выбранное количество итераций: $iterationsCount" . PHP_EOL;

$result = execute($integral, $iterationsCount);
echo "Интеграл равен: " . $result . PHP_EOL;
echo PHP_EOL;

$prevResult = null;

echo "Подсчет разностей: " . PHP_EOL;
foreach (range(3, 1) as $i) {
    $result = execute($integral, $i);

    if ($prevResult === null) {
        $prevResult = $result;

        continue;
    }

    $difference = $result - $prevResult;
    $iMinus1 = $i - 1;

    echo "Разница между $i и $iMinus1 равна " . $difference . PHP_EOL;
}

function execute(Integral $integral, int $iterations): float
{
    $result = 0;

    $fn = $integral->fn;

    $coefficients = getCoefficientsMap($iterations);

    $firstFactor = 0.5 * ($integral->to - $integral->from);

    foreach ($coefficients as $coefficient) {
        $x = 0.5 * ($integral->from + $integral->to + $coefficient['t'] * ($integral->to - $integral->from));

        $result += $firstFactor * $coefficient['omega'] * $fn($x);
    }

    return $result;
}

class Integral
{
    public Closure $fn;

    public float $from;

    public float $to;

    public string $friendlyDescription;

    public function __construct(Closure $function, float $from, float $to, string $friendlyDescription)
    {
        $this->fn = $function;
        $this->from = $from;
        $this->to = $to;
        $this->friendlyDescription = $friendlyDescription;
    }
}

function getCoefficientsMap(int $iteration)
{
    return [
        [
            [
                't' => 0,
                'omega' => 2
            ]
        ],
        [
            [
                't' => 0.577,
                'omega' => 1
            ],
            [
                't' => -0.577,
                'omega' => 1
            ]
        ],
        [
            [
                't' => 0,
                'omega' => 0.888
            ],
            [
                't' => 0.774,
                'omega' => 0.555
            ],
            [
                't' => -0.774,
                'omega' => 0.555
            ]
        ],
        [
            [
                't' => 0.339,
                'omega' => 0.652
            ],
            [
                't' => -0.339,
                'omega' => 0.652
            ],
            [
                't' => 0.861,
                'omega' => 0.347
            ],
            [
                't' => -0.861,
                'omega' => 0.347
            ],
        ],
    ][$iteration];
}