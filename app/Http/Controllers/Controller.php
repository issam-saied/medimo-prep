<?php

namespace App\Http\Controllers;

/**
 * @mixin \Illuminate\Foundation\Auth\Access\AuthorizesRequests
 */
abstract class Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
}
