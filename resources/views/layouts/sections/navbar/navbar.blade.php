@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
$containerNav = ($configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');
@endphp
@php
    $users=\Auth::user();
    $profile=\App\Models\Utility::get_file('uploads/avatar/');

    $setting = \App\Models\Utility::settings();

    $unseenCounter=App\Models\ChMessage::where('to_id', Auth::user()->id)->where('seen', 0)->count();
@endphp
<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
  @endif
  @if(isset($navbarDetached) && $navbarDetached == '')
  <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="{{$containerNav}}">
      @endif

      <!--  Brand demo (display only for navbar-full and hide on below xl) -->
      @if(isset($navbarFull))
        <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
          <a href="{{url('/')}}" class="app-brand-link">
            <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20])</span>
            <span class="app-brand-text demo menu-text fw-bold">{{config('variables.templateName')}}</span>
          </a>
        </div>
      @endif

      <!-- ! Not required for layout-without-menu -->
      @if(!isset($navbarHideToggle))
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
          <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-md"></i>
          </a>
        </div>
      @endif

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        @if($configData['hasCustomizer'] == true)
          <!-- Style Switcher -->
          <div class="navbar-nav align-items-center">
            <div class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
              <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class='ti ti-md'></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-start dropdown-styles">
                <li>
                  <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                    <span class="align-middle"><i class='ti ti-sun ti-md me-3'></i>Light</span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                    <span class="align-middle"><i class="ti ti-moon-stars ti-md me-3"></i>Dark</span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                    <span class="align-middle"><i class="ti ti-device-desktop-analytics ti-md me-3"></i>System</span>
                  </a>
                </li>
              </ul>
            </div>
          </div>
          <!--/ Style Switcher -->
        @endif

        <ul class="navbar-nav flex-row align-items-center ms-auto">
          @if(\Auth::user()->type == 'company' )
            @impersonating($guard = null)
            <li class="dropdown dash-h-item drp-company">
                <a class="btn btn-danger btn-sm me-3" href="{{ route('exit.company') }}"><i class="ti ti-ban"></i>
                    {{ __('Exit Company Login') }}
                </a>
            </li>
            @endImpersonating
            @endif

            @if( \Auth::user()->type !='client' && \Auth::user()->type !='super admin' )
                <li class="dropdown dash-h-item drp-notification">
                    <a class="dash-head-link arrow-none me-0" href="{{ url('chats') }}" aria-haspopup="false"
                        aria-expanded="false">
                        <i class="ti ti-brand-hipchat"></i>
                        <span class="bg-danger dash-h-badge message-toggle-msg  message-counter custom_messanger_counter beep"> {{ $unseenCounter }}<span
                                class="sr-only"></span>
                        </span>
                    </a>
                </li>
            @endif
          <!-- User -->
          <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
              <div class="avatar avatar-online">
                <img src="https://robohash.org/{{ \Auth::user()->id }}?set=set3&size=100x100&bgset=bg1" alt class="rounded-circle">
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item mt-0" href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                  <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-2">
                      <div class="avatar avatar-online">
                        <img src="https://robohash.org/{{ \Auth::user()->id }}?set=set3&size=100x100&bgset=bg1" alt class="rounded-circle">
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-0">
                      {{__('Hi, ')}}{{\Auth::user()->name }}!
                      </h6>
                      <small class="text-muted">Admin</small>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <div class="dropdown-divider my-1 mx-n2"></div>
              </li>
              <li>
                <a class="dropdown-item" href="{{route('profile')}}">
                  <i class="ti ti-user me-3 ti-md"></i><span class="align-middle">My Profile</span>
                </a>
              </li>
                <li>
                  <div class="d-grid px-2 pt-2 pb-1">
                    <a class="btn btn-sm btn-danger d-flex" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();">
                      <small class="align-middle">Logout</small>
                      <i class="ti ti-logout ms-2 ti-14px"></i>
                    </a>
                  </div>
                </li>
                <form id="frm-logout" action="{{ route('logout') }}" method="POST" class="d-none">
                    {{ csrf_field() }}
                </form>
            </ul>
          </li>
          <!--/ User -->
        </ul>
      </div>

      @if(!isset($navbarDetached))
    </div>
    @endif
  </nav>