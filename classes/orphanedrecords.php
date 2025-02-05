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
 * Orphaned records class.
 *
 * @package   tool_orphanedrecords
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_orphanedrecords;

use context_system;
use core\output\notification;
use moodle_recordset;
use stdClass;
use tool_orphanedrecords\event\record_deleted;
use tool_orphanedrecords\event\record_ignored;
use tool_orphanedrecords\event\record_restored;
use xmldb_table;

class orphanedrecords {

    const STATUS_PENDING = 0;
    const STATUS_IGNORED = 1;
    const STATUS_DELETED = 2;
    const STATUS_RESTORED = 3;

    const REASON_FOREIGNKEY = 0;
    const REASON_MISSINGINSTANCE = 1;
    const REASON_MISSINGMODULE = 2;
    const REASON_MISSINGCOURSE = 3;
    const REASON_MISSINGSECTION = 4;

    const TABLE = 'tool_orphanedrecords';

    /**
     * Get the orphaned records for a specific table.
     *
     * @param xmldb_table $table
     * @return void
     */
    public static function save_new_orphaned_records(xmldb_table $table): void {
        global $DB;

        $tablename = $table->getName();
        $dbman = $DB->get_manager();
        $sqlchecks = [];

        foreach ($table->getKeys() as $key) {
            if ($key->getRefTable() !== null && $dbman->table_exists($key->getRefTable())) {
                // We have a foreign check, so build the SQL.
                // Find all records in the table where the record in the foreign constraint is missing.
                // This can result in false positives such as mdl_course.originalcourseid as this doesn't get removed when the
                // original course is deleted. But that's why we have the "approved" status.
                $joinitems = [];
                $whereitems = ['b.id IS NULL', 'tor.id IS NULL'];

                foreach ($key->getRefFields() as $fieldkey => $fieldvalue) {
                    $joinitems[] = "target.{$key->getFields()[$fieldkey]} = b.$fieldvalue";
                    // We use CAST to CHAR to exclude NULL, 0, and '' at the same time.
                    $whereitems[] = $DB->sql_cast_to_char("target.{$key->getFields()[$fieldkey]}") . " != ''";
                    $whereitems[] = $DB->sql_cast_to_char("target.{$key->getFields()[$fieldkey]}") . " != '0'";
                }
                unset($fieldkey);
                unset($fieldvalue);

                $joins = implode(' AND ', $joinitems);
                $wheres = implode(' AND ', $whereitems);
                unset($joinitems);
                unset($whereitems);
                $sqlchecks[] = "SELECT
                        target.id,
                        '{$tablename}' AS orphantable,
                        " . self::REASON_FOREIGNKEY . " AS orphanreason,
                        '" . implode('|', $key->getFields()) . "' AS reffields,
                        '{$key->getRefTable()}' AS reftable
                    FROM {{$tablename}} target
                    LEFT JOIN {{$key->getRefTable()}} b
                        ON {$joins}
                    LEFT JOIN {" . self::TABLE . "} tor
                        ON tor.orphanid = target.id
                        AND tor.orphantable = '{$tablename}'
                        AND tor.reason = " . self::REASON_FOREIGNKEY . "
                    WHERE {$wheres}";
                unset($wheres);
                unset($joins);
            }
        }

        // Additional checks for non-foreign keys on the table.
        $activity = $DB->get_record('modules', ['name' => $tablename]);
        switch ($tablename) {
            // Check if it is a module table.
            case !empty($activity):
                // Check for missing module records.
                $sqlchecks[] = "SELECT
                        target.id,
                        '{$tablename}' as orphantable,
                        " . self::REASON_MISSINGMODULE . " AS orphanreason,
                        '' AS reffields,
                        '' AS reftable
                    FROM {{$tablename}} target
                    LEFT JOIN {course_modules} b
                        ON target.id = b.instance
                        AND b.module = {$activity->id}
                    LEFT JOIN {" . self::TABLE . "} tor
                        ON tor.orphanid = target.id
                        AND tor.orphantable = '{$tablename}'
                        AND tor.reason = " . self::REASON_MISSINGMODULE . "
                    WHERE b.id IS NULL
                    AND tor.id IS NULL";
                break;
            case 'course_modules':
                // Check the activity instance tables.
                $activities = $DB->get_records('modules');
                foreach ($activities as $activity) {
                    // If the activity has a table then add the sql.
                    if ($dbman->table_exists($activity->name)) {
                        // First add the check for missing instance records.
                        $sqlchecks[] = "SELECT
                                target.id,
                                'course_modules' as orphantable,
                                " . self::REASON_MISSINGINSTANCE . " AS orphanreason,
                                '' AS reffields,
                                '' AS reftable
                            FROM {course_modules} target
                            LEFT JOIN {{$activity->name}} b
                                ON target.instance = b.id
                            LEFT JOIN {" . self::TABLE . "} tor
                                ON tor.orphanid = target.id
                                AND tor.orphantable = 'course_modules'
                                AND tor.reason = " . self::REASON_MISSINGINSTANCE . "
                            WHERE b.id IS NULL
                            AND target.module = {$activity->id}
                            AND tor.id IS NULL";
                    }
                }

                // Check for course modules with no course.
                $sqlchecks[] = "SELECT
                        target.id,
                        'course_modules' as orphantable,
                        " . self::REASON_MISSINGCOURSE . " AS orphanreason,
                        '' AS reffields,
                        '' AS reftable
                    FROM {course_modules} target
                    LEFT JOIN {course} c
                        ON target.course = c.id
                    LEFT JOIN {" . self::TABLE . "} tor
                        ON tor.orphanid = target.id
                        AND tor.orphantable = 'course_modules'
                        AND tor.reason = " . self::REASON_MISSINGCOURSE . "
                    WHERE c.id IS NULL
                    AND tor.id IS NULL";

                // Check for course modules with no sections.
                $sqlchecks[] = "SELECT
                        target.id,
                        'course_modules' as orphantable,
                        " . self::REASON_MISSINGSECTION . " AS orphanreason,
                        '' AS reffields,
                        '' AS reftable
                    FROM {course_modules} target
                    LEFT JOIN {course_sections} cs
                        ON target.section = cs.id
                    LEFT JOIN {" . self::TABLE . "} tor
                        ON tor.orphanid = target.id
                        AND tor.orphantable = 'course_modules'
                        AND tor.reason = " . self::REASON_MISSINGSECTION . "
                    WHERE cs.id IS NULL
                    AND tor.id IS NULL";
                break;
            case 'course_sections':
                // Check for course sections with no course.
                $sqlchecks[] = "SELECT
                        target.id,
                        'course_sections' as orphantable,
                        " . self::REASON_MISSINGCOURSE . " AS orphanreason,
                        '' AS reffields,
                        '' AS reftable
                    FROM {course_sections} target
                    LEFT JOIN {course} c
                        ON target.course = c.id
                    LEFT JOIN {" . self::TABLE . "} tor
                        ON tor.orphanid = target.id
                        AND tor.orphantable = 'course_sections'
                        AND tor.reason = " . self::REASON_MISSINGCOURSE . "
                    WHERE c.id IS NULL
                    AND tor.id IS NULL";
                break;
            case 'grade_items':
                // Check for grade items with no course module record.
                $sqlchecks[] = "SELECT
                        target.id,
                        'grade_items' as orphantable,
                        " . self::REASON_MISSINGMODULE . " AS orphanreason,
                        '' AS reffields,
                        '' AS reftable
                    FROM {grade_items} target
                    LEFT JOIN {modules} m 
                        ON target.itemmodule = m.name
                    LEFT JOIN {course_modules} cm
                        ON cm.instance = target.iteminstance
                        AND cm.module = m.id
                    LEFT JOIN {" . self::TABLE . "} tor
                        ON tor.orphanid = target.id
                        AND tor.orphantable = 'grade_items'
                        AND tor.reason = " . self::REASON_MISSINGMODULE . "
                    WHERE itemtype = 'mod'
                    AND cm.id IS NULL
                    AND tor.id IS NULL";
                break;
        }

        // Run each of the checks and save the orphaned records.
        $recordcount = 0;

        // Process the SQL in batches of 1m to account for memory usage.
        // Early tests showed tables such as grade_grades_history returning ~ 1 million records without issue.
        $sqlbatchsize = 1000000;
        foreach ($sqlchecks as $sqlcheck) {
            while (true) {
                // We always request the next batch size starting at 0 as the previous entries
                // will have already been loaded so won't return with the left join on the tool table.
                $records = $DB->get_records_sql($sqlcheck, [], 0, $sqlbatchsize);
                if ($records) {
                    self::save_records($records);
                    $recordcount += count($records);
                    if (!PHPUNIT_TEST) {
                        mtrace("Found " . count($records) . " orphaned records.");
                    }
                } else {
                    break;
                }
            }
        }
        if (!PHPUNIT_TEST) {
            mtrace("Found {$recordcount} total orphaned records for table.");
        }
    }

    /**
     * Save the orphaned records.
     *
     * @param array $records
     * @return void
     */
    public static function save_records(array $records): void {
        global $DB;

        $inserts = [];
        foreach ($records as $record) {
            // Build the record.
            $data = new stdClass();
            $data->orphanid = $record->id;
            $data->orphantable = $record->orphantable;
            $data->status = self::STATUS_PENDING;
            $data->reason = $record->orphanreason;
            $data->reffields = $record->reffields;
            $data->reftable = $record->reftable;
            $data->timecreated = time();
            $data->timemodified = time();

            $inserts[] = $data;
        }

        // Check for any remaining batch items and save them.
        if (count($inserts) > 0) {
            $DB->insert_records(self::TABLE, $inserts);
        }
        unset($inserts);
    }

    /**
     * Get the textual reason for the orphaned record.
     *
     * @param $reason
     * @param $reffields
     * @param $reftable
     * @return string
     */
    public static function get_reason_text($reason, $reffields, $reftable): string {
        return match ($reason) {
            self::REASON_FOREIGNKEY => get_string(
                'reason:' . self::REASON_FOREIGNKEY,
                'tool_orphanedrecords',
                [
                    'reffields' => $reffields,
                    'reftable' => $reftable,
                ]
            ),
            default => get_string('reason:' . $reason, 'tool_orphanedrecords'),
        };
    }

    /**
     * Process the form data and loop through for each record passed.
     *
     * @param stdClass $data
     * @return void
     */
    public static function process_form(stdClass $data): void {
        foreach (explode(',', $data->recordids) as $recordid) {
            self::process_record($recordid, $data->action);
        }
    }

    /**
     * Process each record, setting the correct status, perform the require actions, and firing the trigger.
     *
     * @param int $id
     * @param string $action
     * @return void
     */
    public static function process_record(int $id, string $action): void {
        global $DB;

        $record = $DB->get_record(self::TABLE, ['id' => $id]);
        $eventparams = array('context' => context_system::instance());
        $event = null;
        switch ($action) {
            case 'ignore':
                $record->status = self::STATUS_IGNORED;
                $event = record_ignored::create($eventparams);
                break;
            case 'delete':
                $record->status = self::STATUS_DELETED;
                $originalrecord = $DB->get_record($record->orphantable, ['id' => $record->orphanid]);
                if ($originalrecord) {
                    $record->orphanrow = serialize($originalrecord);
                    $DB->delete_records($record->orphantable, ['id' => $record->orphanid]);
                }
                $event = record_deleted::create($eventparams);
                break;
            case 'restore':
                $record->status = self::STATUS_RESTORED;
                $originalrecord = unserialize($record->orphanrow);
                if (!$DB->record_exists($record->orphantable, ['id' => $originalrecord->id])) {
                    $originalrecord = (array) $originalrecord;

                    // Clean the data to remove additional elements from the queries.
                    $columns = $DB->get_columns($record->orphantable);
                    foreach ($originalrecord as $field => $value) {
                        if (!isset($columns[$field])) {
                            unset($originalrecord[$field]);
                        }
                    }
                    $DB->insert_record_raw($record->orphantable, $originalrecord, false, false, true);
                    $event = record_restored::create($eventparams);
                }
                break;
        }

        $record->timemodified = time();
        $DB->update_record(self::TABLE, $record);
        $event?->trigger();
    }
}
