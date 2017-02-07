 /**
  * @module format_lm/addtask
  */
define([ 'jquery'], function(){
 
    /**
     * @constructor
     * @alias module:course_format_lm/addtask
     */
    var AddClock = function() {
        /** @access private */
      //  var privateThoughts = 'I like the colour blue';
 
        /** @access public */
        //this.publicThoughts = 'I like the colour orange';
 
    };

    AddClock.prototype.init	= function(servertime, hourtostart, minuttostart, hourstocountdown, minutestocountdown){
    
    // All times in secunds.	
    function setduration (){
    	var counter = 0;
    	time = new Date();
	    var starttime = timetoseconds(hourtostart,minuttostart);
	    var inteval = timetoseconds(hourstocountdown,minutestocountdown);
	    var localservertime = timetoseconds(parseInt(servertime.substring(0,2)),parseInt(servertime.substring(3,5)))+(time.getUTCSeconds());

    	
    	for (var i = starttime; i<86400; i=i+inteval){
    		if (localservertime < i){
    			break;
    		}
    		counter++;
    	}
    	
    	return ((starttime + counter * inteval) - localservertime) ;
    	
    }	
    
    function timetoseconds (h,m){
    	return parseInt (((h * (60 * 60)) + (m * 60)));
    }
    
	function startTimer(duration, display) {
    	    var timer = duration, hours,  minutes, seconds;
    	    setInterval(function () {
    	    	hours =  Math.floor(timer / 3600);
    	        minutes = Math.floor((timer - (hours*3600)) / 60);
    	        seconds = Math.floor(timer % 60);
    	        
    	        hours = hours < 10 ? "0" + hours : hours;
    	        minutes = minutes < 10 ? "0" + minutes : minutes;
    	        seconds = seconds < 10 ? "0" + seconds : seconds;
    	        
    	        display.text(hours + ":" + minutes + ":" + seconds);
    	        display.innerHTML = (hours + ":" + minutes + ":" + seconds);

    	        if (--timer < 0) {
    	        	duration = setduration(); 
    	            timer = duration;
    	        }
    	    }, 1000);
    	}

    	jQuery(function ($) {
    	    var duration = setduration();   	       	    
    	    display = $('#countdown');
    	    startTimer(duration, display);
    	});
    }
    return new AddClock();
    }); 	
    	