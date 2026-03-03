@php
    $currentURL = Request::segment(1);
    $activeClass = 'active';
@endphp
<style>
    .rounded-circle{
        width: 50px;
        height: 50px;
    }
    .profile-element{
        align-items: center
    }
    .nav-link-container{
        display: flex; align-items: center; gap: 5px;
    }
    .roboto-custom {
        font-family: "Roboto", sans-serif;
        font-optical-sizing: auto;
        font-weight: 400;
        font-style: normal;
        font-size: 15px;
    }
</style>
<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <img alt="image" class="rounded-circle" src="{{Avatar::create(Auth::user()->name)->toBase64();}}" />
                    <div style="display: flex;justify-content: center;">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#" style="width: 100%;">
                            <span class="block m-t-xs font-bold" style="display: inline !important;">{{ Auth::user()->name }}</span> <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li>
                                <a class="dropdown-item" href="#">
                                    <div class="nav-link-container">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#000000"><path d="M240.92-268.31q51-37.84 111.12-59.77Q412.15-350 480-350t127.96 21.92q60.12 21.93 111.12 59.77 37.3-41 59.11-94.92Q800-417.15 800-480q0-133-93.5-226.5T480-800q-133 0-226.5 93.5T160-480q0 62.85 21.81 116.77 21.81 53.92 59.11 94.92Zm146.7-219.31Q350-525.23 350-580q0-54.77 37.62-92.38Q425.23-710 480-710q54.77 0 92.38 37.62Q610-634.77 610-580q0 54.77-37.62 92.38Q534.77-450 480-450q-54.77 0-92.38-37.62ZM480-100q-79.15 0-148.5-29.77t-120.65-81.08q-51.31-51.3-81.08-120.65Q100-400.85 100-480t29.77-148.5q29.77-69.35 81.08-120.65 51.3-51.31 120.65-81.08Q400.85-860 480-860t148.5 29.77q69.35 29.77 120.65 81.08 51.31 51.3 81.08 120.65Q860-559.15 860-480t-29.77 148.5q-29.77 69.35-81.08 120.65-51.3 51.31-120.65 81.08Q559.15-100 480-100Zm104.42-77.42q50.27-17.43 89.27-48.73-39-30.16-88.11-47Q536.46-290 480-290t-105.77 16.65q-49.31 16.66-87.92 47.2 39 31.3 89.27 48.73Q425.85-160 480-160t104.42-17.42Zm-54.5-352.66Q550-550.15 550-580t-20.08-49.92Q509.85-650 480-650t-49.92 20.08Q410-609.85 410-580t20.08 49.92Q450.15-510 480-510t49.92-20.08ZM480-580Zm0 355Z"/></svg>
                                        <span class="roboto-custom">Profile</span>
                                    </div>
                                </a>
                            </li>
                            <li class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" ref="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <div class="nav-link-container">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#000000"><path d="M212.31-140Q182-140 161-161q-21-21-21-51.31v-535.38Q140-778 161-799q21-21 51.31-21h268.07v60H212.31q-4.62 0-8.46 3.85-3.85 3.84-3.85 8.46v535.38q0 4.62 3.85 8.46 3.84 3.85 8.46 3.85h268.07v60H212.31Zm436.92-169.23-41.54-43.39L705.08-450H363.85v-60h341.23l-97.39-97.38 41.54-43.39L820-480 649.23-309.23Z"/></svg>
                                        <span class="roboto-custom">Log out</span>
                                    </div>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="logo-element">
                    <img alt="image" class="rounded-circle" src="{{Avatar::create(Auth::user()->name)->toBase64();}}" />
                </div>
            </li>
            <li class="{{ $currentURL == 'home' ? $activeClass : '' }}">
                <a href="{{route('dashboard')}}" title="Dashboard">
                    <div class="nav-link-container">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF"><path d="M530-636.34v-147.31q0-15.66 10.43-26Q550.86-820 566.27-820h217.62q15.42 0 25.76 10.35 10.35 10.34 10.35 26v147.31q0 15.65-10.43 25.99Q799.14-600 783.73-600H566.11q-15.42 0-25.76-10.35Q530-620.69 530-636.34ZM140-496v-288.01q0-15.3 10.43-25.64Q160.86-820 176.27-820h217.62q15.42 0 25.76 10.35Q430-799.3 430-784v288.01q0 15.3-10.43 25.64Q409.14-460 393.73-460H176.11q-15.42 0-25.76-10.35Q140-480.7 140-496Zm390 320v-288.01q0-15.3 10.43-25.64Q550.86-500 566.27-500h217.62q15.42 0 25.76 10.35Q820-479.3 820-464v288.01q0 15.3-10.43 25.64Q799.14-140 783.73-140H566.11q-15.42 0-25.76-10.35Q530-160.7 530-176Zm-390-.35v-147.31q0-15.65 10.43-25.99Q160.86-360 176.27-360h217.62q15.42 0 25.76 10.35Q430-339.31 430-323.66v147.31q0 15.66-10.43 26Q409.14-140 393.73-140H176.11q-15.42 0-25.76-10.35-10.35-10.34-10.35-26ZM200-520h170v-240H200v240Zm390 320h170v-240H590v240Zm0-460h170v-100H590v100ZM200-200h170v-100H200v100Zm170-320Zm220-140Zm0 220ZM370-300Z"/></svg>
                        <span class="nav-label roboto-custom" style="">Dashboard</span>
                    </div>
                </a>
            </li>
            <li class="{{$currentURL == 'product' ? $activeClass : ''}}">
                <a href="{{route('product')}}" title="Product">
                    <div class="nav-link-container">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF"><path d="M200-643.85v431.54q0 5.39 3.46 8.85t8.85 3.46h535.38q5.39 0 8.85-3.46t3.46-8.85v-431.54H620v295.77l-140-70-140 70v-295.77H200ZM212.31-140q-29.92 0-51.12-21.19Q140-182.39 140-212.31v-467.46q0-12.84 4.12-24.5 4.11-11.65 12.34-21.5l56.16-67.92q9.84-12.85 24.61-19.58Q252-820 268.46-820h422.31q16.46 0 31.42 6.73T747-793.69L803.54-725q8.23 9.85 12.34 21.69 4.12 11.85 4.12 24.7v466.3q0 29.92-21.19 51.12Q777.61-140 747.69-140H212.31Zm3.31-563.84H744l-43.62-51.93q-1.92-1.92-4.42-3.08-2.5-1.15-5.19-1.15H268.85q-2.69 0-5.2 1.15-2.5 1.16-4.42 3.08l-43.61 51.93ZM400-643.85v198.08l80-40 80 40v-198.08H400Zm-200 0h560-560Z"/></svg>
                        <span class="nav-label roboto-custom">Product</span>
                    </div>
                </a>
            </li>
            <li class="{{$currentURL == 'subscription' ? $activeClass : ''}}">
                <a href="{{route('subscription')}}" title="Subscription">
                    <div class="nav-link-container">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#ffffff"><path d="M172.31-100Q142-100 121-121q-21-21-21-51.31v-375.38Q100-578 121-599q21-21 51.31-21h615.38Q818-620 839-599q21 21 21 51.31v375.38Q860-142 839-121q-21 21-51.31 21H172.31Zm0-60h615.38q4.62 0 8.46-3.85 3.85-3.84 3.85-8.46v-375.38q0-4.62-3.85-8.46-3.84-3.85-8.46-3.85H172.31q-4.62 0-8.46 3.85-3.85 3.84-3.85 8.46v375.38q0 4.62 3.85 8.46 3.84 3.85 8.46 3.85ZM410-218.46 622.31-360 410-501.54v283.08ZM170-675.38v-60h620v60H170Zm120-115.39v-60h380v60H290ZM160-160v-400 400Z"/></svg>
                        <span class="nav-label roboto-custom">Subscription</span>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</nav>
