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

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add(
        'development',
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

    $settings->add(
        new admin_setting_configcheckbox(
            'tool_orphanedrecords/check_grade_grades_history',
            new lang_string('settings:check_grade_grades_history', 'tool_orphanedrecords'),
            new lang_string('settings:check_grade_grades_history:desc', 'tool_orphanedrecords'),
            ''
        )
    );
}
