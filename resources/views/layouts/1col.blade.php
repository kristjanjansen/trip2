@php

$title = $title ?? '';
$head_title = $head_title ?? '';
$head_description = $head_description ?? '';
$head_image = $head_image ?? '';
$color = $color ?? '';
$background = $background ?? '';
$header = $header ?? '';
$top = isset($top) ? collect($top) : collect();
$content = isset($content) ? collect($content) : collect();
$bottom = isset($bottom) ? collect($bottom) : collect();
$footer = $footer ?? '';
$background_color = $background_color ?? 'background-white';
$container_background_color = $container_background_color ?? '';
$column_class = $column_class ?? 'col-8';

@endphp

@extends('v2.layouts.main')

@section('title', $title)
@section('head_title', $head_title)
@section('head_description', $head_description)
@section('head_image')
    {!! $head_image !!}
@endsection

@section('color', $color)

@section('background')
    {!! $background !!}
@endsection

@section('header')

<header class="position-relative">
    
    {!! $header !!}

    @if ($top->count())

        <div class="background-gray">

        @foreach ($top as $top_item)

            {!! $top_item !!}
                
        @endforeach

        </div>

    @endif

</header>

@endsection

@section('content')

    <section class="position-relative">

    @if ($content->count())

    <div class="{{ $background_color }}">

        <div class="container {{ $container_background_color }}">

            <div class="row row-center padding-top-lg padding-bottom-md">

                <div class="{{ $column_class }}">

                    @foreach ($content as $content_item)
                    
                    <div @if (!$loop->last) class="margin-bottom-md" @endif>

                        {!! $content_item !!}
                            
                    </div>

                    @endforeach

                </div>

            </div>

        </div>

        @if ($bottom->count())

        <div class="padding-top-lg padding-bottom-lg background-gray">

            <div class="container">

            @foreach ($bottom as $bottom_item)
            
                <div @if (!$loop->last) class="margin-bottom-md" @endif>

                    {!! $bottom_item !!}
                        
                </div>
                    
            @endforeach

            </div>

        </div>

        @endif
        
    </div>

    @endif

    </section>

@endsection

@section('footer')

    <footer class="background-white">

    {!! $footer !!}

    </footer>

@endsection


