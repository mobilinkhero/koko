-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 14, 2025 at 08:55 AM
-- Server version: 10.11.15-MariaDB
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ahtisham_marksass`
--

-- --------------------------------------------------------

--
-- Table structure for table `abc_chats`
--

CREATE TABLE `abc_chats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Reference to tenant',
  `name` varchar(100) NOT NULL,
  `receiver_id` varchar(20) NOT NULL,
  `last_message` text DEFAULT NULL,
  `last_msg_time` datetime DEFAULT NULL,
  `wa_no` varchar(20) DEFAULT NULL,
  `wa_no_id` varchar(20) DEFAULT NULL,
  `time_sent` datetime NOT NULL,
  `type` varchar(500) DEFAULT NULL,
  `type_id` varchar(500) DEFAULT NULL,
  `agent` text DEFAULT NULL,
  `is_ai_chat` tinyint(1) NOT NULL DEFAULT 0,
  `ai_message_json` text DEFAULT NULL,
  `is_bots_stoped` tinyint(1) DEFAULT NULL,
  `bot_stoped_time` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `abc_chats`
--

INSERT INTO `abc_chats` (`id`, `tenant_id`, `name`, `receiver_id`, `last_message`, `last_msg_time`, `wa_no`, `wa_no_id`, `time_sent`, `type`, `type_id`, `agent`, `is_ai_chat`, `ai_message_json`, `is_bots_stoped`, `bot_stoped_time`, `created_at`, `updated_at`) VALUES
(4, 1, 'Hoxway', '923306055177', 'test is working', '2025-11-12 09:04:15', '923324538746', '717118924823378', '2025-11-12 09:04:13', 'lead', '1', '{\"assign_id\": null, \"agents_id\": \"\"}', 0, NULL, NULL, NULL, '2025-11-12 04:05:14', '2025-11-14 13:00:48');

-- --------------------------------------------------------

--
-- Table structure for table `abc_chat_messages`
--

CREATE TABLE `abc_chat_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Reference to tenant',
  `interaction_id` int(10) UNSIGNED NOT NULL,
  `sender_id` varchar(20) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `message` longtext NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `status_message` text DEFAULT NULL,
  `time_sent` datetime NOT NULL,
  `message_id` varchar(500) DEFAULT NULL,
  `staff_id` varchar(500) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `ref_message_id` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `abc_chat_messages`
--

INSERT INTO `abc_chat_messages` (`id`, `tenant_id`, `interaction_id`, `sender_id`, `url`, `message`, `status`, `status_message`, `time_sent`, `message_id`, `staff_id`, `type`, `is_read`, `ref_message_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '923306055177', '', 'hi\'', 'sent', NULL, '2025-11-11 22:57:53', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhgUM0ZBNUREQTVBMUJFQkQ5M0I1RjcA', NULL, 'text', 1, '', '2025-11-12 03:57:53', '2025-11-12 04:00:11'),
(2, 1, 1, '923324538746', NULL, 'how are you', 'sent', NULL, '2025-11-11 22:58:08', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAERgSRTc0QzBBQUNFOUJDQzZDQ0Q5AA==', '2', 'text', 1, '', '2025-11-12 03:58:08', '2025-11-12 04:00:11'),
(3, 1, 2, '923306055177', '', 'hi', 'sent', NULL, '2025-11-11 23:01:44', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhgUM0YwOUIxREMyRjRGNDQxMzAzMTUA', NULL, 'text', 1, '', '2025-11-12 04:01:44', '2025-11-12 04:03:04'),
(4, 1, 2, '923306055177', '', 'hi', 'sent', NULL, '2025-11-11 23:01:47', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhgUM0Y2REY0MDlGQzcyQ0I5MzRERTIA', NULL, 'text', 1, '', '2025-11-12 04:01:47', '2025-11-12 04:03:04'),
(5, 1, 2, '923306055177', '', 'g', 'sent', NULL, '2025-11-11 23:02:05', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhgUM0ZDQ0RDN0Q2MjIzNDA3Q0ExMzYA', NULL, 'text', 1, '', '2025-11-12 04:02:05', '2025-11-12 04:03:04'),
(6, 1, 2, '923306055177', '', 'kaisy ho ap', 'sent', NULL, '2025-11-11 23:02:15', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhgUM0Y0MDdBNkMxQUVFMDZFMkQwOTIA', NULL, 'text', 1, '', '2025-11-12 04:02:15', '2025-11-12 04:03:04'),
(7, 1, 2, '923324538746', NULL, '{company_email} yes', 'delivered', NULL, '2025-11-11 23:02:33', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAERgSRjA5MkQxM0U0NUJDNDBDNTNDAA==', '2', 'text', 1, '', '2025-11-12 04:02:33', '2025-11-12 04:03:04'),
(8, 1, 3, '923306055177', '', 'hi', 'sent', NULL, '2025-11-11 23:03:10', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhgUM0ZFNDY5MzNERTYzQUU4RUUzRDMA', NULL, 'text', 1, '', '2025-11-12 04:03:10', '2025-11-12 04:04:37'),
(9, 1, 4, '923306055177', '', '.', 'sent', NULL, '2025-11-11 23:05:14', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhgUM0YzM0FDRkQ3RTY4Qjc4QjUwOTAA', NULL, 'text', 1, '', '2025-11-12 04:05:14', '2025-11-14 13:00:55'),
(10, 1, 4, '923306055177', '', 'test', 'sent', NULL, '2025-11-11 23:09:41', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhgUM0Y4MDAwMENGM0ZFMzk2MTAxMDIA', NULL, 'text', 1, '', '2025-11-12 04:09:41', '2025-11-14 13:00:55'),
(11, 1, 4, '923306055177', '', 'test', 'sent', NULL, '2025-11-11 23:10:34', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhgUM0Y5OTdBNTE5MTg1MEU1NzJDOTMA', NULL, 'text', 1, '', '2025-11-12 04:10:34', '2025-11-14 13:00:55'),
(12, 1, 4, '923306055177', '', 'hi', 'sent', NULL, '2025-11-11 23:16:07', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhgUM0YyNjU3OUY0RjAwNTI1MzhGNTQA', NULL, 'text', 1, '', '2025-11-12 04:16:07', '2025-11-14 13:00:55'),
(13, 1, 4, '923306055177', '', 'test', 'sent', NULL, '2025-11-12 00:24:48', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhggQTU5QTU3MTgzNkUyMkNDNDI4N0JENzAxQzYzNkREQjEA', NULL, 'text', 1, '', '2025-11-12 05:24:48', '2025-11-14 13:00:55'),
(14, 1, 4, '923324538746', NULL, '<p>test is working</p>', 'delivered', NULL, '2025-11-12 00:24:50', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAERgSMUIzMEQ5NkU0Mjg3NDg2QUVBAA==', '0', 'text', 1, NULL, NULL, '2025-11-14 13:00:55'),
(15, 1, 4, '923306055177', '', 'okay test', 'sent', NULL, '2025-11-12 00:25:07', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhggQTVEN0VCNzZFNUVGOEVCQzVFRjA1MzI0RkZFMkRDODkA', NULL, 'text', 1, '', '2025-11-12 05:25:07', '2025-11-14 13:00:55'),
(16, 1, 4, '923306055177', '', 'test', 'sent', NULL, '2025-11-12 00:25:13', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhggQTUxMDUwNTAwRjEzRTVEODc1OUYxNzVDOTlDQUM1QTAA', NULL, 'text', 1, '', '2025-11-12 05:25:13', '2025-11-14 13:00:55'),
(17, 1, 4, '923324538746', NULL, '<p>test is working</p>', 'delivered', NULL, '2025-11-12 00:25:15', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAERgSMjNFQUEzOTk4OTk1REE3QTE1AA==', '0', 'text', 1, NULL, NULL, '2025-11-14 13:00:55'),
(18, 1, 4, '923306055177', '', 'test', 'sent', NULL, '2025-11-12 02:59:20', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhggQTVDRTAwNThCQ0MzNzIwMDU2Q0VGNkQxRjVENjdDNDAA', NULL, 'text', 1, '', '2025-11-12 07:59:20', '2025-11-14 13:00:55'),
(19, 1, 4, '923324538746', NULL, '<p>test is working</p>', 'delivered', NULL, '2025-11-12 02:59:22', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAERgSNzA3NTUzNDA0RDVGQTk0QkQ2AA==', '0', 'text', 1, NULL, NULL, '2025-11-14 13:00:55'),
(20, 1, 4, '923306055177', '', 'test', 'sent', NULL, '2025-11-12 09:04:13', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAEhggQTUzREJCNDNENTBDMUFGOTRFMDIxRDUxQjZFMkVDRTEA', NULL, 'text', 1, '', '2025-11-12 14:04:13', '2025-11-14 13:00:55'),
(21, 1, 4, '923324538746', NULL, '<p>test is working</p>', 'delivered', NULL, '2025-11-12 09:04:15', 'wamid.HBgMOTIzMzA2MDU1MTc3FQIAERgSQzIwMzA0MUUxN0ZCRUQ3OTMyAA==', '0', 'text', 1, NULL, NULL, '2025-11-14 13:00:55');

-- --------------------------------------------------------

--
-- Table structure for table `abc_contacts`
--

CREATE TABLE `abc_contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Reference to tenant',
  `firstname` varchar(191) NOT NULL,
  `lastname` varchar(191) NOT NULL,
  `company` varchar(191) DEFAULT NULL,
  `type` enum('lead','customer') NOT NULL DEFAULT 'lead',
  `description` text DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `zip` varchar(15) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `assigned_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED NOT NULL,
  `source_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `addedfrom` int(11) DEFAULT NULL,
  `custom_fields_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields_data`)),
  `dateassigned` datetime DEFAULT NULL,
  `last_status_change` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `group_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`group_id`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `abc_contacts`
--

INSERT INTO `abc_contacts` (`id`, `tenant_id`, `firstname`, `lastname`, `company`, `type`, `description`, `country_id`, `zip`, `city`, `state`, `address`, `assigned_id`, `status_id`, `source_id`, `email`, `website`, `phone`, `is_enabled`, `addedfrom`, `custom_fields_data`, `dateassigned`, `last_status_change`, `created_at`, `updated_at`, `group_id`) VALUES
(1, 1, 'Tracerpk', '', NULL, 'lead', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, '+923306055177', 1, 0, NULL, NULL, NULL, '2025-11-12 04:16:07', '2025-11-12 04:16:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `abc_contact_notes`
--

CREATE TABLE `abc_contact_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Reference to tenant',
  `contact_id` bigint(20) UNSIGNED NOT NULL,
  `notes_description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_prompts`
--

CREATE TABLE `ai_prompts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bot_flows`
--

CREATE TABLE `bot_flows` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `flow_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`flow_data`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bot_flows`
--

INSERT INTO `bot_flows` (`id`, `tenant_id`, `name`, `description`, `flow_data`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'A', 'Abc', '{\"nodes\":[{\"id\":\"trigger-1\",\"type\":\"trigger\",\"draggable\":true,\"initialized\":false,\"position\":{\"x\":94,\"y\":115},\"data\":{\"label\":\"Start Trigger\",\"keywords\":[],\"dimensions\":{\"width\":280,\"height\":150},\"isValid\":true,\"output\":[{\"reply_type_text\":\"When lead or client send the first message\",\"reply_type\":\"3\",\"rel_type\":\"customer\",\"trigger\":\"\"}]}},{\"id\":\"1762902001434\",\"type\":\"textMessage\",\"initialized\":false,\"position\":{\"x\":676,\"y\":402},\"data\":{\"output\":[{\"reply_text\":\"How are you sir\"}],\"isValid\":true,\"errorMessage\":\"\",\"dimensions\":{\"width\":280,\"height\":150}}}],\"edges\":[{\"id\":\"vueflow__edge-trigger-1-1762902001434\",\"type\":\"button\",\"source\":\"trigger-1\",\"target\":\"1762902001434\",\"sourceHandle\":null,\"targetHandle\":null,\"data\":{},\"label\":\"\",\"animated\":true,\"sourceX\":482,\"sourceY\":388.390625,\"targetX\":668,\"targetY\":529}],\"position\":[0,0],\"zoom\":1,\"viewport\":{\"x\":0,\"y\":0,\"zoom\":1}}', 1, '2025-11-12 03:40:33', '2025-11-12 04:02:53'),
(2, 1, 'test', 'test', '{\"nodes\":[{\"id\":\"trigger-1\",\"type\":\"trigger\",\"draggable\":true,\"initialized\":false,\"position\":{\"x\":169.39404296875,\"y\":120.82901000976562},\"data\":{\"label\":\"Start Trigger\",\"keywords\":[],\"dimensions\":{\"width\":280,\"height\":150},\"isValid\":true,\"output\":[{\"reply_type_text\":\"On exact match\",\"reply_type\":\"1\",\"rel_type\":\"customer\",\"trigger\":\"test\"}]}},{\"id\":\"1762902567845\",\"type\":\"textMessage\",\"initialized\":false,\"position\":{\"x\":814,\"y\":318},\"data\":{\"output\":[{\"reply_text\":\"test is working\"}],\"isValid\":true,\"errorMessage\":\"\",\"dimensions\":{\"width\":280,\"height\":150}}}],\"edges\":[{\"id\":\"vueflow__edge-trigger-1-1762902567845\",\"type\":\"button\",\"source\":\"trigger-1\",\"target\":\"1762902567845\",\"sourceHandle\":null,\"targetHandle\":null,\"data\":{},\"label\":\"\",\"animated\":true,\"sourceX\":557.39404296875,\"sourceY\":367.427978515625,\"targetX\":806,\"targetY\":445}],\"position\":[-213.04351806640625,187.757080078125],\"zoom\":1,\"viewport\":{\"x\":-213.04351806640625,\"y\":187.757080078125,\"zoom\":1}}', 1, '2025-11-12 04:09:02', '2025-11-14 12:52:52');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Reference to tenant',
  `name` varchar(100) NOT NULL,
  `rel_type` varchar(50) NOT NULL,
  `template_id` text DEFAULT NULL,
  `scheduled_send_time` timestamp NULL DEFAULT NULL,
  `send_now` tinyint(1) NOT NULL DEFAULT 0,
  `header_params` text DEFAULT NULL,
  `body_params` text DEFAULT NULL,
  `footer_params` text DEFAULT NULL,
  `pause_campaign` tinyint(1) NOT NULL DEFAULT 0,
  `select_all` tinyint(1) NOT NULL DEFAULT 0,
  `is_sent` tinyint(1) NOT NULL DEFAULT 0,
  `sending_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `filename` text DEFAULT NULL,
  `rel_data` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `campaign_details`
--

CREATE TABLE `campaign_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Reference to tenant',
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `rel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rel_type` varchar(50) NOT NULL,
  `header_message` text DEFAULT NULL,
  `body_message` text DEFAULT NULL,
  `footer_message` text DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `response_message` text DEFAULT NULL,
  `whatsapp_id` text DEFAULT NULL,
  `message_status` varchar(25) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `canned_replies`
--

CREATE TABLE `canned_replies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Reference to tenant',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `added_from` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_imports`
--

CREATE TABLE `contact_imports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `total_records` int(11) NOT NULL DEFAULT 0,
  `processed_records` int(11) NOT NULL DEFAULT 0,
  `valid_records` int(11) NOT NULL DEFAULT 0,
  `invalid_records` int(11) NOT NULL DEFAULT 0,
  `skipped_records` int(11) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL,
  `error_messages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`error_messages`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('percentage','fixed_amount') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `usage_limit_per_customer` int(11) DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `minimum_amount` decimal(10,2) DEFAULT NULL,
  `maximum_discount` decimal(10,2) DEFAULT NULL,
  `applicable_plans` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_plans`)),
  `applicable_billing_periods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_billing_periods`)),
  `first_payment_only` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usages`
--

CREATE TABLE `coupon_usages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coupon_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subscription_id` bigint(20) UNSIGNED DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `credit_transactions`
--

CREATE TABLE `credit_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('credit','debit','transfer') NOT NULL,
  `amount` decimal(13,2) NOT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `invoice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(3) NOT NULL,
  `symbol` varchar(10) NOT NULL DEFAULT '$',
  `format` varchar(255) DEFAULT NULL,
  `exchange_rate` decimal(10,6) DEFAULT 1.000000,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `name`, `code`, `symbol`, `format`, `exchange_rate`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 'US Dollar', 'USD', '$', 'before_amount', 1.000000, 1, '2025-10-17 05:40:52', '2025-10-17 05:40:52');

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields`
--

CREATE TABLE `custom_fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_label` varchar(255) NOT NULL,
  `field_type` enum('text','textarea','number','date','dropdown','checkbox') NOT NULL,
  `field_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`field_options`)),
  `placeholder` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `default_value` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `show_on_table` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `assignee_id` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `status`, `assignee_id`, `created_at`, `updated_at`) VALUES
(1, 'Sales', 'Handles client relationships, lead generation, and deal closures.', 1, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(2, 'Technical', 'Responsible for development, deployment, and technical support.', 1, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(3, 'Quality Assurance', 'Ensures product meets quality standards through rigorous testing.', 1, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(4, 'General', 'Manages company-wide services like HR, admin, and operations.', 1, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44');

-- --------------------------------------------------------

--
-- Table structure for table `email_layouts`
--

CREATE TABLE `email_layouts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `header` text DEFAULT NULL,
  `footer` text DEFAULT NULL,
  `master_template` text NOT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_layouts`
--

INSERT INTO `email_layouts` (`id`, `name`, `slug`, `header`, `footer`, `master_template`, `variables`, `is_default`, `is_system`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Default Layout', 'default', '<tr><td style=\"background-color: #6366f1; padding: 30px; text-align: center; border-top-left-radius: 12px; border-top-right-radius: 12px;\"> <img src=\"{dark_logo}\" alt=\"{company_name}\" style=\"height: 40px; width: auto; margin-bottom: 10px;\"></td></tr>', '<tr> <td style=\"background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;\"> <p style=\"color: #6b7280; font-size: 14px; margin: 0 0 10px 0;\"> © {current_year} {company_name}.All rights reserved. </p><p style=\"color: #6b7280; font-size: 14px; margin: 0;\">Made with ♥ by {company_name}</p></td></tr>', '<!DOCTYPE html>\n        <html>\n        <head>\n            <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n            <title>{{laungauge}}</title>\n            <style>\n                body {\n                margin: 0; padding: 0; background-color: #f5f6fa;\n                }\n\n            </style>\n        </head>\n        <body>\n            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color: #f5f6fa; padding: 30px;\">\n                <tr>\n                    <td align=\"center\">\n                        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n                            style=\"max-width: 800px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\">\n                            {HEADER}\n                            <tr>\n                                <td style=\"padding:40px 50px;\">\n                                        {CONTENT}\n                                </td>\n                                {FOOTER}\n                            </tr>\n                        </table>\n                    </td>\n                </tr>\n            </table>\n        </body>\n        </html>', '[\"company_name\",\"company_logo\",\"company_address\",\"unsubscribe_url\"]', 1, 1, 1, '2025-10-17 05:36:44', '2025-10-17 05:36:44');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email_template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `from` varchar(255) DEFAULT NULL,
  `to` varchar(255) DEFAULT NULL,
  `cc` varchar(255) DEFAULT NULL,
  `bcc` varchar(255) DEFAULT NULL,
  `reply_to` varchar(255) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `status` enum('pending','sent','failed','scheduled') NOT NULL DEFAULT 'pending',
  `error` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `merge_fields_groups` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`merge_fields_groups`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `category` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `layout_id` int(11) DEFAULT NULL,
  `use_layout` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `name`, `slug`, `description`, `subject`, `content`, `variables`, `merge_fields_groups`, `is_active`, `is_system`, `category`, `type`, `layout_id`, `use_layout`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Test Email', 'test-email', NULL, 'This is a test email from {company_name}', '<p>Hello {first_name} {last_name},</p><p>This is a test email from <strong>{company_name}</strong> to confirm your email configuration is working correctly.</p><p>Thank you, </p><p>{company_name} Team</p>', NULL, '\"[\\\"user-group\\\",\\\"other-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(2, 'Tenant Welcome Email', 'tenant-welcome-mail', NULL, 'Welcome to {company_name}, {tenant_company_name}!', '<p>Hello {tenant_company_name},</p><p>Welcome to <strong>{company_name}</strong>! We are thrilled to have you on board. Your account has been successfully created and is ready to use.</p><p>If you have any questions or need assistance, feel free to reach out.</p><p>Best regards,</p><p>{company_name} Team</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(3, 'Staff Welcome Email', 'staff-welcome-mail', NULL, 'Welcome to {company_name}, {first_name} {last_name}!', '<p>Hello {first_name} {last_name},</p>\n                <p>Welcome to <strong>{company_name}</strong>! Your staff account has been successfully created and is ready to use.</p>\n                <p>If you have any questions or need assistance, feel free to reach out.</p>\n                <p>Best regards,</p>\n                <p>{company_name} Team</p>', NULL, '\"[\\\"user-group\\\",\\\"other-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(4, 'New Tenant Reminder Email to Admin', 'new-tenant-reminder-email-to-admin', NULL, 'New tenant {tenant_company_name} has signed up', '<p>Hi {first_name} {last_name},</p><p>A new tenant <strong>{tenant_company_name}</strong> has just signed up on <strong>{company_name}.</strong></p><p>Please log in to your admin panel to review the details.</p><p>Regards,</p><p>{company_name} </p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"user-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(5, 'Subscription Renewal Payment Successful', 'subscription-renewal-success', NULL, 'Your subscription has been successfully renewed', '<p>Hi {tenant_company_name},</p><p>We are happy to let you know that your subscription has been successfully renewed.</p><p><strong>Plan:</strong> {plan_name}</p><p><strong>Amount:</strong> {plan_price}</p><p>Thank you for staying with us!</p><p>Best,</p><p>{company_name} Team</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"subscription-group\\\",\\\"plan-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(6, 'Subscription Renewal Payment Failed', 'subscription-renewal-failed', NULL, 'Subscription renewal failed for {tenant_company_name}', '<p>Hi {tenant_company_name},</p><p>Unfortunately, your recent subscription renewal attempt was unsuccessful.</p><p>Please update your payment method to avoid any disruption in service.</p><p>Best regards,</p><p>{company_name} Team</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"subscription-group\\\",\\\"plan-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(7, 'Subscription Created', 'subscription-created', NULL, 'Your subscription to {plan_name} has been created', '<p>Hello {tenant_company_name},</p><p>Your subscription to the {plan_name} plan has been successfully created.</p><p>Next billing date: {subscription_period_ends_at}</p><p>We hope you enjoy our services!</p><p>Thanks,</p><p>{company_name} Team</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"subscription-group\\\",\\\"plan-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(8, 'Subscription Activated', 'subscription-activated', NULL, 'Your subscription is now active!', '<p>Hi {tenant_company_name},</p><p>Your subscription to {plan_name} is now active.</p><p>Enjoy all the benefits that come with your plan. If you need support, don’t hesitate to contact us.</p><p>Cheers,</p><p>{company_name} Team</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"subscription-group\\\",\\\"plan-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(9, 'Invoice Receipt', 'invoice-receipt', NULL, 'Your payment receipt for Invoice #{invoice_number}', '<p>Hello {tenant_company_name},</p><p>Thank you for your payment.</p><p><strong>Invoice Number:</strong> {invoice_number}</p><p><strong>Amount:</strong> {invoice_total}</p><p><strong>Date:</strong> {invoice_paid_at}</p><p>You can download your invoice from your dashboard at any time.</p><p>Best Regards,</p><p>{company_name}</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"invoice-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(10, 'Subscription Renewal Reminder', 'subscription-renewal-reminder', NULL, 'Your subscription will renew soon', '<p>Hi {tenant_company_name},</p><p>This is a reminder that your subscription to {subscription_plan_name} will renew on {subscription_period_ends_at}.</p><p>If you need to make any changes to your plan or payment method, please do so before this date.</p><p>Thank you,</p><p>{company_name} Team</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"subscription-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(11, 'Subscription Expiring Soon', 'subscription-expiring-soon', NULL, 'Your subscription is expiring soon', '<p>Dear {company_name},</p><p>Your subscription to {subscription_plan_name} is set to expire on {subscription_period_ends_at}.</p><p>To continue uninterrupted service, please renew or update your subscription details before the expiration date.</p><p>Best regards,</p><p>{company_name} Team</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"subscription-group\\\",\\\"plan-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(12, 'Payment Approved Email', 'payment-approved', NULL, 'Payment approved for Invoice #{invoice_number}', '<p>Hello {tenant_company_name},</p><p>We have successfully received your payment for <strong>Invoice #{invoice_number}</strong></p><p><strong>Amount Paid:</strong> {invoice_total}</p><p><strong>Date:</strong> {invoice_paid_at}</p><p>Thank you for your business!</p><p>Best,</p><p>{company_name} Team</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"invoice-group\\\",\\\"transaction-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(13, 'Transaction Created Reminder to Admin', 'transection-created-reminder-mail-to-admin', NULL, 'New transaction recorded for tenant {tenant_company_name}', '<p>Hello Admin,</p><p>A new transaction has been created for {tenant_company_name}.</p><p><strong>Status:</strong> {tenant_status}</p><p><strong>Amount:</strong> {transaction_amount}</p><p>Check the admin panel for more details.</p><p>Regards,</p><p>{company_name}</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"transaction-group\\\",\\\"user-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(14, 'Subscription Expired', 'subscription-expired', NULL, 'Your subscription has expired', '<p>Hi {tenant_company_name},</p><p>Your subscription to {plan_name} has expired as of {subscription_period_ends_at}.</p><p>Please renew your plan to continue enjoying our services without interruption.</p><p>Thanks,</p><p>{company_name} Team</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"subscription-group\\\",\\\"plan-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(15, 'Subscription Cancelled', 'subscription-cancelled', NULL, 'Your subscription has been cancelled', '<p>Hello {tenant_company_name},</p><p>We\'re confirming that your subscription to {plan_name} was cancelled on {subscription_cancelled_at}.</p><p>If this was a mistake or you wish to rejoin, you can renew your subscription anytime.</p><p>Thank you,</p><p>{company_name} Team</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"subscription-group\\\",\\\"plan-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(16, 'Payment Rejected Notification', 'payment-rejected', NULL, 'Payment for your invoice was rejected', '<p>Dear {company_name},</p><p>Unfortunately, your payment for <strong>Invoice #{invoice_number}</strong> has been rejected.</p><p>Please update your payment method or try again to avoid interruption of service.</p><p>Thank you,</p><p>{company_name} Team</p>', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"invoice-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(17, 'Email Confirmation', 'email-confirmation', NULL, 'Verify Your Email Address', '<p>Hello {first_name} {last_name},</p>\n                            <p>Thank you for signing up! Please verify your email address by clicking the button below:</p>\n                            <p><a href=\"{verification_url}\" class=\"button\">Verify Email Address</a></p>\n                            <p>If you did not create an account, no further action is required.</p>\n                            <p>Thanks,<br>{company_name} Team</p>', NULL, '\"[\\\"user-group\\\",\\\"other-group\\\"]\"', 1, 0, NULL, NULL, 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(18, 'Password Reset', 'password-reset', NULL, 'Reset Password Notification', '<p>Hello {first_name} {last_name},</p>\n                            <p>You are receiving this email because we received a password reset request for your account.</p>\n                            <p><a href=\"{reset_url}\" class=\"button\">Reset Password</a></p>\n                            <p>This password reset link will expire in 60 minutes.</p>\n                            <p>If you did not request a password reset, no further action is required.</p>\n                            <p>Regards,<br>{company_name} Team</p>', NULL, '\"[\\\"user-group\\\",\\\"other-group\\\"]\"', 1, 0, NULL, NULL, 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(19, 'Subscription renewal invoice email to tenant', 'subscription-renewal-invoice', NULL, 'Subscription renewal invoice', '<p>Hello {first_name} {last_name},</p>\n            <p>We’ve generated your renewal invoice for the <strong>{plan_name}</strong> subscription.</p>\n            <h2>Invoice Summary</h2>\n              <ul style=\"list-style: none; padding: 0;\">\n                <li><strong>Plan:</strong> {plan_name}</li>\n            </ul>\n            <p>If you have any questions or need help with the payment process, feel free to contact our support team.</p>\n\n            <p>Thank you for being a valued customer of {company_name}.</p>\n\n            <p style=\"margin-top: 40px;\">Best regards,<br>\n            The {company_name} Team</p>\n        </div>\n               ', NULL, '\"[\\\"tenant-group\\\",\\\"other-group\\\",\\\"subscription-group\\\",\\\"plan-group\\\",\\\"invoice-group\\\",\\\"user-group\\\"]\"', 1, 0, NULL, NULL, 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(20, 'New Ticket Created (Admin Notification)', 'ticket-created', NULL, 'New Support Ticket Created - {ticket_subject}', '<p>Hello Admin,</p>\n                    <p>A new support ticket has been created and requires your attention.</p>\n\n                    <div style=\"background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;\">\n                        <h3 style=\"margin-top: 0; color: #495057;\">Ticket Details</h3>\n                        <table style=\"width: 100%; border-collapse: collapse;\">\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Ticket ID:</td><td style=\"padding: 8px 0;\">{ticket_id}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Subject:</td><td style=\"padding: 8px 0;\">{ticket_subject}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Priority:</td><td style=\"padding: 8px 0;\">{ticket_priority}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Department:</td><td style=\"padding: 8px 0;\">{ticket_department}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Status:</td><td style=\"padding: 8px 0;\">{ticket_status}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Created:</td><td style=\"padding: 8px 0;\">{ticket_created_at}</td></tr>\n                        </table>\n                    </div>\n\n                    <div style=\"background-color: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;\">\n                        <h3 style=\"margin-top: 0; color: #1976d2;\">Customer Information</h3>\n                        <p><strong>Company:</strong> {tenant_company_name}</p>\n                    </div>\n\n                    <div style=\"background-color: #fff3e0; padding: 20px; border-radius: 8px; margin: 20px 0;\">\n                        <h3 style=\"margin-top: 0; color: #f57c00;\">Ticket Description</h3>\n                        <div style=\"background-color: #ffffff; padding: 15px; border-left: 4px solid #ff9800; border-radius: 4px;\">\n                            {ticket_body}\n                        </div>\n                    </div>\n\n                    <p>Please review and assign this ticket at your earliest convenience.</p>\n\n                    <div style=\"text-align: center; margin: 30px 0;\">\n                        <a href=\"{admin_url}\" style=\"background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;\">View Ticket in Admin Panel</a>\n                    </div>\n\n                    <p>Best regards,<br>{company_name} System</p>', NULL, '\"[\\\"ticket-group\\\",\\\"other-group\\\",\\\"tenant-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(21, 'Ticket Reply Notification (To Tenant)', 'ticket-reply-tenant', NULL, 'Support Ticket Reply - {ticket_subject}', '<p>Hello {tenant_company_name},</p>\n                    <p>You have received a new reply on your support ticket.</p>\n\n                    <div style=\"background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;\">\n                        <h3 style=\"margin-top: 0; color: #495057;\">Ticket Information</h3>\n                        <table style=\"width: 100%; border-collapse: collapse;\">\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Ticket ID:</td><td style=\"padding: 8px 0;\">{ticket_id}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Subject:</td><td style=\"padding: 8px 0;\">{ticket_subject}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Status:</td><td style=\"padding: 8px 0;\">{ticket_status}</td></tr>\n                        </table>\n                    </div>\n\n                    <p>You can view the full conversation and reply by clicking the link below:</p>\n\n                    <div style=\"text-align: center; margin: 30px 0;\">\n                        <a href=\"{ticket_url}\" style=\"background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;\">View & Reply to Ticket</a>\n                    </div>\n\n                    <p>Best regards,<br>{company_name} Support Team</p>', NULL, '\"[\\\"ticket-group\\\",\\\"other-group\\\",\\\"tenant-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:38:37'),
(22, 'Ticket Reply Notification (To Admin)', 'ticket-reply-admin', NULL, 'New Reply on Ticket {ticket_id} - {ticket_subject}', '<p>Hello {assigned_user_name},</p>\n                    <p>A new reply has been added to ticket <strong>{ticket_id}</strong>.</p>\n\n                    <div style=\"background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;\">\n                        <h3 style=\"margin-top: 0; color: #495057;\">Ticket Details</h3>\n                        <table style=\"width: 100%; border-collapse: collapse;\">\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Ticket ID:</td><td style=\"padding: 8px 0;\">{ticket_id}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Subject:</td><td style=\"padding: 8px 0;\">{ticket_subject}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Priority:</td><td style=\"padding: 8px 0;\">{ticket_priority}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Status:</td><td style=\"padding: 8px 0;\">{ticket_status}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Customer:</td><td style=\"padding: 8px 0;\">{tenant_company_name}</td></tr>\n                        </table>\n                    </div>\n\n                    <div style=\"text-align: center; margin: 30px 0;\">\n                        <a href=\"{ticket_admin_url}\" style=\"background-color: #dc3545; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;\">View Ticket in Admin Panel</a>\n                    </div>\n\n                    <p>Best regards,<br>{company_name} System</p>', NULL, '\"[\\\"ticket-group\\\",\\\"other-group\\\",\\\"tenant-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(23, 'Ticket Status Changed Notification', 'ticket-status-changed', NULL, 'Ticket Status Updated - {ticket_subject}', '<p>Hello {tenant_company_name},</p>\n                    <p>The status of your support ticket has been updated.</p>\n\n                    <div style=\"background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;\">\n                        <h3 style=\"margin-top: 0; color: #495057;\">Ticket Information</h3>\n                        <table style=\"width: 100%; border-collapse: collapse;\">\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Ticket ID:</td><td style=\"padding: 8px 0;\">{ticket_id}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Subject:</td><td style=\"padding: 8px 0;\">{ticket_subject}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Previous Status:</td><td style=\"padding: 8px 0;\">{previous_status}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">New Status:</td><td style=\"padding: 8px 0; color: #007bff;\"><strong>{new_status}</strong></td></tr>\n                        </table>\n                    </div>\n\n                    <div style=\"text-align: center; margin: 30px 0;\">\n                        <a href=\"{ticket_url}\" style=\"background-color: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;\">View Ticket Details</a>\n                    </div>\n\n                    <p>Best regards,<br>{company_name} Support Team</p>', NULL, '\"[\\\"ticket-group\\\",\\\"other-group\\\",\\\"tenant-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:38:37'),
(24, 'Ticket Status Changed Notification (Admin)', 'ticket-status-changed-admin', NULL, 'Ticket Status Updated by Tenant - {ticket_subject}', '<p>Hello Admin,</p>\n                    <p>A ticket status has been updated by the tenant.</p>\n\n                    <div style=\"background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;\">\n                        <h3 style=\"margin-top: 0; color: #495057;\">Ticket Information</h3>\n                        <table style=\"width: 100%; border-collapse: collapse;\">\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Ticket ID:</td><td style=\"padding: 8px 0;\">{ticket_id}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Subject:</td><td style=\"padding: 8px 0;\">{ticket_subject}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Customer:</td><td style=\"padding: 8px 0;\">{tenant_company_name}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Previous Status:</td><td style=\"padding: 8px 0;\">{previous_status}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">New Status:</td><td style=\"padding: 8px 0; color: #007bff;\"><strong>{new_status}</strong></td></tr>\n                        </table>\n                    </div>\n\n                    <div style=\"text-align: center; margin: 30px 0;\">\n                        <a href=\"{ticket_admin_url}\" style=\"background-color: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;\">View in Admin Panel</a>\n                    </div>\n\n                    <p>Best regards,<br>{company_name} System</p>', NULL, '\"[\\\"ticket-group\\\",\\\"other-group\\\",\\\"tenant-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(25, 'Ticket Assigned Notification', 'ticket-assigned', NULL, 'Ticket Assigned to You - {ticket_subject}', '<p>Hello {assigned_user_name},</p>\n                    <p>A support ticket has been assigned to you.</p>\n\n                    <div style=\"background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;\">\n                        <h3 style=\"margin-top: 0; color: #495057;\">Ticket Details</h3>\n                        <table style=\"width: 100%; border-collapse: collapse;\">\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Ticket ID:</td><td style=\"padding: 8px 0;\">{ticket_id}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Subject:</td><td style=\"padding: 8px 0;\">{ticket_subject}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Priority:</td><td style=\"padding: 8px 0;\">{ticket_priority}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Department:</td><td style=\"padding: 8px 0;\">{ticket_department}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Customer:</td><td style=\"padding: 8px 0;\">{tenant_company_name}</td></tr>\n                            <tr><td style=\"padding: 8px 0; font-weight: bold;\">Created:</td><td style=\"padding: 8px 0;\">{ticket_created_at}</td></tr>\n                        </table>\n                    </div>\n\n                    <div style=\"background-color: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;\">\n                        <h3 style=\"margin-top: 0; color: #1976d2;\">Ticket Description</h3>\n                        <div style=\"background-color: #ffffff; padding: 15px; border-left: 4px solid #2196f3; border-radius: 4px;\">\n                            {ticket_body}\n                        </div>\n                    </div>\n\n                    <div style=\"text-align: center; margin: 30px 0;\">\n                        <a href=\"{ticket_admin_url}\" style=\"background-color: #6f42c1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;\">View & Respond to Ticket</a>\n                    </div>\n\n                    <p>Best regards,<br>{company_name} System</p>', NULL, '\"[\\\"ticket-group\\\",\\\"other-group\\\",\\\"tenant-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(26, 'Transaction Successful', 'transaction-success', NULL, 'Payment Confirmed - {transaction_amount} from {company_name}', '\n<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;\">\n    <div style=\"background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;\">\n        <h1 style=\"color: white; margin: 0; font-size: 28px;\">✅ Payment Successful!</h1>\n    </div>\n\n    <div style=\"background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;\">\n        <p style=\"font-size: 16px; line-height: 1.6; margin: 0 0 20px;\">Hello <strong>{first_name} {last_name}</strong>,</p>\n\n        <p style=\"font-size: 16px; line-height: 1.6; margin: 0 0 25px;\">Great news! Your payment has been successfully processed. Here are the details:</p>\n\n        <div style=\"background: #f9fafb; padding: 20px; border-radius: 8px; margin: 25px 0;\">\n            <table style=\"width: 100%; border-collapse: collapse;\">\n                <tr>\n                    <td style=\"padding: 8px 0; font-weight: bold;\">Transaction ID:</td>\n                    <td style=\"padding: 8px 0; text-align: right; font-family: monospace;\">{transaction_id}</td>\n                </tr>\n                <tr>\n                    <td style=\"padding: 8px 0; font-weight: bold;\">Amount:</td>\n                    <td style=\"padding: 8px 0; text-align: right; color: #10b981; font-size: 18px; font-weight: bold;\">{transaction_amount}</td>\n                </tr>\n                <tr>\n                    <td style=\"padding: 8px 0; font-weight: bold;\">Payment Method:</td>\n                    <td style=\"padding: 8px 0; text-align: right;\">{transaction_type}</td>\n                </tr>\n                <tr style=\"border-top: 2px solid #e5e7eb;\">\n                    <td style=\"padding: 12px 0 8px; font-weight: bold;\">Description:</td>\n                    <td style=\"padding: 12px 0 8px; text-align: right;\">{transaction_description}</td>\n                </tr>\n            </table>\n        </div>\n\n                    <p>Thank you for choosing {company_name}.</p>\n                    <p>Best regards,<br>{company_name} Support Team</p>\n    </div>\n</div>', NULL, '\"[\\\"transaction-group\\\",\\\"user-group\\\",\\\"other-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(27, 'Transaction Pending', 'transaction-pending', NULL, 'Payment Pending - Action Required for {transaction_amount} - {company_name}', '\n<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;\">\n    <div style=\"background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;\">\n        <h1 style=\"color: white; margin: 0; font-size: 28px;\">⏳ Payment Pending</h1>\n    </div>\n\n    <div style=\"background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;\">\n        <p style=\"font-size: 16px; line-height: 1.6; margin: 0 0 20px;\">Hello <strong>{first_name} {last_name}</strong>,</p>\n\n        <p style=\"font-size: 16px; line-height: 1.6; margin: 0 0 25px;\">Your payment is currently being processed. Additional verification may be required to complete this transaction.</p>\n\n        <div style=\"background: #fef3c7; padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #f59e0b;\">\n            <p style=\"margin: 0; font-weight: bold; color: #92400e;\">⚠️ Action May Be Required</p>\n            <p style=\"margin: 10px 0 0; color: #92400e;\">Some payments require additional authentication (like 3D Secure) or take time to process depending on your bank or payment method.</p>\n        </div>\n\n        <div style=\"background: #f9fafb; padding: 20px; border-radius: 8px; margin: 25px 0;\">\n            <table style=\"width: 100%; border-collapse: collapse;\">\n                <tr>\n                    <td style=\"padding: 8px 0; font-weight: bold;\">Transaction ID:</td>\n                    <td style=\"padding: 8px 0; text-align: right; font-family: monospace;\">{transaction_id}</td>\n                </tr>\n                <tr>\n                    <td style=\"padding: 8px 0; font-weight: bold;\">Amount:</td>\n                    <td style=\"padding: 8px 0; text-align: right; color: #f59e0b; font-size: 18px; font-weight: bold;\">{transaction_amount}</td>\n                </tr>\n                <tr>\n                    <td style=\"padding: 8px 0; font-weight: bold;\">Payment Method:</td>\n                    <td style=\"padding: 8px 0; text-align: right;\">{transaction_type}</td>\n                </tr>\n                <tr>\n                    <td style=\"padding: 8px 0; font-weight: bold;\">Status:</td>\n                    <td style=\"padding: 8px 0; text-align: right; color: #f59e0b; font-weight: bold;\">Pending</td>\n                </tr>\n                <tr style=\"border-top: 2px solid #e5e7eb;\">\n                    <td style=\"padding: 12px 0 8px; font-weight: bold;\">Description:</td>\n                    <td style=\"padding: 12px 0 8px; text-align: right;\">{transaction_description}</td>\n                </tr>\n            </table>\n        </div>\n\n        <div style=\"background: #eff6ff; padding: 20px; border-radius: 8px; margin: 25px 0;\">\n            <h3 style=\"margin: 0 0 15px; color: #1e40af;\">What happens next?</h3>\n            <ul style=\"margin: 0; padding-left: 20px; color: #1e40af;\">\n                <li>We will send you an update once the payment is processed</li>\n                <li>Most pending payments are resolved within 24 hours</li>\n                <li>You may receive additional verification requests from your bank</li>\n                <li>Check your email and banking app for any notifications</li>\n            </ul>\n        </div>\n\n        <p style=\"font-size: 16px; line-height: 1.6; margin: 25px 0 0;\">Thank you for your patience,</p>\n        <p style=\"font-size: 16px; line-height: 1.6; margin: 5px 0 0;\"><strong>{company_name} Team</strong></p>\n    </div>\n</div>', NULL, '\"[\\\"transaction-group\\\",\\\"user-group\\\",\\\"other-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(28, 'Transaction Failed', 'transaction-failed', NULL, 'Payment Failed - {transaction_amount} - {company_name}', '\n<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;\">\n    <div style=\"background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;\">\n        <h1 style=\"color: white; margin: 0; font-size: 28px;\">❌ Payment Failed</h1>\n    </div>\n\n    <div style=\"background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;\">\n        <p style=\"font-size: 16px; line-height: 1.6; margin: 0 0 20px;\">Hello <strong>{first_name} {last_name}</strong>,</p>\n\n        <p style=\"font-size: 16px; line-height: 1.6; margin: 0 0 25px;\">Unfortunately, we were unable to process your payment. Please try again.</p>\n\n        <div style=\"background: #f9fafb; padding: 20px; border-radius: 8px; margin: 25px 0;\">\n            <table style=\"width: 100%; border-collapse: collapse;\">\n                <tr>\n                    <td style=\"padding: 8px 0; font-weight: bold;\">Transaction ID:</td>\n                    <td style=\"padding: 8px 0; text-align: right; font-family: monospace;\">{transaction_id}</td>\n                </tr>\n                <tr>\n                    <td style=\"padding: 8px 0; font-weight: bold;\">Amount:</td>\n                    <td style=\"padding: 8px 0; text-align: right; color: #ef4444; font-size: 18px; font-weight: bold;\">{transaction_amount}</td>\n                </tr>\n                <tr>\n                    <td style=\"padding: 8px 0; font-weight: bold;\">Payment Method:</td>\n                    <td style=\"padding: 8px 0; text-align: right;\">{transaction_type}</td>\n                </tr>\n                <tr>\n                    <td style=\"padding: 8px 0; font-weight: bold;\">Status:</td>\n                    <td style=\"padding: 8px 0; text-align: right; color: #ef4444; font-weight: bold;\">Failed</td>\n                </tr>\n                <tr style=\"border-top: 2px solid #e5e7eb;\">\n                    <td style=\"padding: 12px 0 8px; font-weight: bold;\">Description:</td>\n                    <td style=\"padding: 12px 0 8px; text-align: right;\">{transaction_description}</td>\n                </tr>\n            </table>\n        </div>\n\n        <div style=\"background: #f0f9ff; padding: 20px; border-radius: 8px; margin: 25px 0;\">\n            <h3 style=\"margin: 0 0 15px; color: #0369a1;\">💡 How to resolve this:</h3>\n            <ul style=\"margin: 0; padding-left: 20px; color: #0369a1;\">\n                <li><strong>Check your card details:</strong> Ensure card number, expiry date, and CVV are correct</li>\n                <li><strong>Verify billing address:</strong> Make sure your billing address matches your card</li>\n                <li><strong>Check your account balance:</strong> Ensure you have sufficient funds available</li>\n                <li><strong>Contact your bank:</strong> Your card issuer may have declined the transaction</li>\n                <li><strong>Try a different payment method:</strong> Use another card or payment option</li>\n            </ul>\n        </div>\n\n        <p style=\"font-size: 16px; line-height: 1.6; margin: 5px 0 0;\"><strong>{company_name} Team</strong></p>\n    </div>\n</div>', NULL, '\"[\\\"transaction-group\\\",\\\"user-group\\\",\\\"other-group\\\"]\"', 1, 0, NULL, 'admin', 1, 1, NULL, NULL, '2025-10-17 05:36:44', '2025-10-17 05:36:44');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `is_visible`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'How do I upgrade my subscription plan?', 'You can upgrade your plan from your billing dashboard. Navigate to Subscriptions > Upgrade and select your desired higher-tier plan. The upgrade will be prorated, meaning you\'ll only pay the difference for the remaining billing period. Payment is required immediately to activate the upgrade.', 1, 1, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(2, 'How do I downgrade my subscription plan?', 'To downgrade, go to Subscriptions > Downgrade and choose a lower-tier plan. Downgrades are processed immediately, and any credit from the price difference will be applied to your account for future billing cycles.', 1, 2, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(3, 'How does the billing cycle work?', 'Your billing cycle depends on your plan\'s billing period (monthly or yearly). The cycle starts from your subscription activation date and renews automatically unless you\'ve disabled auto-renewal. You can view your next billing date in your subscription dashboard.', 1, 3, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(4, 'When will I be charged for renewals?', 'Auto-billing occurs 1 day after your current period ends. For example, if your subscription expires on January 15th, auto-billing will attempt on January 16th. This grace period ensures uninterrupted service.', 1, 4, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(5, 'What happens if my payment fails?', 'If auto-billing fails, you\'ll receive notifications and have a grace period to update your payment method. Failed payments are logged in your transaction history, and you can manually retry payment from your billing dashboard.', 1, 5, '2025-10-17 05:40:52', '2025-10-17 05:40:52');

-- --------------------------------------------------------

--
-- Table structure for table `features`
--

CREATE TABLE `features` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('limit','boolean') NOT NULL DEFAULT 'boolean',
  `icon` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `features`
--

INSERT INTO `features` (`id`, `name`, `slug`, `description`, `type`, `icon`, `display_order`, `default`, `created_at`, `updated_at`) VALUES
(1, 'Contacts ', 'contacts', 'Number of contacts allowed', 'limit', NULL, 10, 1, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(2, 'Template Bots ', 'template_bots', 'Number of template bots allowed', 'limit', NULL, 20, 0, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(3, 'Message Bots ', 'message_bots', 'Number of message bots allowed', 'limit', NULL, 30, 0, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(4, 'Campaigns ', 'campaigns', 'Number of campaigns allowed', 'limit', NULL, 40, 0, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(5, 'AI Prompts ', 'ai_prompts', 'Number of ai prompts allowed', 'limit', NULL, 50, 0, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(6, 'Canned Replies ', 'canned_replies', 'Number of canned replies allowed', 'limit', NULL, 60, 0, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(7, 'Staff ', 'staff', 'Number of staffs allowed', 'limit', NULL, 70, 0, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(8, 'Conversation ', 'conversations', 'Number of conversation allowed', 'limit', NULL, 80, 0, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(9, 'Bot Flow ', 'bot_flow', 'Number of bot flows allowed', 'limit', NULL, 90, 0, '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(10, 'Enable Api ', 'enable_api', 'Enable(-1) or Disable(0) Api', 'limit', NULL, 100, 0, '2025-10-17 05:40:52', '2025-10-17 05:40:52');

-- --------------------------------------------------------

--
-- Table structure for table `feature_limits`
--

CREATE TABLE `feature_limits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `feature_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `custom_limit` varchar(255) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feature_usages`
--

CREATE TABLE `feature_usages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `feature_slug` varchar(255) NOT NULL,
  `limit_value` int(11) NOT NULL DEFAULT -1,
  `used` int(11) NOT NULL DEFAULT 0,
  `reset_date` timestamp NULL DEFAULT NULL,
  `period_start` timestamp NULL DEFAULT NULL,
  `last_reset_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feature_usages`
--

INSERT INTO `feature_usages` (`id`, `tenant_id`, `subscription_id`, `feature_slug`, `limit_value`, `used`, `reset_date`, `period_start`, `last_reset_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'bot_flow', 100000, 2, NULL, '2025-11-12 03:40:23', NULL, '2025-11-12 03:40:23', '2025-11-12 04:09:02'),
(2, 1, 1, 'conversations', 100000, 21, NULL, '2025-11-12 03:58:08', NULL, '2025-11-12 03:58:08', '2025-11-14 12:59:55'),
(3, 1, 1, 'message_bots', 100000, 2, NULL, '2025-11-12 04:03:25', NULL, '2025-11-12 04:03:25', '2025-11-12 04:10:28'),
(4, 1, 1, 'canned_replies', 100000, 0, NULL, '2025-11-12 04:07:18', NULL, '2025-11-12 04:07:18', '2025-11-14 12:59:56'),
(5, 1, 1, 'ai_prompts', 100000, 0, NULL, '2025-11-12 04:07:18', NULL, '2025-11-12 04:07:18', '2025-11-14 12:59:56'),
(6, 1, 1, 'contacts', 100000, 1, NULL, '2025-11-12 04:07:18', NULL, '2025-11-12 04:07:18', '2025-11-12 04:16:07'),
(7, 1, 1, 'campaigns', 100000, 0, NULL, '2025-11-12 04:07:18', NULL, '2025-11-12 04:07:18', '2025-11-14 12:59:56'),
(8, 1, 1, 'template_bots', 100000, 0, NULL, '2025-11-12 04:07:18', NULL, '2025-11-12 04:07:18', '2025-11-14 12:59:56'),
(9, 1, 1, 'staff', 100000, 0, NULL, '2025-11-12 04:07:18', NULL, '2025-11-12 04:07:18', '2025-11-14 12:59:56'),
(10, 1, 1, 'conversation', 0, 9, NULL, '2025-11-12 04:07:18', NULL, '2025-11-12 04:07:18', '2025-11-12 04:07:18');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `status` enum('new','paid','cancelled') NOT NULL DEFAULT 'new',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `metadata` text DEFAULT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `coupon_id` bigint(20) UNSIGNED DEFAULT NULL,
  `coupon_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `coupon_code` varchar(255) DEFAULT NULL,
  `coupon_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Snapshot of coupon data when applied' CHECK (json_valid(`coupon_snapshot`)),
  `total_tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fee` decimal(13,2) NOT NULL DEFAULT 0.00,
  `invoice_number` varchar(255) DEFAULT NULL,
  `due_date` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `no_payment_required_when_free` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `tenant_id`, `subscription_id`, `type`, `status`, `title`, `description`, `metadata`, `currency_id`, `coupon_id`, `coupon_discount`, `coupon_code`, `coupon_snapshot`, `total_tax_amount`, `fee`, `invoice_number`, `due_date`, `paid_at`, `cancelled_at`, `no_payment_required_when_free`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'new_subscription', 'paid', 'New Subscription', 'Subscription to Free plan until November 25, 2025', NULL, 1, NULL, 0.00, NULL, NULL, 0.00, 0.00, 'INV202511-000001', NULL, NULL, NULL, 0, '2025-11-12 03:40:13', '2025-11-12 03:40:13'),
(2, 1, 1, 'new_subscription', 'paid', 'New Subscription', 'Subscription to ABC XYZ plan until December 11, 2025', NULL, 1, NULL, 0.00, NULL, NULL, 0.00, 0.00, 'INV202511-000002', NULL, '2025-11-12 04:08:38', NULL, 0, '2025-11-12 04:07:18', '2025-11-12 04:08:38');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_type` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(13,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `item_id`, `item_type`, `title`, `description`, `amount`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 'Free', 'Subscription to Free plan', 0.00, 1, '2025-11-12 03:40:13', '2025-11-12 03:40:13'),
(2, 2, NULL, NULL, 'ABC XYZ', 'Subscription to ABC XYZ plan', 30.00, 1, '2025-11-12 04:07:18', '2025-11-12 04:07:18');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_taxes`
--

CREATE TABLE `invoice_taxes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `tax_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `rate` decimal(8,2) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(5) NOT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `tenant_id`, `name`, `code`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'English', 'en', 1, '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(2, NULL, 'Portuguese', 'br', 1, '2025-10-17 05:36:48', '2025-10-17 05:36:48'),
(3, 1, 'English', 'en', 1, '2025-11-12 03:39:58', '2025-11-12 03:39:58');

-- --------------------------------------------------------

--
-- Table structure for table `message_bots`
--

CREATE TABLE `message_bots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Reference to tenant',
  `name` varchar(255) NOT NULL,
  `rel_type` varchar(50) NOT NULL,
  `reply_text` text NOT NULL,
  `reply_type` tinyint(4) NOT NULL,
  `trigger` text DEFAULT NULL,
  `bot_header` varchar(191) DEFAULT NULL,
  `bot_footer` varchar(191) DEFAULT NULL,
  `button1` varchar(255) DEFAULT NULL,
  `button1_id` varchar(255) DEFAULT NULL,
  `button2` varchar(255) DEFAULT NULL,
  `button2_id` varchar(255) DEFAULT NULL,
  `button3` varchar(255) DEFAULT NULL,
  `button3_id` varchar(255) DEFAULT NULL,
  `button_name` varchar(255) DEFAULT NULL,
  `button_url` varchar(255) DEFAULT NULL,
  `addedfrom` int(11) NOT NULL,
  `is_bot_active` tinyint(1) NOT NULL DEFAULT 1,
  `sending_count` int(11) NOT NULL DEFAULT 0,
  `filename` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_bots`
--

INSERT INTO `message_bots` (`id`, `tenant_id`, `name`, `rel_type`, `reply_text`, `reply_type`, `trigger`, `bot_header`, `bot_footer`, `button1`, `button1_id`, `button2`, `button2_id`, `button3`, `button3_id`, `button_name`, `button_url`, `addedfrom`, `is_bot_active`, `sending_count`, `filename`, `created_at`, `updated_at`) VALUES
(1, 1, '1st message', 'lead', 'Hello', 3, NULL, NULL, NULL, 'Avc', '1', 'Axc', '2', 'Mxc', '3', NULL, NULL, 1, 1, 0, NULL, '2025-11-12 04:04:20', '2025-11-12 04:04:20'),
(2, 1, 'test', 'customer', 'test', 2, 'test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 0, NULL, '2025-11-12 04:10:28', '2025-11-12 04:10:28');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2022_12_14_083707_create_settings_table', 1),
(5, '2023_07_13_000000_create_taxes_table', 1),
(6, '2025_01_08_000001_create_module_validation_logs_table', 1),
(7, '2025_03_02_083707_alter_users_table', 1),
(8, '2025_03_24_072033_create_faqs_table', 1),
(9, '2025_03_25_130551_create_theme_settings', 1),
(10, '2025_03_26_121434_create_tenant_settings_table', 1),
(11, '2025_03_28_070510_create_system_settings', 1),
(12, '2025_03_28_074346_create_re_captcha_settings', 1),
(13, '2025_03_28_075625_create_email_settings', 1),
(14, '2025_03_28_101632_create_cron_job_settings', 1),
(15, '2025_03_28_104323_create_whats_mark_settings', 1),
(16, '2025_03_31_030310_create_tenants_table', 1),
(17, '2025_03_31_062225_create_plans_table', 1),
(18, '2025_03_31_124314_announcement_settings', 1),
(19, '2025_04_01_062442_create_languages_table', 1),
(20, '2025_04_05_172456_create_features_table', 1),
(21, '2025_04_05_172507_create_plan_features_table', 1),
(22, '2025_04_14_115126_create_payment_settings', 1),
(23, '2025_04_18_045949_tenant_specific_settings', 1),
(24, '2025_04_26_060520_create_currencies_table', 1),
(25, '2025_05_02_000001_create_email_layouts_table', 1),
(26, '2025_05_03_120258_create_whatsapp_settings', 1),
(27, '2025_05_04_000001_create_email_templates_table', 1),
(28, '2025_05_05_000001_create_email_logs_table', 1),
(29, '2025_05_09_062838_create_permission_tables', 1),
(30, '2025_05_19_110957_create_invoice_settings', 1),
(31, '2025_05_26_091936_create_departments_table', 1),
(32, '2025_05_28_095954_create_privacy_policy_settings', 1),
(33, '2025_06_07_123456_create_terms_conditions_settings', 1),
(34, '2025_06_12_125000_add_default_taxes_to_invoice_settings', 1),
(35, '2025_06_24_105018_add_razorpay_to_payment_settings', 1),
(36, '2025_07_01_051611_create_tenant_additional_settings', 1),
(37, '2025_07_01_073021_add_additional_fields_in_tenant_table', 1),
(38, '2025_07_05_044100_create_miscellaneous_settings', 1),
(39, '2025_07_07_073019_create_bot_flows_table', 1),
(40, '2025_07_11_123806_add_themes_style_settings_table', 1),
(41, '2025_07_12_044529_create_groups_table', 1),
(42, '2025_07_12_072850_alter_contact_table', 1),
(43, '2025_07_16_000000_add_wm_fb_config_id_to_whatsapp_settings', 1),
(44, '2025_07_16_053052_create_modules_table', 1),
(45, '2025_07_18_093520_alter_description__pages_table', 1),
(46, '2025_07_18_102235_add_is_enable_landing_page_setting', 1),
(47, '2025_07_19_061218_alter_contacts_table', 1),
(48, '2025_08_05_120000_add_default_tenant_language_setting', 1),
(49, '2025_08_06_133503_alter_whatsapp_templates_table_add_new_fields', 1),
(50, '2025_08_21_000001_create_tenant_languages_table', 1),
(51, '2025_06_27_040042_add_paypal_to_payment_settings', 2),
(52, '2025_06_27_040042_add_razorpay_to_payment_settings', 2),
(53, '2025_08_18_122205_create_custom_fields_table', 2),
(54, '2025_08_18_122322_add_custom_fields_to_contacts_table', 2),
(55, '2025_08_18_123343_add_custom_fields_permissions', 2),
(56, '2025_08_27_000001_add_max_queue_jobs_setting', 2),
(57, '2025_08_27_000001_create_contact_imports_table', 2),
(58, '2025_08_28_105718_add_deleted_at_to_tenants_table', 3),
(59, '2025_09_08_090013_create_paystack_settings', 3),
(60, '2025_09_13_101740_update_ticket_status_changed_email_template_type', 3),
(61, '2025_09_24_080751_add_message_id_index_to_chat_messages_tables', 4),
(62, '2025_09_11_054333_create_coupons_table', 5),
(63, '2025_09_11_054353_create_coupon_usages_table', 5),
(64, '2025_09_11_054426_add_coupon_fields_to_invoices_table', 5),
(65, '2025_10_01_000001_add_campaign_performance_indexes', 5),
(66, '2025_10_06_063913_add_coupon_snapshot_to_invoices_table', 5),
(67, '2025_10_06_064335_add_heading_and_extrafeature', 5),
(68, '2025_10_09_131358_create_api_settings', 6),
(69, '2025_10_13_045338_add_payload_to_themes_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `item_id` varchar(255) DEFAULT NULL,
  `version` varchar(255) NOT NULL,
  `payload` text DEFAULT NULL,
  `hash` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `module_validation_logs`
--

CREATE TABLE `module_validation_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module_name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `purchase_code` varchar(255) NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `validation_response` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `show_in_menu` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `payment_method_id` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_webhooks`
--

CREATE TABLE `payment_webhooks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `provider` varchar(50) NOT NULL,
  `webhook_id` varchar(255) DEFAULT NULL,
  `endpoint_url` varchar(255) NOT NULL,
  `secret` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`events`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `last_pinged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `scope` varchar(255) NOT NULL DEFAULT 'tenant',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `scope`, `created_at`, `updated_at`) VALUES
(1, 'admin.tenants.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(2, 'admin.tenants.create', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(3, 'admin.tenants.edit', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(4, 'admin.tenants.delete', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(5, 'admin.tenants.login', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(6, 'admin.subscription.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(7, 'admin.invoices.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(8, 'admin.transactions.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(9, 'admin.transactions.actions', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(10, 'admin.plans.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(11, 'admin.plans.create', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(12, 'admin.plans.edit', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(13, 'admin.plans.delete', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(14, 'admin.website_settings.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(15, 'admin.website_settings.edit', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(16, 'admin.system_settings.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(17, 'admin.system_settings.edit', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(18, 'admin.payment_settings.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(19, 'admin.payment_settings.edit', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(20, 'admin.users.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(21, 'admin.users.create', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(22, 'admin.users.edit', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(23, 'admin.users.delete', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(24, 'admin.roles.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(25, 'admin.roles.create', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(26, 'admin.roles.edit', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(27, 'admin.roles.delete', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(28, 'admin.department.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(29, 'admin.department.create', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(30, 'admin.department.edit', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(31, 'admin.department.delete', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(32, 'admin.currency.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(33, 'admin.currency.create', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(34, 'admin.currency.edit', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(35, 'admin.currency.delete', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(36, 'admin.faq.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(37, 'admin.faq.create', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(38, 'admin.faq.edit', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(39, 'admin.faq.delete', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(40, 'admin.pages.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(41, 'admin.pages.create', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(42, 'admin.pages.edit', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(43, 'admin.pages.delete', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(44, 'admin.email_template.view', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(45, 'admin.email_template.edit', 'web', 'admin', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(46, 'tenant.connect_account.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(47, 'tenant.connect_account.connect', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(48, 'tenant.connect_account.disconnect', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(49, 'tenant.contact.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(50, 'tenant.contact.view_own', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(51, 'tenant.contact.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(52, 'tenant.contact.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(53, 'tenant.contact.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(54, 'tenant.contact.bulk_import', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(55, 'tenant.subscription.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(56, 'tenant.invoices.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(57, 'tenant.template.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(58, 'tenant.template.load_template', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(59, 'tenant.template.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(60, 'tenant.template.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(61, 'tenant.template.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(62, 'tenant.campaigns.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(63, 'tenant.campaigns.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(64, 'tenant.campaigns.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(65, 'tenant.campaigns.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(66, 'tenant.campaigns.show_campaign', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(67, 'tenant.bulk_campaigns.send', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(68, 'tenant.template_bot.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(69, 'tenant.template_bot.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(70, 'tenant.template_bot.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(71, 'tenant.template_bot.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(72, 'tenant.template_bot.clone', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(73, 'tenant.message_bot.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(74, 'tenant.message_bot.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(75, 'tenant.message_bot.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(76, 'tenant.message_bot.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(77, 'tenant.message_bot.clone', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(78, 'tenant.source.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(79, 'tenant.source.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(80, 'tenant.source.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(81, 'tenant.source.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(82, 'tenant.status.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(83, 'tenant.status.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(84, 'tenant.status.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(85, 'tenant.status.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(86, 'tenant.group.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(87, 'tenant.group.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(88, 'tenant.group.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(89, 'tenant.group.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(90, 'tenant.ai_prompt.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(91, 'tenant.ai_prompt.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(92, 'tenant.ai_prompt.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(93, 'tenant.ai_prompt.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(94, 'tenant.canned_reply.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(95, 'tenant.canned_reply.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(96, 'tenant.canned_reply.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(97, 'tenant.canned_reply.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(98, 'tenant.chat.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(99, 'tenant.chat.read_only', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(100, 'tenant.chat.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(101, 'tenant.activity_log.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(102, 'tenant.activity_log.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(103, 'tenant.whatsmark_settings.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(104, 'tenant.whatsmark_settings.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(105, 'tenant.system_settings.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(106, 'tenant.system_settings.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(107, 'tenant.staff.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(108, 'tenant.staff.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(109, 'tenant.staff.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(110, 'tenant.staff.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(111, 'tenant.role.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(112, 'tenant.role.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(113, 'tenant.role.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(114, 'tenant.role.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(115, 'tenant.email_template.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(116, 'tenant.email_template.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(117, 'tenant.bot_flow.view', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(118, 'tenant.bot_flow.create', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(119, 'tenant.bot_flow.edit', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(120, 'tenant.bot_flow.delete', 'web', 'tenant', '2025-10-17 05:36:44', '2025-10-17 05:36:44'),
(121, 'tenant.custom_fields.view', 'web', 'tenant', '2025-10-17 05:37:58', '2025-10-17 05:37:58'),
(122, 'tenant.custom_fields.create', 'web', 'tenant', '2025-10-17 05:37:58', '2025-10-17 05:37:58'),
(123, 'tenant.custom_fields.edit', 'web', 'tenant', '2025-10-17 05:37:58', '2025-10-17 05:37:58'),
(124, 'tenant.custom_fields.delete', 'web', 'tenant', '2025-10-17 05:37:58', '2025-10-17 05:37:58'),
(125, 'admin.coupon.view', 'web', 'admin', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(126, 'admin.coupon.create', 'web', 'admin', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(127, 'admin.coupon.edit', 'web', 'admin', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(128, 'admin.coupon.delete', 'web', 'admin', '2025-10-17 05:40:03', '2025-10-17 05:40:03');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `yearly_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `yearly_discount` int(11) NOT NULL DEFAULT 0,
  `billing_period` varchar(255) NOT NULL DEFAULT 'monthly',
  `trial_days` int(11) NOT NULL DEFAULT 0,
  `interval` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_free` tinyint(1) NOT NULL DEFAULT 0,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `color` varchar(255) DEFAULT NULL,
  `currency_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `slug`, `description`, `price`, `yearly_price`, `yearly_discount`, `billing_period`, `trial_days`, `interval`, `is_active`, `is_free`, `featured`, `color`, `currency_id`, `created_at`, `updated_at`) VALUES
(1, 'Free', 'free', 'Basic plan for individuals getting started with WhatsApp', 0.00, 0.00, 0, 'monthly', 14, 1, 1, 1, 0, '#EF4444', 1, '2025-10-17 05:36:43', '2025-10-17 05:36:43'),
(2, 'ABC XYZ', 'abc-xyz', 'How arte you', 30.00, 0.00, 0, 'monthly', 0, 1, 1, 0, 1, '#000000', 1, '2025-11-12 04:06:57', '2025-11-12 04:06:57');

-- --------------------------------------------------------

--
-- Table structure for table `plan_features`
--

CREATE TABLE `plan_features` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `feature_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT 'Feature display name',
  `slug` varchar(255) DEFAULT NULL COMMENT 'URL-friendly feature name',
  `description` text DEFAULT NULL COMMENT 'Feature description',
  `value` varchar(255) NOT NULL DEFAULT '0' COMMENT 'Feature value or limit',
  `resettable_period` int(11) DEFAULT NULL COMMENT 'Period after which usage resets',
  `resettable_interval` varchar(255) DEFAULT NULL COMMENT 'Interval for reset (day, month, year)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plan_features`
--

INSERT INTO `plan_features` (`id`, `plan_id`, `feature_id`, `name`, `slug`, `description`, `value`, `resettable_period`, `resettable_interval`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Contacts ', 'contacts', 'Number of contacts allowed', '50', NULL, NULL, '2025-10-17 05:36:43', '2025-10-17 05:36:43'),
(2, 1, 2, 'Template Bots ', 'template_bots', 'Number of template bots allowed', '5', NULL, NULL, '2025-10-17 05:36:43', '2025-10-17 05:36:43'),
(3, 1, 3, 'Message Bots ', 'message_bots', 'Number of message bots allowed', '5', NULL, NULL, '2025-10-17 05:36:43', '2025-10-17 05:36:43'),
(4, 1, 4, 'Campaigns ', 'campaigns', 'Number of campaigns allowed', '5', NULL, NULL, '2025-10-17 05:36:43', '2025-10-17 05:36:43'),
(5, 1, 5, 'AI Prompts ', 'ai_prompts', 'Number of ai prompts allowed', '0', NULL, NULL, '2025-10-17 05:36:43', '2025-10-17 05:36:43'),
(6, 1, 6, 'Canned Replies ', 'canned_replies', 'Number of canned replies allowed', '0', NULL, NULL, '2025-10-17 05:36:43', '2025-10-17 05:36:43'),
(7, 1, 7, 'Staff ', 'staff', 'Number of staffs allowed', '1', NULL, NULL, '2025-10-17 05:36:43', '2025-10-17 05:36:43'),
(8, 1, 8, 'Conversation ', 'conversations', 'Number of conversation allowed', '50', NULL, NULL, '2025-10-17 05:36:43', '2025-10-17 05:36:43'),
(9, 1, 9, 'Bot Flow ', 'bot_flow', 'Number of bot flows allowed', '1', NULL, NULL, '2025-10-17 05:36:43', '2025-10-17 05:36:43'),
(10, 1, 10, 'Enable Api ', 'enable_api', 'Enable(-1) or Disable(0) Api', '0', NULL, NULL, '2025-10-17 05:36:43', '2025-10-17 05:36:43'),
(11, 2, 1, 'Contacts ', 'contacts', NULL, '100000', NULL, NULL, '2025-11-12 04:06:57', '2025-11-12 04:06:57'),
(12, 2, 2, 'Template Bots ', 'template_bots', NULL, '100000', NULL, NULL, '2025-11-12 04:06:57', '2025-11-12 04:06:57'),
(13, 2, 3, 'Message Bots ', 'message_bots', NULL, '100000', NULL, NULL, '2025-11-12 04:06:57', '2025-11-12 04:06:57'),
(14, 2, 4, 'Campaigns ', 'campaigns', NULL, '100000', NULL, NULL, '2025-11-12 04:06:57', '2025-11-12 04:06:57'),
(15, 2, 5, 'AI Prompts ', 'ai_prompts', NULL, '100000', NULL, NULL, '2025-11-12 04:06:57', '2025-11-12 04:06:57'),
(16, 2, 6, 'Canned Replies ', 'canned_replies', NULL, '100000', NULL, NULL, '2025-11-12 04:06:57', '2025-11-12 04:06:57'),
(17, 2, 7, 'Staff ', 'staff', NULL, '100000', NULL, NULL, '2025-11-12 04:06:57', '2025-11-12 04:06:57'),
(18, 2, 8, 'Conversation ', 'conversations', NULL, '100000', NULL, NULL, '2025-11-12 04:06:57', '2025-11-12 04:06:57'),
(19, 2, 9, 'Bot Flow ', 'bot_flow', NULL, '100000', NULL, NULL, '2025-11-12 04:06:57', '2025-11-12 04:06:57'),
(20, 2, 10, 'Enable Api ', 'enable_api', NULL, '-1', NULL, NULL, '2025-11-12 04:06:57', '2025-11-12 04:06:57');

-- --------------------------------------------------------

--
-- Table structure for table `pusher_notifications`
--

CREATE TABLE `pusher_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `isread` int(11) NOT NULL DEFAULT 0,
  `isread_inline` tinyint(1) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL,
  `description` mediumtext NOT NULL,
  `fromuserid` int(11) NOT NULL,
  `fromclientid` int(11) NOT NULL DEFAULT 0,
  `from_fullname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `touserid` int(11) NOT NULL,
  `fromcompany` int(11) DEFAULT NULL,
  `link` longtext DEFAULT NULL,
  `additional_data` mediumtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `tenant_id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 1, 'Agent', 'web', '2025-11-12 03:39:58', '2025-11-12 03:39:58');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(98, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('2GPqxNPLBDxV7cAkZnAJSG372RthBmdvgUJb1uYT', NULL, '66.249.93.142', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUWNSZmtWcjcybEVkZVpPWjdpTjhnZmFPSFNGSmxRVkV0dngxcUZGNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYXBwLmNoYXR2b28uY29tL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1763106815),
('7EsHyjGQz51QjMtLsVVOcAt2UFXMiXJOAZo21cdL', NULL, '66.249.93.129', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQVZvRGM2OExDWm5QSkZTR0RjY1FGcmFBdmhzYTdsOHpzZXF5VEF3ZSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYXBwLmNoYXR2b28uY29tL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1763106731),
('7wUCWsX92zOquzGghHkmTt2THFqGNiUEAvj2LE2c', NULL, '66.249.93.142', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNFV1Z0RnWjJvWEFCeVduZUQ2YXVSUG51b3Z5c3I0dmV1UjlkcUtmRiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1MToiaHR0cHM6Ly9hcHAuY2hhdHZvby5jb20vYWJjL3NldHRpbmdzL2FpLWludGVncmF0aW9uIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTE6Imh0dHBzOi8vYXBwLmNoYXR2b28uY29tL2FiYy9zZXR0aW5ncy9haS1pbnRlZ3JhdGlvbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1763106814),
('B3NP2n8j4vOyBn3h8WLCvmGKI1QQYfRzB65QfDd5', NULL, '66.249.93.140', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicmR1dWJvZEkxNEpVVm1XbGVpOW5ySjcydGlWTXdsOUdWT3NHc1N3RSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYXBwLmNoYXR2b28uY29tL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1763106800),
('coOwuaJngPaJ7DN1iBQ67e2zvBWUvkLcXfadTrYD', NULL, '66.249.93.142', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSnFtVlBjc2lSZjdYdTBBVFc1RERzN2hQUUlwWlJNQUJEa05JYnVqdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYXBwLmNoYXR2b28uY29tL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1763106741),
('Fwa2lot39NCVuqLgE9Z3IBsEABQl7gMf3UrWSFto', NULL, '66.249.93.142', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieGdJOWRKNmZsQWFkSjdUcDJuRVlWWEc5N242VjRQZjd6R1h2Nzh2cSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYXBwLmNoYXR2b28uY29tL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1763106805),
('nbjk1dHVfYfusaGrDbTz0JHVOveDcKQwGPAyB3g4', NULL, '66.249.93.129', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVWhWVUVGamxNUDFtYnNxVUp1ajFjVVpFMzhDY20zTk9PdXFpUEs5dCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NToiaHR0cHM6Ly9hcHAuY2hhdHZvby5jb20vYWJjL3NldHRpbmdzL3N0b3AtYm90Ijt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDU6Imh0dHBzOi8vYXBwLmNoYXR2b28uY29tL2FiYy9zZXR0aW5ncy9zdG9wLWJvdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1763106804),
('nYmY0J39fwHNppZfNWe4Azr1pVnJGODPlbN8XZ3c', 1, '175.107.204.31', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il9mbGFzaCI7YToyOntzOjM6Im5ldyI7YTowOnt9czozOiJvbGQiO2E6MDp7fX1zOjY6Il90b2tlbiI7czo0MDoiU1BIdVdoUzFPZjdCeGdkV0ZGRllSa0E4VERmNmxkVVQ0SDU1bFAyViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHBzOi8vYXBwLmNoYXR2b28uY29tL3RoZW1lLXN0eWxlLWNzcz90PTE3NjMxMDcxMDg0ODAiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NjoibG9jYWxlIjtzOjI6ImVuIjtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJFNSTVZCdldHN0NycFl4ZDBZR3dOVGVEYUlaSWVHVlIub2psN3gzdE1PaFZzZ2Q1UkNPcmplIjt9', 1763107131),
('qYEQlx7qKxkcFp2yurt9QyqOcSMS9jwT82Kl3cfG', 2, '175.107.204.31', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6IlZMeURhTkVza01NNTVHMW1ZRnhFTm1DeVJGZW9WaWJtWXBGaVVvNXciO3M6ODoiYWRtaW5faWQiO2k6MTtzOjE3OiJjdXJyZW50X3RlbmFudF9pZCI7aToxO3M6MTI6ImN1cnJlbnRfdXNlciI7TzoxNToiQXBwXE1vZGVsc1xVc2VyIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJteXNxbCI7czo4OiIAKgB0YWJsZSI7czo1OiJ1c2VycyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czozOiJpbnQiO3M6MTI6ImluY3JlbWVudGluZyI7YjoxO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjIyOntzOjI6ImlkIjtzOjE6IjIiO3M6OToiZmlyc3RuYW1lIjtzOjg6Ik1VSEFNTUFEIjtzOjg6Imxhc3RuYW1lIjtzOjc6IlNBR0hFRVIiO3M6NToiZW1haWwiO3M6MjQ6Im1hbGlsc2hhbmkwMDk5QGdtYWlsLmNvbSI7czoxNzoiZW1haWxfdmVyaWZpZWRfYXQiO3M6MTk6IjIwMjUtMTEtMTEgMjI6NDA6MTIiO3M6ODoicGFzc3dvcmQiO3M6NjA6IiQyeSQxMiRCd3RwLkxKQXhpcEdXdWI5VUU0NDYuTHBBdUZ6aVEzaFFoeEdmSDNrOENhRDhEZGpRVWUzMiI7czo5OiJ0ZW5hbnRfaWQiO3M6MToiMSI7czo4OiJpc19hZG1pbiI7czoxOiIxIjtzOjc6InJvbGVfaWQiO047czo2OiJhdmF0YXIiO047czo1OiJwaG9uZSI7czoxMzoiKzkyMzQwMDg3ODY2NyI7czoxNjoiZGVmYXVsdF9sYW5ndWFnZSI7czoyOiJlbiI7czoxMDoiY291bnRyeV9pZCI7TjtzOjc6ImFkZHJlc3MiO047czo5OiJ1c2VyX3R5cGUiO3M6NjoidGVuYW50IjtzOjY6ImFjdGl2ZSI7czoxOiIxIjtzOjE3OiJzZW5kX3dlbGNvbWVfbWFpbCI7czoxOiIwIjtzOjEzOiJsYXN0X2xvZ2luX2F0IjtzOjE5OiIyMDI1LTExLTExIDIyOjQwOjEzIjtzOjIwOiJsYXN0X3Bhc3N3b3JkX2NoYW5nZSI7TjtzOjE0OiJyZW1lbWJlcl90b2tlbiI7czo2MDoiTEowcjZUT096WWJzSDVGM29vZ0UwTFBmQWhudmJEUkU3MXJrYmNnQWJkdkhuS3lGQ2Vnb3p4RUVPcGVqIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjE5OiIyMDI1LTExLTExIDIyOjQwOjA1IjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjE5OiIyMDI1LTExLTExIDIyOjQwOjEzIjt9czoxMToiACoAb3JpZ2luYWwiO2E6MjI6e3M6MjoiaWQiO3M6MToiMiI7czo5OiJmaXJzdG5hbWUiO3M6ODoiTVVIQU1NQUQiO3M6ODoibGFzdG5hbWUiO3M6NzoiU0FHSEVFUiI7czo1OiJlbWFpbCI7czoyNDoibWFsaWxzaGFuaTAwOTlAZ21haWwuY29tIjtzOjE3OiJlbWFpbF92ZXJpZmllZF9hdCI7czoxOToiMjAyNS0xMS0xMSAyMjo0MDoxMiI7czo4OiJwYXNzd29yZCI7czo2MDoiJDJ5JDEyJEJ3dHAuTEpBeGlwR1d1YjlVRTQ0Ni5McEF1RnppUTNoUWh4R2ZIM2s4Q2FEOERkalFVZTMyIjtzOjk6InRlbmFudF9pZCI7czoxOiIxIjtzOjg6ImlzX2FkbWluIjtzOjE6IjEiO3M6Nzoicm9sZV9pZCI7TjtzOjY6ImF2YXRhciI7TjtzOjU6InBob25lIjtzOjEzOiIrOTIzNDAwODc4NjY3IjtzOjE2OiJkZWZhdWx0X2xhbmd1YWdlIjtzOjI6ImVuIjtzOjEwOiJjb3VudHJ5X2lkIjtOO3M6NzoiYWRkcmVzcyI7TjtzOjk6InVzZXJfdHlwZSI7czo2OiJ0ZW5hbnQiO3M6NjoiYWN0aXZlIjtzOjE6IjEiO3M6MTc6InNlbmRfd2VsY29tZV9tYWlsIjtzOjE6IjAiO3M6MTM6Imxhc3RfbG9naW5fYXQiO3M6MTk6IjIwMjUtMTEtMTEgMjI6NDA6MTMiO3M6MjA6Imxhc3RfcGFzc3dvcmRfY2hhbmdlIjtOO3M6MTQ6InJlbWVtYmVyX3Rva2VuIjtzOjYwOiJMSjByNlRPT3pZYnNINUYzb29nRTBMUGZBaG52YkRSRTcxcmtiY2dBYmR2SG5LeUZDZWdvenhFRU9wZWoiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6MTk6IjIwMjUtMTEtMTEgMjI6NDA6MDUiO3M6MTA6InVwZGF0ZWRfYXQiO3M6MTk6IjIwMjUtMTEtMTEgMjI6NDA6MTMiO31zOjEwOiIAKgBjaGFuZ2VzIjthOjA6e31zOjExOiIAKgBwcmV2aW91cyI7YTowOnt9czo4OiIAKgBjYXN0cyI7YTo4OntzOjk6InRlbmFudF9pZCI7czozOiJpbnQiO3M6MTc6ImVtYWlsX3ZlcmlmaWVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjg6ImlzX2FkbWluIjtzOjQ6ImJvb2wiO3M6MTc6InNlbmRfd2VsY29tZV9tYWlsIjtzOjQ6ImJvb2wiO3M6Njoic3RhdHVzIjtzOjQ6ImJvb2wiO3M6MTM6Imxhc3RfbG9naW5fYXQiO3M6ODoiZGF0ZXRpbWUiO3M6MjA6Imxhc3RfcGFzc3dvcmRfY2hhbmdlIjtzOjg6ImRhdGV0aW1lIjtzOjg6InBhc3N3b3JkIjtzOjY6Imhhc2hlZCI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2VzVW5pcXVlSWRzIjtiOjA7czo5OiIAKgBoaWRkZW4iO2E6Mjp7aTowO3M6ODoicGFzc3dvcmQiO2k6MTtzOjE0OiJyZW1lbWJlcl90b2tlbiI7fXM6MTA6IgAqAHZpc2libGUiO2E6MDp7fXM6MTE6IgAqAGZpbGxhYmxlIjthOjE4OntpOjA7czo5OiJmaXJzdG5hbWUiO2k6MTtzOjg6Imxhc3RuYW1lIjtpOjI7czo1OiJlbWFpbCI7aTozO3M6ODoicGFzc3dvcmQiO2k6NDtzOjk6InRlbmFudF9pZCI7aTo1O3M6ODoiaXNfYWRtaW4iO2k6NjtzOjY6ImF2YXRhciI7aTo3O3M6NToicGhvbmUiO2k6ODtzOjEwOiJjb3VudHJ5X2lkIjtpOjk7czo3OiJhZGRyZXNzIjtpOjEwO3M6MjA6Imxhc3RfcGFzc3dvcmRfY2hhbmdlIjtpOjExO3M6MTc6InNlbmRfd2VsY29tZV9tYWlsIjtpOjEyO3M6NjoiYWN0aXZlIjtpOjEzO3M6OToidXNlcl90eXBlIjtpOjE0O3M6MTM6Imxhc3RfbG9naW5fYXQiO2k6MTU7czo3OiJyb2xlX2lkIjtpOjE2O3M6MTY6ImRlZmF1bHRfbGFuZ3VhZ2UiO2k6MTc7czoxNzoiZW1haWxfdmVyaWZpZWRfYXQiO31zOjEwOiIAKgBndWFyZGVkIjthOjE6e2k6MDtzOjE6IioiO31zOjE5OiIAKgBhdXRoUGFzc3dvcmROYW1lIjtzOjg6InBhc3N3b3JkIjtzOjIwOiIAKgByZW1lbWJlclRva2VuTmFtZSI7czoxNDoicmVtZW1iZXJfdG9rZW4iO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO3M6NjoibG9jYWxlIjtzOjI6ImVuIjtzOjY6Il9mbGFzaCI7YToyOntzOjM6Im5ldyI7YTowOnt9czozOiJvbGQiO2E6MDp7fX1zOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJEJ3dHAuTEpBeGlwR1d1YjlVRTQ0Ni5McEF1RnppUTNoUWh4R2ZIM2s4Q2FEOERkalFVZTMyIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNzoiaHR0cHM6Ly9hcHAuY2hhdHZvby5jb20vcHVibGljL2FwaS92MSI7fXM6Mzc6ImVuc3VyZV92YWxpZF90ZW5hbnRfc2Vzc2lvbl90ZW5hbnRfaWQiO2k6MTt9', 1763107426),
('rxVFXZkMSYj5MOInEE5bG52oOQoD8pRCMZpxGdNm', NULL, '66.249.93.139', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRkVrQTVoWElvbkVPc3ZnM1FXM3Q2bU5RQUtzTlhOOVEzWWI1Y0VEcSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1NToiaHR0cHM6Ly9hcHAuY2hhdHZvby5jb20vYWJjL3NldHRpbmdzL3doYXRzYXBwLWF1dG8tbGVhZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjU1OiJodHRwczovL2FwcC5jaGF0dm9vLmNvbS9hYmMvc2V0dGluZ3Mvd2hhdHNhcHAtYXV0by1sZWFkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1763106799),
('ZHNMmC49nYty5rg0sjoCjHRt30SvQtWxgXEBLkJu', NULL, '66.249.93.142', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicU9HYnRnUnJYd1Rqa3dPOHJGcVV2aE54ZTVsdlZqa0tmamZVSUppciI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NDoiaHR0cHM6Ly9hcHAuY2hhdHZvby5jb20vYWJjL2JvdC1mbG93cy9lZGl0LzIiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0NDoiaHR0cHM6Ly9hcHAuY2hhdHZvby5jb20vYWJjL2JvdC1mbG93cy9lZGl0LzIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1763106740),
('ZoZMWP65A4u814j8XQm4TqVkaODvj0R3Qb6trF6I', NULL, '66.249.93.142', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSFhTTUhRS1hzVmxQRlN4MG9WeEQzMDQ1MkN6bHNrVkpZZzU0S3R0MCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MToiaHR0cHM6Ly9hcHAuY2hhdHZvby5jb20vYWJjL2JvdC1mbG93LWxpc3QiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MToiaHR0cHM6Ly9hcHAuY2hhdHZvby5jb20vYWJjL2JvdC1mbG93LWxpc3QiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1763106730);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `group` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT 0,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `group`, `name`, `locked`, `payload`, `created_at`, `updated_at`) VALUES
(1, 'theme', 'site_logo', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(2, 'theme', 'favicon', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(3, 'theme', 'dark_logo', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(4, 'theme', 'cover_page_image', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(5, 'theme', 'pricing_section_title', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(6, 'theme', 'pricing_section_subtitle', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(7, 'theme', 'faq_section_title', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(8, 'theme', 'faq_section_subtitle', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(9, 'theme', 'author_name', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(10, 'theme', 'seo_meta_title', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(11, 'theme', 'seo_meta_keywords', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(12, 'theme', 'seo_meta_description', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(13, 'theme', 'og_title', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(14, 'theme', 'og_description', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(15, 'theme', 'customCss', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(16, 'theme', 'custom_js_header', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(17, 'theme', 'custom_js_footer', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(18, 'theme', 'partner_logos', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(19, 'theme', 'uni_feature_image', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(20, 'theme', 'uni_feature_title', 0, '\"Innovative Features\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(21, 'theme', 'uni_feature_sub_title', 0, '\"Unlocking New Possibilities with Cutting-Edge Technology\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(22, 'theme', 'uni_feature_description', 0, '\"Deliver great service experiences fast - without the complexity of traditional ITSM solutions.\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(23, 'theme', 'uni_feature_list', 0, '\"[\\\"Continuous integration and deployment\\\",\\\"Development workflow\\\",\\\"Knowledge management\\\"]\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(24, 'theme', 'feature_title', 0, '\"We invest in the world\\u2019s potential\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(25, 'theme', 'feature_subtitle', 0, '\"Deliver great service experiences fast - without the complexity of traditional ITSM solutions. Accelerate critical development work, eliminate toil, and deploy changes with ease.\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(26, 'theme', 'feature_description', 0, '\"Deliver great service experiences fast - without the complexity of traditional ITSM solutions.\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(27, 'theme', 'feature_list', 0, '\"[\\\"Dynamic reports and dashboards\\\",\\\"Templates for everyone\\\",\\\"Development workflow\\\",\\\"Limitless business automation\\\",\\\"Knowledge management\\\"]\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(28, 'theme', 'feature_image', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(29, 'theme', 'title', 0, '\"Scale your business with WhatsApp marketing automation\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(30, 'theme', 'description', 0, '\"Transform customer engagement with our powerful WhatsApp marketing platform. Send bulk messages, create chatbots, and automate conversations to boost sales and customer satisfaction.\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(31, 'theme', 'primary_button_text', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(32, 'theme', 'primary_button_url', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(33, 'theme', 'primary_button_type', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(34, 'theme', 'secondary_button_text', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(35, 'theme', 'secondary_button_url', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(36, 'theme', 'secondary_button_type', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(37, 'theme', 'image_path', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(38, 'theme', 'image_alt_text', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(39, 'theme', 'testimonials', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(40, 'system', 'site_name', 0, '\"Whatsmark saas\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(41, 'system', 'site_description', 0, '\"Whatsmark SaaS is a cloud-based platform for automating and managing WhatsApp marketing campaigns\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(42, 'system', 'timezone', 0, '\"UTC\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(43, 'system', 'date_format', 0, '\"Y-m-d\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(44, 'system', 'time_format', 0, '\"24\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(45, 'system', 'active_language', 0, '\"en\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(46, 'system', 'company_name', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(47, 'system', 'company_country_id', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(48, 'system', 'company_email', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(49, 'system', 'company_city', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(50, 'system', 'company_state', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(51, 'system', 'company_zip_code', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(52, 'system', 'company_address', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:36:24'),
(53, 're-captcha', 'isReCaptchaEnable', 0, 'false', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(54, 're-captcha', 'site_key', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(55, 're-captcha', 'secret_key', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(56, 'email', 'mailer', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(57, 'email', 'smtp_port', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(58, 'email', 'smtp_username', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(59, 'email', 'smtp_password', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(60, 'email', 'smtp_encryption', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(61, 'email', 'sender_name', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(62, 'email', 'sender_email', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(63, 'email', 'smtp_host', 0, '\"\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(64, 'cron-job', 'last_cron_run', 0, 'false', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(65, 'cron-job', 'status', 0, '\"unknown\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(66, 'cron-job', 'last_cron_stats', 0, '\"{}\"', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(67, 'cron-job', 'last_execution_time', 0, '0', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(68, 'cron-job', 'job_start_time', 0, '0', '2025-10-17 05:31:34', '2025-10-17 05:31:34'),
(69, 'whats-mark', 'wm_version', 0, '\"1.4.0\"', '2025-10-17 05:31:34', '2025-11-14 12:59:46'),
(70, 'whats-mark', 'wm_verification_id', 0, '\"NTg3MTQ5Njh8NzIyfGthZGtvZXJ8MjI5OGU0ZGEtZGY5Ny00MDY1LTk4ZWUtYmY1NzQ5OGFlZDk3\"', '2025-10-17 05:31:34', '2025-11-14 12:59:46'),
(71, 'whats-mark', 'wm_verification_token', 0, '\"NTg3MTQ5Njh8NzIyfGthZGtvZXJ8MjI5OGU0ZGEtZGY5Ny00MDY1LTk4ZWUtYmY1NzQ5OGFlZDk3|eyJwdXJjaGFzZV9jb2RlIjoiMjI5OGU0ZGEtZGY5Ny00MDY1LTk4ZWUtYmY1NzQ5OGFlZDk3IiwiaXRlbV9pZCI6NTg3MTQ5NjgsImJ1eWVyIjoia2Fka29lciIsInB1cmNoYXNlX2NvdW50IjoxLCJhY3RpdmF0ZWRfZG9tYWluIjoiaHR0cHM6XC9cL3doYXRzYXBwLnFycGF5dS5jb20iLCJpcCI6IjE1Ni4xNDYuNDUuMTEyIiwicHVyY2hhc2VfdGltZSI6IjIwMjUtMTAtMTdUMTU6MjA6MjMrMTE6MDAiLCJjaGVja19pbnRlcnZhbCI6MjU5MjAwLCJjcmVhdGVkX2F0IjoxNzYwNjc5MDYwfQ==.59c63abad89a37708a59811cb6ac22b0da21823f492bc357f143d7d2f0c82c23f8e6815ab8ae6b577ec5d397fcc06edea1ce491ef8ec88e2e62f190c590cb3e6\"', '2025-10-17 05:31:34', '2025-11-14 12:59:46'),
(72, 'whats-mark', 'wm_last_verification', 0, '\"2078639986\"', '2025-10-17 05:31:34', '2025-11-14 12:59:46'),
(73, 'whats-mark', 'wm_support_until', 0, '\"2026-04-18T05:20:23+10:00\"', '2025-10-17 05:31:34', '2025-11-14 12:59:46'),
(74, 'whats-mark', 'wm_validate', 0, 'true', '2025-10-17 05:31:34', '2025-11-14 12:59:46'),
(75, 'whats-mark', 'whatsmark_latest_version', 0, '\"1.0.0\"', '2025-10-17 05:31:34', '2025-11-14 12:59:46'),
(76, 'announcement', 'isEnable', 0, '\"\"', '2025-10-17 05:31:35', '2025-10-17 05:31:35'),
(77, 'announcement', 'message', 0, '\"\"', '2025-10-17 05:31:35', '2025-10-17 05:31:35'),
(78, 'announcement', 'link', 0, '\"\"', '2025-10-17 05:31:35', '2025-10-17 05:31:35'),
(79, 'announcement', 'link_text', 0, '\"\"', '2025-10-17 05:31:35', '2025-10-17 05:31:35'),
(80, 'announcement', 'background_color', 0, '\"\"', '2025-10-17 05:31:35', '2025-10-17 05:31:35'),
(81, 'announcement', 'link_text_color', 0, '\"\"', '2025-10-17 05:31:35', '2025-10-17 05:31:35'),
(82, 'announcement', 'message_color', 0, '\"\"', '2025-10-17 05:31:35', '2025-10-17 05:31:35'),
(83, 'payment', 'default_gateway', 0, '\"offline\"', '2025-10-17 05:31:36', '2025-10-17 05:31:36'),
(84, 'payment', 'offline_enabled', 0, 'true', '2025-10-17 05:31:36', '2025-10-17 05:31:36'),
(85, 'payment', 'offline_description', 0, '\"Pay via direct bank transfer.\"', '2025-10-17 05:31:36', '2025-10-17 05:31:36'),
(86, 'payment', 'offline_instructions', 0, '\"Please transfer the amount to our bank account and email the receipt.\"', '2025-10-17 05:31:36', '2025-10-17 05:31:36'),
(87, 'payment', 'stripe_enabled', 0, 'false', '2025-10-17 05:31:36', '2025-10-17 05:31:36'),
(88, 'payment', 'stripe_key', 0, '\"\"', '2025-10-17 05:31:36', '2025-10-17 05:31:36'),
(89, 'payment', 'stripe_secret', 0, '\"\"', '2025-10-17 05:31:36', '2025-10-17 05:31:36'),
(90, 'payment', 'stripe_webhook_secret', 0, '\"\"', '2025-10-17 05:31:36', '2025-10-17 05:31:36'),
(91, 'payment', 'stripe_webhook_id', 0, '\"\"', '2025-10-17 05:31:36', '2025-10-17 05:31:36'),
(92, 'payment', 'tax_enabled', 0, 'false', '2025-10-17 05:31:36', '2025-10-17 05:31:36'),
(93, 'tenant', 'isRegistrationEnabled', 0, 'true', '2025-10-17 05:31:36', '2025-10-17 05:31:36'),
(94, 'tenant', 'isVerificationEnabled', 0, 'false', '2025-10-17 05:31:36', '2025-10-17 05:31:36'),
(95, 'whatsapp', 'wm_fb_app_id', 0, '\"\"', '2025-10-17 05:31:36', '2025-11-12 03:38:45'),
(96, 'whatsapp', 'wm_fb_app_secret', 0, '\"\"', '2025-10-17 05:31:36', '2025-11-12 03:38:45'),
(97, 'whatsapp', 'is_webhook_connected', 0, '\"0\"', '2025-10-17 05:31:36', '2025-11-12 03:38:45'),
(98, 'whatsapp', 'api_version', 0, '\"v21.0\"', '2025-10-17 05:31:36', '2025-11-12 03:38:45'),
(99, 'whatsapp', 'daily_limit', 0, '\"1000\"', '2025-10-17 05:31:36', '2025-11-12 03:38:45'),
(100, 'whatsapp', 'webhook_verify_token', 0, '\"u0x3M76NvjLiAC5F\"', '2025-10-17 05:31:36', '2025-11-12 03:38:45'),
(101, 'whatsapp', 'queue', 0, '\"{\\\"name\\\":\\\"whatsapp-messages\\\",\\\"connection\\\":\\\"database\\\",\\\"retry_after\\\":180,\\\"timeout\\\":60}\"', '2025-10-17 05:31:36', '2025-11-12 03:38:45'),
(102, 'whatsapp', 'paths', 0, '\"{\\\"qrcodes\\\":\\\"\\\\\\/home\\\\\\/qrpayuco\\\\\\/whatsapp.qrpayu.com\\\\\\/storage\\\\\\/app\\\\\\/public\\\\\\/whatsapp\\\\\\/qrcodes\\\",\\\"media\\\":\\\"\\\\\\/home\\\\\\/qrpayuco\\\\\\/whatsapp.qrpayu.com\\\\\\/storage\\\\\\/app\\\\\\/public\\\\\\/whatsapp\\\\\\/media\\\"}\"', '2025-10-17 05:31:36', '2025-11-12 03:38:45'),
(103, 'whatsapp', 'logging', 0, '\"{\\\"enabled\\\":false,\\\"channel\\\":\\\"whatsapp\\\",\\\"level\\\":\\\"info\\\"}\"', '2025-10-17 05:31:36', '2025-11-12 03:38:45'),
(104, 'invoice', 'bank_name', 0, '\"\"', '2025-10-17 05:31:37', '2025-10-17 05:31:37'),
(105, 'invoice', 'account_name', 0, '\"\"', '2025-10-17 05:31:37', '2025-10-17 05:31:37'),
(106, 'invoice', 'account_number', 0, '\"\"', '2025-10-17 05:31:37', '2025-10-17 05:31:37'),
(107, 'invoice', 'ifsc_code', 0, '\"\"', '2025-10-17 05:31:37', '2025-10-17 05:31:37'),
(108, 'invoice', 'prefix', 0, '\"INV\"', '2025-10-17 05:31:37', '2025-10-17 05:31:37'),
(109, 'invoice', 'footer_text', 0, '\"Thanks for your purchase. Contact support for help.\"', '2025-10-17 05:31:37', '2025-10-17 05:31:37'),
(110, 'privacy-policy', 'title', 0, '\"Privacy Policy\"', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(111, 'privacy-policy', 'content', 0, '\"<h2>Privacy Policy<\\/h2><p>Your privacy is important to us. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our service.<\\/p><p>Please read this Privacy Policy carefully. If you do not agree with the terms of this Privacy Policy, please do not access the application.<\\/p>\"', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(112, 'privacy-policy', 'updated_at', 0, 'null', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(113, 'terms-conditions', 'title', 0, '\"Terms and Conditions\"', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(114, 'terms-conditions', 'content', 0, '\"<h2>Terms and Conditions<\\/h2><p>Please read these Terms and Conditions carefully before using our service. Your access to and use of the service is conditioned on your acceptance of and compliance with these Terms.<\\/p><p>By accessing or using the service you agree to be bound by these Terms. If you disagree with any part of the terms then you may not access the service.<\\/p>\"', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(115, 'terms-conditions', 'updated_at', 0, 'null', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(116, 'invoice', 'default_taxes', 0, '\"[]\"', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(117, 'payment', 'razorpay_enabled', 0, 'false', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(118, 'payment', 'razorpay_key_id', 0, '\"\"', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(119, 'payment', 'razorpay_key_secret', 0, '\"\"', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(120, 'payment', 'razorpay_webhook_secret', 0, '\"\"', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(121, 'tenant', 'isEmailConfirmationEnabled', 0, 'false', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(122, 'tenant', 'isEnableWelcomeEmail', 0, 'false', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(123, 'system', 'default_country_code', 0, '[]', '2025-10-17 05:31:38', '2025-10-17 05:36:24'),
(124, 'system', 'tables_pagination_limit', 0, '10', '2025-10-17 05:31:38', '2025-10-17 05:36:24'),
(125, 'theme', 'theme_style', 0, '\"{\\\"primary\\\":{\\\"valueStop\\\":600,\\\"lMax\\\":100,\\\"lMin\\\":0,\\\"hex\\\":\\\"#4f46e5\\\",\\\"swatches\\\":[{\\\"stop\\\":0,\\\"hex\\\":\\\"#FFFFFF\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":100},{\\\"stop\\\":50,\\\"hex\\\":\\\"#F2F2FD\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":97},{\\\"stop\\\":100,\\\"hex\\\":\\\"#E1E0FB\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":93},{\\\"stop\\\":200,\\\"hex\\\":\\\"#C3C0F6\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":86},{\\\"stop\\\":300,\\\"hex\\\":\\\"#A5A1F2\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":79},{\\\"stop\\\":400,\\\"hex\\\":\\\"#8782ED\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":72},{\\\"stop\\\":500,\\\"hex\\\":\\\"#6D67EA\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":66},{\\\"stop\\\":600,\\\"hex\\\":\\\"#4F46E5\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":58.6},{\\\"stop\\\":700,\\\"hex\\\":\\\"#241CC5\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":44},{\\\"stop\\\":800,\\\"hex\\\":\\\"#181282\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":29},{\\\"stop\\\":900,\\\"hex\\\":\\\"#0C0943\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":15},{\\\"stop\\\":950,\\\"hex\\\":\\\"#06041F\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":7},{\\\"stop\\\":1000,\\\"hex\\\":\\\"#000000\\\",\\\"h\\\":243,\\\"hScale\\\":0,\\\"s\\\":75.4,\\\"sScale\\\":0,\\\"l\\\":0}]},\\\"danger\\\":{\\\"valueStop\\\":600,\\\"lMax\\\":100,\\\"lMin\\\":0,\\\"hex\\\":\\\"#dc2626\\\",\\\"swatches\\\":[{\\\"stop\\\":0,\\\"hex\\\":\\\"#FFFFFF\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":100},{\\\"stop\\\":50,\\\"hex\\\":\\\"#FCEDED\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":96},{\\\"stop\\\":100,\\\"hex\\\":\\\"#F9DCDC\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":92},{\\\"stop\\\":200,\\\"hex\\\":\\\"#F4B9B9\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":84},{\\\"stop\\\":300,\\\"hex\\\":\\\"#ED9191\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":75},{\\\"stop\\\":400,\\\"hex\\\":\\\"#E86E6E\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":67},{\\\"stop\\\":500,\\\"hex\\\":\\\"#E24B4B\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":59},{\\\"stop\\\":600,\\\"hex\\\":\\\"#DC2626\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":50.6},{\\\"stop\\\":700,\\\"hex\\\":\\\"#A71B1B\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":38},{\\\"stop\\\":800,\\\"hex\\\":\\\"#6E1212\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":25},{\\\"stop\\\":900,\\\"hex\\\":\\\"#390909\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":13},{\\\"stop\\\":950,\\\"hex\\\":\\\"#1A0404\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":6},{\\\"stop\\\":1000,\\\"hex\\\":\\\"#000000\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":72.2,\\\"sScale\\\":0,\\\"l\\\":0}]},\\\"warning\\\":{\\\"valueStop\\\":600,\\\"lMax\\\":100,\\\"lMin\\\":0,\\\"hex\\\":\\\"#eab308\\\",\\\"swatches\\\":[{\\\"stop\\\":0,\\\"hex\\\":\\\"#FFFFFF\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":100},{\\\"stop\\\":50,\\\"hex\\\":\\\"#fefce8\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":95},{\\\"stop\\\":100,\\\"hex\\\":\\\"#fef9c3\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":90},{\\\"stop\\\":200,\\\"hex\\\":\\\"#fef08a\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":80},{\\\"stop\\\":300,\\\"hex\\\":\\\"#fde047\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":70},{\\\"stop\\\":400,\\\"hex\\\":\\\"#facc15\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":60},{\\\"stop\\\":500,\\\"hex\\\":\\\"#eab308\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":50},{\\\"stop\\\":600,\\\"hex\\\":\\\"#eab308\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":40.4},{\\\"stop\\\":700,\\\"hex\\\":\\\"#a16207\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":30},{\\\"stop\\\":800,\\\"hex\\\":\\\"#854d0e\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":20},{\\\"stop\\\":900,\\\"hex\\\":\\\"#713f12\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":10},{\\\"stop\\\":950,\\\"hex\\\":\\\"#191100\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":5},{\\\"stop\\\":1000,\\\"hex\\\":\\\"#000000\\\",\\\"h\\\":41,\\\"hScale\\\":0,\\\"s\\\":96.1,\\\"sScale\\\":0,\\\"l\\\":0}]},\\\"success\\\":{\\\"valueStop\\\":600,\\\"lMax\\\":100,\\\"lMin\\\":0,\\\"hex\\\":\\\"#16a34a\\\",\\\"swatches\\\":[{\\\"stop\\\":0,\\\"hex\\\":\\\"#FFFFFF\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":100},{\\\"stop\\\":50,\\\"hex\\\":\\\"#E9FCF0\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":95},{\\\"stop\\\":100,\\\"hex\\\":\\\"#CEF8DD\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":89},{\\\"stop\\\":200,\\\"hex\\\":\\\"#A1F2BF\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":79},{\\\"stop\\\":300,\\\"hex\\\":\\\"#6FEC9D\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":68},{\\\"stop\\\":400,\\\"hex\\\":\\\"#42E67E\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":58},{\\\"stop\\\":500,\\\"hex\\\":\\\"#1DD35F\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":47},{\\\"stop\\\":600,\\\"hex\\\":\\\"#16A34A\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":36.3},{\\\"stop\\\":700,\\\"hex\\\":\\\"#107937\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":27},{\\\"stop\\\":800,\\\"hex\\\":\\\"#0B5125\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":18},{\\\"stop\\\":900,\\\"hex\\\":\\\"#052812\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":9},{\\\"stop\\\":950,\\\"hex\\\":\\\"#03160A\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":5},{\\\"stop\\\":1000,\\\"hex\\\":\\\"#000000\\\",\\\"h\\\":142,\\\"hScale\\\":0,\\\"s\\\":76.2,\\\"sScale\\\":0,\\\"l\\\":0}]},\\\"info\\\":{\\\"valueStop\\\":600,\\\"lMax\\\":100,\\\"lMin\\\":0,\\\"hex\\\":\\\"#0284c7\\\",\\\"swatches\\\":[{\\\"stop\\\":0,\\\"hex\\\":\\\"#FFFFFF\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":100},{\\\"stop\\\":50,\\\"hex\\\":\\\"#E6F6FF\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":95},{\\\"stop\\\":100,\\\"hex\\\":\\\"#CDEEFE\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":90},{\\\"stop\\\":200,\\\"hex\\\":\\\"#9ADDFE\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":80},{\\\"stop\\\":300,\\\"hex\\\":\\\"#68CBFD\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":70},{\\\"stop\\\":400,\\\"hex\\\":\\\"#35BAFD\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":60},{\\\"stop\\\":500,\\\"hex\\\":\\\"#03A9FC\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":50},{\\\"stop\\\":600,\\\"hex\\\":\\\"#0284C7\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":39.4},{\\\"stop\\\":700,\\\"hex\\\":\\\"#026597\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":30},{\\\"stop\\\":800,\\\"hex\\\":\\\"#014465\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":20},{\\\"stop\\\":900,\\\"hex\\\":\\\"#012232\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":10},{\\\"stop\\\":950,\\\"hex\\\":\\\"#001119\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":5},{\\\"stop\\\":1000,\\\"hex\\\":\\\"#000000\\\",\\\"h\\\":200,\\\"hScale\\\":0,\\\"s\\\":98,\\\"sScale\\\":0,\\\"l\\\":0}]},\\\"neutral\\\":{\\\"valueStop\\\":600,\\\"lMax\\\":100,\\\"lMin\\\":0,\\\"hex\\\":\\\"#525252\\\",\\\"swatches\\\":[{\\\"stop\\\":0,\\\"hex\\\":\\\"#FFFFFF\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":100},{\\\"stop\\\":50,\\\"hex\\\":\\\"#F0F0F0\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":94},{\\\"stop\\\":100,\\\"hex\\\":\\\"#E3E3E3\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":89},{\\\"stop\\\":200,\\\"hex\\\":\\\"#C4C4C4\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":77},{\\\"stop\\\":300,\\\"hex\\\":\\\"#A8A8A8\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":66},{\\\"stop\\\":400,\\\"hex\\\":\\\"#8C8C8C\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":55},{\\\"stop\\\":500,\\\"hex\\\":\\\"#707070\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":44},{\\\"stop\\\":600,\\\"hex\\\":\\\"#525252\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":32.2},{\\\"stop\\\":700,\\\"hex\\\":\\\"#3D3D3D\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":24},{\\\"stop\\\":800,\\\"hex\\\":\\\"#292929\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":16},{\\\"stop\\\":900,\\\"hex\\\":\\\"#141414\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":8},{\\\"stop\\\":950,\\\"hex\\\":\\\"#0A0A0A\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":4},{\\\"stop\\\":1000,\\\"hex\\\":\\\"#000000\\\",\\\"h\\\":0,\\\"hScale\\\":0,\\\"s\\\":0,\\\"sScale\\\":0,\\\"l\\\":0}]},\\\"secondary\\\":{\\\"valueStop\\\":600,\\\"lMax\\\":100,\\\"lMin\\\":0,\\\"hex\\\":\\\"#4b5563\\\",\\\"swatches\\\":[{\\\"stop\\\":0,\\\"hex\\\":\\\"#FFFFFF\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":100},{\\\"stop\\\":50,\\\"hex\\\":\\\"#F0F2F4\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":95},{\\\"stop\\\":100,\\\"hex\\\":\\\"#DFE2E7\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":89},{\\\"stop\\\":200,\\\"hex\\\":\\\"#BFC6CF\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":78},{\\\"stop\\\":300,\\\"hex\\\":\\\"#9FA9B6\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":67},{\\\"stop\\\":400,\\\"hex\\\":\\\"#7F8C9E\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":56},{\\\"stop\\\":500,\\\"hex\\\":\\\"#637083\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":45},{\\\"stop\\\":600,\\\"hex\\\":\\\"#4B5563\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":34.1},{\\\"stop\\\":700,\\\"hex\\\":\\\"#39414B\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":26},{\\\"stop\\\":800,\\\"hex\\\":\\\"#252A31\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":17},{\\\"stop\\\":900,\\\"hex\\\":\\\"#14161A\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":9},{\\\"stop\\\":950,\\\"hex\\\":\\\"#090A0C\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":4},{\\\"stop\\\":1000,\\\"hex\\\":\\\"#000000\\\",\\\"h\\\":215,\\\"hScale\\\":0,\\\"s\\\":13.8,\\\"sScale\\\":0,\\\"l\\\":0}]}}\"', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(126, 'theme', 'theme_style_modified_at', 0, 'null', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(127, 'whatsapp', 'wm_fb_config_id', 0, '\"\"', '2025-10-17 05:31:38', '2025-11-12 03:38:45'),
(128, 'system', 'is_enable_landing_page', 0, 'true', '2025-10-17 05:31:38', '2025-10-17 05:36:24'),
(129, 'tenant', 'set_default_tenant_language', 0, '\"en\"', '2025-10-17 05:31:38', '2025-10-17 05:31:38'),
(217, 'payment', 'paypal_enabled', 0, 'false', '2025-10-17 05:37:57', '2025-10-17 05:37:57'),
(218, 'payment', 'paypal_mode', 0, '\"sandbox\"', '2025-10-17 05:37:57', '2025-10-17 05:37:57'),
(219, 'payment', 'paypal_client_id', 0, '\"\"', '2025-10-17 05:37:57', '2025-10-17 05:37:57'),
(220, 'payment', 'paypal_client_secret', 0, '\"\"', '2025-10-17 05:37:57', '2025-10-17 05:37:57'),
(221, 'payment', 'paypal_webhook_id', 0, '\"\"', '2025-10-17 05:37:57', '2025-10-17 05:37:57'),
(222, 'payment', 'paypal_brand_name', 0, '\"WhatsMarks\"', '2025-10-17 05:37:57', '2025-10-17 05:37:57'),
(223, 'system', 'max_queue_jobs', 0, '\"100\"', '2025-10-17 05:37:58', '2025-10-17 05:37:58'),
(231, 'paystack', 'enabled', 0, 'false', '2025-10-17 05:38:37', '2025-10-17 05:38:37'),
(232, 'paystack', 'public_key', 0, '\"\"', '2025-10-17 05:38:37', '2025-10-17 05:38:37'),
(233, 'paystack', 'secret_key', 0, '\"\"', '2025-10-17 05:38:37', '2025-10-17 05:38:37'),
(248, 'theme', 'hero_heading', 0, '\"Empower Your Business with Our Smart Solutions\"', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(249, 'theme', 'feature_two_enabled', 0, 'true', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(250, 'theme', 'feature_title_two', 0, '\"No-code, drag-and-drop Bot Flow Builder\"', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(251, 'theme', 'feature_subtitle_two', 0, '\"Create AI-powered WhatsApp chatbots easily with our no-code drag & drop flow builder. Automate chats, capture leads, and engage customers 24 7.\"', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(252, 'theme', 'feature_description_two', 0, '\"all without writing a single line of code.\"', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(253, 'theme', 'feature_list_two', 0, '[\"Free up your team! Automatically reply to customer messages on WhatsApp using AI.\",\"Transfer complex cases to human agents for better support and customer experience.\",\"Automatically trigger custom message flows based on keywords messages for maximum engagement.\",\"Keep your WhatsApp active round-the-clock with automated responses and lead collection.\"]', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(254, 'theme', 'feature_image_two', 0, '\"\"', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(255, 'theme', 'feature_three_enabled', 0, 'true', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(256, 'theme', 'feature_title_three', 0, '\"Ecommerce Integration & Personal AI Assistant & Knowledgebase\"', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(257, 'theme', 'feature_subtitle_three', 0, '\"no team require, ai will manage it everything\"', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(258, 'theme', 'feature_description_three', 0, '\"Trigger WhatsApp messages instantly with ecommerce events. Automate cart, checkout, and payment notifications for higher engagement and conversions.\"', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(259, 'theme', 'feature_list_three', 0, '[\"Automate WhatsApp Notifications with Ecommerce Webhooks\",\"AI WhatsApp Assistant | Automate Conversations & Support\",\"Engage Customers Smarter with Personal AI Assistant & Knowledgebase\",\"Answer common queries instantly with AI-powered responses.\"]', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(260, 'theme', 'feature_image_three', 0, '\"\"', '2025-10-17 05:40:03', '2025-10-17 05:40:03'),
(268, 'api', 'enable_api', 0, 'false', '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(269, 'api', 'api_token', 0, '\"\"', '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(270, 'api', 'abilities', 0, '[]', '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(271, 'api', 'last_used_at', 0, '\"\"', '2025-10-17 05:40:52', '2025-10-17 05:40:52'),
(272, 'api', 'token_generated_at', 0, '\"\"', '2025-10-17 05:40:52', '2025-10-17 05:40:52');

-- --------------------------------------------------------

--
-- Table structure for table `sources`
--

CREATE TABLE `sources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sources`
--

INSERT INTO `sources` (`id`, `tenant_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 'facebook', '2025-11-12 03:39:58', '2025-11-12 03:39:58'),
(2, 1, 'whatsapp', '2025-11-12 03:39:58', '2025-11-12 03:39:58');

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#28B8DA',
  `isdefault` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `tenant_id`, `name`, `color`, `isdefault`, `created_at`, `updated_at`) VALUES
(1, 1, 'New', '#4CAF50', 1, '2025-11-12 03:39:58', '2025-11-12 03:39:58'),
(2, 1, 'In Progress', '#2196F3', 0, '2025-11-12 03:39:58', '2025-11-12 03:39:58'),
(3, 1, 'Contacted', '#FFC107', 0, '2025-11-12 03:39:58', '2025-11-12 03:39:58'),
(4, 1, 'Qualified', '#9C27B0', 0, '2025-11-12 03:39:58', '2025-11-12 03:39:58'),
(5, 1, 'Closed', '#F44336', 0, '2025-11-12 03:39:58', '2025-11-12 03:39:58');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('new','active','ended','cancelled','terminated','trial','paused') NOT NULL DEFAULT 'new',
  `current_period_ends_at` timestamp NULL DEFAULT NULL,
  `trial_starts_at` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT 1,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `terminated_at` timestamp NULL DEFAULT NULL,
  `canceled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `payment_attempt_count` int(11) NOT NULL DEFAULT 0,
  `last_payment_attempt_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `tenant_id`, `plan_id`, `status`, `current_period_ends_at`, `trial_starts_at`, `trial_ends_at`, `is_recurring`, `cancelled_at`, `ended_at`, `terminated_at`, `canceled_at`, `cancellation_reason`, `payment_attempt_count`, `last_payment_attempt_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'active', '2025-12-12 04:08:38', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-11-12 03:40:13', '2025-11-12 04:08:38');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_logs`
--

CREATE TABLE `subscription_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `transaction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_logs`
--

INSERT INTO `subscription_logs` (`id`, `subscription_id`, `type`, `description`, `transaction_id`, `data`, `created_at`, `updated_at`) VALUES
(1, 1, 'updated', NULL, NULL, '\"{\\\"plan\\\":\\\"ABC XYZ\\\",\\\"price\\\":\\\"30.00\\\"}\"', '2025-11-12 04:07:18', '2025-11-12 04:07:18'),
(2, 1, 'activated', NULL, NULL, '\"{\\\"plan\\\":\\\"ABC XYZ\\\",\\\"price\\\":\\\"30.00\\\"}\"', '2025-11-12 04:08:38', '2025-11-12 04:08:38');

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `rate` decimal(8,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `taxes`
--

INSERT INTO `taxes` (`id`, `name`, `rate`, `description`, `created_at`, `updated_at`) VALUES
(1, 'CGST', 9.00, 'Standard CGST rate (9%)', '2025-10-17 05:36:43', '2025-10-17 05:36:43'),
(2, 'SGST', 9.00, 'Standard SGST rate (9%)', '2025-10-17 05:36:43', '2025-10-17 05:36:43');

-- --------------------------------------------------------

--
-- Table structure for table `template_bots`
--

CREATE TABLE `template_bots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rel_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `header_params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body_params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `footer_params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trigger` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reply_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `is_bot_active` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sending_count` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL COMMENT 'Custom domain if available',
  `subdomain` varchar(255) NOT NULL COMMENT 'Subdomain for tenant access',
  `stripe_customer_id` text DEFAULT NULL,
  `status` enum('active','deactive','suspended') NOT NULL DEFAULT 'active',
  `custom_colors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Tenant UI customization colors' CHECK (json_valid(`custom_colors`)),
  `timezone` varchar(255) DEFAULT 'UTC',
  `has_custom_domain` tinyint(1) DEFAULT 0,
  `features_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Tenant-specific feature configuration' CHECK (json_valid(`features_config`)),
  `address` text DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`)),
  `billing_name` varchar(255) DEFAULT NULL,
  `billing_email` varchar(255) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `billing_city` varchar(255) DEFAULT NULL,
  `billing_state` varchar(255) DEFAULT NULL,
  `billing_zip_code` varchar(255) DEFAULT NULL,
  `billing_country` varchar(255) DEFAULT NULL,
  `billing_phone` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `company_name`, `domain`, `subdomain`, `stripe_customer_id`, `status`, `custom_colors`, `timezone`, `has_custom_domain`, `features_config`, `address`, `country_id`, `payment_method`, `payment_details`, `billing_name`, `billing_email`, `billing_address`, `billing_city`, `billing_state`, `billing_zip_code`, `billing_country`, `billing_phone`, `expires_at`, `created_at`, `updated_at`, `deleted_date`) VALUES
(1, 'Abc', NULL, 'abc', NULL, 'active', NULL, 'UTC', 0, NULL, 'DHA PHASE 7 NEAR DOSTIGHAR KARACHI', 167, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 03:39:58', '2025-11-12 03:39:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tenant_credit_balances`
--

CREATE TABLE `tenant_credit_balances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `balance` decimal(13,2) NOT NULL DEFAULT 0.00,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant_credit_balances`
--

INSERT INTO `tenant_credit_balances` (`id`, `tenant_id`, `balance`, `currency_id`, `created_at`, `updated_at`) VALUES
(1, 1, 0.00, 1, '2025-11-12 04:07:20', '2025-11-12 04:07:20');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_email_templates`
--

CREATE TABLE `tenant_email_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext DEFAULT NULL,
  `merge_fields_groups` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`merge_fields_groups`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `layout_id` int(10) UNSIGNED DEFAULT NULL,
  `use_layout` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant_email_templates`
--

INSERT INTO `tenant_email_templates` (`id`, `tenant_id`, `name`, `slug`, `description`, `subject`, `content`, `merge_fields_groups`, `is_active`, `is_system`, `category`, `type`, `layout_id`, `use_layout`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Email Confirmation', 'tenant-email-confirmation', NULL, 'Email Confirmation', '<p>Thank you for signing up with {company_name}, {first_name} {last_name}!</p><p>We\'re thrilled to have you on board. Before you get started, we need to verify your email address to ensure the security of your account.</p><p>Please click the button below to verify your email:</p><p><br></p><p> {verification_url}</p><p><br></p><p>Thank you.</p>', '[\"tenant-other-group\",\"tenant-user-group\"]', 1, 0, NULL, 'tenant', 1, 1, NULL, NULL, NULL, NULL),
(2, 1, 'Welcome Email', 'tenants-welcome-mail', NULL, 'Welcome to {company_name}!', '<p>Dear {first_name} {last_name},</p><p>Welcome to {company_name}! We\'re excited to have you on board. 🚀</p><p>Get ready to explore our amazing features and make your life easier.</p><p>If you have any questions, our support team at <a href=\"mailto:{company_email}\">{company_email}</a> is always here to help.</p><p>Start your journey here: <a href=\"{base_url}\">{base_url}</a></p><p>Looking forward to seeing you thrive!</p>', '[\"tenant-other-group\",\"tenant-user-group\"]', 1, 0, NULL, 'tenant', 1, 1, NULL, NULL, NULL, NULL),
(3, 1, 'Password Reset', 'tenant-password-reset', NULL, 'Password Reset Request', '<p>Hello {first_name} {last_name},</p><p>We received a request to reset your password for your {company_name} account.</p><p>If you made this request, click the button below to reset your password:</p><p><a href=\"{reset_url}\" rel=\"noopener noreferrer\" target=\"_blank\">{reset_url}</a></p><p>If you did not request a password reset, please ignore this email or contact support at <a href=\"mailto:{company_email}\" rel=\"noopener noreferrer\" target=\"_blank\">{company_email}</a>.</p>', '[\"tenant-other-group\",\"tenant-user-group\"]', 1, 0, NULL, 'tenant', 1, 1, NULL, NULL, NULL, NULL),
(4, 1, 'New Contact Assigned', 'tenant-new-contact-assigned', NULL, '📌 New Contact Assigned to You', '<p>Hi {first_name} {last_name},</p><p>A new contact has been assigned to you. Here are the details:</p><ol><li data-list=\"bullet\"><span class=\"ql-ui\" contenteditable=\"false\"></span><strong>Contact Name:</strong> {contact_first_name} {contact_last_name}</li><li data-list=\"bullet\"><span class=\"ql-ui\" contenteditable=\"false\"></span><strong>Email:</strong> {contact_email}</li><li data-list=\"bullet\"><span class=\"ql-ui\" contenteditable=\"false\"></span><strong>Phone:</strong> {contact_phone_number}</li><li data-list=\"bullet\"><span class=\"ql-ui\" contenteditable=\"false\"></span><strong>Assigned By:</strong> {assigned_by}</li></ol><p>Please reach out to them promptly and ensure a smooth follow-up.</p><p>If you have any questions, feel free to get in touch.</p><p><strong>Best regards,</strong></p><p> {company_name}</p>', '[\"tenant-other-group\",\"tenant-user-group\",\"tenant-contact-group\"]', 1, 0, NULL, 'tenant', 1, 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tenant_languages`
--

CREATE TABLE `tenant_languages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(3) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenant_languages`
--

INSERT INTO `tenant_languages` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'Portuguese', 'br', '2025-10-17 05:36:48', '2025-10-17 05:36:48');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_settings`
--

CREATE TABLE `tenant_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenant_settings`
--

INSERT INTO `tenant_settings` (`id`, `tenant_id`, `group`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, '1', 'system', 'active_language', '\"en\"', '2025-11-12 03:39:58', '2025-11-12 03:39:58'),
(2, '1', 'system', 'timezone', '\"UTC\"', '2025-11-12 03:40:04', '2025-11-12 03:40:04'),
(3, '1', 'system', 'date_format', '\"Y-m-d\"', '2025-11-12 03:40:04', '2025-11-12 03:40:04'),
(4, '1', 'system', 'time_format', '\"12\"', '2025-11-12 03:40:04', '2025-11-12 03:40:04'),
(5, '1', 'pusher', 'app_id', '\"2076453\"', '2025-11-12 03:40:04', '2025-11-12 03:53:15'),
(6, '1', 'pusher', 'app_key', '\"dc0ff7779ab4fba58cd8\"', '2025-11-12 03:40:04', '2025-11-12 03:53:15'),
(7, '1', 'pusher', 'app_secret', '\"2bcc3e86ac4121415e32\"', '2025-11-12 03:40:04', '2025-11-12 03:53:15'),
(8, '1', 'pusher', 'cluster', '\"ap1\"', '2025-11-12 03:40:04', '2025-11-12 03:53:15'),
(9, '1', 'pusher', 'real_time_notify', 'true', '2025-11-12 03:40:04', '2025-11-12 03:53:15'),
(10, '1', 'pusher', 'desk_notify', 'true', '2025-11-12 03:40:04', '2025-11-12 03:53:15'),
(11, '1', 'pusher', 'dismiss_desk_notification', '\"5\"', '2025-11-12 03:40:04', '2025-11-12 03:53:15'),
(12, '1', 'dynamic_tables', 'tenant_table_names', '\"{\\\"chats\\\":\\\"abc_chats\\\",\\\"chat_messages\\\":\\\"abc_chat_messages\\\",\\\"contacts\\\":\\\"abc_contacts\\\",\\\"contact_notes\\\":\\\"abc_contact_notes\\\"}\"', '2025-11-12 03:40:04', '2025-11-12 03:40:04'),
(13, '1', 'miscellaneous', 'tables_pagination_limit', '10', '2025-11-12 03:40:04', '2025-11-12 03:40:04'),
(14, '1', 'whatsapp', 'logging', '\"{\\\"enabled\\\":false,\\\"channel\\\":\\\"whatsapp\\\",\\\"level\\\":\\\"info\\\"}\"', '2025-11-12 03:40:04', '2025-11-12 03:40:04'),
(17, '1', 'whatsapp', 'is_webhook_connected', '1', '2025-11-12 03:43:12', '2025-11-12 04:01:42'),
(19, '1', 'whatsapp', 'wm_fb_app_id', '\"28116551107993669\"', '2025-11-12 03:43:50', '2025-11-12 03:43:50'),
(20, '1', 'whatsapp', 'wm_fb_app_secret', '\"16dc6ef623cdc7182f85b0779ae7652c\"', '2025-11-12 03:43:50', '2025-11-12 03:43:50'),
(26, '1', 'whats-mark', 'enable_chat_notification_sound', 'true', '2025-11-12 03:55:11', '2025-11-12 03:55:11'),
(35, '1', 'whatsapp', 'wm_business_account_id', '\"1947223819397152\"', '2025-11-12 04:01:18', '2025-11-12 04:01:32'),
(36, '1', 'whatsapp', 'wm_access_token', '\"EAGPj2rEZA4EUBPx4kZCvPK9MM3TZCTnKBuHhs2OK7sHZCB6pFqBcIAxWXgB4lMQJSTEjDTQS7ZCKOC9w6qv9edxDkeyuE9ZAh7NjWznfaPoqKEIdLu6ilf6vhhQ6Y1ZAXVZBuDjwLFfQg9vPlREZAILqGQBhkUpvdzCaKS7W93RfuDRs6qm33qx45zhNt9umDDvsjpgZDZD\"', '2025-11-12 04:01:18', '2025-11-12 04:01:32'),
(37, '1', 'whatsapp', 'is_whatsmark_connected', '1', '2025-11-12 04:01:18', '2025-11-12 04:01:42'),
(38, '1', 'whatsapp', 'wm_default_phone_number', '\"923324538746\"', '2025-11-12 04:01:18', '2025-11-12 04:01:44'),
(39, '1', 'whatsapp', 'wm_default_phone_number_id', '\"717118924823378\"', '2025-11-12 04:01:18', '2025-11-12 04:01:44'),
(40, '1', 'whatsapp', 'wm_health_check_time', '\"Tuesday 11th November 2025 11:01:44 pm\"', '2025-11-12 04:01:18', '2025-11-12 04:01:44'),
(41, '1', 'whatsapp', 'wm_health_data', '\"{\\\"health_status\\\":{\\\"can_send_message\\\":\\\"LIMITED\\\",\\\"entities\\\":[{\\\"entity_type\\\":\\\"WABA\\\",\\\"id\\\":\\\"1947223819397152\\\",\\\"can_send_message\\\":\\\"AVAILABLE\\\"},{\\\"entity_type\\\":\\\"BUSINESS\\\",\\\"id\\\":\\\"1829128007858975\\\",\\\"can_send_message\\\":\\\"LIMITED\\\",\\\"errors\\\":[{\\\"error_code\\\":141010,\\\"error_description\\\":\\\"The Business has not passed business verification.\\\",\\\"possible_solution\\\":\\\"Visit business settings and start or resolve the business verification request.\\\"}]},{\\\"entity_type\\\":\\\"APP\\\",\\\"id\\\":\\\"28116551107993669\\\",\\\"can_send_message\\\":\\\"AVAILABLE\\\"}]},\\\"id\\\":\\\"1947223819397152\\\"}\"', '2025-11-12 04:01:18', '2025-11-12 04:01:44'),
(42, '1', 'whatsapp', 'wm_profile_picture_url', '\"https:\\/\\/pps.whatsapp.net\\/v\\/t61.24694-24\\/563775894_2016395515797375_1336450878558998020_n.jpg?ccb=11-4&oh=01_Q5Aa3AGCoNcJs06H6Q2TEdz7oQh3sK1OgcZ53eiESKzsrNIuFg&oe=691791DC&_nc_sid=5e03e0&_nc_cat=107\"', '2025-11-12 04:01:18', '2025-11-12 04:04:59'),
(43, '1', 'whats-mark', 'auto_lead_enabled', 'true', '2025-11-12 04:12:50', '2025-11-12 04:12:50'),
(44, '1', 'whats-mark', 'lead_status', '\"1\"', '2025-11-12 04:12:51', '2025-11-12 04:12:51'),
(45, '1', 'whats-mark', 'lead_source', '\"2\"', '2025-11-12 04:12:51', '2025-11-12 04:12:51'),
(46, '1', 'whats-mark', 'lead_assigned_to', NULL, '2025-11-12 04:12:51', '2025-11-12 04:12:51');

-- --------------------------------------------------------

--
-- Table structure for table `test_users`
--

CREATE TABLE `test_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `folder` varchar(191) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `version` varchar(255) DEFAULT NULL,
  `theme_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payload` longtext DEFAULT NULL,
  `theme_html` longtext DEFAULT NULL,
  `theme_css` longtext DEFAULT NULL,
  `type` enum('core','custom') NOT NULL DEFAULT 'core'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tenant_staff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `assignee_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('open','answered','closed','on_hold') NOT NULL DEFAULT 'open',
  `ticket_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_viewed` tinyint(1) NOT NULL DEFAULT 0,
  `tenant_viewed` tinyint(1) NOT NULL DEFAULT 1,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_replies`
--

CREATE TABLE `ticket_replies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_type` enum('admin','tenant','tenant_staff','system') NOT NULL DEFAULT 'tenant',
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `viewed` tinyint(1) NOT NULL DEFAULT 0,
  `send_notification` tinyint(1) DEFAULT 0,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `payment_method_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `amount` decimal(13,2) NOT NULL,
  `idempotency_key` text DEFAULT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `error` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `invoice_id`, `payment_method_id`, `type`, `status`, `amount`, `idempotency_key`, `currency_id`, `description`, `error`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, 'offline', 'success', 30.00, NULL, 1, 'Payment for Invoice', NULL, '{\"payment_reference\":\"76575675676757\",\"payment_date\":\"2025-11-11\",\"payment_method\":\"Bank Transfer\",\"payment_details\":\"7567567567\"}', '2025-11-12 04:07:40', '2025-11-12 04:08:38');

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

CREATE TABLE `uploaded_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `firstname` varchar(255) NOT NULL COMMENT 'User first name',
  `lastname` varchar(255) NOT NULL COMMENT 'User last name',
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Tenant ID',
  `is_admin` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether user is a super admin',
  `role_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Role ID',
  `avatar` varchar(255) DEFAULT NULL COMMENT 'User profile image',
  `phone` varchar(255) DEFAULT NULL COMMENT 'User phone number',
  `default_language` varchar(255) DEFAULT NULL COMMENT 'User default language',
  `country_id` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `user_type` varchar(255) NOT NULL COMMENT 'User Type',
  `active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether user is active',
  `send_welcome_mail` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether send welcome mail.',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT 'Last successful login',
  `last_password_change` timestamp NULL DEFAULT NULL COMMENT 'Last password changed',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `email`, `email_verified_at`, `password`, `tenant_id`, `is_admin`, `role_id`, `avatar`, `phone`, `default_language`, `country_id`, `address`, `user_type`, `active`, `send_welcome_mail`, `last_login_at`, `last_password_change`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'WhatsMark', 'Admin', 'ahtishamabbasmalik@gmail.com', '2025-10-17 05:36:24', '$2y$12$SRMVBvWG7CrpYxd0YGwNTeDaIZIeGVR.ojl7x3tMOhVsgd5RCOrje', NULL, 1, NULL, NULL, '+923000054644', NULL, NULL, NULL, 'admin', 1, 0, '2025-11-14 12:59:20', NULL, '8ixev1zg92j7c0KqzibRGI52J3VV9qyyYcbJzVTLGUbOsHF85N8yQoTnBQll', '2025-10-17 05:31:39', '2025-11-14 12:59:20'),
(2, 'MUHAMMAD', 'SAGHEER', 'malilshani0099@gmail.com', '2025-11-12 03:40:12', '$2y$12$Bwtp.LJAxipGWub9UE446.LpAuFziQ3hQhxGfH3k8CaD8DdjQUe32', 1, 1, NULL, NULL, '+923400878667', 'en', NULL, NULL, 'tenant', 1, 0, '2025-11-12 03:40:13', NULL, 'LJ0r6TOOzYbsH5F3oogE0LPfAhnvbDRE71rkbcgAbdvHnKyFCegozxEEOpej', '2025-11-12 03:40:05', '2025-11-12 03:40:13');

-- --------------------------------------------------------

--
-- Table structure for table `webhook_logs`
--

CREATE TABLE `webhook_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempt` int(11) NOT NULL DEFAULT 1,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response`)),
  `error_message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_code` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_connections`
--

CREATE TABLE `whatsapp_connections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Connection display name',
  `phone_number_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'WhatsApp phone number ID',
  `phone_number` varchar(20) NOT NULL COMMENT 'WhatsApp phone number',
  `business_account_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'WhatsApp Business Account ID',
  `access_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'WhatsApp API access token',
  `waba_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'WhatsApp Business Account ID',
  `app_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Facebook App ID',
  `app_secret` text DEFAULT NULL COMMENT 'Facebook App Secret',
  `verified_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Verified business name',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active' COMMENT 'Connection status',
  `webhook_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether webhook is verified',
  `webhook_verified_at` timestamp NULL DEFAULT NULL COMMENT 'When webhook was verified',
  `token_expires_at` timestamp NULL DEFAULT NULL COMMENT 'Token expiration date',
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional connection settings' CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_templates`
--

CREATE TABLE `whatsapp_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `template_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `category` varchar(100) NOT NULL,
  `header_data_format` varchar(10) DEFAULT NULL,
  `header_data_text` text DEFAULT NULL,
  `header_params_count` int(11) DEFAULT NULL,
  `body_data` text NOT NULL,
  `body_params_count` int(11) DEFAULT NULL,
  `footer_data` text DEFAULT NULL,
  `footer_params_count` int(11) DEFAULT NULL,
  `buttons_data` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `header_file_url` text DEFAULT NULL,
  `header_variable_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`header_variable_value`)),
  `body_variable_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`body_variable_value`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `whatsapp_templates`
--

INSERT INTO `whatsapp_templates` (`id`, `tenant_id`, `template_id`, `template_name`, `language`, `status`, `category`, `header_data_format`, `header_data_text`, `header_params_count`, `body_data`, `body_params_count`, `footer_data`, `footer_params_count`, `buttons_data`, `created_at`, `updated_at`, `header_file_url`, `header_variable_value`, `body_variable_value`) VALUES
(5, 1, 1748178175824836, 'welcome_message', 'en', 'APPROVED', 'MARKETING', 'IMAGE', NULL, 0, '🌟 Why Choose Us – Ham Pe Trust Kyu Karein? 🌟\n\nHamari services 100% trusted aur verified hain 💯. Hum pichlay kai saalon se real data aur verification services provide kar rahe hain — accurate aur reliable results ke sath.\n\n📌 Hamari Khasiyat:\n\n✅ Real & Updated Data – Har report fresh record se verify hoti hai.\n\n🔒 Secure Process – Aapka data safe rehta hai, share nahi hota.\n\n🧾 Proof & Reports – Har service ka solid proof milta hai.\n\n💯 Trusted Source – Genuine aur authentic information hi di jati hai.\n\n💬 Trust ek dafa hota hai — aur ham kabhi usay todte nahi.\nIs liye log kehte hain 👉 “Once you try our service, you’ll never go anywhere else.”\n\n👇 Nechy click karein aur wo service select karein jo aapko chahiye.', 0, NULL, 0, '[{\"type\":\"QUICK_REPLY\",\"text\":\"SIM Owner Details\"},{\"type\":\"QUICK_REPLY\",\"text\":\"All Numbers on CNIC\"},{\"type\":\"QUICK_REPLY\",\"text\":\"Current Location\"},{\"type\":\"QUICK_REPLY\",\"text\":\"CDR Report\"},{\"type\":\"QUICK_REPLY\",\"text\":\"Fake WhatsApp\"},{\"type\":\"QUICK_REPLY\",\"text\":\"Family Tree Written\"},{\"type\":\"QUICK_REPLY\",\"text\":\"Family Tree Pic\"},{\"type\":\"QUICK_REPLY\",\"text\":\"CNIC Color Copy\"},{\"type\":\"QUICK_REPLY\",\"text\":\"Passport Details\"},{\"type\":\"QUICK_REPLY\",\"text\":\"Other Services\"}]', '2025-11-12 04:01:42', '2025-11-12 04:01:42', 'https://scontent.whatsapp.net/v/t61.29466-34/558295112_1748178179158169_1336418100519050661_n.jpg?ccb=1-7&_nc_sid=8b1bef&_nc_ohc=EYsPvNNFUH4Q7kNvwE7p7wO&_nc_oc=AdmyDpq-fQ33iAIsL2IjljsxsIM53zAc5vyetD_jrHsf_VV1J7qU5ob-RnVmGXGHHlusp7jNTEzUK9KklTqWmlqv&_nc_zt=3&_nc_ht=scontent.whatsapp.net&edm=AH51TzQEAAAA&_nc_gid=E04NYujsWAL0_2iNfUz0OA&_nc_tpa=Q5bMBQH4cHWBKU4YayfYTiImkFXgRIKW1KNIP6yVHkO7o27d7MiSlZY1WtlPYTB3pqkjj5ajTfv6kT4x2kY&oh=01_Q5Aa3AEJBopsU58lqbN7p3u9hBF262x753ahdW4W6XXHfFEueA&oe=693B444C', '[\"https:\\/\\/scontent.whatsapp.net\\/v\\/t61.29466-34\\/558295112_1748178179158169_1336418100519050661_n.jpg?ccb=1-7&_nc_sid=8b1bef&_nc_ohc=EYsPvNNFUH4Q7kNvwE7p7wO&_nc_oc=AdmyDpq-fQ33iAIsL2IjljsxsIM53zAc5vyetD_jrHsf_VV1J7qU5ob-RnVmGXGHHlusp7jNTEzUK9KklTqWmlqv&_nc_zt=3&_nc_ht=scontent.whatsapp.net&edm=AH51TzQEAAAA&_nc_gid=E04NYujsWAL0_2iNfUz0OA&_nc_tpa=Q5bMBQH4cHWBKU4YayfYTiImkFXgRIKW1KNIP6yVHkO7o27d7MiSlZY1WtlPYTB3pqkjj5ajTfv6kT4x2kY&oh=01_Q5Aa3AEJBopsU58lqbN7p3u9hBF262x753ahdW4W6XXHfFEueA&oe=693B444C\"]', NULL),
(6, 1, 1295432148469755, 'welcome_to_tracerpk', 'en', 'APPROVED', 'MARKETING', NULL, NULL, 0, 'Here is our services list', 0, NULL, 0, '[{\"type\":\"QUICK_REPLY\",\"text\":\"SIM DETAISL\"},{\"type\":\"QUICK_REPLY\",\"text\":\"CNIC Data\"},{\"type\":\"QUICK_REPLY\",\"text\":\"f trre\"},{\"type\":\"QUICK_REPLY\",\"text\":\"pak data\"},{\"type\":\"QUICK_REPLY\",\"text\":\"Color copy\"},{\"type\":\"QUICK_REPLY\",\"text\":\"pic\"},{\"type\":\"QUICK_REPLY\",\"text\":\"Other services\"},{\"type\":\"QUICK_REPLY\",\"text\":\"New services\"}]', '2025-11-12 04:01:42', '2025-11-12 04:01:42', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wm_activity_logs`
--

CREATE TABLE `wm_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `phone_number_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `business_account_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `rel_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rel_id` int(11) DEFAULT NULL,
  `category_params` longtext DEFAULT NULL,
  `raw_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wm_activity_logs`
--

INSERT INTO `wm_activity_logs` (`id`, `tenant_id`, `phone_number_id`, `access_token`, `business_account_id`, `response_code`, `client_id`, `response_data`, `category`, `category_id`, `rel_type`, `rel_id`, `category_params`, `raw_data`, `created_at`, `updated_at`) VALUES
(1, 1, '717118924823378', 'EAGPj2rEZA4EUBPx4kZCvPK9MM3TZCTnKBuHhs2OK7sHZCB6pFqBcIAxWXgB4lMQJSTEjDTQS7ZCKOC9w6qv9edxDkeyuE9ZAh7NjWznfaPoqKEIdLu6ilf6vhhQ6Y1ZAXVZBuDjwLFfQg9vPlREZAILqGQBhkUpvdzCaKS7W93RfuDRs6qm33qx45zhNt9umDDvsjpgZDZD', '1947223819397152', '200', NULL, '{\"messaging_product\":\"whatsapp\",\"contacts\":[{\"input\":\"923306055177\",\"wa_id\":\"923306055177\"}],\"messages\":[{\"id\":\"wamid.HBgMOTIzMzA2MDU1MTc3FQIAERgSMUIzMEQ5NkU0Mjg3NDg2QUVBAA==\"}]}', 'message_bot', 0, 'lead', 1, '{\"message\":\"\\ntest is working\\n\"}', '{\"messaging_product\":\"whatsapp\",\"recipient_type\":\"individual\",\"to\":\"923306055177\",\"type\":\"text\",\"text\":{\"preview_url\":true,\"body\":\"\\ntest is working\\n\"}}', '2025-11-12 05:24:50', '2025-11-12 05:24:50'),
(2, 1, '717118924823378', 'EAGPj2rEZA4EUBPx4kZCvPK9MM3TZCTnKBuHhs2OK7sHZCB6pFqBcIAxWXgB4lMQJSTEjDTQS7ZCKOC9w6qv9edxDkeyuE9ZAh7NjWznfaPoqKEIdLu6ilf6vhhQ6Y1ZAXVZBuDjwLFfQg9vPlREZAILqGQBhkUpvdzCaKS7W93RfuDRs6qm33qx45zhNt9umDDvsjpgZDZD', '1947223819397152', '200', NULL, '{\"messaging_product\":\"whatsapp\",\"contacts\":[{\"input\":\"923306055177\",\"wa_id\":\"923306055177\"}],\"messages\":[{\"id\":\"wamid.HBgMOTIzMzA2MDU1MTc3FQIAERgSMjNFQUEzOTk4OTk1REE3QTE1AA==\"}]}', 'message_bot', 0, 'lead', 1, '{\"message\":\"\\ntest is working\\n\"}', '{\"messaging_product\":\"whatsapp\",\"recipient_type\":\"individual\",\"to\":\"923306055177\",\"type\":\"text\",\"text\":{\"preview_url\":true,\"body\":\"\\ntest is working\\n\"}}', '2025-11-12 05:25:15', '2025-11-12 05:25:15'),
(3, 1, '717118924823378', 'EAGPj2rEZA4EUBPx4kZCvPK9MM3TZCTnKBuHhs2OK7sHZCB6pFqBcIAxWXgB4lMQJSTEjDTQS7ZCKOC9w6qv9edxDkeyuE9ZAh7NjWznfaPoqKEIdLu6ilf6vhhQ6Y1ZAXVZBuDjwLFfQg9vPlREZAILqGQBhkUpvdzCaKS7W93RfuDRs6qm33qx45zhNt9umDDvsjpgZDZD', '1947223819397152', '200', NULL, '{\"messaging_product\":\"whatsapp\",\"contacts\":[{\"input\":\"923306055177\",\"wa_id\":\"923306055177\"}],\"messages\":[{\"id\":\"wamid.HBgMOTIzMzA2MDU1MTc3FQIAERgSNzA3NTUzNDA0RDVGQTk0QkQ2AA==\"}]}', 'message_bot', 0, 'lead', 1, '{\"message\":\"\\ntest is working\\n\"}', '{\"messaging_product\":\"whatsapp\",\"recipient_type\":\"individual\",\"to\":\"923306055177\",\"type\":\"text\",\"text\":{\"preview_url\":true,\"body\":\"\\ntest is working\\n\"}}', '2025-11-12 07:59:22', '2025-11-12 07:59:22'),
(4, 1, '717118924823378', 'EAGPj2rEZA4EUBPx4kZCvPK9MM3TZCTnKBuHhs2OK7sHZCB6pFqBcIAxWXgB4lMQJSTEjDTQS7ZCKOC9w6qv9edxDkeyuE9ZAh7NjWznfaPoqKEIdLu6ilf6vhhQ6Y1ZAXVZBuDjwLFfQg9vPlREZAILqGQBhkUpvdzCaKS7W93RfuDRs6qm33qx45zhNt9umDDvsjpgZDZD', '1947223819397152', '200', NULL, '{\"messaging_product\":\"whatsapp\",\"contacts\":[{\"input\":\"923306055177\",\"wa_id\":\"923306055177\"}],\"messages\":[{\"id\":\"wamid.HBgMOTIzMzA2MDU1MTc3FQIAERgSQzIwMzA0MUUxN0ZCRUQ3OTMyAA==\"}]}', 'message_bot', 0, 'lead', 1, '{\"message\":\"\\ntest is working\\n\"}', '{\"messaging_product\":\"whatsapp\",\"recipient_type\":\"individual\",\"to\":\"923306055177\",\"type\":\"text\",\"text\":{\"preview_url\":true,\"body\":\"\\ntest is working\\n\"}}', '2025-11-12 14:04:15', '2025-11-12 14:04:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abc_chats`
--
ALTER TABLE `abc_chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `abc_chats_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `abc_chat_messages`
--
ALTER TABLE `abc_chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `abc_chat_messages_tenant_id_index` (`tenant_id`),
  ADD KEY `idx_message_id_tenant_id` (`message_id`,`tenant_id`),
  ADD KEY `abc_chat_messages_tenant_id_interaction_id_index` (`tenant_id`,`interaction_id`),
  ADD KEY `abc_chat_messages_tenant_id_time_sent_index` (`tenant_id`,`time_sent`);

--
-- Indexes for table `abc_contacts`
--
ALTER TABLE `abc_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `abc_contacts_status_id_index` (`status_id`),
  ADD KEY `abc_contacts_source_id_index` (`source_id`),
  ADD KEY `abc_contacts_assigned_id_index` (`assigned_id`),
  ADD KEY `abc_contacts_type_index` (`type`),
  ADD KEY `abc_contacts_phone_index` (`phone`),
  ADD KEY `abc_contacts_firstname_lastname_index` (`firstname`,`lastname`),
  ADD KEY `abc_contacts_is_enabled_index` (`is_enabled`),
  ADD KEY `abc_contacts_tenant_id_index` (`tenant_id`),
  ADD KEY `abc_contacts_tenant_id_email_index` (`tenant_id`,`email`),
  ADD KEY `abc_contacts_tenant_id_phone_index` (`tenant_id`,`phone`),
  ADD KEY `abc_contacts_tenant_id_status_id_index` (`tenant_id`,`status_id`),
  ADD KEY `abc_contacts_tenant_id_created_at_index` (`tenant_id`,`created_at`),
  ADD KEY `idx_tenant_contact_search` (`tenant_id`,`firstname`,`lastname`,`email`,`phone`);

--
-- Indexes for table `abc_contact_notes`
--
ALTER TABLE `abc_contact_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `abc_contact_notes_contact_id_index` (`contact_id`),
  ADD KEY `abc_contact_notes_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `ai_prompts`
--
ALTER TABLE `ai_prompts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_prompts_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `bot_flows`
--
ALTER TABLE `bot_flows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bot_flows_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaigns_name_is_sent_scheduled_send_time_index` (`name`,`is_sent`,`scheduled_send_time`),
  ADD KEY `idx_campaign_scheduling` (`is_sent`,`scheduled_send_time`,`pause_campaign`),
  ADD KEY `idx_tenant_campaigns` (`tenant_id`,`is_sent`);

--
-- Indexes for table `campaign_details`
--
ALTER TABLE `campaign_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_details_campaign_id_tenant_id_index` (`campaign_id`,`tenant_id`),
  ADD KEY `campaign_details_tenant_id_foreign` (`tenant_id`),
  ADD KEY `idx_whatsapp_id_tenant_id` (`whatsapp_id`(191),`tenant_id`),
  ADD KEY `idx_campaign_processing` (`campaign_id`,`tenant_id`,`status`),
  ADD KEY `idx_status_lookup` (`status`,`message_status`);

--
-- Indexes for table `canned_replies`
--
ALTER TABLE `canned_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `canned_replies_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `contact_imports`
--
ALTER TABLE `contact_imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact_imports_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`),
  ADD KEY `coupons_created_by_foreign` (`created_by`),
  ADD KEY `coupons_code_is_active_index` (`code`,`is_active`),
  ADD KEY `coupons_starts_at_expires_at_index` (`starts_at`,`expires_at`),
  ADD KEY `coupons_is_active_created_at_index` (`is_active`,`created_at`);

--
-- Indexes for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupon_usages_coupon_id_tenant_id_index` (`coupon_id`,`tenant_id`),
  ADD KEY `coupon_usages_tenant_id_created_at_index` (`tenant_id`,`created_at`),
  ADD KEY `coupon_usages_invoice_id_index` (`invoice_id`),
  ADD KEY `coupon_usages_subscription_id_index` (`subscription_id`);

--
-- Indexes for table `credit_transactions`
--
ALTER TABLE `credit_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credit_transactions_currency_id_foreign` (`currency_id`),
  ADD KEY `credit_transactions_invoice_id_foreign` (`invoice_id`),
  ADD KEY `credit_transactions_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `custom_fields`
--
ALTER TABLE `custom_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `custom_fields_tenant_id_field_name_unique` (`tenant_id`,`field_name`),
  ADD KEY `custom_fields_tenant_id_is_active_index` (`tenant_id`,`is_active`),
  ADD KEY `custom_fields_tenant_id_display_order_index` (`tenant_id`,`display_order`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_layouts`
--
ALTER TABLE `email_layouts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_layouts_slug_unique` (`slug`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_logs_email_template_id_foreign` (`email_template_id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_templates_slug_unique` (`slug`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `features`
--
ALTER TABLE `features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `features_name_unique` (`name`),
  ADD UNIQUE KEY `features_slug_unique` (`slug`);

--
-- Indexes for table `feature_limits`
--
ALTER TABLE `feature_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `feature_limits_plan_id_feature_id_tenant_id_unique` (`plan_id`,`feature_id`,`tenant_id`),
  ADD KEY `feature_limits_plan_id_feature_id_tenant_id_index` (`plan_id`,`feature_id`,`tenant_id`),
  ADD KEY `feature_limits_feature_id_foreign` (`feature_id`),
  ADD KEY `feature_limits_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `feature_usages`
--
ALTER TABLE `feature_usages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `feature_usages_tenant_id_subscription_id_feature_slug_unique` (`tenant_id`,`subscription_id`,`feature_slug`),
  ADD KEY `feature_usages_subscription_id_foreign` (`subscription_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groups_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoices_currency_id_foreign` (`currency_id`),
  ADD KEY `invoices_subscription_id_foreign` (`subscription_id`),
  ADD KEY `invoices_tenant_id_foreign` (`tenant_id`),
  ADD KEY `invoices_coupon_id_foreign` (`coupon_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_items_invoice_id_foreign` (`invoice_id`);

--
-- Indexes for table `invoice_taxes`
--
ALTER TABLE `invoice_taxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_taxes_invoice_id_index` (`invoice_id`),
  ADD KEY `invoice_taxes_tax_id_foreign` (`tax_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`),
  ADD KEY `idx_queue_processing` (`queue`,`available_at`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `languages_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `message_bots`
--
ALTER TABLE `message_bots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_bots_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD UNIQUE KEY `model_has_permissions_permission_model_type_unique` (`tenant_id`,`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  ADD KEY `model_has_permissions_team_foreign_key_index` (`tenant_id`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD UNIQUE KEY `model_has_roles_role_model_type_unique` (`tenant_id`,`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  ADD KEY `model_has_roles_team_foreign_key_index` (`tenant_id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `modules_name_unique` (`name`);

--
-- Indexes for table `module_validation_logs`
--
ALTER TABLE `module_validation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_validation_logs_module_name_created_at_index` (`module_name`,`created_at`),
  ADD KEY `module_validation_logs_user_id_created_at_index` (`user_id`,`created_at`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_slug_unique` (`slug`),
  ADD KEY `pages_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_methods_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `payment_webhooks`
--
ALTER TABLE `payment_webhooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_webhooks_provider_is_active_index` (`provider`,`is_active`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plans_name_unique` (`name`),
  ADD UNIQUE KEY `plans_slug_unique` (`slug`),
  ADD KEY `plans_is_active_slug_index` (`is_active`,`slug`);

--
-- Indexes for table `plan_features`
--
ALTER TABLE `plan_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_features_plan_id_index` (`plan_id`),
  ADD KEY `plan_features_feature_id_index` (`feature_id`);

--
-- Indexes for table `pusher_notifications`
--
ALTER TABLE `pusher_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pusher_notifications_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_tenant_id_name_guard_name_unique` (`tenant_id`,`name`,`guard_name`),
  ADD KEY `roles_team_foreign_key_index` (`tenant_id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_group_name_unique` (`group`,`name`);

--
-- Indexes for table `sources`
--
ALTER TABLE `sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sources_name_tenant_id_index` (`name`,`tenant_id`),
  ADD KEY `sources_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `statuses_name_tenant_id_index` (`name`,`tenant_id`),
  ADD KEY `statuses_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_plan_id_foreign` (`plan_id`),
  ADD KEY `subscriptions_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `subscription_logs`
--
ALTER TABLE `subscription_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_logs_subscription_id_foreign` (`subscription_id`),
  ADD KEY `subscription_logs_transaction_id_foreign` (`transaction_id`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `template_bots`
--
ALTER TABLE `template_bots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_bots_tenant_id_index` (`tenant_id`),
  ADD KEY `template_bots_template_id_foreign` (`template_id`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenants_subdomain_unique` (`subdomain`),
  ADD KEY `tenants_status_index` (`status`),
  ADD KEY `tenants_subdomain_index` (`subdomain`),
  ADD KEY `tenants_expires_at_index` (`expires_at`);

--
-- Indexes for table `tenant_credit_balances`
--
ALTER TABLE `tenant_credit_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenant_credit_balances_tenant_id_unique` (`tenant_id`),
  ADD KEY `tenant_credit_balances_currency_id_foreign` (`currency_id`);

--
-- Indexes for table `tenant_email_templates`
--
ALTER TABLE `tenant_email_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_email_templates_created_by_foreign` (`created_by`),
  ADD KEY `tenant_email_templates_tenant_id_foreign` (`tenant_id`),
  ADD KEY `tenant_email_templates_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `tenant_languages`
--
ALTER TABLE `tenant_languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenant_languages_code_unique` (`code`);

--
-- Indexes for table `tenant_settings`
--
ALTER TABLE `tenant_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenant_settings_tenant_id_group_key_unique` (`tenant_id`,`group`,`key`),
  ADD KEY `tenant_settings_tenant_id_group_index` (`tenant_id`,`group`);

--
-- Indexes for table `test_users`
--
ALTER TABLE `test_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `themes_folder_unique` (`folder`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tickets_ticket_id_unique` (`ticket_id`),
  ADD KEY `tickets_department_id_foreign` (`department_id`),
  ADD KEY `tickets_tenant_id_foreign` (`tenant_id`),
  ADD KEY `tickets_tenant_staff_id_foreign` (`tenant_staff_id`);

--
-- Indexes for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_replies_ticket_id_foreign` (`ticket_id`),
  ADD KEY `ticket_replies_user_id_foreign` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_currency_id_foreign` (`currency_id`),
  ADD KEY `transactions_invoice_id_foreign` (`invoice_id`),
  ADD KEY `transactions_payment_method_id_foreign` (`payment_method_id`);

--
-- Indexes for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `idx_users_role_status` (`is_admin`,`active`);

--
-- Indexes for table `webhook_logs`
--
ALTER TABLE `webhook_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `webhook_logs_model_event_status_created_at_tenant_id_index` (`model`,`event`,`status`,`created_at`,`tenant_id`),
  ADD KEY `webhook_logs_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `whatsapp_connections`
--
ALTER TABLE `whatsapp_connections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `whatsapp_connections_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `whatsapp_templates`
--
ALTER TABLE `whatsapp_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `whatsapp_templates_template_id_unique` (`template_id`),
  ADD KEY `whatsapp_templates_tenant_id_category_status_index` (`tenant_id`,`category`,`status`);

--
-- Indexes for table `wm_activity_logs`
--
ALTER TABLE `wm_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wm_activity_logs_tenant_id_index` (`tenant_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abc_chats`
--
ALTER TABLE `abc_chats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `abc_chat_messages`
--
ALTER TABLE `abc_chat_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `abc_contacts`
--
ALTER TABLE `abc_contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `abc_contact_notes`
--
ALTER TABLE `abc_contact_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_prompts`
--
ALTER TABLE `ai_prompts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bot_flows`
--
ALTER TABLE `bot_flows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `campaign_details`
--
ALTER TABLE `campaign_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `canned_replies`
--
ALTER TABLE `canned_replies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_imports`
--
ALTER TABLE `contact_imports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credit_transactions`
--
ALTER TABLE `credit_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `custom_fields`
--
ALTER TABLE `custom_fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `email_layouts`
--
ALTER TABLE `email_layouts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `features`
--
ALTER TABLE `features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `feature_limits`
--
ALTER TABLE `feature_limits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feature_usages`
--
ALTER TABLE `feature_usages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `invoice_taxes`
--
ALTER TABLE `invoice_taxes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `message_bots`
--
ALTER TABLE `message_bots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `module_validation_logs`
--
ALTER TABLE `module_validation_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_webhooks`
--
ALTER TABLE `payment_webhooks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `plan_features`
--
ALTER TABLE `plan_features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `pusher_notifications`
--
ALTER TABLE `pusher_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=808;

--
-- AUTO_INCREMENT for table `sources`
--
ALTER TABLE `sources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subscription_logs`
--
ALTER TABLE `subscription_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `template_bots`
--
ALTER TABLE `template_bots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tenant_credit_balances`
--
ALTER TABLE `tenant_credit_balances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tenant_email_templates`
--
ALTER TABLE `tenant_email_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tenant_languages`
--
ALTER TABLE `tenant_languages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tenant_settings`
--
ALTER TABLE `tenant_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `test_users`
--
ALTER TABLE `test_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `webhook_logs`
--
ALTER TABLE `webhook_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `whatsapp_connections`
--
ALTER TABLE `whatsapp_connections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `whatsapp_templates`
--
ALTER TABLE `whatsapp_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `wm_activity_logs`
--
ALTER TABLE `wm_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `abc_chats`
--
ALTER TABLE `abc_chats`
  ADD CONSTRAINT `abc_chats_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `abc_chat_messages`
--
ALTER TABLE `abc_chat_messages`
  ADD CONSTRAINT `abc_chat_messages_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `abc_contacts`
--
ALTER TABLE `abc_contacts`
  ADD CONSTRAINT `abc_contacts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `abc_contact_notes`
--
ALTER TABLE `abc_contact_notes`
  ADD CONSTRAINT `abc_contact_notes_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `abc_contacts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `abc_contact_notes_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_prompts`
--
ALTER TABLE `ai_prompts`
  ADD CONSTRAINT `ai_prompts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bot_flows`
--
ALTER TABLE `bot_flows`
  ADD CONSTRAINT `bot_flows_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `campaign_details`
--
ALTER TABLE `campaign_details`
  ADD CONSTRAINT `campaign_details_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `campaign_details_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `canned_replies`
--
ALTER TABLE `canned_replies`
  ADD CONSTRAINT `canned_replies_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD CONSTRAINT `coupon_usages_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usages_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `coupon_usages_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `coupon_usages_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `credit_transactions`
--
ALTER TABLE `credit_transactions`
  ADD CONSTRAINT `credit_transactions_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `credit_transactions_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `credit_transactions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `custom_fields`
--
ALTER TABLE `custom_fields`
  ADD CONSTRAINT `custom_fields_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_email_template_id_foreign` FOREIGN KEY (`email_template_id`) REFERENCES `email_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `feature_limits`
--
ALTER TABLE `feature_limits`
  ADD CONSTRAINT `feature_limits_feature_id_foreign` FOREIGN KEY (`feature_id`) REFERENCES `features` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feature_limits_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feature_limits_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feature_usages`
--
ALTER TABLE `feature_usages`
  ADD CONSTRAINT `feature_usages_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feature_usages_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `invoices_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_taxes`
--
ALTER TABLE `invoice_taxes`
  ADD CONSTRAINT `invoice_taxes_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_taxes_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `languages`
--
ALTER TABLE `languages`
  ADD CONSTRAINT `languages_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_bots`
--
ALTER TABLE `message_bots`
  ADD CONSTRAINT `message_bots_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `plan_features`
--
ALTER TABLE `plan_features`
  ADD CONSTRAINT `plan_features_feature_id_foreign` FOREIGN KEY (`feature_id`) REFERENCES `features` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `plan_features_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pusher_notifications`
--
ALTER TABLE `pusher_notifications`
  ADD CONSTRAINT `pusher_notifications_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sources`
--
ALTER TABLE `sources`
  ADD CONSTRAINT `sources_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `statuses`
--
ALTER TABLE `statuses`
  ADD CONSTRAINT `statuses_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  ADD CONSTRAINT `subscriptions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_logs`
--
ALTER TABLE `subscription_logs`
  ADD CONSTRAINT `subscription_logs_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_logs_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `template_bots`
--
ALTER TABLE `template_bots`
  ADD CONSTRAINT `template_bots_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `whatsapp_templates` (`template_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `template_bots_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenant_credit_balances`
--
ALTER TABLE `tenant_credit_balances`
  ADD CONSTRAINT `tenant_credit_balances_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `tenant_credit_balances_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenant_email_templates`
--
ALTER TABLE `tenant_email_templates`
  ADD CONSTRAINT `tenant_email_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tenant_email_templates_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenant_email_templates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tickets_tenant_staff_id_foreign` FOREIGN KEY (`tenant_staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD CONSTRAINT `ticket_replies_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `transactions_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `webhook_logs`
--
ALTER TABLE `webhook_logs`
  ADD CONSTRAINT `webhook_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `whatsapp_connections`
--
ALTER TABLE `whatsapp_connections`
  ADD CONSTRAINT `whatsapp_connections_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `whatsapp_templates`
--
ALTER TABLE `whatsapp_templates`
  ADD CONSTRAINT `whatsapp_templates_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wm_activity_logs`
--
ALTER TABLE `wm_activity_logs`
  ADD CONSTRAINT `wm_activity_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
