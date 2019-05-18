@extends('layout')

@section('title', 'Houston, we have a problem!')

@section('content')
    @parent
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 well well-sm small" style="padding: 4px">
        <h4> {{ $error or 'Houston, we have a problem!' }}</h4>
        <ol>
            @if( ! empty(Request::get('term')))
                <li><a href="https://www.google.com/search?q={{ urlencode(Request::get('term')) }}">Google it!</a></li>
            @endif
            <li>Try another search term</li>
            <li>Try again in a few days (if that's a new song)</li>
            <li>Search on YouTube & paste the url in the search box</li>
        </ol>
    </div>
@endsection