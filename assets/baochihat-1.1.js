(function($){  
    var $w = $(window);
    var lazyLoadOffset = 50;
	var lazyLoadPlaceHolder = 'place-holder.gif';
	var infiniteLoading = false;

    function lazyLoad() {
		var scrollPos = $w.height() + $w.scrollTop() + lazyLoadOffset;
		$('img.thumb-pending').each(function(){
			var $img = $(this);           
			if($img.offset().top < scrollPos) {
				$img.attr('src',$img.attr('data-thumb-src')).removeClass('thumb-pending');
			}
		});
		if (!ranOut && !infiniteLoading && $('#bottom').offset().top < scrollPos) {
			infiniteLoading = true;
			$.post(
				'ajax.php'
				,{'feedLast': window.feedLast}
				,function(data){
					$('#feed').append(data);
					infiniteLoading = false;
					$('.feed-video, .feed-photo').lightBox();
				}
				,'html'
			);
		}
    }
	
	$(document).ready(function(){
		$('img.thumb').each(function(){
			var $img = $(this);
			$img.attr('data-thumb-src',$img.attr('src')).attr('src',lazyLoadPlaceHolder).addClass('thumb-pending');
		});
		lazyLoad();
		$w.resize(lazyLoad).scroll(lazyLoad);
		$('.feed-video, .feed-photo').lightBox();
	});
})(jQuery);