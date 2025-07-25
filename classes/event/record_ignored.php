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

namespace tool_orphanedrecords\event;

use core\event\base;
use tool_orphanedrecords\orphanedrecords;

/**
 * Record restored event.
 */
class record_ignored extends base {

    /**
     * Init function.
     * @return void
     */
    protected function init(): void {
        $this->data['objecttable'] = orphanedrecords::TABLE;
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Get name.
     * @return string
     */
    public static function get_name(): string {
        return get_string('event:record_ignored', 'tool_orphanedrecords');
    }

    /**
     * Get description.
     * @return string
     */
    public function get_description(): string {
        $data = $this->get_data();
        return get_string(
            'event:record_ignored:description',
            'tool_orphanedrecords',
            [
                'id' => $data['objectid'],
                'userid' => $data['userid'],
                'reason' => get_string(
                    'reason:' . $data['other']['reason'],
                    'tool_orphanedrecords',
                    [
                        'reffields' => $data['other']['reffields'],
                        'reftable' => $data['other']['reftable'],
                    ]
                )
            ]
        );
    }
}
