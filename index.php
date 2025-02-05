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
 * Index files.
 *
 * @package   tool_orphanedrecords
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use core_reportbuilder\system_report_factory;
use tool_orphanedrecords\form\bulk_actions;
use tool_orphanedrecords\orphanedrecords;

require(dirname(__FILE__, 4) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/user_bulk_forms.php');

admin_externalpage_setup('toolorphanedrecords');

$action = optional_param('action', null, PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
// Single item has been selected.
if ($action && $id) {
    orphanedrecords::process_record($id, $action);
    redirect($PAGE->url, get_string('redirect:success', 'tool_orphanedrecords'), null, notification::NOTIFY_SUCCESS);
}

// Load the bulk actions form.
$bulkactions = new bulk_actions(new moodle_url('/admin/tool/orphanedrecords/index.php'), [], 'post', '', ['id' => 'bulk-action-form']);
$bulkactions->set_data(['returnurl' => $PAGE->url->out_as_local_url(false)]);

// Bulk action submitted.
if ($data = $bulkactions->get_data()) {
    orphanedrecords::process_form($data);
    redirect($PAGE->url, get_string('redirect:success', 'tool_orphanedrecords'), null, notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_orphanedrecords'));
echo get_string('plugin:description', 'tool_orphanedrecords');

echo html_writer::start_div('', ['data-region' => 'report-list-wrapper']);

// Output the report.
$report = system_report_factory::create(\tool_orphanedrecords\reportbuilder\systemreports\orphaned_records::class,
    context_system::instance(), parameters: ['withcheckboxes' => $bulkactions->has_bulk_actions()]);
echo $report->output();

// Add the JS if it is required.
if ($bulkactions->has_bulk_actions()) {
    $PAGE->requires->js_call_amd('tool_orphanedrecords/bulk_actions', 'init');
    $bulkactions->display();
}

echo html_writer::end_div();

echo $OUTPUT->footer();