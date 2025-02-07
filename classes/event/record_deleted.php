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
 * Record deleted event.
 *
 * @package   tool_orphanedrecords
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_orphanedrecords\event;

use core\event\base;
use tool_orphanedrecords\orphanedrecords;

/**
 * Record deleted event.
 */
class record_deleted extends base {

    /**
     * Init function.
     * @return void
     */
    protected function init(): void {
        $this->data['objecttable'] = orphanedrecords::TABLE;
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Get name.
     * @return string
     */
    public static function get_name(): string {
        return get_string('event:record_deleted', 'tool_orphanedrecords');
    }

    /**
     * Get description.
     * @return string
     */
    public function get_description(): string {
        return get_string(
            'event:record_deleted:description',
            'tool_orphanedrecords'
        );
    }
}
