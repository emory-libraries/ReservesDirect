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
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (8, 'text/html', NULL, 'Image', 'images/doc_type_icons/doctype-image.gif');
