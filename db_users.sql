#CREATE USER 'property_agent'@'localhost' IDENTIFIED BY '';


-- -----------------------------------------------------
-- Database property_agent
-- -----------------------------------------------------
DROP DATABASE IF EXISTS `property_agent`;

CREATE DATABASE `property_agent`
    CHARACTER SET = 'utf8'
    COLLATE 'utf8_general_ci';

USE `property_agent`;

#DROP TRIGGER IF EXISTS ``;
DROP VIEW IF EXISTS `users_view`;
DROP PROCEDURE IF EXISTS `getUser`;
DROP PROCEDURE IF EXISTS `createUser`;

-- -----------------------------------------------------
-- Table `property_agent`.`users`
-- -----------------------------------------------------
CREATE TABLE `users` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`     VARCHAR(255) NOT NULL,
    `username`  VARCHAR(200) NOT NULL,
    `password`  VARCHAR(255) NOT NULL,
    `verified`  TINYINT(1) NOT NULL,

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
    UNIQUE `scope_UNIQUE` (`scope`)
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `property_agent`.`user_scopes`
-- -----------------------------------------------------
CREATE TABLE `user_scopes` (
    `user_id`     INT UNSIGNED NOT NULL,
    `scope_id`    INT UNSIGNED NOT NULL,

    FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE,

    FOREIGN KEY (`scope_id`)
        REFERENCES `scopes` (`id`)
        ON DELETE CASCADE
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- View `property_agent`.`users_view`
-- -----------------------------------------------------
CREATE VIEW `users_view` AS
    SELECT `users`.`id`, `users`.`username`, `users`.`email`, GROUP_CONCAT(DISTINCT `scopes`.`scope` SEPARATOR ',') `scopes`
        FROM `users`
        INNER JOIN `user_scopes` ON `users`.`id` = `user_scopes`.`user_id`
        INNER JOIN `scopes` ON `user_scopes`.`scope_id` = `scopes`.`id`
        GROUP BY `users`.`id` ASC;

DELIMITER //
-- -----------------------------------------------------
-- Stored procedure `property_agent`.`createUser`
-- -----------------------------------------------------
CREATE PROCEDURE createUser(IN username VARCHAR(255), IN email VARCHAR(255), IN password VARCHAR(255))
BEGIN
    DECLARE last_id INT UNSIGNED;

    INSERT INTO `users` (`username`, `email`, `password`, `verified`)
        VALUES (username, email, password, 0);

    SET last_id = LAST_INSERT_ID();

    INSERT INTO `user_scopes` VALUES (last_id, 1);

    SELECT * FROM `users_view` WHERE `id` = last_id;
END//

-- -----------------------------------------------------
-- Stored procedure `property_agent`.`getUserForSignIn`
-- -----------------------------------------------------
CREATE PROCEDURE getUserForSignIn(IN username VARCHAR(200), IN password VARCHAR(255))
BEGIN
    SELECT `users`.`id`, `users`.`username`, `users`.`email`, GROUP_CONCAT(DISTINCT `scopes`.`scope` SEPARATOR ',') `scopes`
        FROM `users`
        INNER JOIN `user_scopes` ON `users`.`id` = `user_scopes`.`user_id`
        INNER JOIN `scopes` ON `user_scopes`.`scope_id` = `scopes`.`id`
        WHERE `users`.`username` = username
        AND `users`.`password` = password
        LIMIT 1;
END//

DELIMITER ;

START TRANSACTION;

INSERT INTO `scopes` VALUES
    (1, 'normal'),
    (2, 'realtor'),
    (3, 'admin'),
    (4, 'superadmin');

COMMIT;