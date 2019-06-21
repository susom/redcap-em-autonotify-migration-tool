<?php
namespace Stanford\AutonotifyMigrationTool;
/** @var \Stanford\AutonotifyMigrationTool\AutonotifyMigrationTool $module */


$action = $_REQUEST['action'];

if (empty($action)) {
    $module->emError("Invalid Action", $_REQUEST);
    exit();
}

switch ($action) {
    case "all_projects":
        // Get all projects as a table

        $projects = $module->scanForAutoNotifyProjects();

        $result = array("data" => $projects);
        break;
    case "set_status":
        // NOT USED
        // Sets the status for a project in terms of conversion
        $status = @$_POST['status'];
        $notes = @$_POST['notes'];
        $pid = @$_GET['pid'];
        $result = $module->updateStatus($status,$notes,$pid);
        break;
    default:
        $result = false;
        $module->emError("Invalid action: $action");
}


header('Content-Type: application/json');

echo json_encode($result);
