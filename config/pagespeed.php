<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PageSpeed Insights Thresholds
    |--------------------------------------------------------------------------
    | Berdasarkan standar Google PageSpeed Insights dan Core Web Vitals
    | https://web.dev/defining-core-web-vitals-thresholds/
    */
    
    'thresholds' => [
        // Response Time (untuk monitoring HTTP)
        'response_time' => [
            'good' => 2000,      // ≤ 2 detik (PageSpeed recommended)
            'warning' => 4000,   // 2 - 4 detik
            'down' => 8000,      // > 8 detik (anggap down)
        ],
        
        // Core Web Vitals
        'lcp' => [
            'good' => 2500,      // ≤ 2.5 detik
            'warning' => 4000,   // 2.5 - 4.0 detik
        ],
        'fid' => [
            'good' => 100,       // ≤ 100 ms
            'warning' => 300,    // 100 - 300 ms
        ],
        'cls' => [
            'good' => 0.1,       // ≤ 0.1
            'warning' => 0.25,   // 0.1 - 0.25
        ],
        
        // Additional metrics
        'fcp' => [
            'good' => 1800,      // ≤ 1.8 detik
            'warning' => 3000,   // 1.8 - 3.0 detik
        ],
        'tti' => [
            'good' => 3800,      // ≤ 3.8 detik
            'warning' => 7300,   // 3.8 - 7.3 detik
        ],
        'speed_index' => [
            'good' => 3400,      // ≤ 3.4 detik
            'warning' => 5800,   // 3.4 - 5.8 detik
        ],
    ],
    
    // Status mapping untuk konsistensi
    'status_mapping' => [
        'good' => 'UP',
        'warning' => 'WARNING',
        'down' => 'DOWN',
    ],
];