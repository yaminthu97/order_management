<style>
    .modal-dialog {
        margin: 200px auto;
    }
</style>
<div id="common-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="common-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="common-modal-title" class="modal-title"></h5>
            </div>
            <div id="common-modal-body" class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="閉じる">閉じる</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function setCommonModalTitle(title) {
        $('#common-modal-title').text(title);
    }
    function setCommonModalBody(body) {
        $('#common-modal-body').empty();
        $('#common-modal-body').append(body);
    }
</script>