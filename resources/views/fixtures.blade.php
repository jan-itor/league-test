<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Generated fixtures') }}</title>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="layout-main">

<div class="container px-4 py-5" id="featured-3">
    <h2 class="pb-2 border-bottom">Generated fixtures</h2>
    <div class="row g-4 py-5 row-cols-1 row-cols-lg-3">
        @foreach($groupFixtures as $group)
            <div class="feature col">
                <h2>{{ $group->first()->stage->name}}</h2>
                <table class="table table-primary table-striped">
                    <tbody class="table-group-divider">
                    @foreach($group as $fixture)
                        <tr>
                            <td>{{$fixture->homeTeam->name}} - {{$fixture->awayTeam->name}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
    <div class="btn-group" role="group">
        <a href="{{route('league.home')}}" class="btn btn-info">Main page</a>
    </div>
</div>
</body>
</html>
