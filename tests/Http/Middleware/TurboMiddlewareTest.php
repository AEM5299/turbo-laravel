<?php

namespace Tonysm\TurboLaravel\Tests\Http\Middleware;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Tonysm\TurboLaravel\Http\Middleware\TurboMiddleware;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\TurboLaravelFacade;

class TurboMiddlewareTest extends TestCase
{
    /** @test */
    public function doesnt_change_response_when_not_turbo_visit()
    {
        $request = Request::create('/source');
        $request->headers->add([
            'Accept' => 'text/html;',
        ]);
        $response = new RedirectResponse('/destination');
        $next = function () use ($response) {
            return $response;
        };

        $result = (new TurboMiddleware())->handle($request, $next);

        $this->assertEquals($response->getTargetUrl(), $result->getTargetUrl());
        $this->assertEquals(302, $result->getStatusCode());
    }

    /** @test */
    public function handles_redirect_responses()
    {
        $request = Request::create('/source');
        $request->headers->add([
            'Accept' => 'text/html; turbo-stream, text/html, application/xhtml+xml',
        ]);
        $response = new RedirectResponse('/destination');
        $next = function () use ($response) {
            return $response;
        };

        $result = (new TurboMiddleware())->handle($request, $next);

        $this->assertEquals($response->getTargetUrl(), $result->getTargetUrl());
        $this->assertEquals(303, $result->getStatusCode());
    }

    /** @test */
    public function can_detect_turbo_native_visits()
    {
        $this->assertFalse(
            TurboLaravelFacade::isTurboNativeVisit(),
            'Expected to not have started saying it is a Turbo Native visit, but it said it is.'
        );

        $request = Request::create('/source');
        $request->headers->add([
            'User-Agent' => 'Turbo Native Android',
        ]);
        $next = function () {
        };

        (new TurboMiddleware())->handle($request, $next);

        $this->assertTrue(
            TurboLaravelFacade::isTurboNativeVisit(),
            'Expected to have detected a Turbo Native visit, but it did not.'
        );
    }
}