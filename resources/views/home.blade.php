<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'League simulator') }}</title>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="layout-main">

<div class="container px-4 py-5" id="featured-3">
    <h2 class="pb-2 border-bottom">League simulator</h2>
    {!! $errors->first('error', '<div class="alert alert-danger" role="alert">:message</div>') !!}
    <div class="row g-4 py-5 row-cols-1 row-cols-lg-3">
        <div class="feature col">
            <h2>League table</h2>
            <table class="table table-primary table-striped">
                <thead>
                <tr>
                    <th scope="col">Teams</th>
                    <th scope="col">PTS</th>
                    <th scope="col">P</th>
                    <th scope="col">W</th>
                    <th scope="col">D</th>
                    <th scope="col">L</th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($teamsStats as $team)
                    <tr>
                        <th scope="row">{{ $team->team->name }}</th>
                        <td>{{ $team->points }}</td>
                        <td>{{ $team->played }}</td>
                        <td>{{ $team->won }}</td>
                        <td>{{ $team->draw }}</td>
                        <td>{{ $team->loss }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <a href="{{route('league.play-all')}}" class="btn btn-primary icon-link d-inline-flex align-items-center">
                Play All
            </a>
        </div>
        <div class="feature col">
            <h2>Match results</h2>
            <table class="table table-success table-striped">
                @if(!$currentResults)
                    <span class="badge badge-pill btn-warning">NO GAMES YET</span>
                @else
                    <h6 class="text-center">
                        {{$currentResults->name}} match results
                    </h6>
                    <tbody class="table-group-divider">
                    @foreach($currentResults->fixtures as $fixture)
                        <tr>
                            <th>{{$fixture->homeTeam->name}}</th>
                            <td>{{$fixture->home_team_score}} - {{$fixture->away_team_score}}</td>
                            <td>{{$fixture->awayTeam->name}}</td>
                        </tr>
                    @endforeach
                    @endif
                    </tbody>
            </table>
            <a href="{{route('league.play-next')}}" class="btn btn-success icon-link d-inline-flex align-items-center">
                Play Next Week
            </a>
        </div>
        <div class="feature col">
            <h2>League predictions</h2>
            <table class="table table-info table-striped">
                @if(!$currentResults)
                    <span class="badge badge-pill btn-warning">NO GAMES YET</span>
                @else
                    <h6 class="text-center">
                        {{$currentResults->name}} predictions
                    </h6>
                    <tbody class="table-group-divider">
                    @foreach($teamsStats as $team)
                        <tr>
                            <th scope="row">{{$team->team->name}}</th>
                            <td>{{$team->prediction}} %</td>
                        </tr>
                    @endforeach
                @endif
            </table>
            <a href="{{route('league.reset')}}" class="btn btn-danger icon-link d-inline-flex align-items-center">
                Reset All
            </a>
        </div>
    </div>
    <div class="btn-group" role="group">
        <a href="{{route('league.stages')}}" class="btn btn-info">League plan</a>
    </div>
</div>
</body>
</html>
