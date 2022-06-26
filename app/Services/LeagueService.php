<?php

namespace App\Services;


use App\Models\Fixture;
use App\Models\LeagueStage;
use App\Models\Stats;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Console\Kernel;

class LeagueService
{
    /**
     * @var LeagueStage
     */
    private LeagueStage $leagueStageBuilder;
    /**
     * @var Stats
     */
    private Stats $statsBuilder;
    /**
     * @var Fixture
     */
    private Fixture $fixturesBuilder;
    /**
     * @var Team
     */
    private Team $teamsBuilder;
    /**
     * @var Kernel
     */
    private Kernel $kernel;
    /**
     * @var Connection
     */
    private Connection $connection;

    public function __construct(
        LeagueStage $leagueBuilder,
        Stats $statsBuilder,
        Fixture $fixtureBuilder,
        Team $teamsBuilder,
        Connection $connection,
        Kernel $kernel
    )
    {
        $this->leagueStageBuilder = $leagueBuilder;
        $this->statsBuilder = $statsBuilder;
        $this->fixturesBuilder = $fixtureBuilder;
        $this->teamsBuilder = $teamsBuilder;
        $this->connection = $connection;
        $this->kernel = $kernel;
    }

    /**
     * @return Stats[]|Collection
     */
    public function getTeamStats(): Collection
    {
        return $this->statsBuilder->getAllStats();
    }

    /**
     * @return LeagueStage|array|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getLastPlayedStats()
    {
        return $this->leagueStageBuilder->getLastPlayedStage();
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function playAllStages(): void
    {
        $stagesToPlay = $this->leagueStageBuilder->getStagesToPlay();
        if ($stagesToPlay->isEmpty()) {
            throw new \Exception('No league stages to play');
        }

        $this->connection->beginTransaction();
        try {
            foreach ($stagesToPlay as $stage) {
                $this->playSingleStage($stage);
            }
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollback();
            throw new \Exception('Play All Stages error:' . print_r($e->getMessage(), true));
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function playNextStage(): void
    {
        $stageToPlay = $this->leagueStageBuilder->getNextStageToPlay();
        if (!$stageToPlay) {
            throw new \Exception('No league stage to play');
        }
        $this->playSingleStage($stageToPlay);
    }

    /**
     * @throws \Exception
     */
    public function resetAllLeague(): void
    {
        $this->fixturesBuilder->getQuery()->truncate();
        $this->leagueStageBuilder->getQuery()->delete();
        $this->statsBuilder->getQuery()->truncate();
        $this->teamsBuilder->getQuery()->delete();
        $this->kernel->call('db:seed --class=InitSeeder');
    }

    /**
     * @return Fixture[]|Collection
     */
    public function getAllFixtures(): Collection
    {
        return $this->fixturesBuilder->getFixturesGroupedByStages();
    }

    /**
     * @param LeagueStage $stage
     * @throws \Throwable
     */
    private function playSingleStage(LeagueStage $stage): void
    {
        $this->connection->beginTransaction();
        try {
            foreach ($stage->fixtures as $fixture) {
                $this->playSingleFixture($fixture);
            }
            $stage->finished_at = Carbon::now();
            $this->leagueStageBuilder->processSave($stage);
            $this->calculatePredictions();
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollback();
            throw new \Exception('Play Single Stage ' . print_r($e->getMessage(), true));
        }
    }

    /**
     * @param Fixture $fixture
     */
    private function playSingleFixture(Fixture $fixture): void
    {
        if ($fixture->homeTeam->stats->power > $fixture->awayTeam->stats->power) {
            if ($this->isChanceWinner($fixture->awayTeam->stats->power / $fixture->homeTeam->stats->power)) {
                $fixture->away_team_score = $this->generateWinnerScore();
                $fixture->home_team_score = $this->generateLoserScore($fixture->away_team_score);
                $fixture->played_at = Carbon::now();
                $this->fixturesBuilder->processSave($fixture);
                $this->recalculateTeamStats($fixture);
                return;
            }
            $fixture->home_team_score = $this->generateWinnerScore();
            $fixture->away_team_score = $this->generateLoserScore($fixture->home_team_score);
            $fixture->played_at = Carbon::now();
            $this->fixturesBuilder->processSave($fixture);
            $this->recalculateTeamStats($fixture);
            return;
        } else {
            if ($this->isChanceWinner($fixture->homeTeam->stats->power / $fixture->awayTeam->stats->power)) {
                $fixture->home_team_score = $this->generateWinnerScore();
                $fixture->away_team_score = $this->generateLoserScore($fixture->home_team_score);
                $fixture->played_at = Carbon::now();
                $this->fixturesBuilder->processSave($fixture);
                $this->recalculateTeamStats($fixture);
                return;
            }
            $fixture->away_team_score = $this->generateWinnerScore();
            $fixture->home_team_score = $this->generateLoserScore($fixture->away_team_score);
            $fixture->played_at = Carbon::now();
            $this->fixturesBuilder->processSave($fixture);
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
        return rand(Fixture::POSSIBLE_MIN_WIN_SCORE, Fixture::POSSIBLE_MAX_SCORE);
    }

    /**
     * @param int $winnerScore
     * @return int
     */
    private function generateLoserScore(int $winnerScore): int
    {
        return rand(Fixture::POSSIBLE_MIN_SCORE, $winnerScore);
    }

    /**
     * @param Fixture $fixture
     */
    private function recalculateTeamStats(Fixture $fixture): void
    {
        $fixture->homeTeam->stats->played++;
        $fixture->awayTeam->stats->played++;

        if ($fixture->home_team_score > $fixture->away_team_score) {
            $fixture->homeTeam->stats->won++;
            $fixture->homeTeam->stats->points += Fixture::POINTS_FOR_WIN;
            $fixture->homeTeam->stats->power += Fixture::POWER_AFTER_WIN;
            $fixture->awayTeam->stats->loss++;
            $fixture->awayTeam->stats->power -= Fixture::POWER_AFTER_LOSE;
            $this->fixturesBuilder->processPush($fixture);
            return;
        }

        if ($fixture->home_team_score < $fixture->away_team_score) {
            $fixture->awayTeam->stats->won++;
            $fixture->awayTeam->stats->points += Fixture::POINTS_FOR_WIN;
            $fixture->awayTeam->stats->power += Fixture::POWER_AFTER_WIN;
            $fixture->homeTeam->stats->loss++;
            $fixture->homeTeam->stats->power -= Fixture::POWER_AFTER_LOSE;
            $this->fixturesBuilder->processPush($fixture);
            return;
        }

        if ($fixture->home_team_score === $fixture->away_team_score) {
            $fixture->homeTeam->stats->draw++;
            $fixture->homeTeam->stats->points += Fixture::POINTS_FOR_DRAW;
            $fixture->awayTeam->stats->draw++;
            $fixture->awayTeam->stats->points += Fixture::POINTS_FOR_DRAW;
            $this->fixturesBuilder->processPush($fixture);
            return;
        }
    }

    private function calculatePredictions(): void
    {
        $teamsStatsList = $this->statsBuilder->getAllStats();
        $stagesToPlay = $this->leagueStageBuilder->getStagesToPlay();
        if ($stagesToPlay->isEmpty()) {
            $this->setLeagueWinner($teamsStatsList);
            return;
        }

        $totalPoints = $teamsStatsList->sum(function ($teamStats) {
            /** @var Stats $teamStats */
            return $teamStats->points;
        });

        foreach ($teamsStatsList as $teamStats) {
            /** @var Stats $teamStats */
            $teamStats->prediction = $teamStats->points / $totalPoints * 100;
            $this->statsBuilder->processSave($teamStats);
        }

        $this->calculatePossibleFuture($stagesToPlay);
    }

    /**
     * @param Collection<Stats> $teamsStatsList
     */
    private function setLeagueWinner(Collection $teamsStatsList): void
    {
        $this->statsBuilder->getQuery()->update(['prediction' => 0]);
        /** @var Stats $leagueWinner */
        $leagueWinner = $teamsStatsList->sortBy(function ($post) {
            return $post->points;
        }, SORT_NUMERIC, true)->first();
        $leagueWinner->prediction = 100;
        $this->statsBuilder->processSave($leagueWinner);
    }

    /**
     * @param Collection<LeagueStage> $stagesToPlay
     */
    private function calculatePossibleFuture(Collection $stagesToPlay): void
    {
        $totalPoints = 0;
        $predictionList = [];
        foreach ($stagesToPlay as $stage) {
            /** @var LeagueStage $stage */
            foreach ($stage->fixtures as $fixture) {
                /** @var Fixture $fixture */
                $predictionList[$fixture->id]['homeTeam']['points'] = $fixture->homeTeam->stats->points;
                $predictionList[$fixture->id]['awayTeam']['points'] = $fixture->awayTeam->stats->points;
                $predictionList[$fixture->id]['homeTeam']['power'] = $fixture->homeTeam->stats->power;
                $predictionList[$fixture->id]['awayTeam']['power'] = $fixture->awayTeam->stats->power;
                $this->predictSingleFixtureResults($predictionList);
                $totalPoints += $predictionList[$fixture->id]['homeTeam']['points'] + $predictionList[$fixture->id]['awayTeam']['points'];
            }
        }

        $fixturesList = $this->fixturesBuilder->getFixturesByIds(array_keys($predictionList));
        foreach ($predictionList as $fixtureID => $fixture) {
            /** @var Fixture $targetFixture */
            $targetFixture = $fixturesList->find($fixtureID);
            $targetFixture->homeTeam->stats->prediction = $fixture['homeTeam']['points'] / $totalPoints * 100;
            $targetFixture->awayTeam->stats->prediction = $fixture['awayTeam']['points'] / $totalPoints * 100;
            $this->fixturesBuilder->processPush($targetFixture);
        }
    }

    /**
     * @param array $predictionList
     */
    private function predictSingleFixtureResults(array &$predictionList): void
    {
        foreach ($predictionList as $fixture) {
            if ($fixture['homeTeam']['power'] > $fixture['awayTeam']['power']) {
                if ($this->isChanceWinner($fixture['awayTeam']['power'] / $fixture['homeTeam']['power'])) {
                    $fixture['awayTeam']['score'] = $this->generateWinnerScore();
                    $fixture['homeTeam']['score'] = $this->generateLoserScore($fixture['awayTeam']['score']);
                    $this->predictTeamStats($fixture);
                    return;
                }
                $fixture['homeTeam']['score'] = $this->generateWinnerScore();
                $fixture['awayTeam']['score'] = $this->generateLoserScore($fixture['homeTeam']['score']);
                $this->predictTeamStats($fixture);
                return;
            } else {
                if ($this->isChanceWinner($fixture['homeTeam']['power'] / $fixture['awayTeam']['power'])) {
                    $fixture['homeTeam']['score'] = $this->generateWinnerScore();
                    $fixture['awayTeam']['score'] = $this->generateLoserScore($fixture['homeTeam']['score']);
                    $this->predictTeamStats($fixture);
                    return;
                }
                $fixture['awayTeam']['score'] = $this->generateWinnerScore();
                $fixture['homeTeam']['score'] = $this->generateLoserScore($fixture['awayTeam']['score']);
                $this->predictTeamStats($fixture);
                return;
            }
        }
    }

    /**
     * @param array $fixture
     */
    private function predictTeamStats(array &$fixture): void
    {
        if ($fixture['homeTeam']['score'] > $fixture['awayTeam']['score']) {
            $fixture['homeTeam']['points'] += Fixture::POINTS_FOR_WIN;
            $fixture['homeTeam']['power'] += Fixture::POWER_AFTER_WIN;
            $fixture['awayTeam']['power'] -= Fixture::POWER_AFTER_LOSE;
        }

        if ($fixture['homeTeam']['score'] < $fixture['awayTeam']['score']) {
            $fixture['awayTeam']['points'] += Fixture::POINTS_FOR_WIN;
            $fixture['awayTeam']['power'] += Fixture::POWER_AFTER_WIN;
            $fixture['homeTeam']['power'] -= Fixture::POWER_AFTER_LOSE;
            return;
        }

        if ($fixture['homeTeam']['score'] === $fixture['awayTeam']['score']) {
            $fixture['homeTeam']['points'] += Fixture::POINTS_FOR_DRAW;
            $fixture['awayTeam']['points'] += Fixture::POINTS_FOR_DRAW;
            return;
        }
    }
}
