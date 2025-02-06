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

namespace tool_orphanedrecords;

/**
 * Unit tests for tool_orphanedrecords\orphanedrecords class.
 *
 * @covers    \tool_orphanedrecords\orphanedrecords
 * @package   tool_orphanedrecords
 * @author    Simon Thornett <simon.thornett@catalyst-eu.net>
 * @copyright Catalyst IT, 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class orphanedrecords_test extends \advanced_testcase {

    public function test_save_new_orphaned_records() {
        global $DB;

        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Grouping 1.
        $course1 = $this->getDataGenerator()->create_course(['numsections' => 1]);
        $course2 = $this->getDataGenerator()->create_course(['numsections' => 1]);
        $scorm1 = $this->getDataGenerator()->create_module('scorm', ['course' => $course1]);
        $scorm2 = $this->getDataGenerator()->create_module('scorm', ['course' => $course1]);
        $this->getDataGenerator()->create_module('scorm', ['course' => $course2]);

        $scormmodule = $DB->get_record('modules', ['name' => 'scorm']);

        $this->getDataGenerator()->create_module('scorm', ['course' => $course1]);
        $dbman = $DB->get_manager();
        $schema = $dbman->get_install_xml_schema();
        $tables = $schema->getTables();

        // Get all of the xmldb tables.
        foreach ($tables as $key => $table) {
            $tables[$table->getName()] = $table;
            unset($tables[$key]);
        }

        // Add a course and module to validate no issues.
        $course = $this->getDataGenerator()->create_course(['numsections' => 1]);
        $this->getDataGenerator()->create_module('scorm', ['course' => $course]);

        // Check the course record. Will not have any issues.
        orphanedrecords::save_new_orphaned_records($tables['course']);
        $orphanedrecords = $DB->get_records(orphanedrecords::TABLE);
        $this->assertCount(0, $orphanedrecords);

        // Check the course_modules record. Will not have any issues.
        orphanedrecords::save_new_orphaned_records($tables['course_modules']);
        $orphanedrecords = $DB->get_records(orphanedrecords::TABLE);
        $this->assertCount(0, $orphanedrecords);

        // Check the scorm record. Will not have any issues.
        orphanedrecords::save_new_orphaned_records($tables['scorm']);
        $orphanedrecords = $DB->get_records(orphanedrecords::TABLE);
        $this->assertCount(0, $orphanedrecords);

        // Now start processing each potential failure.
        // Set the originalcourseid on the course record. This is a foreign key constraint violation.
        $course1->originalcourseid = 123;
        update_course($course1);
        orphanedrecords::save_new_orphaned_records($tables['course']);
        $orphanedrecords = $DB->get_records(orphanedrecords::TABLE);
        $this->assertCount(1, $orphanedrecords);

        // Delete the `course_module` record for scorm1.
        $DB->delete_records('course_modules', ['instance' => $scorm1->id, 'module' => $scormmodule->id]);
        orphanedrecords::save_new_orphaned_records($tables['scorm']);
        $orphanedrecords = $DB->get_records(orphanedrecords::TABLE);
        $this->assertCount(2, $orphanedrecords);

        // Delete the `scorm` record for scorm2.
        $DB->delete_records('scorm', ['id' => $scorm2->id]);
        orphanedrecords::save_new_orphaned_records($tables['course_modules']);
        $orphanedrecords = $DB->get_records(orphanedrecords::TABLE);
        $this->assertCount(3, $orphanedrecords);

        // Delete the `course` record course2 for scorm3.
        $DB->delete_records('course', ['id' => $course2->id]);
        orphanedrecords::save_new_orphaned_records($tables['course_modules']);
        $orphanedrecords = $DB->get_records(orphanedrecords::TABLE);
        $this->assertCount(4, $orphanedrecords);

        // The previously deleted `course` record course2 will also have 2 orphaned course_section's.
        orphanedrecords::save_new_orphaned_records($tables['course_sections']);
        $orphanedrecords = $DB->get_records(orphanedrecords::TABLE);
        $this->assertCount(6, $orphanedrecords);

        // Delete the `section` record course2 containing scorm3.
        $DB->delete_records('course_sections', ['course' => $course2->id]);
        orphanedrecords::save_new_orphaned_records($tables['course_modules']);
        $orphanedrecords = $DB->get_records(orphanedrecords::TABLE);
        $this->assertCount(7, $orphanedrecords);

        // The deleted course2, scorm2, and course_modules for scorm1 will result in 3 missing grade_items.
        orphanedrecords::save_new_orphaned_records($tables['grade_items']);
        $orphanedrecords = $DB->get_records(orphanedrecords::TABLE);
        $this->assertCount(10, $orphanedrecords);

    }
}
