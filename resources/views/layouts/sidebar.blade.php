<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link {{ Route::currentRouteName() == 'home' ? 'active' : '' }}" href="{{ route('home') }}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->

        <li class="nav-heading">Pages</li>

        <li class="nav-item">
            <a class="nav-link {{ Route::currentRouteName() == 'task.index' ? 'active' : '' }}" href="{{ route('task.index') }}">
                <i class="bi bi-person"></i>
                <span>Tasks</span>
            </a>
        </li><!-- End Profile Page Nav -->
    </ul>
</aside><!-- End Sidebar-->
