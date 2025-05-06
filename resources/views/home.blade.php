
<!DOCTYPE html>
<html lang="en">
@include('head')

@yield('css')
<body>
    <div class="wrapper" style="display: flex;">
        @include('sidebar')
        <div id="page-wrapper" class="gray-bg dashbard-1">
            @include('header')
            <div class="wrapper wrapper-content">
                @yield('content')
                @include('footer')
            </div>
        </div>
    </div>
    @yield('script')
</body>
</html>

