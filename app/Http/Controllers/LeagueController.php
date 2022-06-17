<?php

namespace App\Http\Controllers;


use App\Models\Fixtures;
use App\Models\LeagueStages;
use App\Models\Stats;
use App\Services\LeagueService;
use Illuminate\Support\Facades\Log;

class LeagueController extends Controller
{
    /**
     * @var LeagueService
     */
    private LeagueService $leagueService;

    /**
     * LeagueController constructor.
     * @param LeagueService $leagueService
     */
    public function __construct(LeagueService $leagueService)
    {
        $this->leagueService = $leagueService;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('home', [
            'teamsStats' => Stats::all(),
            'currentResults' => LeagueStages::getLastPlayedStage(),
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function playAll()
    {
        try {
            $this->leagueService->playAllStages();
        } catch (\Exception $e) {
            Log::alert('Play all - '.print_r($e->getMessage(), true));
        }
        return response()->redirectTo('/');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function playNext()
    {
        try {
            $this->leagueService->playNextStage();
        } catch (\Exception $e) {
            Log::alert('Play next - '.print_r($e->getMessage(), true));
        }
        return response()->redirectTo('/');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function stages()
    {
        return view('fixtures', [
            'groupFixtures' => Fixtures::all()->groupBy('stage_id'),
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset()
    {
        try {
            $this->leagueService->resetAllLeague();
        } catch (\Exception $e) {
            Log::alert('Reset - '.print_r($e->getMessage(), true));
        }
        return response()->redirectTo('/');
    }
}
