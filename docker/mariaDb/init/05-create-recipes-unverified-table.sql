CREATE TABLE IF NOT EXISTS `recipes_unverified` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `image_path` TEXT NULL DEFAULT NULL,
    `ingredients` TEXT NOT NULL,
    `instructions` TEXT NOT NULL,
    `prep_time_value` INT UNSIGNED NULL,
    `prep_time_unit` ENUM('min','h') NULL,
    `cook_time_value` INT UNSIGNED NULL,
    `cook_time_unit` ENUM('min','h') NULL,
    `rest_time_value` INT UNSIGNED NULL,
    `rest_time_unit` ENUM('min','h') NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);