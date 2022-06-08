<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/ck-editor/imgupload',

        '/test-suite/create',
        '/test-suite/delete',
        '/test-suite/update',

        '/test-case/create',
        '/test-case/get',
        '/test-case/delete',
        '/test-case/update',

        '/get-repository-suites',
        '/get-test-suite',
        '/trcs',
        'tsup',
        'tsuo',
        'tcuo'
    ];


}
