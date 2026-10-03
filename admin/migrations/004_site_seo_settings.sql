USE `ids_integrated`;

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

ALTER TABLE `case_studies`
  DROP COLUMN `seo_title`,
  DROP COLUMN `seo_description`,
  DROP COLUMN `canonical_url`,
  DROP COLUMN `og_image`;
