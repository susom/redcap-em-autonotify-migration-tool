<?php
namespace Stanford\AutonotifyMigrationTool;


class Alert {

    public $project_id;
    public $module;

    public function __construct($project_id, $module)
    {
        $this->project_id = $project_id;
        $this->module = $module;
    }


    /**
     * See if an alert exists in this project
     * @param $title
     * @return bool
     */
    public function doesAlertExist($title) {
        $sql = "select count(*) from redcap_alerts where " .
            "    alert_title    = '" . db_real_escape_string($title) . "' " .
            "and project_id     = " . intval($this->project_id);
        $q = db_query($sql);
        return db_result($q,0);
    }



    /**
     * @param $trigger
     * @return bool | \mysqli_result
     */
    public function createAlertFromTrigger($trigger){
        /*
            [title] => Test2
            [logic] => [send(1)] = "1"
            [test_record] => 1
            [test_event] =>
            [to] => andy123@stanford.edu
            [cc] =>
            [bcc] =>
            [from] => no-reply@stanford.edu
            [subject] => Trigger TEST2
            [template] => standard
            [body] => BODY HERE    Piping:  redcord id: [record_id]
            [file_field] => 0
            [file_event] =>
            [enabled] => 1
        */

        $alert_title        = trim($trigger['title']);
        $alert_condition    = $trigger['logic'];
        $email_from         = $trigger['from'];
        $email_to           = $trigger['to'];
        $email_cc           = $trigger['cc'];
        $email_bcc          = $trigger['bcc'];
        $email_subject      = $trigger['subject'];
        $alert_message      = $trigger['body'];
        $enabled            = $trigger['enabled'];

        $sql = "insert into redcap_alerts set " .
            "project_id = " . intval($this->project_id) .
            ", email_incomplete = 1" .
            ", alert_title = '" . db_real_escape_string($alert_title) . "'" .
            ($enabled == 1           ? "" : ", email_deleted = 1") .
            (empty($alert_condition) ? "" : ", alert_condition = '" . db_real_escape_string($alert_condition) . "'") .
            (empty($email_from)      ? "" : ", email_from = '"      . db_real_escape_string($email_from) . "'") .
            (empty($email_to)        ? "" : ", email_to = '"        . db_real_escape_string($email_to) . "'") .
            (empty($email_cc)        ? "" : ", email_cc = '"        . db_real_escape_string($email_cc) . "'") .
            (empty($email_bcc)       ? "" : ", email_bcc = '"       . db_real_escape_string($email_bcc) . "'") .
            (empty($email_subject)   ? "" : ", email_subject = '"   . db_real_escape_string($email_subject) . "'") .
            (empty($alert_message)   ? "" : ", alert_message = '"   . db_real_escape_string($alert_message) . "'");

        // $this->module->emDebug("SQL:" . $sql);

        $q = db_query($sql);
        $id = db_insert_id();
        // $this->module->emDebug($q, $id);
        return $id;
    }


    /**
     * DELETE ANY ALERTS FOR THE CURRENT PROJECT
     * @param $project_id
     */
    public function clearAlertsFromProject($project_id) {
        $sql = "delete from redcap_alerts where project_id = " . intval($project_id);
        $q = db_query($sql);
        $this->module->emDebug($q);
    }



    /**
     * BUILD THE ALERT HISTORY FROM THE AUTONOTIFY NOTIFICATION ARRAY
     * @param $new_id
     * @param $notification
     * @param $trigger
     * @return bool|\mysqli_result
     */
    public function createNotificationFromAutoNotifyAlert($new_id, $notification, $trigger) {

        // $this->module->emDebug($new_id, $notification);

        $sql = "insert into redcap_alerts_sent set " .
            "alert_id = " . intval($new_id) .
            ", record = '" . db_real_escape_string($notification['record']) . "'" .
            ", last_sent = '" . db_real_escape_string($notification['last_sent']) . "'" .
            (empty($notification['event_id']) ? "" : ", event_id = " . intval($notification['event_id']));

        // $this->module->emDebug($sql);
        $q = db_query($sql);
        $count = db_affected_rows();

        if ($q) {
            $sql = "insert into redcap_alerts_sent_log set " .
                "alert_sent_id = " . intval(db_insert_id()) .
                ", time_sent =  '" . db_real_escape_string($notification['last_sent']) . "'" .
                ", email_from = '" . db_real_escape_string($trigger['from'])           . "'" .
                ", subject =    '" . db_real_escape_string($trigger['subject'])        . "'" .
                ", message =    '" . db_real_escape_string($trigger['body'])           . "'" .
                (empty($trigger['to'])  ? "" : ", email_to  = '" . db_real_escape_string($trigger['to'])  . "'" ) .
                (empty($trigger['cc'])  ? "" : ", email_cc  = '" . db_real_escape_string($trigger['cc'])  . "'" ) .
                (empty($trigger['bcc']) ? "" : ", email_bcc = '" . db_real_escape_string($trigger['bcc']) . "'" )
            ;

            // $this->module->emDebug($sql);
            $q = db_query($sql);
        }

        return $q ? $count : FALSE;
    }




}

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
