jQuery(function($) {

	$.removeEmpty = function(array) {
		return $.map(array, function(val) {
			return val ? val : null;
		});
	};

	var sttSettings = $.parseJSON( $('.stt-settings-json').text() );

	$.each(sttSettings, function(key, tax) {
		$('#' + tax + 'div, #tagsdiv-' + tax).addClass('single-term-taxonomy');
	});


	var limitTagAddition = function(el) {
		var $metaBox = $(el).closest('.single-term-taxonomy');
		if ( !$metaBox.length ) return true;

		var currentTagsCount = $.removeEmpty( $metaBox.find('.the-tags').val().split(',') ).length,
			newTagsCount = $metaBox.find('input.newtag').val() ? $.removeEmpty( $metaBox.find('input.newtag').val().split(',') ).length : 1;

		if ( (currentTagsCount + newTagsCount) > 1 ) return false;

		return true;
	};

	$('.single-term-taxonomy .categorychecklist').on('mousedown', 'input:radio, label', function() {
		var $radio = $(this);

		if ( $(this).is('label') ) {
			$radio = $('input:radio', this);
		}

		if ( $radio.is(':checked') ) {
			var click_handler = function() {
				$radio.removeAttr('checked');

				$radio.unbind( 'click', click_handler );
			};

			$radio.click( click_handler );

			return false;
		}
	});

	$('.single-term-taxonomy .category-tabs > li > a').click(function() {
		var activeTermID = $(this).closest('.categorydiv').find('input:radio:checked').val();

		if ( activeTermID ) {
			$( $(this).attr('href') ).find('input:radio[value=' + activeTermID + ']').click();
		}
	});

	var tagBoxFlushTagsBackup = tagBox.flushTags;
	tagBox.flushTags = function(el, a, f) {
		var proceedExecution = limitTagAddition(el);

		if ( !proceedExecution ) return;

		tagBoxFlushTagsBackup.call(this, el, a, f);
	};

});