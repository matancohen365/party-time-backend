@extends('layout')

@section('title', title_case(isset($term) ? $term : 'Party Time'))

@section('content')
    @parent
    @isset($tracks)
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="list-group" style="padding: 0; margin: 0">
                @foreach($tracks as $key => $track)

                    <div class="track list-group-item" style="padding: 1px;">

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

                        <div style="clear: both"></div>
                    </div>

                @endforeach
            </div>
        </div>
    @endisset
@endsection