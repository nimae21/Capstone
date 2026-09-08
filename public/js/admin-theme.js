(() => {
    const root = document.documentElement;
    const key = root.dataset.adminThemeKey;
    let persistent = true;
    let theme = 'light';
    try { theme = localStorage.getItem(key) === 'dark' ? 'dark' : 'light'; } catch { persistent = false; }

    function syncControls() {
        const toggle = document.getElementById('admin-dark-mode');
        if (toggle) toggle.setAttribute('aria-checked', String(theme === 'dark'));
        const status = document.getElementById('admin-theme-status');
        if (status) status.textContent = (theme === 'dark' ? 'Dark mode is on.' : 'Light mode is on.')
            + (persistent ? ' Saved automatically.' : ' Applied for this visit; browser storage is unavailable.');
    }
    function apply(value) {
        theme = value === 'dark' ? 'dark' : 'light';
        root.dataset.adminTheme = theme;
        syncControls();
        if (window.Chart) {
            window.Chart.defaults.color = theme === 'dark' ? '#cbd5e1' : '#64748b';
            window.Chart.defaults.borderColor = theme === 'dark' ? '#334155' : '#e2e8f0';
            Object.values(window.Chart.instances || {}).forEach(chart => chart.update('none'));
        }
    }
    // Runs in the head, before page content paints.
    apply(theme);
    window.addEventListener('storage', event => {
        if (event.key === key || event.key === null) apply(event.newValue);
    });
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Chart) {
            window.Chart.register({
                id: 'achillesAdminTheme',
                beforeUpdate(chart) {
                    const dark = root.dataset.adminTheme === 'dark';
                    const text = dark ? '#cbd5e1' : '#64748b';
                    const grid = dark ? '#334155' : '#e2e8f0';
                    const legend = chart.options.plugins?.legend;
                    if (legend && legend.labels) legend.labels.color = text;
                    const title = chart.options.plugins?.title;
                    if (title) title.color = text;
                    for (const axis of Object.values(chart.options.scales || {})) {
                        if (axis.ticks) axis.ticks.color = text;
                        if (axis.grid) axis.grid.color = grid;
                        if (axis.border) axis.border.color = grid;
                        if (axis.title) axis.title.color = text;
                    }
                },
            });
            apply(theme);
        }
        syncControls();
        document.getElementById('admin-dark-mode')?.addEventListener('click', () => {
            const next = theme === 'dark' ? 'light' : 'dark';
            try { localStorage.setItem(key, next); persistent = true; } catch { persistent = false; }
            apply(next);
        });
    });
})();
