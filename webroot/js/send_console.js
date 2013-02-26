(function( $ ){


	$(function(){
		var $cc = $("div.NewsletterSending div.consoleContainer");
		var $console = $("div.console", $cc);
		var height = $(window).height();
		height -= $("div.console_box", $cc).offset().top;
		height -= 60;
		height = Math.max(200,height);
		$("div.console_box", $cc).css('height',height);
		//console.log(height);
		
		scrollDown();
			
		$("a.ajax_button", $cc).click(function(){
			var confirmMsg = $(this).attr('confirm');
			if(confirmMsg){
				if(!confirm(confirmMsg)){
					return false;
				}
			}
			var url = $(this).attr('href');
			if(url){
				var $output = $(document.createElement('div'));
				var xhr = $.ajax({
				  "url" : url,
				  cache : false,
				  success: function(res,status){
					//console.log('action done');
					var $output = $(document.createElement('div'));
					$console.append($output);
					$output.append(res);
					$("div.console_box", $cc).scrollTop($console.outerHeight());
					/*if(window.console){
						console.log(res);
					}*/
				  }
				});
				//we dont need the reponse;
				var interval;
				interval = setInterval(function(){
					//console.log("test readyState : "+xhr.readyState);
					if(xhr.readyState != 0){
						xhr.abort();
						clearInterval(interval);
					}
				},1000);
			}
			start_stream();
			return false;
		});
		
		
		
		var stream = {started:0,delay:1000,waiting:0,data:{}}
		function start_stream(){
			if(!stream.started){
				stream.started = 1;
				if(!stream.waiting){
					stream_resume();
				}
			}
		}
		
		function stop_stream(){
			stream.started = 0;
		}
		
		function stream_handler(res,status){
			if(window.console){
				//console.log(res);
				console.log('stream_handler');
			}
			$('.stream',$console).remove();
			$('.original_log',$console).remove();
			var $output = $(document.createElement('div'));
			$output.addClass('stream');
			$console.append($output);
			$output.append(res);
			scrollDown();
			try{
				var json = $(".newsletterSendingOutput",$output).attr('json');
				if(json){
					eval('stream.data = ('+json+')');
					if(!stream.data.stream){
						stop_stream();
					}
				}
			}catch(e){
				if(window.console){
					console.log(e);
					console.log(json);
				}
			}
			
			stream_resume();
		}
		
		function stream_resume(){
			if(stream.started){
				stream.waiting = 1;
				if(stream.delay){
					setTimeout(stream_go,stream.delay)
				}else{
					stream_go();
				}
			}else{
				stream.started = 0;
				stream.waiting = 0;
			}
		}
		
		function stream_go(){
			if(stream.started){
				stream.waiting = 1;
				$.ajax({
				  "url" : $cc.attr('stream'),
				  cache : false,
				  success: stream_handler
				});
			}else{
				stream.started = 0;
				stream.waiting = 0;
			}
		}
		
		function scrollDown(){
			$("div.console_box", $cc).scrollTop($console.outerHeight());
		}
	});
	
	
})( jQuery );