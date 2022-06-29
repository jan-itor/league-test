<?php

namespace Tests\Feature;

use App\Services\LeagueService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery\MockInterface;
use Tests\TestCase;

class LeagueControllerTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_home()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_play_all()
    {
        $this->mock(LeagueService::class, function (MockInterface $mock) {
            $mock->shouldReceive('playAllStages')->once();
        });

        $response = $this->get('/play-all');

        $response->assertStatus(302);
    }

    public function test_play_all_with_exception()
    {
        $this->mock(LeagueService::class, function (MockInterface $mock) {
            $mock->shouldReceive('playAllStages')->andThrow('Exception');
        });

        $response = $this->get('/play-all');

        $response->assertStatus(302);
    }

    public function test_play_next()
    {
        $this->mock(LeagueService::class, function (MockInterface $mock) {
            $mock->shouldReceive('playNextStage')->once();
        });

        $response = $this->get('/play-next');

        $response->assertStatus(302);
    }

    public function test_play_next_with_exception()
    {
        $this->mock(LeagueService::class, function (MockInterface $mock) {
            $mock->shouldReceive('playNextStage')->andThrow('Exception');
        });

        $response = $this->get('/play-next');

        $response->assertStatus(302);
    }

    public function test_stages()
    {
        $response = $this->get('/stages');

        $response->assertStatus(200);
    }

    public function test_reset()
    {
        $this->mock(LeagueService::class, function (MockInterface $mock) {
            $mock->shouldReceive('resetAllLeague')->once();
        });

        $response = $this->get('/reset');

        $response->assertStatus(302);
    }

    public function test_reset_with_exception()
    {
        $this->mock(LeagueService::class, function (MockInterface $mock) {
            $mock->shouldReceive('resetAllLeague')->andThrow('Exception');
        });

        $response = $this->get('/reset');

        $response->assertStatus(302);
    }
}
