-- new column material_type for items
ALTER TABLE `items` ADD `material_type` varchar(255) DEFAULT NULL;
ALTER TABLE `items` ADD `publisher` varchar(255) DEFAULT NULL;
ALTER TABLE `items` ADD `availability` tinyint(1) DEFAULT NULL;

-- new physical items type icon definitions
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (8, 'text/html', NULL, 'Book', 'images/doc_type_icons/doctype-book.gif');
INSERT INTO mimetypes (mimetype_id, mimetype, helper_app_url, helper_app_name, helper_app_icon) VALUES (9, 'text/html', NULL, 'Multimedia Disk', 'images/doc_type_icons/doctype-disc2.gif');
