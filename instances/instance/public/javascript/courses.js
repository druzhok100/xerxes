$(document).ready(highlight);
$(document).ready(resort);

// add and remove highlighting

function highlight()
{ 
	$(".reading-list-item").mouseover(function() {
			$(this).addClass("reading-list-highlight");
			$(this).children(".reading-list-item-action").css('visibility', 'visible');
		});

	$(".reading-list-item").mouseout(function() {
			$(this).removeClass("reading-list-highlight");
			$(this).children(".reading-list-item-action").css('visibility', 'hidden');
		});
}

// drag and drop

function resort()
{	
	$("#reading-list-content ul").sortable({ opacity: 0.6, cursor: 'move', update: function() {
		
		var course = $(this).attr('data-source')
		var order = $(this).sortable("serialize") + "&noredirect=1&course_id=" + course; 

		$.post("readinglist/reorder", order, function(theResponse){
			
			// do something!

		});														 
	}});
}
