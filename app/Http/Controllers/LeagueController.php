<?php

namespace App\Http\Controllers;


use App\Services\FixtureService;

class LeagueController extends Controller
{
    /**
     * @var FixtureService
     */
    private $fixtureService;

    public function __construct(FixtureService $fixtureService)
    {
        $this->fixtureService = $fixtureService;
    }
}
