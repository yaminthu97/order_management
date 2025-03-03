function csvImport(event) {
    event.preventDefault();
    const form = document.getElementById("Form1");
    const formData = new FormData(form);

    fetch('./import', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        }

    })
    .then(response => response.json())
    .then(data => {
        const exportError = document.getElementById('exportError');
        if(exportError !== null) exportError.innerHTML = '';
        const importError = document.getElementById('importError');
        importError.innerHTML = '';
        if(data.type === 'importError') {
            importError.innerHTML = `${data.importError}`;
            data.type = 'error';
            data.viewMessage = ['＜異常＞入力にエラーがあります。'];
        }
        const messageContainer = document.getElementById('messageContainer');
        messageContainer.innerHTML = '';
        const messageClass = data.type === 'success' ? 'icon_sy_notice_03' : 'icon_sy_notice_01';
        data.viewMessage.forEach(message => {
            messageContainer.innerHTML += `<div class="c-box--1700 c-tbl-border-all u-p--sm sy_notice u-mb--ss"><p class="${messageClass}">${message}</p></div>`;
        });
        
        
        document.getElementById('csv_input_file').value = '';
        window.scrollTo({ top: 0 });

    })
    .catch(error => console.error('Error:', error));
}

function csvExport(event) {
    event.preventDefault();
    document.getElementById('csv_output').value = 'csv_output';
    const form = document.getElementById("Form1");
    const formData = new FormData(form);
    
    fetch('./export', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        }
        
    })
    .then(response => response.json())
    .then(data => {
        const importError = document.getElementById('importError');
        if(importError !== null) importError.innerHTML = '';
        const exportError = document.getElementById('exportError');
        exportError.innerHTML = '';
        if(data.type === 'exportError') {
            exportError.innerHTML = `${data.exportError}`;
            data.type = 'error';
            data.viewMessage = ['＜異常＞入力にエラーがあります。'];
        }
        const messageContainer = document.getElementById('messageContainer');
        messageContainer.innerHTML = '';
        const messageClass = data.type === 'success' ? 'icon_sy_notice_03' : 'icon_sy_notice_01';
        data.viewMessage.forEach(message => {
            messageContainer.innerHTML += `<div class="c-box--1700 c-tbl-border-all u-p--sm sy_notice u-mb--ss"><p class="${messageClass}">${message}</p></div>`;
        });
        
        const checkboxes = document.querySelectorAll('.nowrap input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        window.scrollTo({ top: 0 });

    })
    .catch(error => console.error('Error:', error));
}

function csvBulkExport(event) {
    event.preventDefault();
    document.getElementById('csv_output').value = 'csv_bulk_output';
    const form = document.getElementById("Form1");
    const formData = new FormData(form);
    
    fetch('./export', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        }
        
    })
    .then(response => response.json())
    .then(data => {
        const messageContainer = document.getElementById('messageContainer');
        messageContainer.innerHTML = '';
        const messageClass = data.type === 'success' ? 'icon_sy_notice_03' : 'icon_sy_notice_01';
        data.viewMessage.forEach(message => {
            messageContainer.innerHTML += `<div class="c-box--1700 c-tbl-border-all u-p--sm sy_notice u-mb--ss"><p class="${messageClass}">${message}</p></div>`;
        });

        window.scrollTo({ top: 0 });

    })
    .catch(error => console.error('Error:', error));
}