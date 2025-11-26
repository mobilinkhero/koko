<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Handle, useVueFlow, useNode } from "@vue-flow/core";
const { removeNodes, nodes, addNodes, removeEdges } = useVueFlow();

const props = defineProps({
    id: { type: String, required: true },
    data: { type: Object, required: true },
    selected: { type: Boolean, default: false },
});

const emit = defineEmits(["update:isValid"]);
const { toObject } = useVueFlow();
const output = ref(
    props.data.output?.[0] || {
        reply_text: "",
        button1: "",
        button2: "",
        button3: "",
    }
);

// Node content logic
const message = ref(output.value.reply_text || "");
const isExpanded = ref(true);
const node = useNode();
const MAX_BUTTONS = 3;
const unconnectedButtons = ref([]);
const mergeFields = ref([]);
const buttons = ref([]);

function getMergeFields(chatType) {
  fetch(`/${tenantSubdomain}/load-mergefields/${chatType}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({ type: chatType }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (Array.isArray(data)) {
        mergeFields.value = data;
      } else {
        console.error('Expected an array but got:', data);
        mergeFields.value = [];
      }
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

let tributeInstance = null;

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

function addButton() {
    if (buttons.value.length < MAX_BUTTONS) {
        buttons.value.push({
            id: Date.now(),
            text: "",
            value: "",
            target: null,
        });

        updateNodeData();
        // Check connections after adding a button
        checkButtonConnections();
        //  Check connected edges after update
        const nodeId = props.id;
        const allEdges = toObject().edges;

        //   Get all edges coming from this node's button handles
        const connectedEdges = allEdges.filter(
            (edge) =>
                edge.source === nodeId &&
                edge.sourceHandle?.startsWith("button-")
        );

        // Reset all button targets first
        buttons.value.forEach((btn) => {
            btn.target = null;
        });

        //  Match each button with its edge and store the info
        connectedEdges.forEach((edge) => {
            const match = edge.sourceHandle?.match(/button-(\d+)/);
            if (match) {
                const index = parseInt(match[1]);
                if (buttons.value[index]) {
                    buttons.value[index].target = {
                        edgeId: edge.id,
                        targetNodeId: edge.target,
                    };
                }
            }
        });
    }
}

function handleClickDelete() {
    removeNodes(node.id);
}

function handleClickDuplicate() {
    const { type, position, data } = node.node;

    const newNode = {
        id: (nodes.value.length + 1).toString(),
        type,
        position: {
            x: position.x - 100,
            y: position.y - 100,
        },
        data: JSON.parse(JSON.stringify(data)), // Deep copy to prevent shared reference
    };

    addNodes(newNode);
}

function removeButton(index) {
    const nodeId = props.id;
    const handleId = `button-${index}`;

    const allEdges = toObject().edges;

    //  Find and remove edges related to the button being removed
    const edgesToRemove = allEdges.filter(
        (edge) => edge.source === nodeId && edge.sourceHandle === handleId
    );

    if (edgesToRemove.length > 0) {
        // Remove each matching edge by ID
        edgesToRemove.forEach((edge) => removeEdges(edge.id));
    }

    //  Remove the button
    buttons.value.splice(index, 1);

    // (their indexes may have shifted, so we recalculate everything)
    const updatedEdges = toObject().edges;

    buttons.value.forEach((btn, i) => {
        btn.target = null; // reset first
        const edge = updatedEdges.find(
            (edge) =>
                edge.source === nodeId && edge.sourceHandle === `button-${i}`
        );
        if (edge) {
            btn.target = {
                edgeId: edge.id,
                targetNodeId: edge.target,
            };
        }
    });

    updateNodeData();
    checkButtonConnections();
}

function handleMessageInput() {
    // Automatically trim to 1024 characters if exceeded
    if (message.value.length > 1024) {
        message.value = message.value.substring(0, 1024);
    }
    updateNodeData();
}

function updateNodeData() {
    // Update the output in whatsmark format
    const newOutput = {
        reply_text: message.value,
    };

    // Add buttons (up to 3)
    buttons.value.forEach((button, index) => {
        if (index < 3) {
            newOutput[`button${index + 1}`] = button.text;
        }
    });

    // Update the data
    props.data.output = [newOutput];

    // Validation
    validateNode();
}

function toggleExpand() {
    isExpanded.value = !isExpanded.value;
}

const buttonValidation = computed(() => {
    const validations = buttons.value.map((button) => {
        return {
            id: button.id,
            textValid:
                button.text.trim().length > 0 && button.text.length <= 20,
            valueValid: button.value.trim().length > 0,
        };
    });

    return {
        items: validations,
        allValid: validations.every((v) => v.textValid && v.valueValid),
        hasButtons: buttons.value.length > 0,
    };
});

const messageValidation = computed(() => {
    return {
        hasContent: message.value.trim().length > 0,
        isValidLength: message.value.length <= 1024,
        isValid: message.value.trim().length > 0 && message.value.length <= 1024,
        currentLength: message.value.length
    };
});

// Check which buttons are not connected
function checkButtonConnections() {
    // Get all edges
    const edges = toObject().edges;

    // Reset the unconnected buttons
    unconnectedButtons.value = [];

    // Check each button for a connection
    buttons.value.forEach((button, index) => {
        const sourceHandleId = `button-${index}`;
        const isConnected = edges.some(
            (edge) =>
                edge.source === props.id && edge.sourceHandle === sourceHandleId
        );

        if (!isConnected) {
            unconnectedButtons.value.push(index);
        }
    });

    // Update validation
    validateNode();
}

// Updated isValid to include button connection check
const isValid = computed(() => {
    const formValid =
        messageValidation.value.isValid &&
        buttonValidation.value.hasButtons &&
        buttonValidation.value.allValid;
    const connectionsValid = unconnectedButtons.value.length === 0;

    return formValid && connectionsValid;
});

const canAddMoreButtons = computed(() => {
    return buttons.value.length < MAX_BUTTONS;
});

// Validation function to update node data and emit validation status
function validateNode() {
    // Update isValid status in node data
    props.data.isValid = isValid.value;

    // Emit validation status to parent
    emit("update:isValid", isValid.value);
}

// Replace the existing setupEdgeWatcher function with this:
function setupEdgeWatcher() {
    const { edges } = useVueFlow();

    watch(
        edges,
        () => {
            checkButtonConnections();
        },
        { deep: true }
    );
}

onMounted(() => {
    // Initialize buttons from whatsmark format
    if (output.value.button1) {
        buttons.value.push({
            id: Date.now(),
            text: output.value.button1,
            value: output.value.button1,
        });
    }
    if (output.value.button2) {
        buttons.value.push({
            id: Date.now() + 1,
            text: output.value.button2,
            value: output.value.button2,
        });
    }
    if (output.value.button3) {
        buttons.value.push({
            id: Date.now() + 2,
            text: output.value.button3,
            value: output.value.button3,
        });
    }

    // If no buttons exist, create empty button
    if (buttons.value.length === 0) {
        buttons.value.push({ id: Date.now(), text: "", value: "" });
    }

    findRelationTypeFromSource();
    setupEdgeWatcher();
    checkButtonConnections();
    validateNode();
});

// Run validation whenever isValid changes
watch(isValid, (newValue) => {
    validateNode();
});
</script>
<template>
    <div class="h-full w-full">
        <div
            class="h-full w-full bg-white p-0 shadow-lg transition-all duration-200 hover:shadow-xl dark:bg-gray-800"
            :class="[
                'overflow-hidden rounded-lg',
                selected
                    ? 'ring-2 ring-info-500'
                    : 'ring-1 ring-gray-200 dark:ring-gray-700',
                !isValid ? 'shadow-danger-100 ring-danger-300' : '',
            ]"
        >
            <!-- Top gradient bar -->
            <div
                :class="[
                    'node-type-indicator h-1.5 rounded-t-md bg-gradient-to-r',
                    isValid
                        ? 'from-info-500 to-cyan-600'
                        : 'from-danger-500 to-orange-500',
                ]"
            ></div>

            <Handle
                type="target"
                position="left"
                :class="[
                    '!h-4 !w-4 !border-2 !border-white !bg-gradient-to-r !shadow-md !transition-transform !duration-300',
                    isValid
                        ? '!from-info-500 !to-cyan-600'
                        : '!from-danger-500 !to-orange-500',
                ]"
            />

            <div class="node-container p-4">
                <!-- Node Header -->
                <div class="node-header mb-3 flex items-center justify-between">
                    <div class="node-title flex items-center">
                        <div
                            :class="[
                                'node-icon mr-3 rounded-lg p-2 shadow-sm',
                                isValid
                                    ? 'bg-info-100 text-info-600 dark:bg-info-900/50 dark:text-info-300'
                                    : 'bg-danger-100 text-danger-600 dark:bg-danger-900/50 dark:text-danger-300',
                            ]"
                            class="node-icon mr-3 rounded-lg shadow-sm"
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
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="2"
                                    ry="2"
                                ></rect>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                                <line x1="12" y1="16" x2="12" y2="16.01"></line>
                            </svg>
                        </div>
                        <span
                            class="text-sm font-medium text-gray-800 dark:text-gray-200"
                            >{{ data.label || "Button Message" }}</span
                        >
                    </div>

                    <div class="node-actions flex space-x-1">
                        <button
                            @click="toggleExpand"
                            class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-200 hover:bg-gray-50 hover:text-info-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-info-400"
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

                <!-- Node Content -->
                <div v-show="isExpanded" class="node-content space-y-5">
                    <div class="mb-3">
                        <label
                            class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-700 dark:text-gray-300"
                        >
                            Message Text
                            <span
                                v-if="!messageValidation.isValid"
                                class="ml-1 text-xs font-medium text-danger-500"
                                >(Required)</span
                            >
                        </label>
                        <textarea
                            v-model="message"
                            @input="handleMessageInput"
                            @paste="setTimeout(handleMessageInput, 0)"
                            @focus="handleTributeEvent"
                            class="mentionable block w-full resize-none rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-info-500 focus:ring focus:ring-info-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-info-500 dark:focus:ring-info-500/30"
                            :class="{
                                'border-danger-300 dark:border-danger-700':
                                    !messageValidation.isValid,
                            }"
                            placeholder="Enter message text here..."
                            rows="4"
                            maxlength="1024"
                        ></textarea>
                        <div class="mt-1.5 flex justify-between text-xs">
                            <span
                                :class="
                                    messageValidation.currentLength >= 1024
                                        ? 'text-warning-500'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                            >
                                {{ messageValidation.currentLength }}/1024
                                <span v-if="messageValidation.currentLength >= 1024" class="ml-1 text-warning-600">(Max reached)</span>
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div
                            class="flex items-center justify-between text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            <div class="flex items-center">
                                Buttons
                                <span
                                    v-if="!buttonValidation.hasButtons"
                                    class="ml-1 text-xs font-medium text-danger-500"
                                    >(Required)</span
                                >
                            </div>
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400"
                                >{{ buttons.length }}/{{ MAX_BUTTONS }}</span
                            >
                        </div>

                        <div
                            v-for="(button, index) in buttons"
                            :key="button.id"
                            :data-handleid="`button-${button.id}`"
                            :data-nodeid="props.id"
                            class="mb-2 rounded-md border border-gray-200 p-3 shadow-sm transition-all duration-200 hover:shadow dark:border-gray-700"
                            :class="{
                                'border-danger-300 bg-danger-50 dark:border-danger-800 dark:bg-danger-900/30':
                                    !buttonValidation.items[index]?.textValid ||
                                    !buttonValidation.items[index]
                                        ?.valueValid ||
                                    unconnectedButtons.includes(index),
                            }"
                        >
                            <div
                                class="mb-2.5 flex items-center justify-between"
                            >
                                <span
                                    class="text-xs font-semibold text-gray-700 dark:text-gray-300"
                                    >Button {{ index + 1 }}</span
                                >
                                <button
                                    @click="removeButton(index)"
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 text-danger-500 transition-colors hover:bg-danger-100 hover:text-danger-700 dark:bg-gray-700 dark:text-danger-400 dark:hover:bg-danger-900/50 dark:hover:text-danger-300"
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

                            <div class="mb-2.5">
                                <label
                                    class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400"
                                >
                                    Button Text
                                    <span class="text-danger-500">*</span>
                                </label>
                                <input
                                    v-model="button.text"
                                    @input="updateNodeData"
                                    class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-info-500 focus:ring focus:ring-info-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    :class="{
                                        'border-danger-300 dark:border-danger-700':
                                            !buttonValidation.items[index]
                                                ?.textValid,
                                    }"
                                    placeholder="Button text (max 20 chars)"
                                    maxlength="20"
                                />
                                <span
                                    v-if="button.text.length > 0"
                                    class="mt-1 block text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ button.text.length }}/20
                                </span>
                            </div>

                            <div>
                                <label
                                    class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400"
                                >
                                    Value <span class="text-danger-500">*</span>
                                </label>
                                <input
                                    v-model="button.value"
                                    @input="updateNodeData"
                                    class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-info-500 focus:ring focus:ring-info-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    :class="{
                                        'border-danger-300 dark:border-danger-700':
                                            !buttonValidation.items[index]
                                                ?.valueValid,
                                    }"
                                    placeholder="Payload value"
                                />
                            </div>

                            <!-- Connection point for this button -->
                            <div class="relative mt-3 h-5">
                                <Handle
                                    :id="`button-${index}`"
                                    type="source"
                                    position="right"
                                    :style="`right: 0; top: 50%; transform: translateY(-50%)`"
                                    :class="[
                                        '!h-3 !w-3 !border-2 !border-white !bg-gradient-to-r !shadow-md !transition-transform !duration-300 dark:!border-gray-800',
                                        unconnectedButtons.includes(index)
                                            ? '!animate-pulse !from-danger-500 !to-orange-500'
                                            : '!from-info-500 !to-cyan-500',
                                    ]"
                                />
                                <div
                                    class="absolute right-4 top-0 flex justify-between text-xs text-gray-500 dark:text-gray-400"
                                >
                                    <div>Button {{ index + 1 }} response</div>
                                    <div
                                        v-if="
                                            unconnectedButtons.includes(index)
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
                        @click="addButton"
                        class="mt-3 w-full rounded-md bg-info-50 px-3 py-2 text-sm font-medium text-info-600 transition-colors hover:bg-info-100 focus:outline-none focus:ring-2 focus:ring-info-500 focus:ring-opacity-50 dark:bg-info-900/30 dark:text-info-400 dark:hover:bg-info-900/50"
                        :disabled="!canAddMoreButtons"
                        :class="{
                            'cursor-not-allowed opacity-50': !canAddMoreButtons,
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
                            Add Button {{ canAddMoreButtons ? "" : "(Max 3)" }}
                        </span>
                    </button>

                    <!-- Validation warning -->
                    <div
                        v-if="!isValid"
                        class="mt-3 rounded-md border border-danger-200 bg-danger-50 p-3 text-sm text-danger-600 dark:border-danger-800/50 dark:bg-danger-900/30 dark:text-danger-400"
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
                                <ul class="mt-1 list-inside list-disc">
                                    <li v-if="!messageValidation.hasContent">
                                        Message text is required
                                    </li>
                                    <li v-if="messageValidation.hasContent && !messageValidation.isValidLength">
                                        Message text exceeds 1024 characters
                                    </li>
                                    <li v-if="!buttonValidation.hasButtons">
                                        At least one button is required
                                    </li>
                                    <li
                                        v-if="
                                            buttonValidation.hasButtons &&
                                            !buttonValidation.allValid
                                        "
                                    >
                                        All buttons must have text and value
                                    </li>
                                    <li v-if="unconnectedButtons.length > 0">
                                        All buttons must be connected to another
                                        node
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
