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
 * Settings file.
 *
 * @package   tool_orphanedrecords
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add(
        'reports',
        new admin_externalpage(
            'toolorphanedrecords',
            get_string('settings:report', 'tool_orphanedrecords'),
            "$CFG->wwwroot/$CFG->admin/tool/orphanedrecords/index.php"
        )
    );

    $settings = new admin_settingpage('tool_orphanedrecords_settings', get_string('settings:category', 'tool_orphanedrecords'));
    $ADMIN->add('development', $settings);
    $settings->add(
        new admin_setting_heading(
            'tool_orphanedrecords/generalsettings',
            new lang_string('settings:generalheader', 'tool_orphanedrecords'),
            ''
        )
    );

    $tables = $DB->get_tables();
    ksort($tables);

    $settings->add(
        new admin_setting_configmultiselect(
            'tool_orphanedrecords/skip_tables',
            new lang_string('settings:skip_tables', 'tool_orphanedrecords'),
            new lang_string('settings:skip_tables:desc', 'tool_orphanedrecords'),
            ['grade_grades_history', 'logstore_standard_log'],
            $tables
        )
    );

    $settings->add(
        new admin_setting_configduration(
            'tool_orphanedrecords/deleted_lifetime',
            new lang_string('settings:deleted_lifetime', 'tool_orphanedrecords'),
            new lang_string('settings:deleted_lifetime:desc', 'tool_orphanedrecords'),
            30 * 86400
        )
    );

}
