-- new column material_type for items
ALTER TABLE `items` ADD `material_type` varchar(255) DEFAULT NULL;
ALTER TABLE `items` ADD `publisher` varchar(255) DEFAULT NULL;
ALTER TABLE `items` ADD `availability` tinyint(1) DEFAULT NULL;

-- new physical items type icon definitions
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (8, 'text/html', NULL, 'Book', 'images/doc_type_icons/doctype-book.gif');
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (9, 'text/html', NULL, 'Multimedia Disk', 'images/doc_type_icons/doctype-disc2.gif');

-- pages_times_range will contain the range of pages or time interval requested.
ALTER TABLE `items` ADD `pages_times_range` varchar(255) DEFAULT NULL;
-- pages_times_total will contain the total pages or amount of time in the resource.
ALTER TABLE `items` ADD `pages_times_total` varchar(255) DEFAULT NULL;
-- pages_times_used will contain the number of pages or amount of time requested.
ALTER TABLE `items` ADD `pages_times_used` varchar(255) DEFAULT NULL;
