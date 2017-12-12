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
 *
 * @module     local_remote_backup_provider/fail_meneger_helper
 * @package    local_course_pack
 * @copyright  2017 Sysbind
 */
define(['jquery', 'core/ajax', 'core/str'],function($, ajax, str) {
    
    return{
        init: function() {
            $( '.resand' ).each(function () {
                $(this).click(function () {
                    console.log(this.id) ;
                    var promises = ajax.call([
                        { methodname: 'local_remote_backup_provider_retry_send_notification', args: { id: this.id } },
                    ]); 
                   promises[0].done(function(response) {
                       $( this ).closest('td').remove();
                       var successmesege = str.get_string('successmesege', 'local_remote_backup_provider');
                       $.when(successmesege).done(function(localizedEditString) {
                           alert(localizedEditString);
                      });
                   }).fail(function(ex) {
                       var failmesege = str.get_string('failmesege', 'local_remote_backup_provider');
                       $.when(failmesege).done(function(localizedEditString) {
                           alert(localizedEditString);
                      });
                   });
                })
            })
        }
    }
    
    });
    