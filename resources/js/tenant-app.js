import Quill from "quill";
import "quill/dist/quill.snow.css";
import { Picker } from "emoji-mart";
import Tribute from "tributejs";
import { prettyPrintJson } from "pretty-print-json";
import Recorder from "recorder-core";
import "recorder-core/src/engine/mp3";
import "recorder-core/src/engine/mp3-engine";
// Quill Start
window.Quill = Quill;
window.Tribute = Tribute;
// Make Recorder globally available
window.Recorder = Recorder;
// pretty json
window.preety = function (data) {
    let { response, category, raw } = data;

    try {
        response =
            typeof response === "string" ? JSON.parse(response) : response;
        category =
            typeof category === "string" ? JSON.parse(category) : category;
        raw = typeof raw === "string" ? JSON.parse(raw) : raw;
    } catch (error) {
        console.error("JSON Parsing Error:", error);
    }

    if (response && document.getElementById("json1")) {
        document.getElementById("json1").innerHTML =
            prettyPrintJson.toHtml(response);
    }
    if (raw && document.getElementById("datas")) {
        document.getElementById("datas").innerHTML =
            prettyPrintJson.toHtml(raw);
    }
    if (category && document.getElementById("raw")) {
        document.getElementById("raw").innerHTML =
            prettyPrintJson.toHtml(category);
    }
};
/**
 * Initializes the emoji picker and appends it to the designated container.
 */
function initializeEmojiPicker() {
    const pickerContainer = document.getElementById("emoji-picker-container");
    const emojiPickerElement = document.getElementById("emoji-picker");
    const textMessageInput = document.getElementById("textMessageInput");

    if (!pickerContainer || !emojiPickerElement || !textMessageInput) return;

    emojiPickerElement.innerHTML = "";

    const picker = new Picker({
        onEmojiSelect: (emoji) => {
            textMessageInput.value += emoji.native;
            textMessageInput.dispatchEvent(new Event("input"));
        },
    });

    emojiPickerElement.appendChild(picker);
}
window.initializeEmojiPicker = initializeEmojiPicker;
