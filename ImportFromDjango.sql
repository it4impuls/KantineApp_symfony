-- Template to import db from django-db. Since django and symfony have sligtly different row names.
-- 
-- how to use:
-- export the tables kasseBE_order and kasseBE_user from the django db (https://w014103a.kasserver.com/mysqladmin)
-- open it with a text-editor and copy the values after "INSERT INTO `kasseBE_user` (`firstname`, `lastname`, `code`, `active`, `enddate`) VALUES"
-- where "---DATA---" is in this file (after "INSERT INTO `Costumer` (`firstname`,`lastname`,`id`,`active`,`enddate`) VALUES")
-- repeat for kasseBE_order
-- WARNING: the exporter splits up long queries. Search for additional "[...];[\n] INSERT INTO [...] VALUES" commands in the middle of data and replace them with ","
-- ensure target DB is completely empty
-- Click on import
-- select the file
-- un-check Enable foreign key checks (we allow deletion of costumers while keeping orders)
-- ------------------------------------------------------
--
-- Table structure for table `kasseBE_user`
--
CREATE TABLE
  Costumer (
    id INT AUTO_INCREMENT NOT NULL,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    active TINYINT (1) NOT NULL,
    enddate DATE NOT NULL,
    Department VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id)
  ) DEFAULT CHARACTER
SET
  utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE
  `order` (
    id INT AUTO_INCREMENT NOT NULL,
    order_dateTime DATETIME NOT NULL,
    ordered_item NUMERIC(4, 2) NOT NULL,
    tax SMALLINT NOT NULL,
    Costumer_id INT DEFAULT NULL,
    INDEX IDX_F5299398E62B9E85 (Costumer_id),
    PRIMARY KEY (id)
  ) DEFAULT CHARACTER
SET
  utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE
  user__user (
    id INT AUTO_INCREMENT NOT NULL,
    username VARCHAR(180) NOT NULL,
    username_canonical VARCHAR(180) NOT NULL,
    email VARCHAR(180) NOT NULL,
    email_canonical VARCHAR(180) NOT NULL,
    enabled TINYINT (1) NOT NULL,
    salt VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    last_login DATETIME DEFAULT NULL,
    confirmation_token VARCHAR(180) DEFAULT NULL,
    password_requested_at DATETIME DEFAULT NULL,
    roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE INDEX UNIQ_32745D0A92FC23A8 (username_canonical),
    UNIQUE INDEX UNIQ_32745D0AA0D96FBF (email_canonical),
    UNIQUE INDEX UNIQ_32745D0AC05FB297 (confirmation_token),
    UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username),
    PRIMARY KEY (id)
  ) DEFAULT CHARACTER
SET
  utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

ALTER TABLE `order` ADD CONSTRAINT FK_F5299398E62B9E85 FOREIGN KEY (Costumer_id) REFERENCES Costumer (id) ON DELETE SET NULL;

--
-- Dumping data for table `Costumer`
--
INSERT INTO
  `Costumer` (
    `firstname`,
    `lastname`,
    `id`,
    `active`,
    `enddate`
  )
VALUES
  ---- DATA ----
;

--
-- Dumping data for table `order`
--
INSERT INTO
  `order` (
    `id`,
    `order_dateTime`,
    `ordered_item`,
    `tax`,
    `Costumer_id`
  )
VALUES
  ---- DATA ----
;