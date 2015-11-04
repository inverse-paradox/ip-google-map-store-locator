(function($) {
	$.fn.select_it = function( options ) {
		var choices_array = [];
		var count = 0;
		$(this).each(function() {
			choices_array = [];
			count++;
			var placeholder, selected, select_check, current, onchange, tabindex;
			var settings = $.extend({
				onchange: 'none',
				onchange_url:  'gallery-ajax.php',
				onchange_container: '.gallery .carousel.block',
				returned_div: 'toggled',
				placeholder: '...',
				tabindex: 0
			}, options );
			var option_count = 0;
			$(this).hide();
			var choices = $(this).html();
			if( $(this).attr('tabindex') ) { tabindex = $(this).attr('tabindex'); } else { tabindex = settings.tabindex; }
			if( $(this).data('placeholder') ) {
				placeholder = $(this).data('placeholder');
			} else {
				if( $(this).parents('form').parents('div').hasClass('gform_wrapper') ) {
					placeholder = $(this).find('option:first-child').text();
				} else {
					placeholder = settings.placeholder;
				}
			}
			$(this).find('option').each(function() { option_count++; if( option_count != 1 && $(this).is(':selected') ) { placeholder = $(this).text(); } });
			if( $(this).data('onchange') ) { onchange = $(this).data('onchange'); } else { onchange = settings.onchange; }
			$(this).children('option').each(function(index) {
				var choice_value = $(this).val();
				var choice_text = $(this).text();
				choices_array[index] = ['<li rel="'+choice_value+'">'+choice_text+'</li>'];
			});
			choices_array = choices_array.join('\r');
			$(this).after('<div id="select_'+count+'" class="select_it"><div class="display" tabindex="'+tabindex+'"><span class="current_item">'+placeholder+'</span><span class="tab"></span></div><ul style="display: none;">'+choices_array+'</ul></div><!--/select_it-->');
			$('.select_it .display').click(function(e) {
				e.stopImmediatePropagation();
				current = $(this).parents('.select_it').attr('id');
				if( $('#'+current+' .display').hasClass('open') ) {
					$('.display').removeClass('open');
					$('.select_it ul').hide();
				} else {
					$('.display').removeClass('open');
					$('.select_it ul').hide();
					$('#'+current+' .display').addClass('open');
					$('#'+current+' ul').show();
				}
			});
			$('.select_it .display').keydown(function(e) {
				e.stopImmediatePropagation();
				current = $(this).parents('.select_it').attr('id');
				var list_count = $('#'+current+' ul li').length;
				console.log('code is:  '+e.keyCode+' items:  '+list_count);

				// ARROW DOWN
				if( e.keyCode == 40 ) {
					if( $('#'+current+' ul li.selected').next('li').length ) {
						$('#'+current+' ul li.selected').next().addClass('selected');
						$('#'+current+' ul li.selected').first().removeClass('selected');
						selected = $('#'+current+' ul li.selected').html();
						$('#'+current+' .display .current_item').html(selected);
					} else {
						$('#'+current+' ul li').removeClass('selected');
						$('#'+current+' ul li:first-child').addClass('selected');
						selected = $('#'+current+' ul li.selected').html();
						$('#'+current+' .display .current_item').html(selected);
					}
					$(this).parents('#'+current).prev('select').find('option').each(function() {
						select_check = $(this).val();
						if( select_check == selected ) {
							$(this).parents('select').find('option').removeAttr('selected');
							$(this).attr('selected', 'selected');
						}
					});
				}

				// ARROW UP
				if( e.keyCode == 38 ) {
					if( $('#'+current+' ul li.selected').prev('li').length ) {
						$('#'+current+' ul li.selected').prev().addClass('selected');
						$('#'+current+' ul li.selected').last().removeClass('selected');
						selected = $('#'+current+' ul li.selected').html();
						$('#'+current+' .display .current_item').html(selected);
					} else {
						$('#'+current+' ul li').removeClass('selected');
						$('#'+current+' ul li:last-child').addClass('selected');
						selected = $('#'+current+' ul li.selected').html();
						$('#'+current+' .display .current_item').html(selected);
					}
					$(this).parents('#'+current).prev('select').find('option').each(function() {
						select_check = $(this).val();
						if( select_check == selected ) {
							$(this).parents('select').find('option').removeAttr('selected');
							$(this).attr('selected', 'selected');
						}
					});
				}

				// ENTER
				if( e.keyCode == 13 ) {
					selected = $('#'+current+' ul li.selected').html();
					$('#'+current+' .display .current_item').html(selected);
				}

				// TAB
				if( e.keyCode == 9 ) {
					var next_tabindex = parseInt(tabindex)+1;
					$("[TabIndex='"+next_tabindex+"']").focus();
				}
				if( e.shiftKey  && e.keyCode == 9 ) {
					var prev_tabindex = parseInt(tabindex)-1;
					$("[TabIndex='"+prev_tabindex+"']").focus();
				}				

				// LETTERS & NUMBERS
				if( e.keyCode >= 48 && e.keyCode <= 90 ) {
					console.log( e.keyCode );
					var character = String.fromCharCode(e.keyCode).toLowerCase();
					$('#'+current+' ul li').each(function() {
						var value = $(this).text().toLowerCase();
						if( character == value.charAt(0) ) {
							//console.log('found it!  character is:  '+character+' and first letter is:  '+value.charAt(0) );
							selected = $(this).attr('rel');
							selected_text = $(this).html();
							$('#'+current+' ul li').removeClass('selected');
							$('#'+current+' ul').hide();
							$(this).addClass('selected');
							/*
							selected = $('#'+current+' ul li.selected').html();
							$('#'+current+' .display .current_item').html(selected);
							*/
							$(this).parents('#'+current).prev('select').find('option').each(function() {
								select_check = $(this).val();
								if( select_check == selected ) {
									$(this).parents('select').find('option').removeAttr('selected');
									$(this).attr('selected', 'selected');
								}
							});
						}
					});
					$('#'+current).children('.display').removeClass('open');
					$('#'+current+' .display .current_item').html(selected);
					$('#'+current+' .display .current_item').html(selected_text);
				}
				return false;
			});
			$('.select_it ul li').click(function(e) {
				e.stopImmediatePropagation();
				current = $(this).parents('.select_it').attr('id');
				$('#'+current+' ul li').removeClass('selected');
				$('#'+current+' ul').hide();
				selected = $(this).attr('rel');
				selected_text = $(this).html();
				$(this).addClass('selected');
				$(this).parents('#'+current).prev('select').find('option').each(function() {
					select_check = $(this).val();
					if( select_check == selected ) {
						$(this).parents('select').find('option').removeAttr('selected');
						$(this).attr('selected', 'selected');
					}
				});
				$('#'+current).children('.display').removeClass('open');
				$('#'+current+' .display .current_item').html(selected);
				$('#'+current+' .display .current_item').html(selected_text);
				if( onchange == 'toggle' ) {
					var toggled = $(this).attr('rel');
					$('.'+settings.returned_div).hide();
					$('#'+toggled).show(200);
				}
				if( onchange == 'load' ) {
					$(settings.onchange_container).html('<img class="loading" src="http://centerforautism.aycdemo.com/wp-content/themes/cfa_theme/images/loading.gif" />');
					var value = $(this).attr('rel');
					$.ajax({
						type: 'POST',
						url:  ajax_call.ajaxurl,
						data: { action: 'gallery_loader', id: value },
						error: function(data) { console.log('fail'); },
						success: function(data) {
							console.log('is ' . data);
							$(settings.onchange_container).html(data);
							if( $('.gallery' ).hasClass('photos_videos') ) {
								$(settings.onchange_container).data('owlCarousel').reinit({
									itemsCustom: [[0, 1], [400, 2]]
								});
							} else {
								$(settings.onchange_container).data('owlCarousel').reinit({
									itemsCustom: [[0, 1], [480, 2], [850, 3], [1000, 4]]
								});
							}
						}
					});
				}
				if( onchange == 'change_url' ) {
					$('.gallery .videos .video_container .video iframe').hide();
					var new_url = $(this).attr('rel');
					$('.gallery .videos .video_container .video iframe').after('<img class="loading" src="http://localhost/~webdesigner/cfa_site/wp-content/themes/cfa_theme/images/loading.gif" />');
					$('.gallery .videos .video_container .video iframe').attr('src', new_url).fadeIn(200);
					$('.video .loading').remove();
				}
				if( onchange == 'submit' ) {
					$(this).parents('form').submit();
				}
			});
		});
		var i_count = 0;
		$('.select_it').each(function(){
			i_count++;
			$(this).attr('id', 'select_'+i_count);
		});
		$(document).click(function() {
			$('.select_it .display').removeClass('open');
			$('.select_it ul').hide();
		});
		return this;
	}; //  END SELECT_IT FUNCTION
}(jQuery));