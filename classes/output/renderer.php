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

namespace tool_courserating\output;

use plugin_renderer_base;
use tool_courserating\external\summary_exporter;
use tool_courserating\external\ratings_list_exporter;
use tool_courserating\helper;
use tool_courserating\permission;

/**
 * Renderer
 *
 * @package     tool_courserating
 * @copyright   2022 Marina Glancy <marina.glancy@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Reads contents of a custom field and displays it
     *
     * @param int $courseid
     * @return string
     */
    public function cfield(int $courseid): string {
        $content = '';
        $data = helper::get_course_rating_data_in_cfield($courseid);
        if ($data) {
            $output = $this->page->get_renderer('core_customfield');
            $fd = new \core_customfield\output\field_data($data);
            $content .= $output->render($fd);
        }
        return $content;
    }

    /**
     * Content of a course rating summary popup
     *
     * @param int $courseid
     * @return string
     */
    public function course_ratings_popup(int $courseid): string {
        $data1 = (new summary_exporter($courseid))->export($this);
        $data2 = (new ratings_list_exporter(['courseid' => $courseid]))->export($this);
        $data = (array)$data1 + (array)$data2;
        return $this->render_from_template('tool_courserating/course_ratings_popup', $data);
    }

    /**
     * Course review widget to be added to the course page
     *
     * @param int $courseid
     * @return string
     */
    public function course_rating_block(int $courseid): string {
        if (!permission::can_view_ratings($courseid)) {
            return '';
        }
        $data = [
            'ratingdisplay' => $this->cfield($courseid),
            'courseid' => $courseid,
            'rate' => permission::can_add_rating($courseid)
        ];
        return $this->render_from_template('tool_courserating/course_rating_block', $data);
    }
}
