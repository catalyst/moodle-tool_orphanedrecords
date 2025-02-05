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
 * Add bulk actions to the orphaned report.
 *
 * @package
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as reportSelectors from 'core_reportbuilder/local/selectors';
import * as tableEvents from 'core_table/local/dynamic/events';
import * as FormChangeChecker from 'core_form/changechecker';
import * as CustomEvents from 'core/custom_interaction_events';
import jQuery from 'jquery';

const Selectors = {
    bulkActionsForm: 'form#bulk-action-form',
    reportWrapper: '[data-region="report-list-wrapper"]',
    checkbox: 'input[type="checkbox"][data-togglegroup="report-select-all"][data-toggle="slave"]',
    masterCheckbox: 'input[type="checkbox"][data-togglegroup="report-select-all"][data-toggle="master"]',
    checkedRows: '[data-togglegroup="report-select-all"][data-toggle="slave"]:checked',
};

/**
 * Initialise module
 */
export const init = () => {

    const bulkForm = document.querySelector(Selectors.bulkActionsForm);
    const report = bulkForm?.closest(Selectors.reportWrapper)?.querySelector(reportSelectors.regions.report);
    if (!bulkForm || !report) {
        return;
    }
    const actionSelect = bulkForm.querySelector('select');
    CustomEvents.define(actionSelect, [CustomEvents.events.accessibleChange]);

    jQuery(actionSelect).on(CustomEvents.events.accessibleChange, event => {
        if (event.target.value && `${event.target.value}` !== "0") {
            const e = new Event('submit', {cancelable: true});
            bulkForm.dispatchEvent(e);
            if (!e.defaultPrevented) {
                FormChangeChecker.markFormSubmitted(bulkForm);
                bulkForm.submit();
            }
        }
    });

    // Every time the checkboxes in the report are changed, update the list of ids in the form values
    // and enable/disable the action select.
    const updateIds = () => {
        const selectedRows = [...report.querySelectorAll(Selectors.checkedRows)];
        const selectedIds = selectedRows.map(check => parseInt(check.value));
        bulkForm.querySelector('[name="recordids"]').value = selectedIds.join(',');

        // Disable the action selector if nothing selected, and reset the current selection.
        actionSelect.disabled = selectedRows.length === 0;
        if (actionSelect.disabled) {
            actionSelect.value = "0";
        }

        // Add the idsto the form data attributes so they can be available from the
        // other JS modules that listen to the form submit event.
        bulkForm.data = {ids: selectedIds};
    };

    updateIds();

    document.addEventListener('change', event => {
        // When checkboxes are checked next to individual rows or the master toggle (Select all/none).
        if ((event.target.matches(Selectors.checkbox) || event.target.matches(Selectors.masterCheckbox))
                && report.contains(event.target)) {
            updateIds();
        }
    });

    document.addEventListener(tableEvents.tableContentRefreshed, event => {
        // When the report contents is updated (i.e. page is changed, filters applied, etc).
        if (report.contains(event.target)) {
            updateIds();
        }
    });
};
