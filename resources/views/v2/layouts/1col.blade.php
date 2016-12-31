@php

$title = $title ?? '';
$head_description = $head_description ?? '';
$head_image = $head_image ?? '';
$header = $header ?? '';
$content = $content ?? collect();
$bottom = $bottom ?? collect();
$footer = $footer ?? '';

@endphp

@extends('v2.layouts.main')

@section('title', $title)
@section('head_description', $head_description)
@section('head_image', $head_image)

@section('header', $header)

@section('content')

    <div class="container">

        <div class="row row-center padding-top-md padding-bottom-md">

            <div class="col-9">

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
    
@endsection

@section('footer', $footer)


