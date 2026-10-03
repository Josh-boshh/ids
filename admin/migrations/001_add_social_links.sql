USE `ids_integrated`;

ALTER TABLE `case_studies`
  ADD COLUMN `social_website` TEXT NULL,
  ADD COLUMN `social_linkedin` TEXT NULL,
  ADD COLUMN `social_instagram` TEXT NULL,
  ADD COLUMN `social_facebook` TEXT NULL,
  ADD COLUMN `social_x` TEXT NULL;
