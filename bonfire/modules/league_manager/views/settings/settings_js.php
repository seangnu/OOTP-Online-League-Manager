﻿	$('input#use_ootp_details').click(function() {
		$('#ootp_block').toggle(!this.checked);
	});
	$('#ootp_block').toggle(!$('input#use_ootp_details').is(':checked'));
	
	<?php
	echo('var sports = '.(isset($sports) ? json_encode($sports) : '[]').',');
	echo('sources = '.(isset($sources) ? json_encode($sources) : '[]').',');
	echo('versions = '.(isset($versions) ? json_encode($versions) : '[]').';');
	?>
	$('#game_sport').change(function() {
		var val = $('#game_sport').val();
		$('#game_source').empty()
		.each(sources.val, function(item, label) {
			$('#game_source').append('<option value="'+item+'">'+label+'</option>');
		})
		.removeAttr("disabled");
	});
	$('#game_source').change(function() {
		var val = $('#game_source').val();
		$('#source_version').empty()
		.each(versions.val, function(item, label) {
			$('#source_version').append('<option value="'+item+'">'+label+'</option>');
		})
		.removeAttr("disabled");
	});