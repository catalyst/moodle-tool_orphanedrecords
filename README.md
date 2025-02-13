## Development Tool Orphaned Records

Description
------------------------

The purpose of this plugin is to work on identifying records within the database that are considered orphaned.
Whilst this can be done directly on the database, the purpose of the plugin was to allow for the recording, deletion,
and if needed, quick restoration of database records without requiring database access.

This is done through two main methods:

1. Looking at the install.xml files within the site and building a list of foreign key constraints. Whilst Moodle
   does not enforce this, it is useful to identify records that should be linked but where that link has been broken.
2. Manual SQL to identify areas of the system where there should be links, but aren't. Currently the following
   checks are made:

* Check each activity table for missing `course_modules` records.
  By looking at each module record to get the activity table name, the activity table (i.e `scorm`, `survey`)
  is then joined on the `course_modules` table looking for where the `course_modules` record doesn't exist.
* Check each `course_modules` table record for missing activity table records.
  This is the inverse of the above. We join the `course_modules` table onto each of the activity tables
  (i.e `scorm`, `survey`) looking for where the activity table record is missing.
* Check for `course_modules` records without matching `course` records.
* Check for `course_modules` records without matching `course_sections` records.
* Check for `course_sections` records without matching `course` records.
* Check for `grade_items` records without matching `course_modules` records.

The records that have been identified have their ID and table name stored in the table `tool_orphanedrecords`
with the status of PENDING.

Log tables are excluded from the scheduled task.

Report
------

Once loaded into the plugins table they can be viewed with the report source 'Orphaned records' which is accessed via the
Development tab of the admin settings called "Orphaned records report".

From there each record can be actioned as follows (both singularly and individually):

* Ignore - This flags that the record is as it should be. For example, the `course` table contains a foreign key constraint 
  on the field `originalcourseid`. This is populated when restoring/duplicating a course on the site. However this is not updated
  when the original course is removed. This however is an acceptable 'orphan' as it does not impact the site and is not used after the fact.
* Delete - This will store a serialized snapshot of the row being deleted in the `tool_orphanedrecords` table and remove the original.
  The reason for storing the row is for the restore functionality.
* Restore - This will recreate the originally deleted record back in the database. This can be used in instances where removal of a record
  has impacted the site negativly in some way.

In addition to the report interface that allows for individual and bulk records to be deleted, and due to the possibility of larger volumes of errors,
a CLI script has also been added that allows for mass action on certain record groups.
The CLI script can be found under /admin/tool/orphanedrecords/cli/process.php and the --help argument will provide the following:

```
CLI processing of orphaned records in the tool_orphaned records table.
orphanid and/or reason, along with orphantable are used to identify the records to action.

Options:
 -h, --help             Print out this help
 -a, --action           The action you would like to perform. Available actions are
                        - ignore
                        - delete
                        - restore
 -r, --reason           The reason that the orphaned record was flagged. Available reasons are:
                        - 0 (Foreign key violations);
                        - 1 (Missing module instance record i.e scorm record);
                        - 2 (Missing course_module record );
                        - 3 (Missing course record);
                        - 4 (Missing section record);
 -i, --orphanid         The id of the orphaned table record (i.e the course.id record).
                        This must be used in conjunction with 'orphantable'
 -t, --orphantable      The orphan table i.e course, course_module. Note, we do not store the prefix.
 --reftable             The forign field table i.e course for enrol.courseid
 --reffields             The forign fields i.e courseid for enrol.courseid
 -d, --dryrun           Run the script without updating/deleting records.
                        This is set to 1 by default so needs setting to 0 to execute.
```

Settings 
--------

Settings are available under the Development tab of the admin settings called "Orphaned records general settings".

"Check `grade_grades_history` record" - The reason for this is that on large sites this table can contain billions of records
which can take dozens of hours to run.

Contributing and support
------------------------

Issues, and pull requests using github are welcome and encouraged!

https://github.com/catalyst/moodle-tool_orphanedrecords/issues

If you would like commercial support or would like to sponsor additional improvements
to this plugin please contact us:

https://www.catalyst-eu.net/contact-us

Warm thanks
-----------

Thank you to QMUL for funding the development work for this project

Thank you to Andrew Hancox for supporting this work with his investigations and database work.

Crafted by Catalyst IT
----------------------

This plugin was developed by Catalyst EU:

https://www.catalyst-eu.net/