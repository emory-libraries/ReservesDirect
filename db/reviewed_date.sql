ALTER TABLE `course_instances` ADD `reviewed_date` DATE NULL COMMENT 'reviewed by staff for copyright compliance'; 
ALTER TABLE `course_instances` ADD `reviewed_by`   INTEGER  NULL COMMENT 'reviewed by staff for copyright compliance';


-- Add Report
INSERT INTO `reports` (`title`, `param_group`, `sql`, `parameters`, `min_permissions`, `sort_order`, `cached`, `cache_refresh_delay`) VALUES 
('Classes Needing Review', 'term', 'SELECT DISTINCT concat( ''<a href="index.php%3Fcmd%3DeditClass%26ci%3D'', ci.course_instance_id, ''" target="new">edit class</a>'' ) AS link, u.last_name, u.first_name, ca.course_name\r\nFROM course_instances AS ci\r\nJOIN course_aliases AS ca ON ca.course_alias_id = ci.primary_course_alias_id\r\nLEFT JOIN access AS a ON ca.course_alias_id = a.alias_id\r\nAND a.permission_level = 3\r\nLEFT JOIN users AS u ON a.user_id = u.user_id\r\nJOIN terms AS t on ci.term = t.term_name AND ci.year = t.term_year\r\nWHERE t.term_id in (!)\r\nAND ci.reviewed_date IS NULL\r\nAND EXISTS (\r\nSELECT course_instance_id\r\nFROM reserves WHERE course_instance_id = ci.course_instance_id\r\n) ', 'term_id', 4, 17, 1, 6);