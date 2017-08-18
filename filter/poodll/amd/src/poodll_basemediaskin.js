/* jshint ignore:start */
define(['jquery','core/log','filter_poodll/utils_amd'], function($, log, utils) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Base Skin: initialising');

    return {
    
        instanceprops: null,
        pmr: null,
        uploaded: false,

        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        init: function(ip, pmr){
            this.instanceprops=ip;
            this.pmr=pmr;
        },


        fetch_instanceprops : function(){
            return this.instanceprops;
        },


        onUploadSuccess: function(controlbarid){
             $('#' + controlbarid + ' > .poodll_save-recording').hide();
            // $('#' + controlbarid  + '_messages').hide();
             $('#' + controlbarid + ' > .poodll_savedsuccessfully').show();
        },
        
        onUploadFailure: function(controlbarid){
            return;
        },		

        fetch_status_bar: function(skin){
            var status = '<div class="poodll_status_' + skin + '" width="320" height="50">00:00:00</div>';
            return status;
        },
        
        fetch_preview_audio: function(skin){
            var preview = '<audio class="poodll_preview_' + skin + ' hide" controls></audio>';
            return preview;
        },
        fetch_preview_video: function(skin){
            var preview ='<video class="poodll_preview_' + skin + '" width="320" height="240"></video>';
            return preview;
        },
        
        onMediaError: function(e) {
                console.error('media error', e);
        },

        onMediaSuccess_video: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.stopbutton.attr('disabled',false);
            ip.controlbar.pausebutton.attr('disabled',false);
            ip.controlbar.savebutton.attr('disabled',false);
        },

        onMediaSuccess_audio: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.preview.attr('src',null);
            ip.controlbar.stopbutton.attr('disabled',false);
            ip.controlbar.pausebutton.attr('disabled',false);
            ip.controlbar.savebutton.attr('disabled',false);
        },

        handle_timer_update: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.status.html(ip.timer.fetch_display_time());
            if(ip.timer.seconds==0 && ip.timer.initseconds >0){
                 ip.controlbar.stopbutton.click();
            }
        },

       update_status: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.status.html(ip.timer.fetch_display_time());
        },

        //set visuals for different states (ie recording or playing)
        set_visual_mode: function(mode, controlbarid){
            var self = this;
            var ip = this.fetch_instanceprops(controlbarid);

           switch(mode){

               case 'recordmode':
                    ip.controlbar.preview.addClass('poodll_recording');
                    ip.controlbar.status.addClass('poodll_recording');
                    if(ip.config.mediatype=='audio'){
                        ip.controlbar.preview.addClass('hide');
                    }
                    ip.controlbar.status.removeClass('hide');
                    break;

               case 'previewmode':
                    ip.controlbar.preview.removeClass('poodll_recording');
                    ip.controlbar.status.removeClass('poodll_recording');
                    if(ip.config.mediatype=='audio'){
                        ip.controlbar.preview.removeClass('hide');
                    }
                    ip.controlbar.status.addClass('hide');
                    break;

               case 'pausedmode':
                    ip.controlbar.preview.removeClass('poodll_recording');
                    ip.controlbar.status.removeClass('poodll_recording');
                    break;
           }

       },

        //insert the control bar and return it to be reused
        insert_controlbar_video: function(element, controlbarid, preview) {
              return this.insert_controlbar_audio(element,controlbarid,preview);
        },
        //insert the control bar and return it to be reused
        insert_controlbar_audio: function(element,controlbarid, preview){
                var ip = this.fetch_instanceprops(controlbarid);
                var skin_style = ip.config.media_skin_style;

                var controls ='<div class="poodll_mediarecorderholder_standard ' + skin_style + '" id="holder_' + controlbarid + '">' ;
                controls +='<div class="poodll_mediarecorderbox_standard ' + skin_style + '" id="' + controlbarid + '">' ;
                var status = this.fetch_status_bar('standard');
                controls += status,
                controls += preview,
                controls +=  '<button type="button" class="poodll_mediarecorder_button_standard poodll_start-recording_standard">' + M.util.get_string('recui_record', 'filter_poodll') + '</button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_standard poodll_stop-recording_standard pmr_disabled" disabled>' + M.util.get_string('recui_stop', 'filter_poodll') + '</button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_standard poodll_pause-recording_standard pmr_disabled" disabled>' + M.util.get_string('recui_pause', 'filter_poodll') + '</button>';
                controls += ' <button type="button" class="poodll_mediarecorder_button_standard poodll_resume-recording_standard pmr_disabled hide" disabled>' + M.util.get_string('recui_continue', 'filter_poodll') + '</button>';
                controls += ' <button type="button" class="poodll_mediarecorder_button_standard poodll_play-recording_standard pmr_disabled" disabled>' + M.util.get_string('recui_play', 'filter_poodll') + '</button>';
                controls += '<button type="button" class="poodll_save-recording_standard pmr_disabled" disabled>' + M.util.get_string('recui_save', 'filter_poodll') + '</button>';
                controls += '</div></div>';
                $(element).prepend(controls);
                var controlbar ={
                    status: $('#' + controlbarid + ' > .poodll_status_standard'),
                    preview: $('#' + controlbarid + ' > .poodll_preview_standard'),
                    startbutton: $('#' + controlbarid + ' > .poodll_start-recording_standard'),
                    stopbutton: $('#' + controlbarid + ' > .poodll_stop-recording_standard'),
                    pausebutton: $('#' + controlbarid + ' > .poodll_pause-recording_standard'),
                    resumebutton: $('#' + controlbarid + ' > .poodll_resume-recording_standard'),
                    playbutton: $('#' + controlbarid + ' > .poodll_play-recording_standard'),
                    savebutton: $('#' + controlbarid + ' > .poodll_save-recording_standard')    
                };
                return controlbar;
        }, //end of fetch_control_bar_standard


        register_controlbar_events_video: function(onMediaSuccess, mediaConstraints, controlbarid) {
            return this.register_controlbar_events_audio(onMediaSuccess, mediaConstraints, controlbarid);
        },

        register_controlbar_events_audio: function(onMediaSuccess, mediaConstraints, controlbarid){
            var self = this;
            var pmr=this.pmr;
            var ip = this.fetch_instanceprops(controlbarid);

            ip.controlbar.startbutton.click(function() {
                pmr.do_start_audio(ip, mediaConstraints, onMediaSuccess);

                //clear messages
                $('#' + ip.config.widgetid  + '_messages').text('');
                self.disable_button(this);
                self.disable_button(ip.controlbar.playbutton);
                ip.controlbar.resumebutton.hide();
                self.enable_button(ip.controlbar.stopbutton);
                self.disable_button(ip.controlbar.savebutton);
                ip.controlbar.pausebutton.show();
                self.enable_button(ip.controlbar.pausebutton);
                self.set_visual_mode('recordmode',controlbarid);
                
                //timer and status bar
                ip.timer.reset();
                ip.timer.start();
                self.update_status(controlbarid);
            });
            
            ip.controlbar.stopbutton.click(function() {

                pmr.do_stop_audio(ip);

                self.disable_button(this);
                 var preview = ip.controlbar.preview;
                if(preview && preview.get(0)){
                    preview.get(0).pause();
                }
                
               //turn border black etc
               self.set_visual_mode('previewmode',controlbarid);
               //timer and status bar
               ip.timer.stop()
               self.update_status(controlbarid);
                
               self.enable_button(ip.controlbar.playbutton);
               self.disable_button(ip.controlbar.pausebutton);
              if(!self.uploaded){
                self.enable_button(ip.controlbar.startbutton);
                self.enable_button(ip.controlbar.savebutton);
              } 
               ip.controlbar.resumebutton.hide();
               ip.controlbar.pausebutton.show();
            });
          
            ip.controlbar.pausebutton.click(function() {
                this.disabled = true;
                $(this).hide();
                ip.controlbar.resumebutton.show();
                pmr.do_pause_audio(ip);
                self.enable_button(ip.controlbar.resumebutton);
                 self.enable_button(ip.controlbar.savebutton);
                self.set_visual_mode('pausedmode',controlbarid);
                
                //timer and status bar
                ip.timer.pause();
                self.update_status(controlbarid);
            });
            
            ip.controlbar.resumebutton.click(function() {
                self.disable_button(ip.controlbar.resumebutton);
                 self.disable_button(ip.controlbar.savebutton);
                $(this).hide();
                ip.controlbar.pausebutton.show();
                pmr.do_resume_audio(ip);
                self.enable_button(ip.controlbar.pausebutton);
                self.set_visual_mode('recordmode',controlbarid);
                
                //timer and status bar
                ip.timer.resume();
                self.update_status(controlbarid);
            });
            
            ip.controlbar.playbutton.click(function() {
                self.disable_button(this);
                var preview = ip.controlbar.preview.get(0);
                pmr.do_play_audio(ip,preview);

                self.enable_button(ip.controlbar.stopbutton);
                self.disable_button(ip.controlbar.startbutton);
                self.disable_button(ip.controlbar.savebutton);
            });
            
           ip.controlbar.savebutton.click(function() {
              self.disable_button(this);
              if(ip.blobs && ip.blobs.length > 0){
                  pmr.do_save_audio(ip);
                  self.uploaded = true;
                  self.disable_button(ip.controlbar.startbutton);
                }else{
                    ip.uploader.Output(M.util.get_string('recui_nothingtosaveerror','filter_poodll'));
                }//end of if self.blobs		
                //probably not necessary  ... but getting odd ajax errors occasionally
                return false;
            });//end of save recording
            
            window.onbeforeunload = function() {
                self.enable_button(ip.controlbar.startbutton);
                var preview = ip.controlbar.preview;
                if(preview && preview.get(0)){
                    preview.get(0).pause();
                }
            };
        }, //end of register_control_bar_events_standard

        enable_button: function(button){
            $(button).attr('disabled',false);
            $(button).removeClass('pmr_disabled');
        },
        disable_button: function(button){
            $(button).attr('disabled',true);
            $(button).addClass('pmr_disabled');
        },

    };//end of returned object
});//total end
