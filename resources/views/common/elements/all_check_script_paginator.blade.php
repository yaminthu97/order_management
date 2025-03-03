<!-- 全チェックON/OFF ここから -->
<script>
    function checkAll()
    {
        var count = {{ isset( $paginator ) ? $paginator->count() : 0 }};
        if(count > 0)
        {
            var checkAll = document.getElementById('check_all');
            var checkBoxs = document.Form1.elements["{{ $column_name }}[]"];

            for(var i=0; i<checkBoxs.length; i++)
            {
                if(checkBoxs[i])
                {
                    if(checkAll.checked)
                    {
                        checkBoxs[i].checked = true;
                    }
                    else
                    {
                        checkBoxs[i].checked = false;
                    }
                }
            }
        }
    }
</script>
<!-- 全チェックON/OFF ここまで -->