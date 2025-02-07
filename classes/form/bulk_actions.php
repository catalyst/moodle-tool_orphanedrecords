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
 * Bulk actions form.
 *
 * @package   tool_orphanedrecords
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_orphanedrecords\form;

use action_link;
use moodle_url;
use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/datalib.php');

/**
 * Bulk actions form.
 *
 * @package   tool_orphanedrecords
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulk_actions extends moodleform {

    /** @var bool */
    protected $hasbulkactions = true;

    /** @var array|null */
    protected $actions = null;

    /**
     * Returns an array of action_link's of all bulk actions available for this user.
     *
     * @return array of action_link objects
     */
    public function get_actions(): array {
        if ($this->actions === null) {
            $this->actions = $this->build_actions();
            $this->hasbulkactions = !empty($this->actions);
        }

        return $this->actions;
    }

    /**
     * Builds the list of bulk user actions available for this user.
     *
     * @return array
     */
    protected function build_actions(): array {

        $actionstring = get_string('actions', 'moodle');
        $actions = [];
        $actions[$actionstring] = [];

        $actions[$actionstring]['delete'] = new action_link(
            new moodle_url('/admin/tool/orphanedrecords/index.php', ['action' => 'delete']),
            get_string('form:delete', 'tool_orphanedrecords')
        );
        $actions[$actionstring]['ignore'] = new action_link(
            new moodle_url('/admin/tool/orphanedrecords/index.php', ['action' => 'ignore']),
            get_string('form:ignore', 'tool_orphanedrecords')
        );
        $actions[$actionstring]['restore'] = new action_link(
            new moodle_url('/admin/tool/orphanedrecords/index.php', ['action' => 'restore']),
            get_string('form:restore', 'tool_orphanedrecords')
        );

        return $actions;
    }

    /**
     * Form definition
     */
    public function definition(): void {
        $mform =& $this->_form;

        // Most bulk actions perform a redirect on selection, so we shouldn't trigger formchange warnings (specifically because
        // the user must have _already_ changed the current form by selecting users to perform the action on).
        $mform->disable_form_change_checker();

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_LOCALURL);

        $mform->addElement('hidden', 'recordids');
        $mform->setType('recordids', PARAM_SEQUENCE);

        $actions = ['' => [0 => get_string('choose') . '...']];
        $bulkactions = $this->get_actions();
        foreach ($bulkactions as $category => $categoryactions) {
            $actions[$category] = array_map(fn($action) => $action->text, $categoryactions);
        }
        $objs = [];
        $objs[] = $selectel = $mform->createElement(
            'selectgroups',
            'action',
            get_string('form:recordbulk', 'tool_orphanedrecords'),
            $actions
        );
        $selectel->setHiddenLabel(true);
        $mform->addElement(
            'group',
            'actionsgrp',
            get_string('form:withselectedrecords', 'tool_orphanedrecords'),
            $objs,
            ' ',
            false);
    }

    /**
     * Is there at least one available bulk action in this form
     *
     * @return bool
     */
    public function has_bulk_actions(): bool {
        return $this->hasbulkactions;
    }
}
