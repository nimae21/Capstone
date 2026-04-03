<?php

test('the application returns a successful response', function () {
    $uris = collect(app('router')->getRoutes()->getRoutes())->map(fn($route) => $route->uri())->all();

    dump($uris);

    $response = $this->get('/dashboard');

    dump($response->getStatusCode());
    dump($response->headers->all());

    $response->assertRedirect('/login');
});
