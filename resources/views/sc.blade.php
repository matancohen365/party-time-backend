@extends('layout')

@section('title', 'Human?')

@section('content')
    @parent
    <style>
        .g-recaptcha div {
            margin: 15px auto;
        }
        .well {
            text-align: center;
        }
    </style>
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 well well-sm small" style="padding: 4px">
        <h2>Human?</h2>
        <form method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="next" value="{{ request('next', '/') }}">
            {!! app('captcha')->display(); !!}
            <button class="btn btn-primary btn-block" type="submit">Yeah!</button>
        </form>
    </div>
@endsection