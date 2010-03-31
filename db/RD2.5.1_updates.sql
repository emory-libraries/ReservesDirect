-- Release 2.5.1 database updates.

-- Copyright data enhancement will expand the items table's pages-time field.
-- the release_2.4.X pages-time field will expand into:
-- pages_times_range = the range of pages/times requested for reserve.
-- pages_times_total = the total pages/times in material requested for reserve.
-- pages_times_used = the sum total of range requested for reserve;
--                   historic data may only provide used and not range data.
-- example:
-- pages_times (original value) = pp. 4-6; 21-22 (9 of 90)
-- will expand into these fields:
-- pages_times_range = 4-6; 21-22
-- pages_times_total = 90
-- pages_times_used = 9
-- this will allow the percentage calculation:
--      10% of the material is requested for reserve.
-- safety check: do not delete the pages_times column until a complete semester has passed.

-- pages_times_range will contain the range of pages or time interval requested.
ALTER TABLE `items` ADD `pages_times_range` varchar(255) DEFAULT NULL;
-- pages_times_total will contain the total pages or amount of time in the resource.
ALTER TABLE `items` ADD `pages_times_total` varchar(255) DEFAULT NULL;
-- pages_times_used will contain the number of pages or amount of time requested.
ALTER TABLE `items` ADD `pages_times_used` varchar(255) DEFAULT NULL;


