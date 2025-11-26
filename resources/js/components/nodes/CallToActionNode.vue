<script setup>
import { ref, computed, watch } from "vue";
import { Handle, useVueFlow, useNode } from "@vue-flow/core";

const props = defineProps({
    id: { type: String, required: true },
    data: { type: Object, required: true },
    selected: { type: Boolean, default: false },
});
const output = ref(
    props.data.output?.[0] || {
        reply_text: "",
        buttonText: "",
        buttonLink: "",
        bot_header: "",
        bot_footer: "",
    }
);
const emit = defineEmits(["update:isValid"]);

const { removeNodes, nodes, addNodes } = useVueFlow();
const node = useNode();

// Form fields
const header = ref(output.value.bot_header || "");
const valueText = ref(output.value.reply_text || "");
const buttonText = ref(output.value.buttonText || "");
const buttonLink = ref(output.value.buttonLink || "");
const footer = ref(output.value.bot_footer || "");
const isExpanded = ref(true);

// Validation states
const errors = ref({
    valueText: false,
    buttonText: false,
    buttonLink: false,
});

// Validate URL function
function isValidUrl(url) {
    if (!url) return false;

    // Allow variable format: {{variable}}
    if (/^\{\{[\w_]+\}\}$/.test(url)) return true;

    try {
        new URL(url);
        return true;
    } catch (e) {
        return false;
    }
}

// Input handlers with automatic trimming
function handleHeaderInput() {
    if (header.value.length > 60) {
        header.value = header.value.substring(0, 60);
    }
    updateNodeData();
}

function handleValueTextInput() {
    if (valueText.value.length > 1000) {
        valueText.value = valueText.value.substring(0, 1000);
    }
    updateNodeData();
}

function handleButtonTextInput() {
    if (buttonText.value.length > 20) {
        buttonText.value = buttonText.value.substring(0, 20);
    }
    updateNodeData();
}

function handleFooterInput() {
    if (footer.value.length > 60) {
        footer.value = footer.value.substring(0, 60);
    }
    updateNodeData();
}

// Computed property for overall validation
const isValid = computed(() => {
    return (
        valueText.value.trim().length > 0 &&
        buttonText.value.trim().length > 0 &&
        buttonLink.value.trim().length > 0 &&
        isValidUrl(buttonLink.value)
    );
});

// Handle form validation
function validateForm() {
    errors.value = {
        valueText: valueText.value.trim().length === 0,
        buttonText: buttonText.value.trim().length === 0,
        buttonLink:
            buttonLink.value.trim().length === 0 ||
            !isValidUrl(buttonLink.value),
    };

    // Calculate overall validity
    const valid =
        !errors.value.valueText &&
        !errors.value.buttonText &&
        !errors.value.buttonLink;

    // Update the node data with validation state
    props.data.isValid = valid;

    // Emit the validation status to parent components
    emit("update:isValid", valid);

    return valid;
}

// Update node data
function updateNodeData() {
    const newOutput = {
        reply_text: valueText.value,
        buttonText: buttonText.value,
        buttonLink: buttonLink.value,
        bot_header: header.value,
        bot_footer: footer.value,
    };

    // Update the data
    props.data.output = [newOutput];

    validateForm();
}

// Node actions
function toggleExpand() {
    isExpanded.value = !isExpanded.value;
}

function handleClickDelete() {
    removeNodes(node.id);
}

function handleClickDuplicate() {
    const { type, position, data } = node.node;

    const newNode = {
        id: `node-${Date.now()}`,
        type,
        position: {
            x: position.x - 100,
            y: position.y - 100,
        },
        data: JSON.parse(JSON.stringify(data)), // Deep copy to prevent shared reference
    };

    addNodes(newNode);
}

// Watch for changes to update validation
watch(
    [valueText, buttonText, buttonLink, header, footer],
    () => {
        updateNodeData();
    },
    { immediate: true, deep: true }
);

// Node styling based on selection and validation
const nodeClasses = computed(() => {
    return `cta-node rounded-lg ${
        props.selected ? "border-info-500" : "border-gray-200"
    } ${
        !isValid.value ? "border-danger-300" : ""
    } border-2 bg-white shadow transition-all duration-200`;
});

// Character counts for all fields
const headerCount = computed(() => header.value.length);
const valueTextCount = computed(() => valueText.value.length);
const buttonTextCount = computed(() => buttonText.value.length);
const footerCount = computed(() => footer.value.length);
</script>

<template>
    <div class="h-full w-full">
        <Handle
            type="target"
            position="left"
            :class="[
                '!h-4 !w-4 !border-2 !border-white',
                isValid ? '!bg-warning-500' : '!bg-danger-500',
            ]"
        />

        <div
            :class="[
                nodeClasses,
                'overflow-hidden rounded-lg bg-white shadow-lg transition-all duration-200 hover:shadow-xl dark:bg-gray-800',
            ]"
            style="min-width: 300px; max-width: 350px"
        >
            <!-- Node type indicator - gradient bar -->
            <div
                :class="[
                    'h-1.5',
                    isValid
                        ? 'bg-gradient-to-r from-warning-500 to-warning-500'
                        : 'bg-gradient-to-r from-danger-500 to-orange-500',
                ]"
            ></div>

            <div class="p-4">
                <!-- Node Header -->
                <div class="node-header mb-3 flex items-center justify-between">
                    <div class="node-title flex items-center">
                        <div
                            :class="[
                                'node-icon mr-3 rounded-lg p-2 shadow-sm',
                                isValid
                                    ? 'bg-warning-100 text-warning-600 dark:bg-warning-900/50 dark:text-warning-300'
                                    : 'bg-danger-100 text-danger-600 dark:bg-danger-900/50 dark:text-danger-300',
                            ]"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="h-4 w-4"
                            >
                                <rect
                                    x="3"
                                    y="5"
                                    width="18"
                                    height="14"
                                    rx="2"
                                    ry="2"
                                ></rect>
                                <polyline points="3 7 12 13 21 7"></polyline>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span
                                class="text-sm font-medium text-gray-800 dark:text-gray-200"
                                >{{ data.label || "Call to Action" }}</span
                            >
                            <span
                                v-if="!isValid"
                                class="text-xs text-danger-500 dark:text-danger-400"
                                >Required fields missing</span
                            >
                        </div>
                    </div>

                    <div class="node-actions flex space-x-1">
                        <button
                            @click="toggleExpand"
                            class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-200 hover:bg-gray-50 hover:text-warning-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-warning-400"
                            :title="isExpanded ? 'Collapse' : 'Expand'"
                        >
                            <svg
                                v-if="isExpanded"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="h-4 w-4 transform transition-transform duration-300"
                            >
                                <polyline points="18 15 12 9 6 15"></polyline>
                            </svg>
                            <svg
                                v-else
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="h-4 w-4 transform transition-transform duration-300"
                            >
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <button
                            v-on:click="handleClickDuplicate"
                            class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-200 hover:bg-gray-50 hover:text-warning-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-warning-400"
                            title="Copy node"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-4 w-4"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"
                                />
                            </svg>
                        </button>
                        <button
                            v-on:click="handleClickDelete"
                            class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-danger-200 hover:bg-danger-50 hover:text-danger-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-danger-800/50 dark:hover:bg-danger-900/30 dark:hover:text-danger-400"
                            title="Delete node"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="h-4 w-4"
                            >
                                <path d="M3 6h18"></path>
                                <path
                                    d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"
                                ></path>
                                <path
                                    d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"
                                ></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Validation Error Message (only shown when not valid) -->
                <div
                    v-if="!isValid && isExpanded"
                    class="mb-3 rounded-md border border-danger-200 bg-danger-50 p-3 text-sm text-danger-600 dark:border-danger-800/50 dark:bg-danger-900/30 dark:text-danger-400"
                >
                    <div class="flex">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="mr-2 h-5 w-5 text-danger-500"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <div>
                            <div class="font-medium">
                                Please fix the following issues:
                            </div>
                            <ul class="ml-2 mt-1 list-inside list-disc">
                                <li v-if="errors.valueText">
                                    Value text is required
                                </li>
                                <li v-if="errors.buttonText">
                                    Button text is required
                                </li>
                                <li v-if="errors.buttonLink">
                                    Valid button link is required
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Node Content -->
                <div v-show="isExpanded" class="node-content space-y-4">
                    <!-- Header (optional) -->
                    <div class="node-field">
                        <label
                            class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-700 dark:text-gray-300"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="mr-1.5 h-3.5 w-3.5 text-warning-500"
                            >
                                <rect
                                    x="3"
                                    y="4"
                                    width="18"
                                    height="18"
                                    rx="2"
                                    ry="2"
                                ></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            Header (Optional)
                        </label>
                        <input
                            v-model="header"
                            @input="handleHeaderInput"
                            @paste="setTimeout(handleHeaderInput, 0)"
                            class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-warning-500 focus:ring focus:ring-warning-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            placeholder="Enter header text (optional)"
                            maxlength="60"
                        />
                        <div class="mt-1 flex justify-end text-xs">
                            <span
                                :class="
                                    headerCount >= 60
                                        ? 'text-warning-500'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                            >
                                {{ headerCount }}/60
                                <span v-if="headerCount >= 60" class="ml-1 text-warning-600">(Max reached)</span>
                            </span>
                        </div>
                    </div>

                    <!-- Value Text (required) -->
                    <div class="node-field">
                        <label
                            class="node-field-label mb-1.5 flex items-center text-xs font-medium"
                            :class="[
                                errors.valueText
                                    ? 'text-danger-600 dark:text-danger-400'
                                    : 'text-gray-700 dark:text-gray-300',
                            ]"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="mr-1.5 h-3.5 w-3.5"
                                :class="[
                                    errors.valueText
                                        ? 'text-danger-500'
                                        : 'text-warning-500',
                                ]"
                            >
                                <path
                                    d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                                ></path>
                            </svg>
                            Value Text
                            <span class="ml-1 text-danger-500">*</span>
                        </label>
                        <textarea
                            v-model="valueText"
                            @input="handleValueTextInput"
                            @paste="setTimeout(handleValueTextInput, 0)"
                            :class="[
                                'block w-full resize-none rounded-md bg-white px-3 py-2 text-sm shadow-sm focus:ring focus:ring-opacity-50 dark:bg-gray-700 dark:text-gray-200',
                                errors.valueText
                                    ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-200 dark:border-danger-700'
                                    : 'border-gray-300 focus:border-warning-500 focus:ring-warning-200 dark:border-gray-600',
                            ]"
                            placeholder="Enter value text"
                            rows="3"
                            maxlength="1000"
                        ></textarea>
                        <div class="mt-1 flex justify-between text-xs">
                            <span
                                class="text-danger-500"
                                v-if="errors.valueText"
                                >Value text is required</span
                            >
                            <span
                                :class="
                                    valueTextCount >= 1000
                                        ? 'text-warning-500'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                                class="ml-auto"
                            >
                                {{ valueTextCount }}/1000
                                <span v-if="valueTextCount >= 1000" class="ml-1 text-warning-600">(Max reached)</span>
                            </span>
                        </div>
                    </div>

                    <!-- Button Text (required) -->
                    <div class="node-field">
                        <label
                            class="node-field-label mb-1.5 flex items-center text-xs font-medium"
                            :class="[
                                errors.buttonText
                                    ? 'text-danger-600 dark:text-danger-400'
                                    : 'text-gray-700 dark:text-gray-300',
                            ]"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="mr-1.5 h-3.5 w-3.5"
                                :class="[
                                    errors.buttonText
                                        ? 'text-danger-500'
                                        : 'text-warning-500',
                                ]"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="2"
                                    ry="2"
                                ></rect>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                            Button Text
                            <span class="ml-1 text-danger-500">*</span>
                        </label>
                        <input
                            v-model="buttonText"
                            @input="handleButtonTextInput"
                            @paste="setTimeout(handleButtonTextInput, 0)"
                            :class="[
                                'block w-full rounded-md bg-white px-3 py-2 text-sm shadow-sm focus:ring focus:ring-opacity-50 dark:bg-gray-700 dark:text-gray-200',
                                errors.buttonText
                                    ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-200 dark:border-danger-700'
                                    : 'border-gray-300 focus:border-warning-500 focus:ring-warning-200 dark:border-gray-600',
                            ]"
                            placeholder="Enter button text"
                            maxlength="20"
                        />
                        <div class="mt-1 flex justify-between text-xs">
                            <span
                                class="text-danger-500"
                                v-if="errors.buttonText"
                                >Button text is required</span
                            >
                            <span
                                :class="
                                    buttonTextCount >= 20
                                        ? 'text-warning-500'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                                class="ml-auto"
                            >
                                {{ buttonTextCount }}/20
                                <span v-if="buttonTextCount >= 20" class="ml-1 text-warning-600">(Max reached)</span>
                            </span>
                        </div>
                    </div>

                    <!-- Button Link (required) -->
                    <div class="node-field">
                        <label
                            class="node-field-label mb-1.5 flex items-center text-xs font-medium"
                            :class="[
                                errors.buttonLink
                                    ? 'text-danger-600 dark:text-danger-400'
                                    : 'text-gray-700 dark:text-gray-300',
                            ]"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="mr-1.5 h-3.5 w-3.5"
                                :class="[
                                    errors.buttonLink
                                        ? 'text-danger-500'
                                        : 'text-warning-500',
                                ]"
                            >
                                <path
                                    d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"
                                ></path>
                                <path
                                    d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"
                                ></path>
                            </svg>
                            Button Link
                            <span class="ml-1 text-danger-500">*</span>
                        </label>
                        <input
                            v-model="buttonLink"
                            @input="updateNodeData"
                            :class="[
                                'block w-full rounded-md bg-white px-3 py-2 text-sm shadow-sm focus:ring focus:ring-opacity-50 dark:bg-gray-700 dark:text-gray-200',
                                errors.buttonLink
                                    ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-200 dark:border-danger-700'
                                    : 'border-gray-300 focus:border-warning-500 focus:ring-warning-200 dark:border-gray-600',
                            ]"
                            placeholder="Enter URL (https://example.com)"
                        />
                        <div
                            class="mt-1 text-xs text-danger-500"
                            v-if="errors.buttonLink"
                        >
                            Please enter a valid URL
                        </div>
                    </div>

                    <!-- Footer (optional) -->
                    <div class="node-field">
                        <label
                            class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-700 dark:text-gray-300"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="mr-1.5 h-3.5 w-3.5 text-warning-500"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="2"
                                    ry="2"
                                ></rect>
                                <line x1="3" y1="15" x2="21" y2="15"></line>
                            </svg>
                            Footer (Optional)
                        </label>
                        <input
                            v-model="footer"
                            @input="handleFooterInput"
                            @paste="setTimeout(handleFooterInput, 0)"
                            class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-warning-500 focus:ring focus:ring-warning-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            placeholder="Enter footer text (optional)"
                            maxlength="60"
                        />
                        <div class="mt-1 flex justify-end text-xs">
                            <span
                                :class="
                                    footerCount >= 60
                                        ? 'text-warning-500'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                            >
                                {{ footerCount }}/60
                                <span v-if="footerCount >= 60" class="ml-1 text-warning-600">(Max reached)</span>
                            </span>
                        </div>
                    </div>

                    <!-- CTA Preview -->
                    <div
                        class="mt-4 rounded-md border border-gray-200 p-4 dark:border-gray-700"
                    >
                        <div
                            class="mb-2 text-xs font-medium text-gray-700 dark:text-gray-300"
                        >
                            Preview:
                        </div>
                        <div class="rounded-md bg-gray-50 p-4 dark:bg-gray-800">
                            <div
                                v-if="header"
                                class="mb-2 font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ header }}
                            </div>
                            <div
                                class="mb-4 text-sm text-gray-700 dark:text-gray-300"
                            >
                                {{
                                    valueText ||
                                    "Your value text will appear here"
                                }}
                            </div>
                            <div
                                class="cursor-pointer rounded-md bg-warning-500 px-4 py-2 text-center font-medium text-white transition-colors hover:bg-warning-600"
                            >
                                {{ buttonText || "Button Text" }}
                            </div>
                            <div
                                v-if="footer"
                                class="mt-3 text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ footer }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
