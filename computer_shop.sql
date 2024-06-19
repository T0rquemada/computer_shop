-- Creating tables
CREATE TABLE IF NOT EXISTS `users` (
	`user_id` INT NOT NULL AUTO_INCREMENT UNIQUE,
	`username` VARCHAR(255) NOT NULL,
	`password` VARCHAR(255) NOT NULL,
	`phone_number` VARCHAR(255) NOT NULL UNIQUE,
	`email` VARCHAR(255) NOT NULL UNIQUE,
	PRIMARY KEY(`user_id`)
);

-- Contain user orders, with user_id, mail_id.
-- Depending on order_id extracting order from orders.json
CREATE TABLE IF NOT EXISTS `orders` (
	`order_id` INT NOT NULL AUTO_INCREMENT UNIQUE,
	`user_id` INT NOT NULL,
	`mail_id` INT NOT NULL,
	PRIMARY KEY(`order_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
        ON UPDATE NO ACTION ON DELETE NO ACTION,
    FOREIGN KEY (`mail_id`) REFERENCES `mail`(`mail_id`)
        ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE IF NOT EXISTS `gpu` (
	`gpu_id` INT NOT NULL AUTO_INCREMENT UNIQUE,
	`video_memory` INT NOT NULL,
	`type_memory` VARCHAR(255) NOT NULL,
	`brand` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `price` INT NOT NULL,
	PRIMARY KEY(`gpu_id`)
);

CREATE TABLE IF NOT EXISTS `categories` (
	`category_id` INT NOT NULL AUTO_INCREMENT UNIQUE,
	`title` VARCHAR(255) NOT NULL UNIQUE,
	PRIMARY KEY(`category_id`)
);

CREATE TABLE IF NOT EXISTS `cpu` (
	`cpu_id` INT NOT NULL AUTO_INCREMENT UNIQUE,
	`brand` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
	`cores` SMALLINT NOT NULL,
	`threads` SMALLINT NOT NULL,
	`basic_speed` DECIMAL(5,2) NOT NULL,
	`integrated_graphics` BOOLEAN NOT NULL,
    `price` INT NOT NULL,
	PRIMARY KEY(`cpu_id`)
);

CREATE TABLE IF NOT EXISTS `mail` (
	`mail_id` INT NOT NULL AUTO_INCREMENT UNIQUE,
	`company` VARCHAR(255) NOT NULL,
	`department_number` INT NOT NULL,
	`city` VARCHAR(255) NOT NULL,
	`street` VARCHAR(255) NOT NULL,
	PRIMARY KEY(`mail_id`)
);

CREATE TABLE IF NOT EXISTS `motherboards` (
	`motherboard_id` INT NOT NULL AUTO_INCREMENT UNIQUE,
	`brand` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
	`socket` VARCHAR(255) NOT NULL,
	`form_factor` VARCHAR(255) NOT NULL,
    `price` INT NOT NULL,
	PRIMARY KEY(`motherboard_id`)
);

CREATE TABLE IF NOT EXISTS `ram` (
	`ram_id` INT NOT NULL AUTO_INCREMENT UNIQUE,
	`brand` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
	`memory` INT NOT NULL,
	`memory_frequency` INT NOT NULL,
	`memory_type` VARCHAR(255) NOT NULL,
    `price` INT NOT NULL,
	PRIMARY KEY(`ram_id`)
);

CREATE TABLE `cart` (
	`cart_id` INT NOT NULL AUTO_INCREMENT UNIQUE,
	`user_id` INT NOT NULL UNIQUE,
	`items` JSON NOT NULL,
	PRIMARY KEY(`cart_id`)
);

-- Inserting data in created tables
INSERT INTO `categories` (`title`) VALUES
    ("cpu"),
    ("gpu"),
    ("motherboads"),
    ("ram");

INSERT INTO `mail` (`company`, `department_number`, `city`, `street`) VALUES
  ("nova_pochta", 36, "Dnipro", "Olesya Honchara 14"),
  ("nova_pochta", 50, "Lviv", "Torfyana 23"),
  ("nova_pochta", 150, "Kyiv", "Antonovicha 43");

INSERT INTO `cpu` (`brand`, `title`, `cores`, `threads`, `basic_speed`, `integrated_graphics`, `price`) VALUES
    ("amd", "AMD Ryzen 7 5700X", 8, 16, 3.4, false, 10000),
    ("intel", "Intel Core i5", 6, 12, 2.5, false, 15000),
    ("amd", "AMD Ryzen 7 7800X3D", 8, 16, 4.2, true, 20000);

INSERT INTO `gpu` (`video_memory`, `type_memory`, `brand`, `title`, `price`) VALUES
    (8, "GDDR6", "Gigabyte", "GeForce RTX 4060", 15000),
    (16, "GDDR6", "MSI", "GeForce RTX 4090", 20000),
    (12, "GDDR5", "ASUS", "GeForce RTX 2080", 1000);

INSERT INTO `ram` (`brand`, `title`, `memory`, `memory_frequency`, `memory_type`, `price`) VALUES
     ("Kingston", "Fury", 16, 3200, "DDR4", 2000),
     ("Goodram", "Goodram", 16, 3200, "DDR3", 1000),
     ("Kingston", "Fury", 32, 3600, "DDR5", 3000);

INSERT INTO `motherboards` (`brand`, `title`, `Socket`, `form_factor`, price) VALUES
    ("MSI", "MAG B550", "AM4", "ATX", 6899),
    ("ASUS", "ROG STRIZ", "AM5", "ATX", 14499),
    ("Gigabyte", "B550M", "AM4", "MicroATX", 4399);