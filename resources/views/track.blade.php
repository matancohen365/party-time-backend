@extends('layout')

@section('title', "{$track->artist} - {$track->title}")

@section('styles')
    @parent

    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:url" content="{{ route('track', $track->hash) }}">
    <meta property="og:title" content="{{ $track->artist }} - {{ $track->title }}">
    <meta property="og:image"
          content="{{ 'https://images.weserv.nl/?url=' . urlencode(preg_replace('/^((https?:)?\/\/)(.+)/i', '\\3', $track->cover)) }}">
    <meta property="og:description"
          content="{{ $track->title }} By {{ $track->artist }} from the album {{ $track->album }}">

@endsection

@section('content')
    @parent
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 well well-sm">
        <div class="single-track">

            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item"
                        src="https://www.youtube-nocookie.com/embed/{{ $track->video->getKey() }}?rel=0&amp;showinfo=0&amp;autoplay=1"
                        allowfullscreen></iframe>
            </div>


            <div class="single-track-interaction btn-group btn-group-justified" role="group">
                <a class="btn btn-default track-interaction-download download" role="button"
                   href="{{ route('download', $track->hash) }}">
                    <i class="fa fa-download fa-lg" aria-hidden="true"></i>
                </a>
                <a class="btn btn-default track-interaction-share skip-loading" role="button"
                   href="whatsapp://send/?text={{ urlencode(route('track', $track->hash)) }}">
                    <i class="fa fa-whatsapp fa-lg" aria-hidden="true"></i>
                </a>
            </div>


            <div class="single-track-image" style="float:left;padding:3px;">
                <img src="{{ 'https://images.weserv.nl/?url=' . urlencode(preg_replace('/^((https?:)?\/\/)(.+)/i', '\\3', $track->cover)) }}"
                     alt="{{ "{$track->artist} - {$track->title}" }}" style="height:100px;width:100px;">
            </div>

            <div class="single-track-title">
                <h3>{{ $track->title }}</h3>
                <h4>
                    <a href="{{ route('search', ['term' => "{$track->artist}"]) }}">{{ $track->artist  }}</a>
                </h4>
                <h5>
                    <a href="{{ route('search', ['term' => "{$track->artist} - {$track->album}"]) }}">{{ $track->album  }}</a>
                    <small>({{ $track->number }})</small>
                </h5>
                <h5>{{ $track->genre }}, {{ (new DateTime($track->release->date))->format('Y') }}</h5>
            </div>

            @if( ! empty($track->lyrics))
                <div class="single-track-lyrics small">
                    @php
                        echo implode(PHP_EOL, array_map(function($span) {
                            return sprintf('<span>%s</span>', e($span));
                        }, explode(PHP_EOL, $track->lyrics)));
                    @endphp
                </div>
            @endif

        </div>

        <div style="clear: both;"></div>
        @isset($tracks)
                <div class="list-group" style="padding:0;margin:0;">
                    @foreach($tracks as $key => $track)

                        <div class="track list-group-item" style="padding:1px;">

                            <div class="track-image">
                                <a href="{{ isset($track->hash) ? route('track', $track->hash) : route('search', ['term' => (isset($track->term) ? $track->term : "{$track->artist} - {$track->title}")]) }}">
                                    <img src="{{ 'https://images.weserv.nl/?url=' . urlencode(preg_replace('/^((https?:)?\/\/)(.+)/i', '\\3', $track->cover)) }}"
                                         alt="{{ "{$track->artist} - {$track->title}" }}">
                                </a>
                            </div>

                            <div class="track-details">
                                <div class="track-details-title">
                                    <h2>
                                        <a href="{{ isset($track->hash) ? route('track', $track->hash) : route('search', ['term' => (isset($track->term) ? $track->term : "{$track->artist} - {$track->title}")]) }}">
                                            {{ trim("{$track->title}", " \t\n\r\0\x0B-") }}
                                        </a>
                                    </h2>
                                </div>
                                <div class="track-details-artist">
                                    <h3>
                                        <a href="{{ route('search', ['term' => "{$track->artist}"]) }}">{{ $track->artist  }}</a>
                                    </h3>
                                </div>
                                <div class="track-details-details">
                                    <h4>
                                        @if(isset($track->genre) && isset($track->release))
                                            <a href="{{ route('search', ['term' => "{$track->artist} - {$track->album}"]) }}">{{ $track->album  }}</a>
                                            <small>({{ $track->number }})</small> | {{ $track->genre }}
                                            | {{ (new DateTime($track->release->date))->format('Y') }}
                                        @else
                                            #{{ isset($_position) ? ++$_position : $_position = 1 }}
                                        @endif
                                    </h4>
                                </div>
                            </div>

                            <div style="clear:both;"></div>
                        </div>

                    @endforeach
                </div>
        @endisset

    </div>

@endsection