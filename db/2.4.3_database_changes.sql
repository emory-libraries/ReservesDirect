 CREATE TABLE `reserves`.`mimetype_extensions` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'primary key',
`mimetype_id` INT NOT NULL COMMENT 'foreign key to mimetypes table',
`file_extension` VARCHAR( 5 ) NOT NULL COMMENT 'file extension',
INDEX ( `mimetype_id` ) ,
UNIQUE (
`file_extension`
)
) ENGINE = InnoDB COMMENT = 'allow storage of mulitple file extensions for each mimetype';


-- Add existing file extensions to new table
INSERT INTO mimetype_extensions (mimetype_id, file_extension) SELECT mimetype_id, file_extentions FROM mimetypes;

-- Add new values
INSERT INTO mimetype_extensions (mimetype_id, file_extension) VALUES (4, "docx"), (5, "xlsx"), (6, "pptx"), (6, "ppsx"), (7, "html"), (7, "htm"), (7, "xhtml");

-- Remove file_extentions from mimetype table
ALTER TABLE `mimetypes` DROP `file_extentions`;
 
-- items.mimetype should be numeric only
UPDATE items set mimetype = 7 WHERE mimetype IS NULL OR mimetype = 'text/html' OR mimetype = 0;
ALTER TABLE `items` CHANGE `mimetype` `mimetype` TINYINT NOT NULL DEFAULT '7';