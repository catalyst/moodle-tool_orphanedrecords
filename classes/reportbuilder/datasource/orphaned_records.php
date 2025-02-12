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

namespace tool_orphanedrecords\reportbuilder\datasource;

use context_system;
use core_reportbuilder\datasource;

/**
 * Orphaned records system report class.
 */
class orphaned_records extends datasource {

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

        $this->add_all_from_entities();

        // Set if report can be downloaded.
        $this->set_downloadable(true);
    }

    /**
     * Validates access to view this report.
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('moodle/site:config', context_system::instance());
    }

    /**
     * Return the default columns that will be added to the report upon creation, by {@see add_default_columns}
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'orphaned_records:orphanid',
            'orphaned_records:orphantable',
            'orphaned_records:status',
            'orphaned_records:reason',
            'orphaned_records:timecreated',
            'orphaned_records:timemodified',
        ];
    }

    /**
     * Return the filters that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return [
            'orphaned_records:orphanid',
            'orphaned_records:orphantable',
            'orphaned_records:status',
            'orphaned_records:reason',
            'orphaned_records:timecreated',
            'orphaned_records:timemodified',
        ];
    }

    public function get_default_conditions(): array {
        return [
            'orphaned_records:orphantable',
            'orphaned_records:status',
            'orphaned_records:reason',
        ];
    }
}
