<?php

namespace Stanford\AutonotifyMigrationTool;

require_once("emLoggerTrait.php");

use REDCap;

class AutonotifyMigrationTool extends \ExternalModules\AbstractExternalModule
{

    use emLoggerTrait;

    const KEY_PROJECT_NOTES = 'projectNotes';

    const VALID_SMART_VARS = ['user-name', 'user-dag-name', 'user-dag-id', 'user-dag-label', 'record-name', 'record-dag-name',
        'record-dag-id', 'record-dag-label', 'is-form', 'form-url', 'form-link', 'is-survey', 'survey-url', 'survey-link',
        'survey-queue-url', 'survey-queue-link','survey-time-completed','survey-date-completed','event-name','event-label',
        'previous-event-name', 'previous-event-label', 'next-event-name', 'next-event-label', 'first-event-name',
        'first-event-label', 'last-event-name', 'last-event-label', 'arm-number', 'arm-label',
        'previous-instance', 'current-instance', 'next-instance', 'first-instance', 'last-instance'];


    const STATUS_ICONS = [
        1 => '<i style="color:gray;"  class="far fa-sign-out"></i>           Skip',
        2 => '<i style="color:red;"   class="far fa-exclamation-circle"></i> Help',
        3 => '<i style="color:green;" class="far fa-check-circle"></i>       Complete'
    ];


    /**
     * Log the status of AutoNotify Migration as two settings for the project
     * @param $status
     * @param $note
     * @param $pid
     */
    function updateStatus($status, $note, $pid) {
        // Get current status
        $current_status = $this->getProjectSetting('status', $pid);
        $current_note = $this->getProjectSetting('note', $note);

        $updates = [];
        if ($status != $current_status) {
            $this->setProjectSetting('status', $status, $pid);
            $updates[] = "Changed status from $current_status to $status";
        }
        if ($note != $current_note) {
            $this->setProjectSetting('note', $note, $pid);
            $updates[] = "Changed note from $current_note to $note";
        }
        if (!empty($updates)) REDCap::logEvent("AutoNotify Migration Tool Update", implode("\n",$updates));
    }






    /**
     * Store a project-specific note
     *
     * @param      $note
     * @param null $project_id
     * @return bool
     */
    function addNote($note, $project_id = null, $class=null) {
        if (empty($project_id)) $project_id = $this->getProjectId();
        if (empty($project_id)) {
            $this->emError("Unable to addNote without project id!");
            return false;
        }

        if (!empty($class)) {
            $note = "<span class='badge badge-" . $class . ">$note</span>";
        }


        $notes = $this->getSystemSetting(self::KEY_PROJECT_NOTES);
        if (empty($notes)) $notes = array();
        $notes[$project_id] = $note;
        $this->setSystemSetting(self::KEY_PROJECT_NOTES, $notes);
    }




    /**
     * Scan for any project where the DET url is using autonotify
     * @return array
     */
    function scanForAutoNotifyProjects() {

        // See any custom notes that have been made for this project
        $notes = $this->getSystemSetting(self::KEY_PROJECT_NOTES);

        $sql = "
            select 
                project_id, project_name, status, last_logged_event, data_entry_trigger_url
            from 
                redcap_projects where data_entry_trigger_url like '%autonotify%'";
        $q = db_query($sql);
        $results = [];

        while ($row = db_fetch_assoc($q)) {
            // extract($row);
            $project_id = $row['project_id'];
            $status = $row['status'];

            switch($status) {
                case 0:
                    $status = "Dev";
                    break;
                case 1:
                    $status = "Prod";
                    break;
                case 2:
                    $status = "Inactive";
                    break;
                case 3:
                    $status = "Archived";
                    break;
                default:
                    // nothing
            }


            $migration_status = $this->getProjectSetting('status', $project_id);

            $actions = isset($this::STATUS_ICONS[$migration_status]) ?  $this::STATUS_ICONS[$migration_status] : $migration_status;

            // if (empty($notes[$project_id])) {
            //     $actions = "<span data-action='add-note' data-pid='$project_id'><i class='fas fa-comment'></i></span>";
            // } else {
            //     $actions = $notes[$project_id];
            // }

            $pid_row = "<a href='#' class='goto_project' data-pid='$project_id'><btn class='btn btn-xs btn-primary'>$project_id</btn></a>";


            $results[] = array(
                $pid_row,
                $row['project_name'],
                $status,
                $row['last_logged_event'],
                $actions
            );
        }
        return $results;
    }


    // Get the status of a project for this em (in a giant array)
    function setProjectStatus($project_id, $status) {
        $status = $this->getSystemSetting("projects-status");
        if (empty($status)) $status = [];

        $status[$project_id] = $status;
        $this->setSystemSetting('projects-status', $status);
    }


    // Set the status of a project for this em (in a giant array)
    function getProjectStatus($project_id) {
        $status = $this->getSystemSetting("projects-status");
        return empty($status[$project_id]) ? null : $status[$project_id];
    }

}