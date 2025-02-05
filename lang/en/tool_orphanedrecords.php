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
$string['event:record_deleted:description'] = 'Record deleted';
$string['event:record_ignored'] = 'Record ignored';
$string['event:record_ignored:description'] = 'Record ignored';
$string['event:record_restored'] = 'Record restored';
$string['event:record_restored:description'] = 'Record restored';
$string['form:delete'] = 'Delete';
$string['form:ignore'] = 'Ignore';
$string['form:restore'] = 'Restore';
$string['plugin:description'] = 'Insert description here';
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
$string['report:filter:orphanid'] = 'Orphaned ID';
$string['report:filter:orphanrow'] = 'Orphaned Row';
$string['report:filter:orphantable'] = 'Orphaned Table';
$string['report:filter:reason'] = 'Reason';
$string['report:filter:status'] = 'Status';
$string['report:filter:timecreated'] = 'Time created';
$string['report:filter:timemodified'] = 'Time modified';
$string['report:entitiy'] = 'Orphaned records';
$string['report:name'] = 'Orphaned records';
$string['settings:category'] = 'Orphaned records general settings';
$string['settings:generalheader'] = 'General settings';
$string['settings:check_grade_grades_history'] = 'Check "grade_grades_history" records';
$string['settings:check_grade_grades_history:desc'] = 'On large sites the "grade_grades_history" table can take upwards of serveral hours to run.
Disabling this can improve the runtime of the scheduled task.';
$string['settings:report'] = 'Orphaned records report';
$string['status:0'] = 'Pending';
$string['status:1'] = 'Ignored';
$string['status:2'] = 'Deleted';
$string['status:3'] = 'Restored';
$string['task:process_orphaned_records'] = 'Process orphaned records';

