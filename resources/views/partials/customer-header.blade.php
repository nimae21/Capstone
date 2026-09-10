    <nav class="navbar" id="customerHeader" aria-label="Store navigation">
        <a href="{{ route('home') }}" class="logo">
            <img src="{{ asset('images/achilles logo.png') }}" 
                 alt="Achilles Electronics and Computer Shop"
                 class="site-logo">
        </a>
        <div class="nav-links">
            <a href="{{ route('new') }}" class="nav-link {{ request()->routeIs('new') ? 'active' : '' }}">NEW</a>
            <a href="{{ route('men') }}" class="nav-link {{ request()->routeIs('men') ? 'active' : '' }}">MEN</a>
            <a href="{{ route('women') }}" class="nav-link {{ request()->routeIs('women') ? 'active' : '' }}">WOMEN</a>
            <a href="{{ route('kids') }}" class="nav-link {{ request()->routeIs('kids') ? 'active' : '' }}">KIDS</a>
        </div>
        <div class="nav-icons">
            @auth
                <div class="user-menu" id="userMenu">
                    <button type="button" class="user-menu-trigger" id="userMenuTrigger" aria-label="Account menu" aria-expanded="false" aria-controls="userDropdown">
                    <i class="fas fa-user-circle"></i>
                    <span>{{ Auth::user()->first_name }}</span>
                    <i class="fas fa-chevron-down" style="font-size:0.7rem; margin-left:4px;"></i>
                    </button>
                    <div class="user-dropdown" id="userDropdown" hidden>
                        <a href="{{ route('profile.index') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}" @if(request()->routeIs('profile.*')) aria-current="page" @endif>
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="{{ route('addresses.index') }}" class="{{ request()->routeIs('addresses.*') ? 'active' : '' }}" @if(request()->routeIs('addresses.*')) aria-current="page" @endif>
                            <i class="fas fa-location-dot"></i> My Addresses
                        </a>
                        <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'active' : '' }}" @if(request()->routeIs('orders.*')) aria-current="page" @endif>
                            <i class="fas fa-box"></i> My Orders
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                            @csrf
                            <button type="submit">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="auth-btn">Login</a>
                <a href="{{ route('register') }}" class="auth-btn">Register</a>
            @endauth

            <form class="product-search" id="productSearch" action="{{ route('search') }}" method="GET" role="search" data-suggestions-url="{{ route('search.suggestions') }}">
                <div class="product-search-field">
                    <input type="search" name="q" id="productSearchInput" placeholder="Search shoes..." aria-label="Search shoes" value="{{ request()->routeIs('search') ? request('q') : '' }}" maxlength="100" autocomplete="off" aria-controls="productSearchResults" aria-expanded="false" required>
                    <button type="submit" aria-label="Search"><i class="fas fa-search" aria-hidden="true"></i></button>
                </div>
                <div id="productSearchResults" class="product-search-results" hidden>
                    <p id="productSearchStatus" role="status" aria-live="polite"></p>
                    <ul id="productSearchList" aria-label="Matching shoes"></ul>
                </div>
            </form>

            <!-- Unified Cart Button -->
            <a href="{{ route('cart.index') }}" class="cart-btn" aria-label="Shopping cart">
                <i class="fas fa-shopping-bag"></i>
                <span>Cart</span>
                <span class="cart-count" id="cartCount">{{ $cartCount ?? 0 }}</span>
            </a>
        </div>
    </nav>
