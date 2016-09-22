$(document).ready(function() {
	initMobileMenu();
	initElementHiding();
	initLengthCounters();
	initjQueryAutocomplete();
	disableFormsAutocomplete();

	initWordEdit($('#word-edit'));
	initWords($('#words'));
	initWordView($('.word.view'));

	initConditionalCss();
	initSearchForm();
});

(function ($) {
	$.browserTest = function (a, z) {
		var u = 'unknown', x = 'X', m = function (r, h) {
			for (var i = 0; i < h.length; i = i + 1) {
				r = r.replace(h[i][0], h[i][1]);
			}

			return r;
		}, c = function (i, a, b, c) {
			var r = {
				name: m((a.exec(i) || [u, u])[1], b)
			};

			r[r.name] = true;

			r.version = (c.exec(i) || [x, x, x, x])[3];

			if (r.name.match(/safari/) && r.version > 400) {
				r.version = '2.0';
			}

			if (r.name === 'presto') {
				r.version = ($.browser.version > 9.27) ? 'futhark' : 'linear_b';
			}
			r.versionNumber = parseFloat(r.version, 10) || 0;
			r.versionX = (r.version !== x) ? (r.version + '').substr(0, 1) : x;
			r.className = r.name + r.versionX;

			return r;
		};

		a = (a.match(/Opera|Navigator|Minefield|KHTML|Chrome/) ? m(a, [
			[/(Firefox|MSIE|KHTML,\slike\sGecko|Konqueror)/, ''],
			['Chrome Safari', 'Chrome'],
			['KHTML', 'Konqueror'],
			['Minefield', 'Firefox'],
			['Navigator', 'Netscape']
		]) : a).toLowerCase();

		$.browser = $.extend((!z) ? $.browser : {}, c(a, /(camino|chrome|firefox|netscape|konqueror|lynx|msie|opera|safari)/, [], /(camino|chrome|firefox|netscape|netscape6|opera|version|konqueror|lynx|msie|safari)(\/|\s)([a-z0-9\.\+]*?)(\;|dev|rel|\s|$)/));

		$.layout = c(a, /(gecko|konqueror|msie|opera|webkit)/, [
			['konqueror', 'khtml'],
			['msie', 'trident'],
			['opera', 'presto']
		], /(applewebkit|rv|konqueror|msie)(\:|\/|\s)([a-z0-9\.]*?)(\;|\)|\s)/);

		$.os = {
			name: (/(win|mac|linux|sunos|solaris|iphone)/.exec(navigator.platform.toLowerCase()) || [u])[0].replace('sunos', 'solaris')
		};

		if (!z) {
			$('html').addClass([$.os.name, $.browser.name, $.browser.className, $.layout.name, $.layout.className].join(' '));
		}
	};

	$.browserTest(navigator.userAgent);
})(jQuery);

$(window).scroll(function() {
	if ($('#words').length > 0) {
		loadWords();
	}
});

function disableFormsAutocomplete() {
	$(':input, form').each(function(k, v) {
		var $form = $(v).is('form') ? $(v) : $(v).closest('form'),
			acForms = ['login-form'];

		if ($.inArray($form.attr('id'), acForms) == -1) {
			$(v).attr('autocomplete', 'off');
		}
	});
}

function initMobileMenu() {
	$('#mobile-menu-btn').click(function() {
		$('#mobile-menu').toggleClass('active');
	});
}

function initElementHiding() {
	$('html').click(function(e) {
		// hide mobile menu
		if (!$(e.target).is('#mobile-menu-btn') && !$(e.target).closest('#mobile-menu').length) {
			if ($('#mobile-menu').hasClass('active')) {
				$('#mobile-menu').removeClass('active');
			}
		}
	});
}

function ajaxGet(data, callback) {
	ajaxecute(data, callback, 'GET');
}

function ajaxPost(data, callback) {
	ajaxecute(data, callback, 'POST');
}

function ajaxecute(data, callback, method) {
	var res = {};

	if ($.active == 0) {
		$.ajax({
			url: (typeof data.ajaxUrl != 'undefined' ? data.ajaxUrl : '/ajax/' + method.toLowerCase() + '-data'),
			type: method,
			dataType: 'json',
			data: data,
			async: true,
			success: function(response, status, jqXHR) {
				res = response;

				if (typeof callback == 'function') {
					return callback(res);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				return false;
			}
		});
	}

	return res;
}


function ajaxForm($form, callback, beforeSubmit) {
	var res = {};

	$form.unbind('submit').bind('submit', function(e) {
		e.preventDefault();

		if ($.active == 0) {
			$.ajax({
				url: $form.attr('action'),
				type: 'POST',
				dataType: 'json',
				data: $form.serialize(),
				async: true,
				beforeSend: function(jqXHR) {
					if (typeof beforeSubmit == 'function') {
						if (beforeSubmit($form)) {
							$form.addClass('process');
							return true;
						}
						
						return false;
					}

					$form.addClass('process');
					
					return true;
				},
				success: function(response, status, jqXHR) {
					$form.removeClass('process');

					res = response;

					if (typeof callback == 'function') {
						return callback(res, $form);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					$form.removeClass('process');

					return false;
				}
			});
		}
	});

	return res;
}

function getObjectValues(obj) {
	var values = [];

	for (var key in obj) {
	    if (obj.hasOwnProperty(key)) {
	        values.push(obj[key]);
	    }
	}

	return values;
}

function capitalizeStr(str) {
	return str.substr(0, 1).toUpperCase() + str.substr(1);	
}

function initLengthCounters() {
	var $inputs = $('.length-counting');

	$inputs.each(function(k, v) {
		initLengthCounterWrap(v);
	});

	$inputs.keyup(function(e) {
		lengthCounterCount(this);
	});
}

function lengthCounterCount(v) {
	var maxLength = $(v).attr('maxlength') !== undefined ? $(v).attr('maxlength') : 100;
	var charsLeft = maxLength - $(v).val().length;

	$(v).val($(v).val().substr(0, maxLength));
	$(v).parent().attr('data-chars-left', charsLeft);
}

function initLengthCounterWrap(v) {
	var $wrapTmp = $('<div class="length-counting">');

	if ($(v).is('input[type="text"]')) {
		$wrapTmp.addClass('input-text');
	}

	$wrapTmp.addClass($(v).data('wrapClass'));

	var $wrap = $(v).removeClass('length-counting').wrap($wrapTmp).parent();
	lengthCounterCount(v);

	return $wrap;
}

function resetForm($form) {
	$form[0].reset();

	$form.find('input[type="text"], textarea').each(function(k, v) {
		lengthCounterCount(v);
	});
}

function elementInViewport(el) {
    //special bonus for those using jQuery
    if (typeof jQuery === "function" && el instanceof jQuery) {
        el = el[0];
    }

    var rect = el.getBoundingClientRect();

    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or $(window).height() */
        rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
    );
}

function loadWords() {
	var $wordsWrap = $('#words .wordsWrap');
	var $lastWord = $wordsWrap.find('.word').last();

	if (elementInViewport($lastWord) && !$wordsWrap.data('finish')) {
		var $progressIcon = $wordsWrap.find('.ajaxingWrap').length > 0 ? $wordsWrap.find('.ajaxingWrap') : $('<div class="ajaxingWrap"><span class="ajaxing"></div>').appendTo($wordsWrap);

		function postLoad(res) {
			$progressIcon.remove();
			
			switch (res.state) {
				case 'ok':
					$wordsWrap.data().page++;

					$(res.wordsHtml).each(function(k, v) {
						var $word = $($.parseHTML(v)).hide();
						$word.appendTo($wordsWrap).fadeIn(300);
					});
					break;

				case 'err':
					$wordsWrap.data('finish', 1);
					break;
			}
		}

		ajaxGet({'ajaxUrl': $wordsWrap.data('url'), 'page': $wordsWrap.data('page') + 1}, postLoad);
	}
}

function initWordEdit($div) {
	$div.find('.add-definition').click(function() {
		var $btn = $(this);
		var $newEl = $btn.prevAll('.definition').eq(0).clone(true);

		$btn.siblings('.word-add-err').remove();

		$newEl.find(':input').val('');
		$newEl.find('.input-del-btn').remove();
		$newEl.insertBefore($btn);
		addInputDelBtn($newEl);
	});

	$div.find('.definition').each(function(k, v) {
		addInputDelBtn($(v));
	});

	// проверка слова на наличие в базе
	var $wordWrap = $('.field-word-name');
	var $wordStatus = $wordWrap.find('.status').length > 0 ? $wordWrap.find('.status') : $('<span class="status">').appendTo($wordWrap.find('label'));
	var $request = false;
	
	$wordWrap.find('input[type="text"]').keyup(function() {
		var $input = $(this);

		if ($request !== false) {
			$request.abort();
		}

		$request = $.ajax({
			url: '/ajax/get-data',
			type: 'GET',
			dataType: 'json',
			data: {'type': 'checkWordExistence', 'name': $input.val()},
			async: true,
			beforeSend: function(jqXHR, settings) {
				$wordStatus.addClass('checking');
			},
			success: function(response, status, jqXHR) {
				var statusClass =  $input.val().length > 0 ? ('status ' + (response.exists ? 'exists' : 'not-exists')) : 'status';
				$wordStatus.attr('class', statusClass);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				return false;
			}
		});
	});


	var $form = $div.find('#word-add-form');

	$form.submit(function(e) {
		var definitionCheck = false;
		var tagCheck = false;

		$form.find('[name="Word[postDefinitions][meaning][]"]').each(function(k, v) {
			if ($(v).val() != '') {
				definitionCheck = true;
				return false;
			}
		});

		$form.find('[name="Word[postTags][]"]').each(function(k, v) {
			if ($(v).val() != '') {
				tagCheck = true;
				return false;
			}
		});

		$div.find('.word-add-err').remove();

		if (!definitionCheck) {
			$div.find('.definitions .add-definition').before($('<div class="word-add-err mb10">').text('Заполните хотя бы одно определение'));
		}

		if (!tagCheck) {
			$div.find('.field-word-posttags').eq(0).find('label').after($('<div class="word-add-err mb10 col-xs-12">').text('Заполните хотя бы один тег'))
		}

		if (!definitionCheck || !tagCheck) {
			e.preventDefault();
		}
	});
}

function addInputDelBtn($div) {
	var $delBtn = $('<a class="input-del-btn">').text('x').appendTo($div);

	$delBtn.click(function() {
		if ($delBtn.closest('.definitions').find('.definition').length > 1) {
			$div.remove();
		}
	});
}

function initjQueryAutocomplete() {
	jQueryAutocompletePatch();

	$('input[type="text"]').each(function(k, v) {
		var source = $(v).data('autocomplete');
		
		if (source !== undefined) {
			jQueryAutocomplete({'source': source, 'input': $(v)});
		}
	});
}

function jQueryAutocomplete(data) {
	if (typeof data.input == 'undefined')
	{
		return false;
	}

	var cache = {};
	var source;

	switch (data.source) {
		default:
			source = function(request, response) {
				var term = request.term;

				if (term in cache) {
					response(cache[term]);
					return;
				}

				$.getJSON('/ajax/autocomplete', {source: data.source, term: term, limit: 10}, function(data, status, xhr) {
					cache[term] = data;
					response(data);
				});
			}
			break;
	}

	data.input.autocomplete({
		delay: 100,
		minLength: 3,
		source: source
	}).autocomplete('widget').addClass('autocomplete-box');
}

function jQueryAutocompletePatch() {
	var oldFn = $.ui.autocomplete.prototype._renderItem; // старая функция рендеринга

	$.ui.autocomplete.prototype._renderItem = function(ul, item) {
		var re = new RegExp(this.term, 'gi');
		var t = item.label.replace(re, '<b>' + '$&' + '</b>');
	
		return $('<li>')
			   .data('item.autocomplete', item)
			   .append('<a>' + t + '</a>')
			   .appendTo(ul);
	};
}

function initWords($div) {
	$div.find('.word').each(function(k, v) {
		initWord($(v));
	});
}

function initWordView($div) {
	initWord($div, true);
	initQuizTeaser($div.find('.quiz-teaser'));
}


function initWord($div, view) {
	var view = typeof view != 'undefined' ? view : false;
	var $wordDefForm = $div.find('#word-definition-form');
	var $wordDefFormWrap = $wordDefForm.closest('.row');

	function beforeSubmit($form) {
		$form.find('.err').text('');
		return true;
	}

	function postDefAdd(res) {
		if (res.state == 'ok') {
			resetForm($wordDefForm);
			$wordDefForm.hide(0, function() {
				var $newItem = $($.parseHTML(res.html)).addClass('new').insertAfter($wordDefFormWrap);
				
				initWordDefRating($newItem.find('.rating'));

				setTimeout(function() {
					$newItem.removeClass('new');
				}, 300);
			});
		} else {
			var errorText = getObjectValues(res.errors).join("<br>");

			$wordDefForm.find('.err').html(errorText);
		}
	}

	ajaxForm($wordDefForm, postDefAdd, beforeSubmit);

	initWordDefRating($div.find('.rating:not(.lock)'));

	$('.add-def').click(function() {
		$wordDefForm.stop().slideDown(300);
	});

	if ($('.add-def').hasClass('click-me')) {
		$('.add-def').click();
	}
}

function initCustomValidity($div) {
	var intputElements = $div.find('input[type="text"], textarea').get();

    for (var i = 0; i < intputElements.length; i++) {
       	intputElements[i].oninput = 
       	intputElements[i].onchange = 
       	intputElements[i].oninvalid = 
       	function(e) {
            e.target.setCustomValidity('');

			var vText = '';

            if (!e.target.validity.valid) {
            	if (e.target.required) {
            		switch (e.target.type) {
            			case 'radio':
            				vText = 'Выберите один из вариантов';
            				break;

            			default:
            				vText = 'Поле обязательно';
            				break;
            		}
				}

            	if (e.target.pattern != '') {
            		switch(e.target.pattern) {
            			case 'https?://.+':
            				vText = 'URL должен начинаться с http:// или https://';
            				break;

            			case '[0-9]+':
            				vText = 'Можно только указать только цифры';
            				break;
            		}
				}
            	
            	e.target.setCustomValidity(vText);
            }
        };
    }
}

function initSocialButtons($div) {
	$div.socialLikes();
}

function initConditionalCss() {
	if ($.browser.name == 'safari' && $.browser.versionNumber < 7) {
		$('head').append($('<link>', {'href': '/css/no-flexbox.css?v=3', 'rel': 'stylesheet'}));
	}
}

function initSearchForm() {
	$('#search-form').find('.go').click(function() {
		$(this).closest('form').submit();
	});
}
