<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Handle, useVueFlow, useNode } from "@vue-flow/core";

const props = defineProps({
    id: { type: String, required: true },
    data: { type: Object, required: true },
    selected: { type: Boolean, default: false },
});
const emit = defineEmits(["update:isValid"]);

const output = ref(
    props.data.output?.[0] || {
        reply_text: "",
        buttonText: "",
        sections: "",
        bot_header: "",
        bot_footer: "",
    }
);

const headerText = ref(output.value.bot_header || "");
const bodyText = ref(output.value.reply_text || "");
const footerText = ref(output.value.bot_footer || "");
const buttonText = ref(output.value.buttonText || "");
const sections = ref(
    output.value.sections
        ? typeof output.value.sections === "string"
            ? JSON.parse(output.value.sections)
            : output.value.sections
        : [
              {
                  title: "",
                  items: [{ id: Date.now(), title: "", description: "" }],
              },
          ]
);

// Rest of your variables
const isExpanded = ref(true);
const unconnectedItems = ref([]);

// WhatsApp list message constraints
const MAX_SECTIONS = 10;
const MAX_ITEMS_TOTAL = 10;
const MAX_ITEMS_PER_SECTION = 10;
const node = useNode();
const { toObject, edges, removeNodes, removeEdges, nodes, addNodes } =
    useVueFlow();

function addSection() {
    if (
        sections.value.length < MAX_SECTIONS &&
        getTotalItems() < MAX_ITEMS_TOTAL
    ) {
        sections.value.push({
            title: `Section ${sections.value.length + 1}`,
            items: [{ id: Date.now(), title: "", description: "" }],
        });
        updateNodeData();
    }
}

// Validation function to update node data and emit validation status
function validateNode() {
    // Check connections after validation
    checkItemConnections();

    // Update isValid status in node data
    props.data.isValid = isValid.value;

    // Emit validation status to parent
    emit("update:isValid", isValid.value);
}

function removeSection(index) {
    const section = sections.value[index];

    //  Remove all items inside the section one by one using removeItem
    for (let i = section.items.length - 1; i >= 0; i--) {
        removeItem(index, i); // You must go backward to avoid index shifting
    }

    // Remove the section itself
    sections.value.splice(index, 1);

    updateNodeData();
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

function addItem(sectionIndex) {
    const section = sections.value[sectionIndex];
    const nodeId = props.id;
    const allEdges = toObject().edges;
    if (
        section.items.length < MAX_ITEMS_PER_SECTION &&
        getTotalItems() < MAX_ITEMS_TOTAL
    ) {
        section.items.push({
            id: Date.now(),
            title: `Item ${section.items.length + 1}`,
            description: "",
            target: null,
        });

        updateNodeData();
        //   Get all edges coming from this node's button handles
        // 🔁 Loop over all items in the section to sync connections
        section.items.forEach((item, itemIndex) => {
            const handleId = `item-${sectionIndex}-${itemIndex}`;
            const edge = allEdges.find(
                (edge) =>
                    edge.source === nodeId && edge.sourceHandle === handleId
            );

            if (edge) {
                item.target = {
                    edgeId: edge.id,
                    targetNodeId: edge.target,
                };
            } else {
                item.target = null;
            }
        });
    }
}

function removeItem(sectionIndex, itemIndex) {
    const nodeId = props.id;
    const section = sections.value[sectionIndex];
    const handleId = `item-${sectionIndex}-${itemIndex}`;
    //  Get all current edges
    const allEdges = toObject().edges;

    //  Find and remove edges connected to this specific item handle
    const edgesToRemove = allEdges.filter(
        (edge) => edge.source === nodeId && edge.sourceHandle === handleId
    );

    edgesToRemove.forEach((edge) => removeEdges(edge.id));

    //  Remove the item
    section.items.splice(itemIndex, 1);

    //  Re-sync all remaining items in the section with current connections
    section.items.forEach((item, i) => {
        const newHandleId = `item-${sectionIndex}-${i}`;
        const connectedEdge = allEdges.find(
            (edge) =>
                edge.source === nodeId && edge.sourceHandle === newHandleId
        );

        item.target = connectedEdge
            ? {
                  edgeId: connectedEdge.id,
                  targetNodeId: connectedEdge.target,
              }
            : null;
    });

    updateNodeData();
}

function updateNodeData() {
    const newOutput = {
        reply_text: bodyText.value,
        buttonText: buttonText.value,
        sections: sections.value, // Store as object, will be stringified when saved
        bot_header: headerText.value,
        bot_footer: footerText.value,
    };

    // Update the data
    props.data.output = [newOutput];
}

function toggleExpand() {
    isExpanded.value = !isExpanded.value;
}

// Get total number of items across all sections
function getTotalItems() {
    return sections.value.reduce(
        (total, section) => total + section.items.length,
        0
    );
}

// Input handlers with automatic trimming
function handleHeaderTextInput() {
    if (headerText.value.length > 60) {
        headerText.value = headerText.value.substring(0, 60);
    }
    updateNodeData();
}

function handleBodyTextInput() {
    if (bodyText.value.length > 1024) {
        bodyText.value = bodyText.value.substring(0, 1024);
    }
    updateNodeData();
}

function handleFooterTextInput() {
    if (footerText.value.length > 60) {
        footerText.value = footerText.value.substring(0, 60);
    }
    updateNodeData();
}

function handleButtonTextInput() {
    if (buttonText.value.length > 20) {
        buttonText.value = buttonText.value.substring(0, 20);
    }
    updateNodeData();
}

function handleSectionTitleInput(sectionIndex) {
    if (sections.value[sectionIndex].title.length > 24) {
        sections.value[sectionIndex].title = sections.value[sectionIndex].title.substring(0, 24);
    }
    updateNodeData();
}

function handleItemTitleInput(sectionIndex, itemIndex) {
    if (sections.value[sectionIndex].items[itemIndex].title.length > 24) {
        sections.value[sectionIndex].items[itemIndex].title = sections.value[sectionIndex].items[itemIndex].title.substring(0, 24);
    }
    updateNodeData();
}

function handleItemDescriptionInput(sectionIndex, itemIndex) {
    if (sections.value[sectionIndex].items[itemIndex].description.length > 72) {
        sections.value[sectionIndex].items[itemIndex].description = sections.value[sectionIndex].items[itemIndex].description.substring(0, 72);
    }
    updateNodeData();
}

// Validation functions
const bodyTextValid = computed(() => bodyText.value.trim().length > 0);
const buttonTextValid = computed(() => buttonText.value.trim().length > 0);

const sectionValidations = computed(() => {
    return sections.value.map((section) => {
        const titleValid = section.title.trim().length > 0;

        const itemValidations = section.items.map((item) => ({
            id: item.id,
            titleValid: item.title.trim().length > 0,
        }));

        const allItemsValid = itemValidations.every((v) => v.titleValid);

        return {
            titleValid,
            items: itemValidations,
            allItemsValid,
        };
    });
});

// Check which items are not connected
function checkItemConnections() {
    // Get all edges
    const allEdges = toObject().edges;

    // Reset the unconnected items
    unconnectedItems.value = [];

    // Check each section and item for a connection
    sections.value.forEach((section, sectionIndex) => {
        section.items.forEach((item, itemIndex) => {
            const sourceHandleId = `item-${sectionIndex}-${itemIndex}`;
            const isConnected = allEdges.some(
                (edge) =>
                    edge.source === props.id &&
                    edge.sourceHandle === sourceHandleId
            );

            if (!isConnected) {
                unconnectedItems.value.push({ sectionIndex, itemIndex });
            }
        });
    });
}

// Updated isValid to include item connection check
const isValid = computed(() => {
    const formValid =
        bodyTextValid.value &&
        buttonTextValid.value &&
        sectionValidations.value.every(
            (section) => section.titleValid && section.allItemsValid
        );

    const connectionsValid = unconnectedItems.value.length === 0;

    return formValid && connectionsValid;
});

const canAddMoreItems = computed(() => {
    return getTotalItems() < MAX_ITEMS_TOTAL;
});

// Character counts for all fields
const headerTextCount = computed(() => headerText.value.length);
const bodyTextCount = computed(() => bodyText.value.length);
const footerTextCount = computed(() => footerText.value.length);
const buttonTextCount = computed(() => buttonText.value.length);

const nodeClasses = computed(() => {
    return `list-message-node rounded-lg border-2 ${
        props.selected ? "border-info-500" : "border-gray-200"
    } ${!isValid.value ? "border-danger-300" : ""} bg-white shadow`;
});

// Function to check if an item is unconnected
function isItemUnconnected(sectionIndex, itemIndex) {
    return unconnectedItems.value.some(
        (item) =>
            item.sectionIndex === sectionIndex && item.itemIndex === itemIndex
    );
}

// Setup edge watcher to track connections
function setupEdgeWatcher() {
    // Watch for changes to edges
    watch(
        edges,
        () => {
            checkItemConnections();
            validateNode();
        },
        { deep: true }
    );
}

onMounted(() => {
    // Run initial validation
    validateNode();

    // Setup edge watcher
    setupEdgeWatcher();

    // Initial connection check
    checkItemConnections();
});

// Watch for changes to validate
watch(isValid, (newValue) => {
    validateNode();
});
</script>

<template>
    <div class="h-full w-full">
        <div
            :class="[
                nodeClasses,
                'overflow-hidden rounded-lg border-2 border-gray-200 bg-white shadow-lg transition-all duration-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800',
            ]"
            style="min-width: 300px; max-width: 400px"
        >
            <!-- Node type indicator - gradient bar -->

            <div
                :class="[
                    ' h-1.5 rounded-t-md bg-gradient-to-r',
                    isValid
                        ? 'from-teal-500 to-emerald-600'
                        : 'from-danger-500 to-orange-500',
                ]"
            ></div>

            <Handle
                type="target"
                position="left"
                :class="[
                    '!h-4 !w-4 !border-2 !border-white !bg-gradient-to-r !shadow-md !transition-transform !duration-300',
                    isValid
                        ? '!from-teal-500 !to-emerald-600'
                        : '!from-danger-500 !to-orange-500',
                ]"
            />

            <div class="p-4">
                <div class="node-header mb-3 flex items-center justify-between">
                    <div class="node-title flex items-center">
                        <div
                            :class="[
                                'node-icon mr-3 rounded-lg p-2 shadow-sm',
                                isValid
                                    ? 'bg-teal-100 text-teal-600 dark:bg-teal-900/50 dark:text-teal-300'
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
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                        </div>
                        <span
                            class="text-sm font-medium text-gray-800 dark:text-gray-200"
                            >{{ data.label || "List Message" }}</span
                        >
                    </div>

                    <div class="node-actions flex space-x-1">
                        <button
                            @click="toggleExpand"
                            class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-200 hover:bg-gray-50 hover:text-teal-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-teal-400"
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
                            class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-200 hover:bg-gray-50 hover:text-teal-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-teal-400"
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

                <div v-show="isExpanded" class="node-content space-y-4">
                    <div
                        v-if="!isValid"
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
                                    <li v-if="!bodyTextValid">
                                        Body text is required
                                    </li>
                                    <li v-if="!buttonTextValid">
                                        Button text is required
                                    </li>
                                    <li
                                        v-if="
                                            sectionValidations.some(
                                                (section) => !section.titleValid
                                            )
                                        "
                                    >
                                        All section titles are required
                                    </li>
                                    <li
                                        v-if="
                                            sectionValidations.some(
                                                (section) =>
                                                    !section.allItemsValid
                                            )
                                        "
                                    >
                                        All item titles are required
                                    </li>
                                    <li v-if="unconnectedItems.length > 0">
                                        All list items must be connected to
                                        another node
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 space-y-3">
                        <div>
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
                                    class="mr-1.5 h-3.5 w-3.5 text-teal-500"
                                >
                                    <rect
                                        x="3"
                                        y="4"
                                        width="18"
                                        height="18"
                                        rx="2"
                                        ry="2"
                                    ></rect>
                                    <path d="M16 2v4M8 2v4M3 10h18"></path>
                                </svg>
                                Header Text
                            </label>
                            <input
                                v-model="headerText"
                                @input="handleHeaderTextInput"
                                @paste="setTimeout(() => handleHeaderTextInput(), 0)"
                                class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                placeholder="Header text (optional)"
                                maxlength="60"
                            />
                            <div class="mt-1 flex justify-end text-xs">
                                <span
                                    :class="
                                        headerTextCount >= 60
                                            ? 'text-warning-500'
                                            : 'text-gray-500 dark:text-gray-400'
                                    "
                                >
                                    {{ headerTextCount }}/60
                                    <span v-if="headerTextCount >= 60" class="ml-1 text-warning-600">(Max reached)</span>
                                </span>
                            </div>
                        </div>

                        <div>
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
                                    class="mr-1.5 h-3.5 w-3.5 text-teal-500"
                                >
                                    <path
                                        d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                                    ></path>
                                </svg>
                                Body Text
                                <span class="ml-1 text-danger-500">*</span>
                                <span
                                    v-if="!bodyTextValid"
                                    class="ml-1 text-xs font-medium text-danger-500"
                                    >(Required)</span
                                >
                            </label>
                            <textarea
                                v-model="bodyText"
                                @input="handleBodyTextInput"
                                @paste="setTimeout(() => handleBodyTextInput(), 0)"
                                class="block w-full resize-none rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                :class="{
                                    'border-danger-300 dark:border-danger-700':
                                        !bodyTextValid,
                                }"
                                placeholder="Body text"
                                rows="3"
                                maxlength="1024"
                            ></textarea>
                            <div class="mt-1 flex justify-end text-xs">
                                <span
                                    :class="
                                        bodyTextCount >= 1024
                                            ? 'text-warning-500'
                                            : 'text-gray-500 dark:text-gray-400'
                                    "
                                >
                                    {{ bodyTextCount }}/1024
                                    <span v-if="bodyTextCount >= 1024" class="ml-1 text-warning-600">(Max reached)</span>
                                </span>
                            </div>
                        </div>

                        <div>
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
                                    class="mr-1.5 h-3.5 w-3.5 text-teal-500"
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
                                Footer Text
                            </label>
                            <input
                                v-model="footerText"
                                @input="handleFooterTextInput"
                                @paste="setTimeout(() => handleFooterTextInput(), 0)"
                                class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                placeholder="Footer text (optional)"
                                maxlength="60"
                            />
                            <div class="mt-1 flex justify-end text-xs">
                                <span
                                    :class="
                                        footerTextCount >= 60
                                            ? 'text-warning-500'
                                            : 'text-gray-500 dark:text-gray-400'
                                    "
                                >
                                    {{ footerTextCount }}/60
                                    <span v-if="footerTextCount >= 60" class="ml-1 text-warning-600">(Max reached)</span>
                                </span>
                            </div>
                        </div>

                        <div>
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
                                    class="mr-1.5 h-3.5 w-3.5 text-teal-500"
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
                                    <line x1="12" y1="8" x2="12" y2="16"></line>
                                </svg>
                                Button Text
                                <span class="ml-1 text-danger-500">*</span>
                                <span
                                    v-if="!buttonTextValid"
                                    class="ml-1 text-xs font-medium text-danger-500"
                                    >(Required)</span
                                >
                            </label>
                            <input
                                v-model="buttonText"
                                @input="handleButtonTextInput"
                                @paste="setTimeout(() => handleButtonTextInput(), 0)"
                                class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                :class="{
                                    'border-danger-300 dark:border-danger-700':
                                        !buttonTextValid,
                                }"
                                placeholder="Button text"
                                maxlength="20"
                            />
                            <div class="mt-1 flex justify-end text-xs">
                                <span
                                    :class="
                                        buttonTextCount >= 20
                                            ? 'text-warning-500'
                                            : 'text-gray-500 dark:text-gray-400'
                                    "
                                >
                                    {{ buttonTextCount }}/20
                                    <span v-if="buttonTextCount >= 20" class="ml-1 text-warning-600">(Max reached)</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="sections-container">
                        <div class="mb-3 flex items-center justify-between">
                            <div
                                class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="mr-1.5 h-4 w-4 text-teal-500"
                                >
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line
                                        x1="3"
                                        y1="12"
                                        x2="3.01"
                                        y2="12"
                                    ></line>
                                    <line
                                        x1="3"
                                        y1="18"
                                        x2="3.01"
                                        y2="18"
                                    ></line>
                                </svg>
                                Sections & Items
                            </div>
                            <div
                                class="text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ getTotalItems() }}/{{
                                    MAX_ITEMS_TOTAL
                                }}
                                items
                            </div>
                        </div>

                        <div
                            v-for="(section, sectionIndex) in sections"
                            :key="sectionIndex"
                            class="section mb-3 rounded-md border border-gray-200 p-3 shadow-sm transition-all duration-200 hover:shadow dark:border-gray-700"
                            :class="{
                                'border-danger-300 bg-danger-50 dark:border-danger-800 dark:bg-danger-900/30':
                                    !sectionValidations[sectionIndex]
                                        .titleValid ||
                                    !sectionValidations[sectionIndex]
                                        .allItemsValid,
                            }"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <label
                                    class="node-field-label flex items-center text-xs font-medium text-gray-700 dark:text-gray-300"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="mr-1.5 h-3.5 w-3.5 text-teal-500"
                                    >
                                        <path d="M12 2H2v10h10V2z"></path>
                                        <path d="M12 12H2v10h10V12z"></path>
                                        <path d="M22 2h-10v10h10V2z"></path>
                                        <path d="M22 12h-10v10h10V12z"></path>
                                    </svg>
                                    Section Title
                                    <span class="ml-1 text-danger-500">*</span>
                                    <span
                                        v-if="
                                            !sectionValidations[sectionIndex]
                                                .titleValid
                                        "
                                        class="ml-1 text-xs font-medium text-danger-500"
                                    >
                                        (Required)
                                    </span>
                                </label>
                                <button
                                    @click="removeSection(sectionIndex)"
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 text-danger-500 transition-colors hover:bg-danger-100 hover:text-danger-700 dark:bg-gray-700 dark:text-danger-400 dark:hover:bg-danger-900/50 dark:hover:text-danger-300"
                                    :disabled="sections.length <= 1"
                                    :class="{
                                        'cursor-not-allowed opacity-50':
                                            sections.length <= 1,
                                    }"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="h-3 w-3"
                                    >
                                        <line
                                            x1="18"
                                            y1="6"
                                            x2="6"
                                            y2="18"
                                        ></line>
                                        <line
                                            x1="6"
                                            y1="6"
                                            x2="18"
                                            y2="18"
                                        ></line>
                                    </svg>
                                </button>
                            </div>

                            <input
                                v-model="section.title"
                                @input="handleSectionTitleInput(sectionIndex)"
                                @paste="setTimeout(() => handleSectionTitleInput(sectionIndex), 0)"
                                class="mb-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                :class="{
                                    'border-danger-300 dark:border-danger-700':
                                        !sectionValidations[sectionIndex]
                                            .titleValid,
                                }"
                                placeholder="Section title"
                                maxlength="24"
                            />
                            <div class="mb-3 flex justify-end text-xs">
                                <span
                                    :class="
                                        section.title.length >= 24
                                            ? 'text-warning-500'
                                            : 'text-gray-500 dark:text-gray-400'
                                    "
                                >
                                    {{ section.title.length }}/24
                                    <span v-if="section.title.length >= 24" class="ml-1 text-warning-600">(Max reached)</span>
                                </span>
                            </div>

                            <div class="items-container mt-3 space-y-3">
                                <div
                                    v-for="(item, itemIndex) in section.items"
                                    :key="item.id"
                                    class="item-row rounded-md border border-gray-200 p-3 shadow-sm transition-all duration-200 hover:shadow dark:border-gray-700"
                                    :class="{
                                        'border-danger-300 bg-danger-50 dark:border-danger-800 dark:bg-danger-900/30':
                                            !sectionValidations[sectionIndex]
                                                .items[itemIndex].titleValid ||
                                            isItemUnconnected(
                                                sectionIndex,
                                                itemIndex
                                            ),
                                    }"
                                >
                                    <div
                                        class="mb-2 flex items-center justify-between"
                                    >
                                        <label
                                            class="node-field-label flex items-center text-xs font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="mr-1.5 h-3.5 w-3.5 text-teal-500"
                                            >
                                                <line
                                                    x1="8"
                                                    y1="12"
                                                    x2="21"
                                                    y2="12"
                                                ></line>
                                                <line
                                                    x1="3"
                                                    y1="12"
                                                    x2="3.01"
                                                    y2="12"
                                                ></line>
                                            </svg>
                                            Item {{ itemIndex + 1 }}
                                            <span class="ml-1 text-danger-500"
                                                >*</span
                                            >
                                            <span
                                                v-if="
                                                    !sectionValidations[
                                                        sectionIndex
                                                    ].items[itemIndex]
                                                        .titleValid
                                                "
                                                class="ml-1 text-xs font-medium text-danger-500"
                                            >
                                                (Required)
                                            </span>
                                            <span
                                                v-if="
                                                    isItemUnconnected(
                                                        sectionIndex,
                                                        itemIndex
                                                    )
                                                "
                                                class="ml-1 text-xs font-medium text-danger-500"
                                            >
                                                (Not connected)
                                            </span>
                                        </label>
                                        <button
                                            @click="
                                                removeItem(
                                                    sectionIndex,
                                                    itemIndex
                                                )
                                            "
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 text-danger-500 transition-colors hover:bg-danger-100 hover:text-danger-700 dark:bg-gray-700 dark:text-danger-400 dark:hover:bg-danger-900/50 dark:hover:text-danger-300"
                                            :disabled="
                                                section.items.length <= 1
                                            "
                                            :class="{
                                                'cursor-not-allowed opacity-50':
                                                    section.items.length <= 1,
                                            }"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="h-3 w-3"
                                            >
                                                <line
                                                    x1="18"
                                                    y1="6"
                                                    x2="6"
                                                    y2="18"
                                                ></line>
                                                <line
                                                    x1="6"
                                                    y1="6"
                                                    x2="18"
                                                    y2="18"
                                                ></line>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="flex flex-col space-y-2">
                                        <input
                                            v-model="item.title"
                                            @input="handleItemTitleInput(sectionIndex, itemIndex)"
                                            @paste="setTimeout(() => handleItemTitleInput(sectionIndex, itemIndex), 0)"
                                            class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                            :class="{
                                                'border-danger-300 dark:border-danger-700':
                                                    !sectionValidations[
                                                        sectionIndex
                                                    ].items[itemIndex]
                                                        .titleValid,
                                            }"
                                            placeholder="Item title"
                                            maxlength="24"
                                        />
                                        <div class="mt-1 flex justify-end text-xs">
                                            <span
                                                :class="
                                                    item.title.length >= 24
                                                        ? 'text-warning-500'
                                                        : 'text-gray-500 dark:text-gray-400'
                                                "
                                            >
                                                {{ item.title.length }}/24
                                                <span v-if="item.title.length >= 24" class="ml-1 text-warning-600">(Max reached)</span>
                                            </span>
                                        </div>
                                        <input
                                            v-model="item.description"
                                            @input="handleItemDescriptionInput(sectionIndex, itemIndex)"
                                            @paste="setTimeout(() => handleItemDescriptionInput(sectionIndex, itemIndex), 0)"
                                            class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                            placeholder="Item description (optional)"
                                            maxlength="72"
                                        />
                                        <div class="mt-1 flex justify-end text-xs">
                                            <span
                                                :class="
                                                    item.description.length >= 72
                                                        ? 'text-warning-500'
                                                        : 'text-gray-500 dark:text-gray-400'
                                                "
                                            >
                                                {{ item.description.length }}/72
                                                <span v-if="item.description.length >= 72" class="ml-1 text-warning-600">(Max reached)</span>
                                            </span>
                                        </div>

                                        <!-- Item connection point -->
                                        <div class="relative mt-2 h-5">
                                            <Handle
                                                :id="`item-${sectionIndex}-${itemIndex}`"
                                                type="source"
                                                position="right"
                                                :style="`right: 0; top: 50%; transform: translateY(-50%)`"
                                                :class="[
                                                    '!h-3 !w-3 !border-2 !border-white !shadow-md !transition-transform !duration-300 dark:!border-gray-800',
                                                    isItemUnconnected(
                                                        sectionIndex,
                                                        itemIndex
                                                    )
                                                        ? '!animate-pulse !bg-gradient-to-r !from-danger-500 !to-orange-500'
                                                        : '!bg-gradient-to-r !from-teal-500 !to-emerald-500',
                                                ]"
                                            />
                                            <div
                                                class="absolute right-4 top-0 flex justify-between text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                <div>
                                                    {{ section.title }} - Item
                                                    {{ itemIndex + 1 }}
                                                </div>
                                                <div
                                                    v-if="
                                                        isItemUnconnected(
                                                            sectionIndex,
                                                            itemIndex
                                                        )
                                                    "
                                                    class="ml-2 text-danger-500"
                                                >
                                                    Not connected
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    @click="addItem(sectionIndex)"
                                    class="w-full rounded-md bg-teal-50 px-3 py-2 text-sm font-medium text-teal-600 transition-colors hover:bg-teal-100 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-50 dark:bg-teal-900/30 dark:text-teal-400 dark:hover:bg-teal-900/50"
                                    :disabled="
                                        !canAddMoreItems ||
                                        section.items.length >=
                                            MAX_ITEMS_PER_SECTION
                                    "
                                    :class="{
                                        'cursor-not-allowed opacity-50':
                                            !canAddMoreItems ||
                                            section.items.length >=
                                                MAX_ITEMS_PER_SECTION,
                                    }"
                                >
                                    <span
                                        class="flex items-center justify-center"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="mr-1 h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <line
                                                x1="12"
                                                y1="5"
                                                x2="12"
                                                y2="19"
                                            ></line>
                                            <line
                                                x1="5"
                                                y1="12"
                                                x2="19"
                                                y2="12"
                                            ></line>
                                        </svg>
                                        Add Item
                                        {{
                                            canAddMoreItems &&
                                            section.items.length <
                                                MAX_ITEMS_PER_SECTION
                                                ? ""
                                                : "(Max reached)"
                                        }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <button
                            @click="addSection"
                            class="mb-4 w-full rounded-md bg-teal-50 px-3 py-2 text-sm font-medium text-teal-600 transition-colors hover:bg-teal-100 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-50 dark:bg-teal-900/30 dark:text-teal-400 dark:hover:bg-teal-900/50"
                            :disabled="
                                !canAddMoreItems ||
                                sections.length >= MAX_SECTIONS
                            "
                            :class="{
                                'cursor-not-allowed opacity-50':
                                    !canAddMoreItems ||
                                    sections.length >= MAX_SECTIONS,
                            }"
                        >
                            <span class="flex items-center justify-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="mr-1 h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Add Section
                                {{
                                    canAddMoreItems &&
                                    sections.length < MAX_SECTIONS
                                        ? ""
                                        : "(Max reached)"
                                }}
                            </span>
                        </button>

                        <div
                            class="mt-3 rounded-md border border-gray-200 bg-gray-50 p-3 text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-400"
                        >
                            <div class="mb-1 flex items-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="mr-1 h-4 w-4 text-gray-400"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="16"></line>
                                    <line
                                        x1="12"
                                        y1="16"
                                        x2="12.01"
                                        y2="16"
                                    ></line>
                                </svg>
                                Usage Information
                            </div>
                            <ul class="ml-5 list-disc space-y-1">
                                <li>Required fields are marked with *</li>
                                <li>
                                    Maximum {{ MAX_ITEMS_TOTAL }} items total
                                    across all sections
                                </li>
                                <li>
                                    Maximum {{ MAX_SECTIONS }} sections allowed
                                </li>
                                <li>
                                    Maximum {{ MAX_ITEMS_PER_SECTION }} items
                                    per section
                                </li>
                                <li>
                                    Each list item must be connected to another
                                    node
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
