// Hide all `.only_yamoto` and `.only_seino` elements initially
document.querySelectorAll(".only_yamoto, .only_seino").forEach(element => {
    element.style.display = "none";
});

const yamatoValue = "100";
const seinoValue = "500";

// Run on page load in case of previously selected radio button
window.onload = function() {
    const selectedValue = document.querySelector('input[name="delivery_type"]:checked')?.value;
    if (selectedValue == yamatoValue) {
        document.querySelectorAll(".only_yamoto").forEach(el => {
            el.style.display = "table-row";  // Show .only_yamoto if 100 is selected
        });
    } else if (selectedValue == seinoValue) {
        document.querySelectorAll(".only_seino").forEach(el => {
            el.style.display = "table-row";  // Show .only_seino if 500 is selected
        });
    }
    // Check if there was a previously selected file (after validation errors or reload)
    if (fileInput.files.length > 0) {
        fileNameDisplay.textContent = fileInput.files[0].name;
    }
};

// Event listener to change visibility on radio button selection
document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
    radio.addEventListener("change", function () {
        const selectedValue = document.querySelector('input[name="delivery_type"]:checked').value;

        // Hide all `.only_yamoto` and `.only_seino` elements first
        document.querySelectorAll(".only_yamoto, .only_seino").forEach(el => {
            el.style.display = "none";
        });

        // Show the correct ones based on selected value
        if (selectedValue == yamatoValue) {
            document.querySelectorAll(".only_yamoto").forEach(el => {
                el.style.display = "table-row";  // Show the .only_yamoto rows
            });
        } else if (selectedValue == seinoValue) {
            document.querySelectorAll(".only_seino").forEach(el => {
                el.style.display = "table-row";  // Show the .only_seino rows
            });
        }
    });
});

// Get references to the file input and file name display elements
const fileInput = document.getElementById("file");
const fileNameDisplay = document.getElementById("file-name");

// Add event listener to handle file selection change
fileInput.addEventListener("change", function() {
    // Check if any files are selected
    if (fileInput.files.length > 0) {
        // If files are selected, display the name of the first file
        fileNameDisplay.textContent = fileInput.files[0].name;
    } else {
        // If no file is selected, clear the file name display
        fileNameDisplay.textContent = "";
    }
});
