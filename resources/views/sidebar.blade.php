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
</style>
<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <img alt="image" class="rounded-circle" src="{{Avatar::create(Auth::user()->name)->toBase64();}}" />
                    <div style="display: flex;">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#" style="width: 100%;">
                            <span class="block m-t-xs font-bold" style="display: inline !important;">{{ Auth::user()->name }}</span> <b class="caret"></b>
                            {{-- <span class="text-muted text-xs block"> </span> --}}
                        </a>
                        <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li><a class="dropdown-item" href="profile.html">Profile</a></li>
                            <li class="dropdown-divider"></li>
                            <li>
                                {{-- <a href="login.html">Logout</a> --}}
                                <a class="dropdown-item" ref="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fa fa-sign-out"></i> Log out
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
                <a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboard</span></a>
            </li>
            <li class="{{$currentURL == 'product' ? $activeClass : ''}}">
                <a href="{{route('product')}}"><i class="fa fa-cubes"></i> <span class="nav-label">Product</span></a>
            </li>
        </ul>
    </div>
</nav>
