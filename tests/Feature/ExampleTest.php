<?php

test('guest is redirected to login when visiting root', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
