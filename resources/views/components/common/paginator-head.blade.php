<div>
    <!-- It is never too late to be what you might have been. - George Eliot -->
    <!-- ページャーここから -->
    @if (isset($paginator))
        <input type="hidden" name="hidden_next_page_no" id="hidden_next_page_no" value="{{ $paginator->currentPage() }}">
        <script>
            function setNextPage($page) {
                document.getElementById("hidden_next_page_no").value = $page;

                document.Form1.submit();

                return false;

            }
        </script>
        <x-common.paginator-main
            :paginator="$paginator"
        />
    @endif
<!-- ページャーここまで -->
</div>
