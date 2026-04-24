<?php

namespace App\Domain\Administration;

use Carbon\Carbon;

class AdministrationValidator
{
    public function isBeforeStart(string $administeredAt, string $startDate): bool
    {
        return Carbon::parse($administeredAt)->lt(Carbon::parse($startDate));
    }

    public function isAfterEnd(string $administeredAt, string $endDate): bool
    {
        return Carbon::parse($administeredAt)->gt(Carbon::parse($endDate));
    }
}
