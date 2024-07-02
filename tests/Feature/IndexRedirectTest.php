<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function testRedirectToLoginForUnauthenticatedUser()
    {
        $response = $this->get('/');

        // expect redirect because user is unauthenticated
        $response->assertStatus(302);
        $response->assertRedirect(route('login_page'));
    }

    public function testRedirectToProjectsForAuthenticatedUser()
    {

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/');

        // expect redirect because route for index is configured to redirect to
        // projects index page
        $response->assertStatus(302);
        $response->assertRedirect(route('project_list_page'));
    }
}
