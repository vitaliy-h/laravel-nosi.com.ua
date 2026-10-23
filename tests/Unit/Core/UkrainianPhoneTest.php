<?php

namespace Tests\Unit\Core;

use App\Modules\Core\Rules\UkrainianPhone;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UkrainianPhoneTest extends TestCase
{
    public static function typedNumbers(): array
    {
        return [
            'local'               => ['0671234567', '+380671234567'],
            'local with spaces'   => ['067 123 45 67', '+380671234567'],
            'full with brackets'  => ['+38 (067) 123-45-67', '+380671234567'],
            'without plus'        => ['380671234567', '+380671234567'],
            'old 8 prefix'        => ['80671234567', '+380671234567'],
            'already normalized'  => ['+380671234567', '+380671234567'],
        ];
    }

    #[DataProvider('typedNumbers')]
    public function test_it_normalizes_the_ways_a_customer_types_a_number(string $typed, string $expected): void
    {
        $this->assertSame($expected, UkrainianPhone::normalize($typed));
    }

    public function test_a_foreign_or_broken_number_is_left_as_typed(): void
    {
        $this->assertSame('+48 512 345 678', UkrainianPhone::normalize('+48 512 345 678'));
        $this->assertSame('067 123', UkrainianPhone::normalize('067 123'));
        $this->assertNull(UkrainianPhone::normalize(null));
    }

    public function test_only_the_normalized_form_passes(): void
    {
        $passes = fn (string $phone) => Validator::make(['phone' => $phone], ['phone' => [new UkrainianPhone()]])->passes();

        $this->assertTrue($passes('+380671234567'));
        $this->assertFalse($passes('0671234567'));
        $this->assertFalse($passes('+48512345678'));
        $this->assertFalse($passes('+3806712345678'));
    }
}
