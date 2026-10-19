<?php

use App\Support\MediaUrl;

beforeEach(function () {
    config(['filesystems.disks.s3.url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
});

it('returns null for null and empty values', function () {
    expect(MediaUrl::url(null))->toBeNull();
    expect(MediaUrl::url(''))->toBeNull();
});

it('returns absolute http(s) values unchanged', function () {
    expect(MediaUrl::url('https://cdn.example.com/a.jpg'))->toBe('https://cdn.example.com/a.jpg');
    expect(MediaUrl::url('http://cdn.example.com/a.jpg'))->toBe('http://cdn.example.com/a.jpg');
});

it('builds a public url from a relative s3 key', function () {
    expect(MediaUrl::url('images/products/foo.jpg'))
        ->toBe('https://example.supabase.co/storage/v1/object/public/CHON_THANH/images/products/foo.jpg');
});

it('prefers local public file when it exists', function () {
    expect(MediaUrl::url('images/home-hero.jpg'))
        ->toBe('/images/home-hero.jpg');
});

it('prefers local public file for keys with leading slash', function () {
    expect(MediaUrl::url('/images/home-hero.jpg'))
        ->toBe('/images/home-hero.jpg');
});

it('strips leading slashes from keys', function () {
    expect(MediaUrl::url('/files/documents/a.pdf'))
        ->toBe('https://example.supabase.co/storage/v1/object/public/CHON_THANH/files/documents/a.pdf');
});
