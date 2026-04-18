<?php

namespace Tests\Unit;

use App\Domain\Prescription\PrescriptionValidator;
use PHPUnit\Framework\TestCase;

class PrescriptionValidatorTest extends TestCase
{
    public function test_it_requires_end_date_when_status_is_completed(): void
    {
        $validator = new PrescriptionValidator();

        $result = $validator->requiresEndDate('completed', null);

        $this->assertTrue($result);
    }

    public function test_it_does_not_require_end_date_when_status_is_active(): void
    {
        $validator = new PrescriptionValidator();

        $result = $validator->requiresEndDate('active', null);

        $this->assertFalse($result);
    }

    public function test_it_does_not_require_end_date_when_present(): void
    {
        $validator = new PrescriptionValidator();

        $result = $validator->requiresEndDate('completed', '2026-01-01');

        $this->assertFalse($result);
    }
}
