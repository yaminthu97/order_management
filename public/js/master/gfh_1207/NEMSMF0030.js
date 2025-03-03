const yamatoValue = "100";
const seinoValue = "500";
// Run on page load in case of previously selected radio button
window.onload = function() {
    // Hide all `.only_yamato` and `.only_seino` elements initially
    document.querySelectorAll(".only_yamato, .only_seino").forEach(el => {
        el.style.display = "none";
    });

    const inputTextValue = document.querySelector('input[name="delivery_type"]')?.value;
    if (inputTextValue == yamatoValue) {
        document.querySelectorAll(".only_yamato").forEach(el => {
            el.style.display = "table-row";  // Show .only_yamato if 100 is selected
        });
    } else if (inputTextValue == seinoValue) {
        document.querySelectorAll(".only_seino").forEach(el => {
            el.style.display = "table-row";  // Show .only_seino if 500 is selected
        });
    }
};
