<?php
namespace Stanford\AutonotifyMigrationTool;
/** @var \Stanford\AutonotifyMigrationTool\AutonotifyMigrationTool $module */

// RENDER PAGE
require APP_PATH_DOCROOT . "ControlCenter/header.php";

?>
    <h4>Convert AutoNotify to new Alerts and Notifications</h4>
    <p>
        The purpose of this EM is to help move projects using AutoNotify to Alerts and Notifications.  This typically
        means migration of the alert configs and then the history of alerts so users are not re-alerted again.
    </p>

    <p>
        The following projects are using AutoNotify on your server:
    </p>


        <table id="autonotify_projects" style="width:100%";>
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Last Event</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
<!--                <tr>-->
<!--                    <td></td>-->
<!--                    <td></td>-->
<!--                    <td></td>-->
<!--                    <td></td>-->
<!--                    <td></td>-->
<!--                </tr>-->
            </tbody>
        </table>

    <br>
    <button id="refresh" class="btn btn-primary">Refresh Table</button>

<script>

    var AN = {};

    $(document).ready(function() {
        AN.table = $('#autonotify_projects').DataTable({
            ajax: <?php echo json_encode($module->getUrl('pages/ajax.php') . "&action=all_projects"); ?>
        });
    });


    // Open detail page
    $('#autonotify_projects').on('click', '.goto_project', function(){
        var pid = $(this).data('pid');
        var url = <?php echo json_encode($module->getUrl("pages/amt.php")) ?> + "&pid=" + pid;
        window.open(url, '_blank');
    });

    // Refresh table
    $('#refresh').on('click', function(){
        AN.table.ajax.reload();
    });



</script>

<style>
    th {font-weight: bold;}


</style>

<?php

require APP_PATH_DOCROOT . "ControlCenter/footer.php";