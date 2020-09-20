<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="/css/app.css" rel="stylesheet">
    <link href="/css/admin.css" rel="stylesheet">

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

  <script
    src="//code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
    crossorigin="anonymous"></script>

  <script
    src="//code.jquery.com/ui/1.12.1/jquery-ui.min.js"
    integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
    crossorigin="anonymous"></script>

    <script src="/js/admin.js"></script>


</head>
<body>
    <div id="app">
      <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
          <div class="navbar-header">

            <!-- Branding Image -->
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name') }}
            </a>

            <a class="navbar headerLink {{ Request::path() == 'cats' ? 'headerLinkActive' : '' }}"
               href="/cats">Categories</a>

            <a class="navbar headerLink {{ Request::path() == 'items' ? 'headerLinkActive' : '' }}"
               href="/items">Main Accounts</a>

            <a class="navbar headerLink {{ Request::path() == 'socialmediaaccounts/admin' ? 'headerLinkActive' : '' }}"
               href="/socialmediaaccounts/admin">Social Media Accounts</a>

            <a class="navbar headerLink {{ Request::path() == 'socialmedia' ? 'headerLinkActive' : ''}}"
               href="/socialmedia">Social Media</a>

            <a class="navbar headerLink {{ Request::path() == 'links' ? 'headerLinkActive' : '' }}"
               href="/links">Links</a>

            @if (\App\Site::inst('USES_WIKIPEDIA_SEARCH'))
              <a class="navbar headerLink {{ Request::path() == 'wikipedia' ? 'headerLinkActive' : '' }}"
                href="/wikipedia">Wikipedia</a>
            @endif


            @if (\App\Site::inst('USES_CONTACT_INFO'))
              <a class="navbar headerLink {{ Request::path() == 'contactinfo/all' ? 'headerLinkActive' : '' }}"
                href='/contactinfo/all'>Contact Info</a>
            @endif

            @if (\App\Site::inst('USES_HOURS'))
              <a class="navbar headerLink {{ Request::path() == 'hours/all' ? 'headerLinkActive' : '' }}"
                 href='/hours/all'>Hours</a>
            @endif

          </div>

          <div>

              <!-- Right Side Of Navbar -->

                  <!-- Authentication Links -->
                  @guest
                      <ul class="nav navbar-nav navbar-right">
                      <li>{{--  a href="/login">Login</a --}}</li>
{{--                            <li><a href="/register">Register</a></li>--}}
                      </ul>
                  @else
                    <div class="userlink">
                    {{ Auth::user()->name }}

                    <a href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="/logout" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>
                    </div>

                  @endguest

          </div>
        </div>
      </nav>

        <div class="container">
            @include('partials.form-status')
        </div>
        @yield('content')

    </div>

    <div id="delete-confirm" style='display:none;' title="Confirm delete">
      <p>Really delete?</p>
    </div>

    <div id="confirm-click" style='display:none;' title="Confirm">
      <p>Proceed?</p>
    </div>

    <div id="alert-problem" style='display:none;' title="Error">
      <p id="alert-problem-text"></p>
    </div>

    <div id="alert-confirm" style='display:none;' title="Confirm">
      <p id="alert-confirm-text"></p>
    </div>

    <div id="alert-fyi" style='display:none;' title="Heads up">
      <p id="alert-fyi-text"></p>
    </div>

    <!-- Scripts -->
{{--    <script src="{{ asset('js/app.js') }}"></script>--}}

</body>
</html>
