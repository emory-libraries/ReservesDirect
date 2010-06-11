-- new column material_type for items
ALTER TABLE `items` ADD `material_type` varchar(255) DEFAULT NULL;
ALTER TABLE `items` ADD `publisher` varchar(255) DEFAULT NULL;
ALTER TABLE `items` ADD `availability` tinyint(1) DEFAULT NULL;

-- pages_times_range will contain the range of pages or time interval requested.
ALTER TABLE `items` ADD `pages_times_range` varchar(255) DEFAULT NULL;
-- pages_times_total will contain the total pages or amount of time in the resource.
ALTER TABLE `items` ADD `pages_times_total` varchar(255) DEFAULT NULL;
-- pages_times_used will contain the number of pages or amount of time requested.
ALTER TABLE `items` ADD `pages_times_used` varchar(255) DEFAULT NULL;

-- set images to have an image icon and launch with a browser.
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (8, 'image/jpeg', NULL, 'Image', 'images/doc_type_icons/doctype-image.gif');
UPDATE mimetypes set helper_app_icon = 'images/doc_type_icons/doctype-excel.gif' where helper_app_name = 'Microsoft Excel';
UPDATE mimetypes set helper_app_icon = 'images/doc_type_icons/doctype-ppt.gif' where helper_app_name = 'Microsoft Powerpoint';

-- set mimetype extensions.
UPDATE mimetype_extensions set file_extension = 'xls' where id = 5 and file_extension = 'xcl';
INSERT INTO mimetype_extensions (mimetype_id, file_extension) VALUES (8, 'jpeg');
INSERT INTO mimetype_extensions (mimetype_id, file_extension) VALUES (8, 'jpg');
INSERT INTO mimetype_extensions (mimetype_id, file_extension) VALUES (8, 'gif');

-- add new field for copyright status in the reserves table for queue display.
ALTER TABLE `reserves` ADD `copyright_status` set('NEW', 'PENDING', 'ACCEPTED', 'DENIED') NOT NULL DEFAULT 'NEW' COMMENT 'Do we need/have permission from pub?';

-- create rightsholder info table.
CREATE TABLE rightsholders (
  ISBN varchar(13) NOT NULL,
  name varchar(255),
  contact_name varchar(255),
  contact_email varchar(255),
  fax varchar(50),
  post_address text,
  rights_url varchar(255),
  policy_limit varchar(255),
  PRIMARY KEY (ISBN)
);

-- bootstrap material_type by assuming every PDF is a BOOK_PORTION, though
-- we only care about new courses
UPDATE items SET material_type='BOOK_PORTION' WHERE material_type IS NULL AND 
  url LIKE '%.pdf' AND
  item_id IN (SELECT r.item_id 
              FROM reserves r
                JOIN course_instances ci ON ci.course_instance_id = r.course_instance_id
              WHERE ci.year >= 2010 and ci.term IS NOT 'SPRING');
