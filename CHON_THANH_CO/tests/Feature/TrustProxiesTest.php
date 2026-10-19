<?php

it('coi request X-Forwarded-Proto https từ proxy nội bộ là secure', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '172.17.0.1']);

    $this->withHeaders(['X-Forwarded-Proto' => 'https'])
        ->get('/up')
        ->assertOk();

    expect(url('/'))->toStartWith('https://');
});

it('không tin X-Forwarded-Proto từ IP ngoài dải tin cậy', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8']);

    $this->withHeaders(['X-Forwarded-Proto' => 'https'])
        ->get('/up')
        ->assertOk();

    expect(url('/'))->toStartWith('http://');
});
