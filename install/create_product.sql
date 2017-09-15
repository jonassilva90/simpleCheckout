CREATE TABLE `product` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`digital` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	`title` VARCHAR(191) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`description` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`image` VARCHAR(191) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`tags` VARCHAR(191) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`category` VARCHAR(191) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`price` DOUBLE(7,2) NOT NULL,
	`weight` INT(10) UNSIGNED NOT NULL COMMENT 'Peso em gramas',
	`width` INT(10) UNSIGNED NOT NULL COMMENT 'Largura em mm',
	`height` INT(10) UNSIGNED NOT NULL COMMENT 'Altura em mm',
	`length` INT(10) UNSIGNED NOT NULL COMMENT 'Comprimento em mm',
	PRIMARY KEY (`id`)
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;
