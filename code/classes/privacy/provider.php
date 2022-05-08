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
 * Provider class for the code question type.
 *
 * @package    qtype_code
 * @copyright  2022 Maximilian Ranger
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_code\privacy;

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\user_preference_provider;
use \core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for qtype_code implementing user_preference_provider.
 *
 * @copyright  2022 Maximilian Ranger
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This component has data.
// We need to return default options that have been set a user preferences.
class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\user_preference_provider {
    /**
     * Returns meta data about this system.
     *
     * @param   collection     $collection The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_user_preference('qtype_code_defaultmark', 'privacy:preference:defaultmark');
        $collection->add_user_preference('qtype_code_responseformat', 'privacy:preference:responseformat');
        $collection->add_user_preference('qtype_code_responserequired', 'privacy:preference:responserequired');
        $collection->add_user_preference('qtype_code_responsefieldlines', 'privacy:preference:responsefieldlines');
        $collection->add_user_preference('qtype_code_attachments', 'privacy:preference:attachments');
        $collection->add_user_preference('qtype_code_attachmentsrequired', 'privacy:preference:attachmentsrequired');
        $collection->add_user_preference('qtype_code_maxbytes', 'privacy:preference:maxbytes');
        return $collection;
    }

    /**
     * Export all user preferences for the plugin.
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $preference = get_user_preferences('qtype_code_defaultmark', null, $userid);
        if (null !== $preference) {
            $desc = get_string('privacy:preference:defaultmark', 'qtype_code');
            writer::export_user_preference('qtype_code', 'defaultmark', $preference, $desc);
        }

        $preference = get_user_preferences('qtype_code_responseformat', null, $userid);
        if (null !== $preference) {
            $desc = get_string('privacy:preference:responseformat', 'qtype_code');
            writer::export_user_preference('qtype_code', 'responseformat', $preference, $desc);
        }

        $preference = get_user_preferences('qtype_code_responserequired', null, $userid);
        if (null !== $preference) {
            if ($preference) {
                $stringvalue = get_string('responseisrequired', 'qtype_code');
            } else {
                $stringvalue = get_string('responsenotrequired', 'qtype_code');
            }
            $desc = get_string('privacy:preference:responserequired', 'qtype_code');
            writer::export_user_preference('qtype_code', 'responserequired', $stringvalue, $desc);
        }

        $preference = get_user_preferences('qtype_code_responsefieldlines', null, $userid);
        if (null !== $preference) {
            $desc = get_string('privacy:preference:responsefieldlines', 'qtype_code');
            writer::export_user_preference('qtype_code', 'responsefieldlines',
                    get_string('nlines', 'qtype_code', $preference), $desc);
        }

        $preference = get_user_preferences('qtype_code_attachments', null, $userid);
        if (null !== $preference) {
            if ($preference == 0) {
                $stringvalue = get_string('no');
            } else if ($preference == -1) {
                    $stringvalue = get_string('unlimited');
            } else {
                $stringvalue = $preference;
            }
            $desc = get_string('privacy:preference:attachments', 'qtype_code');
            writer::export_user_preference('qtype_code', 'attachments', $stringvalue, $desc);
        }

        $preference = get_user_preferences('qtype_code_attachmentsrequired', null, $userid);
        if (null !== $preference) {
            if ($preference == 0) {
                $stringvalue = get_string('attachmentsoptional', 'qtype_code');
            } else {
                $stringvalue = $preference;
            }
            $desc = get_string('privacy:preference:attachmentsrequired', 'qtype_code');
            writer::export_user_preference('qtype_code', 'attachmentsrequired', $stringvalue, $desc);
        }

        $preference = get_user_preferences('qtype_code_maxbytes', null, $userid);
        if (null !== $preference) {
            switch ($preference) {
                case 52428800:
                    $stringvalue = '50MB';
                    break;
                case 20971520:
                    $stringvalue = '20MB';
                    break;
                case 10485760:
                    $stringvalue = '10MB';
                    break;
                case 5242880:
                    $stringvalue = '5MB';
                    break;
                case 2097152:
                    $stringvalue = '2MB';
                    break;
                case 1048576:
                    $stringvalue = '1MB';
                    break;
                case 512000:
                    $stringvalue = '500KB';
                    break;
                case 102400:
                    $stringvalue = '100KB';
                    break;
                case 51200:
                    $stringvalue = '50KB';
                    break;
                case 10240:
                    $stringvalue = '10KB';
                    break;
                default:
                    $stringvalue = '50MB';
                    break;
            }
            $desc = get_string('privacy:preference:maxbytes', 'qtype_code');
            writer::export_user_preference('qtype_code', 'maxbytes', $stringvalue, $desc);
        }
    }
}
