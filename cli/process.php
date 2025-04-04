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
 * CLI script to mass process records.
 *
 * @package   tool_orphanedrecords
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_orphanedrecords\orphanedrecords;

define('CLI_SCRIPT', true);

require(dirname(__FILE__, 5) . '/config.php');
require_once("{$CFG->libdir}/clilib.php");

list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'action' => null,
        'orphanid' => null,
        'reason' => null,
        'orphantable' => null,
        'reftable' => null,
        'reffields' => null,
        'dryrun' => true,
    ], [
        'h' => 'help',
        'a' => 'action',
        'i' => 'orphanid',
        'r' => 'reason',
        't' => 'orphantable',
        'd' => 'dryrun',
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

$helptext = <<<EOT
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
EOT;

// Extract the options as their own variables.
$help = $options['help'];
$action = $options['action'];
$orphanid = $options['orphanid'];
$orphantable = $options['orphantable'];
$reason = $options['reason'];
$reffields = $options['reffields'];
$reftable = $options['reftable'];
$dryrun = $options['dryrun'];

// Display the help text.
if ($help) {
    cli_writeln($helptext);
    exit(0);
}

if (!$orphantable) {
    cli_error("Missing orphan table - required");
}

if (is_null($orphanid) && is_null($reason)) {
    cli_error("Missing orphan id or reason - required");
}

if (!$action) {
    cli_error("Missing action $action");
}

if (!in_array($action, ['delete', 'ignore', 'restore'])) {
    cli_error("Invalid action $action");
}

if ($dryrun) {
    cli_writeln("Dry run enabled");
}

$sql = "FROM {" . orphanedrecords::TABLE . "} WHERE 1 = 1";
$params = [];

// Add the params based on CLI args.
if ($orphanid) {
    $params['orphanid'] = $orphanid;
    $sql .= " AND orphanid = :orphanid";
}

if ($reason) {
    $params['reason'] = $reason;
    $sql .= " AND reason = :reason";
}

if ($orphantable) {
    $params['orphantable'] = $orphantable;
    $sql .= " AND orphantable = :orphantable";
}

if ($reffields) {
    $params['reffields'] = $reffields;
    $sql .= " AND reffields = :reffields";
}

if ($reftable) {
    $params['reftable'] = $reftable;
    $sql .= " AND reftable = :reftable";
}

switch ($action) {
    case 'delete':
        $sql .= " AND status != :status";
        $params['status'] = orphanedrecords::STATUS_DELETED;
        break;
    case 'ignore':
        $sql .= " AND status != :status";
        $params['status'] = orphanedrecords::STATUS_IGNORED;
        break;
    case 'restore':
        $sql .= " AND status != :status";
        $params['status'] = orphanedrecords::STATUS_RESTORED;
        break;
    default:
        break;
}

$recordcount = $DB->count_records_sql("SELECT count(1) " . $sql, $params);
if (!$recordcount) {
    cli_error("No orphaned record found");
}

raise_memory_limit(MEMORY_UNLIMITED);
$records = $DB->get_recordset_sql("SELECT * " . $sql, $params);
$processedcount = 0;
$counter = 0;
$errors = [];
if (cli_input("Are you sure you would like attempt to {$action} {$recordcount} record(s) (y/n)?", 'n', ['y', 'n']) == 'y') {
    // Start the progress bar.
    $progressbar = new progress_bar();
    $progressbar->create();
    foreach ($records as $record) {
        // Increment counter for progress bar.
        $counter++;
        $progressbar->update($counter, $recordcount, "Performing '$action'");
        if ($action == 'restore' && $record->status != orphanedrecords::STATUS_DELETED) {
            $errors[] = "Record {$record->id} cannot be restored.";
            continue;
        }
        // No errors, so process the record.
        $processedcount++;
        if (!$dryrun) {
            orphanedrecords::process_record($record->id, $action);
        }
    }
    if ($dryrun) {
        cli_writeln("$processedcount records out of a possible $recordcount will be actioned once --dryrun is disabled (see below for errors)");
    } else {
        cli_writeln("$processedcount records out of a possible $recordcount actioned (see below for errors)");
    }
    foreach ($errors as $error) {
        cli_writeln($error);
    }
}
$records->close();
