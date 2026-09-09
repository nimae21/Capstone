(() => {
    // Keep wide data inside a keyboard- and touch-scrollable region, not the page.
    document.querySelectorAll('.admin-site main table, .admin-site .main table').forEach(table => {
        let region = table.closest('.overflow-x-auto, .table-scroll');
        if (!region) {
            region = document.createElement('div');
            region.className = 'table-scroll';
            table.before(region);
            region.append(table);
        }
        region.tabIndex = 0;
        region.setAttribute('role', 'region');
        region.setAttribute('aria-label', 'Scrollable data table');
    });

    const menu = document.getElementById('userMenu');
    const dropdown = document.getElementById('userDropdown');
    const trigger = document.getElementById('userMenuTrigger');
    if (menu && dropdown && trigger) {
        const setOpen = open => {
            dropdown.hidden = !open;
            trigger.setAttribute('aria-expanded', String(open));
        };
        trigger.addEventListener('click', () => setOpen(dropdown.hidden));
        document.addEventListener('click', event => {
            if (!menu.contains(event.target)) setOpen(false);
        });
        menu.addEventListener('keydown', event => {
            if (event.key === 'Escape') { setOpen(false); trigger.focus(); }
        });
        menu.addEventListener('focusout', event => {
            if (!menu.contains(event.relatedTarget)) setOpen(false);
        });
    }

    const sidebar = document.getElementById('adminSidebar');
    const toggle = document.getElementById('mobileMenuToggle');
    if (sidebar && toggle) {
        const sync = () => {
            const mobile = matchMedia('(max-width: 768px)').matches;
            const open = sidebar.classList.contains('mobile-open');
            sidebar.inert = mobile && !open;
            toggle.setAttribute('aria-expanded', String(mobile && open));
        };
        toggle.setAttribute('aria-controls', 'adminSidebar');
        new MutationObserver(sync).observe(sidebar, { attributes: true, attributeFilter: ['class'] });
        window.addEventListener('resize', sync);
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
                document.body.classList.remove('menu-open');
                toggle.focus();
            }
        });
        sync();
    }
})();
