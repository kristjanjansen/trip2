<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <meta id="globalprops" name="globalprops" content="
            {{
                rawurlencode(json_encode([
                    'token' => csrf_token(),
                    'alertRoute' => route('utils.alert'),
                    'allowedTags' => config('site.allowedtags'),
                    'maxfilesize' => config('site.maxfilesize'),
                    'promo' => config('promo')
                ])) 
            }}
        ">
        <link rel="stylesheet" href="/v2/css/main.css">
    </head>
    <body>

        @include('v2.include.svg')
        
        @yield('header')
        @yield('content')
        @yield('footer')
        
        <script src="/v2/js/main.js"></script>
        
    </body>
</html>
