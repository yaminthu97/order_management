<p class="mail">経理部各位</p>
<br>
<p class="mail">通信販売システムの請求入金データを下記の条件で出力しました。</p>
<p class="mail">ただちにダウンロードを行い、所定の場所に保存してください。</p>
<br>
<p class="mail">～条件～</p>
<p class="mail">出荷日：{{ $mailContent }}</p>

@push('css')
<style>
    .mail {
        color: black;
    }
</style>
@endpush
