<script setup>
import { ref, computed, watch, onMounted } from "vue";
import {
    BsTrash,
    LuChevronDown,
    LuChevronUp,
    AkCirclePlus,
    CaHttp,
} from "@kalimahapps/vue-icons";

import { Handle, useVueFlow, useNode } from "@vue-flow/core";

const props = defineProps({
    id: { type: String, required: true },
    data: { type: Object, required: true },
    selected: { type: Boolean, default: false },
});

const emit = defineEmits(["update:isValid"]);

const { removeNodes, nodes, addNodes, toObject } = useVueFlow();
const node = useNode();

// Form fields
const output = ref(
    props.data.output?.[0] || {
        requestUrl: "",
        requestMethod: "get",
        requestFormat: "json",
        requestHeaders: [{ name: "", value: "", isCustom: false }],
        requestBody: [{ key: "", value: "" }],
    }
);

const requestUrl = ref(output.value.requestUrl || "");
const requestMethod = ref(output.value.requestMethod || "get");
const requestFormat = ref(output.value.requestFormat || "json");
const requestHeaders = ref(
    output.value.requestHeaders || [{ name: "", value: "", isCustom: false }]
);
const requestBody = ref(output.value.requestBody || [{ key: "", value: "" }]);

const isExpanded = ref(true);
const mergeFields = ref([]);
let tributeInstance = null;

function getMergeFields(chatType) {
    fetch(`/${tenantSubdomain}/load-mergefields/${chatType}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        },
        body: JSON.stringify({ type: chatType }),
    })
        .then((response) => response.json())

        .then((data) => {
            if (Array.isArray(data)) {
                mergeFields.value = data;
            } else {
                console.error("Expected an array but got:", data);
                mergeFields.value = [];
            }
        })
        .catch((error) => console.error("Fetch error:", error));
}

function handleTributeEvent() {
    setTimeout(() => {
        if (typeof window.Tribute === "undefined") {
            console.warn("Tribute.js is not loaded.");
            return;
        }

        if (!tributeInstance) {
            tributeInstance = new window.Tribute({
                trigger: "@",
                values: mergeFields.value,
            });
        }
        const elements = document.querySelectorAll(".mentionable");
        elements.forEach((el, index) => {
            if (el.getAttribute("data-tribute") !== "true") {
                tributeInstance.attach(el);
                el.setAttribute("data-tribute", "true");
            }
        });
    }, 1000);
}

function findRelationTypeFromSource() {
    const allNodes = toObject().nodes;
    // Find the specific node by ID or type (customize this condition as needed)
    const sourceNode = allNodes.find((node) => node.type === "trigger");
    if (
        sourceNode &&
        sourceNode.data.output &&
        sourceNode.data.output.length > 0
    ) {
        const relationType = sourceNode.data.output[0].rel_type;
        getMergeFields(relationType);
    }
}

// HTTP Methods
const httpMethods = [
    { value: "get", name: "GET" },
    { value: "post", name: "POST" },
    { value: "put", name: "PUT" },
    { value: "patch", name: "PATCH" },
    { value: "delete", name: "DELETE" },
];
const requestFormats = [
    { value: "json", name: "JSON" },
    { value: "form", name: "Form" },
]; // Validation states
const errors = ref({
    requestUrl: false,
    requestHeaders: false,
    requestBody: false,
    requestFormat: false,
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
        // Check if it's a relative URL or contains variables
        return /^(https?:\/\/|\/|{{.+}})/.test(url);
    }
}

// Common HTTP Headers
const commonHeaders = [
    { value: "accept", name: "Accept" },
    { value: "accept-charset", name: "Accept-Charset" },
    { value: "accept-encoding", name: "Accept-Encoding" },
    { value: "accept-language", name: "Accept-Language" },
    { value: "accept-datetime", name: "Accept-Datetime" },
    { value: "authorization", name: "Authorization" },
    { value: "cache-control", name: "Cache-Control" },
    { value: "connection", name: "Connection" },
    { value: "cookie", name: "Cookie" },
    { value: "content-length", name: "Content-Length" },
    { value: "content-type", name: "Content-Type" },
    { value: "date", name: "Date" },
    { value: "expect", name: "Expect" },
    { value: "forwarded", name: "Forwarded" },
    { value: "from", name: "From" },
    { value: "host", name: "Host" },
    { value: "if-match", name: "If-Match" },
    { value: "if-modified-since", name: "If-Modified-Since" },
    { value: "if-none-match", name: "If-None-Match" },
    { value: "if-range", name: "If-Range" },
    { value: "if-unmodified-since", name: "If-Unmodified-Since" },
    { value: "max-forwards", name: "Max-Forwards" },
    { value: "origin", name: "Origin" },
    { value: "pragma", name: "Pragma" },
    { value: "proxy-authorization", name: "Proxy-Authorization" },
    { value: "range", name: "Range" },
    { value: "referer", name: "Referer" },
    { value: "te", name: "TE" },
    { value: "user-agent", name: "User-Agent" },
    { value: "upgrade", name: "Upgrade" },
    { value: "via", name: "Via" },
    { value: "warning", name: "Warning" },
    { value: "custom", name: "Custom" },
];
// Input handlers with automatic trimming
function handleRequestUrlInput() {
    if (requestUrl.value.length > 2048) {
        requestUrl.value = requestUrl.value.substring(0, 2048);
    }
    updateNodeData();
}

// Request Headers management
function addRequestHeader() {
    requestHeaders.value.push({ name: "", value: "", isCustom: false });
    updateNodeData();
}

function removeRequestHeader(index) {
    if (requestHeaders.value.length > 1) {
        requestHeaders.value.splice(index, 1);
        updateNodeData();
    }
}

function handleHeaderInput(index, field) {
    const maxLengths = { name: 100, value: 500 };
    if (requestHeaders.value[index][field].length > maxLengths[field]) {
        requestHeaders.value[index][field] = requestHeaders.value[index][
            field
        ].substring(0, maxLengths[field]);
    }

    // Convert custom header names to lowercase with hyphens
    if (field === "name" && requestHeaders.value[index].isCustom) {
        const originalValue = requestHeaders.value[index][field];
        requestHeaders.value[index][field] = originalValue
            .toLowerCase()
            .replace(/\s+/g, "-");
    }

    updateNodeData();
}

function handleHeaderNameSelect(index, selectedValue) {
    if (selectedValue === "custom") {
        requestHeaders.value[index].name = "";
        requestHeaders.value[index].isCustom = true;
    } else {
        // Find the header object to get the display name
        const headerObj = commonHeaders.find((h) => h.value === selectedValue);
        requestHeaders.value[index].name = headerObj
            ? headerObj.value
            : selectedValue;
        requestHeaders.value[index].isCustom = false;
    }
    updateNodeData();
}

function clearCustomHeaderName(index) {
    requestHeaders.value[index].name = "";
    requestHeaders.value[index].isCustom = false;
    updateNodeData();
}

// Request Body management
function addRequestBodyField() {
    requestBody.value.push({ key: "", value: "" });
    updateNodeData();
}

function removeRequestBodyField(index) {
    if (requestBody.value.length > 1) {
        requestBody.value.splice(index, 1);
        updateNodeData();
    }
}

function handleBodyKeyInput(index) {
    if (requestBody.value[index].key.length > 256) {
        requestBody.value[index].key = requestBody.value[index].key.substring(
            0,
            256
        );
    }
    updateNodeData();
}

function handleBodyValueInput(index) {
    if (requestBody.value[index].value.length > 1000) {
        requestBody.value[index].value = requestBody.value[
            index
        ].value.substring(0, 1000);
    }
    updateNodeData();
}

// Computed property for overall validation
const isValid = computed(() => {
    const urlValid =
        requestUrl.value.trim().length > 0 && isValidUrl(requestUrl.value);
    const headersValid = requestHeaders.value.every(
        (header) =>
            header.name.trim().length > 0 && header.value.trim().length > 0
    );

    // Format validation - JSON not allowed for GET, DELETE, HEAD, OPTIONS
    const methodsWithoutBody = ["get", "delete", "head", "options"];
    const formatValid = !(
        methodsWithoutBody.includes(requestMethod.value.toLowerCase()) &&
        requestFormat.value === "json"
    );

    // Body validation - not required for GET, DELETE, HEAD, OPTIONS
    const bodyValid = methodsWithoutBody.includes(
        requestMethod.value.toLowerCase()
    )
        ? true // Body not required for these methods
        : requestBody.value.every(
              (bodyField) =>
                  bodyField.key.trim().length > 0 &&
                  bodyField.value.trim().length > 0
          );

    return urlValid && headersValid && formatValid && bodyValid;
});

// Handle form validation
function validateForm() {
    const methodsWithoutBody = ["get", "delete", "head", "options"];

    errors.value = {
        requestUrl:
            requestUrl.value.trim().length === 0 ||
            !isValidUrl(requestUrl.value),
        requestHeaders: !requestHeaders.value.every(
            (header) =>
                header.name.trim().length > 0 && header.value.trim().length > 0
        ),
        requestFormat:
            methodsWithoutBody.includes(requestMethod.value.toLowerCase()) &&
            requestFormat.value === "json",
        requestBody: methodsWithoutBody.includes(
            requestMethod.value.toLowerCase()
        )
            ? false // Body not required for these methods
            : !requestBody.value.every(
                  (bodyField) =>
                      bodyField.key.trim().length > 0 &&
                      bodyField.value.trim().length > 0
              ),
    };

    // Calculate overall validity
    const valid =
        !errors.value.requestUrl &&
        !errors.value.requestHeaders &&
        !errors.value.requestFormat &&
        !errors.value.requestBody;

    // Update the node data with validation state
    props.data.isValid = valid;

    // Emit the validation status to parent components
    emit("update:isValid", valid);

    return valid;
} // Update node data
function updateNodeData() {
    const newOutput = {
        requestUrl: requestUrl.value,
        requestMethod: requestMethod.value,
        requestFormat: requestFormat.value,
        requestHeaders: requestHeaders.value,
        requestBody: requestBody.value,
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
            y: position.y + 100,
        },
        data: JSON.parse(JSON.stringify(data)),
    };

    addNodes([newNode]);
}

// Close menus if clicked outside
onMounted(() => {
    // Validate on mount
    validateForm();

    findRelationTypeFromSource();
});

// Watch for changes
watch(
    [requestUrl, requestMethod, requestFormat, requestHeaders, requestBody],
    () => {
        updateNodeData();
    },
    { deep: true }
);
</script>

<template>
    <div
        class="h-full w-full rounded-xl border-2 bg-white shadow-lg transition-all duration-200 hover:shadow-2xl dark:bg-gray-800"
        :class="{
            'border-blue-400 ring-2 ring-blue-200 dark:border-blue-500 dark:ring-blue-900/50':
                selected,
            'border-red-400 ring-2 ring-red-200 dark:border-red-500 dark:ring-red-900/50':
                !isValid,
            'border-gray-200 dark:border-gray-700': isValid && !selected,
        }"
    >
        <!-- Gradient indicator bar -->
        <div
            class="h-1.5 rounded-t-xl bg-gradient-to-r"
            :class="
                isValid
                    ? 'from-sky-500 via-blue-500 to-cyan-500'
                    : 'from-red-500 via-orange-500 to-yellow-500'
            "
        ></div>

        <!-- Node Header -->
        <div
            class="flex items-center justify-between border-b border-gray-100 p-4 dark:border-gray-700"
        >
            <div class="flex items-center space-x-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg shadow-sm"
                    :class="
                        isValid
                            ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-300'
                            : 'bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-300'
                    "
                >
                    <CaHttp class="h-7 w-7" />
                </div>
                <div>
                    <h3
                        class="text-sm font-semibold text-gray-800 dark:text-gray-100"
                    >
                        {{ data.label || "API Request" }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ requestMethod.toUpperCase() }} Request
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-1">
                <button
                    @click="toggleExpand"
                    class="rounded-md border border-transparent p-1.5 text-gray-500 transition-all hover:border-gray-200 hover:bg-gray-50 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:bg-gray-700"
                    :title="isExpanded ? 'Collapse' : 'Expand'"
                >
                    <LuChevronUp v-if="isExpanded" class="h-4 w-4" />
                    <LuChevronDown v-else class="h-4 w-4" />
                </button>
                <button
                    v-on:click="handleClickDuplicate"
                    class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-200 hover:bg-gray-50 hover:text-info-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-info-400"
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
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Validation Alert -->
        <div
            v-if="!isValid && isExpanded"
            class="mx-4 mt-4 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800/50 dark:bg-red-900/20"
        >
            <div class="flex items-start">
                <svg
                    class="mr-2 h-5 w-5 flex-shrink-0 text-red-400"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"
                    />
                </svg>
                <div class="text-sm text-red-600 dark:text-red-400">
                    <span class="font-medium">Invalid configuration:</span>
                    <ul class="mt-1 list-disc list-inside space-y-1">
                        <li v-if="errors.requestUrl">
                            Valid request URL is required
                        </li>
                        <li v-if="errors.requestHeaders">
                            All header names and values are required
                        </li>
                        <li v-if="errors.requestFormat">
                            JSON format is not allowed for
                            {{ requestMethod.toUpperCase() }} requests
                        </li>
                        <li
                            v-if="
                                errors.requestBody &&
                                !['get', 'delete', 'head', 'options'].includes(
                                    requestMethod.toLowerCase()
                                )
                            "
                        >
                            All body keys and values are required
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Node Content -->
        <div v-show="isExpanded" class="space-y-4 p-4">
            <!-- Request URL -->
            <div class="space-y-2">
                <label
                    class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-200"
                >
                    <svg
                        class="mr-1.5 h-3.5 w-3.5"
                        :class="
                            errors.requestUrl ? 'text-red-500' : 'text-blue-500'
                        "
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                        />
                    </svg>
                    Request URL
                    <span class="ml-1 text-red-500">*</span>
                </label>
                <input
                    v-model="requestUrl"
                    @input="handleRequestUrlInput"
                    @paste="handleRequestUrlInput()"
                    class="block w-full rounded-lg border px-3 py-2.5 text-sm shadow-sm transition-all focus:ring-2 focus:ring-offset-1"
                    :class="{
                        'border-red-300 focus:border-red-500 focus:ring-red-200 dark:border-red-700 dark:focus:border-red-500':
                            errors.requestUrl,
                        'border-gray-300 focus:border-blue-500 focus:ring-blue-200 dark:border-gray-600 dark:focus:border-blue-500':
                            !errors.requestUrl,
                    }"
                    placeholder="https://api.example.com/endpoint"
                    maxlength="2048"
                />
                <div class="flex items-center justify-between text-xs">
                    <span v-if="errors.requestUrl" class="text-red-500">
                        Enter a valid URL
                    </span>
                    <span
                        :class="
                            requestUrl.length >= 2048
                                ? 'text-orange-500'
                                : 'text-gray-400'
                        "
                        class="ml-auto"
                    >
                        {{ requestUrl.length }}/2048
                    </span>
                </div>
            </div>

            <!-- Method and Format -->
            <div class="grid grid-cols-2 gap-3">
                <!-- Request Method -->
                <div class="space-y-2" @click.stop @mousedown.stop>
                    <label
                        class="block text-xs font-semibold text-gray-700 dark:text-gray-200"
                    >
                        Method
                    </label>
                    <v-select
                        v-model="requestMethod"
                        :options="httpMethods"
                        :reduce="(option) => option.value"
                        label="name"
                        @update:modelValue="updateNodeData"
                        placeholder="Select HTTP method"
                        :class="[
                            'vue-select-custom',
                            errors.requestMethod ? 'border-danger-300' : '',
                        ]"
                    />
                </div>

                <!-- Request Format -->
                <div class="space-y-2" @click.stop @mousedown.stop>
                    <label
                        class="block text-xs font-semibold text-gray-700 dark:text-gray-200"
                    >
                        Format
                    </label>
                    <v-select
                        v-model="requestFormat"
                        :options="requestFormats"
                        :reduce="(option) => option.value"
                        label="name"
                        @update:modelValue="updateNodeData"
                        placeholder="Select request format"
                        :class="[
                            'vue-select-custom',
                            errors.requestFormat ? 'border-danger-300' : '',
                        ]"
                    />
                </div>
            </div>
            <div v-if="errors.requestFormat" class="text-xs text-red-500 mt-1">
                JSON format is not supported for
                {{ requestMethod.toUpperCase() }} requests. Please select Form
                format.
            </div>
            <!-- Request Headers -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label
                        class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-200"
                    >
                        Headers ({{
                            requestHeaders.filter((h) => h.name || h.value)
                                .length
                        }})
                        <span class="ml-1 text-red-500">*</span>
                    </label>
                </div>
                <div
                    class="space-y-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-900/30"
                >
                    <div
                        v-for="(header, index) in requestHeaders"
                        :key="`header-${index}`"
                        class="space-y-4"
                    >
                        <div class="flex items-start gap-3">
                            <!-- Header Name -->
                            <div class="flex-1 flex flex-col gap-1">
                                <span
                                    class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-200"
                                >
                                    Name
                                    <span class="ml-1 text-red-500">*</span>
                                </span>
                                <!-- Select dropdown when not custom or empty -->
                                <select
                                    v-if="
                                        !header.isCustom &&
                                        (!header.name ||
                                            commonHeaders.some(
                                                (h) => h.value === header.name
                                            ))
                                    "
                                    :value="header.name || ''"
                                    @change="
                                        handleHeaderNameSelect(
                                            index,
                                            $event.target.value
                                        )
                                    "
                                    :class="[
                                        'block w-full rounded-md px-2 py-1.5 text-xs focus:ring-1 dark:bg-gray-800 dark:text-gray-200',
                                        !header.name.trim()
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-200 dark:border-red-700'
                                            : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600',
                                    ]"
                                >
                                    <option value="">Select header...</option>
                                    <option
                                        v-for="headerObj in commonHeaders.filter(
                                            (h) => h.value !== 'custom'
                                        )"
                                        :key="headerObj.value"
                                        :value="headerObj.value"
                                    >
                                        {{ headerObj.name }}
                                    </option>
                                    <option value="custom">Custom</option>
                                </select>
                                <!-- Custom input with cross icon -->
                                <div v-else class="relative">
                                    <input
                                        v-model="header.name"
                                        @input="
                                            handleHeaderInput(index, 'name')
                                        "
                                        :class="[
                                            'block w-full rounded-md px-2 py-1.5 pr-8 text-xs focus:ring-1 dark:bg-gray-800 dark:text-gray-200',
                                            !header.name.trim()
                                                ? 'border-red-300 focus:border-red-500 focus:ring-red-200 dark:border-red-700'
                                                : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600',
                                        ]"
                                        placeholder="Enter custom header name"
                                        maxlength="100"
                                    />
                                    <button
                                        @click="clearCustomHeaderName(index)"
                                        type="button"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors"
                                        title="Clear custom header and return to dropdown"
                                    >
                                        <svg
                                            class="h-3 w-3"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Header Value -->
                            <div class="flex-1 flex flex-col gap-1">
                                <span
                                    class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-200"
                                >
                                    Value
                                    <span class="ml-1 text-red-500">*</span>
                                </span>
                                <input
                                    v-model="header.value"
                                    @input="handleHeaderInput(index, 'value')"
                                    @focus="handleTributeEvent"
                                    :class="[
                                        'mentionable block w-full rounded-md px-2 py-1.5 text-xs focus:ring-1 dark:bg-gray-800 dark:text-gray-200',
                                        !header.value.trim()
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-200 dark:border-red-700'
                                            : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600',
                                    ]"
                                    placeholder="Value (required)"
                                    maxlength="500"
                                />
                                <!-- Counter -->
                                <div class="flex justify-end text-xs">
                                    <span
                                        :class="
                                            header.value.length >= 500
                                                ? 'text-orange-500'
                                                : header.value.length >= 450
                                                ? 'text-yellow-500'
                                                : 'text-gray-400 dark:text-gray-500'
                                        "
                                    >
                                        {{ header.value.length }}/500
                                        <span
                                            v-if="header.value.length >= 500"
                                            class="ml-1 text-orange-600"
                                        >
                                            (Max reached)
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <!-- Remove Button -->
                            <button
                                @click="removeRequestHeader(index)"
                                type="button"
                                :disabled="requestHeaders.length === 1"
                                class="flex-shrink-0 rounded p-1 text-red-500 transition-colors hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-red-900/20"
                                :title="
                                    requestHeaders.length === 1
                                        ? 'At least one header required'
                                        : 'Remove header'
                                "
                            >
                                <BsTrash class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="requestHeaders.length === 0"
                        class="py-4 text-center text-xs text-gray-400"
                    >
                        No headers added. Click "Add Header" to add one.
                    </div>
                </div>
                <div class="flex items-center justify-end">
                    <button
                        @click="addRequestHeader"
                        type="button"
                        class="flex items-center space-x-1 rounded-md border border-blue-400 bg-blue-50 px-2 py-1 text-xs font-medium text-blue-600 transition-colors hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50"
                    >
                        <AkCirclePlus class="h-3 w-3" />
                        <span>Add Header</span>
                    </button>
                </div>
            </div>

            <!-- Request Body -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label
                        class="flex items-center text-xs font-semibold text-gray-700 dark:text-gray-200"
                    >
                        Body ({{
                            requestBody.filter((b) => b.key || b.value).length
                        }})
                        <span
                            v-if="
                                !['get', 'delete', 'head', 'options'].includes(
                                    requestMethod.toLowerCase()
                                )
                            "
                            class="ml-1 text-red-500"
                            >*</span
                        >
                        <span v-else class="ml-1 text-gray-400 text-xs"
                            >(optional)</span
                        >
                    </label>
                </div>
                <div
                    class="space-y-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-900/30"
                >
                    <div
                        v-for="(bodyField, index) in requestBody"
                        :key="`body-${index}`"
                        class="space-y-1"
                    >
                        <div class="flex items-center space-x-2">
                            <div class="flex-1 space-y-1">
                                <input
                                    v-model="bodyField.key"
                                    @input="handleBodyKeyInput(index)"
                                    @paste="handleBodyKeyInput(index)"
                                    :class="[
                                        ' block w-full rounded-md px-2 py-1.5 text-xs focus:ring-1 dark:bg-gray-800 dark:text-gray-200',
                                        !bodyField.key.trim()
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-200 dark:border-red-700'
                                            : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600',
                                    ]"
                                    placeholder="Key (required)"
                                    maxlength="256"
                                />
                                <div class="flex justify-end text-xs">
                                    <span
                                        :class="
                                            bodyField.key.length >= 100
                                                ? 'text-orange-500'
                                                : bodyField.key.length >= 85
                                                ? 'text-yellow-500'
                                                : 'text-gray-400 dark:text-gray-500'
                                        "
                                    >
                                        {{ bodyField.key.length }}/100
                                        <span
                                            v-if="bodyField.key.length >= 100"
                                            class="ml-1 text-orange-600"
                                            >(Max reached)</span
                                        >
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1 space-y-1">
                                <input
                                    v-model="bodyField.value"
                                    @input="handleBodyValueInput(index)"
                                    @paste="handleBodyValueInput(index)"
                                    @focus="handleTributeEvent"
                                    :class="[
                                        'mentionable block w-full rounded-md px-2 py-1.5 text-xs focus:ring-1 dark:bg-gray-800 dark:text-gray-200',
                                        !bodyField.value.trim()
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-200 dark:border-red-700'
                                            : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600',
                                    ]"
                                    placeholder="Value (required)"
                                    maxlength="1000"
                                />
                                <div class="flex justify-end text-xs">
                                    <span
                                        :class="
                                            bodyField.value.length >= 1000
                                                ? 'text-orange-500'
                                                : bodyField.value.length >= 900
                                                ? 'text-yellow-500'
                                                : 'text-gray-400 dark:text-gray-500'
                                        "
                                    >
                                        {{ bodyField.value.length }}/1000
                                        <span
                                            v-if="
                                                bodyField.value.length >= 1000
                                            "
                                            class="ml-1 text-orange-600"
                                            >(Max reached)</span
                                        >
                                    </span>
                                </div>
                            </div>
                            <button
                                @click="removeRequestBodyField(index)"
                                type="button"
                                :disabled="requestBody.length === 1"
                                class="flex-shrink-0 rounded p-1 text-red-500 transition-colors hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-red-900/20"
                                :title="
                                    requestBody.length === 1
                                        ? 'At least one field required'
                                        : 'Remove field'
                                "
                            >
                                <BsTrash class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="requestBody.length === 0"
                        class="py-4 text-center text-xs text-gray-400"
                    >
                        No body fields added. Click "Add Field" to add one.
                    </div>
                </div>
                <div class="flex items-center justify-end mt-1">
                    <button
                        @click="addRequestBodyField"
                        type="button"
                        class="flex items-center space-x-1 rounded-md border border-blue-400 bg-blue-50 px-2 py-1 text-xs font-medium text-blue-600 transition-colors hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50"
                    >
                        <AkCirclePlus class="h-3 w-3" />
                        <span>Add Field</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Connection Handles -->
        <Handle
            type="target"
            position="left"
            :class="[
                '!h-4 !w-4 !border-2 !border-white z-10',
                isValid ? '!bg-info-500' : '!bg-danger-500',
            ]"
        />
    </div>
</template>

<style scoped>
.webhook-api-node {
    font-family: inherit;
}

/* Custom scrollbar */
.max-h-40::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.max-h-40::-webkit-scrollbar-track {
    background: transparent;
}

.max-h-40::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.max-h-40::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.dark .max-h-40::-webkit-scrollbar-thumb {
    background: #4b5563;
}

.dark .max-h-40::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
}
</style>
