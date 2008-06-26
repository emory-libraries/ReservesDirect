ALTER TABLE `ils_requests` ADD `date_processed` TIMESTAMP NULL AFTER `date_added` ;
ALTER TABLE `ils_requests` CHANGE `date_added` `date_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;