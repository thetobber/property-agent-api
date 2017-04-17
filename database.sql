CREATE USER 'property_agent'@'localhost' IDENTIFIED BY '';


-- -----------------------------------------------------
-- Database property_agent
-- -----------------------------------------------------
CREATE DATABASE `library`
    CHARACTER SET = 'utf8mb4'
    COLLATE 'utf8mb4_unicode_ci';

USE `property_agent`;

-- -----------------------------------------------------
-- Table `property_agent`.`users`
-- -----------------------------------------------------
CREATE TABLE `users` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`     VARCHAR(255) NOT NULL,
    `username`  VARCHAR(255) NOT NULL,
    `password`  VARCHAR(255) NOT NULL,
    `verified`  TINYINT NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE INDEX `username_UNIQUE` (`username` ASC)
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `property_agent`.`scopes`
-- -----------------------------------------------------
CREATE TABLE `scopes` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `scope`     VARCHAR(50) NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE INDEX `scope_UNIQUE` (`scope` ASC)
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `property_agent`.`user_scopes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `property_agent`.`user_scopes` (
    `user_id`     INT UNSIGNED NOT NULL,
    `scope_id`    INT UNSIGNED NOT NULL,

    FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`),
    FOREIGN KEY (`scope_id`)
        REFERENCES `scopes` (`id`)
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- View `property_agent`.`users_view`
-- -----------------------------------------------------
CREATE VIEW `users_view` AS
    SELECT `users`.`id`, `users`.`username`, `users`.`email`, `scopes`.`scope`
    FROM `users`
    INNER JOIN `scopes`
    ON `users`.`id` = `scopes`.`id`;



DELIMITER //
-- -----------------------------------------------------
-- Stored procedure `property_agent`.`createUser`
-- -----------------------------------------------------
CREATE PROCEDURE createUser(IN username VARCHAR(255), IN email VARCHAR(255), IN password VARCHAR(255))
BEGIN
    INSERT INTO `users` (`username`, `email`, `password`)
        VALUES (username, email, password);
END//

-- -----------------------------------------------------
-- Stored procedure `property_agent`.`getUser`
-- -----------------------------------------------------
CREATE PROCEDURE getUser(IN users_username VARCHAR)
BEGIN
    SELECT `username`, `email` FROM `users`
        WHERE `username` = users_username;
END//

DELIMITER ;