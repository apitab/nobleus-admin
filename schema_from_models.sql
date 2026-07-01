-- Generated from Yii2 ActiveRecord models
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `alerts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `level` TEXT NOT NULL,
  `type` TEXT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `expiry_date` DATETIME NOT NULL,
  `attachment` VARCHAR(200) NULL,
  `status` INT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `app_settings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `app_name` VARCHAR(100) NOT NULL,
  `default_currency_id` INT NOT NULL,
  `primary_color` VARCHAR(20) NOT NULL,
  `primary_dark_color` VARCHAR(20) NOT NULL,
  `secondary_color` VARCHAR(20) NOT NULL,
  `secondary_dark_color` VARCHAR(20) NOT NULL,
  `accent_color` VARCHAR(20) NOT NULL,
  `accent_dark_color` VARCHAR(20) NOT NULL,
  `primary_text` VARCHAR(20) NOT NULL,
  `secondary_text` VARCHAR(20) NOT NULL,
  `buttons_text` VARCHAR(20) NOT NULL,
  `divider_color` VARCHAR(20) NOT NULL,
  `default_mobile_language` TEXT NULL,
  `default_country_code` VARCHAR(10) NOT NULL,
  `app_version` VARCHAR(10) NOT NULL,
  `enable_version` INT NOT NULL,
  `currency_decimal_digits` VARCHAR(2) NOT NULL,
  `distance_unit` VARCHAR(10) NOT NULL,
  `enable_otp` INT NULL,
  `usd_sos_rate` DECIMAL(15,2) NULL,
  `is_online` INT NULL,
  `uom_volume` TEXT NULL,
  `price_per_barrel` DECIMAL(15,2) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `complaints` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `type` TEXT NOT NULL,
  `customer_id` INT NOT NULL,
  `order_id` INT NULL,
  `title` VARCHAR(250) NOT NULL,
  `category` VARCHAR(100) NULL,
  `description` VARCHAR(250) NOT NULL,
  `resolution_notes` TEXT NULL,
  `status` INT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `currencies` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL,
  `code` VARCHAR(5) NOT NULL,
  `status` INT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  `symbol` VARCHAR(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `customer_address` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `customer_id` INT NULL,
  `location_id` INT NOT NULL,
  `address` VARCHAR(250) NOT NULL,
  `location_coordinates` VARCHAR(100) NOT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `customer_favorites` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `customer_id` INT NULL,
  `vendor_id` INT NOT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `customer_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `customer_id` INT NULL,
  `customer_address` INT NOT NULL,
  `volume_requested` DECIMAL(15,2) NOT NULL,
  `total_amount` DECIMAL(15,2) NULL,
  `delivery_notes` TEXT NULL,
  `delivery_date` DATETIME NOT NULL,
  `payment_id` INT NULL,
  `cancel_reason` TEXT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  `vendor_id` INT NULL,
  `is_shared_request` INT NULL,
  `cancel_track` INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `alias` VARCHAR(150) NULL,
  `phone_number` VARCHAR(100) NULL,
  `primary_address` INT NULL,
  `currency_id` INT NOT NULL,
  `language` TEXT NULL,
  `password_hash` TEXT NOT NULL,
  `api_token` TEXT NULL,
  `device_token` TEXT NULL,
  `code` INT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  `wallet_balance` DECIMAL(15,2) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `target` TEXT NULL,
  `type` TEXT NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `groups` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL,
  `description` VARCHAR(250) NULL,
  `status` INT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `information_guides` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `target` TEXT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `type` TEXT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `customer_request_id` INT NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `edahab_number` VARCHAR(20) NOT NULL,
  `currency` VARCHAR(4) NULL,
  `status` TEXT NULL,
  `transaction_id` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `kiosks` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `location_desc` VARCHAR(200) NOT NULL,
  `location_coordinates` VARCHAR(100) NOT NULL,
  `is_open` TEXT NULL,
  `status` INT NOT NULL,
  `operating_hours` VARCHAR(200) NULL,
  `date_created` DATETIME NULL,
  `date_modified` DATETIME NULL,
  `location_id` INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `locations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL,
  `description` VARCHAR(250) NULL,
  `status` INT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `module_actions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `module_id` INT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(250) NOT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `modules` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `module_name` VARCHAR(100) NULL,
  `description` VARCHAR(250) NULL,
  `status` INT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `notification` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `device_id` VARCHAR(200) NOT NULL,
  `customer_id` INT NOT NULL,
  `data_values` TEXT NULL,
  `notif_keys` TEXT NULL,
  `notif_values` TEXT NULL,
  `title` VARCHAR(100) NOT NULL,
  `message` TEXT NULL,
  `type` TEXT NULL,
  `status` INT NULL,
  `is_read` INT NULL,
  `is_pushed` INT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `device_id` VARCHAR(250) NOT NULL,
  `data_values` VARCHAR(250) NULL,
  `key_type` TEXT NOT NULL,
  `key_id` INT NOT NULL,
  `type` VARCHAR(20) NULL,
  `title` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `status` INT NULL,
  `is_read` INT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  `not_type` TEXT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `outbound_emails` (
  `outbound_email_id` INT NOT NULL AUTO_INCREMENT,
  `template` VARCHAR(100) NULL,
  `payload` TEXT NOT NULL,
  `status` INT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`outbound_email_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `outbound_sms` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `msisdn` VARCHAR(15) NULL,
  `message` TEXT NOT NULL,
  `status` TEXT NULL,
  `date_created` DATETIME NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL,
  `description` TEXT NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `image` VARCHAR(100) NOT NULL,
  `requires_phone` TEXT NULL,
  `method_metadata` TEXT NULL,
  `enabled` INT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payment_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `phone_number` VARCHAR(20) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `status` TEXT NOT NULL,
  `response` TEXT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  `customer_id` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payment_status` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `payment_id` INT NULL,
  `status` VARCHAR(50) NOT NULL,
  `timestamp` VARCHAR(255) NULL,
  `metadata` TEXT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payment_statuses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NULL,
  `status` VARCHAR(10) NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `request_id` INT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `description` VARCHAR(100) NOT NULL,
  `phone_number` VARCHAR(20) NULL,
  `status` INT NOT NULL,
  `payment_method_id` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `module_id` INT NULL,
  `action_id` INT NOT NULL,
  `group_id` INT NOT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ratings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `vendor_id` INT NULL,
  `rating` INT NOT NULL,
  `notes` VARCHAR(250) NULL,
  `order_id` INT NOT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `topup_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `phone_number` VARCHAR(20) NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `status` TEXT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  `customer_id` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_groups` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NULL,
  `group_id` INT NOT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `email_address` VARCHAR(100) NULL,
  `names` VARCHAR(255) NOT NULL,
  `phone_number` VARCHAR(50) NOT NULL,
  `group_id` INT NOT NULL,
  `language` TEXT NOT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  `password_hash` TEXT NOT NULL,
  `password_reset_token` TEXT NULL,
  `verification_token` TEXT NULL,
  `auth_key` TEXT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vendor_certifications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `vendor_id` INT NULL,
  `certification_details` TEXT NOT NULL,
  `certification_meta` TEXT NULL,
  `expiry_date` DATETIME NOT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vendor_deliveries` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `cr_id` INT NOT NULL,
  `start_lat` DOUBLE NOT NULL,
  `start_lng` DOUBLE NOT NULL,
  `end_lat` DOUBLE NOT NULL,
  `end_lng` DOUBLE NOT NULL,
  `distance_km` DECIMAL(15,2) NULL,
  `points_earned` DECIMAL(15,2) NULL,
  `delivery_date` DATETIME NOT NULL,
  `created_at` DATETIME NULL,
  `status` INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vendor_group_points` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `group_id` INT NOT NULL,
  `min_distance` DECIMAL(15,2) NOT NULL,
  `max_distance` DECIMAL(15,2) NULL,
  `points_per_km` DECIMAL(15,2) NOT NULL,
  `date_created` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vendor_groups` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL,
  `description` VARCHAR(250) NULL,
  `price_of_water` DECIMAL(15,2) NOT NULL,
  `status` INT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vendor_notifications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `vendor_id` INT NULL,
  `title` VARCHAR(100) NOT NULL,
  `message` VARCHAR(250) NOT NULL,
  `type` VARCHAR(10) NOT NULL,
  `data` TEXT NULL,
  `status` INT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vendor_refills` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `vendor_id` INT NOT NULL,
  `kiosk` VARCHAR(200) NULL,
  `volume` VARCHAR(20) NOT NULL,
  `status` INT NOT NULL,
  `created_at` DATETIME NOT NULL,
  `update_at` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vendor_updates` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `vendor_id` INT NOT NULL,
  `first_name` VARCHAR(100) NULL,
  `other_names` VARCHAR(100) NULL,
  `mobile_number` VARCHAR(20) NULL,
  `operating_hours` VARCHAR(250) NULL,
  `tank_volume` VARCHAR(10) NULL,
  `vehicle_registration_number` VARCHAR(20) NULL,
  `minimum_order_qty` VARCHAR(20) NULL,
  `dp` VARCHAR(200) NULL,
  `price_of_water` DECIMAL(15,2) NULL,
  `status` TEXT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vendors` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NULL,
  `other_names` VARCHAR(250) NOT NULL,
  `mobile_number` VARCHAR(20) NOT NULL,
  `owner_rental` TEXT NOT NULL,
  `owners_name` VARCHAR(250) NOT NULL,
  `owner_phone_number` VARCHAR(50) NULL,
  `govt_reg` VARCHAR(100) NULL,
  `residential_location` INT NOT NULL,
  `vendor_group` INT NOT NULL,
  `water_source` INT NOT NULL,
  `tank_volume` INT NOT NULL,
  `available_volume` DECIMAL(15,2) NULL,
  `borehole_id` VARCHAR(20) NULL,
  `kiosk_id` VARCHAR(100) NOT NULL,
  `rating` INT NULL,
  `location_coordinates` VARCHAR(100) NULL,
  `location_description` TEXT NULL,
  `specific_locations` VARCHAR(255) NULL,
  `restrict_to_specific_locations` INT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  `display_pic` VARCHAR(255) NULL,
  `auth_key` VARCHAR(255) NULL,
  `password_hash` VARCHAR(255) NULL,
  `api_token` VARCHAR(255) NULL,
  `device_token` VARCHAR(255) NULL,
  `currency_id` INT NULL,
  `code` VARCHAR(255) NULL,
  `is_online` INT NULL,
  `wallet_balance` DECIMAL(15,2) NULL,
  `moq` INT NULL,
  `vendor_type` VARCHAR(255) NULL,
  `operating_hours` VARCHAR(255) NULL,
  `vehicle_registration` VARCHAR(20) NULL,
  `price_of_water` DECIMAL(15,2) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `video_tutorials` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `video_name` TEXT NULL,
  `status` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `water_sources` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL,
  `description` VARCHAR(250) NULL,
  `status` INT NULL,
  `date_created` DATETIME NOT NULL,
  `date_modified` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `withdrawals` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `from_type` TEXT NOT NULL,
  `from_id` INT NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `status` TEXT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `country` (
  `code` VARCHAR(3) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `population` INT NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
