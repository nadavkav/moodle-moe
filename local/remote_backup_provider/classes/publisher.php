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
class publisher {

    /**
     * get id of subscriber by his name or url.
     *
     * @param $name - name of remote server.
     * @param $url - prefix of remote server.
     * @return int - the id of the subscriber or false if none found or -1 if both parameters ar null.
     */
    public static function  get_id ($name = null, $url = null){
        global $DB;
        $table  = 'remote_backup_provider_subsc';
        $return = 'id';

        if ($name == null && $url == null){
            return -1;
        } else if ($name != null){
            $sql = "select id from {remote_backup_provider_subsc} where " . $DB->sql_compare_text('name') . "=$name";
            return $DB->get_field_sql($sql);
        } else {
            $sql = "select id from {remote_backup_provider_subsc} where " . $DB->sql_compare_text('base_url') . '=' ." '" .$url ." '";
            return $DB->get_field_sql($sql);
        }
    }

    /**
    * Subscribe remote server.
    *
    * @param $name - name of remote server.
    * @param $url - prefix of remote server.
    *
    * @return bool|int true or new id or false if subscriber already exist
    */
    public static function  subscribe ($name, $url, $user, $token){
        global $DB;

        if ($id = self::get_id(null, $url)) {
            $DB->delete_records('remote_backup_provider_subsc', array( 'id' =>$id));
        }

        $data = array('subscriber_name' => $name,
                      'base_url'        => $url,
                      'remote_user'     => $user,
                      'remote_token'    => $token
        );
        return $DB->insert_record('remote_backup_provider_subsc', $data);
    }

    /**
     * unsubscribe remote server.
     *
     * @param $id - id of subscriber (can get the id from get_id function)
     * @return bool true or false
     */
    public static function  unsubscribe ($id){
        global $DB;
        return $DB->delete_records('remote_backup_provider_subsc', array('id' => $id));
    }

    /**
     * get all subscribers.
     *
     * @return array An array of Objects indexed by id column.
     */
    public static function  get_all_subscribers (){
        global $DB;
        return $DB->get_records('remote_backup_provider_subsc');
    }

}