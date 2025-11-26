import TomSelect from "tom-select";
import "tom-select/dist/css/tom-select.css";
import intlTelInput from "intl-tel-input";
import html2canvas from "html2canvas";
import Chart from "chart.js/auto";
import DOMPurify from "dompurify";
window.Chart = Chart;
window.TomSelect = TomSelect;

//notify event
window.showNotification = function (message, type = "info") {
    const event = new CustomEvent("notify", {
        detail: [{ message: message, type: type }],
    });
    window.dispatchEvent(event);
};

// Function to initialize TomSelect safely
window.initTomSelect = function (selector, options = {}) {
    document.querySelectorAll(selector).forEach((element) => {
        if (!(element instanceof HTMLSelectElement)) return; // Ensure it's a <select>

        // Check if the <select> has valid options to prevent the "trim" error
        let hasValidOptions = Array.from(element.options).some(
            (opt) => opt.value?.trim() !== "" || opt.text?.trim() !== ""
        );
        if (!hasValidOptions) return;

        if (element.tomselect) {
            element.tomselect.destroy(); // Destroy existing instance if already initialized
        }
        new TomSelect(element, options);
    });
};

//sanitize
window.sanitizeMessage = function (message) {
    return DOMPurify.sanitize(message, {
        USE_PROFILES: {
            html: true,
        },
    });
};
// Initialize TomSelect when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    window.initTomSelect("#basic-select", {
        allowEmptyOption: true,
        placeholder: "Select an option...",
        allowEmptyOption: true,
    });

    window.initTomSelect("#multiple-select", {
        plugins: ["remove_button"],
        allowEmptyOption: true,
        maxItems: null,
        delimiter: ",",
        persist: false,
    });

    window.initTomSelect("#child-select", {
        plugins: ["remove_button"],
        allowEmptyOption: true,
        maxItems: null,
        persist: false,
    });

    window.initTomSelect(".tom-select", {
        maxOptions: 500,
        allowEmptyOption: true,
        persist: false,
    });
    window.initTomSelect(".tom-select-two", {
        persist: false,
    });
});
window.loadCountryListOnce = function () {
    const dummyInput = document.querySelector(".all-country-loader");

    if (!dummyInput) return;

    const iti = intlTelInput(dummyInput, {
        initialCountry: String(defaultCountryCode),
        autoPlaceholder: "off",
        nationalMode: false,
        loadUtils: () => import("intl-tel-input/build/js/utils.js"),
    });
    iti.promise
        .then(() => {
            // Get the country list and make it globally available
            window.AllCountryList = iti.countries;
        })
        .catch((err) => {
            console.error("Failed to load country list", err);
        });
};
document.addEventListener("DOMContentLoaded", function () {
    window.loadCountryListOnce();
    const phoneInputs = document.querySelectorAll(".phone-input");

    phoneInputs.forEach((input) => {
        const iti = intlTelInput(input, {
            strictMode: true,
            separateDialCode: false,
            autoHideDialCode: false,
            formatOnDisplay: false,

            initialCountry: String(defaultCountryCode),
            autoPlaceholder: "off",
            nationalMode: false,
            
            loadUtils: () => import("intl-tel-input/build/js/utils.js"),
        });

        iti.promise
            .then(() => {
                input.addEventListener("blur", function () {
                    const fullPhoneNumber = iti.getNumber();

                    if (input.value !== fullPhoneNumber) {
                        input.value = fullPhoneNumber;
                        input.dispatchEvent(new Event("input")); // Sync with Livewire
                    }
                });
            })
            .catch((error) => {
                console.error("Error loading utils.js", error);
            });
    });
});

window.copyToClipboard = (text) => {
    if (!text) {
        showNotification("No text provided to copy", "danger");
        return;
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        // Clipboard API
        navigator.clipboard
            .writeText(text)
            .then(() => {
                showNotification("Text copied to clipboard!", "success");
            })
            .catch(() => {
                showNotification("Failed to copy text", "danger");
            });
    } else {
        // Fallback for unsupported browsers
        const tempTextArea = document.createElement("textarea");
        tempTextArea.value = text;
        document.body.appendChild(tempTextArea);
        tempTextArea.select();
        try {
            document.execCommand("copy");
            showNotification("Text copied to clipboard", "success");
        } catch {
            showNotification("Failed to copy text", "danger");
        } finally {
            document.body.removeChild(tempTextArea);
        }
    }
};

window.captureScreenshot = function (elementId, fileName = "screenshot") {
    const captureArea = document.getElementById(elementId);

    if (!captureArea) {
        console.error(`Element with ID "${elementId}" not found.`);
        return;
    }

    const timestamp = new Date().toISOString().replace(/[:.]/g, "-");
    const dynamicFileName = `${fileName}-${timestamp}.png`;

    html2canvas(captureArea, {
        backgroundColor: null,
        scale: 2,
        letterRendering: true,
        height: captureArea.scrollHeight,
        useCORS: true,
    }).then((canvas) => {
        const link = document.createElement("a");
        link.href = canvas.toDataURL("image/png");
        link.download = dynamicFileName;
        link.click();
    });
};

window.getObserver = function () {
    if (document.getElementById("power-grid-table-base") != null) {
        const observer = new MutationObserver(function (
            mutationsList,
            observer
        ) {
            let parent = document.getElementById("power-grid-table-base");

            if (parent && parent.querySelector(".pg-filter-container")) {
                let firstChild = parent.querySelector(".pg-filter-container")
                    .children[0];

                if (
                    firstChild &&
                    !firstChild.classList.contains("2xl:grid-cols-5")
                ) {
                    firstChild.classList.remove("2xl:grid-cols-6");
                    firstChild.classList.add("2xl:grid-cols-5");
                }
            }
        });

        observer.observe(document.getElementById("power-grid-table-base"), {
            childList: true,
            subtree: true,
            attributes: true,
        });
    }
};
