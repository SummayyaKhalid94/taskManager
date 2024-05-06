<!DOCTYPE html>
<html lang="en">

@include('layouts.head')

<body>

    <div id="loader" class="loader-container">
        <div class="loader"></div>
    </div>

    @include('layouts.header')


    @include('layouts.sidebar')

    <main id="main" class="main">

        @yield('content')

    </main><!-- End #main -->

    @include('layouts.footer')

    @yield('scripts')

</body>

</html>
