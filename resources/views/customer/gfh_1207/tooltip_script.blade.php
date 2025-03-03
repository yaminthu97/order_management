@php
    $truncatedText = Str::limit($text, $limit, '');
@endphp

<span 
    data-bs-toggle="tooltip" 
    title="{{ $text }}"
    style="cursor: pointer; border-bottom: 1px; color: #000;">
    {{ $truncatedText }}
    @if(strlen($text) > $limit)
        ...
    @endif
</span>

<script>
    $(document).ready(function(){
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
