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
 * Editor input element.
 *
 * Contains class to create code_editor form element.
 *
 * @package   core_form
 * @copyright 2022 Max Ranger
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/lib/form/editor.php');

/**
 * Editor element
 *
 * It creates a Monaco codeeditor form element
 *
 * @package   core_form
 * @category  form
 * @copyright 2022 Max Ranger
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @todo      MDL-29421 element Freezing
 * @todo      MDL-29426 ajax format conversion
 */
class MoodleQuickForm_codeeditor extends MoodleQuickForm_editor implements templatable {
    use templatable_form_element {
        export_for_template as export_for_template_base;
    }

    /**
     * Constructor
     *
     * @param string $elementname (optional) name of the editor
     * @param string $elementlabel (optional) editor label
     * @param array $attributes (optional) Either a typical HTML attribute string
     *              or an associative array
     * @param array $options set of options to initalize filepicker
     */
    public function __construct($elementname=null, $elementlabel=null, $attributes=null, $options=null) {
        global $CFG, $PAGE;
        $this->_type = 'editor';
        parent::__construct($elementname, $elementlabel, $attributes, $options);
        $options = (array)$options;
        foreach ($options as $name => $value) {
            if (!array_key_exists($name, $this->_options)) {
                $this->_options[$name] = $value;
            }
        }
    }

    /**
     * Returns HTML for editor form element.
     *
     * @return string
     */
    function toHtml() {
        global $CFG, $PAGE, $OUTPUT;
        require_once($CFG->dirroot.'/repository/lib.php');

        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        }

        $ctx = $this->_options['context'];

        $id           = $this->_attributes['id'];
        $elname       = $this->_attributes['name'];

        $subdirs      = $this->_options['subdirs'];
        $maxbytes     = $this->_options['maxbytes'];
        $areamaxbytes = $this->_options['areamaxbytes'];
        $maxfiles     = $this->_options['maxfiles'];

        $text         = $this->_values['text'];
        $format       = $this->_values['format'];
        $draftitemid  = $this->_values['itemid'];

        // Security - never ever allow guest/not logged in user to upload anything.
        if (isguestuser() or !isloggedin()) {
            $maxfiles = 0;
        }

        $str = $this->_getTabs();
        $str .= '<div>';

        $editor = $this->get_monaco_editor();
        $strformats = format_text_menu();
        $formats = $editor->get_supported_formats();
        foreach ($formats as $fid) {
            $formats[$fid] = $strformats[$fid];
        }

        // Get filepicker info.
        //
        $fpoptions = array();
        if ($maxfiles != 0 ) {
            if (empty($draftitemid)) {
                // No existing area info provided - let's use fresh new draft area.
                require_once("$CFG->libdir/filelib.php");
                $this->setValue(array('itemid' => file_get_unused_draft_itemid()));
                $draftitemid = $this->_values['itemid'];
            }

            $args = new stdClass();
            // Need these three to filter repositories list.
            $args->accepted_types = array('web_image');
            $args->return_types = $this->_options['return_types'];
            $args->context = $ctx;
            $args->env = 'filepicker';

            // For advimage plugin.
            $imageoptions = initialise_filepicker($args);
            $imageoptions->context = $ctx;
            $imageoptions->client_id = uniqid();
            $imageoptions->maxbytes = $this->_options['maxbytes'];
            $imageoptions->areamaxbytes = $this->_options['areamaxbytes'];
            $imageoptions->env = 'editor';
            $imageoptions->itemid = $draftitemid;

            // For moodlemedia plugin.
            $args->accepted_types = array('video', 'audio');
            $mediaoptions = initialise_filepicker($args);
            $mediaoptions->context = $ctx;
            $mediaoptions->client_id = uniqid();
            $mediaoptions->maxbytes  = $this->_options['maxbytes'];
            $mediaoptions->areamaxbytes  = $this->_options['areamaxbytes'];
            $mediaoptions->env = 'editor';
            $mediaoptions->itemid = $draftitemid;

            // For advlink plugin.
            $args->accepted_types = '*';
            $linkoptions = initialise_filepicker($args);
            $linkoptions->context = $ctx;
            $linkoptions->client_id = uniqid();
            $linkoptions->maxbytes  = $this->_options['maxbytes'];
            $linkoptions->areamaxbytes  = $this->_options['areamaxbytes'];
            $linkoptions->env = 'editor';
            $linkoptions->itemid = $draftitemid;

            $args->accepted_types = array('.vtt');
            $subtitleoptions = initialise_filepicker($args);
            $subtitleoptions->context = $ctx;
            $subtitleoptions->client_id = uniqid();
            $subtitleoptions->maxbytes  = $this->_options['maxbytes'];
            $subtitleoptions->areamaxbytes  = $this->_options['areamaxbytes'];
            $subtitleoptions->env = 'editor';
            $subtitleoptions->itemid = $draftitemid;

            if (has_capability('moodle/h5p:deploy', $ctx)) {
                // Only set H5P Plugin settings if the user can deploy new H5P content.
                // H5P plugin.
                $args->accepted_types = array('.h5p');
                $h5poptions = initialise_filepicker($args);
                $h5poptions->context = $ctx;
                $h5poptions->client_id = uniqid();
                $h5poptions->maxbytes  = $this->_options['maxbytes'];
                $h5poptions->areamaxbytes  = $this->_options['areamaxbytes'];
                $h5poptions->env = 'editor';
                $h5poptions->itemid = $draftitemid;
                $fpoptions['h5p'] = $h5poptions;
            }

            $fpoptions['image'] = $imageoptions;
            $fpoptions['media'] = $mediaoptions;
            $fpoptions['link'] = $linkoptions;
            $fpoptions['subtitle'] = $subtitleoptions;
        }

        // Print text area - TODO: add on-the-fly switching, size configuration, etc.
        $editor->set_text($text);
        $editor->use_editor($id, $this->_options, $fpoptions);

        $rows = empty($this->_attributes['rows']) ? 15 : $this->_attributes['rows'];
        $cols = empty($this->_attributes['cols']) ? 80 : $this->_attributes['cols'];

        // Apply editor validation if required field.
        $context = [];
        $context['rows'] = $rows;
        $context['cols'] = $cols;
        $context['frozen'] = $this->_flagFrozen;
        foreach ($this->getAttributes() as $name => $value) {
            $context[$name] = $value;
        }
        $context['hasformats'] = count($formats) > 1;
        $context['formats'] = [];
        if (($format === '' || $format === null) && count($formats)) {
            $format = key($formats);
        }
        foreach ($formats as $formatvalue => $formattext) {
            $context['formats'][] = ['value' => $formatvalue, 'text' => $formattext, 'selected' => ($formatvalue == $format)];
        }
        $context['id'] = $id;
        $context['value'] = $text;
        $context['format'] = $format;

        if (!is_null($this->getAttribute('onblur')) && !is_null($this->getAttribute('onchange'))) {
            $context['changelistener'] = true;
        }

        $str .= $OUTPUT->render_from_template('core_form/editor_textarea', $context);

        // During moodle installation, user area doesn't exist.
        // So we need to disable filepicker here.
        if (!during_initial_install() && empty($CFG->adminsetuppending)) {
            // 0 means no files, -1 unlimited.
            if ($maxfiles != 0 ) {
                $str .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $elname.'[itemid]',
                        'value' => $draftitemid));

                // Used by non js editor only.
                $editorurl = new moodle_url("$CFG->wwwroot/repository/draftfiles_manager.php", array(
                    'action' => 'browse',
                    'env' => 'editor',
                    'itemid' => $draftitemid,
                    'subdirs' => $subdirs,
                    'maxbytes' => $maxbytes,
                    'areamaxbytes' => $areamaxbytes,
                    'maxfiles' => $maxfiles,
                    'ctx_id' => $ctx->id,
                    'course' => $PAGE->course->id,
                    'sesskey' => sesskey(),
                    ));
                $str .= '<noscript>';
                $str .= "<div><object type='text/html' data='$editorurl' height='160' width='600' style='border:1px solid #000'>";
                $str .= "</object></div>";
                $str .= '</noscript>';
            }
        }

        $str .= '</div>';

        return $str;
    }

    /**
     * Returns monaco editor if available and enabled
     *
     * @return texteditor object
     */
    protected function get_monaco_editor() {
        global $CFG;
        $libfile = "$CFG->libdir/editor/monaco/lib.php";

        if (!empty($CFG->adminsetuppending) || !file_exists($libfile)) {
            // Must not use other editors before install completed!
            return get_texteditor('textarea');
        }

        require_once($libfile);

        $classname = 'monaco_texteditor';
        if (!class_exists($classname)) {
            return get_texteditor('textarea');
        }

        $editor = new $classname();
        if (!$editor) {
            $editor = get_texteditor('textarea'); // Must exist and can edit anything.
        }

        return $editor;
    }

    public function export_for_template(renderer_base $output) {
        $context = parent::export_for_template($output);
        return $context;
    }
}
