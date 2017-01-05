/**
  * @module local_moereports/reports
  */
define(['local_moereports/handsontable', 'jquery', 'core/ajax'],function(Handsontable, $, ajax){
	
	var Report = function() {
	};

	Report.prototype.init = function(report) {
		var deleted = new Array();
		
		emptyValidator = function(value, callback) {
		    if (value == '' || value == null || value == ' ' || value == false) { // isEmpty is a function that determines emptiness, you should define it
		        callback(false);
		    } else {
		        callback(true);
		    }
		}
		
		rowErased = function (changes) {
			var erased;
			
	    	if(changes.length > 3 && changes.length%4==0) {
	    		for (var int = 0; int < changes.length; int++) {
	    			if(changes[int][3] != '') {
	    				return false;
	    			}
	    			if((int%4 == 0 && changes[int][1] != 1) || (int%4 != 0 && changes[int][1] == 1)) {
	    				return false;
	    			}
	    		}
	    		erased = { 
	    				start: changes[0][0],
	    				times: (changes.length/4)
	    		}
			}
			else {
				return false;
			}
			return erased;
		}
		
		eraseEmptyRows = function() {
			var rowsnum = hot.countRows();
			hot.minSpareRows = 0;


			for (var int = 0; int < rowsnum; int++) {
				if(hot.isEmptyRow(int)) {
					hot.alter('remove_row', int);
					int--;
					rowsnum--;
				}
			}
		}
		
		var hot = new Handsontable($('#reportstable')[0], {
			data : report,
			rowHeaders: true,
		    colHeaders: [
		 		M.util.get_string('id', 'local_moereports'),
		        M.util.get_string('region', 'local_moereports'),         
		        M.util.get_string('name', 'local_moereports'),
		        M.util.get_string('city', 'local_moereports'),
		    ],
		    contextMenu: true,
		    columns: [
		              {
		            	  type: 'numeric',
		            	  validator: emptyValidator
		              },
		              {
		            	  type: 'text',
		            	  validator: emptyValidator
		              },
		              {
		            	  type: 'text',
		            	  validator: emptyValidator
		              },
		              {
		            	  type: 'text',
		            	  validator: emptyValidator
		              }
		    ],
		    beforeChange: function (changes, source) {
/*		    	for (var index in changes){
		    		if(changes[index][1] !== 3) {
		    			changes[index][3] = changes[index][3].trim();
		    		}
		    	}*/
		    	var rowsToErase = rowErased(changes);
		    	if(rowsToErase) {
		    		for (var int = 0; int < rowsToErase.times; int++) {
		    			hot.alter('remove_row', rowsToErase.start);
					}
		    		return false;
		    	}
		    	hot.validateCells(function(valid){
		    		if(valid) {
		    			
		    		}
		    	});
		    },
		    afterChange: function (changes, source) {
		        if(this.countEmptyRows(true) < 1) {
		        	this.alter("insert_row");
		        }
		    },
		    beforeRemoveRow: function(index, amount) {
		    	if(hot.getData()[index][0]) {
		    		deleted.push(hot.getData()[index][0]);
		    	}
			}
		});
	
	
	$("#savereporttable").click(function (e) {
		eraseEmptyRows();
		hot.validateCells(function (valid) {
			if(valid) {
				var call = ajax.call([
						{methodname: 'local_moereports_saveschools', args: { schools: hot.getData(), del: deleted }}]
				);
				
				call[0].done(function (resp) {
					hot.loadData(resp);
				}).fail(function (ex) {
					console.log(ex);
				});
			} else {
				alert("NO!");
			}
		});
	});
	
	$('[name="resetall"]').click(function(){
		$(this).closest('form').find("input[type='text'], textarea").val("");
	});

	}
	
	return new Report();
});