<?php

namespace App\Events;

use App\Models\Administration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdministrationCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Administration $administration
    ) {
    }
}
