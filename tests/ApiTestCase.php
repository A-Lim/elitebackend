<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class ApiTestCase extends BaseTestCase {
    use CreatesApplication;

    protected $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ];
}
