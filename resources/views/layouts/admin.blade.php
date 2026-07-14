<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Admin Dashboard') | Achilles</title>

    <!-- Google Fonts + Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%);
            color: #1e293b;
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* ========== SIDEBAR (GLASS + DARK + COLLAPSIBLE) ========== */
        .sidebar {
            width: 280px;
            background: rgba(10, 10, 15, 0.96);
            backdrop-filter: blur(14px);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            padding: 1.5rem 1rem;
            transition: width 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            z-index: 1000;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.1);
        }

        /* Collapsed state (icons only) */
        .sidebar.collapsed {
            width: 85px;
        }

        /* Custom scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #e60023;
            border-radius: 10px;
        }

        /* Logo + toggle wrapper */
        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }
        .logo {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff, #e60023);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            white-space: nowrap;
            transition: opacity 0.2s;
        }
        .sidebar.collapsed .logo {
            opacity: 0;
            visibility: hidden;
            width: 0;
        }
        .toggle-btn {
            background: rgba(255,255,255,0.05);
            border: none;
            color: #cbd5e1;
            font-size: 1.2rem;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .toggle-btn:hover {
            background: rgba(230,0,35,0.2);
            color: white;
        }
        .sidebar.collapsed .toggle-btn {
            margin-left: auto;
            margin-right: auto;
        }

        /* Section headings */
        .sidebar h3 {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #94a3b8;
            margin: 1.5rem 0 0.8rem 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.2s;
        }
        .sidebar.collapsed h3 {
            opacity: 0;
            visibility: hidden;
            height: 0;
            margin: 0;
        }

        /* Divider line between sections */
        .sidebar-divider {
            height: 1px;
            background: linear-gradient(90deg, rgba(255,255,255,0.05), rgba(255,255,255,0.2), rgba(255,255,255,0.05));
            margin: 0.8rem 0.5rem;
        }
        .sidebar.collapsed .sidebar-divider {
            margin: 0.8rem 0;
        }

        /* Navigation links */
        .sidebar a,
        .sidebar .logout-btn {
            display: flex;
            align-items: center;
            gap: 14px;
            color: #cbd5e1;
            text-decoration: none;
            padding: 0.7rem 1rem;
            border-radius: 12px;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            position: relative;
            white-space: nowrap;
        }

        /* Icon styling */
        .sidebar a i,
        .sidebar .logout-btn i {
            width: 24px;
            font-size: 1.2rem;
            text-align: center;
        }

        /* Hide text when collapsed */
        .sidebar.collapsed a span,
        .sidebar.collapsed .logout-btn span {
            display: none;
        }

        /* Hover effect */
        .sidebar a:hover,
        .sidebar .logout-btn:hover {
            background: rgba(230, 0, 35, 0.15);
            color: white;
            transform: translateX(4px);
        }

        /* Active link styling – with left red border + glow (applies to ALL sections) */
        .sidebar a.active {
            background: rgba(230, 0, 35, 0.2);
            color: white;
            box-shadow: 0 4px 12px rgba(230, 0, 35, 0.2);
            border-left: 3px solid #e60023;
            border-radius: 12px 8px 8px 12px;
        }
        .sidebar a.active i {
            color: #e60023;
            text-shadow: 0 0 6px rgba(230,0,35,0.5);
        }

        /* ========== MAIN CONTENT (dynamic margin) ========== */
        .main {
            flex: 1;
            padding: 1.8rem 2rem;
            transition: margin-left 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            margin-left: 280px;
        }
        /* When sidebar is collapsed on desktop */
        body.sidebar-collapsed .main {
            margin-left: 85px;
        }

        /* ========== TOPBAR (glass) ========== */
        .topbar {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border-radius: 1.5rem;
            padding: 0.8rem 1.8rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 8px 20px -6px rgba(0, 0, 0, 0.05);
        }

        .page-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #0f172a, #334155);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.01em;
        }
        .page-header p {
            font-size: 0.85rem;
            color: #5b6e8c;
            margin-top: 0.2rem;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: white;
            padding: 0.4rem 1rem 0.4rem 0.8rem;
            border-radius: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            border: 1px solid #e2e8f0;
        }
        .avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
        }
        .admin-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1e293b;
        }

        /* ========== RESPONSIVE (mobile overlay) ========== */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 280px;
            }
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            .main {
                margin-left: 0 !important;
                width: 100%;
                padding: 1rem;
            }
            body.sidebar-collapsed .main {
                margin-left: 0;
            }
            /* On mobile, collapsed state is ignored, we only use overlay */
            .sidebar.collapsed {
                width: 280px;
            }
            .sidebar.collapsed .logo {
                opacity: 1;
                visibility: visible;
                width: auto;
            }
            .sidebar.collapsed a span,
            .sidebar.collapsed .logout-btn span {
                display: inline;
            }
            .sidebar.collapsed h3 {
                opacity: 1;
                visibility: visible;
                height: auto;
                margin: 1.5rem 0 0.8rem 0.5rem;
            }
            .menu-toggle {
                position: fixed;
                top: 1rem;
                right: 1rem;
                z-index: 1100;
                background: white;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 2rem;
                font-size: 1.2rem;
                cursor: pointer;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .desktop-toggle {
                display: none;
            }
        }

        @media (min-width: 769px) {
            .menu-toggle {
                display: none;
            }
            .desktop-toggle {
                display: inline-flex;
            }
        }

        /* Utility */
        .text-red { color: #e60023; }
        
        /* ========== FLOATING TOOLTIP (appears above cursor on hover, hide mode only) ========== */
        .floating-tooltip {
            position: fixed;
            background: #1e293b;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
            z-index: 10000;
            pointer-events: none;
            box-shadow: 0 4px 14px rgba(0,0,0,0.25);
            border-left: 3px solid #e60023;
            font-family: 'Inter', sans-serif;
            backdrop-filter: blur(4px);
            letter-spacing: 0.3px;
            transition: opacity 0.12s ease;
            opacity: 0;
            transform: translateY(-8px);
        }
        
        .floating-tooltip.visible {
            opacity: 1;
            transform: translateY(-12px);
        }
        
        /* ensure tooltip doesn't cause any layout shift */
        body {
            overflow-x: hidden;
        }
    </style>

    @yield('styles')
</head>
<body>

    <!-- Mobile toggle button (always visible on small screens) -->
    <button class="menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i> Menu
    </button>

    <!-- SIDEBAR (Enhanced with collapsible logic) -->
    <div class="sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <div class="logo">Achilles</div>
            <button class="toggle-btn desktop-toggle" id="sidebarToggle">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <h3><i class="fas fa-th-large fa-fw"></i> Main</h3>
        <a href="{{ route('admin.dashboard') }}" data-tooltip="Dashboard" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.products.index') }}" data-tooltip="Products" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <i class="fas fa-box-open"></i> <span>Products</span>
        </a>
        <a href="{{ route('admin.categories.index') }}" data-tooltip="Categories" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <i class="fas fa-tags"></i> <span>Categories</span>
        </a>
        <a href="{{ route('admin.brands.index') }}" data-tooltip="Brands" class="{{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
            <i class="fas fa-building"></i> <span>Brands</span>
        </a>
<a href="{{ route('admin.shoe-types.index') }}" data-tooltip="Shoe Types" class="{{ request()->routeIs('admin.shoe-types.*') ? 'active' : '' }}">
            <i class="fas fa-shoe-prints"></i> <span>Shoe Types</span>
        </a>

        <a href="{{ route('admin.users.index') }}" data-tooltip="Users" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> <span>Users</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" data-tooltip="Orders" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-bag"></i> <span>Orders</span>
        </a>

        <div class="sidebar-divider"></div>

        <h3><i class="fas fa-chart-line fa-fw"></i> Analytics</h3>
        <!-- Reports – now with active highlighting -->
        <a href="{{ route('admin.reports.index') }}" data-tooltip="Reports" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <i class="fas fa-file-alt"></i> <span>Reports</span>
        </a>
        <!-- Inventory – now with active highlighting -->
        <a href="{{ route('admin.inventory.index') }}" data-tooltip="Inventory" class="{{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
            <i class="fas fa-warehouse"></i> <span>Inventory</span>
        </a>

        <div class="sidebar-divider"></div>

        <h3><i class="fas fa-user-cog fa-fw"></i> Account</h3>
        <!-- Settings – active if route exists (optional) -->
        <a href="#" data-tooltip="Settings" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="fas fa-sliders-h"></i> <span>Settings</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" style="margin-top: 1rem;">
            @csrf
            <button type="submit" class="logout-btn" data-tooltip="Logout">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </button>
        </form>
    </div>

    <!-- ===== IMMEDIATE SIDEBAR STATE RESTORATION ===== -->
    <script>
        (function() {
            // This runs immediately after the sidebar element is parsed,
            // before the rest of the page loads, to prevent flicker.
            var sidebar = document.getElementById('adminSidebar');
            if (sidebar) {
                var stored = localStorage.getItem('achilles_sidebar_collapsed');
                // Only apply on desktop (>=769px) – we'll also check on load,
                // but we can safely set the class now; it will be overridden if on mobile.
                var isDesktop = window.innerWidth >= 769;
                if (stored === '1' && isDesktop) {
                    sidebar.classList.add('collapsed');
                    document.body.classList.add('sidebar-collapsed');
                    // Fix toggle icon
                    var toggleBtn = document.getElementById('sidebarToggle');
                    if (toggleBtn) {
                        var icon = toggleBtn.querySelector('i');
                        if (icon) icon.className = 'fas fa-chevron-right';
                    }
                }
            }
        })();
    </script>

    <!-- MAIN CONTENT -->
    <div class="main">
        @yield('content')
    </div>

    <script>
        (function() {
            // DOM elements
            const sidebar = document.getElementById('adminSidebar');
            const body = document.body;
            const toggleBtn = document.getElementById('sidebarToggle');
            const mobileToggle = document.getElementById('mobileMenuToggle');

            // localStorage key
            const STORAGE_KEY = 'achilles_sidebar_collapsed';

            // Check if we are on desktop (min-width 769px)
            function isDesktop() {
                return window.innerWidth >= 769;
            }

            // Apply collapsed state (only on desktop)
            function setCollapsed(collapsed) {
                if (!isDesktop()) {
                    // On mobile, collapsed class is not used (sidebar width fixed)
                    sidebar.classList.remove('collapsed');
                    body.classList.remove('sidebar-collapsed');
                    return;
                }
                if (collapsed) {
                    sidebar.classList.add('collapsed');
                    body.classList.add('sidebar-collapsed');
                    const icon = toggleBtn?.querySelector('i');
                    if (icon) icon.className = 'fas fa-chevron-right';
                } else {
                    sidebar.classList.remove('collapsed');
                    body.classList.remove('sidebar-collapsed');
                    const icon = toggleBtn?.querySelector('i');
                    if (icon) icon.className = 'fas fa-chevron-left';
                }
                localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
            }

            // Initialize from localStorage – but we already set the class immediately,
            // so we just need to sync the toggle icon and body class if not already set.
            function initSidebarState() {
                const saved = localStorage.getItem(STORAGE_KEY);
                const shouldCollapse = saved === '1';
                // If the sidebar already has the class, make sure body class matches.
                // But we trust the immediate script did it.
                // However, if we are on mobile, we need to override.
                if (!isDesktop()) {
                    // Mobile: ensure collapsed class is removed
                    sidebar.classList.remove('collapsed');
                    body.classList.remove('sidebar-collapsed');
                    // Restore toggle icon to left
                    const icon = toggleBtn?.querySelector('i');
                    if (icon) icon.className = 'fas fa-chevron-left';
                } else {
                    // Desktop: ensure state matches saved, but if the immediate script already set it,
                    // we might not need to do anything. However, if the saved state changed since
                    // the immediate script (e.g., user toggled in another tab), we should sync.
                    // But we don't have a way to detect that easily, so we just enforce.
                    // To avoid flicker, we only set if different.
                    const currentlyCollapsed = sidebar.classList.contains('collapsed');
                    if (shouldCollapse !== currentlyCollapsed) {
                        setCollapsed(shouldCollapse);
                    } else {
                        // Ensure body class matches
                        if (shouldCollapse) {
                            body.classList.add('sidebar-collapsed');
                        } else {
                            body.classList.remove('sidebar-collapsed');
                        }
                    }
                }
            }

            // Toggle function
            function toggleSidebar() {
                if (!isDesktop()) return;
                const isCollapsed = sidebar.classList.contains('collapsed');
                setCollapsed(!isCollapsed);
            }

            // Mobile: open/close overlay
            function toggleMobile() {
                if (isDesktop()) return;
                sidebar.classList.toggle('mobile-open');
            }

            // Close mobile sidebar when clicking outside
            function handleClickOutside(e) {
                if (!isDesktop() && sidebar.classList.contains('mobile-open')) {
                    if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                        sidebar.classList.remove('mobile-open');
                    }
                }
            }

            // Re‑evaluate on window resize
            function handleResize() {
                if (isDesktop()) {
                    sidebar.classList.remove('mobile-open');
                    // Re‑apply saved collapsed state for desktop
                    const saved = localStorage.getItem(STORAGE_KEY);
                    const shouldCollapse = saved === '1';
                    // Use setCollapsed to ensure consistency
                    setCollapsed(shouldCollapse);
                } else {
                    // On mobile: remove collapsed class and ensure full width
                    sidebar.classList.remove('collapsed');
                    body.classList.remove('sidebar-collapsed');
                    if (toggleBtn) {
                        const icon = toggleBtn.querySelector('i');
                        if (icon) icon.className = 'fas fa-chevron-left';
                    }
                }
            }

            // ========== FLOATING TOOLTIP ==========
            let tooltipElement = null;
            
            function createTooltipElement() {
                if (tooltipElement) return tooltipElement;
                const div = document.createElement('div');
                div.className = 'floating-tooltip';
                document.body.appendChild(div);
                tooltipElement = div;
                return tooltipElement;
            }
            
            function updateTooltipPosition(event, tooltip) {
                if (!tooltip) return;
                let x = event.clientX;
                let y = event.clientY;
                const rect = tooltip.getBoundingClientRect();
                const width = rect.width;
                const height = rect.height;
                let topPos = y - height - 12;
                let leftPos = x + 15;
                if (leftPos + width > window.innerWidth - 10) {
                    leftPos = x - width - 15;
                }
                if (leftPos < 8) leftPos = 12;
                if (leftPos + width > window.innerWidth - 8) {
                    leftPos = window.innerWidth - width - 12;
                }
                if (topPos < 8) {
                    topPos = y + 20;
                }
                tooltip.style.left = leftPos + 'px';
                tooltip.style.top = topPos + 'px';
            }
            
            let activeTooltipTarget = null;
            let tooltipTimeout = null;
            
            function showFloatingTooltip(target, event) {
                if (!target) return;
                let label = target.getAttribute('data-tooltip');
                if (!label) {
                    const span = target.querySelector('span');
                    if (span && span.innerText.trim()) {
                        label = span.innerText.trim();
                    } else {
                        label = 'Link';
                    }
                }
                const tooltip = createTooltipElement();
                tooltip.innerText = label;
                tooltip.classList.remove('visible');
                updateTooltipPosition(event, tooltip);
                requestAnimationFrame(() => {
                    if (tooltip.innerText === label) {
                        tooltip.classList.add('visible');
                    }
                });
                activeTooltipTarget = target;
            }
            
            function hideFloatingTooltip() {
                if (tooltipElement) {
                    tooltipElement.classList.remove('visible');
                }
                activeTooltipTarget = null;
            }
            
            function attachFloatingTooltips() {
                const navItems = document.querySelectorAll('#adminSidebar a, #adminSidebar .logout-btn');
                navItems.forEach(item => {
                    if (item._floatingMouseEnter) {
                        item.removeEventListener('mouseenter', item._floatingMouseEnter);
                        item.removeEventListener('mouseleave', item._floatingMouseLeave);
                        item.removeEventListener('mousemove', item._floatingMouseMove);
                    }
                    const mouseEnterHandler = function(e) {
                        if (!isDesktop()) return;
                        const sidebarElem = document.getElementById('adminSidebar');
                        if (!sidebarElem || !sidebarElem.classList.contains('collapsed')) return;
                        if (tooltipTimeout) {
                            clearTimeout(tooltipTimeout);
                            tooltipTimeout = null;
                        }
                        showFloatingTooltip(this, e);
                    };
                    const mouseMoveHandler = function(e) {
                        if (!isDesktop()) return;
                        const sidebarElem = document.getElementById('adminSidebar');
                        if (!sidebarElem || !sidebarElem.classList.contains('collapsed')) return;
                        if (activeTooltipTarget === this && tooltipElement) {
                            updateTooltipPosition(e, tooltipElement);
                        }
                    };
                    const mouseLeaveHandler = function() {
                        if (!isDesktop()) return;
                        tooltipTimeout = setTimeout(() => {
                            hideFloatingTooltip();
                            tooltipTimeout = null;
                        }, 80);
                    };
                    item._floatingMouseEnter = mouseEnterHandler;
                    item._floatingMouseMove = mouseMoveHandler;
                    item._floatingMouseLeave = mouseLeaveHandler;
                    item.addEventListener('mouseenter', mouseEnterHandler);
                    item.addEventListener('mousemove', mouseMoveHandler);
                    item.addEventListener('mouseleave', mouseLeaveHandler);
                });
            }
            
            function refreshTooltipBinding() {
                attachFloatingTooltips();
            }
            
            const sidebarObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        if (!sidebar.classList.contains('collapsed')) {
                            hideFloatingTooltip();
                        }
                    }
                });
            });
            sidebarObserver.observe(sidebar, { attributes: true });
            
            window.addEventListener('resize', function() {
                if (!isDesktop()) {
                    hideFloatingTooltip();
                } else {
                    if (sidebar && !sidebar.classList.contains('collapsed')) {
                        hideFloatingTooltip();
                    }
                }
                refreshTooltipBinding();
            });
            
            if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
            if (mobileToggle) mobileToggle.addEventListener('click', toggleMobile);
            document.addEventListener('click', handleClickOutside);
            window.addEventListener('resize', handleResize);
            
            attachFloatingTooltips();
            
            // Final initialization – ensures state is correct after all content loads
            setTimeout(() => {
                initSidebarState();
                refreshTooltipBinding();
                // Also, if on desktop and sidebar is collapsed, ensure tooltips work
                if (isDesktop() && sidebar.classList.contains('collapsed')) {
                    // tooltips ready
                } else {
                    hideFloatingTooltip();
                }
            }, 50);
            
            // Also call init immediately after this script to catch any edge cases
            initSidebarState();
        })();
    </script>
    @stack('scripts')
</body>
</html>