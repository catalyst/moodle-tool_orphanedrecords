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
 * TODO Add description
 *
 * @package   TODO Add package name
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_orphanedrecords\reportbuilder\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;
use tool_orphanedrecords\orphanedrecords;

class orphaned_records extends base {

    /**
     * Database table that this entity uses.
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return ['tool_orphanedrecords'];
    }

    /**
     * Database tables that this entity uses and their default aliases.
     *
     * @return string[]
     */
    protected function get_default_table_aliases(): array {
        return ['tool_orphanedrecords' => 'tor'];
    }

    /**
     * {@inheritDoc}
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('report:entitiy', 'tool_orphanedrecords');
    }

    /**
     * {@inheritDoc}
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        // All the filters defined by the entity can also be used as conditions.
        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {

        $tablealias = $this->get_table_alias('tool_orphanedrecords');

        // Orphan ID column.
        $columns[] = (new column(
            'orphanid',
            new lang_string('report:column:orphanid', 'tool_orphanedrecords'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("$tablealias.orphanid")
            ->set_is_sortable(true);

        // Orphan table column.
        $columns[] = (new column(
            'orphantable',
            new lang_string('report:column:orphantable', 'tool_orphanedrecords'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("$tablealias.orphantable")
            ->set_is_sortable(true);

        // Orphan row column.
        $columns[] = (new column(
            'orphanrow',
            new lang_string('report:column:orphanrow', 'tool_orphanedrecords'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("$tablealias.orphanrow")
            ->set_is_sortable(false);

        // Status column.
        $columns[] = (new column(
            'status',
            new lang_string('report:column:status', 'tool_orphanedrecords'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$tablealias}.status")
            ->set_is_sortable(true)
            ->add_callback(static function($value): string {
                return get_string("status:{$value}", 'tool_orphanedrecords');
            });

        // Reason column.
        $columns[] = (new column(
            'reason',
            new lang_string('report:column:reason', 'tool_orphanedrecords'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$tablealias}.reason, {$tablealias}.reffields, {$tablealias}.reftable")
            ->set_is_sortable(false)
            ->add_callback(static function($reason, $row): string {
                return \tool_orphanedrecords\orphanedrecords::get_reason_text($reason, $row->reffields, $row->reftable);
            });

        // Start time column.
        $columns[] = (new column(
            'timecreated',
            new lang_string('report:column:timecreated', 'tool_orphanedrecords'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$tablealias}.timecreated")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate'], get_string('strftimedatetimeshortaccurate', 'core_langconfig'));

        // Modified time column.
        $columns[] = (new column(
            'timemodified',
            new lang_string('report:column:timemodified', 'tool_orphanedrecords'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$tablealias}.timemodified")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate'], get_string('strftimedatetimeshortaccurate', 'core_langconfig'));

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        global $DB;

        $tablealias = $this->get_table_alias('tool_orphanedrecords');

        // Orphan ID filter.
        $filters[] = (new filter(
            number::class,
            'orphanid',
            new lang_string('report:filter:orphanid', 'tool_orphanedrecords'),
            $this->get_entity_name(),
            "{$tablealias}.orphanid"
        ))
            ->add_joins($this->get_joins());

        // Orphan table filter.
        $filters[] = (new filter(
            text::class,
            'orphantable',
            new lang_string('report:filter:orphantable', 'tool_orphanedrecords'),
            $this->get_entity_name(),
            "{$tablealias}.orphantable"
        ))
            ->add_joins($this->get_joins());

        // Orphan row filter.
        $filters[] = (new filter(
            text::class,
            'orphanrow',
            new lang_string('report:filter:orphanrow', 'tool_orphanedrecords'),
            $this->get_entity_name(),
            "{$tablealias}.orphanrow"
        ))
            ->add_joins($this->get_joins());

        // Status filter.
        $filters[] = (new filter(
            select::class,
            'status',
            new lang_string('report:filter:status', 'tool_orphanedrecords'),
            $this->get_entity_name(),
            "{$tablealias}.status"
        ))
            ->add_joins($this->get_joins())
            ->set_options([
                orphanedrecords::STATUS_IGNORED => new lang_string(
                    'status:' . orphanedrecords::STATUS_IGNORED,
                    'tool_orphanedrecords'
                ),
                orphanedrecords::STATUS_PENDING => new lang_string(
                    'status:' . orphanedrecords::STATUS_PENDING,
                    'tool_orphanedrecords'
                ),
                orphanedrecords::STATUS_DELETED => new lang_string(
                    'status:' . orphanedrecords::STATUS_DELETED,
                    'tool_orphanedrecords'
                ),
            ]);

        // Created time filter.
        $filters[] = (new filter(
            date::class,
            'timecreated',
            new lang_string('report:filter:timecreated', 'tool_orphanedrecords'),
            $this->get_entity_name(),
            "{$tablealias}.timecreated"
        ))
            ->add_joins($this->get_joins())
            ->set_limited_operators([
                date::DATE_ANY,
                date::DATE_RANGE,
                date::DATE_PREVIOUS,
                date::DATE_CURRENT,
            ]);

        // Modified time filter.
        $filters[] = (new filter(
            date::class,
            'timemodified',
            new lang_string('report:filter:timemodified', 'tool_orphanedrecords'),
            $this->get_entity_name(),
            "{$tablealias}.timemodified"
        ))
            ->add_joins($this->get_joins())
            ->set_limited_operators([
                date::DATE_ANY,
                date::DATE_RANGE,
                date::DATE_PREVIOUS,
                date::DATE_CURRENT,
            ]);

        return $filters;
    }
}