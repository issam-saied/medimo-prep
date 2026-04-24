<?php

namespace Tests\Unit;

use App\Domain\Administration\AdministrationValidator;
use PHPUnit\Framework\TestCase;

class AdministrationValidatorTest extends TestCase
{
    private AdministrationValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new AdministrationValidator();
    }

    public function test_it_detects_administered_at_before_start_date(): void
    {
        $result = $this->validator->isBeforeStart('2026-01-01 08:00:00', '2026-01-02');

        $this->assertTrue($result);
    }

    public function test_it_allows_administered_at_on_start_date(): void
    {
        $result = $this->validator->isBeforeStart('2026-01-01 00:00:00', '2026-01-01');

        $this->assertFalse($result);
    }

    public function test_it_allows_administered_at_after_start_date(): void
    {
        $result = $this->validator->isBeforeStart('2026-01-05 10:00:00', '2026-01-01');

        $this->assertFalse($result);
    }

    public function test_it_detects_administered_at_after_end_date(): void
    {
        $result = $this->validator->isAfterEnd('2026-01-10 08:00:00', '2026-01-05');

        $this->assertTrue($result);
    }

    public function test_it_allows_administered_at_on_end_datetime(): void
    {
        $result = $this->validator->isAfterEnd('2026-01-05 14:00:00', '2026-01-05 18:00:00');

        $this->assertFalse($result);
    }

    public function test_it_allows_administered_at_before_end_date(): void
    {
        $result = $this->validator->isAfterEnd('2026-01-03 10:00:00', '2026-01-05');

        $this->assertFalse($result);
    }
}
