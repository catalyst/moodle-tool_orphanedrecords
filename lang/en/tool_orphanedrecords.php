<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lang file.
 *
 * @package   tool_orphanedrecords
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['event:record_deleted'] = 'Record deleted';
$string['event:record_deleted:description'] = 'Record {$a->id} was deleted by user with id {$a->userid}. This record had the reason "{$a->reason}".';
$string['event:record_ignored'] = 'Record ignored';
$string['event:record_ignored:description'] = 'Record {$a->id} was ignored by user with id {$a->userid}. This record had the reason "{$a->reason}".';
$string['event:record_pending'] = 'Record set to pending';
$string['event:record_pending:description'] = 'Record {$a->id} was set to pending by user with id {$a->userid}.';
$string['event:record_restored'] = 'Record restored';
$string['event:record_restored:description'] = 'Record {$a->id} was restored by user with id {$a->userid}.';
$string['form:delete'] = 'Delete';
$string['form:ignore'] = 'Ignore';
$string['form:pending'] = 'Set pending';
$string['form:recordbulk'] = 'Bulk record actions';
$string['form:restore'] = 'Restore';
$string['form:withselectedrecords'] = 'With selected records';
$string['pluginname'] = 'Orphaned records';
$string['reason:0'] = 'Potential foreign key violation against table "{$a->reftable}" with field(s) "{$a->reffields}"';
$string['reason:1'] = 'Missing activity instance record';
$string['reason:2'] = 'Missing course module record';
$string['reason:3'] = 'Missing course record';
$string['reason:4'] = 'Missing course section record';
$string['redirect:error'] = 'Error processing record id: {$a->id} Error {$a->message}';
$string['redirect:success'] = 'Record(s) processed successfully';
$string['report:column:orphanid'] = 'Orphaned ID';
$string['report:column:orphanrow'] = 'Orphaned Row';
$string['report:column:orphantable'] = 'Orphaned Table';
$string['report:column:reason'] = 'Reason';
$string['report:column:status'] = 'Status';
$string['report:column:timecreated'] = 'Time created';
$string['report:column:timemodified'] = 'Time modified';
$string['report:description'] = "This report shows all of the records that have been identified as orphaned by the scheduled task.<br/>
For each record you can opt to:<br/>
<ul>
    <li><strong>Ignore</strong> - This flags that the record is as it should be. For example, the <code>course</code> table contains a foreign key constraint on the field <code>originalcourseid</code>. This is populated when restoring/duplicating a course on the site, however this is not updated when the original course is removed. This however is an acceptable 'orphan' as it does not impact the site and is not used after the fact.</li>
    <li><strong>Delete</strong> - This will store a serialized snapshot of the row being deleted in the <code>tool_orphanedrecords</code> table and remove the original. The reason for storing the row is for the restore functionality.</li>
    <li><strong>Restore</strong> - This will recreate the originally deleted record back in the database. This can be used in instances where removal of a record has impacted the site negativly in some way.</li>
</ul>
This report is designed to be used by developers to initially identify records for deletion.<br/>
This should <strong>NOT</strong> be used to action records without first checking the potential impact of doing so.";
$string['report:entitiy'] = 'Orphaned records';
$string['report:filter:orphanid'] = 'Orphaned ID';
$string['report:filter:orphanrow'] = 'Orphaned Row';
$string['report:filter:orphantable'] = 'Orphaned Table';
$string['report:filter:reason'] = 'Reason';
$string['report:filter:reason:0'] = 'Potential foreign key violation';
$string['report:filter:reason:1'] = 'Missing activity instance record';
$string['report:filter:reason:2'] = 'Missing course module record';
$string['report:filter:reason:3'] = 'Missing course record';
$string['report:filter:reason:4'] = 'Missing course section record';
$string['report:filter:status'] = 'Status';
$string['report:filter:timecreated'] = 'Time created';
$string['report:filter:timemodified'] = 'Time modified';
$string['report:name'] = 'Orphaned records';
$string['settings:category'] = 'Orphaned records general settings';
$string['settings:generalheader'] = 'General settings';
$string['settings:deleted_lifetime'] = 'Deleted lifetime';
$string['settings:deleted_lifetime:desc'] = 'The length of time that the deleted backup data in `tool_orphanedrecords` will be stored before being deleted permanently.';
$string['settings:skip_tables'] = 'Skip tables';
$string['settings:skip_tables:desc'] = 'On large sites certain tables, such as "grade_grades_history", can take hours to run.
Tables selected here will be skipped from analysis.';
$string['settings:report'] = 'Orphaned records report';
$string['status:0'] = 'Pending action';
$string['status:1'] = 'Ignored';
$string['status:2'] = 'Deleted';
$string['status:3'] = 'Restored';
$string['task:clear_deleted_records'] = 'Clear deleted records';
$string['task:process_orphaned_records'] = 'Discover orphaned records';

