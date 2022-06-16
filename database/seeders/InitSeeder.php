<?php

namespace Database\Seeders;

use App\Models\Fixtures;
use App\Models\LeagueStages;
use App\Models\Stats;
use App\Models\Teams;
use Illuminate\Database\Seeder;
use App\Services\FixtureService;

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
                $newTeam = Teams::create(['name' => $name]);
                Stats::create(['team_id' => $newTeam->id, 'power' => $power]);
            }

            LeagueStages::insert(
                [
                    ['name' => 'Week 1'],
                    ['name' => 'Week 2'],
                    ['name' => 'Week 3'],
                    ['name' => 'Week 4'],
                    ['name' => 'Week 5'],
                    ['name' => 'Week 6'],
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
        $teams = Teams::all('id')->keyBy('id')->keys()->toArray();
        $stages = LeagueStages::all('id')->keyBy('id')->keys()->toArray();
        $fixtureList = FixtureService::generateRoundRobin($teams);
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
                $fixtureTeams = explode(Fixtures::FIXTURE_DELIMITER, $fixture);
                $fixtures[] = [
                    'stage_id' => $stage,
                    'home_team_id' => $fixtureTeams[0],
                    'away_team_id' => $fixtureTeams[1],
                ];
            }
        }

        Fixtures::insert($fixtures);
    }
}
