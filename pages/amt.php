<?php
namespace Stanford\AutonotifyMigrationTool;
/** @var \Stanford\AutonotifyMigrationTool\AutonotifyMigrationTool $module */


use REDCap;

use AutoNotify3;
// use AutoNotify2;


require_once($module->getModulePath() . "classes/Alert.php");
// require_once($module->getModulePath() . "classes/AutoNotify2.php");
require_once($module->getModulePath() . "classes/AutoNotify3.php");


$status = $module->getProjectSetting('status');
$note = $module->getProjectSetting('note');
$status_options = array(
    1 => 'Skip',
    2 => 'Help',
    3 => "Complete"
);

    // 1 => '<i style="color:gray;" class="far fa-sign-out"></i> Skip',
    // 2 => '<i style="color:red;" class="far fa-exclamation-circle"></i> Help',
    // 3 => "<i style='color:green;' class='far fa-check-circle'></i> Complete"

include_once (APP_PATH_DOCROOT . "ProjectGeneral/header.php");
?>
<div class="row">
    <div class="col-12">
        <h3>AutoNotify to Alerts and Notifications Conversion</h3>
    </div>
</div>
<?php


$project_id = $module->getProjectId();
if (empty($project_id)) die ("Project ID Required");


// Load the current AutoNotify3
$an = new AutoNotify3($project_id);
$an->loadConfig();


// Build an array of all previous autonotify alerts and group by title
$prior_notifications = $an->getAllNotifications();
$notifications = [];
foreach ($prior_notifications as $notification) {
    $title      = trim($notification['title']);
    if (empty($notifications[$title])) $notifications[$title] = [];
    $notifications[$title][] = $notification;
}


// Make a new Alert Helper Object
$alert = new Alert($project_id, $module);




/**
 * A simple post to update the notes/status for this project's migration
 */
if (@$_POST['action'] == "update") {
    $status = @$_POST['status'];
    $note = @$_POST['note'];
    $pid = @$_GET['pid'];
    $result = $module->updateStatus($status,$note,$pid);
    $module->emDebug("Update Result", $result);
}


/**
 * Do the actual migration
 */
if (@$_POST['action'] == "migrate") {

    // TESTING - CLEARING EACH TIME
    // $alert->clearAlertsFromProject($project_id);
    $output = [];

    // Loop through each trigger:
    $update_required = false;
    foreach ($an->triggers as $id => $trigger) {
        // Copy the trigger since we are modifying it a bit here...
        $t     = $trigger;
        $title = $t['title'];
        $body  = $t['body'];


        // TODO: CHECK IF TITLE ALREADY EXISTS AS AN ALERT AND NOTIFICATION
        if ($alert->doesAlertExist($title)) {
            $module->emDebug("$title already exists as an Alert and Notification - skipping");
            $output[] = "$title NOT MIGRATED - title already exists an Alert and Notification";
            continue;
        }


        // HANDLE BODY FORMATTING
        if ($t['template'] == "standard") {
            global $redcap_version;
            $url  = APP_PATH_WEBROOT_FULL . "redcap_v{$redcap_version}/" . "DataEntry/record_home.php?pid={$project_id}&id=[record-name]";
            $body = $an->renderStandardMessage($body, $url, trim($title));
        } elseif ($t['template'] == "stanford") {
            $body = $an->renderStanfordMessage($body);
        }
        $t['body'] = $body;


        // CREATE A MATCHING ALERT FROM THIS NOTIFY TRIGGER
        $new_id   = $alert->createAlertFromTrigger($t);
        $output[] = "$title ($id) migrated to Alerts and Notifications";


        // Search for non-compatible piping:
        $re = '/((\[(?\'event_name\'\S*)\])?\[(?\'command\'[A-Za-z_-]*):?(?\'param1\'[A-Za-z_\d]*):?(?\'param2\'[^\]:]*):?(?\'param3\'[^\]:]*)\])/m';
        // find all the tags that match the above reg expression
        if (preg_match_all($re, $body, $matches, PREG_PATTERN_ORDER)) {
            foreach ($matches['command'] as $command) {
                if (!in_array($command, $module::VALID_SMART_VARS) && !in_array($command, \REDCap::getFieldNames()) && !in_array($command, \REDCap::getEventNames(true))) {
                    $module->emLog("Check Notification $title for correct use of $command");
                    $output[] = " - Check email to ensure \"$command\" is valid.";
                }
            }
        }


        if ($new_id) {
            // SUCCESS - should we deactivate the original trigger
            if ($t['enabled'] == 1) {
                // $module->emLog("Deactivating $project_id -> trigger $id after migration to alert");
                $an->triggers[$id]['enabled'] = 0;

                $output[] = " - Disabled alert $id, " . $title . " in autonotify config";
                REDCap::logEvent("Disabling AutoNotify Trigger", "Migrating $title to Alerts and Notifications");
                $update_required = true;
            }


            // MIGRATE OVER THE ALERTS
            if (!empty($notifications[$title])) {
                // We have previous an alerts
                $good = 0;
                $failed = 0;
                foreach ($notifications[$title] as $notification) {
                    $result = $alert->createNotificationFromAutoNotifyAlert($new_id, $notification, $trigger);
                    if (!$result) {
                        $module->emError("Failure to transfer over $title notification", $notification);
                        $failed++;
                    } else {
                        $good++;
                    }
                }
                if ($failed > 0) {
                    $output[] = " - Failed to migrate over $failed prior notification" . ($failed > 1 ? "s" : "");
                } else {
                    $output[] = " - Migrated $good prior notification" . ($good > 1 ? "s" : "");
                }
            }
        }
    }


    // SEE IF WE NEED TO RE-SAVE THE AUTONOTIFY CONFIG
    if ($update_required) {
        $an->config['triggers'] = json_encode($an->triggers);
        // $module->emDebug("Saving AutoNotify Update", $an->config);
        $output[] = " - updating AutoNotify configuration";
        $an->saveConfig();
    }

    // Update Note
    if (!empty($output)) {
        $note = "[" . date("Y-m-d H:i:s") . "] MIGRATION LOGS\n" . implode("\n",$output) . "\n\n" . $note;
        $result = $module->updateStatus($status,$note,$pid);

    }

}



// RENDER A SUMMARY OF WHAT HAS TO BE MIGRATED
?>
    <div class="row">

    </div>

    <div class="row ml-2">
        <form method="POST" name="form" style="width: 80%;">



            <div class="card mt-3 mb-3">
                <div class="card-header card-danger">
                    <h6>Attempt Migration</h6>
                </div>
                <div class="card-body">

                            <div class="ml-3">
            <table id="alerts">
                <thead>
                    <tr>
                        <th>AutoNotify<br>Title</th>
                        <th>AutoNotify<br>Enabled</th>
                        <th>AutoNotify<br>Alert Count</th>
                        <th>Alerts and<br>Notifications Match</th>
                    </tr>
                </thead>
                <tbody>
        <?php

            // $module->emDebug($notifications);

            foreach ($an->triggers as $id => $trigger) {
                // $module->emDebug($trigger);
                $title = trim($trigger['title']);
                $enabled = $trigger['enabled'] ? '<i style="color:green" class="far fa-check-circle"></i>' : '<i style="color:red" class="far fa-stop-circle"></i>';
                $count = count($notifications[$title]);

                // Does alert exist under Alerts and Notifications
                $alertExist = $alert->doesAlertExist($title) ? '<i style="color:green" class="far fa-check-circle"></i> Exists' : '<i style="color:red" class="far fa-stop-circle"></i> Missing';

                ?>
                    <tr>
                        <td><?php echo $title ?></td>
                        <td><?php echo $enabled ?></td>
                        <td><?php echo $count ?></td>
                        <td><?php echo $alertExist ?></td>
                    </tr>
                <?php
            }
        ?>
                </tbody>
            </table>
        </div>


                    <p>
                        This will attempt to move all AutoNotify alerts over to Alerts and Notifications.  It will then
                        mark the AutoNotify alert as 'disabled' so it will no longer fire.  However, it will leave the
                        AutoNotify in place.
                    </p>
                    <button class="btn btn-danger"  type="submit" name="action" value="migrate">Try To Migrate</button>
                </div>
            </div>


            <div class="card mt-3 mb-3">
                <div class="card-header card-secondary">
                    <h6>Update Status and Notes</h6>
                </div>
                <div class="card-body">

                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="status"><strong>Status</strong></label>
                        </div>
                        <select class="custom-select" name="status" id="status">
                            <?php
                                echo "<option>Select a status</option>";
                                foreach ($status_options as $k => $v) {
                                    echo "<option " . ($status == $k ? "selected" : "") . " value='$k'>$v</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="input-group input-group-sm mb-3" style="height: 250px;overflow: scroll;">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><strong>Notes</strong></span>
                        </div>
                        <textarea class="form-control" name="note" aria-label="Note"><?php echo $note?></textarea>
                    </div>

                    <div>
                        <button class="btn btn-primary" name="action" value="update">Save Notes/Status</button>
                    </div>
                </div>
            </div>

        </form>

    </div>

    <script>
        $(document).ready(function(){
            $('#alerts').DataTable();
        });
    </script>

