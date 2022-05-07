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
 * Monaco admin setting stuff.
 *
 * @package   editor_monaco
 * @copyright 2022 Maximilian Ranger
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class editor_monaco_json_setting_textarea extends admin_setting_configtextarea {
    /**
     * Returns an XHTML string for the editor and adds a checkmark or a cross if sucsessfully decoded in array
     *
     * @param string $data
     * @param string $query
     * @return string XHTML string for the editor
     */
    public function output_html($data, $query='') {
        $result = parent::output_html($data, $query);

        $data = trim($data);
        if ($data) {
            $decoded = json_decode($data, true); // Decodes json string and return associative array.
            if (is_array($decoded)) {
                $valid = '<span class="pathok">&#x2714;</span>';        // Unicode Character = &#x2714: HEAVY CHECKMARK.
            } else {
                $valid = '<span class="patherror">&#x2718;</span>';     // Unicode Character = &#x2715: HEAVY CROSS.
            }
            $result = str_replace('</textarea>', '</textarea>'.$valid, $result);
        }

        return $result;
    }
}
