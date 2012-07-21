
$.tablesorter.addWidget({id: "highlightOnHover",
			 format: function(table) {
			     $("tbody tr.highlight", table).remove();
			     $("tbody tr", table).hover(
				 function(){ $(this).children("td").addClass("highlight"); },
				 function(){ $(this).children("td").removeClass("highlight"); }
			     );
			 }
			});