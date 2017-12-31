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

namespace local_remote_backup_provider\output;

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @package local_remote_backup_provider
 * @copyright 2017 Sysbind
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fails_meneger_renderer extends \plugin_renderer_base {

    public function showall() {
        global $DB;
        $context = new \stdClass();
        $allfails = array_values($DB->get_records('remote_backup_provider_fails'));
        foreach ($allfails as &$fail) {
            $url = explode('/', $fail->url);
            $fail->name = $url[2];
            $fail->timecreate = date('d-m-Y H:i:s', $fail->timecreate);
            $fail->last_time_try = date('d-m-Y H:i:s', $fail->last_time_try);
        }
        $context->allfails = $allfails;
        return $this->render_from_template('local_remote_backup_provider/fails_meneger', $context);
    }
}

