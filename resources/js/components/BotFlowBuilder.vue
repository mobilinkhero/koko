<!-- resources/js/components/BotFlowBuilder.vue -->
<script setup>
import {
    ref,
    reactive,
    onMounted,
    onBeforeUnmount,
    computed,
    watch,
    markRaw,
} from "vue";
import {
    VueFlow,
    useVueFlow,
    MarkerType,
    ConnectionMode,
} from "@vue-flow/core";
import { MiniMap } from "@vue-flow/minimap";
import { Controls } from "@vue-flow/controls";
import { Background } from "@vue-flow/background";
import "@vue-flow/core/dist/style.css";
import "@vue-flow/core/dist/theme-default.css";
import "@vue-flow/controls/dist/style.css";
import "@vue-flow/minimap/dist/style.css";

import {
  MdFullscreen,
  MdFullscreenExit,
  ClSave,
  LuRefreshCw,
  BsMenuButtonFill,
  CaHttp,
  FlFilledLightbulbFilament,
  FlContactCard,
  CaLocationPerson,
  MdOutlinedPermMedia,
  CdChecklist,
  BsChatRightQuote,
  AkArrowLeft,
  AkArrowRight,
  AkLinkOn,
  // Phase 1 New Node Icons
  BsGearFill,
  MdAccessTime,
  HiDocumentText
} from "@kalimahapps/vue-icons";


// Import custom node types
import TextMessageNode from "./nodes/TextMessageNode.vue";
import ButtonMessageNode from "./nodes/ButtonMessageNode.vue";
import CallToActionNode from "./nodes/CallToActionNode.vue";
import TriggerNode from "./nodes/TriggerNode.vue";
import ListMessageNode from "./nodes/ListMessageNode.vue";
import MediaMessageNode from "./nodes/MediaMessageNode.vue";
import LocationMessageNode from "./nodes/LocationMessageNode.vue";
import ContactMessageNode from "./nodes/ContactMessageNode.vue";
import AIAssistantNode from "./nodes/AIAssistantNode.vue";
import WebhookApiNode from "./nodes/WebhookApi.vue";
// Phase 1 New Nodes
import ConditionNode from "./nodes/ConditionNode.vue";
import DelayNode from "./nodes/DelayNode.vue";
import InputCollectionNode from "./nodes/InputCollectionNode.vue";
// Phase 2 New Nodes
import TagManagementNode from "./nodes/TagManagementNode.vue";
import VariableManagementNode from "./nodes/VariableManagementNode.vue";
// Custom edge
import CustomEdge from "./ui/CustomEdge.vue";

// Define node types mapping
const nodeTypes = markRaw({
    textMessage: TextMessageNode,
    buttonMessage: ButtonMessageNode,
    callToAction: CallToActionNode,
    trigger: TriggerNode,
    listMessage: ListMessageNode,
    mediaMessage: MediaMessageNode,
    locationMessage: LocationMessageNode,
    contactMessage: ContactMessageNode,
    aiAssistant: AIAssistantNode,
    webhookApi: WebhookApiNode,
    // Phase 1 New Nodes
    condition: ConditionNode,
    delay: DelayNode,
    inputCollection: InputCollectionNode,
    tagManagement: TagManagementNode,
    variableManagement: VariableManagementNode,
});

// Custom edge types
const edgeTypes = {
    button: markRaw(CustomEdge),
};

// Default edge options
const defaultEdgeOptions = {
    type: 'button',
    animated: true,
    markerEnd: {
        type: MarkerType.ArrowClosed,
        width: 20,
        height: 20,
    },
};

// Flow metadata
const flowId = ref(null);
let flowData = ref([]);
const isLoading = ref(false);
const errorMessage = ref("");
const sidebarCollapsed = ref(false);
const isDragging = ref(false);
const isFullscreen = ref(false);
const { toObject } = useVueFlow();
const flow_create_permission = ref(window.flow_create_permission === true);
const aiAssistantEnabled = ref(window.isAiAssistantModuleEnabled ?? false);

// Initial flow state
var initialNodes = ref([
    {
        id: "1", // Change from "trigger-1" to "1"
        type: "trigger", // Change from "trigger" to "start"
        initialized: false,
        position: { x: 250, y: 0 },
        data: {
            output: [
                {
                    // Change to match WhatsMark SaaS's output array pattern
                    reply_type_text: "",
                    reply_type: "",
                    rel_type: "",
                    trigger: "",
                },
            ],
        },
        label: "Start Trigger",
    },
]);
const initialEdges = ref([]);
const isFlowValid = ref(false);

// Use Vue Flow composable
const { addEdges, onConnect, addNodes, setNodes, setEdges, getNodes, getEdges } = useVueFlow();

// Watch for node and edge changes to update flow validity
watch([initialNodes, initialEdges], () => {
    updateFlowValidity();
}, { deep: true });

// Toggle sidebar collapse
function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem("flow-sidebar-collapsed", sidebarCollapsed.value);
}

// Enhanced node drag handlers
function onNodeDragStart(event, node) {
    isDragging.value = true;

    // Use initialNodes.value instead of getNodes()
    if (node && node.id) {
        const draggedNode = initialNodes.value.find((n) => n.id === node.id);
        if (draggedNode) {
            draggedNode.zIndex = 1000;
        }
    }
}

// Handle node drag stop - save position
function handleNodeDragStop(event, node) {
    isDragging.value = false;

    // Use initialNodes.value instead of getNodes()
    if (node && node.id) {
        const draggedNode = initialNodes.value.find((n) => n.id === node.id);
        if (draggedNode) {
            draggedNode.zIndex = 0;
        }
    }
}

// Handle node connections
onConnect((params) => {
    console.log('Connection attempt:', params);
    const newEdge = {
        ...params,
        id: `e${params.source}-${params.target}`,
        animated: true,
        type: "button",
        markerEnd: {
            type: MarkerType.ArrowClosed,
            width: 20,
            height: 20,
        },
    };
    addEdges(newEdge);
    console.log('Edge added:', newEdge);
});

// Node templates for adding new nodes
const nodeTemplates = reactive([
    { type: "textMessage", label: "Text Message", icon: BsChatRightQuote },
    { type: "buttonMessage", label: "Button Message", icon: BsMenuButtonFill },
    { type: "callToAction", label: "Call To Action", icon: AkLinkOn },
    { type: "listMessage", label: "List Message", icon: CdChecklist },
    { type: "mediaMessage", label: "Media Message", icon: MdOutlinedPermMedia },
    {
        type: "locationMessage",
        label: "Location",
        icon: CaLocationPerson,
    },
    {
        type: "contactMessage",
        label: "Contact Card",
        icon: FlContactCard,
    },
    {
        type: "aiAssistant",
        label: "AI Personal Assistant",
        icon: FlFilledLightbulbFilament,
    },
    {
        type: "webhookApi",
        label: "API Request",
        icon: CaHttp,
    },
    // Phase 1 New Nodes
    {
        type: "condition",
        label: "Condition",
        icon: BsGearFill,
    },
    {
        type: "delay",
        label: "Delay",
        icon: MdAccessTime,
    },
    {
        type: "inputCollection",
        label: "Input Collection",
        icon: HiDocumentText,
    },
    // Phase 2 New Nodes
    {
        type: "tagManagement",
        label: "Tag Management",
        icon: BsGearFill,
    },
    {
        type: "variableManagement",
        label: "Variable Manager",
        icon: HiDocumentText,
    },
]);

// Group node templates by category
const nodeCategories = computed(() => {
    return {
        "Basic Messages": ["textMessage", "buttonMessage", "callToAction"],
        "Interactive Content": [
            "listMessage",
            "mediaMessage",
            "locationMessage",
            "contactMessage",
        ],
        "Flow Control": ["condition", "delay", "inputCollection"],
        "Data & Variables": ["variableManagement", "tagManagement"],
        "Advanced Features": aiAssistantEnabled.value
            ? ["aiAssistant", "webhookApi"]
            : ["webhookApi"],
    };
});

// Add a new node to the flow - with drag and drop enhanced

// Function to handle drag start when dragging from palette
function handleOnDragStart(event, type) {
    event.dataTransfer.setData("application/vueflow", type);
    event.dataTransfer.effectAllowed = "move";
}

// Function to handle dropping a node from the palette onto the canvas
function onDrop(event) {
    const type = event.dataTransfer.getData("application/vueflow");
    if (!type) return;

    // Get position relative to the pane
    const { left, top } = document
        .querySelector(".vue-flow__transformationpane")
        .getBoundingClientRect();
    const position = {
        x: event.clientX - left,
        y: event.clientY - top,
    };

    // Create the node based on the type and position
    addNodeAtPosition(type, position);
}

// Add a node at a specific position (used for drag and drop)
function addNodeAtPosition(type, position) {
    const newNodeId = Date.now().toString(); // Use simple numeric IDs like WhatsMarkSaaS

    // Default node data with output array structure
    let nodeData = {
        output: [],
    };

    // Configure the node data based on type
    switch (type) {
        case "textMessage":
            nodeData = {
                output: [{ reply_text: "" }],
            };
            break;
        case "buttonMessage": // Change to buttonsMessage to match WhatsMarkSaaS
            nodeData = {
                output: [
                    {
                        reply_text: "",
                        button1: "",
                        button2: "",
                        button3: "",
                        bot_header: "header",
                        bot_footer: "footer",
                    },
                ],
            };
            break;
        case "callToAction":
            nodeData = {
                output: [
                    {
                        header: "",
                        valueText: "",
                        buttonText: "Click Here",
                        buttonLink: "",
                        footer: "",
                    },
                ],
            };
            break;
        case "webhookApi":
            nodeData = {
                output: [
                    {
                        requestUrl: "",
                        requestMethod: "GET",
                        requestFormat: "JSON",
                        requestHeaders: [{ name: "", value: "" }],
                        requestBody: [{ key: "", value: "" }],
                    },
                ],
            };
            break;
        case "condition":
            nodeData = {
                conditions: [
                    {
                        variable: '',
                        customVariable: '',
                        operator: 'equals',
                        value: '',
                        logic: 'AND'
                    }
                ],
                defaultAction: 'continue',
                isValid: true
            };
            break;
        case "delay":
            nodeData = {
                delayType: 'fixed',
                duration: 3,
                unit: 'seconds',
                showTyping: true,
                isValid: true
            };
            break;
        case "inputCollection":
            nodeData = {
                message: 'Please provide the following information:',
                fields: [
                    {
                        type: 'text',
                        variable: 'user_name',
                        label: 'What is your name?',
                        required: true,
                        minLength: 2,
                        errorMessage: 'Please enter a valid name',
                        options: ''
                    }
                ],
                collectionMode: 'sequential',
                maxRetries: 3,
                skipOnError: false,
                isValid: true
            };
            break;
        case "tagManagement":
            nodeData = {
                action: 'add',
                tags: [{ name: '', color: 'blue' }],
                conditions: [],
                createIfNotExist: true,
                logActivity: true,
                isValid: true
            };
            break;
        case "variableManagement":
            nodeData = {
                action: 'set',
                variableName: '',
                value: '',
                scope: 'contact',
                expirationValue: 0,
                expirationUnit: 'never',
                encrypt: false,
                logChanges: true,
                isValid: true
            };
            break;
        // Update other node types similarly...
    }

    // Add the node with WhatsMarkSaaS structure
    addNodes([
        {
            id: newNodeId,
            type: type === "buttonMessage" ? "buttonMessage" : type, // Map to WhatsMarkSaaS naming
            data: nodeData,
            position,
            initialized: false,
        },
    ]);
}

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        sidebarCollapsed.value = true;
        localStorage.setItem("flow-sidebar-collapsed", "true"); // collapsed in fullscreen
    } else {
        document.exitFullscreen();
        sidebarCollapsed.value = false;
        localStorage.setItem("flow-sidebar-collapsed", "false"); // expanded on exit
    }
};

const onFullscreenChange = () => {
    isFullscreen.value = !!document.fullscreenElement;
    sidebarCollapsed.value = isFullscreen.value;
    localStorage.setItem(
        "flow-sidebar-collapsed",
        isFullscreen.value ? "true" : "false"
    );
};

onMounted(() => {
    document.addEventListener("fullscreenchange", onFullscreenChange);
    const stored = localStorage.getItem("flow-sidebar-collapsed");
    sidebarCollapsed.value = stored === "true"; // stored is a string
});

onBeforeUnmount(() => {
    document.removeEventListener("fullscreenchange", onFullscreenChange);
});

function hasUnconnectedNodes(nodes, edges) {
    const connectedNodes = new Set();

    edges.forEach((edge) => {
        connectedNodes.add(edge.source);
        connectedNodes.add(edge.target);
    });

    const unconnectedNodes = nodes.filter(
        (node) => !connectedNodes.has(node.id)
    );

    if (unconnectedNodes.length > 0) {
        showNotification("All nodes must be connected!", "danger");
        return true;
    }

    return false;
}

const validateWorkflow = () => {
    const flowData = toObject(); // Convert flow to object
    const nodes = flowData.nodes || [];
    const edges = flowData.edges || [];

    if (nodes.length === 0) {
        return false;
    }

    // Check if all nodes are valid (including required fields)
    const invalidNodes = nodes.filter((node) => {
        return (
            (node.type === "textMessage" && node.data.isValid === false) ||
            (node.type === "trigger" && node.data.isValid === false) ||
            (node.type === "callToAction" && node.data.isValid === false) ||
            (node.type === "buttonMessage" && node.data.isValid === false) ||
            (node.type === "listMessage" && node.data.isValid === false) ||
            (node.type === "locationMessage" && node.data.isValid === false) ||
            (node.type === "contactMessage" && node.data.isValid === false) ||
            (node.type === "mediaMessage" && node.data.isValid === false) ||
            (node.type === "aiAssistant" && node.data.isValid === false) ||
            (node.type === "webhookApi" && node.data.isValid === false) ||
            (node.type === "condition" && node.data.isValid === false) ||
            (node.type === "delay" && node.data.isValid === false) ||
            (node.type === "inputCollection" && node.data.isValid === false) ||
            (node.type === "tagManagement" && node.data.isValid === false) ||
            (node.type === "variableManagement" && node.data.isValid === false)
        );
    });

    if (invalidNodes.length > 0) {
        return false;
    }

    return true;
};

function updateFlowValidity() {
    isFlowValid.value = validateWorkflow();
}
// Save flow configuration
function saveFlow() {
    // IMPORTANT: Sync AI Assistant nodes from initialNodes to Vue Flow before saving
    // This ensures all data updates are captured
    const aiAssistantNodes = initialNodes.value.filter(n => n.type === 'aiAssistant');
    if (aiAssistantNodes.length > 0) {
        aiAssistantNodes.forEach(node => {
            // Ensure required properties are present
            if (!node.data.assistantMode) {
                node.data.assistantMode = 'personal';
            }
            if (!node.data.selectedAssistantId && window.personalAssistantsList && window.personalAssistantsList.length > 0) {
                node.data.selectedAssistantId = window.personalAssistantsList[0].id;
            }
            if (!node.data.output) {
                node.data.output = [];
            }
        });
    }
    
    const flowDataObj = toObject();
    const nodes = flowDataObj.nodes || [];
    const edges = flowDataObj.edges || [];

    if (hasUnconnectedNodes(nodes, edges)) {
        return; // Stop if unconnected nodes found
    }
    if (!validateWorkflow()) {
        return; // Stop if validation fails
    }

    // Debug: Log AI Assistant node data before saving
    const aiAssistantNodesInFlow = nodes.filter(n => n.type === 'aiAssistant');
    if (aiAssistantNodesInFlow.length > 0) {
        console.log('BotFlowBuilder - Saving flow with AI Assistant nodes:', aiAssistantNodesInFlow.map(n => ({
            id: n.id,
            data: n.data,
            hasSelectedAssistant: !!n.data?.selectedAssistantId,
            assistantMode: n.data?.assistantMode,
            dataKeys: Object.keys(n.data || {})
        })));
    }

    const allFlowData = JSON.stringify(flowDataObj);

    isLoading.value = true;
    errorMessage.value = "";
    const flowData = {
        id: flowId.value,
        flow_data: allFlowData,
    };

    fetch(`/${tenantSubdomain}/save-bot-flow`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN":
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") || "",
        },
        body: JSON.stringify(flowData),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                if (!flowId.value) {
                    flowId.value = data.flow_id;
                    // Update the URL without reloading the page
                    window.history.pushState(
                        {},
                        "",
                        `/admin/bot-flows/edit/${data.flow_id}`
                    );
                }
                showNotification("Flow saved successfully!", "success");

                // Dispatch event for parent components
                window.dispatchEvent(
                    new CustomEvent("flow:saved", {
                        detail: { success: true, flowId: data.flow_id },
                    })
                );
            } else {
                errorMessage.value = data.message || "Failed to save flow";
                showNotification(errorMessage.value, "error");
            }
        })
        .catch((error) => {
            console.error("Error saving flow:", error);
            errorMessage.value = "An error occurred while saving the flow";
            showNotification(errorMessage.value, "error");
        })
        .finally(() => {
            isLoading.value = false;
        });
}

// Show notification function
function showNotification(message, type = "info") {
    window.dispatchEvent(
        new CustomEvent("notify", {
            detail: {
                message,
                type,
            },
        })
    );
}
function handleTextNodeValidation(nodeId, isValid) {
    // Find the node in our nodes array
    const node = initialNodes.value.find((n) => n.id === nodeId);
    if (node) {
        // Update the node data with the validation state
        node.data.isValid = isValid;
        // Update the overall flow validation
        updateFlowValidity();
    }
}

// Handle node data updates
function handleNodeUpdate(payload) {
    const { id, data } = payload;
    
    // Find the node in our nodes array
    const node = initialNodes.value.find((n) => n.id === id);
    if (node) {
        // For AI Assistant nodes, ensure we preserve selectedAssistantId and assistantMode
        if (node.type === 'aiAssistant') {
            console.log('BotFlowBuilder - Updating AI Assistant node:', {
                nodeId: id,
                oldData: { ...node.data },
                newData: data
            });
            
            // IMPORTANT: Completely replace node.data with the new data to ensure all properties are saved
            // This ensures selectedAssistantId and assistantMode are always included
            node.data = {
                ...node.data,  // Preserve existing properties like output, dimensions, etc.
                ...data,       // Override with new data (selectedAssistantId, assistantMode, etc.)
            };
            
            // Explicitly ensure these properties are set
            if (data.selectedAssistantId !== undefined) {
                node.data.selectedAssistantId = data.selectedAssistantId;
            }
            if (data.assistantMode !== undefined) {
                node.data.assistantMode = data.assistantMode;
            }
            
            console.log('BotFlowBuilder - AI Assistant node data after update:', {
                nodeId: id,
                data: { ...node.data },
                hasSelectedAssistant: !!node.data.selectedAssistantId,
                assistantMode: node.data.assistantMode
            });
        } else {
            // Update the node data for other node types
            node.data = {
                ...node.data,
                ...data
            };
        }
        
        // Update the overall flow validation
        updateFlowValidity();
    }
}

// Watch for changes to save automatically
watch(
    () => ({
        nodes: initialNodes.value,
        edges: initialEdges.value,
    }),

    () => {
        if (flowId.value) {
        }
    },
    { deep: true }
);

// Load existing flow (if editing)
onMounted(() => {
    // Get flow ID from data attribute or URL parameter
    const flowBuilderElement = document.getElementById("bot-flow-builder");
    const dataFlowId = flowBuilderElement?.dataset.flowId;

    if (dataFlowId) {
        flowId.value = dataFlowId;
        isLoading.value = true;

        fetch(`/${tenantSubdomain}/get-bot-flow/${dataFlowId}`)
            .then((response) => response.json())
            .then((data) => {
                if (data.success && data.flow) {
                    // Process nodes with dimensions
                    if (data.flow.nodes && data.flow.nodes.length > 0) {
                        // Make sure all nodes have dimensions property
                        // Also upgrade old AI Assistant nodes to new format
                        const processedNodes = data.flow.nodes.map((node) => {
                            if (!node.data.dimensions) {
                                node.data.dimensions = {
                                    width: 280,
                                    height: 150,
                                };
                            }
                            
                            // Upgrade old AI Assistant nodes
                            if (node.type === 'aiAssistant') {
                                console.log('BotFlowBuilder - Upgrading old AI Assistant node:', {
                                    nodeId: node.id,
                                    oldData: { ...node.data }
                                });
                                
                                // Set assistantMode to 'personal' if not set or if it's 'custom'
                                if (!node.data.assistantMode || node.data.assistantMode === 'custom') {
                                    node.data.assistantMode = 'personal';
                                }
                                
                                // Auto-select first assistant if none selected and assistants are available
                                if (!node.data.selectedAssistantId && window.personalAssistantsList && window.personalAssistantsList.length > 0) {
                                    node.data.selectedAssistantId = window.personalAssistantsList[0].id;
                                    console.log('BotFlowBuilder - Auto-selected assistant:', node.data.selectedAssistantId);
                                }
                                
                                console.log('BotFlowBuilder - Upgraded AI Assistant node data:', {
                                    nodeId: node.id,
                                    newData: { ...node.data }
                                });
                            }
                            
                            return node;
                        });
                        setNodes(processedNodes);
                        flowData = data;
                        
                        // If any AI Assistant nodes were upgraded, save the flow automatically
                        const upgradedNodes = processedNodes.filter(n => n.type === 'aiAssistant' && n.data.assistantMode === 'personal');
                        if (upgradedNodes.length > 0) {
                            console.log('BotFlowBuilder - Auto-saving upgraded AI Assistant nodes...');
                            // Trigger save after a short delay to ensure nodes are set
                            setTimeout(() => {
                                saveFlow();
                            }, 1000);
                        }
                    }

                    // Process edges
                    if (data.flow.edges && data.flow.edges.length > 0) {
                        setEdges(data.flow.edges);
                    }
                } else {
                    setNodes([
                        {
                            id: "trigger-1",
                            type: "trigger",
                            data: {
                                label: "Start Trigger",
                                keywords: [],
                                dimensions: { width: 280, height: 150 },
                            },
                            position: { x: 250, y: 0 },
                            draggable: true,
                        },
                    ]);
                }
            })
            .catch((error) => {
                console.error("Error loading flow:", error);
                errorMessage.value = "Failed to load flow data";
                showNotification("Failed to load flow data", "error");
            })
            .finally(() => {
                isLoading.value = false;
                // Update flow validity after loading
                updateFlowValidity();
            });
    } else {
        // If no flow ID, validate the initial trigger node
        updateFlowValidity();
    }

    // Expose component methods to parent
    if (flowBuilderElement) {
        flowBuilderElement.__vue__ = {
            saveFlow,
        };
    }
});

function getReplyTypeText(type) {
    const typeMap = {
        1: "On exact match",
        2: "When message contains",
        3: "When lead or client send the first message",
        4: "If any keyword does not match",
    };
    return typeMap[type] || "When message contains";
}
</script>

<template>
    <div
        class="bot-flow-builder-container flex h-[calc(100vh_-_114px)] flex-col"
    >
        <div
            v-if="errorMessage"
            class="mb-4 rounded-md bg-danger-100 p-3 text-danger-700"
        >
            {{ errorMessage }}
        </div>

        <div
            class="flex h-full overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
        >
            <!-- Left Sidebar: Component Palette -->
            <div
                :class="[
                    'component-sidebar border-r border-gray-200 bg-white transition-all duration-300 ease-in-out dark:border-gray-700 dark:bg-gray-900',
                    sidebarCollapsed ? 'w-18' : 'w-64',
                ]"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900"
                >
                    <h3
                        :class="[
                            'font-medium text-gray-700 dark:text-gray-200',
                            sidebarCollapsed ? 'hidden' : 'block',
                        ]"
                    >
                        Available Components
                    </h3>
                    <button
                        @click="toggleSidebar"
                        class="rounded p-1 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
                        :title="
                            sidebarCollapsed
                                ? 'Expand sidebar'
                                : 'Collapse sidebar'
                        "
                    >
                        <AkArrowLeft v-if="!sidebarCollapsed" class="h-5 w-5" />
                        <AkArrowRight v-else class="h-5 w-5" />
                    </button>
                </div>

                <div
                    class="h-[calc(100vh_-_160px)] overflow-y-auto p-3 dark:border-gray-700 dark:bg-gray-900"
                >
                    <template
                        v-for="(types, category) in nodeCategories"
                        :key="category"
                    >
                        <template v-if="types.length > 0">
                            <div
                                v-if="!sidebarCollapsed"
                                class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                {{ category }}
                            </div>

                            <div class="mb-4 space-y-2">
                                <div
                                    v-for="templateType in types"
                                    :key="templateType"
                                    class="cursor-grab rounded transition-colors duration-150 hover:bg-info-50 dark:hover:bg-gray-700"
                                    :class="[
                                        sidebarCollapsed ? 'p-2' : 'p-2',
                                        'border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800',
                                    ]"
                                    @dragstart="
                                        handleOnDragStart($event, templateType)
                                    "
                                    draggable="true"
                                >
                                    <div class="flex items-center">
                                        <div
                                            :data-tippy-content="
                                                nodeTemplates.find(
                                                    (t) =>
                                                        t.type === templateType
                                                )?.label
                                            "
                                            :class="[
                                                'flex items-center justify-center rounded bg-primary-50 text-primary-600 dark:bg-primary-800 dark:text-white',
                                                sidebarCollapsed
                                                    ? 'h-7 w-7'
                                                    : 'mr-3 h-7 w-7',
                                            ]"
                                        >
                                            <component
                                                :is="
                                                    nodeTemplates.find(
                                                        (t) =>
                                                            t.type ===
                                                            templateType
                                                    )?.icon ||
                                                    HiOutlineDocumentText
                                                "
                                                class="h-5 w-5"
                                            />
                                        </div>
                                        <span
                                            v-if="!sidebarCollapsed"
                                            class="text-sm text-gray-800 dark:text-gray-200"
                                        >
                                            {{
                                                nodeTemplates.find(
                                                    (t) =>
                                                        t.type === templateType
                                                )?.label
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </template>
                </div>
            </div>

            <!-- Flow Canvas Container -->
            <div class="flex flex-1 flex-col">
                <!-- Toolbar -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900"
                >
                    <div class="flex items-center space-x-2">
                        <button
                            @click="toggleFullscreen"
                            class="flex items-center space-x-1 rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                            :title="
                                isFullscreen
                                    ? 'Exit Fullscreen (ESC)'
                                    : 'Enter Fullscreen'
                            "
                        >
                            <MdFullscreenExit
                                v-if="isFullscreen"
                                class="h-4 w-4"
                            />
                            <MdFullscreen v-else class="h-4 w-4" />
                            <span>{{
                                isFullscreen ? "Exit Fullscreen" : "Fullscreen"
                            }}</span>
                        </button>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button
                            v-if="flow_create_permission"
                            @click="saveFlow"
                            :disabled="isLoading || !isFlowValid"
                            :class="[
                                'flex items-center space-x-1 rounded-md px-4 py-2 text-sm font-medium transition-colors duration-150',
                                isFlowValid
                                    ? 'bg-info-600 text-white hover:bg-info-700'
                                    : 'cursor-not-allowed bg-gray-300 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                            ]"
                        >
                            <ClSave class="h-4 w-4" v-if="!isLoading" />
                            <LuRefreshCw class="h-4 w-4 animate-spin" v-else />
                            <span v-if="isLoading">Saving...</span>
                            <span v-else>Save Flow</span>
                        </button>
                    </div>
                </div>

                <!-- Flow Canvas -->
                <div
                    class="relative h-[600px] flex-1 bg-gray-50 dark:bg-gray-900 dark:bg-opacity-80"
                >
                    <!-- Loading overlay -->
                    <div
                        v-if="isLoading"
                        class="absolute inset-0 z-10 flex items-center justify-center bg-white bg-opacity-70 dark:bg-gray-900 dark:bg-opacity-80"
                    >
                        <div class="text-center">
                            <svg
                                class="mx-auto h-8 w-8 animate-spin text-primary-600"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            <p class="mt-2 text-gray-700 dark:text-gray-200">
                                Loading flow...
                            </p>
                        </div>
                    </div>

                    <VueFlow
                        v-model="initialNodes"
                        v-model:edges="initialEdges"
                        :node-types="nodeTypes"
                        :edge-types="edgeTypes"
                        :default-edge-options="defaultEdgeOptions"
                        :default-zoom="1"
                        :min-zoom="0.2"
                        :max-zoom="4"
                        class="h-full"
                        :connection-mode="ConnectionMode.Loose"
                        :connectable="true"
                        :nodes-connectable="true"
                        @nodeDragStart="onNodeDragStart"
                        @nodeDragStop="handleNodeDragStop"
                        @drop="onDrop"
                        @dragover.prevent
                        @dragenter.prevent
                    >
                        <template #edge-button="buttonEdgeProps">
                            <CustomEdge
                                :id="buttonEdgeProps.id"
                                :source-x="buttonEdgeProps.sourceX"
                                :source-y="buttonEdgeProps.sourceY"
                                :target-x="buttonEdgeProps.targetX"
                                :target-y="buttonEdgeProps.targetY"
                                :source-position="
                                    buttonEdgeProps.sourcePosition
                                "
                                :target-position="
                                    buttonEdgeProps.targetPosition
                                "
                                :marker-end="buttonEdgeProps.markerEnd"
                                :style="buttonEdgeProps.style"
                            />
                        </template>
                        <Background pattern-color="#bb9fd1" gap="16" size="1" />
                        <MiniMap
                            class="custom-minimap"
                            pannable
                            zoomable
                            nodeColor="#1e88e5"
                            nodeStrokeColor="#408B9A"
                            nodeClassName="custom-node-class"
                        />
                        <Controls />

                        <template #node-textMessage="nodeProps">
                            <TextMessageNode
                                v-bind="nodeProps"
                                @update-node="handleNodeUpdate"
                                @update:isValid="
                                    handleTextNodeValidation(
                                        nodeProps.id,
                                        $event
                                    )
                                "
                            />
                        </template>

                        <template #node-buttonMessage="nodeProps">
                            <ButtonMessageNode
                                v-bind="nodeProps"
                                @update:isValid="
                                    handleTextNodeValidation(
                                        nodeProps.id,
                                        $event
                                    )
                                "
                            />
                        </template>

                        <template #node-callToAction="nodeProps">
                            <CallToActionNode
                                v-bind="nodeProps"
                                @update:isValid="
                                    handleTextNodeValidation(
                                        nodeProps.id,
                                        $event
                                    )
                                "
                            />
                        </template>

                        <template #node-trigger="nodeProps">
                            <TriggerNode
                                v-bind="nodeProps"
                                @update-node="handleNodeUpdate"
                                @update:isValid="
                                    handleTextNodeValidation(
                                        nodeProps.id,
                                        $event
                                    )
                                "
                            />
                        </template>

                        <template #node-listMessage="nodeProps">
                            <ListMessageNode
                                v-bind="nodeProps"
                                @update:isValid="
                                    handleTextNodeValidation(
                                        nodeProps.id,
                                        $event
                                    )
                                "
                            />
                        </template>

                        <template #node-mediaMessage="nodeProps">
                            <MediaMessageNode
                                v-bind="nodeProps"
                                @update-node="handleNodeUpdate"
                                @update:isValid="
                                    handleTextNodeValidation(
                                        nodeProps.id,
                                        $event
                                    )
                                "
                            />
                        </template>

                        <template #node-locationMessage="nodeProps">
                            <LocationMessageNode
                                v-bind="nodeProps"
                                @update:isValid="
                                    handleTextNodeValidation(
                                        nodeProps.id,
                                        $event
                                    )
                                "
                            />
                        </template>

                        <template #node-contactMessage="nodeProps">
                            <ContactMessageNode
                                v-bind="nodeProps"
                                @update:isValid="
                                    handleTextNodeValidation(
                                        nodeProps.id,
                                        $event
                                    )
                                "
                            />
                        </template>
                        <template
                            #node-aiAssistant="nodeProps"
                            v-if="aiAssistantEnabled.value"
                        >
                            <AIAssistantNode
                                v-bind="nodeProps"
                                @update:isValid="
                                    handleTextNodeValidation(
                                        nodeProps.id,
                                        $event
                                    )
                                "
                            />
                        </template>

                        <template #node-webhookApi="nodeProps">
                            <WebhookApiNode
                                v-bind="nodeProps"
                                @update:isValid="
                                    handleTextNodeValidation(
                                        nodeProps.id,
                                        $event
                                    )
                                "
                            />
                        </template>

                        <!-- Phase 2 Node Templates -->

                        <template #node-tagManagement="nodeProps">
                            <TagManagementNode
                                v-bind="nodeProps"
                                @update-node="handleNodeUpdate"
                                @update:isValid="
                                    handleTextNodeValidation(
                                        nodeProps.id,
                                        $event
                                    )
                                "
                            />
                        </template>

                        <template #node-variableManagement="nodeProps">
                            <VariableManagementNode
                                v-bind="nodeProps"
                                @update-node="handleNodeUpdate"
                                @update:isValid="
                                    handleTextNodeValidation(
                                        nodeProps.id,
                                        $event
                                    )
                                "
                            />
                        </template>
                    </VueFlow>
                </div>
            </div>
        </div>
    </div>
</template>
