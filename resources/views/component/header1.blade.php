<div
    class="component-header-1"
    style="background-image: url({{
        isset($__env->getSections()['header1.image'])
        ? $__env->getSections()['header1.image']
        : $random_image
    }});"
>
    <div class="overlay">

        <div class="navbar container">

            @include('component.navbar')

        </div>    

        <div class="content container">

            <div class="row">

                <div class="col-sm-8 col-sm-push-4 text-center">
                    
                    @yield('header.top')
                    
                    <h2>@yield('title')</h2>
                    
                </div>

                <div class="col-sm-4 col-sm-pull-8">
                    
                    @yield('header.left')
                
                </div>

                <div class="col-sm-4 text-right">
                    
                    @yield('header.right')
                
                </div>

            </div>
    
        </div>

    </div>

</div>