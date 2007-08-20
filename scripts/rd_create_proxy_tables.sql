CREATE TABLE `proxied_hosts` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'primary key',
	`proxy_id` INT NOT NULL COMMENT 'foreign key to proxy table',
	`domain` VARCHAR( 255 ) NOT NULL COMMENT 'host domain',
	`partial_match` BINARY NOT NULL DEFAULT '0' COMMENT 'if 0 require exact match against domain'
) ENGINE = MYISAM 
COMMENT = 'list of host to be proxied';


ALTER TABLE `proxied_hosts` ADD UNIQUE `unique_domain` ( `domain` );


CREATE TABLE `proxies` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'primary key',
	`name` VARCHAR( 50 ) NOT NULL COMMENT 'display name',
	`prefix` VARCHAR( 255 ) NOT NULL COMMENT 'url prefix'
) ENGINE = MYISAM 
COMMENT = 'proxies';