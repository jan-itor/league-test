<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'League simulator') }}</title>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="layout-main">

<div class="container px-4 py-5" id="featured-3">
    <h2 class="pb-2 border-bottom">League simulator</h2>
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
                <tr>
                    <th scope="row">Arsenal</th>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                </tr>
                <tr>
                    <th scope="row">Liverpool</th>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                </tr>
                <tr>
                    <th scope="row">Manchester City</th>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                </tr>
                <tr>
                    <th scope="row">Chelsea</th>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                </tr>
                </tbody>
            </table>
            <a href="#" class="btn btn-primary icon-link d-inline-flex align-items-center">
                Play All
            </a>
        </div>
        <div class="feature col">
            <h2>Match results</h2>
            <table class="table table-success table-striped">
                <h6 class="text-center">
                    Week1 match results
                </h6>
                <tbody class="table-group-divider">
                <tr>
                    <th scope="row">Arsenal</th>
                    <td>3 - 2</td>
                    <td>Liverpool</td>
                </tr>
                <tr>
                    <th scope="row">Liverpool</th>
                    <td>3 - 2</td>
                    <td>Manchester City</td>
                </tr>
                </tbody>
            </table>
            <a href="#" class="btn btn-success icon-link d-inline-flex align-items-center">
                Play Next Week
            </a>
        </div>
        <div class="feature col">
            <h2>League predictions</h2>
            <table class="table table-info table-striped">
                <h6 class="text-center">
                    Week4 predictions
                </h6>
                <tbody class="table-group-divider">
                <tr>
                    <th scope="row">Arsenal</th>
                    <td>45%</td>
                </tr>
                <tr>
                    <th scope="row">Liverpool</th>
                    <td>25%</td>
                </tr>
                <tr>
                    <th scope="row">Manchester City</th>
                    <td>25%</td>
                </tr>
                <tr>
                    <th scope="row">Chelsea</th>
                    <td>5%</td>
                </tr>
                </tbody>
            </table>
            <a href="#" class="btn btn-danger icon-link d-inline-flex align-items-center">
                Reset All
            </a>
        </div>
    </div>
    <div class="btn-group" role="group" aria-label="Basic example">
        <a href="/stages" class="btn btn-info">League plan</a>
    </div>
</div>
</body>
</html>
