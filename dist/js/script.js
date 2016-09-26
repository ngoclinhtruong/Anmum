$(document).ready(function(){
	$('.mac-mic-desc .tabs li').click(function(){
	  var index = $(this).index() + 1;

	  $('.mac-mic-desc .tabs li').removeClass('active');
	  $('.wpr-macro-micro .mac-mic-desc .tab-content li').removeClass('active');

	  $(this).addClass('active');
	  $('.wpr-macro-micro .mac-mic-desc .tab-content li:nth-child('+index+')').addClass('active');
	});


// bubbles description
	$('html').click(function() {
	  $('.wpr-macro-micro .bubbles .desc').removeClass('active');
	});

	$('.wpr-macro-micro .bubbles label').click(function(event){
		event.stopPropagation();
		$('.wpr-macro-micro .bubbles label').removeClass('active');
		$('.wpr-macro-micro .bubbles .desc').removeClass('active');

		$(this).addClass('active');
		$(this).next('.desc').addClass('active');
	});
});
