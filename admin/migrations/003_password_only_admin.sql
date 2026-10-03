USE `ids_integrated`;

ALTER TABLE `admin_users`
  DROP INDEX `uq_admin_users_username`,
  DROP COLUMN `username`;
