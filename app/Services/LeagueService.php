<?php

namespace App\Services;


use App\Models\Fixtures;
use App\Models\LeagueStages;
use App\Models\Stats;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class LeagueService
{
    /**
     * @param array $teams
     * @return array
     */
    public static function generateRoundRobin(array $teams)
    {
        $firstPart = [];
        $lastPart = [];
        while (count($firstPart) < count($teams) / 2 * (count($teams) - 1)) {
            for ($i = 0; $i < count($teams); $i += 2) {
                $firstPart[] = $teams[$i] . Fixtures::FIXTURE_DELIMITER . $teams[$i + 1];
                $lastPart[] = $teams[$i + 1] . Fixtures::FIXTURE_DELIMITER . $teams[$i];
            }
            for ($i = count($teams) - 1; $i > 1; $i--) {
                $temp = $teams[$i - 1];
                $teams[$i - 1] = $teams[$i];
                $teams[$i] = $temp;
            }
        }

        return array_merge($firstPart, $lastPart);
    }

    /**
     * @throws \Exception
     */
    public function playAllStages()
    {
        $stagesToPlay = LeagueStages::whereNull(['finished_at'])->get();
        if (!$stagesToPlay) {
            throw new \Exception('No league stages to play');
        }
        foreach ($stagesToPlay as $stage) {
            $this->playSingleStage($stage);
        }
    }

    /**
     * @throws \Exception
     */
    public function playNextStage()
    {
        $stageToPlay = LeagueStages::whereNull(['finished_at'])->orderBy('id', 'ASC')->first();
        if (!$stageToPlay) {
            throw new \Exception('No league stage to play');
        }
        $this->playSingleStage($stageToPlay);
    }

    /**
     * @throws \Exception
     */
    public function resetAllLeague()
    {
        DB::table('fixtures')->truncate();
        DB::table('league_stages')->delete();
        DB::table('stats')->truncate();
        DB::table('teams')->delete();
        Artisan::call('db:seed --class=InitSeeder');
    }

    /**
     * @param LeagueStages $stage
     */
    private function playSingleStage(LeagueStages $stage)
    {
        foreach ($stage->fixtures as $fixture) {
            $this->playSingleFixture($fixture);
        }
        $stage->finished_at = Carbon::now();
        $stage->save();
        $this->calculatePredictions();
    }

    /**
     * @param Fixtures $fixture
     */
    private function playSingleFixture(Fixtures $fixture)
    {
        if ($fixture->homeTeam->stats->power > $fixture->awayTeam->stats->power) {
            if ($this->isChanceWinner($fixture->awayTeam->stats->power / $fixture->homeTeam->stats->power)) {
                $fixture->away_team_score = $this->generateWinnerScore();
                $fixture->home_team_score = $this->generateLoserScore($fixture->away_team_score);
                $fixture->played_at = Carbon::now();
                $fixture->save();
                $this->recalculateTeamStats($fixture);
                return;
            }
            $fixture->home_team_score = $this->generateWinnerScore();
            $fixture->away_team_score = $this->generateLoserScore($fixture->home_team_score);
            $fixture->played_at = Carbon::now();
            $fixture->save();
            $this->recalculateTeamStats($fixture);
            return;
        } else {
            if ($this->isChanceWinner($fixture->homeTeam->stats->power / $fixture->awayTeam->stats->power)) {
                $fixture->home_team_score = $this->generateWinnerScore();
                $fixture->away_team_score = $this->generateLoserScore($fixture->home_team_score);
                $fixture->played_at = Carbon::now();
                $fixture->save();
                $this->recalculateTeamStats($fixture);
                return;
            }
            $fixture->away_team_score = $this->generateWinnerScore();
            $fixture->home_team_score = $this->generateLoserScore($fixture->away_team_score);
            $fixture->played_at = Carbon::now();
            $fixture->save();
            $this->recalculateTeamStats($fixture);
            return;
        }
    }

    /**
     * @param float $chance
     * @return bool
     */
    private function isChanceWinner(float $chance): bool
    {
        $random = rand(0, 10) / 10;
        return $random < $chance;
    }

    /**
     * @return int
     */
    private function generateWinnerScore(): int
    {
        return rand(Fixtures::POSSIBLE_MIN_WIN_SCORE, Fixtures::POSSIBLE_MAX_SCORE);
    }

    /**
     * @param int $winnerScore
     * @return int
     */
    private function generateLoserScore(int $winnerScore): int
    {
        return rand(Fixtures::POSSIBLE_MIN_SCORE, $winnerScore);
    }

    /**
     * @param Fixtures $fixture
     */
    private function recalculateTeamStats(Fixtures $fixture)
    {
        $fixture->homeTeam->stats->played++;
        $fixture->awayTeam->stats->played++;

        if ($fixture->home_team_score > $fixture->away_team_score) {
            $fixture->homeTeam->stats->won++;
            $fixture->homeTeam->stats->points += Fixtures::POINTS_FOR_WIN;
            $fixture->homeTeam->stats->power += Fixtures::POWER_AFTER_WIN;
            $fixture->awayTeam->stats->loss++;
            $fixture->awayTeam->stats->power -= Fixtures::POWER_AFTER_LOSE;
            $fixture->push();
            return;
        }

        if ($fixture->home_team_score < $fixture->away_team_score) {
            $fixture->awayTeam->stats->won++;
            $fixture->awayTeam->stats->points += Fixtures::POINTS_FOR_WIN;
            $fixture->awayTeam->stats->power += Fixtures::POWER_AFTER_WIN;
            $fixture->homeTeam->stats->loss++;
            $fixture->homeTeam->stats->power -= Fixtures::POWER_AFTER_LOSE;
            $fixture->push();
            return;
        }

        if ($fixture->home_team_score === $fixture->away_team_score) {
            $fixture->homeTeam->stats->draw++;
            $fixture->homeTeam->stats->points += Fixtures::POINTS_FOR_DRAW;
            $fixture->awayTeam->stats->draw++;
            $fixture->awayTeam->stats->points += Fixtures::POINTS_FOR_DRAW;
            $fixture->push();
            return;
        }
    }

    private function calculatePredictions()
    {
        //TODO: implement complex prediction calculation after middle of the league
        $teamsStatsList = Stats::all();
        $totalPoints = $teamsStatsList->sum(function ($teamStats) {
            /** @var Stats $teamStats */
            return $teamStats->points;
        });

        foreach ($teamsStatsList as $teamStats) {
            /** @var Stats $teamStats */
            $teamStats->prediction = $teamStats->points / $totalPoints * 100;
            $teamStats->save();
        }
    }
}