<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nguồn dữ liệu đã cào sẵn
    |--------------------------------------------------------------------------
    |
    | Mỗi nguồn mô tả: file CSV, thư mục ảnh gốc, thư mục con đích trong
    | public/userfiles/images/news/ và web path tương ứng.
    |
    */

    'sources' => [

        'thaichau' => [
            'label' => 'Thái Châu',
            'csv' => base_path('document/scraped/csv/thaichau-news.csv'),
            'images' => base_path('document/scraped/images/thaichau'),
            'target' => 'thaichau',
            'web_path' => '/userfiles/images/news/thaichau',
        ],

        'bactham' => [
            'label' => 'Bách Thạch',
            'csv' => base_path('document/scraped/csv/bactham-news.csv'),
            'images' => base_path('document/scraped/images/bactham'),
            'target' => 'bactham',
            'web_path' => '/userfiles/images/news/bactham',
        ],

    ],

    'image_map' => base_path('document/scraped/image-map.json'),

    /*
    |--------------------------------------------------------------------------
    | Thư mục public đích khi copy ảnh (public_path() mặc định)
    |--------------------------------------------------------------------------
    */

    'public_root' => null,

    /*
    |--------------------------------------------------------------------------
    | Bộ lọc chủ đề
    |--------------------------------------------------------------------------
    |
    | Chuyên mục nguồn được giữ nếu khớp một trong hai danh sách bên dưới
    | (so sánh không phân biệt hoa thường, đã bỏ dấu). Ngoài ra bài vẫn được
    | giữ nếu tiêu đề / mô tả / nội dung có chứa một trong `keep_keywords`.
    |
    */

    'allowed_source_categories' => [
        'Tin tức chung',
        'Hoạt động công ty',
        'Sự kiện',
        'Dự án tiêu biểu',
        'Tin công ty',
        'Giới thiệu công ty',
        'Vải địa kỹ thuật',
        'Vải địa kỹ thuật không dệt',
        'Vải địa kỹ thuật dệt',
        'Vải địa kỹ thuật trồng cây',
        'Vải địa kỹ thuật phức hợp',
        'Lưới địa kỹ thuật',
        'Màng chống thấm HDPE',
        'Màng chống thấm HDPE HSE',
        'Màng chống thấm Bentonite GCL',
        'GCL',
        'Rọ đá',
        'Rọ đá hộc',
        'Rọ đá neo',
        'Thảm rọ đá',
        'Thảm đá',
        'Thảm 3D',
        'Túi địa kỹ thuật',
        'Bấc thấm',
        'Vỉ nhựa thoát nước',
        'Ống địa kỹ thuật',
        'Địa kỹ thuật nền móng',
        'Địa kỹ thuật môi trường',
        'Thông số kỹ thuật',
        'Thông số kỹ thuật vải địa',
        'Máy hàn màng HDPE',
        'Máy hàn bạt HDPE',
        'Dự án Rọ đá - Thảm đá',
        'Dự án Màng chống thấm HDPE',
    ],

    'always_keep_categories' => [
        'Tin tức chung',
        'Giới thiệu công ty',
        'Tin công ty',
        'Hoạt động công ty',
    ],

    'keep_keywords' => [
        'địa kỹ thuật',
        'lưới địa kỹ thuật',
        'vải địa kỹ thuật',
        'geogrid',
        'geotextile',
        'geosynthetic',
        'geomembrane',
        'gcl',
        'hdpe',
        'thảm 3d',
        'lưới',
        'vải',
        'vỏ nhựa',
        'giao thông',
        'thủy lợt',
        'chống xói mòn',
        'rọ đá',
        'túi địa kỹ thuật',
        'ống địa kỹ thuật',
    ],

    'min_words' => 120,

    'max_title' => 250,

    'max_excerpt' => 500,

    'max_slug' => 120,

    'fallback_images' => [
        '/images/products/industrial-1.jpg',
        '/images/news/workshop.jpg',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dòng ghi nguồn
    |--------------------------------------------------------------------------
    |
    | Vì DB không lưu trường nguồn, mỗi bài được thêm dòng này ở cuối content.
    | Placeholder: {label} = tên nguồn, {url} = URL bài gốc, {host} = domain.
    |
    */

    'attribution' => [
        'enabled' => true,
        'template' => '<p><em>Nguồn: {label} — <a href="{url}" rel="noopener noreferrer" target="_blank">{host}</a></em></p>',
    ],

    /*
    |--------------------------------------------------------------------------
    | Bài seed cũ
    |--------------------------------------------------------------------------
    |
    | Slug của 6 bài NewsSeeder (dữ liệu giả) — soft-delete khi chạy import
    | để chỉ còn dữ liệu thật. Bốn chuyên mục seed được giữ lại.
    |
    */

    'prune_slugs' => [
        'giai-phap-gia-co-nen-dat-yeu',
        'ung-dung-tham-3d-chong-xoi-mon',
        'chon-thanh-dat-doi-tac-hock',
        'tieu-chuan-iso-9001-2015',
        'huong-dan-thi-cong-vai-dia-ky-thuat',
        'huong-dan-thi-cong-vai-dia-ky-thuât',
        'hoi-thao-ung-dung-geogrid-2024',
    ],

    'keep_category_slugs' => [
        'tin-tuc',
        'ky-thuat',
        'su-kien',
        'du-an',
    ],

];
