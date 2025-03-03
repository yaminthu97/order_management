function csvBulkExport(event) {
    event.preventDefault();
    const form = document.getElementById("Form1");
    const formData = new FormData(form);

    fetch('./output', {
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
