/* eslint-disable object-curly-spacing */
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
 * monaco helper javascript functions.
 *
 * @package    editor_monaco
 * @copyright  2022 Maximilian Ranger
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.editor_monaco = M.editor_monaco || {
    init_editor: function(Y, editorid, options) {
        var textarea = document.getElementById(editorid);

        if (!textarea) {
            // No text area found.
            Y.log('Text area not found - unable to setup editor for ' + this.get('elementid'), 'error', LOGNAME);
            return;
        }
        var extraclasses = textarea.getAttribute('class');

        var edItem = createElement("div", { id: editorid + '_parent', class: extraclasses, contenteditable: "true" });
        edItem.id = editorid + '_parent';
        edItem.style = 'width: 100%; flex-grow: 1; min-height: ' + (1.5 * (textarea.getAttribute('rows'))) + 'em;';
        // Height $lines * 1.5 because that is a typical line-height on web pages.
        // That seems to give results that look OK.
        textarea.parentElement.prepend(edItem);
        // textarea.after(edItem);
        textarea.style = "display: none;";
        textarea.setAttribute('hidden', "true");

        M.editor_monaco.editor_options[editorid] = options;
        // Save the file picker options for later.
        M.editor_monaco.filepicker_options[editorid] = options.filepickeroptions;

        require.config({ paths: { vs: M.cfg.wwwroot + '/lib/editor/monaco/monaco-editor/min/vs' } });
        require.config({ 'vs/nls': { availableLanguages: { '*': options.lang } } });

        var editor = null;
        var stdOptions = { value: textarea.value };
        const o = {...stdOptions, ...options };
        require(['vs/editor/editor.main'], function() {
            editor = monaco.editor.create(edItem, o);
            edItem.firstElementChild.setAttribute('role', 'application');
            edItem.append(textarea);
            // Copy the current value back to the textarea when focus leaves us.
            edItem.addEventListener('blur', function() {
                editor.getAction('editor.action.formatDocument').run();
                //editor.trigger('', 'editor.action.formatDocument');
                textarea.value = editor.getValue();
            }, true);
        });

        // Create element with specified properties (attributes) 
        function createElement(type, props) {
            var $e = document.createElement(type);
            for (var prop in props) {
                $e.setAttribute(prop, props[prop]);
            }
            return $e;
        }
    },

    changeLanguage: function(lang) {
        M.editor_monaco.editor.setModelLanguage(M.editor_monaco.editor.getModel(), lang);
        console.log(`model language was changed to ${M.editor_monaco.editor.getModel().getLanguageIdentifier().language}`);
    },

    show_filepicker: function(elementid, type, callback) {
        Y.use('core_filepicker', function(Y) {
            var options = M.editor_monaco.filepickeroptions[elementid][type];
            options.formcallback = callback;
            options.editor_target = Y.one(elementid);
            M.core_filepicker.show(Y, options);
        });
    }
};
M.editor_monaco.editor_options = M.editor_monaco.options || {};
M.editor_monaco.filepicker_options = M.editor_monaco.filepickeroptions || {};

M.editor_monaco.init_filepicker = function(Y, editorid, options) {
    M.editor_monaco.filepicker_options[editorid] = options;
};