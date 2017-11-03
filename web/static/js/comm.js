$().ready(function(){
	//enter event
	document.onkeydown = function (e) { 
		var theEvent = window.event || e; 
		var code = theEvent.keyCode || theEvent.which; 
		if (code == 13) { 
			$(".enter_event").click(); 
		} 
	} 
});