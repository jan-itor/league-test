<?php

namespace App\Http\Controllers;


use App\Services\LeagueService;
use Illuminate\Log\Logger;

class LeagueController extends Controller
{
    /**
     * @var LeagueService
     */
    private LeagueService $leagueService;
    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * LeagueController constructor.
     * @param LeagueService $leagueService
     * @param Logger $logger
     */
    public function __construct(LeagueService $leagueService, Logger $logger)
    {
        $this->leagueService = $leagueService;
        $this->logger = $logger;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('home', [
            'teamsStats' => $this->leagueService->getTeamStats(),
            'currentResults' => $this->leagueService->getLastPlayedStats(),
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws \Throwable
     */
    public function playAll()
    {
        try {
            $this->leagueService->playAllStages();
        } catch (\Exception $e) {
            $this->logger->alert('Play all - ' . print_r($e->getMessage(), true));
            return response('See errors in logs', 400);
        }
        return response()->redirectTo('/');
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws \Throwable
     */
    public function playNext()
    {
        try {
            $this->leagueService->playNextStage();
        } catch (\Exception $e) {
            $this->logger->alert('Play next - ' . print_r($e->getMessage(), true));
            return response('See errors in logs', 400);
        }
        return response()->redirectTo('/');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function stages()
    {
        return view('fixtures', [
            'groupFixtures' => $this->leagueService->getAllFixtures(),
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function reset()
    {
        try {
            $this->leagueService->resetAllLeague();
        } catch (\Exception $e) {
            $this->logger->alert('Reset - ' . print_r($e->getMessage(), true));
            return response('See errors in logs', 400);
        }
        return response()->redirectTo('/');
    }
}
