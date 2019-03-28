<?php
/**
 * Created by PhpStorm.
 * User: andy123
 * Date: 2019-03-22
 * Time: 11:17
 */

namespace Stanford\AutonotifyMigrationTool;


/*
 *

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