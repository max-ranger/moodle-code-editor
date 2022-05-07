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
 * Monaco admin settings.
 *
 * @package    editor_monaco
 * @copyright  2022 Maximilian Ranger
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('editorsettings', new admin_category('editormonaco', $editor->displayname, $editor->is_enabled() === false));

$settings = new admin_settingpage('editorsettingsmonaco', new lang_string('settings', 'editor_monaco'));
if ($ADMIN->fulltree) {
    require_once(__DIR__.'/adminlib.php');

    $defaultlanguagesjson = '{ "cpp": "C++", "csharp": "C#", "css": "CSS", "dockerfile": "Dockerfile",
                               "html": "HTML", "java": "Java", "javascript": "JavaScript", "json": "JSON",
                               "kotlin": "Kotlin", "markdown": "Markdown", "mysql": "MySQL", "pascal": "Pascal",
                               "php": "PHP", "plaintext": "PlainText", "powershell": "Powershell", "python": "Python",
                               "razor": "Razor","scala": "Scala", "sql": "SQL", "typescript": "TypeScript",
                               "xml": "XML", "yaml": "YAML" }';

    $config = get_config('editor_monaco');
    $languages = json_decode($defaultlanguagesjson, true);
    if (isset($config->supportedlanguages)) {
        $languages = json_decode($config->supportedlanguages, true);
    }
    $settings->add(new admin_setting_heading('monacogeneralheader', new lang_string('settings'), ''));
    $settings->add(new editor_monaco_json_setting_textarea('editor_monaco/supportedlanguages',
                                                           new lang_string('supportedlanguages', 'editor_monaco'),
                                                           new lang_string('supportedlanguages_desc', 'editor_monaco'),
                                                           $defaultlanguagesjson, PARAM_RAW, 100, 8));
    $settings->add(new admin_setting_configselect('editor_monaco/defaultlanguage',
                                                  new lang_string('defaultlanguage', 'editor_monaco'),
                                                  null, 'csharp', $languages));
    $settings->add(new editor_monaco_json_setting_textarea('editor_monaco/customconfig',
                                                           new lang_string('customconfig', 'editor_monaco'),
                                                           new lang_string('customconfig_desc', 'editor_monaco'),
                                                           '{"theme": "vs", "automaticLayout": "true", "wordWrap": "on",
                                                           "autoIndent": "true", "formatOnPaste": "true", "formatOnType": "true",
                                                           "minimap": {"enabled": "false"}, "scrollbar": {"vertical": "auto"}}',
                                                           PARAM_RAW, 100, 8));
}
$ADMIN->add('editormonaco', $settings);
unset($settings);
// Monaco does not have standard settings page.
$settings = null;
