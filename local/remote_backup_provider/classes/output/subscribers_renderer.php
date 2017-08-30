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
class subscribers_renderer extends \plugin_renderer_base {

    public function subs_list() {
        global $DB;
        $subs = $DB->get_records('remote_backup_provider_subsc');

        foreach ($subs as $sub) {
            $sub->remove_remote = new \moodle_url('/local/remote_backup_provider/subscribermanager.php', array(
                'unsubscribe' => $sub->id
            ));
        }

        $data = array(
            'subscribers' => array_values($subs)
        );
        return $this->render_from_template('local_remote_backup_provider/subslist', $data);
    }

    public function unsubscribe($id, $confirm) {
        global $DB;
        $data = array();
        if (isset($confirm)) {
            publisher::unsubscribe($id);
            redirect(new \moodle_url('/local/remote_backup_provider/subscribermanager.php'));
        }
        foreach ($DB->get_record('remote_backup_provider_subsc', array(
            'id' => $id
        )) as $reckey => $recval) {
            $data[$reckey] = $recval;
        }
        $data['nourl'] = new \moodle_url('/local/remote_backup_provider/subscribermanager.php');
        $data['nourl'] = $data['nourl']->out();
        $data['yesurl'] = new \moodle_url('/local/remote_backup_provider/subscribermanager.php', array(
            'unsubscribe' => $id,
            'unsubscribeconf' => 1
        ));
        $data['yesurl'] = $data['yesurl']->out(false);
        return $this->render_from_template('local_remote_backup_provider/unsubscribe', $data);
    }
}

