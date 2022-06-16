<?php

use App\Models\Fixtures;
use App\Services\FixtureService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('play', function () {
    $sperm = new FixtureService();
//    $stage = \App\Models\LeagueStages::find(1);
    $sperm->playAllStages();
});

Artisan::command('sex', function () {
    //TODO:добавить команду инициализации проекта
    $teams = ['Liverpool', 'Manchester city', 'Chelsea', 'Arsenal', 'Dinamo', 'Shakhtar', 'Shakhtar2', 'Shakhtar3'];
    $firstPart = [];
    $lastPart = [];

    while (count($firstPart) < count($teams) / 2 * (count($teams) - 1)) {
        for ($i = 0; $i < count($teams); $i += 2) {
            $firstPart[] = $teams[$i] . ' - ' . $teams[$i + 1];
            $lastPart[] = $teams[$i + 1] . ' - ' . $teams[$i];
        }
        for ($i = count($teams) - 1; $i > 1; $i--) {
            $temp = $teams[$i - 1];
            $teams[$i - 1] = $teams[$i];
            $teams[$i] = $temp;
        }
    }
    dump(array_chunk(array_merge($firstPart, $lastPart), 2));
    dd(array_merge($firstPart, $lastPart));

});

Artisan::command('huy', function () {
    $teams = [1, 2, 3, 4];
    $stages = [1, 2, 3, 4, 5, 6];
    $firstPart = [];
    $lastPart = [];
    $readyFixtures = [];

    while (count($firstPart) < count($teams) / 2 * (count($teams) - 1)) {
        for ($i = 0; $i < count($teams); $i += 2) {
            $firstPart[] = $teams[$i] . '-' . $teams[$i + 1];
            $lastPart[] = $teams[$i + 1] . '-' . $teams[$i];
        }
        for ($i = count($teams) - 1; $i > 1; $i--) {
            $temp = $teams[$i - 1];
            $teams[$i - 1] = $teams[$i];
            $teams[$i] = $temp;
        }
    }

    $fixtureList = array_merge($firstPart, $lastPart);
    $fixtureSize = count($fixtureList) / count($stages);
    $splitedFixtures = array_chunk(array_merge($firstPart, $lastPart), $fixtureSize);

    foreach ($stages as $key=>$stage) {
        foreach ($splitedFixtures[$key] as $fixture) {
            $fixtureTeams = explode("-", $fixture);
            Fixtures::create([
                'stage_id' => $stage,
                'home_team_id' => $fixtureTeams[0],
                'away_team_id' => $fixtureTeams[1],
            ]);
        }
    }
    dump($splitedFixtures);

});
