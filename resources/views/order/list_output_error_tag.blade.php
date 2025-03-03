@if( isset($errorResult['csv_output_error'][$name]) )
	<div class="error u-mt--xs">
		{!! implode('<br>', $errorResult['csv_output_error'][$name]) !!}
	</div>
@endif
