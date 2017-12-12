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

namespace local_remote_backup_provider;

defined('MOODLE_INTERNAL') || die();

/**
 * represent one fail notification.
 *
 * @package local_remote_backup_provider
 * @copyright 2017 SysBind LTD
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fail {

    const TABLE = 'remote_backup_provider_fails';

    protected $id;
    protected $type;
    protected $url;
    protected $local_params;
    protected $options;
    protected $timecreate;
    protected $last_time_try;

    /**
     * __construct. you cane send id of DB record OR class properties.
     * @param int $id - id in DB table.
     * @param string $url - full remote url.
     * @param array $local_params - parameters of the notification.
     * @param array $options - curl options.
     * @param string $type - type of notification.
     */
    public function __construct(int $id = null,string $url = null, array $local_params = null, array $options = null,string $type = null) {
        if ($id) {
            global $DB;
            $dbobj = $DB->get_record(self::TABLE, ['id' => $id]);
            if (!$dbobj) {
                return false;
            }
            $this->id             = $dbobj->id;
            $this->type             = $dbobj->type;
            $this->url             = $dbobj->url;
            $this->local_params  = \unserialize($dbobj->local_params);
            $this->options         = \unserialize($dbobj->options);
            $this->timecreate     = $dbobj->timecreate;
            $this->last_time_try = $dbobj->last_time_try;
            return $this;
        } else {
            $this->url             = $url;
            $this->local_params  = $local_params;
            $this->options         = $options;
            $this->type             = $type;
            return $this;
        }
    }

    /**
     * @return the $id
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * @return the $url
     */
    public function get_url() {
        return $this->url;
    }

    /**
     * @return the $params
     */
    public function get_local_params() {
        return $this->local_params;
    }

    /**
     * @return the $options
     */
    public function get_options() {
        return $this->options;
    }

    /**
     * @return the $timecreate
     */
    public function get_timecreate() {
        return $this->timecreate;
    }

    /**
     * @return the $last_time_try
     */
    public function get_last_time_try() {
        return $this->last_time_try;
    }

    /**
     * @return the type of notification
     */
    public function type() {
        return $this->last_time_try;
    }

    /**
     * store new fail to data base or update existing one.
     * @return boolean true | false
     */
    public function save () {
        global $DB;
        if ($this->id) {
            $this->last_time_try = \time();
            $this->local_params  = \serialize($this->local_params);
            $this->options       = \serialize($this->options);
            return $DB->update_record(self::TABLE, $this->to_std());
        } else {
            $this->timecreate = \time();
            $this->last_time_try = \time();
            $this->local_params  = \serialize($this->local_params);
            $this->options       = \serialize($this->options);
            $id = $DB->insert_record(self::TABLE, $this->to_std());
            if ($id) {
                $this->id = $id;
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * delete from database.
     *
     * @return boolean true
     */
    public function delete() {
        global $DB;
        return $DB->delete_records(self::TABLE, ['id' => $this->id]);
    }

    /**
     * send the notification.
     *
     * @return boolean true |false
     */
    public function send() {
        $curl = new \curl();
        $resp = \json_decode($curl->post($this->url, $this->local_params, $this->options), true);
        if (isset($resp['result']) && $resp['result'] == true) {
            $this->delete();
            return true;
        } else {
            \error_log(\mtrace('faild to update' . $this->url));
            \error_log(\print_r($resp,true));
            $this->save();
            return false;
        }
    }
    private function to_std() {
        $obj = new \stdClass();
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            $obj->{$key} = $value;
        }
        return $obj;
    }
}
