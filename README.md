# AutoNotify Migration Tool

The purpose of this tool is to migrate existing projects using AutoNotify to the new REDCap Alerts and Notifications system.

## How it works

This tool is used by super-users only.  

1. It displays all projects that are using AutoNotify on your server.
1. For each project, you can then being the migration of the AutoNotify rules (aka Triggers) into new REDCap Alerts.  The conversion process takes a few steps.
   1. We need to make a new Alert for each AutoNotify Trigger
   1. We need to migrate the history of 'fired' events from AutoNotify log entries into new rows in the redcap_alerts_sent table.
   1. Ensure migration of 'enhanced-piping' works as AutoNotify suppored a bunch of fancy tags that have different names in Smart Variables

Once a migration is complete, and the new alerts are active, you should remove the AutoNotify url from the DET on a migrated project or else you will receive double-notifications.

Once all projects have been migrated, you should remove the plugin from the system so no new projects are able to create more AutoNotify settings.

 
 


During migration, AutoNotify rules are deactivated.  This means they should no longer fire so participants shouldn't
receive multiple notifications (one from AutoNotify and one from Alerts and Notifications).

However, the DET url is left in-tact so if you are using the pre- and post- DET URL options at the bottom of
AutoNotify, they should continue to work (but both will occur PRE or POST Alerts and Notifications -- I haven't tested)
