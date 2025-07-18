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
 * Clear deleted events.
 *
 * @package   tool_orphanedrecords
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_orphanedrecords\task;

use core\task\scheduled_task;
use tool_orphanedrecords\orphanedrecords;

/**
 * Clear deleted events past the defined config window.
 */
class clear_deleted_records extends scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task:clear_deleted_records', 'tool_orphanedrecords');
    }

    /**
     * Execute the task
     */
    public function execute(): void {
        global $DB;

        $deletedlifetime = get_config('tool_orphanedrecords', 'deleted_lifetime');

        $params = [
            'deletedlifetime' => time() - $deletedlifetime,
            'status' => orphanedrecords::STATUS_DELETED,
        ];
        $select = 'status = :status AND timemodified <= :deletedlifetime';

        $count = $DB->count_records_select(
            orphanedrecords::TABLE,
            $select,
            $params
        );

        mtrace("Deleting $count records.");

        if ($count > 0) {
            $DB->delete_records_select(
                orphanedrecords::TABLE,
                $select,
                $params
            );
        }
    }
}
