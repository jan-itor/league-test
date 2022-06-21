<?php

namespace Database\Seeders;

use App\Models\Fixture;
use App\Models\LeagueStage;
use App\Models\Stats;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Services\LeagueService;

class InitSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        try {
            $initTeamsData = [
                'Arsenal' => 5,
                'Liverpool' => 2,
                'Manchester City' => 3,
                'Chelsea' => 4,
            ];

            foreach ($initTeamsData as $name => $power) {
                $newTeam = Team::create(['name' => $name]);
                Stats::create(['team_id' => $newTeam->id, 'power' => $power]);
            }

            LeagueStage::insert(
                [
                    ['name' => 'Week 1', 'created_at' => Carbon::now()],
                    ['name' => 'Week 2', 'created_at' => Carbon::now()],
                    ['name' => 'Week 3', 'created_at' => Carbon::now()],
                    ['name' => 'Week 4', 'created_at' => Carbon::now()],
                    ['name' => 'Week 5', 'created_at' => Carbon::now()],
                    ['name' => 'Week 6', 'created_at' => Carbon::now()],
                ]
            );

            $this->initFixtures();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @throws \Exception
     */
    private function initFixtures()
    {
        $teams = Team::all('id')->keyBy('id')->keys()->toArray();
        $stages = LeagueStage::all('id')->keyBy('id')->keys()->toArray();
        $fixtureList = $this->generateRoundRobin($teams);
        $countFixtures = count($fixtureList);
        $countStages = count($stages);

        if ($countFixtures % 2 !== 0 || $countStages % 2 !== 0) {
            throw new \Exception('Wrong count of teams and/or stages');
        }

        $fixtureSize = count($fixtureList) / count($stages);
        if (!is_int($fixtureSize)) {
            throw new \Exception('Wrong fixture size');
        }

        $splitFixtures = array_chunk($fixtureList, $fixtureSize);
        $fixtures = [];

        foreach ($stages as $key => $stage) {
            foreach ($splitFixtures[$key] as $fixture) {
                $fixtureTeams = explode(Fixture::FIXTURE_DELIMITER, $fixture);
                $fixtures[] = [
                    'stage_id' => $stage,
                    'home_team_id' => $fixtureTeams[0],
                    'away_team_id' => $fixtureTeams[1],
                    'created_at' => Carbon::now()
                ];
            }
        }

        Fixture::insert($fixtures);
    }

    /**
     * @param array $teams
     * @return array
     */
    private function generateRoundRobin(array $teams): array
    {
        $firstPart = [];
        $lastPart = [];
        while (count($firstPart) < count($teams) / 2 * (count($teams) - 1)) {
            for ($i = 0; $i < count($teams); $i += 2) {
                $firstPart[] = $teams[$i] . Fixture::FIXTURE_DELIMITER . $teams[$i + 1];
                $lastPart[] = $teams[$i + 1] . Fixture::FIXTURE_DELIMITER . $teams[$i];
            }
            for ($i = count($teams) - 1; $i > 1; $i--) {
                $temp = $teams[$i - 1];
                $teams[$i - 1] = $teams[$i];
                $teams[$i] = $temp;
            }
        }

        return array_merge($firstPart, $lastPart);
    }
}
