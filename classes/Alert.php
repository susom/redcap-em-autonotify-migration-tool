<?php
/**
 * Created by PhpStorm.
 * User: andy123
 * Date: 2019-03-22
 * Time: 11:17
 */

namespace Stanford\AutonotifyMigrationTool;


/*


select * from redcap_alerts;

insert into redcap_alerts
  ( project_id, alert_title, email_condition, email_from,
  email_to, email_cc,    email_bcc,       email_subject,
  email_text, email_failed)
values
  ( 15, 'test_title', '[record_id] <> ""', 'andy123@stanford.edu',
    'asdf@asdf.com', 'cc@asdf.com', null, 'Subject',
    'body', 'failed@asdf.com' )
;




// TABLE 1

create table redcap_alerts
(
	alert_id int(10) auto_increment
		primary key,
	project_id int(10) null,
	alert_title varchar(100) null,
	email_deleted tinyint(1) default 0 not null,
	alert_expiration datetime null,
	form_name varchar(255) null comment 'Instrument Name',
	form_name_event int(10) null comment 'Event ID',
	email_condition text null comment 'Conditional logic',
	ensure_logic_still_true tinyint(1) default 0 not null,
	email_incomplete tinyint(1) default 0 null comment 'Send alert for any form status?',
	email_from varchar(191) null comment 'Email From',
	email_to text null comment 'Email To',
	email_cc text null comment 'Email CC',
	email_bcc text null comment 'Email BCC',
	email_subject varchar(255) null comment 'Subject',
	email_text text null comment 'Message',
	email_failed varchar(255) null,
	email_attachment_variable text null comment 'REDCap file variables',
	email_attachment1 int(10) null,
	email_attachment2 int(10) null,
	email_attachment3 int(10) null,
	email_attachment4 int(10) null,
	email_attachment5 int(10) null,
	email_repetitive tinyint(1) default 0 null comment 'Re-send alert on form re-save?',
	cron_send_email_on enum('now', 'date', 'time_lag', 'next_occurrence') default 'now' null comment 'When to send alert',
	cron_send_email_on_date datetime null comment 'Exact time to send',
	cron_send_email_on_time_lag_days int(4) null,
	cron_send_email_on_time_lag_hours int(3) null,
	cron_send_email_on_time_lag_minutes int(3) null,
	cron_send_email_on_next_day_type enum('DAY', 'WEEKDAY', 'WEEKENDDAY', 'SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY') default 'DAY' not null,
	cron_send_email_on_next_time time null,
	cron_repeat_for smallint(4) default 0 not null comment 'Repeat every # of days',
	cron_repeat_for_units enum('DAYS', 'HOURS', 'MINUTES') default 'DAYS' not null,
	email_timestamp_sent datetime null comment 'Time last alert was sent',
	email_sent tinyint(1) default 0 not null comment 'Has at least one alert been sent?',
	constraint redcap_alerts_ibfk_1
		foreign key (email_attachment1) references redcap_edocs_metadata (doc_id)
			on update cascade on delete cascade,
	constraint redcap_alerts_ibfk_2
		foreign key (email_attachment2) references redcap_edocs_metadata (doc_id)
			on update cascade on delete cascade,
	constraint redcap_alerts_ibfk_3
		foreign key (email_attachment3) references redcap_edocs_metadata (doc_id)
			on update cascade on delete cascade,
	constraint redcap_alerts_ibfk_4
		foreign key (email_attachment4) references redcap_edocs_metadata (doc_id)
			on update cascade on delete cascade,
	constraint redcap_alerts_ibfk_5
		foreign key (email_attachment5) references redcap_edocs_metadata (doc_id)
			on update cascade on delete cascade,
	constraint redcap_alerts_ibfk_6
		foreign key (form_name_event) references redcap_events_metadata (event_id),
	constraint redcap_alerts_ibfk_7
		foreign key (project_id) references redcap_projects (project_id)
			on update cascade on delete cascade
)
collate=utf8mb4_unicode_ci;

create index alert_expiration
	on redcap_alerts (alert_expiration);

create index email_attachment1
	on redcap_alerts (email_attachment1);

create index email_attachment2
	on redcap_alerts (email_attachment2);

create index email_attachment3
	on redcap_alerts (email_attachment3);

create index email_attachment4
	on redcap_alerts (email_attachment4);

create index email_attachment5
	on redcap_alerts (email_attachment5);

create index form_name_event
	on redcap_alerts (form_name_event);

create index project_id
	on redcap_alerts (project_id);




// TABLE 2

create table redcap_alerts_sent
(
	alert_sent_id int(10) auto_increment
		primary key,
	alert_id int(10) not null,
	record varchar(100) null,
	event_id int(10) null,
	instrument varchar(100) null,
	instance smallint(4) default 1 null,
	last_sent datetime null,
	constraint alert_id_record_event_instrument_instance
		unique (alert_id, record, event_id, instrument, instance),
	constraint redcap_alerts_sent_ibfk_1
		foreign key (alert_id) references redcap_alerts (alert_id)
			on update cascade on delete cascade,
	constraint redcap_alerts_sent_ibfk_2
		foreign key (event_id) references redcap_events_metadata (event_id)
			on update cascade on delete cascade
)
collate=utf8mb4_unicode_ci;

create index event_id_record_alert_id
	on redcap_alerts_sent (event_id, record, alert_id);

create index last_sent
	on redcap_alerts_sent (last_sent);



// TABLE 3

create table redcap_alerts_sent_log
(
	alert_sent_log_id int(10) auto_increment
		primary key,
	alert_sent_id int(10) null,
	time_sent datetime null,
	email_from varchar(191) null,
	email_to text null,
	email_cc text null,
	email_bcc text null,
	subject text null,
	message text null,
	attachment_names text null,
	constraint redcap_alerts_sent_log_ibfk_1
		foreign key (alert_sent_id) references redcap_alerts_sent (alert_sent_id)
			on update cascade on delete cascade
)
collate=utf8mb4_unicode_ci;

create index alert_sent_id_time_sent
	on redcap_alerts_sent_log (alert_sent_id, time_sent);

create index email_from
	on redcap_alerts_sent_log (email_from);

create index time_sent
	on redcap_alerts_sent_log (time_sent);







 */



class Alert
{

}