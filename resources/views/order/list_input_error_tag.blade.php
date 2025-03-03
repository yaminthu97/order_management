@if( isset($errorResult['csv_input_error'][$name]) )
	<div class="error u-mt--xs">
	{!! implode('<br>', $errorResult['csv_input_error'][$name]) !!}
	</div>
@endif
