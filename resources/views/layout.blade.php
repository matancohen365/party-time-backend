<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
    <title>@yield('title') - {{ config('app.name') }}</title>

    <meta name="robots" content="noindex,nofollow">

    <meta name="mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

    <link rel="manifest" href="/manifest.json">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/launcher-icon.png" sizes="36x36 48x48 72x72 96x96 144x144 192x192">

    <!-- Styles -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"
          integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
    @yield('styles')
    <style>.single-track-lyrics span {
            display: inline-block !important;
            width: 100%;
        }</style>
</head>
<body>
<div class="loading">Loading&#8230;</div>
<div class="container">
    <div class="row header">
        <div class="header-btn-group">
            <form action="{{ route('search') }}" class="form-search" id="form-search">
                <div class="input-group">
                    <input type="text" class="form-control typeahead" data-provide="typeahead"
                           placeholder="Search for..." name="term" id="term" autocomplete="off">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit">Search</button>
                    </span>
                </div>
            </form>
            <div class="btn-group btn-group-justified btn-charts" role="group">

                <?php $charts = Tuna\Charts\ChartResolver::make(); ?>

                @foreach($charts->getProviders() as $provider)

                    <a href="/?chart={{ $provider->getName() }}"
                       class="btn btn-default btn-sm{{ (Request::get('chart') === $provider->getName()) ? ' active' : '' }}"
                       role="button">{{ $provider->getName() }}</a>

                @endforeach

            </div>
        </div>
    </div>
    <div class="row content">
        @yield('content')
    </div>
    <div class="row footer" style="margin-top: 35px;">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="btn-group btn-group-justified btn-actions" role="group">
                <a href="/" class="btn btn-default btn-back" role="button">
                    <span class="glyphicon glyphicon-menu-left" aria-hidden="true"></span>
                </a>
                @if(stripos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android') !== false)
                    <a href="http://bit.ly/2Go9y2y" class="btn btn-primary skip-loading" role="button">
                        <i class="fa fa-android fa-lg"></i>
                    </a>
                @else
                    <a href="/" class="btn btn-default" role="button">
                        <span class="glyphicon glyphicon-fire" aria-hidden="true"></span>
                    </a>
                @endif
                <a href="/" class="btn btn-default btn-search skip-loading" role="button">
                    <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- Scripts -->
<script
        src="https://code.jquery.com/jquery-3.2.1.min.js"
        integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
<script src="/js/bootstrap3-typeahead.min.js"></script>
<script src="{{ mix('/js/app.js') }}"></script>
@yield('scripts')
</body>
</html>