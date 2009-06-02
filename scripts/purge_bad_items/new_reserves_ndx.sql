START TRANSACTION;

ALTER TABLE access ADD INDEX user_id_ndx(user_id);

ALTER TABLE course_instances DROP INDEX ci_date_range_idx;
ALTER TABLE course_instances ADD INDEX activation_date_ndx(activation_date);
ALTER TABLE course_instances ADD INDEX expiration_date_ndx(expiration_date);

DROP TABLE courses_no_dept;

ALTER TABLE inst_loan_periods_libraries ADD INDEX library_id_ndx(library_id);
ALTER TABLE inst_loan_periods_libraries ADD INDEX loan_period_id_ndx(loan_period_id);
ALTER TABLE inst_loan_periods_libraries ADD UNIQUE unique_library_loan_period(library_id, loan_period_id);

ALTER TABLE item_upload_log ADD INDEX user_id_ndx(user_id);
ALTER TABLE item_upload_log ADD INDEX course_instance_id_ndx(course_instance_id);
ALTER TABLE item_upload_log ADD INDEX item_id_ndx(item_id);

ALTER TABLE items DROP INDEX old_id;
ALTER TABLE items DROP COLUMN old_id;
ALTER TABLE `items` CHANGE `mimetype` `mimetype` TINYINT NOT NULL DEFAULT '7';

ALTER TABLE libraries ADD INDEX monograph_library_id_ndx(monograph_library_id);
ALTER TABLE libraries ADD INDEX multimedia_library_id_ndx(multimedia_library_id);
ALTER TABLE libraries ADD INDEX copyright_library_id_ndx(copyright_library_id);

ALTER TABLE mimetypes DROP COLUMN file_extentions;

ALTER TABLE proxied_hosts ADD INDEX partial_match_ndx(partial_match);
ALTER TABLE proxied_hosts ADD INDEX proxy_id_ndx(proxy_id);

ALTER TABLE reports_cache ADD INDEX report_id_ndx(report_id);

ALTER TABLE requests DROP INDEX priority;

ALTER TABLE reserves DROP INDEX reserves_sort_ci_idx;
ALTER TABLE reserves ADD INDEX course_instance_id_ndx(course_instance_id);
ALTER TABLE reserves DROP INDEX reserves_date_range_idx;
ALTER TABLE reserves ADD INDEX activation_date_ndx(activation_date);
ALTER TABLE reserves ADD INDEX expiration_ndx(expiration);
ALTER TABLE reserves ADD INDEX parent_id_ndx(parent_id);
ALTER TABLE reserves DROP INDEX status;

ALTER TABLE terms DROP INDEX sort_order;
ALTER TABLE terms ADD INDEX name_year_ndx(term_name, term_year);
ALTER TABLE terms ADD INDEX begin_date_ndx(begin_date);
ALTER TABLE terms ADD INDEX end_date_ndx(end_date);

ALTER TABLE users DROP INDEX old_id;
ALTER TABLE users DROP INDEX old_user_id;
ALTER TABLE users DROP COLUMN old_id;
ALTER TABLE users DROP COLUMN old_user_id;

COMMIT;
