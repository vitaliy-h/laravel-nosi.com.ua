<?php

namespace Tests\Unit\Shop;

use App\Modules\Shop\Models\Parameter;
use App\Modules\Shop\Repository\ShopRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Tests\TestCase;

class ShopRepositoryHelpersTest extends TestCase
{
    private function invokePrivate(string $method, mixed $argument): mixed
    {
        $reflection = new ReflectionMethod(ShopRepository::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke(app(ShopRepository::class), $argument);
    }

    public static function filterTypeProvider(): array
    {
        return [
            'in'          => [1, 'in'],
            'range'       => [2, 'range'],
            'boolean'     => [3, 'boolean'],
            'find_in_set' => [4, 'find_in_set'],
            'equal'       => [99, 'equal'],
        ];
    }

    #[DataProvider('filterTypeProvider')]
    public function test_get_parameter_filter_type(int $type, string $expected): void
    {
        $parameter = new Parameter(['type' => $type]);

        $this->assertSame($expected, $this->invokePrivate('getParameterFilterType', $parameter));
    }

    public function test_string_to_int_array_filters_invalid_values(): void
    {
        $this->assertSame([1, 2], $this->invokePrivate('stringToIntArray', ['1', '2', 'x']));
    }
}
