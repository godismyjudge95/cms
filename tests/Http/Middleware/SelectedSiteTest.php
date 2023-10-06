<?php

namespace Tests\Http\Middleware;

use Illuminate\Http\Request;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\SelectedSite;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SelectedSiteTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    /**
     * @test
     */
    public function it_sets_selected_site_first_authorized_one()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
            'de' => ['url' => '/de/', 'locale' => 'de'],
        ]]);

        Site::setSelected('de');
        $this->assertEquals('de', Site::selected()->handle());

        $this->setTestRoles(['test' => ['access fr site']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->actingAs($user);
        $request = $this->createRequest('/cp/foo');
        $handled = false;

        (new SelectedSite())->handle($request, function () use (&$handled) {
            $handled = true;

            return new Response;
        });

        $this->assertTrue($handled);
        $this->assertEquals('fr', Site::selected()->handle());
    }

    /**
     * @test
     */
    public function middleware_attached_to_routes()
    {
        /** @var Router $router */
        $router = app('router');
        $this->assertTrue(in_array(SelectedSite::class, $router->getMiddlewareGroups()['statamic.cp.authenticated']));
    }

    private function createRequest($url)
    {
        $symfonyRequest = SymfonyRequest::create($url);
        $request = Request::createFromBase($symfonyRequest);
        app()->instance('request', $request);

        return $request;
    }
}
