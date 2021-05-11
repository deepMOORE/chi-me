<?php

$eps = $GLOBALS['argv'][1] ?? 0.000001;

/** @var IntegratedFn[] $integrals */
$integrals = [
    new IntegratedFn(fn ($x) => sin(1 / ($x * $x)), 3, 5, 'sin(1 / x^2)'),
    new IntegratedFn(fn ($x) => log($x) * sin(1 / ($x * $x * $x)), 3, 4, 'ln(x) * sin(1 / x^3)'),
    new IntegratedFn(fn ($x) => exp(2 * $x) * cos(1 / $x), 1, 2, 'e^(2x) cos (1 / x)'),
    new IntegratedFn(fn ($x) => exp($x) / $x * sin(1 / $x * $x * $x), 2, 3, 'e^x / x * sin(1 / x^3)'),
    new IntegratedFn(fn ($x) => ($x * $x * $x) / ($x * $x) * log($x * $x), 1, 2, '3^x / x^2 * ln(x^2)'),
];

foreach ($integrals as $integral) {
    [$result, $divisionsCount] = execute($integral->function, $integral->a, $integral->b, $eps);

    echo "Интаграл функции: $integral->friendlyDescription в пределах ($integral->a, $integral->b) равен $result" . PHP_EOL;
    echo "Количество делений: $divisionsCount" . PHP_EOL;

    echo PHP_EOL;
}

function execute(Closure $f, float $a, float $b, float $eps): array
{
    $theta = 1 / 15;
    $n = 2;

    $integralValue = ($b - $a) / 6 * ($f($a) + 4 * $f(($a + $b) / 2) + $f($b));

    $delta = 1e18;

    while ($delta - $eps > 1e-12) {
        $n <<= 1;

        $prevIntegralValue = $integralValue;

        $oddSum = 0;
        $evenSum = 0;

        $h = ($b - $a) / $n;
        $oddX = $a + $h;
        $evenX = $oddX + $h;

        for ($k = 0; $k < $n; $k++) {
            $oddSum += $f($oddX);

            if ($k === $n / 2 - 1) {
                break;
            }

            $evenSum += $f($evenX);

            $oddX = $evenX + $h;
            $evenX = $oddX+ $h;
        }

        $integralValue = ($b - $a) / (3 * $n) * ($f($a) + 4 * $oddSum + 2 * $evenSum + $f($b));

        $delta = $theta * abs($integralValue - $prevIntegralValue);
    }

    return [$integralValue, $n];
}

class IntegratedFn
{
    public Closure $function;

    public float $a;

    public float $b;

    public string $friendlyDescription;

    public function __construct(Closure $function, float $a, float $b, string $friendlyDescription)
    {
        $this->function = $function;
        $this->a = $a;
        $this->b = $b;
        $this->friendlyDescription = $friendlyDescription;
    }
}