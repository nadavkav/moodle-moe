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
 
    AddClock.prototype.init = function(hours, minutes, seconds) {
      	
    	function countdown( elementName, minutes, seconds )
    	{
    	    var element, endTime, hours, mins, msLeft, time ;
    		
    	    function twoDigits( n )
    	    {
    	        return (n <= 9 ? "0" + n : n);
    	    }


    	    function updateTimer()
    	    {
    	        msLeft = endTime - (+new Date());
    	        if ( msLeft < 1 ) {
    	        countdown( "countdown",120, 0 );
    			   
    	        } else {
    	            time = new Date( msLeft );
    	            hours = time.getUTCHours();
    	            mins = time.getUTCMinutes();
    	            element.innerHTML =  twoDigits(hours) + ':' + twoDigits( mins ) +':' + twoDigits( time.getUTCSeconds() );
    	            setTimeout( updateTimer, time.getUTCMilliseconds() + 500 );
    	        }
    	    }

    	    element = document.getElementById( elementName );
    	    endTime = (+new Date()) + 1000 * (60*minutes + seconds) + 500;
          	updateTimer();
    		}

    	var countminutes;
	  	var countseconds;
	  	if((seconds<1)&&(hours%2===0)&&(minutes===0))
	  		{
	  		countdown("countdown", 120, 0 );
	  		}
	  	else if(hours%2===0)
	  	{ 
	  		countminutes=60-minutes-1;
	  		countminutes=countminutes+60;
	  		countseconds=60-seconds;
	  		countdown("countdown", countminutes,countseconds );
	  	}	
	  	else
	    {
	  		countminutes=60-minutes-1;
	  		countseconds=60-seconds;
	  		countdown("countdown", countminutes,countseconds );
	    }
	 
    };
       
    return new AddClock();
});
