-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 27, 2026 at 03:32 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laravel_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:2;', 1785122751),
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1785122751;', 1785122751);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'By-U', '6285129537429', 1, '2026-07-27 03:23:42', '2026-07-27 03:23:42');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_22_022749_create_services_table', 1),
(5, '2026_06_22_022759_create_contacts_table', 1),
(6, '2026_06_22_022808_create_service_logs_table', 1),
(7, '2026_06_22_022814_create_smoke_devices_table', 1),
(8, '2026_06_22_022820_create_smoke_logs_table', 1),
(9, '2026_06_23_064147_add_last_status_notified_to_smoke_devices', 1),
(10, '2026_06_28_083725_add_username_to_users_table', 1),
(11, '2026_06_28_085256_create_personal_access_tokens_table', 1),
(12, '2026_07_01_210429_add_action_to_service_logs_table', 1),
(13, '2026_07_04_143846_add_check_columns_to_services_and_logs', 1),
(14, '2026_07_19_035614_add_interval_fields_to_services_table', 1),
(15, '2026_07_25_145924_add_last_interval_value_to_services_table', 1),
(16, '2026_07_25_212454_add_missing_wa_columns_to_services_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('http','ping') COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_status` enum('UP','WARNING','DOWN','UNKNOWN') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UNKNOWN',
  `last_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_response_time` double DEFAULT NULL,
  `last_message` text COLLATE utf8mb4_unicode_ci,
  `last_check_at` timestamp NULL DEFAULT NULL,
  `last_wa_sent_at` timestamp NULL DEFAULT NULL,
  `last_wa_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interval_wa_sent_in_this_cycle` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `wa_interval_minutes` int NOT NULL DEFAULT '0',
  `last_interval_checked_at` timestamp NULL DEFAULT NULL,
  `last_interval_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_interval_value` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `target`, `type`, `last_status`, `last_code`, `last_response_time`, `last_message`, `last_check_at`, `last_wa_sent_at`, `last_wa_status`, `interval_wa_sent_in_this_cycle`, `created_at`, `updated_at`, `wa_interval_minutes`, `last_interval_checked_at`, `last_interval_status`, `last_interval_value`) VALUES
(1, 'server', '103.151.63.68', 'ping', 'UP', 'PING_OK', 0.048, 'Host merespon ping (avg: 0.048s, min: 0.046s, max: 0.049s)', '2026-07-27 03:32:03', '2026-07-26 13:57:05', NULL, 0, '2026-07-26 13:45:47', '2026-07-27 03:32:03', 30, '2026-07-27 03:06:03', 'DOWN', 30),
(2, 'Banyumas Pringsewu', 'https://banyumas-lampung.desa.id/', 'http', 'UP', '200', 2.22, 'Service berjalan normal - Pengguna bisa akses', '2026-07-27 03:32:05', '2026-07-27 03:03:07', NULL, 0, '2026-07-26 13:47:03', '2026-07-27 03:32:05', 30, '2026-07-27 03:06:05', 'UP', 30),
(3, 'Simpedu dashboard', 'https://opd.simpedu.lampungprov.go.id/', 'http', 'UP', '200', 1.06, 'Service berjalan normal - Pengguna bisa akses', '2026-07-27 03:32:06', '2026-07-27 03:05:06', NULL, 0, '2026-07-27 03:05:02', '2026-07-27 03:32:06', 30, '2026-07-27 03:06:06', 'UP', 30),
(4, 'Kesbangpol Provinsi Lampung', 'https://kesbangpol.lampungprov.go.id', 'http', 'UP', '200', 1.08, 'Service berjalan normal - Pengguna bisa akses', '2026-07-27 03:32:08', '2026-07-27 03:08:08', NULL, 0, '2026-07-27 03:08:01', '2026-07-27 03:32:08', 5, '2026-07-27 03:31:07', 'UP', 5),
(5, 'Aplikasi Pajak Bahan Bakar Lampung', 'http://pbbkb.bapenda.lampungprov.go.id', 'http', 'UP', '301', 0.02, 'Redirect permanen', '2026-07-27 03:32:08', '2026-07-27 03:08:41', NULL, 0, '2026-07-27 03:08:41', '2026-07-27 03:32:08', 5, '2026-07-27 03:29:08', 'UP', 5),
(6, 'SIPD', 'https://sipd.kemendagri.go.id/', 'http', 'UP', '403', 0.83, 'Forbidden - Pengguna perlu izin - Masih bisa akses', '2026-07-27 03:32:08', '2026-07-27 03:09:06', NULL, 0, '2026-07-27 03:09:05', '2026-07-27 03:32:08', 5, '2026-07-27 03:27:09', 'UP', 5),
(7, 'SI-Manja Pemprov Lampung', 'https://simanja.lampungprov.go.id/', 'http', 'UP', '302', 0.9, 'Redirect sementara', '2026-07-27 03:32:09', '2026-07-27 03:09:46', NULL, 0, '2026-07-27 03:09:45', '2026-07-27 03:32:09', 5, '2026-07-27 03:27:10', 'UP', 5),
(8, 'E-Sughat', 'https://e-sughat.lampungprov.go.id/', 'http', 'UP', '200', 0.93, 'Service berjalan normal - Pengguna bisa akses', '2026-07-27 03:32:10', '2026-07-27 03:16:32', NULL, 0, '2026-07-27 03:10:28', '2026-07-27 03:32:10', 5, '2026-07-27 03:27:11', 'UP', 5),
(9, 'Website Portal PD', 'https://esdm.lampungprov.go.id', 'http', 'UP', '200', 1.26, 'Service berjalan normal - Pengguna bisa akses', '2026-07-27 03:32:12', '2026-07-27 03:11:01', NULL, 0, '2026-07-27 03:11:00', '2026-07-27 03:32:12', 5, '2026-07-27 03:32:12', 'UP', 5),
(10, 'UPTD Labkes Prov Lampung', 'https://labkes.lampungprov.go.id', 'http', 'UP', '200', 2.06, 'Service berjalan normal - Pengguna bisa akses', '2026-07-27 03:32:14', '2026-07-27 03:11:30', NULL, 0, '2026-07-27 03:11:28', '2026-07-27 03:32:14', 5, '2026-07-27 03:28:17', 'UP', 5),
(11, 'e-Horti', 'https://ehorti.e-kpb.lampungprov.go.id/login', 'http', 'UP', '200', 0.84, 'Service berjalan normal - Pengguna bisa akses', '2026-07-27 03:32:15', '2026-07-27 03:11:49', NULL, 0, '2026-07-27 03:11:48', '2026-07-27 03:32:15', 5, '2026-07-27 03:28:18', 'UP', 5),
(12, 'Jama-Jama API Satu Data Provinsi Lampung', 'https://jama-jama.lampungprov.go.id', 'http', 'UP', '200', 1.03, 'Service berjalan normal - Pengguna bisa akses', '2026-07-27 03:32:16', '2026-07-27 03:12:08', NULL, 0, '2026-07-27 03:12:06', '2026-07-27 03:32:16', 5, '2026-07-27 03:28:19', 'UP', 5),
(13, 'TIK interface', 'http://layanantik.lampungprov.go.id', 'http', 'UP', '301', 0.07, 'Redirect permanen', '2026-07-27 03:32:16', '2026-07-27 03:13:24', NULL, 0, '2026-07-27 03:13:24', '2026-07-27 03:32:16', 5, '2026-07-27 03:30:16', 'UP', 5),
(14, 'Dashboard PD Diskominfotik', 'http://dashboard.lampungprov.go.id', 'http', 'UP', '301', 0.07, 'Redirect permanen', '2026-07-27 03:32:16', '2026-07-27 03:13:48', NULL, 0, '2026-07-27 03:13:47', '2026-07-27 03:32:16', 5, '2026-07-27 03:29:16', 'UP', 5),
(15, 'Pendataan Non-ASN Pemrov Lampung', 'https://pendataan-nonasn.bkd.lampungprov.go.id', 'http', 'DOWN', 'TIMEOUT', 0.04, 'Koneksi timeout - Pengguna tidak bisa akses', '2026-07-27 03:32:16', '2026-07-27 03:15:58', NULL, 0, '2026-07-27 03:15:54', '2026-07-27 03:32:16', 5, '2026-07-27 03:32:16', 'DOWN', 5),
(16, 'Seleksi BKD Pemrov Lampung', 'https://seleksi.bkd.lampungprov.go.id', 'http', 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', '2026-07-27 03:32:28', '2026-07-27 03:16:55', NULL, 0, '2026-07-27 03:16:43', '2026-07-27 03:32:28', 5, '2026-07-27 03:32:28', 'DOWN', 5),
(17, 'Monitoring Bapenda', 'https://monitoring.bapenda.lampungprov.go.id', 'http', 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', '2026-07-27 03:31:40', '2026-07-27 03:17:47', NULL, 0, '2026-07-27 03:17:35', '2026-07-27 03:31:40', 5, '2026-07-27 03:28:43', 'DOWN', 5),
(18, 'SIPPKD', 'http://sippkd.lampungprov.go.id', 'http', 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', '2026-07-27 03:31:52', '2026-07-27 03:18:16', NULL, 0, '2026-07-27 03:18:04', '2026-07-27 03:31:52', 5, '2026-07-27 03:29:52', 'DOWN', 5),
(19, 'SIMONEV', 'http://sippd.lampungprov.go.id/simonev/', 'http', 'DOWN', 'TIMEOUT', 0.03, 'Koneksi timeout - Pengguna tidak bisa akses', '2026-07-27 03:31:52', '2026-07-27 03:19:44', NULL, 0, '2026-07-27 03:19:44', '2026-07-27 03:31:52', 5, '2026-07-27 03:31:52', 'DOWN', 5),
(20, 'SILAKI', 'https://silaki.biroadpim.lampungprov.go.id/', 'http', 'DOWN', 'TIMEOUT', 12.02, 'Koneksi timeout - Pengguna tidak bisa akses', '2026-07-27 03:32:04', '2026-07-27 03:21:26', NULL, 0, '2026-07-27 03:21:14', '2026-07-27 03:32:04', 5, '2026-07-27 03:30:04', 'DOWN', 5);

-- --------------------------------------------------------

--
-- Table structure for table `service_logs`
--

CREATE TABLE `service_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `response_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_time` double DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT NULL,
  `is_status_change` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Menandakan apakah ini adalah perubahan status',
  `previous_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status sebelumnya sebelum perubahan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_logs`
--

INSERT INTO `service_logs` (`id`, `service_id`, `status`, `response_code`, `response_time`, `message`, `action`, `checked_at`, `is_status_change`, `previous_status`, `created_at`, `updated_at`) VALUES
(1, 1, 'DOWN', 'UNREACHABLE', 5.82, 'Host tidak dapat dijangkau', 'Periksa koneksi jaringan, firewall, dan routing', '2026-07-26 13:45:53', 1, 'UNKNOWN', '2026-07-26 13:45:53', '2026-07-26 13:45:53'),
(2, 1, 'UP', 'PING_OK', 0.044, 'Host merespon ping (avg: 0.044s, min: 0.037s, max: 0.05s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-26 13:46:05', 1, 'DOWN', '2026-07-26 13:46:05', '2026-07-26 13:46:05'),
(3, 2, 'DOWN', '500', 1.48, 'Server error (500) dengan response kosong - Pengguna tidak bisa akses', 'Cek log server, periksa error di aplikasi', '2026-07-26 13:47:05', 1, 'UNKNOWN', '2026-07-26 13:47:05', '2026-07-26 13:47:05'),
(4, 2, 'UP', '200', 2.53, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-26 13:47:22', 1, 'DOWN', '2026-07-26 13:47:22', '2026-07-26 13:47:22'),
(5, 2, 'DOWN', '500', 1.43, 'Server error (500) dengan response kosong - Pengguna tidak bisa akses', 'Cek log server, periksa error di aplikasi', '2026-07-26 13:53:44', 1, 'UP', '2026-07-26 13:53:44', '2026-07-26 13:53:44'),
(6, 1, 'UP', 'PING_OK', 0.016, 'Host merespon ping (avg: 0.016s, min: 0.015s, max: 0.017s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-26 13:54:03', 0, 'UP', '2026-07-26 13:54:03', '2026-07-26 13:54:03'),
(7, 2, 'DOWN', '500', 1.25, 'Server error (500) dengan response kosong - Pengguna tidak bisa akses', 'Cek log server, periksa error di aplikasi', '2026-07-26 13:54:05', 0, 'DOWN', '2026-07-26 13:54:05', '2026-07-26 13:54:05'),
(8, 2, 'DOWN', '500', 1.26, 'Server error (500) dengan response kosong - Pengguna tidak bisa akses', 'Cek log server, periksa error di aplikasi', '2026-07-26 13:54:33', 0, 'DOWN', '2026-07-26 13:54:33', '2026-07-26 13:54:33'),
(9, 1, 'UP', 'PING_OK', 0.079, 'Host merespon ping (avg: 0.079s, min: 0.035s, max: 0.123s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-26 13:55:03', 0, 'UP', '2026-07-26 13:55:03', '2026-07-26 13:55:03'),
(10, 2, 'UP', '200', 3.08, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-26 13:55:05', 1, 'DOWN', '2026-07-26 13:55:05', '2026-07-26 13:55:05'),
(11, 2, 'UP', '200', 3.99, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-26 13:55:07', 1, 'DOWN', '2026-07-26 13:55:07', '2026-07-26 13:55:07'),
(12, 1, 'UP', 'PING_OK', 0.045, 'Host merespon ping (avg: 0.045s, min: 0.035s, max: 0.054s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-26 13:56:03', 0, 'UP', '2026-07-26 13:56:03', '2026-07-26 13:56:03'),
(13, 2, 'UP', '200', 2.54, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-26 13:56:06', 0, 'UP', '2026-07-26 13:56:06', '2026-07-26 13:56:06'),
(14, 1, 'DOWN', 'UNREACHABLE', 6.22, 'Host tidak dapat dijangkau', 'Periksa koneksi jaringan, firewall, dan routing', '2026-07-26 13:56:13', 1, 'UP', '2026-07-26 13:56:13', '2026-07-26 13:56:13'),
(15, 1, 'DOWN', 'TIMEOUT', 8.95, 'Request timeout - Host tidak merespon', 'Periksa firewall dan pastikan host menyala', '2026-07-26 13:56:43', 0, 'DOWN', '2026-07-26 13:56:43', '2026-07-26 13:56:43'),
(16, 1, 'UP', 'PING_OK', 0.044, 'Host merespon ping (avg: 0.044s, min: 0.015s, max: 0.072s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-26 13:57:03', 1, 'DOWN', '2026-07-26 13:57:03', '2026-07-26 13:57:03'),
(17, 2, 'UP', '200', 2.35, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-26 13:57:07', 0, 'UP', '2026-07-26 13:57:07', '2026-07-26 13:57:07'),
(18, 1, 'UP', 'PING_OK', 0.037, 'Host merespon ping (avg: 0.037s, min: 0.027s, max: 0.047s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:03:04', 0, 'UP', '2026-07-27 03:03:04', '2026-07-27 03:03:04'),
(19, 2, 'UP', '200', 2.22, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:03:06', 0, 'UP', '2026-07-27 03:03:06', '2026-07-27 03:03:06'),
(20, 1, 'UP', 'PING_OK', 0.038, 'Host merespon ping (avg: 0.038s, min: 0.029s, max: 0.046s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:04:03', 0, 'UP', '2026-07-27 03:04:03', '2026-07-27 03:04:03'),
(21, 2, 'UP', '200', 2.33, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:04:05', 0, 'UP', '2026-07-27 03:04:05', '2026-07-27 03:04:05'),
(22, 3, 'UP', '200', 0.8, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:05:03', 1, 'UNKNOWN', '2026-07-27 03:05:03', '2026-07-27 03:05:03'),
(23, 1, 'UP', 'PING_OK', 0.028, 'Host merespon ping (avg: 0.028s, min: 0.027s, max: 0.028s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:05:03', 0, 'UP', '2026-07-27 03:05:03', '2026-07-27 03:05:03'),
(24, 2, 'UP', '200', 2.24, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:05:06', 0, 'UP', '2026-07-27 03:05:06', '2026-07-27 03:05:06'),
(25, 3, 'UP', '200', 0.85, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:05:06', 1, 'UNKNOWN', '2026-07-27 03:05:06', '2026-07-27 03:05:06'),
(26, 1, 'UP', 'PING_OK', 0.049, 'Host merespon ping (avg: 0.049s, min: 0.046s, max: 0.051s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:06:03', 0, 'UP', '2026-07-27 03:06:03', '2026-07-27 03:06:03'),
(27, 2, 'UP', '200', 2.2, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:06:05', 0, 'UP', '2026-07-27 03:06:05', '2026-07-27 03:06:05'),
(28, 3, 'UP', '200', 0.73, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:06:06', 0, 'UP', '2026-07-27 03:06:06', '2026-07-27 03:06:06'),
(29, 1, 'UP', 'PING_OK', 0.031, 'Host merespon ping (avg: 0.031s, min: 0.027s, max: 0.034s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:07:03', 0, 'UP', '2026-07-27 03:07:03', '2026-07-27 03:07:03'),
(30, 2, 'UP', '200', 2.31, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:07:06', 0, 'UP', '2026-07-27 03:07:06', '2026-07-27 03:07:06'),
(31, 3, 'UP', '200', 0.71, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:07:06', 0, 'UP', '2026-07-27 03:07:06', '2026-07-27 03:07:06'),
(32, 4, 'UP', '200', 1.5, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:08:03', 1, 'UNKNOWN', '2026-07-27 03:08:03', '2026-07-27 03:08:03'),
(33, 1, 'UP', 'PING_OK', 0.035, 'Host merespon ping (avg: 0.035s, min: 0.029s, max: 0.041s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:08:03', 0, 'UP', '2026-07-27 03:08:03', '2026-07-27 03:08:03'),
(34, 2, 'UP', '200', 2.26, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:08:06', 0, 'UP', '2026-07-27 03:08:06', '2026-07-27 03:08:06'),
(35, 3, 'UP', '200', 0.96, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:08:07', 0, 'UP', '2026-07-27 03:08:07', '2026-07-27 03:08:07'),
(36, 4, 'UP', '200', 1.24, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:08:08', 1, 'UNKNOWN', '2026-07-27 03:08:08', '2026-07-27 03:08:08'),
(37, 5, 'UP', '301', 0.35, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:08:41', 1, 'UNKNOWN', '2026-07-27 03:08:41', '2026-07-27 03:08:41'),
(38, 1, 'UP', 'PING_OK', 0.051, 'Host merespon ping (avg: 0.051s, min: 0.028s, max: 0.073s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:09:03', 0, 'UP', '2026-07-27 03:09:03', '2026-07-27 03:09:03'),
(39, 2, 'UP', '200', 2.26, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:09:06', 0, 'UP', '2026-07-27 03:09:06', '2026-07-27 03:09:06'),
(40, 6, 'UP', '403', 0.95, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:09:06', 1, 'UNKNOWN', '2026-07-27 03:09:06', '2026-07-27 03:09:06'),
(41, 3, 'UP', '200', 0.88, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:09:06', 0, 'UP', '2026-07-27 03:09:06', '2026-07-27 03:09:06'),
(42, 4, 'UP', '200', 1.23, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:09:08', 0, 'UP', '2026-07-27 03:09:08', '2026-07-27 03:09:08'),
(43, 5, 'UP', '301', 0.24, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:09:08', 0, 'UP', '2026-07-27 03:09:08', '2026-07-27 03:09:08'),
(44, 7, 'UP', '302', 0.81, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:09:46', 1, 'UNKNOWN', '2026-07-27 03:09:46', '2026-07-27 03:09:46'),
(45, 1, 'UP', 'PING_OK', 1.613, 'Host merespon ping (avg: 1.613s, min: 0.077s, max: 3.149s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:10:05', 0, 'UP', '2026-07-27 03:10:05', '2026-07-27 03:10:05'),
(46, 2, 'DOWN', 'TIMEOUT', 20.09, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:10:26', 1, 'UP', '2026-07-27 03:10:26', '2026-07-27 03:10:26'),
(47, 3, 'UP', '200', 2.71, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:10:28', 0, 'UP', '2026-07-27 03:10:28', '2026-07-27 03:10:28'),
(48, 8, 'WARNING', '200', 10.41, 'Response lambat (10.41s) - Pengguna masih bisa akses tapi lambat', 'Optimasi performa server, response time terlalu lama', '2026-07-27 03:10:39', 1, 'UNKNOWN', '2026-07-27 03:10:39', '2026-07-27 03:10:39'),
(49, 4, 'WARNING', '200', 10.56, 'Response lambat (10.56s) - Pengguna masih bisa akses tapi lambat', 'Optimasi performa server, response time terlalu lama', '2026-07-27 03:10:39', 1, 'UP', '2026-07-27 03:10:39', '2026-07-27 03:10:39'),
(50, 5, 'UP', '301', 0.26, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:10:40', 0, 'UP', '2026-07-27 03:10:40', '2026-07-27 03:10:40'),
(51, 6, 'UP', '403', 0.88, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:10:40', 0, 'UP', '2026-07-27 03:10:40', '2026-07-27 03:10:40'),
(52, 7, 'UP', '302', 0.78, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:10:41', 0, 'UP', '2026-07-27 03:10:41', '2026-07-27 03:10:41'),
(53, 9, 'UP', '200', 1.54, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:11:01', 1, 'UNKNOWN', '2026-07-27 03:11:01', '2026-07-27 03:11:01'),
(54, 1, 'UP', 'PING_OK', 0.038, 'Host merespon ping (avg: 0.038s, min: 0.027s, max: 0.048s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:11:03', 0, 'UP', '2026-07-27 03:11:03', '2026-07-27 03:11:03'),
(55, 2, 'UP', '200', 2.31, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:11:06', 1, 'DOWN', '2026-07-27 03:11:06', '2026-07-27 03:11:06'),
(56, 3, 'UP', '200', 0.79, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:11:06', 0, 'UP', '2026-07-27 03:11:06', '2026-07-27 03:11:06'),
(57, 4, 'UP', '200', 1.06, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:11:08', 1, 'WARNING', '2026-07-27 03:11:08', '2026-07-27 03:11:08'),
(58, 5, 'UP', '301', 0.24, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:11:08', 0, 'UP', '2026-07-27 03:11:08', '2026-07-27 03:11:08'),
(59, 6, 'UP', '403', 0.69, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:11:09', 0, 'UP', '2026-07-27 03:11:09', '2026-07-27 03:11:09'),
(60, 7, 'UP', '302', 0.74, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:11:09', 0, 'UP', '2026-07-27 03:11:09', '2026-07-27 03:11:09'),
(61, 8, 'UP', '200', 0.76, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:11:10', 1, 'WARNING', '2026-07-27 03:11:10', '2026-07-27 03:11:10'),
(62, 9, 'UP', '200', 1.33, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:11:11', 0, 'UP', '2026-07-27 03:11:11', '2026-07-27 03:11:11'),
(63, 10, 'UP', '200', 2.05, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:11:30', 1, 'UNKNOWN', '2026-07-27 03:11:30', '2026-07-27 03:11:30'),
(64, 11, 'UP', '200', 1.12, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:11:49', 1, 'UNKNOWN', '2026-07-27 03:11:49', '2026-07-27 03:11:49'),
(65, 1, 'UP', 'PING_OK', 0.051, 'Host merespon ping (avg: 0.051s, min: 0.047s, max: 0.054s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:12:03', 0, 'UP', '2026-07-27 03:12:03', '2026-07-27 03:12:03'),
(66, 2, 'UP', '200', 2.36, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:12:06', 0, 'UP', '2026-07-27 03:12:06', '2026-07-27 03:12:06'),
(67, 3, 'UP', '200', 0.97, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:12:07', 0, 'UP', '2026-07-27 03:12:07', '2026-07-27 03:12:07'),
(68, 12, 'UP', '200', 1.15, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:12:08', 1, 'UNKNOWN', '2026-07-27 03:12:08', '2026-07-27 03:12:08'),
(69, 4, 'UP', '200', 1.26, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:12:08', 0, 'UP', '2026-07-27 03:12:08', '2026-07-27 03:12:08'),
(70, 5, 'UP', '301', 0.24, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:12:08', 0, 'UP', '2026-07-27 03:12:08', '2026-07-27 03:12:08'),
(71, 6, 'UP', '403', 0.72, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:12:09', 0, 'UP', '2026-07-27 03:12:09', '2026-07-27 03:12:09'),
(72, 7, 'UP', '302', 0.68, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:12:10', 0, 'UP', '2026-07-27 03:12:10', '2026-07-27 03:12:10'),
(73, 8, 'UP', '200', 0.76, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:12:10', 0, 'UP', '2026-07-27 03:12:10', '2026-07-27 03:12:10'),
(74, 9, 'UP', '200', 1.53, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:12:12', 0, 'UP', '2026-07-27 03:12:12', '2026-07-27 03:12:12'),
(75, 10, 'UP', '200', 2.08, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:12:14', 0, 'UP', '2026-07-27 03:12:14', '2026-07-27 03:12:14'),
(76, 11, 'UP', '200', 1.03, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:12:15', 0, 'UP', '2026-07-27 03:12:15', '2026-07-27 03:12:15'),
(77, 1, 'UP', 'PING_OK', 0.04, 'Host merespon ping (avg: 0.04s, min: 0.035s, max: 0.045s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:13:03', 0, 'UP', '2026-07-27 03:13:03', '2026-07-27 03:13:03'),
(78, 2, 'UP', '200', 2.17, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:13:05', 0, 'UP', '2026-07-27 03:13:05', '2026-07-27 03:13:05'),
(79, 3, 'UP', '200', 0.75, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:13:06', 0, 'UP', '2026-07-27 03:13:06', '2026-07-27 03:13:06'),
(80, 4, 'UP', '200', 1.27, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:13:07', 0, 'UP', '2026-07-27 03:13:07', '2026-07-27 03:13:07'),
(81, 5, 'UP', '301', 0.24, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:13:08', 0, 'UP', '2026-07-27 03:13:08', '2026-07-27 03:13:08'),
(82, 6, 'UP', '403', 0.84, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:13:09', 0, 'UP', '2026-07-27 03:13:09', '2026-07-27 03:13:09'),
(83, 7, 'UP', '302', 0.76, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:13:09', 0, 'UP', '2026-07-27 03:13:09', '2026-07-27 03:13:09'),
(84, 8, 'UP', '200', 0.81, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:13:10', 0, 'UP', '2026-07-27 03:13:10', '2026-07-27 03:13:10'),
(85, 9, 'UP', '200', 1.35, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:13:12', 0, 'UP', '2026-07-27 03:13:12', '2026-07-27 03:13:12'),
(86, 10, 'UP', '200', 1.98, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:13:14', 0, 'UP', '2026-07-27 03:13:14', '2026-07-27 03:13:14'),
(87, 11, 'UP', '200', 0.85, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:13:14', 0, 'UP', '2026-07-27 03:13:14', '2026-07-27 03:13:14'),
(88, 12, 'UP', '200', 0.92, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:13:15', 0, 'UP', '2026-07-27 03:13:15', '2026-07-27 03:13:15'),
(89, 13, 'UP', '301', 0.23, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:13:24', 1, 'UNKNOWN', '2026-07-27 03:13:24', '2026-07-27 03:13:24'),
(90, 14, 'UP', '301', 0.17, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:13:48', 1, 'UNKNOWN', '2026-07-27 03:13:48', '2026-07-27 03:13:48'),
(91, 1, 'UP', 'PING_OK', 0.056, 'Host merespon ping (avg: 0.056s, min: 0.046s, max: 0.066s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:14:03', 0, 'UP', '2026-07-27 03:14:03', '2026-07-27 03:14:03'),
(92, 2, 'UP', '200', 2.13, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:14:05', 0, 'UP', '2026-07-27 03:14:05', '2026-07-27 03:14:05'),
(93, 3, 'UP', '200', 0.91, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:14:06', 0, 'UP', '2026-07-27 03:14:06', '2026-07-27 03:14:06'),
(94, 4, 'UP', '200', 1.12, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:14:07', 0, 'UP', '2026-07-27 03:14:07', '2026-07-27 03:14:07'),
(95, 5, 'UP', '301', 0.26, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:14:08', 0, 'UP', '2026-07-27 03:14:08', '2026-07-27 03:14:08'),
(96, 6, 'UP', '403', 0.61, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:14:08', 0, 'UP', '2026-07-27 03:14:08', '2026-07-27 03:14:08'),
(97, 7, 'UP', '302', 0.69, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:14:09', 0, 'UP', '2026-07-27 03:14:09', '2026-07-27 03:14:09'),
(98, 8, 'UP', '200', 0.72, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:14:10', 0, 'UP', '2026-07-27 03:14:10', '2026-07-27 03:14:10'),
(99, 9, 'UP', '200', 1.28, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:14:11', 0, 'UP', '2026-07-27 03:14:11', '2026-07-27 03:14:11'),
(100, 10, 'UP', '200', 2.02, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:14:13', 0, 'UP', '2026-07-27 03:14:13', '2026-07-27 03:14:13'),
(101, 11, 'UP', '200', 0.82, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:14:14', 0, 'UP', '2026-07-27 03:14:14', '2026-07-27 03:14:14'),
(102, 12, 'UP', '200', 0.96, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:14:15', 0, 'UP', '2026-07-27 03:14:15', '2026-07-27 03:14:15'),
(103, 13, 'UP', '301', 0.08, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:14:15', 0, 'UP', '2026-07-27 03:14:15', '2026-07-27 03:14:15'),
(104, 14, 'UP', '301', 0.07, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:14:15', 0, 'UP', '2026-07-27 03:14:15', '2026-07-27 03:14:15'),
(105, 1, 'UP', 'PING_OK', 0.319, 'Host merespon ping (avg: 0.319s, min: 0.156s, max: 0.482s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:15:03', 0, 'UP', '2026-07-27 03:15:03', '2026-07-27 03:15:03'),
(106, 2, 'UP', '200', 5.17, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:15:08', 0, 'UP', '2026-07-27 03:15:08', '2026-07-27 03:15:08'),
(107, 3, 'UP', '200', 1.15, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:15:10', 0, 'UP', '2026-07-27 03:15:10', '2026-07-27 03:15:10'),
(108, 4, 'UP', '200', 1.36, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:15:11', 0, 'UP', '2026-07-27 03:15:11', '2026-07-27 03:15:11'),
(109, 5, 'UP', '301', 0.29, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:15:11', 0, 'UP', '2026-07-27 03:15:11', '2026-07-27 03:15:11'),
(110, 6, 'UP', '403', 0.84, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:15:12', 0, 'UP', '2026-07-27 03:15:12', '2026-07-27 03:15:12'),
(111, 7, 'UP', '302', 1.1, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:15:13', 0, 'UP', '2026-07-27 03:15:13', '2026-07-27 03:15:13'),
(112, 8, 'UP', '200', 0.76, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:15:14', 0, 'UP', '2026-07-27 03:15:14', '2026-07-27 03:15:14'),
(113, 9, 'UP', '200', 1.42, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:15:15', 0, 'UP', '2026-07-27 03:15:15', '2026-07-27 03:15:15'),
(114, 10, 'UP', '200', 1.96, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:15:17', 0, 'UP', '2026-07-27 03:15:17', '2026-07-27 03:15:17'),
(115, 11, 'UP', '200', 0.85, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:15:18', 0, 'UP', '2026-07-27 03:15:18', '2026-07-27 03:15:18'),
(116, 12, 'UP', '200', 0.83, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:15:19', 0, 'UP', '2026-07-27 03:15:19', '2026-07-27 03:15:19'),
(117, 13, 'UP', '301', 0.21, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:15:19', 0, 'UP', '2026-07-27 03:15:19', '2026-07-27 03:15:19'),
(118, 14, 'UP', '301', 0.24, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:15:20', 0, 'UP', '2026-07-27 03:15:20', '2026-07-27 03:15:20'),
(119, 15, 'DOWN', 'TIMEOUT', 4.69, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:15:58', 1, 'UNKNOWN', '2026-07-27 03:15:58', '2026-07-27 03:15:58'),
(120, 1, 'DOWN', 'TIMEOUT', 7.81, 'Request timeout - Host tidak merespon', 'Periksa firewall dan pastikan host menyala', '2026-07-27 03:16:10', 1, 'UP', '2026-07-27 03:16:10', '2026-07-27 03:16:10'),
(121, 2, 'WARNING', '200', 17.55, 'Response lambat (17.55s) - Pengguna masih bisa akses tapi lambat', 'Optimasi performa server, response time terlalu lama', '2026-07-27 03:16:27', 1, 'UP', '2026-07-27 03:16:27', '2026-07-27 03:16:27'),
(122, 3, 'UP', '200', 0.78, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:16:28', 0, 'UP', '2026-07-27 03:16:28', '2026-07-27 03:16:28'),
(123, 4, 'UP', '200', 1.21, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:16:29', 0, 'UP', '2026-07-27 03:16:29', '2026-07-27 03:16:29'),
(124, 5, 'UP', '301', 0.24, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:16:30', 0, 'UP', '2026-07-27 03:16:30', '2026-07-27 03:16:30'),
(125, 6, 'UP', '403', 0.67, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:16:30', 0, 'UP', '2026-07-27 03:16:30', '2026-07-27 03:16:30'),
(126, 7, 'UP', '302', 0.72, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:16:31', 0, 'UP', '2026-07-27 03:16:31', '2026-07-27 03:16:31'),
(127, 8, 'UP', '200', 0.78, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:16:32', 0, 'UP', '2026-07-27 03:16:32', '2026-07-27 03:16:32'),
(128, 9, 'UP', '200', 1.34, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:16:33', 0, 'UP', '2026-07-27 03:16:33', '2026-07-27 03:16:33'),
(129, 10, 'UP', '200', 1.89, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:16:35', 0, 'UP', '2026-07-27 03:16:35', '2026-07-27 03:16:35'),
(130, 11, 'UP', '200', 0.79, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:16:36', 0, 'UP', '2026-07-27 03:16:36', '2026-07-27 03:16:36'),
(131, 12, 'UP', '200', 0.87, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:16:37', 0, 'UP', '2026-07-27 03:16:37', '2026-07-27 03:16:37'),
(132, 13, 'UP', '301', 0.08, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:16:37', 0, 'UP', '2026-07-27 03:16:37', '2026-07-27 03:16:37'),
(133, 14, 'UP', '301', 0.06, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:16:37', 0, 'UP', '2026-07-27 03:16:37', '2026-07-27 03:16:37'),
(134, 15, 'DOWN', 'TIMEOUT', 0.04, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:16:37', 0, 'DOWN', '2026-07-27 03:16:37', '2026-07-27 03:16:37'),
(135, 16, 'DOWN', 'TIMEOUT', 12.08, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:16:55', 1, 'UNKNOWN', '2026-07-27 03:16:55', '2026-07-27 03:16:55'),
(136, 1, 'UP', 'PING_OK', 0.043, 'Host merespon ping (avg: 0.043s, min: 0.039s, max: 0.046s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:17:03', 1, 'DOWN', '2026-07-27 03:17:03', '2026-07-27 03:17:03'),
(137, 2, 'UP', '200', 2.24, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:17:06', 1, 'WARNING', '2026-07-27 03:17:06', '2026-07-27 03:17:06'),
(138, 3, 'UP', '200', 0.81, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:17:06', 0, 'UP', '2026-07-27 03:17:06', '2026-07-27 03:17:06'),
(139, 4, 'UP', '200', 1.12, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:17:08', 0, 'UP', '2026-07-27 03:17:08', '2026-07-27 03:17:08'),
(140, 5, 'UP', '301', 0.24, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:17:08', 0, 'UP', '2026-07-27 03:17:08', '2026-07-27 03:17:08'),
(141, 6, 'UP', '403', 0.72, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:17:09', 0, 'UP', '2026-07-27 03:17:09', '2026-07-27 03:17:09'),
(142, 7, 'UP', '302', 0.73, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:17:09', 0, 'UP', '2026-07-27 03:17:09', '2026-07-27 03:17:09'),
(143, 8, 'UP', '200', 0.8, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:17:10', 0, 'UP', '2026-07-27 03:17:10', '2026-07-27 03:17:10'),
(144, 9, 'UP', '200', 1.24, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:17:11', 0, 'UP', '2026-07-27 03:17:11', '2026-07-27 03:17:11'),
(145, 10, 'UP', '200', 1.92, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:17:13', 0, 'UP', '2026-07-27 03:17:13', '2026-07-27 03:17:13'),
(146, 11, 'UP', '200', 0.79, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:17:14', 0, 'UP', '2026-07-27 03:17:14', '2026-07-27 03:17:14'),
(147, 12, 'UP', '200', 0.93, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:17:15', 0, 'UP', '2026-07-27 03:17:15', '2026-07-27 03:17:15'),
(148, 13, 'UP', '301', 0.06, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:17:15', 0, 'UP', '2026-07-27 03:17:15', '2026-07-27 03:17:15'),
(149, 14, 'UP', '301', 0.08, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:17:15', 0, 'UP', '2026-07-27 03:17:15', '2026-07-27 03:17:15'),
(150, 15, 'DOWN', 'TIMEOUT', 0.03, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:17:15', 0, 'DOWN', '2026-07-27 03:17:15', '2026-07-27 03:17:15'),
(151, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:17:27', 0, 'DOWN', '2026-07-27 03:17:27', '2026-07-27 03:17:27'),
(152, 17, 'DOWN', 'TIMEOUT', 12.08, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:17:47', 1, 'UNKNOWN', '2026-07-27 03:17:47', '2026-07-27 03:17:47'),
(153, 1, 'UP', 'PING_OK', 0.028, 'Host merespon ping (avg: 0.028s, min: 0.027s, max: 0.028s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:18:03', 0, 'UP', '2026-07-27 03:18:03', '2026-07-27 03:18:03'),
(154, 2, 'UP', '200', 2.18, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:18:06', 0, 'UP', '2026-07-27 03:18:06', '2026-07-27 03:18:06'),
(155, 3, 'UP', '200', 0.7, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:18:06', 0, 'UP', '2026-07-27 03:18:06', '2026-07-27 03:18:06'),
(156, 4, 'UP', '200', 1.17, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:18:07', 0, 'UP', '2026-07-27 03:18:07', '2026-07-27 03:18:07'),
(157, 5, 'UP', '301', 0.23, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:18:08', 0, 'UP', '2026-07-27 03:18:08', '2026-07-27 03:18:08'),
(158, 6, 'UP', '403', 0.66, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:18:08', 0, 'UP', '2026-07-27 03:18:08', '2026-07-27 03:18:08'),
(159, 7, 'UP', '302', 0.71, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:18:09', 0, 'UP', '2026-07-27 03:18:09', '2026-07-27 03:18:09'),
(160, 8, 'UP', '200', 0.81, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:18:10', 0, 'UP', '2026-07-27 03:18:10', '2026-07-27 03:18:10'),
(161, 9, 'UP', '200', 1.34, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:18:11', 0, 'UP', '2026-07-27 03:18:11', '2026-07-27 03:18:11'),
(162, 10, 'UP', '200', 2.05, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:18:13', 0, 'UP', '2026-07-27 03:18:13', '2026-07-27 03:18:13'),
(163, 11, 'UP', '200', 0.77, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:18:14', 0, 'UP', '2026-07-27 03:18:14', '2026-07-27 03:18:14'),
(164, 12, 'UP', '200', 0.9, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:18:15', 0, 'UP', '2026-07-27 03:18:15', '2026-07-27 03:18:15'),
(165, 13, 'UP', '301', 0.07, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:18:15', 0, 'UP', '2026-07-27 03:18:15', '2026-07-27 03:18:15'),
(166, 14, 'UP', '301', 0.07, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:18:15', 0, 'UP', '2026-07-27 03:18:15', '2026-07-27 03:18:15'),
(167, 15, 'DOWN', 'TIMEOUT', 0.03, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:18:15', 0, 'DOWN', '2026-07-27 03:18:15', '2026-07-27 03:18:15'),
(168, 18, 'DOWN', 'TIMEOUT', 12.08, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:18:16', 1, 'UNKNOWN', '2026-07-27 03:18:16', '2026-07-27 03:18:16'),
(169, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:18:27', 0, 'DOWN', '2026-07-27 03:18:27', '2026-07-27 03:18:27'),
(170, 17, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:18:39', 0, 'DOWN', '2026-07-27 03:18:39', '2026-07-27 03:18:39'),
(171, 1, 'UP', 'PING_OK', 0.049, 'Host merespon ping (avg: 0.049s, min: 0.048s, max: 0.05s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:19:03', 0, 'UP', '2026-07-27 03:19:03', '2026-07-27 03:19:03'),
(172, 2, 'UP', '200', 2.96, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:19:06', 0, 'UP', '2026-07-27 03:19:06', '2026-07-27 03:19:06'),
(173, 3, 'UP', '200', 0.73, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:19:07', 0, 'UP', '2026-07-27 03:19:07', '2026-07-27 03:19:07'),
(174, 4, 'UP', '200', 1.08, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:19:08', 0, 'UP', '2026-07-27 03:19:08', '2026-07-27 03:19:08'),
(175, 5, 'UP', '301', 0.28, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:19:08', 0, 'UP', '2026-07-27 03:19:08', '2026-07-27 03:19:08'),
(176, 6, 'UP', '403', 0.71, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:19:09', 0, 'UP', '2026-07-27 03:19:09', '2026-07-27 03:19:09'),
(177, 7, 'UP', '302', 0.76, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:19:10', 0, 'UP', '2026-07-27 03:19:10', '2026-07-27 03:19:10'),
(178, 8, 'UP', '200', 0.85, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:19:11', 0, 'UP', '2026-07-27 03:19:11', '2026-07-27 03:19:11'),
(179, 9, 'UP', '200', 1.23, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:19:12', 0, 'UP', '2026-07-27 03:19:12', '2026-07-27 03:19:12'),
(180, 10, 'UP', '200', 1.87, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:19:14', 0, 'UP', '2026-07-27 03:19:14', '2026-07-27 03:19:14'),
(181, 11, 'UP', '200', 0.83, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:19:15', 0, 'UP', '2026-07-27 03:19:15', '2026-07-27 03:19:15'),
(182, 12, 'UP', '200', 0.98, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:19:16', 0, 'UP', '2026-07-27 03:19:16', '2026-07-27 03:19:16'),
(183, 13, 'UP', '301', 0.12, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:19:16', 0, 'UP', '2026-07-27 03:19:16', '2026-07-27 03:19:16'),
(184, 14, 'UP', '301', 0.1, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:19:16', 0, 'UP', '2026-07-27 03:19:16', '2026-07-27 03:19:16'),
(185, 15, 'DOWN', 'TIMEOUT', 0.03, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:19:16', 0, 'DOWN', '2026-07-27 03:19:16', '2026-07-27 03:19:16'),
(186, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:19:28', 0, 'DOWN', '2026-07-27 03:19:28', '2026-07-27 03:19:28'),
(187, 17, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:19:40', 0, 'DOWN', '2026-07-27 03:19:40', '2026-07-27 03:19:40'),
(188, 19, 'DOWN', 'TIMEOUT', 0.13, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:19:44', 1, 'UNKNOWN', '2026-07-27 03:19:44', '2026-07-27 03:19:44'),
(189, 18, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:19:52', 0, 'DOWN', '2026-07-27 03:19:52', '2026-07-27 03:19:52'),
(190, 1, 'UP', 'PING_OK', 0.028, 'Host merespon ping (avg: 0.028s, min: 0.026s, max: 0.029s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:20:03', 0, 'UP', '2026-07-27 03:20:03', '2026-07-27 03:20:03'),
(191, 2, 'UP', '200', 2.12, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:20:06', 0, 'UP', '2026-07-27 03:20:06', '2026-07-27 03:20:06'),
(192, 3, 'UP', '200', 0.69, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:20:06', 0, 'UP', '2026-07-27 03:20:06', '2026-07-27 03:20:06'),
(193, 4, 'UP', '200', 1.19, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:20:08', 0, 'UP', '2026-07-27 03:20:08', '2026-07-27 03:20:08'),
(194, 5, 'UP', '301', 0.23, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:20:08', 0, 'UP', '2026-07-27 03:20:08', '2026-07-27 03:20:08'),
(195, 6, 'UP', '403', 0.78, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:20:09', 0, 'UP', '2026-07-27 03:20:09', '2026-07-27 03:20:09'),
(196, 7, 'UP', '302', 0.72, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:20:09', 0, 'UP', '2026-07-27 03:20:09', '2026-07-27 03:20:09'),
(197, 8, 'UP', '200', 0.75, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:20:10', 0, 'UP', '2026-07-27 03:20:10', '2026-07-27 03:20:10'),
(198, 9, 'UP', '200', 1.27, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:20:11', 0, 'UP', '2026-07-27 03:20:11', '2026-07-27 03:20:11'),
(199, 10, 'UP', '200', 1.95, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:20:13', 0, 'UP', '2026-07-27 03:20:13', '2026-07-27 03:20:13'),
(200, 11, 'UP', '200', 0.81, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:20:14', 0, 'UP', '2026-07-27 03:20:14', '2026-07-27 03:20:14'),
(201, 12, 'UP', '200', 0.79, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:20:15', 0, 'UP', '2026-07-27 03:20:15', '2026-07-27 03:20:15'),
(202, 13, 'UP', '301', 0.06, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:20:15', 0, 'UP', '2026-07-27 03:20:15', '2026-07-27 03:20:15'),
(203, 14, 'UP', '301', 0.05, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:20:15', 0, 'UP', '2026-07-27 03:20:15', '2026-07-27 03:20:15'),
(204, 15, 'DOWN', 'TIMEOUT', 0.05, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:20:15', 0, 'DOWN', '2026-07-27 03:20:15', '2026-07-27 03:20:15'),
(205, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:20:27', 0, 'DOWN', '2026-07-27 03:20:27', '2026-07-27 03:20:27'),
(206, 17, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:20:39', 0, 'DOWN', '2026-07-27 03:20:39', '2026-07-27 03:20:39'),
(207, 18, 'DOWN', 'TIMEOUT', 12, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:20:51', 0, 'DOWN', '2026-07-27 03:20:51', '2026-07-27 03:20:51'),
(208, 19, 'DOWN', 'TIMEOUT', 0.04, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:20:51', 0, 'DOWN', '2026-07-27 03:20:51', '2026-07-27 03:20:51'),
(209, 1, 'UP', 'PING_OK', 0.033, 'Host merespon ping (avg: 0.033s, min: 0.03s, max: 0.036s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:21:03', 0, 'UP', '2026-07-27 03:21:03', '2026-07-27 03:21:03'),
(210, 2, 'UP', '200', 2.11, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:21:05', 0, 'UP', '2026-07-27 03:21:05', '2026-07-27 03:21:05'),
(211, 3, 'UP', '200', 0.74, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:21:06', 0, 'UP', '2026-07-27 03:21:06', '2026-07-27 03:21:06'),
(212, 4, 'UP', '200', 1.16, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:21:07', 0, 'UP', '2026-07-27 03:21:07', '2026-07-27 03:21:07'),
(213, 5, 'UP', '301', 0.24, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:21:08', 0, 'UP', '2026-07-27 03:21:08', '2026-07-27 03:21:08'),
(214, 6, 'UP', '403', 0.66, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:21:08', 0, 'UP', '2026-07-27 03:21:08', '2026-07-27 03:21:08'),
(215, 7, 'UP', '302', 0.76, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:21:09', 0, 'UP', '2026-07-27 03:21:09', '2026-07-27 03:21:09'),
(216, 8, 'UP', '200', 0.78, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:21:10', 0, 'UP', '2026-07-27 03:21:10', '2026-07-27 03:21:10'),
(217, 9, 'UP', '200', 1.23, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:21:11', 0, 'UP', '2026-07-27 03:21:11', '2026-07-27 03:21:11'),
(218, 10, 'UP', '200', 1.82, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:21:13', 0, 'UP', '2026-07-27 03:21:13', '2026-07-27 03:21:13'),
(219, 11, 'UP', '200', 0.93, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:21:14', 0, 'UP', '2026-07-27 03:21:14', '2026-07-27 03:21:14'),
(220, 12, 'UP', '200', 0.96, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:21:15', 0, 'UP', '2026-07-27 03:21:15', '2026-07-27 03:21:15'),
(221, 13, 'UP', '301', 0.08, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:21:15', 0, 'UP', '2026-07-27 03:21:15', '2026-07-27 03:21:15'),
(222, 14, 'UP', '301', 0.08, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:21:15', 0, 'UP', '2026-07-27 03:21:15', '2026-07-27 03:21:15'),
(223, 15, 'DOWN', 'TIMEOUT', 0.04, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:21:15', 0, 'DOWN', '2026-07-27 03:21:15', '2026-07-27 03:21:15'),
(224, 20, 'DOWN', 'TIMEOUT', 12.08, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:21:26', 1, 'UNKNOWN', '2026-07-27 03:21:26', '2026-07-27 03:21:26'),
(225, 16, 'DOWN', 'TIMEOUT', 12, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:21:27', 0, 'DOWN', '2026-07-27 03:21:27', '2026-07-27 03:21:27'),
(226, 17, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:21:39', 0, 'DOWN', '2026-07-27 03:21:39', '2026-07-27 03:21:39'),
(227, 18, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:21:51', 0, 'DOWN', '2026-07-27 03:21:51', '2026-07-27 03:21:51'),
(228, 19, 'DOWN', 'TIMEOUT', 0.05, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:21:51', 0, 'DOWN', '2026-07-27 03:21:51', '2026-07-27 03:21:51'),
(229, 1, 'UP', 'PING_OK', 0.028, 'Host merespon ping (avg: 0.028s, min: 0.026s, max: 0.029s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:22:03', 0, 'UP', '2026-07-27 03:22:03', '2026-07-27 03:22:03'),
(230, 2, 'UP', '200', 2.11, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:22:06', 0, 'UP', '2026-07-27 03:22:06', '2026-07-27 03:22:06'),
(231, 3, 'UP', '200', 0.77, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:22:06', 0, 'UP', '2026-07-27 03:22:06', '2026-07-27 03:22:06'),
(232, 4, 'UP', '200', 1.13, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:22:08', 0, 'UP', '2026-07-27 03:22:08', '2026-07-27 03:22:08'),
(233, 5, 'UP', '301', 0.23, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:22:08', 0, 'UP', '2026-07-27 03:22:08', '2026-07-27 03:22:08'),
(234, 6, 'UP', '403', 0.64, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:22:08', 0, 'UP', '2026-07-27 03:22:08', '2026-07-27 03:22:08'),
(235, 7, 'UP', '302', 0.7, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:22:09', 0, 'UP', '2026-07-27 03:22:09', '2026-07-27 03:22:09'),
(236, 8, 'UP', '200', 0.77, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:22:10', 0, 'UP', '2026-07-27 03:22:10', '2026-07-27 03:22:10'),
(237, 9, 'UP', '200', 1.43, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:22:11', 0, 'UP', '2026-07-27 03:22:11', '2026-07-27 03:22:11'),
(238, 10, 'UP', '200', 1.9, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:22:13', 0, 'UP', '2026-07-27 03:22:13', '2026-07-27 03:22:13'),
(239, 11, 'UP', '200', 0.79, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:22:14', 0, 'UP', '2026-07-27 03:22:14', '2026-07-27 03:22:14'),
(240, 12, 'UP', '200', 0.83, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:22:15', 0, 'UP', '2026-07-27 03:22:15', '2026-07-27 03:22:15'),
(241, 13, 'UP', '301', 0.06, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:22:15', 0, 'UP', '2026-07-27 03:22:15', '2026-07-27 03:22:15'),
(242, 14, 'UP', '301', 0.07, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:22:15', 0, 'UP', '2026-07-27 03:22:15', '2026-07-27 03:22:15'),
(243, 15, 'DOWN', 'TIMEOUT', 0.05, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:22:15', 0, 'DOWN', '2026-07-27 03:22:15', '2026-07-27 03:22:15'),
(244, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:22:27', 0, 'DOWN', '2026-07-27 03:22:27', '2026-07-27 03:22:27'),
(245, 17, 'DOWN', 'TIMEOUT', 12, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:22:39', 0, 'DOWN', '2026-07-27 03:22:39', '2026-07-27 03:22:39'),
(246, 18, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:22:51', 0, 'DOWN', '2026-07-27 03:22:51', '2026-07-27 03:22:51'),
(247, 19, 'DOWN', 'TIMEOUT', 0.03, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:22:51', 0, 'DOWN', '2026-07-27 03:22:51', '2026-07-27 03:22:51'),
(248, 1, 'UP', 'PING_OK', 0.046, 'Host merespon ping (avg: 0.046s, min: 0.043s, max: 0.049s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:23:03', 0, 'UP', '2026-07-27 03:23:03', '2026-07-27 03:23:03');
INSERT INTO `service_logs` (`id`, `service_id`, `status`, `response_code`, `response_time`, `message`, `action`, `checked_at`, `is_status_change`, `previous_status`, `created_at`, `updated_at`) VALUES
(249, 20, 'DOWN', 'TIMEOUT', 12.02, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:23:03', 0, 'DOWN', '2026-07-27 03:23:03', '2026-07-27 03:23:03'),
(250, 2, 'UP', '200', 2.13, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:23:06', 0, 'UP', '2026-07-27 03:23:06', '2026-07-27 03:23:06'),
(251, 3, 'UP', '200', 0.73, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:23:06', 0, 'UP', '2026-07-27 03:23:06', '2026-07-27 03:23:06'),
(252, 4, 'UP', '200', 1.06, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:23:07', 0, 'UP', '2026-07-27 03:23:07', '2026-07-27 03:23:07'),
(253, 5, 'UP', '301', 0.23, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:23:08', 0, 'UP', '2026-07-27 03:23:08', '2026-07-27 03:23:08'),
(254, 6, 'UP', '403', 0.65, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:23:08', 0, 'UP', '2026-07-27 03:23:08', '2026-07-27 03:23:08'),
(255, 7, 'UP', '302', 0.75, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:23:09', 0, 'UP', '2026-07-27 03:23:09', '2026-07-27 03:23:09'),
(256, 8, 'UP', '200', 0.79, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:23:10', 0, 'UP', '2026-07-27 03:23:10', '2026-07-27 03:23:10'),
(257, 9, 'UP', '200', 1.26, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:23:11', 0, 'UP', '2026-07-27 03:23:11', '2026-07-27 03:23:11'),
(258, 10, 'UP', '200', 1.9, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:23:13', 0, 'UP', '2026-07-27 03:23:13', '2026-07-27 03:23:13'),
(259, 11, 'UP', '200', 0.88, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:23:14', 0, 'UP', '2026-07-27 03:23:14', '2026-07-27 03:23:14'),
(260, 12, 'UP', '200', 0.85, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:23:15', 0, 'UP', '2026-07-27 03:23:15', '2026-07-27 03:23:15'),
(261, 13, 'UP', '301', 0.07, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:23:15', 0, 'UP', '2026-07-27 03:23:15', '2026-07-27 03:23:15'),
(262, 14, 'UP', '301', 0.06, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:23:15', 0, 'UP', '2026-07-27 03:23:15', '2026-07-27 03:23:15'),
(263, 15, 'DOWN', 'TIMEOUT', 0.05, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:23:15', 0, 'DOWN', '2026-07-27 03:23:15', '2026-07-27 03:23:15'),
(264, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:23:27', 0, 'DOWN', '2026-07-27 03:23:27', '2026-07-27 03:23:27'),
(265, 17, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:23:39', 0, 'DOWN', '2026-07-27 03:23:39', '2026-07-27 03:23:39'),
(266, 18, 'DOWN', 'TIMEOUT', 12, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:23:51', 0, 'DOWN', '2026-07-27 03:23:51', '2026-07-27 03:23:51'),
(267, 19, 'DOWN', 'TIMEOUT', 0.03, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:23:51', 0, 'DOWN', '2026-07-27 03:23:51', '2026-07-27 03:23:51'),
(268, 1, 'UP', 'PING_OK', 0.03, 'Host merespon ping (avg: 0.03s, min: 0.028s, max: 0.032s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:24:03', 0, 'UP', '2026-07-27 03:24:03', '2026-07-27 03:24:03'),
(269, 20, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:24:03', 0, 'DOWN', '2026-07-27 03:24:03', '2026-07-27 03:24:03'),
(270, 2, 'UP', '200', 2.35, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:24:06', 0, 'UP', '2026-07-27 03:24:06', '2026-07-27 03:24:06'),
(271, 3, 'UP', '200', 0.69, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:24:06', 0, 'UP', '2026-07-27 03:24:06', '2026-07-27 03:24:06'),
(272, 4, 'UP', '200', 1.23, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:24:08', 0, 'UP', '2026-07-27 03:24:08', '2026-07-27 03:24:08'),
(273, 5, 'UP', '301', 0.22, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:24:08', 0, 'UP', '2026-07-27 03:24:08', '2026-07-27 03:24:08'),
(274, 6, 'UP', '403', 0.67, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:24:08', 0, 'UP', '2026-07-27 03:24:08', '2026-07-27 03:24:08'),
(275, 7, 'UP', '302', 0.79, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:24:09', 0, 'UP', '2026-07-27 03:24:09', '2026-07-27 03:24:09'),
(276, 8, 'UP', '200', 0.87, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:24:10', 0, 'UP', '2026-07-27 03:24:10', '2026-07-27 03:24:10'),
(277, 9, 'UP', '200', 1.33, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:24:12', 0, 'UP', '2026-07-27 03:24:12', '2026-07-27 03:24:12'),
(278, 10, 'UP', '200', 1.98, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:24:14', 0, 'UP', '2026-07-27 03:24:14', '2026-07-27 03:24:14'),
(279, 11, 'UP', '200', 0.76, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:24:14', 0, 'UP', '2026-07-27 03:24:14', '2026-07-27 03:24:14'),
(280, 12, 'UP', '200', 0.93, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:24:15', 0, 'UP', '2026-07-27 03:24:15', '2026-07-27 03:24:15'),
(281, 13, 'UP', '301', 0.1, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:24:15', 0, 'UP', '2026-07-27 03:24:15', '2026-07-27 03:24:15'),
(282, 14, 'UP', '301', 0.12, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:24:16', 0, 'UP', '2026-07-27 03:24:16', '2026-07-27 03:24:16'),
(283, 15, 'DOWN', 'TIMEOUT', 0.05, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:24:16', 0, 'DOWN', '2026-07-27 03:24:16', '2026-07-27 03:24:16'),
(284, 16, 'DOWN', 'TIMEOUT', 12, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:24:28', 0, 'DOWN', '2026-07-27 03:24:28', '2026-07-27 03:24:28'),
(285, 17, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:24:40', 0, 'DOWN', '2026-07-27 03:24:40', '2026-07-27 03:24:40'),
(286, 18, 'DOWN', 'TIMEOUT', 12, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:24:52', 0, 'DOWN', '2026-07-27 03:24:52', '2026-07-27 03:24:52'),
(287, 19, 'DOWN', 'TIMEOUT', 0.04, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:24:52', 0, 'DOWN', '2026-07-27 03:24:52', '2026-07-27 03:24:52'),
(288, 1, 'UP', 'PING_OK', 0.042, 'Host merespon ping (avg: 0.042s, min: 0.028s, max: 0.056s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:25:03', 0, 'UP', '2026-07-27 03:25:03', '2026-07-27 03:25:03'),
(289, 20, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:25:04', 0, 'DOWN', '2026-07-27 03:25:04', '2026-07-27 03:25:04'),
(290, 2, 'UP', '200', 2.24, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:25:06', 0, 'UP', '2026-07-27 03:25:06', '2026-07-27 03:25:06'),
(291, 3, 'UP', '200', 0.83, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:25:06', 0, 'UP', '2026-07-27 03:25:06', '2026-07-27 03:25:06'),
(292, 4, 'UP', '200', 1.01, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:25:08', 0, 'UP', '2026-07-27 03:25:08', '2026-07-27 03:25:08'),
(293, 5, 'UP', '301', 0.26, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:25:08', 0, 'UP', '2026-07-27 03:25:08', '2026-07-27 03:25:08'),
(294, 6, 'UP', '403', 0.68, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:25:09', 0, 'UP', '2026-07-27 03:25:09', '2026-07-27 03:25:09'),
(295, 7, 'UP', '302', 0.84, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:25:09', 0, 'UP', '2026-07-27 03:25:09', '2026-07-27 03:25:09'),
(296, 8, 'UP', '200', 0.94, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:25:10', 0, 'UP', '2026-07-27 03:25:10', '2026-07-27 03:25:10'),
(297, 9, 'UP', '200', 1.26, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:25:12', 0, 'UP', '2026-07-27 03:25:12', '2026-07-27 03:25:12'),
(298, 10, 'UP', '200', 2.11, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:25:14', 0, 'UP', '2026-07-27 03:25:14', '2026-07-27 03:25:14'),
(299, 11, 'UP', '200', 1, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:25:15', 0, 'UP', '2026-07-27 03:25:15', '2026-07-27 03:25:15'),
(300, 12, 'UP', '200', 1.57, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:25:16', 0, 'UP', '2026-07-27 03:25:16', '2026-07-27 03:25:16'),
(301, 13, 'UP', '301', 0.06, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:25:16', 0, 'UP', '2026-07-27 03:25:16', '2026-07-27 03:25:16'),
(302, 14, 'UP', '301', 0.11, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:25:17', 0, 'UP', '2026-07-27 03:25:17', '2026-07-27 03:25:17'),
(303, 15, 'DOWN', 'TIMEOUT', 0.06, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:25:17', 0, 'DOWN', '2026-07-27 03:25:17', '2026-07-27 03:25:17'),
(304, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:25:29', 0, 'DOWN', '2026-07-27 03:25:29', '2026-07-27 03:25:29'),
(305, 17, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:25:41', 0, 'DOWN', '2026-07-27 03:25:41', '2026-07-27 03:25:41'),
(306, 18, 'DOWN', 'TIMEOUT', 12, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:25:53', 0, 'DOWN', '2026-07-27 03:25:53', '2026-07-27 03:25:53'),
(307, 19, 'DOWN', 'TIMEOUT', 0.03, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:25:53', 0, 'DOWN', '2026-07-27 03:25:53', '2026-07-27 03:25:53'),
(308, 1, 'UP', 'PING_OK', 0.031, 'Host merespon ping (avg: 0.031s, min: 0.028s, max: 0.033s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:26:03', 0, 'UP', '2026-07-27 03:26:03', '2026-07-27 03:26:03'),
(309, 20, 'DOWN', 'TIMEOUT', 12.02, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:26:05', 0, 'DOWN', '2026-07-27 03:26:05', '2026-07-27 03:26:05'),
(310, 2, 'UP', '200', 2.04, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:26:05', 0, 'UP', '2026-07-27 03:26:05', '2026-07-27 03:26:05'),
(311, 3, 'UP', '200', 0.95, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:26:06', 0, 'UP', '2026-07-27 03:26:06', '2026-07-27 03:26:06'),
(312, 4, 'UP', '200', 1.2, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:26:07', 0, 'UP', '2026-07-27 03:26:07', '2026-07-27 03:26:07'),
(313, 5, 'UP', '301', 0.24, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:26:08', 0, 'UP', '2026-07-27 03:26:08', '2026-07-27 03:26:08'),
(314, 6, 'UP', '403', 0.73, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:26:08', 0, 'UP', '2026-07-27 03:26:08', '2026-07-27 03:26:08'),
(315, 7, 'UP', '302', 0.96, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:26:09', 0, 'UP', '2026-07-27 03:26:09', '2026-07-27 03:26:09'),
(316, 8, 'UP', '200', 0.93, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:26:10', 0, 'UP', '2026-07-27 03:26:10', '2026-07-27 03:26:10'),
(317, 9, 'UP', '200', 1.29, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:26:12', 0, 'UP', '2026-07-27 03:26:12', '2026-07-27 03:26:12'),
(318, 10, 'UP', '200', 2.05, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:26:14', 0, 'UP', '2026-07-27 03:26:14', '2026-07-27 03:26:14'),
(319, 11, 'UP', '200', 0.91, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:26:15', 0, 'UP', '2026-07-27 03:26:15', '2026-07-27 03:26:15'),
(320, 12, 'UP', '200', 1.1, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:26:16', 0, 'UP', '2026-07-27 03:26:16', '2026-07-27 03:26:16'),
(321, 13, 'UP', '301', 0.09, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:26:16', 0, 'UP', '2026-07-27 03:26:16', '2026-07-27 03:26:16'),
(322, 14, 'UP', '301', 0.08, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:26:16', 0, 'UP', '2026-07-27 03:26:16', '2026-07-27 03:26:16'),
(323, 15, 'DOWN', 'TIMEOUT', 0.04, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:26:16', 0, 'DOWN', '2026-07-27 03:26:16', '2026-07-27 03:26:16'),
(324, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:26:28', 0, 'DOWN', '2026-07-27 03:26:28', '2026-07-27 03:26:28'),
(325, 17, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:26:40', 0, 'DOWN', '2026-07-27 03:26:40', '2026-07-27 03:26:40'),
(326, 18, 'DOWN', 'TIMEOUT', 12.02, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:26:52', 0, 'DOWN', '2026-07-27 03:26:52', '2026-07-27 03:26:52'),
(327, 19, 'DOWN', 'TIMEOUT', 0.05, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:26:52', 0, 'DOWN', '2026-07-27 03:26:52', '2026-07-27 03:26:52'),
(328, 1, 'UP', 'PING_OK', 0.051, 'Host merespon ping (avg: 0.051s, min: 0.049s, max: 0.052s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:27:03', 0, 'UP', '2026-07-27 03:27:03', '2026-07-27 03:27:03'),
(329, 20, 'DOWN', 'TIMEOUT', 12.02, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:27:04', 0, 'DOWN', '2026-07-27 03:27:04', '2026-07-27 03:27:04'),
(330, 2, 'UP', '200', 2.17, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:27:05', 0, 'UP', '2026-07-27 03:27:05', '2026-07-27 03:27:05'),
(331, 3, 'UP', '200', 0.95, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:27:06', 0, 'UP', '2026-07-27 03:27:06', '2026-07-27 03:27:06'),
(332, 4, 'UP', '200', 1.12, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:27:07', 0, 'UP', '2026-07-27 03:27:07', '2026-07-27 03:27:07'),
(333, 5, 'UP', '301', 0.23, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:27:08', 0, 'UP', '2026-07-27 03:27:08', '2026-07-27 03:27:08'),
(334, 6, 'UP', '403', 0.91, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:27:09', 0, 'UP', '2026-07-27 03:27:09', '2026-07-27 03:27:09'),
(335, 7, 'UP', '302', 0.98, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:27:10', 0, 'UP', '2026-07-27 03:27:10', '2026-07-27 03:27:10'),
(336, 8, 'UP', '200', 0.99, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:27:11', 0, 'UP', '2026-07-27 03:27:11', '2026-07-27 03:27:11'),
(337, 9, 'UP', '200', 1.18, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:27:12', 0, 'UP', '2026-07-27 03:27:12', '2026-07-27 03:27:12'),
(338, 10, 'UP', '200', 2.02, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:27:14', 0, 'UP', '2026-07-27 03:27:14', '2026-07-27 03:27:14'),
(339, 11, 'UP', '200', 0.95, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:27:15', 0, 'UP', '2026-07-27 03:27:15', '2026-07-27 03:27:15'),
(340, 12, 'UP', '200', 1.01, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:27:16', 0, 'UP', '2026-07-27 03:27:16', '2026-07-27 03:27:16'),
(341, 13, 'UP', '301', 0.07, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:27:16', 0, 'UP', '2026-07-27 03:27:16', '2026-07-27 03:27:16'),
(342, 14, 'UP', '301', 0.08, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:27:16', 0, 'UP', '2026-07-27 03:27:16', '2026-07-27 03:27:16'),
(343, 15, 'DOWN', 'TIMEOUT', 0.04, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:27:16', 0, 'DOWN', '2026-07-27 03:27:16', '2026-07-27 03:27:16'),
(344, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:27:28', 0, 'DOWN', '2026-07-27 03:27:28', '2026-07-27 03:27:28'),
(345, 17, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:27:40', 0, 'DOWN', '2026-07-27 03:27:40', '2026-07-27 03:27:40'),
(346, 18, 'DOWN', 'TIMEOUT', 12.02, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:27:52', 0, 'DOWN', '2026-07-27 03:27:52', '2026-07-27 03:27:52'),
(347, 19, 'DOWN', 'TIMEOUT', 0.07, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:27:53', 0, 'DOWN', '2026-07-27 03:27:53', '2026-07-27 03:27:53'),
(348, 1, 'UP', 'PING_OK', 0.059, 'Host merespon ping (avg: 0.059s, min: 0.057s, max: 0.06s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:28:03', 0, 'UP', '2026-07-27 03:28:03', '2026-07-27 03:28:03'),
(349, 20, 'DOWN', 'TIMEOUT', 12, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:28:05', 0, 'DOWN', '2026-07-27 03:28:05', '2026-07-27 03:28:05'),
(350, 2, 'UP', '200', 2.28, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:28:06', 0, 'UP', '2026-07-27 03:28:06', '2026-07-27 03:28:06'),
(351, 3, 'UP', '200', 1, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:28:07', 0, 'UP', '2026-07-27 03:28:07', '2026-07-27 03:28:07'),
(352, 4, 'UP', '200', 1.27, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:28:08', 0, 'UP', '2026-07-27 03:28:08', '2026-07-27 03:28:08'),
(353, 5, 'UP', '301', 0.23, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:28:08', 0, 'UP', '2026-07-27 03:28:08', '2026-07-27 03:28:08'),
(354, 6, 'UP', '403', 0.75, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:28:09', 0, 'UP', '2026-07-27 03:28:09', '2026-07-27 03:28:09'),
(355, 7, 'UP', '302', 0.8, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:28:10', 0, 'UP', '2026-07-27 03:28:10', '2026-07-27 03:28:10'),
(356, 8, 'UP', '200', 0.75, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:28:10', 0, 'UP', '2026-07-27 03:28:10', '2026-07-27 03:28:10'),
(357, 9, 'UP', '200', 1.54, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:28:12', 0, 'UP', '2026-07-27 03:28:12', '2026-07-27 03:28:12'),
(358, 10, 'UP', '200', 4.78, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:28:17', 0, 'UP', '2026-07-27 03:28:17', '2026-07-27 03:28:17'),
(359, 11, 'UP', '200', 0.76, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:28:18', 0, 'UP', '2026-07-27 03:28:18', '2026-07-27 03:28:18'),
(360, 12, 'UP', '200', 0.97, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:28:19', 0, 'UP', '2026-07-27 03:28:19', '2026-07-27 03:28:19'),
(361, 13, 'UP', '301', 0.09, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:28:19', 0, 'UP', '2026-07-27 03:28:19', '2026-07-27 03:28:19'),
(362, 14, 'UP', '301', 0.11, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:28:19', 0, 'UP', '2026-07-27 03:28:19', '2026-07-27 03:28:19'),
(363, 15, 'DOWN', 'TIMEOUT', 0.04, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:28:19', 0, 'DOWN', '2026-07-27 03:28:19', '2026-07-27 03:28:19'),
(364, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:28:31', 0, 'DOWN', '2026-07-27 03:28:31', '2026-07-27 03:28:31'),
(365, 17, 'DOWN', 'TIMEOUT', 12, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:28:43', 0, 'DOWN', '2026-07-27 03:28:43', '2026-07-27 03:28:43'),
(366, 18, 'DOWN', 'TIMEOUT', 12.02, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:28:55', 0, 'DOWN', '2026-07-27 03:28:55', '2026-07-27 03:28:55'),
(367, 19, 'DOWN', 'TIMEOUT', 0.09, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:28:55', 0, 'DOWN', '2026-07-27 03:28:55', '2026-07-27 03:28:55'),
(368, 1, 'UP', 'PING_OK', 0.066, 'Host merespon ping (avg: 0.066s, min: 0.027s, max: 0.104s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:29:03', 0, 'UP', '2026-07-27 03:29:03', '2026-07-27 03:29:03'),
(369, 2, 'UP', '200', 2.29, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:29:05', 0, 'UP', '2026-07-27 03:29:05', '2026-07-27 03:29:05'),
(370, 3, 'UP', '200', 0.95, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:29:06', 0, 'UP', '2026-07-27 03:29:06', '2026-07-27 03:29:06'),
(371, 20, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:29:07', 0, 'DOWN', '2026-07-27 03:29:07', '2026-07-27 03:29:07'),
(372, 4, 'UP', '200', 1.15, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:29:08', 0, 'UP', '2026-07-27 03:29:08', '2026-07-27 03:29:08'),
(373, 5, 'UP', '301', 0.22, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:29:08', 0, 'UP', '2026-07-27 03:29:08', '2026-07-27 03:29:08'),
(374, 6, 'UP', '403', 0.6, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:29:08', 0, 'UP', '2026-07-27 03:29:08', '2026-07-27 03:29:08'),
(375, 7, 'UP', '302', 0.76, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:29:09', 0, 'UP', '2026-07-27 03:29:09', '2026-07-27 03:29:09'),
(376, 8, 'UP', '200', 0.76, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:29:10', 0, 'UP', '2026-07-27 03:29:10', '2026-07-27 03:29:10'),
(377, 9, 'UP', '200', 1.38, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:29:11', 0, 'UP', '2026-07-27 03:29:11', '2026-07-27 03:29:11'),
(378, 10, 'UP', '200', 1.85, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:29:13', 0, 'UP', '2026-07-27 03:29:13', '2026-07-27 03:29:13'),
(379, 11, 'UP', '200', 1.19, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:29:14', 0, 'UP', '2026-07-27 03:29:14', '2026-07-27 03:29:14'),
(380, 12, 'UP', '200', 0.83, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:29:15', 0, 'UP', '2026-07-27 03:29:15', '2026-07-27 03:29:15'),
(381, 13, 'UP', '301', 0.1, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:29:15', 0, 'UP', '2026-07-27 03:29:15', '2026-07-27 03:29:15'),
(382, 14, 'UP', '301', 0.09, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:29:16', 0, 'UP', '2026-07-27 03:29:16', '2026-07-27 03:29:16'),
(383, 15, 'DOWN', 'TIMEOUT', 0.04, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:29:16', 0, 'DOWN', '2026-07-27 03:29:16', '2026-07-27 03:29:16'),
(384, 16, 'DOWN', 'TIMEOUT', 12.02, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:29:28', 0, 'DOWN', '2026-07-27 03:29:28', '2026-07-27 03:29:28'),
(385, 17, 'DOWN', 'TIMEOUT', 12.02, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:29:40', 0, 'DOWN', '2026-07-27 03:29:40', '2026-07-27 03:29:40'),
(386, 18, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:29:52', 0, 'DOWN', '2026-07-27 03:29:52', '2026-07-27 03:29:52'),
(387, 19, 'DOWN', 'TIMEOUT', 0.05, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:29:52', 0, 'DOWN', '2026-07-27 03:29:52', '2026-07-27 03:29:52'),
(388, 1, 'UP', 'PING_OK', 0.064, 'Host merespon ping (avg: 0.064s, min: 0.048s, max: 0.08s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:30:03', 0, 'UP', '2026-07-27 03:30:03', '2026-07-27 03:30:03'),
(389, 20, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:30:04', 0, 'DOWN', '2026-07-27 03:30:04', '2026-07-27 03:30:04'),
(390, 2, 'UP', '200', 2.21, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:30:06', 0, 'UP', '2026-07-27 03:30:06', '2026-07-27 03:30:06'),
(391, 3, 'UP', '200', 1.01, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:30:07', 0, 'UP', '2026-07-27 03:30:07', '2026-07-27 03:30:07'),
(392, 4, 'UP', '200', 1.1, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:30:08', 0, 'UP', '2026-07-27 03:30:08', '2026-07-27 03:30:08'),
(393, 5, 'UP', '301', 0.06, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:30:08', 0, 'UP', '2026-07-27 03:30:08', '2026-07-27 03:30:08'),
(394, 6, 'UP', '403', 0.91, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:30:09', 0, 'UP', '2026-07-27 03:30:09', '2026-07-27 03:30:09'),
(395, 7, 'UP', '302', 0.95, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:30:10', 0, 'UP', '2026-07-27 03:30:10', '2026-07-27 03:30:10'),
(396, 8, 'UP', '200', 0.73, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:30:10', 0, 'UP', '2026-07-27 03:30:10', '2026-07-27 03:30:10'),
(397, 9, 'UP', '200', 1.29, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:30:12', 0, 'UP', '2026-07-27 03:30:12', '2026-07-27 03:30:12'),
(398, 10, 'UP', '200', 1.96, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:30:14', 0, 'UP', '2026-07-27 03:30:14', '2026-07-27 03:30:14'),
(399, 11, 'UP', '200', 1, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:30:15', 0, 'UP', '2026-07-27 03:30:15', '2026-07-27 03:30:15'),
(400, 12, 'UP', '200', 0.9, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:30:16', 0, 'UP', '2026-07-27 03:30:16', '2026-07-27 03:30:16'),
(401, 13, 'UP', '301', 0.06, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:30:16', 0, 'UP', '2026-07-27 03:30:16', '2026-07-27 03:30:16'),
(402, 14, 'UP', '301', 0.06, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:30:16', 0, 'UP', '2026-07-27 03:30:16', '2026-07-27 03:30:16'),
(403, 15, 'DOWN', 'TIMEOUT', 0.04, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:30:16', 0, 'DOWN', '2026-07-27 03:30:16', '2026-07-27 03:30:16'),
(404, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:30:28', 0, 'DOWN', '2026-07-27 03:30:28', '2026-07-27 03:30:28'),
(405, 17, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:30:40', 0, 'DOWN', '2026-07-27 03:30:40', '2026-07-27 03:30:40'),
(406, 18, 'DOWN', 'TIMEOUT', 12.02, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:30:52', 0, 'DOWN', '2026-07-27 03:30:52', '2026-07-27 03:30:52'),
(407, 19, 'DOWN', 'TIMEOUT', 0.06, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:30:52', 0, 'DOWN', '2026-07-27 03:30:52', '2026-07-27 03:30:52'),
(408, 1, 'UP', 'PING_OK', 0.036, 'Host merespon ping (avg: 0.036s, min: 0.027s, max: 0.044s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:31:03', 0, 'UP', '2026-07-27 03:31:03', '2026-07-27 03:31:03'),
(409, 20, 'DOWN', 'TIMEOUT', 12.02, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:31:04', 0, 'DOWN', '2026-07-27 03:31:04', '2026-07-27 03:31:04'),
(410, 2, 'UP', '200', 2.24, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:31:05', 0, 'UP', '2026-07-27 03:31:05', '2026-07-27 03:31:05'),
(411, 3, 'UP', '200', 0.91, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:31:06', 0, 'UP', '2026-07-27 03:31:06', '2026-07-27 03:31:06'),
(412, 4, 'UP', '200', 1.06, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:31:07', 0, 'UP', '2026-07-27 03:31:07', '2026-07-27 03:31:07'),
(413, 5, 'UP', '301', 0.04, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:31:08', 0, 'UP', '2026-07-27 03:31:08', '2026-07-27 03:31:08'),
(414, 6, 'UP', '403', 0.65, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:31:08', 0, 'UP', '2026-07-27 03:31:08', '2026-07-27 03:31:08'),
(415, 7, 'UP', '302', 0.86, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:31:09', 0, 'UP', '2026-07-27 03:31:09', '2026-07-27 03:31:09'),
(416, 8, 'UP', '200', 0.97, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:31:10', 0, 'UP', '2026-07-27 03:31:10', '2026-07-27 03:31:10'),
(417, 9, 'UP', '200', 1.24, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:31:11', 0, 'UP', '2026-07-27 03:31:11', '2026-07-27 03:31:11'),
(418, 10, 'UP', '200', 2.08, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:31:13', 0, 'UP', '2026-07-27 03:31:13', '2026-07-27 03:31:13'),
(419, 11, 'UP', '200', 0.86, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:31:14', 0, 'UP', '2026-07-27 03:31:14', '2026-07-27 03:31:14'),
(420, 12, 'UP', '200', 0.97, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:31:15', 0, 'UP', '2026-07-27 03:31:15', '2026-07-27 03:31:15'),
(421, 13, 'UP', '301', 0.08, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:31:15', 0, 'UP', '2026-07-27 03:31:15', '2026-07-27 03:31:15'),
(422, 14, 'UP', '301', 0.12, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:31:16', 0, 'UP', '2026-07-27 03:31:16', '2026-07-27 03:31:16'),
(423, 15, 'DOWN', 'TIMEOUT', 0.05, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:31:16', 0, 'DOWN', '2026-07-27 03:31:16', '2026-07-27 03:31:16'),
(424, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:31:28', 0, 'DOWN', '2026-07-27 03:31:28', '2026-07-27 03:31:28'),
(425, 17, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:31:40', 0, 'DOWN', '2026-07-27 03:31:40', '2026-07-27 03:31:40'),
(426, 18, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:31:52', 0, 'DOWN', '2026-07-27 03:31:52', '2026-07-27 03:31:52'),
(427, 19, 'DOWN', 'TIMEOUT', 0.03, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:31:52', 0, 'DOWN', '2026-07-27 03:31:52', '2026-07-27 03:31:52'),
(428, 1, 'UP', 'PING_OK', 0.048, 'Host merespon ping (avg: 0.048s, min: 0.046s, max: 0.049s)', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:32:03', 0, 'UP', '2026-07-27 03:32:03', '2026-07-27 03:32:03'),
(429, 20, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:32:04', 0, 'DOWN', '2026-07-27 03:32:04', '2026-07-27 03:32:04'),
(430, 2, 'UP', '200', 2.22, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:32:05', 0, 'UP', '2026-07-27 03:32:05', '2026-07-27 03:32:05'),
(431, 3, 'UP', '200', 1.06, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:32:06', 0, 'UP', '2026-07-27 03:32:06', '2026-07-27 03:32:06'),
(432, 4, 'UP', '200', 1.08, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:32:08', 0, 'UP', '2026-07-27 03:32:08', '2026-07-27 03:32:08'),
(433, 5, 'UP', '301', 0.02, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:32:08', 0, 'UP', '2026-07-27 03:32:08', '2026-07-27 03:32:08'),
(434, 6, 'UP', '403', 0.83, 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'Periksa izin akses pengguna', '2026-07-27 03:32:08', 0, 'UP', '2026-07-27 03:32:08', '2026-07-27 03:32:08'),
(435, 7, 'UP', '302', 0.9, 'Redirect sementara', 'Periksa redirect jika mengganggu akses', '2026-07-27 03:32:09', 0, 'UP', '2026-07-27 03:32:09', '2026-07-27 03:32:09'),
(436, 8, 'UP', '200', 0.93, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:32:10', 0, 'UP', '2026-07-27 03:32:10', '2026-07-27 03:32:10'),
(437, 9, 'UP', '200', 1.26, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:32:12', 0, 'UP', '2026-07-27 03:32:12', '2026-07-27 03:32:12'),
(438, 10, 'UP', '200', 2.06, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:32:14', 0, 'UP', '2026-07-27 03:32:14', '2026-07-27 03:32:14'),
(439, 11, 'UP', '200', 0.84, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:32:15', 0, 'UP', '2026-07-27 03:32:15', '2026-07-27 03:32:15'),
(440, 12, 'UP', '200', 1.03, 'Service berjalan normal - Pengguna bisa akses', 'Service dalam kondisi baik, tidak perlu tindakan', '2026-07-27 03:32:16', 0, 'UP', '2026-07-27 03:32:16', '2026-07-27 03:32:16'),
(441, 13, 'UP', '301', 0.07, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:32:16', 0, 'UP', '2026-07-27 03:32:16', '2026-07-27 03:32:16'),
(442, 14, 'UP', '301', 0.07, 'Redirect permanen', 'Update URL endpoint (redirect permanen)', '2026-07-27 03:32:16', 0, 'UP', '2026-07-27 03:32:16', '2026-07-27 03:32:16'),
(443, 15, 'DOWN', 'TIMEOUT', 0.04, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:32:16', 0, 'DOWN', '2026-07-27 03:32:16', '2026-07-27 03:32:16'),
(444, 16, 'DOWN', 'TIMEOUT', 12.01, 'Koneksi timeout - Pengguna tidak bisa akses', 'Periksa firewall dan pastikan server menyala', '2026-07-27 03:32:28', 0, 'DOWN', '2026-07-27 03:32:28', '2026-07-27 03:32:28');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('exbOi35ddBfIjbWyQZBmaR6wX40Qm4faiPHCTECK', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJXakdVUHVpTjVleGJFeEJ5dkEyM3QybUM0S3p2cFJxc3MwNU85Qk9wIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJkYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6Mn0=', 1785120621),
('sbu4PnDSyaACXDCqByl2Vg9q3f6NtRQ0SzWJSOXL', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJCaEdTUFAwdjNpSm1tOUZITGtHdjFTbGZRakx2MVRDME9VY0NYeFoyIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zbW9rZS1kZXRlY3RvciIsInJvdXRlIjoic21va2UifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6Miwid2FfaW50ZXJ2YWwiOiIzMCJ9', 1785122639);

-- --------------------------------------------------------

--
-- Table structure for table `smoke_devices`
--

CREATE TABLE `smoke_devices` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `threshold` int NOT NULL DEFAULT '400',
  `smoke_value` double NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NORMAL',
  `device_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OFFLINE',
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_status_notified` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NORMAL'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `smoke_devices`
--

INSERT INTO `smoke_devices` (`id`, `name`, `location`, `threshold`, `smoke_value`, `status`, `device_status`, `last_seen_at`, `is_active`, `created_at`, `updated_at`, `last_status_notified`) VALUES
(1, 'ESP32-Smoke', 'Ruang Server', 400, 502, 'NORMAL', 'ONLINE', '2026-07-27 03:24:58', 1, '2026-07-27 02:41:28', '2026-07-27 03:24:58', 'NORMAL');

-- --------------------------------------------------------

--
-- Table structure for table `smoke_logs`
--

CREATE TABLE `smoke_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `smoke_device_id` bigint UNSIGNED NOT NULL,
  `smoke_value` int NOT NULL,
  `status` enum('NORMAL','WARNING','DANGER') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NORMAL',
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `smoke_logs`
--

INSERT INTO `smoke_logs` (`id`, `smoke_device_id`, `smoke_value`, `status`, `message`, `created_at`, `updated_at`) VALUES
(1, 1, 701, 'WARNING', '⚠️ Asap terdeteksi! Nilai Asap: 701', '2026-07-27 02:41:28', '2026-07-27 02:41:28'),
(2, 1, 1000, 'DANGER', '🔥 Asap tinggi!: 1000', '2026-07-27 02:43:03', '2026-07-27 02:43:03'),
(3, 1, 502, 'NORMAL', '✅ Kondisi aman | Nilai Asap: 500', '2026-07-27 03:24:51', '2026-07-27 03:24:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Administrator', 'admin@monitoring.com', NULL, '$2y$12$gTyOpIvSuGTkMg0KRiBpEOuMuaiPML7BMCm55U57Cbv55dpqND3yC', NULL, '2026-07-26 13:44:43', '2026-07-26 13:44:43'),
(2, 'jusavocad', 'jusavocad', 'jusavocad@gmail.com', NULL, '$2y$12$1Z0KInHGv1gVZKW5BTf3bOBBxPYuxNVNP.CgDybjKnDF09.DKbp5m', NULL, '2026-07-26 13:45:11', '2026-07-26 13:45:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_logs`
--
ALTER TABLE `service_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_logs_is_status_change_index` (`is_status_change`),
  ADD KEY `service_logs_service_id_is_status_change_index` (`service_id`,`is_status_change`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `smoke_devices`
--
ALTER TABLE `smoke_devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `smoke_logs`
--
ALTER TABLE `smoke_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `smoke_logs_smoke_device_id_foreign` (`smoke_device_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `service_logs`
--
ALTER TABLE `service_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=445;

--
-- AUTO_INCREMENT for table `smoke_devices`
--
ALTER TABLE `smoke_devices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `smoke_logs`
--
ALTER TABLE `smoke_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `service_logs`
--
ALTER TABLE `service_logs`
  ADD CONSTRAINT `service_logs_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `smoke_logs`
--
ALTER TABLE `smoke_logs`
  ADD CONSTRAINT `smoke_logs_smoke_device_id_foreign` FOREIGN KEY (`smoke_device_id`) REFERENCES `smoke_devices` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
