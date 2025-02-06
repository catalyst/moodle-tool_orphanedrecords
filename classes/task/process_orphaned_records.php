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
 * Record restored event.
 *
 * @package   tool_orphanedrecords
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_orphanedrecords\task;

use core\task\scheduled_task;
use stdClass;
use tool_orphanedrecords\orphanedrecords;

/**
 * Record restored event.
 */
class process_orphaned_records extends scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task:process_orphaned_records', 'tool_orphanedrecords');
    }

    /**
     * Execute the task
     */
    public function execute(): void {
        global $DB;

        $dbman = $DB->get_manager();
        $schema = $dbman->get_install_xml_schema();
        $tables = $schema->getTables();
        $count = 0;
        $tablecount = count($tables);

        // Config checks.
        $checkgradegradeshistory = get_config('tool_orphanedrecords', 'check_grade_grades_history');

        // Load all of the foreign checks that we need to do.
        foreach ($tables as $table) {
            $count++;
            // Exclude log tables.
            mtrace("-----");
            if (str_contains($table->getName(), 'log')) {
                mtrace("Skipping table '{$table->getName()}' ($count of $tablecount)");
                continue;
            }
            // Exclude the grade_grades_history table if config specifies it.
            if ($table->getName() == 'grade_grades_history' && !$checkgradegradeshistory) {
                mtrace("Skipping table '{$table->getName()}' ($count of $tablecount)");
                continue;
            }

            mtrace("Processing table '{$table->getName()}' ($count of $tablecount)");

            // Check table exists.
            if (!$dbman->table_exists($table->getName())) {
                return;
            }

            orphanedrecords::save_new_orphaned_records($table);
        }
    }
}
