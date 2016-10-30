 /**
  * @module format_lm/addtask
  */
define([ 'jquery', 'core/ajax'], function($,ajax){
 
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
    	
    //	require(['core/ajax'], function(ajax) {
    //	    var call = ajax.call([
    //	        {methodname: 'lm_get_tasklist' , args: { context: context, course:id[1] }}]);
   // 	    call[0].done(function (resp) {
    //		$('#addtask_container').html(resp);
    //	});
   // });
    	
    	
    	
    	function countdown( elementName, minutes, seconds )
    	{
    	    var element, endTime, hours, mins, msLeft, time ;

    		
    	    function twoDigits( n )
    	    {
    	        return (n <= 9 ? "0" + n : n);
    	    }


    	    function updateTimer()
    	    {
    	    	
    	        msLeft = endTime - (+new Date);
    	        
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

    	    var updateclockajax = ajaxcall('update_clock','aa');
            updateclockajax[0].done(function(resp){
        	   // location
            	alert("sharon");
           	 //alert(resp);
           	 
        		//.reload();
        	}).fail(function(){
        		alert("error");
        	});	
    		    
    	  while(true)
    		 {
          	endTime = (+new Date) + 1000 * (60*minutes + seconds) + 500;
          	
          
    	  	var now =new Date();
    		if((now.getSeconds()<1)&&(now.getMinutes()%2==0))

    	  	//if((now.getSeconds()<1)&&(now.getHours()%2== 0)&&(now.getMinutes()==0))
    		{
    			
    		updateTimer();
    		break;

    		}

    		}
    		}

    	alert(hours);
    	
	  	alert(minutes);
	  	alert(seconds);
	  	//alert(php_var);
    	countdown("countdown", 120, 0 );
    	
	
    };
   //	
   
    
    function ajaxcall(action,obj){
    	var data = {};
    	data.methodname = action;
    	alert(data.methodname);
    	//data.args = obj;
    	return ajax.call([data]);
        }
    return new AddClock();
});
