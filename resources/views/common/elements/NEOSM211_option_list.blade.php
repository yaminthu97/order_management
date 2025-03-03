{{-- ドロップダウンリスト項目設定） --}}
@foreach($arrayName as $keyId => $keyValue)
	<option value="{{$keyId}}"  @if (isset($currentId) && strval($currentId) === strval($keyId)){{'selected'}}@endif>{{$keyValue}}</option>
@endforeach
