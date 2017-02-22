/**
  * @module report_moereports/reports
  */
define(['report_moereports/handsontable', 'jquery', 'core/ajax', 'core/notification'],function(Handsontable, $, ajax, notification){
	
	var Report = function() {
	};

	Report.prototype.init = function(report) {
		var deleted = [];
		
		var emptyValidator = function(value, callback) {
		    if (value === '' || value === null || value === ' ' || value === false) { 
		    	// isEmpty is a function that determines emptiness, you should define it
		        callback(false);
		    } else {
		        callback(true);
		    }
		};
		
		var rowErased = function (changes) {
			var erased;
			var colsnum = hot.countCols();
			
	    	if(changes.length > 3 && changes.length%4===0) {
	    		for (var int = 0; int < changes.length; int++) {
	    			if(changes[int][3] !== '') {
	    				return false;
	    			}
	    		}
	    		erased = { 
	    				start: changes[0][0],
	    				times: (changes.length/colsnum)
	    		};
			}
			else {
				return false;
			}
			return erased;
		};
		
		var eraseEmptyRows = function() {
			var rowsnum = hot.countRows();
			hot.minSpareRows = 0;


			for (var int = 0; int < rowsnum; int++) {
				if(hot.isEmptyRow(int)) {
					hot.alter('remove_row', int);
					int--;
					rowsnum--;
				}
			}
		};
		
		var hot = new Handsontable($('#reportstable')[0], {
			data : report,
			rowHeaders: true,
		    colHeaders: ['ID',
		 		M.util.get_string('symbol', 'report_moereports'),
		        M.util.get_string('region', 'report_moereports'),         
		        M.util.get_string('name', 'report_moereports'),
		        M.util.get_string('city', 'report_moereports'),
		    ],
		    contextMenu: true,
		    columns: [
		              {
		            	  type: 'numeric', 
		            	  readOnly: true
		              },
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
		    beforeChange: function (changes) {
		    	var rowsToErase = rowErased(changes);
		    	if(rowsToErase) {
		    		for (var int = 0; int < rowsToErase.times; int++) {
		    			hot.alter('remove_row', rowsToErase.start);
					}
		    		return false;
		    	}
		    },
		    afterChange: function () {
		        if(this.countEmptyRows(true) < 1) {
		        	this.alter("insert_row");
		        }
		    },
		    beforeRemoveRow: function(index) {
		    	if(hot.getData()[index][0]) {
		    		deleted.push(hot.getData()[index][0]);
		    	}
			}
		});
	
	
	$("#savereporttable").click(function () {
		eraseEmptyRows();
		hot.validateCells(function (valid) {
			if(valid) {
				var call = ajax.call([
						{methodname: 'report_moereports_saveschools', args: { schools: hot.getData(), del: deleted }}]
				);
				
				call[0].done(function (resp) {
					hot.loadData(resp);
				});
			} else {
				notification.addNotification({
				       message: M.util.get_string('changesnotsave', 'report_moereports'),
				       type: "error"
				     });
			}
		});
	});
	
	$('[name="resetall"]').click(function(){
		$(this).closest('form').find("input[type='text'], textarea").val("");
	});

	};
	
	return new Report();
});