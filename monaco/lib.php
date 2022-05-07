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
 * Monaco text editor integration.
 *
 * @package    editor
 * @subpackage monaco
 * @copyright  2022, Maximlian Ranger
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class monaco_texteditor extends texteditor {
    /**
     * @var string active version - this is the directory name where to find tinymce code
     */
    public $version = '"0.33.0"';

    /**
     * Is the current browser supported by this editor?
     * @return bool
     */
    public function supported_by_browser() {
        // We don't support any browsers which it doesn't support.
        return true;
    }

    /**
     * Returns array of supported text formats.
     * @return array
     */
    public function get_supported_formats() {
        // FORMAT_MOODLE is not supported here, sorry. FORMAT_PLAIN.
        return array(FORMAT_HTML => FORMAT_HTML);
    }

    /**
     * Returns text format preferred by this editor.
     * @return int
     */
    public function get_preferred_format() {
        return FORMAT_HTML;
    }

    /**
     * Does this editor support picking from repositories?
     * @return bool
     */
    public function supports_repositories() {
        return true;
    }

    /**
     * Sets up head code if necessary.
     */
    public function head_setup() {
    }

    /**
     * Use this editor for give element.
     *
     * @param string $elementid
     * @param array $options
     * @param null $fpoptions
     */
    public function use_editor($elementid, array $options=null, $fpoptions=null) {
        global $PAGE, $CFG;

        if ($CFG->debugdeveloper) {
            $PAGE->requires->js(new moodle_url('/lib/editor/monaco/monaco-editor/dev/vs/loader.js'));
            $PAGE->requires->js(new moodle_url('/lib/editor/monaco/monaco-editor/dev/vs/editor/editor.main.nls.js'));
            $PAGE->requires->js(new moodle_url('/lib/editor/monaco/monaco-editor/dev/vs/editor/editor.main.js'));
        } else {
            $PAGE->requires->js(new moodle_url('/lib/editor/monaco/monaco-editor/min/vs/loader.js'));
            $PAGE->requires->js(new moodle_url('/lib/editor/monaco/monaco-editor/min/vs/editor/editor.main.nls.js'));
            $PAGE->requires->js(new moodle_url('/lib/editor/monaco/monaco-editor/min/vs/editor/editor.main.js'));
        }
        $PAGE->requires->js_init_call('M.editor_monaco.init_editor',
                                      array($elementid, $this->get_init_params($elementid, $options)), true);
        if ($fpoptions) {
            $PAGE->requires->js_init_call('M.editor_monaco.init_filepicker', array($elementid, $fpoptions), true);
        }
    }

    protected function get_init_params($elementid, array $options=null, array $fpoptions = null, $plugins = null) {
        global $CFG, $PAGE, $OUTPUT;

        $directionality = get_string('thisdirection', 'langconfig');
        $strtime        = get_string('strftimetime');
        $strdate        = get_string('strftimedaydate');
        $lang           = current_language();
        $contentcss     = $PAGE->theme->editor_css_url()->out(false);
        $context        = empty($options['context']) ? context_system::instance() : $options['context'];
        $langrev = -1;
        if (!empty($CFG->cachejs)) {
            $langrev = get_string_manager()->get_revision();
        }

        // Get configuration values from the global monaco settings.
        $config = get_config('editor_monaco');

        $params = array(
            'elementid' => $elementid,
            'content_css' => $contentcss,
            'contextid' => $context->id,
            'lang' => $lang,
            'language' => $config->defaultlanguage,
            'directionality' => $directionality,
            'filepickeroptions' => array(),
            'langrev' => $langrev,
            'moodle_config' => $config
        );
        if (!empty($options['language'])) {
            $params['language'] = $options['language'];
        }
        if (!empty($options['readOnly'])) {
            $params['readOnly'] = $options['readOnly'];
        }

        if ($fpoptions) {
            $params['filepickeroptions'] = $fpoptions;
        }
        // Add monaco editors global custom config settings.
        if (!empty($config->customconfig)) {
            $config->customconfig = trim($config->customconfig);
            $decoded = json_decode($config->customconfig, true);
            if (is_array($decoded)) {
                foreach ($decoded as $k => $v) {
                    $params[$k] = $v;
                }
            }
        }

        return $params;
    }

    /**
     * @return moodle_url url pointing to the root of monaco javascript code.
     */
    public function get_monaco_base_url() {
        return new moodle_url("/lib/editor/monaco/monaco-editor/");
    }

}
