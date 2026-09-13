<?php

function frontendFiles(string $directory, string $suffix): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), $suffix)) {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

it('keeps only the frontend packages used by production entries', function () {
    $package = json_decode(file_get_contents(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $dependencies = array_merge($package['dependencies'] ?? [], $package['devDependencies'] ?? []);

    foreach (['react', 'react-dom', '@vitejs/plugin-react', 'bootstrap', '@popperjs/core', 'axios', 'sass'] as $removed) {
        expect($dependencies)->not->toHaveKey($removed);
    }

    foreach (['alpinejs', 'leaflet', 'chart.js', '@fontsource/inter', '@fortawesome/fontawesome-free'] as $retained) {
        expect($dependencies)->toHaveKey($retained);
    }

    $javascript = collect(frontendFiles(resource_path('js'), '.js'))
        ->map(fn (string $file) => file_get_contents($file))
        ->implode("\n");

    expect(file_exists(resource_path('js/bootstrap.js')))->toBeFalse()
        ->and(file_exists(resource_path('js/components/Example.jsx')))->toBeFalse()
        ->and($javascript)->not->toMatch('/(?:from|import)\s*[\'\"](?:react|react-dom|axios|bootstrap|@popperjs)/')
        ->and(file_get_contents(base_path('vite.config.js')))->not->toContain('@vitejs/plugin-react');
});

it('uses Vite entries instead of the removed browser CDNs for fonts icons maps and charts', function () {
    $blade = collect(frontendFiles(resource_path('views'), '.blade.php'))
        ->map(fn (string $path) => file_get_contents($path))
        ->implode("\n");

    expect($blade)->not->toContain('fonts.googleapis.com')
        ->not->toContain('fonts.bunny.net')
        ->not->toContain('cdnjs.cloudflare.com/ajax/libs/font-awesome')
        ->not->toContain('unpkg.com/leaflet')
        ->not->toContain('cdn.jsdelivr.net/npm/chart.js');

    $vite = file_get_contents(base_path('vite.config.js'));
    foreach (['vendor-assets.css', 'address-selector.js', 'checkout-address.js', 'chart-vendor.js'] as $entry) {
        expect($vite)->toContain($entry);
    }
});
