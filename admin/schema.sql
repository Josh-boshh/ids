CREATE DATABASE IF NOT EXISTS `ids_integrated`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ids_integrated`;

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `case_studies` (
  `id` VARCHAR(32) NOT NULL,
  `slug` VARCHAR(80) NOT NULL,
  `title` VARCHAR(120) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `summary` VARCHAR(280) NOT NULL,
  `image` VARCHAR(2048) NOT NULL,
  `image_alt` VARCHAR(180) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `social_website` TEXT NULL,
  `social_linkedin` TEXT NULL,
  `social_instagram` TEXT NULL,
  `social_facebook` TEXT NULL,
  `social_x` TEXT NULL,
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `published` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_case_studies_slug` (`slug`),
  KEY `idx_case_studies_visibility` (`published`, `featured`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `site_seo_settings` (
  `id` TINYINT UNSIGNED NOT NULL,
  `site_name` VARCHAR(120) NOT NULL,
  `seo_title` VARCHAR(120) NOT NULL,
  `meta_description` VARCHAR(320) NOT NULL,
  `canonical_url` VARCHAR(2048) NOT NULL DEFAULT '',
  `og_title` VARCHAR(120) NOT NULL,
  `og_description` VARCHAR(320) NOT NULL,
  `og_image` TEXT NULL,
  `twitter_title` VARCHAR(120) NOT NULL,
  `twitter_description` VARCHAR(320) NOT NULL,
  `twitter_image` TEXT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `site_seo_settings` (
  `id`, `site_name`, `seo_title`, `meta_description`, `canonical_url`,
  `og_title`, `og_description`, `og_image`,
  `twitter_title`, `twitter_description`, `twitter_image`
) VALUES (
  1,
  'I.D.S Integrated',
  'Web Design & Development Agency – I.D.S Integrated',
  'I.D.S Integrated is a full-service web design, development & SEO agency, building high-stakes websites for organizations that can’t afford to look unfinished — from governments and campaigns to enterprise platforms and premium brands.',
  'https://idsintegrated.com/',
  'Web Design & Development Agency – I.D.S Integrated',
  'I.D.S Integrated is a full-service web design, development & SEO agency, building high-stakes websites for organizations that can’t afford to look unfinished — from governments and campaigns to enterprise platforms and premium brands.',
  '',
  'Web Design & Development Agency – I.D.S Integrated',
  'I.D.S Integrated is a full-service web design, development & SEO agency, building high-stakes websites for organizations that can’t afford to look unfinished — from governments and campaigns to enterprise platforms and premium brands.',
  ''
);

INSERT IGNORE INTO `case_studies` (
  `id`, `slug`, `title`, `category`, `summary`, `image`, `image_alt`, `content`,
  `featured`, `published`, `sort_order`
) VALUES
(
  'case-federal-ministry-of-defence',
  'federal-ministry-of-defence',
  'Federal Ministry of Defence — BPSR compliant',
  'Government & public sector · Flagship build',
  'A full rebuild of Nigeria’s apex defence policy site, serving 220 million citizens — delivered to meet Bureau of Public Service Reforms compliance criteria.',
  'assets/img/case-studies/federal-ministry-of-defence.jpg',
  'Federal Ministry of Defence headquarters with the Nigerian coat of arms.',
  'A full rebuild of Nigeria’s apex defence policy site, serving 220 million citizens — delivered to meet Bureau of Public Service Reforms compliance criteria.',
  1,
  1,
  0
),
(
  'case-apc-promise-kept',
  'apc-promise-kept',
  'APC Promise Kept — 9.1M+ Nigerians reached',
  'Political & campaign',
  'A public accountability portal documenting the Renewed Hope Agenda with live impact counters and news.',
  'assets/img/case-studies/apc-promise-kept.jpg',
  'APC and Renewed Hope Agenda logos.',
  'A public accountability portal documenting the Renewed Hope Agenda with live impact counters and news.',
  0,
  1,
  1
),
(
  'case-placom',
  'placom',
  'PLACOM Commodity Markets — 4 audiences, 1 platform',
  'Agriculture & commodities',
  'An institutional site serving farmers, investors, export traders and government stakeholders.',
  'assets/img/case-studies/placom.jpg',
  'Homepage of the PLACOM Commodity Markets institutional website.',
  'An institutional site serving farmers, investors, export traders and government stakeholders.',
  0,
  1,
  2
);