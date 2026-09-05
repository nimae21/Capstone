<?php

test('the public landing page returns a successful response', function () {
    $this->get('/')->assertSuccessful();
});
