-- -----------------------------------------------------
-- Table `property_agent`.`municipalities`
-- -----------------------------------------------------
CREATE TABLE `municipalities` (
    `postal`        CHAR(4) NOT NULL,
    `municipality`  VARCHAR(100) NOT NULL,

    PRIMARY KEY (`postal`),
    UNIQUE `municipality_unique` (`municipality`)
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `property_agent`.`road_names`
-- -----------------------------------------------------
CREATE TABLE `roads` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `road`          VARCHAR(200) NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE `road_UNIQUE` (`road`)
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `property_agent`.`types`
-- -----------------------------------------------------
CREATE TABLE `types` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`          VARCHAR(100) NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE `type_UNIQUE` (`type`)
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `property_agent`.`properties`
-- -----------------------------------------------------
CREATE TABLE `properties` (
    `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type_id`               INT UNSIGNED,
    `road_id`               INT UNSIGNED,
    `number`                SMALLINT UNSIGNED,
    `floor`                 TINYINT UNSIGNED DEFAULT 0,
    `door`                  VARCHAR(20),
    `municipality_postal`   CHAR(4),
    `rooms`                 SMALLINT UNSIGNED,
    `area`                  INT UNSIGNED,
    `year`                  VARCHAR(10),
    `expenses`              INT UNSIGNED DEFAULT 0,
    `deposit`               INT UNSIGNED DEFAULT 0,
    `price`                 BIGINT UNSIGNED DEFAULT 0,
    `images`                MEDIUMBLOB,
    `map`                   VARCHAR(2083),

    PRIMARY KEY (`id`),

    FOREIGN KEY (`type_id`)
        REFERENCES `types` (`id`),

    FOREIGN KEY (`road_id`)
        REFERENCES `roads` (`id`),

    FOREIGN KEY (`municipality_postal`)
        REFERENCES `municipalities` (`postal`)
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- View `property_agent`.`users_view`
-- -----------------------------------------------------
CREATE VIEW `properties_view` AS
    SELECT  `p`.`id`,
            `t`.`type`,
            `r`.`road`,
            `p`.`number`,
            `p`.`floor`,
            `p`.`door`,
            `m`.`postal`,
            `m`.`municipality`,
            `p`.`rooms`,
            `p`.`area`,
            `p`.`year`,
            `p`.`expenses`,
            `p`.`deposit`,
            `p`.`price`,
            `p`.`images`,
            `p`.`map`
    FROM `properties` AS `p`
    INNER JOIN `types` AS `t`
        ON `p`.`type_id` = `t`.`id`
    INNER JOIN `roads` AS `r`
        ON `p`.`road_id` = `r`.`id`
    INNER JOIN `municipalities` AS `m`
        ON `p`.`municipality_postal` = `m`.`postal`;

/*DELIMITER //
CREATE PROCEDURE searchByAddress(
    IN `type`           VARCHAR(100),
    IN `road`           VARCHAR(200),
    IN `number`         SMALLINT UNSIGNED,
    IN `floor`          TINYINT UNSIGNED,
    IN `door`           VARCHAR(20),
    IN `postal`         CHAR(4),
    IN `municipality`   VARCHAR(100)
)
BEGIN
    SELECT * FROM `properties_view` AS `p`
    WHERE (`p`.`type` LIKE `type` OR `p`.`type` LIKE '%')
    AND (`p`.`road` LIKE `road` OR `p`.`road` LIKE '%')
    AND (`p`.`number` LIKE `number` OR `p`.`number` LIKE '%')
    AND (`p`.`floor` LIKE `floor` OR `p`.`floor` LIKE '%')
END//
DELIMITER ;*/

DELIMITER //

-- -----------------------------------------------------
-- Stored procedure `property_agent`.`createProperty`
-- -----------------------------------------------------
CREATE PROCEDURE createProperty(
    IN inType         VARCHAR(100),
    IN inRoad         VARCHAR(200),
    IN inPostal       CHAR(4),
    IN inMunicipality VARCHAR(100),
    IN inNumber       SMALLINT UNSIGNED,
    IN inFloor        TINYINT UNSIGNED,
    IN inDoor         VARCHAR(20),
    IN inRooms        SMALLINT UNSIGNED,
    IN inArea         INT UNSIGNED,
    IN inYear         VARCHAR(10),
    IN inExpenses     INT UNSIGNED,
    IN inDeposit      INT UNSIGNED,
    IN inPrice        BIGINT UNSIGNED,
    IN inMap          VARCHAR(2083),
    IN inImages       MEDIUMBLOB
)
BEGIN
    DECLARE type_id INT UNSIGNED;
    DECLARE road_id INT UNSIGNED;
    DECLARE postal_id CHAR(4);

    START TRANSACTION;
        INSERT IGNORE INTO `types` (`type`) VALUES (inType);
        SELECT `id` FROM `types` WHERE `type` = inType INTO type_id;

        INSERT IGNORE INTO `roads` (`road`) VALUES (inRoad);
        SELECT `id` FROM `roads` WHERE `road` = inRoad INTO road_id;

        INSERT IGNORE INTO `municipalities` VALUES (inPostal, inMunicipality);
        SELECT `postal` FROM `municipalities` WHERE `postal` = inPostal INTO postal_id;

        INSERT INTO `properties` VALUES (null, type_id, road_id, inNumber, inFloor, inDoor, postal_id, inRooms, inArea, inYear, inExpenses, inDeposit, inPrice, inImages, inMap);
    COMMIT;
END//


-- -----------------------------------------------------
-- Stored procedure `property_agent`.`updateProperty`
-- -----------------------------------------------------
CREATE PROCEDURE updateProperty(
    IN inId         INT UNSIGNED,
    IN inType         VARCHAR(100),
    IN inRoad         VARCHAR(200),
    IN inPostal       CHAR(4),
    IN inMunicipality VARCHAR(100),
    IN inNumber       SMALLINT UNSIGNED,
    IN inFloor        TINYINT UNSIGNED,
    IN inDoor         VARCHAR(20),
    IN inRooms        SMALLINT UNSIGNED,
    IN inArea         INT UNSIGNED,
    IN inYear         VARCHAR(10),
    IN inExpenses     INT UNSIGNED,
    IN inDeposit      INT UNSIGNED,
    IN inPrice        BIGINT UNSIGNED,
    IN inMap          VARCHAR(2083),
    IN inImages       MEDIUMBLOB
)
BEGIN
    DECLARE new_type_id INT UNSIGNED;
    DECLARE new_road_id INT UNSIGNED;
    DECLARE new_postal_id CHAR(4);

    START TRANSACTION;
        INSERT IGNORE INTO `types` (`type`) VALUES (inType);
        SELECT `id` FROM `types` WHERE `type` = inType INTO new_type_id;

        INSERT IGNORE INTO `roads` (`road`) VALUES (inRoad);
        SELECT `id` FROM `roads` WHERE `road` = inRoad INTO new_road_id;

        INSERT IGNORE INTO `municipalities` VALUES (inPostal, inMunicipality);
        SELECT `postal` FROM `municipalities` WHERE `postal` = inPostal INTO new_postal_id;

        UPDATE `properties` SET
            `type_id`             = new_type_id,
            `road_id`             = new_road_id,
            `number`              = inNumber,
            `floor`               = inFloor,
            `door`                = inDoor,
            `municipality_postal` = new_postal_id,
            `rooms`               = inRooms,
            `area`                = inArea,
            `year`                = inYear,
            `expenses`            = inExpenses,
            `deposit`             = inDeposit,
            `price`               = inPrice,
            `images`              = inImages,
            `map`                 = inMap
        WHERE `id` = inId;
    COMMIT;
END//
DELIMITER ;

#CALL createProperty('Værelse', 'Rebæk Søpark', '2650', 'Hvidovre', 5, 1, '240', 1, 23, '1962', 2375, 8000, 0, 'abc', null);
#CALL updateProperty('Værelse', 'Rebæk Søpark', '2650', 'Hvidovre', 5, 1, '240', 1, 23, '1962', 2375, 8000, 0, null, null);