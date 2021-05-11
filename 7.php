<?php


$function = new IntegratedFn(
    fn ($x, $y, $z) => $x * $x * $x,
    [[1, 5], [2, 4], [1, 2]],
    'x ^ 3'
);

$testsCount = 1e4;

$integrals = [];

echo 'Значения интегралов' . PHP_EOL;
foreach (range(0, 2) as $_) {
    echo 'При количестве испытаний = ' . $testsCount . '; f(x) = ';

    $integrals[] = execute($function,  $testsCount);

    echo end($integrals) . PHP_EOL;

    $testsCount *= 1e2;
}

echo 'Модули разности' . PHP_EOL;
foreach (range(0, 1) as $i) {
    $diff = abs($integrals[$i] - $integrals[$i + 1]);

    echo '|I' . ($i + 1) . '} - I' . ($i + 2) . "| = " . $diff . PHP_EOL;
}

function execute(IntegratedFn $function, int $n): float
{
    $result = 0;

    for ($i = 0; $i < $n; $i++) {
        $x = $function->bounds[0][0] + (($function->bounds[0][1] - $function->bounds[0][0]) * random_int(0, PHP_INT_MAX) / PHP_INT_MAX);
        $y = $function->bounds[1][0] + (($function->bounds[1][1] - $function->bounds[1][0]) * random_int(0, PHP_INT_MAX) / PHP_INT_MAX);
        $z = $function->bounds[2][0] + (($function->bounds[2][1] - $function->bounds[2][0]) * random_int(0, PHP_INT_MAX) / PHP_INT_MAX);

        $fn = $function->function;

        $result += $fn($x, $y, $z);
    }

    foreach ($function->bounds as $bound) {
        $result *= $bound[1] - $bound[0];
    }

    $result /= $n;

    return $result;
}

class IntegratedFn
{
    public Closure $function;

    public array $bounds;

    public string $friendlyDescription;

    public function __construct(Closure $function, array $bounds, string $friendlyDescription)
    {
        $this->function = $function;
        $this->bounds = $bounds;
        $this->friendlyDescription = $friendlyDescription;
    }
}
