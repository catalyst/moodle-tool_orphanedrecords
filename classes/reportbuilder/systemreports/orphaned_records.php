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
 * Orphaned records system report.
 *
 * @package   tool_orphanedrecords
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_orphanedrecords\reportbuilder\systemreports;

use context_system;
use core_reportbuilder\local\helpers\database;
use core_reportbuilder\local\report\action;
use core_reportbuilder\system_report;
use lang_string;
use moodle_url;
use pix_icon;
use stdClass;
use tool_orphanedrecords\orphanedrecords;

/**
 * Orphaned records system report class.
 */
class orphaned_records extends system_report {

    /**
     * Return the name of the report.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('report:name', 'tool_orphanedrecords');
    }

    /**
     * {@inheritDoc}
     */
    protected function initialise(): void {

        // Our main entity, it contains all of the column definitions that we need.
        $entitymain = new \tool_orphanedrecords\reportbuilder\entities\orphaned_records();
        $entitymainalias = $entitymain->get_table_alias('tool_orphanedrecords');

        $this->set_main_table('tool_orphanedrecords', $entitymainalias);
        $this->add_entity($entitymain);
        $this->add_base_fields("{$entitymainalias}.id, {$entitymainalias}.status");

        if ($this->get_parameter('withcheckboxes', false, PARAM_BOOL)) {
            $this->set_checkbox_toggleall(static function(\stdClass $row): array {
                return [$row->id, get_string('select')];
            });
        }

        // Now we can call our helper methods to add the content we want to include in the report.
        $this->add_columns();
        $this->add_filters();
        $this->add_actions();

        // Set if report can be downloaded.
        $this->set_downloadable(true);
    }

    /**
     * Adds the columns we want to display in the report.
     *
     * @return void
     */
    public function add_columns(): void {
        $columns = [
            'orphaned_records:orphanid',
            'orphaned_records:orphantable',
            'orphaned_records:status',
            'orphaned_records:reason',
            'orphaned_records:timecreated',
            'orphaned_records:timemodified',
        ];

        $this->add_columns_from_entities($columns);

        // It's possible to set a default initial sort direction for one column.
        $this->set_initial_sort_column('orphaned_records:timecreated', SORT_DESC);
    }

    /**
     * Adds the filters we want to display in the report.
     *
     * @return void
     */
    public function add_filters(): void {
        $filters = [
            'orphaned_records:orphanid',
            'orphaned_records:orphantable',
            'orphaned_records:status',
            'orphaned_records:reason',
            'orphaned_records:timecreated',
            'orphaned_records:timemodified',
        ];

        $this->add_filters_from_entities($filters);
    }

    /**
     * Add the system report actions. An extra column will be appended to each row, containing all actions added here
     *
     * Note the use of ":id" placeholder which will be substituted according to actual values in the row
     */
    protected function add_actions(): void {

        // Delete action. It will be only shown if the statsu is deleted..
        $this->add_action((new action(
            new moodle_url(
                '/admin/tool/orphanedrecords/index.php',
                ['id' => ':id', 'action' => 'delete']
            ),
            new pix_icon('t/delete', '', 'core'),
            [],
            false,
            new lang_string('form:delete', 'tool_orphanedrecords')
        ))->add_callback(function(stdClass $row): bool {
            return $row->status != orphanedrecords::STATUS_DELETED;
        }));

        // Restore action.
        $this->add_action((new action(
            new moodle_url(
                '/admin/tool/orphanedrecords/index.php',
                ['id' => ':id', 'action' => 'restore']
            ),
            new pix_icon('t/restore', '', 'core'),
            [],
            false,
            new lang_string('form:restore', 'tool_orphanedrecords')
        ))->add_callback(function(stdClass $row): bool {
            return $row->status == orphanedrecords::STATUS_DELETED;
        }));

        // Ignore action.
        $this->add_action((new action(
            new moodle_url(
                '/admin/tool/orphanedrecords/index.php',
                ['id' => ':id', 'action' => 'ignore']
            ),
            new pix_icon('t/show', '', 'core'),
            [],
            false,
            new lang_string('form:ignore', 'tool_orphanedrecords')
        ))->add_callback(function(stdClass $row): bool {
            return $row->status == orphanedrecords::STATUS_PENDING;
        }));

        // Pending action.
        $this->add_action((new action(
            new moodle_url(
                '/admin/tool/orphanedrecords/index.php',
                ['id' => ':id', 'action' => 'pending']
            ),
            new pix_icon('t/hide', '', 'core'),
            [],
            false,
            new lang_string('form:pending', 'tool_orphanedrecords')
        ))->add_callback(function(stdClass $row): bool {
            return $row->status == orphanedrecords::STATUS_IGNORED;
        }));

    }

    /**
     * Validates access to view this report.
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('moodle/site:config', context_system::instance());
    }
}
