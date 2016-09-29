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
		$('.wpr-macro-micro .bubbles label').removeClass('active');
	  $('.wpr-macro-micro .bubbles .desc').removeClass('active');
	});

	$('.wpr-macro-micro .bubbles label').click(function(event){
		event.stopPropagation();
		$('.wpr-macro-micro .bubbles label').removeClass('active');
		$('.wpr-macro-micro .bubbles .desc').removeClass('active');

		$(this).addClass('active');
		$(this).siblings('.desc').addClass('active');
	});

	// scroll top
	$(".ctaBackToTop").click(function() {
    $('html, body').animate({
        scrollTop: $(".scrollBackToTop").offset().top
    }, 300);
	});

	// responsive js
	var windowsize = $(window).width();
	if(windowsize < 768){
		$('.macMicTabsSlide').bxSlider({
	  controls: false,
		onSlideAfter: function($slideElement, oldIndex, newIndex) {
	        // alert('onSlideAfter ' + oldIndex + ' ' + newIndex);
					var activeIndex = newIndex + 1;
					$('.wpr-macro-micro .mac-mic-desc .tab-content li').removeClass('active');
					$('.wpr-macro-micro .mac-mic-desc .tab-content li:nth-child('+activeIndex+')').addClass('active');
		 }
		});

		var removeSlider;
		$('.wpr-macro-micro .desc-mobile .btn-close').click(function(){
			$('.wpr-macro-micro .desc-mobile').hide();
			removeSlider.destroySlider();
		});

		$('.wpr-macro-micro .bubbles label.RedBloodCell').click(function(event){
			event.stopPropagation();
			var index = $(this).attr('tabindex');
			$('.RedBloodCellDesc').show();

			removeSlider = $('.RedBloodCellDescSlide').bxSlider({
				startSlide: index
			});
		});

		$('.wpr-macro-micro .bubbles label.generatingEnergy').click(function(event){
			event.stopPropagation();
			var index = $(this).attr('tabindex');
			$('.generatingEnergyDesc').show();
			removeSlider = $('.generatingEnergyDescSlide').bxSlider({
				startSlide: index
			});
		});

		$('.wpr-macro-micro .bubbles label.bonesTeeth').click(function(event){
			event.stopPropagation();
			var index = $(this).attr('tabindex');
			$('.bonesTeethDesc').show();
			removeSlider = $('.bonesTeethDescSlide').bxSlider({
				startSlide: index
			});
		});

		$('.wpr-macro-micro .bubbles label.growthDevelopment').click(function(event){
			event.stopPropagation();
			var index = $(this).attr('tabindex');
			$('.growthDevelopmentDesc').show();
			removeSlider = $('.growthDevelopmentDescSlide').bxSlider({
				startSlide: index
			});
		});

		$('.wpr-macro-micro .bubbles label.digestiveSystem').click(function(event){
			event.stopPropagation();
			var index = $(this).attr('tabindex');
			$('.digestiveSystemDesc').show();
			removeSlider = $('.digestiveSystemDescSlide').bxSlider({
				startSlide: index
			});
		});
	}
});
