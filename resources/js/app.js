import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-recommendations-pending="1"]').forEach(async (section) => {
        try {
            const response = await fetch(section.dataset.recommendationsUrl, {
                headers: { Accept: 'text/html' },
                credentials: 'same-origin',
            });
            if (response.ok && section.isConnected) section.outerHTML = await response.text();
        } catch {
            // Recommendations are optional; the current page remains usable.
        }
    });
});
