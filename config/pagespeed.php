<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PageSpeed Insights Thresholds - Website Pemerintahan
    |--------------------------------------------------------------------------
    | Berdasarkan standar Google PageSpeed Insights dan Core Web Vitals
    | dengan penyesuaian untuk website pemerintahan yang memiliki
    | infrastruktur lebih kompleks dan toleransi lebih tinggi.
    |
    | Referensi:
    | 1. Standar Kementerian Komunikasi dan Informatika Yordania (MODEE)
    |    - LCP: ≤ 3 detik (lebih longgar dari standar Google 2.5 detik)
    |    - FID: ≤ 200 ms
    |    - TTLB: ≤ 6-9 detik
    |
    | 2. Studi Kasus Website Pemerintah Kabupaten Labuhanbatu
    |    - LCP aktual: 4300 ms (4.3 detik) - masih "tidak direkomendasikan"
    |    - Menunjukkan website pemerintah sering beroperasi di luar standar ideal
    |
    | 3. Analisis 25 Website Pemerintah Global
    |    - Performa sangat bervariasi antar negara
    |    - India: LCP 4.60 detik (kategori poor)
    |    - Membuktikan standar global untuk website pemerintah sangat beragam
    |
    | Sumber:
    | - https://web.dev/defining-core-web-vitals-thresholds/
    | - https://www.researchgate.net/publication/374885367_E-Government_Website_Performance_Analysis_Using_GTmetrix
    | - https://www.semrush.com/blog/government-website-performance/
    */
    
    'thresholds' => [
        // ============================================================
        // RESPONSE TIME (HTTP Monitoring) - DIPERLAMBAT UNTUK PEMERINTAHAN
        // ============================================================
        // Berdasarkan:
        // - Standar e-government: LCP ≤ 3 detik
        // - TTLB: 6-9 detik
        // - Toleransi untuk infrastruktur pemerintah yang lebih kompleks
        // ============================================================
        'response_time' => [
            'good' => 3000,      // ≤ 3 detik → UP (berdasarkan standar LCP e-government)
            'warning' => 6000,   // 3 - 6 detik → WARNING (masih dalam toleransi)
            'down' => 10000,     // > 6 - 10 detik → DOWN (melewati batas TTLB)
        ],
        
        // ============================================================
        // CORE WEB VITALS (tetap standar Google, untuk referensi tambahan)
        // ============================================================
        
        // Largest Contentful Paint
        // Standar Google: ≤ 2.5 detik (good) | 2.5-4 detik (warning)
        // Untuk pemerintah: bisa lebih longgar, tapi tetap dipertahankan sebagai referensi
        'lcp' => [
            'good' => 2500,      // ≤ 2.5 detik (standar Google)
            'warning' => 4000,   // 2.5 - 4.0 detik (standar Google)
            // Note: Untuk website pemerintah, LCP 3-4 detik masih dianggap wajar
        ],
        
        // First Input Delay
        // Standar Google: ≤ 100 ms (good) | 100-300 ms (warning)
        // MODEE merekomendasikan: ≤ 200 ms
        'fid' => [
            'good' => 100,       // ≤ 100 ms (standar Google)
            'warning' => 300,    // 100 - 300 ms (standar Google)
        ],
        
        // Cumulative Layout Shift
        // Standar Google: ≤ 0.1 (good) | 0.1-0.25 (warning)
        'cls' => [
            'good' => 0.1,       // ≤ 0.1 (standar Google)
            'warning' => 0.25,   // 0.1 - 0.25 (standar Google)
        ],
        
        // ============================================================
        // ADDITIONAL METRICS (tetap standar Google)
        // ============================================================
        
        // First Contentful Paint
        'fcp' => [
            'good' => 1800,      // ≤ 1.8 detik (standar Google)
            'warning' => 3000,   // 1.8 - 3.0 detik (standar Google)
        ],
        
        // Time to Interactive
        'tti' => [
            'good' => 3800,      // ≤ 3.8 detik (standar Google)
            'warning' => 7300,   // 3.8 - 7.3 detik (standar Google)
        ],
        
        // Speed Index
        'speed_index' => [
            'good' => 3400,      // ≤ 3.4 detik (standar Google)
            'warning' => 5800,   // 3.4 - 5.8 detik (standar Google)
        ],
    ],
    
    // ============================================================
    // STATUS MAPPING
    // ============================================================
    'status_mapping' => [
        'good' => 'UP',
        'warning' => 'WARNING',
        'down' => 'DOWN',
    ],
    
    // ============================================================
    // DESKRIPSI STATUS (untuk tampilan)
    // ============================================================
    'status_descriptions' => [
        'UP' => [
            'label' => 'UP',
            'icon' => '🟢',
            'description' => 'Response time ≤ 3 detik. Performa baik untuk website pemerintahan.',
            'color' => '#059669',
        ],
        'WARNING' => [
            'label' => 'WARNING',
            'icon' => '🟡',
            'description' => 'Response time 3-6 detik. Perlu evaluasi performa.',
            'color' => '#d97706',
        ],
        'DOWN' => [
            'label' => 'DOWN',
            'icon' => '🔴',
            'description' => 'Response time > 6 detik. Service sangat lambat atau tidak responsif.',
            'color' => '#dc2626',
        ],
    ],
    
    // ============================================================
    // KATEGORI RESPONSE TIME (untuk analisis)
    // ============================================================
    'categories' => [
        'sangat_cepat' => [
            'max' => 2000,
            'label' => '⚡ SANGAT CEPAT',
            'description' => 'Performa sangat baik. Pengalaman pengguna optimal.',
        ],
        'baik' => [
            'max' => 3000,
            'label' => '🟢 BAIK',
            'description' => 'Masih dalam batas wajar untuk website pemerintahan.',
        ],
        'cukup_lambat' => [
            'max' => 5000,
            'label' => '🟡 CUKUP LAMBAT',
            'description' => 'Mulai terasa lambat. Perlu evaluasi performa.',
        ],
        'lambat' => [
            'max' => 7000,
            'label' => '🟠 LAMBAT',
            'description' => 'Pengguna mulai tidak sabar. Risiko bounce rate meningkat.',
        ],
        'sangat_lambat' => [
            'max' => 10000,
            'label' => '🔴 SANGAT LAMBAT',
            'description' => 'Pengalaman pengguna buruk. Perbaikan segera diperlukan!',
        ],
        'sangat_kritis' => [
            'max' => PHP_INT_MAX,
            'label' => '🔴 SANGAT KRITIS',
            'description' => 'Service hampir tidak bisa diakses! Tindakan darurat diperlukan!',
        ],
    ],
];