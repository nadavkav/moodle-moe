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
 * Implementaton of the moeworksheetsaccess_ipaddress plugin.
 *
 * @package    moeworksheetsaccess
 * @subpackage ipaddress
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/moeworksheets/accessrule/accessrulebase.php');


/**
 * A rule implementing the ipaddress check against the ->subnet setting.
 *
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moeworksheetsaccess_ipaddress extends moeworksheets_access_rule_base {

    public static function make(moeworksheets $moeworksheetsobj, $timenow, $canignoretimelimits) {
        if (empty($moeworksheetsobj->get_moeworksheets()->subnet)) {
            return null;
        }

        return new self($moeworksheetsobj, $timenow);
    }

    public function prevent_access() {
        if (address_in_subnet(getremoteaddr(), $this->moeworksheets->subnet)) {
            return false;
        } else {
            return get_string('subnetwrong', 'moeworksheetsaccess_ipaddress');
        }
    }
}
