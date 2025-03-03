<h1>顧客新規作成画面</h1>
@if(!empty($custnewData))
    <p>{{ $custnewData['tel'] ?? ''}} </p>
    <p>{{ $custnewData['name_kanji'] ?? ''}} </p>
    <p>{{ $custnewData['name_kana'] ?? ''}} </p>
    <p>{{ $custnewData['postal'] ?? ''}} </p>
    <p>{{ $custnewData['address1'] ?? ''}} </p>
    <p>{{ $custnewData['address2'] ?? ''}} </p>
    <p>{{ $custnewData['email'] ?? ''}} </p>
@else
    <p>データが見つかりません。</p>
@endif
