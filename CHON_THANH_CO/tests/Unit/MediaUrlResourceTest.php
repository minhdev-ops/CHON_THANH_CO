<?php

use App\Http\Resources\CertificateResource;
use App\Http\Resources\SettingResource;
use Illuminate\Http\Request;

beforeEach(function () {
    config(['filesystems.disks.s3.url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
});

it('builds certificate media urls from s3 keys', function () {
    $request = Request::create('/api/v1/certificates');
    $resource = new CertificateResource((object) [
        'slug' => 'chung-nhan iso',
        'name' => 'ISO 9001',
        'description' => 'desc',
        'image' => 'images/certificates/iso.jpg',
        'file' => 'files/certificates/iso.pdf',
    ]);

    $data = $resource->toArray($request);

    expect($data['image'])->toBe('https://example.supabase.co/storage/v1/object/public/CHON_THANH/images/certificates/iso.jpg')
        ->and($data['file'])->toBe('https://example.supabase.co/storage/v1/object/public/CHON_THANH/files/certificates/iso.pdf');
});

it('keeps external urls and nulls untouched on certificates', function () {
    $request = Request::create('/api/v1/certificates');
    $resource = new CertificateResource((object) [
        'slug' => 'x',
        'name' => 'x',
        'description' => null,
        'image' => null,
        'file' => 'https://external.example.com/a.pdf',
    ]);

    $data = $resource->toArray($request);

    expect($data['image'])->toBeNull()
        ->and($data['file'])->toBe('https://external.example.com/a.pdf');
});

it('builds urls for company file settings only', function () {
    $request = Request::create('/api/v1/settings');

    $capability = (new SettingResource((object) [
        'key' => 'company.capability_file',
        'value' => 'documents/ho-so-nang-luc-2026.pdf',
        'group' => 'company',
    ]))->toArray($request);

    $plain = (new SettingResource((object) [
        'key' => 'company.name',
        'value' => 'CHƠN THÀNH',
        'group' => 'company',
    ]))->toArray($request);

    expect($capability['value'])->toBe('/documents/ho-so-nang-luc-2026.pdf')
        ->and($plain['value'])->toBe('CHƠN THÀNH');
});
