<script setup>
import { ref, computed, onMounted, watch, onBeforeUnmount } from "vue";
import { Handle, useVueFlow, useNode } from "@vue-flow/core";
const { removeNodes, nodes, addNodes } = useVueFlow();
const props = defineProps({
    id: { type: String, required: true },
    data: { type: Object, required: true },
    selected: { type: Boolean, default: false },
});

const output = ref(
    props.data.output?.[0] || {
        location_latitude: "",
        location_longitude: "",
        location_name: "",
        location_address: "",
    }
);
const latitude = ref(output.value.location_latitude || "");
const longitude = ref(output.value.location_longitude || "");
const name = ref(output.value.location_name || "");
const address = ref(output.value.location_address || "");

// Rest of your variables
const isExpanded = ref(true);
const mapInitialized = ref(false);
let map = ref(null);
let marker = ref(null);
const searchQuery = ref("");
const searchResults = ref([]);
const isSearching = ref(false);
const node = useNode();

// Add emit to your props
const emit = defineEmits(["update:isValid"]);

// Validation states
// Use these computed properties for validation
const isValidLatitude = computed(() => {
    if (!latitude.value) return false;
    const lat = parseFloat(latitude.value);
    return !isNaN(lat) && lat >= -90 && lat <= 90;
});

const isValidLongitude = computed(() => {
    if (!longitude.value) return false;
    const lng = parseFloat(longitude.value);
    return !isNaN(lng) && lng >= -180 && lng <= 180;
});
function validateCoordinates(value) {
    props.data.isValid = isValid.value;

    // Add validation messages to data
    props.data.validationMessages = {
        latitude: !isValidLatitude.value
            ? "Please enter a valid latitude (-90 to 90)"
            : null,
        longitude: !isValidLongitude.value
            ? "Please enter a valid longitude (-180 to 180)"
            : null,
    };

    // Emit the validation status to parent
    emit("update:isValid", isValid.value);

    return isValid.value;
}
const isValid = computed(() => {
    return isValidLatitude.value && isValidLongitude.value;
});
const isValidLocation = computed(
    () => isValidLatitude.value && isValidLongitude.value
);

// Input handlers with automatic trimming
function handleNameInput() {
    if (name.value.length > 100) {
        name.value = name.value.substring(0, 100);
    }
    updateNodeData();
}

function handleAddressInput() {
    if (address.value.length > 200) {
        address.value = address.value.substring(0, 200);
    }
    updateNodeData();
}

function updateNodeData() {
    props.data.output = [
        {
            location_latitude: latitude.value,
            location_longitude: longitude.value,
            location_name: name.value,
            location_address: address.value,
        },
    ];

    validateCoordinates();
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
function toggleExpand() {
    isExpanded.value = !isExpanded.value;

    // Initialize map when expanded for the first time
    if (isExpanded.value && !mapInitialized.value) {
        initMap();
    }
}

function initMap() {
    if (typeof L === "undefined") {
        console.warn("Leaflet not available");
        return;
    }
    // 🧹 Clean up previously initialized map if it exists
    if (map.value) {
        map.value.remove(); // destroys the previous map instance
        map.value = null;
    }
    const lat = parseFloat(latitude.value) || 28.6139;
    const lon = parseFloat(longitude.value) || 77.209;

    map.value = L.map(`map-${props.id}`, {
        fullscreenControl: true,
        fullscreenControlOptions: {
            position: "topleft", // 'topright', 'bottomleft', 'bottomright'
        },
    }).setView([lat, lon], 13);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {}).addTo(
        map.value
    );

    marker.value = L.marker([lat, lon]).addTo(map.value);
}

async function searchLocation() {
    if (!searchQuery.value.trim()) return;

    isSearching.value = true;

    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(
                searchQuery.value
            )}`
        );
        const data = await response.json();
        searchResults.value = data.map((item) => ({
            lat: parseFloat(item.lat),
            lon: parseFloat(item.lon),
            display_name: item.display_name,
        }));
    } catch (error) {
        console.error("Location search failed:", error);
    } finally {
        isSearching.value = false;
    }
}

// Fix references inside selectSearchResult
function selectSearchResult(result) {
    const lat = parseFloat(result.lat);
    const lon = parseFloat(result.lon);

    if (map.value) {
        map.value.setView([lat, lon], 14);

        if (marker.value) {
            marker.value.setLatLng([lat, lon]);
        } else {
            marker.value = L.marker([lat, lon]).addTo(map.value);
        }

        marker.value.bindPopup(result.display_name).openPopup();
    }

    latitude.value = lat.toFixed(6);
    longitude.value = lon.toFixed(6);
    name.value = result.display_name.split(",")[0].trim();
    address.value = result.display_name;

    searchResults.value = [];
    searchQuery.value = "";

    updateNodeData();
}

const handleManualLocation = () => {
    const lat = parseFloat(latitude.value);
    const lon = parseFloat(longitude.value);

    if (isNaN(lat) || isNaN(lon) || !map.value) return;

    map.value.setView([lat, lon], 14);

    if (marker.value) {
        marker.value.setLatLng([lat, lon]);
    } else {
        marker.value = L.marker([lat, lon]).addTo(map.value);
    }

    reverseGeocode(lat, lon);
    validateCoordinates();
};

async function reverseGeocode(lat, lng) {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
        );
        const data = await response.json();

        if (data && data.display_name) {
            address.value = data.display_name;

            // Try to set a meaningful name based on available data
            if (data.name) {
                name.value = data.name;
            } else if (data.address) {
                if (data.address.shop || data.address.amenity) {
                    name.value = data.address.shop || data.address.amenity;
                } else if (data.address.road) {
                    name.value = data.address.road;
                    if (data.address.house_number) {
                        name.value += " " + data.address.house_number;
                    }
                }
            }

            updateNodeData();
        }
    } catch (error) {
        console.error("Reverse geocoding failed:", error);
    }
}

// Update map when coordinates change
watch([latitude, longitude], ([newLat, newLng]) => {
    if (isValidLatitude.value && isValidLongitude.value && map.value) {
        const lat = parseFloat(newLat);
        const lng = parseFloat(newLng);

        if (marker.value) {
            marker.value.setLatLng([lat, lng]);
        } else {
            marker.value = L.marker([lat, lng]).addTo(map.value);
        }

        map.value.setView([lat, lng], map.value.getZoom());
    }
});
// Watch for changes in coordinates
watch(
    [latitude, longitude],
    () => {
        validateCoordinates();
    },
    { immediate: true }
);

// Character counts for name and address fields
const nameCount = computed(() => name.value.length);
const addressCount = computed(() => address.value.length);

const nodeClasses = computed(() => {
    return `location-message-node relative ${
        props.selected ? "border-info-500" : "border-gray-200"
    } ${
        !isValidLocation ? "border-danger-300" : ""
    } bg-white shadow transition-all duration-200`;
});
onBeforeUnmount(() => {
    if (map.value) {
        map.value.remove();
        map.value = null;
    }
});

onMounted(() => {
    validateCoordinates();

    // Listen for global validation requests
    window.addEventListener("flow:validate-all", () => {
        validateCoordinates();
    });
    initMap();
});
</script>

<template>
    <div class="h-full w-full">
        <Handle
            type="target"
            position="left"
            :class="[
                '!h-4 !w-4 !border-2 !border-white !bg-gradient-to-r !shadow-md !transition-transform !duration-300 z-10',
                isValid
                    ? '!from-lime-500 !to-success-600'
                    : '!from-danger-500 !to-orange-500',
            ]"
        />
        <div
            :class="[
                nodeClasses,
                'overflow-hidden rounded-lg border-2 border-gray-200 bg-white shadow-lg transition-all duration-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800',
            ]"
            style="min-width: 280px; max-width: 320px"
        >
            <div
                :class="[
                    'h-1.5 bg-gradient-to-r',
                    isValid
                        ? 'from-success-500 to-emerald-600'
                        : 'from-danger-500 to-orange-500',
                ]"
            ></div>
            <!-- Connection Handles -->

            <div class="p-4">
                <!-- Node Header -->
                <div class="node-header mb-3 flex items-center justify-between">
                    <div class="node-title flex items-center">
                        <div
                            :class="[
                                'node-icon mr-3 rounded-lg p-2 shadow-sm',
                                isValid
                                    ? 'bg-success-100 text-success-600 dark:bg-success-900/50 dark:text-success-300'
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
                                <path
                                    d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"
                                ></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <span
                            class="text-sm font-medium text-gray-800 dark:text-gray-200"
                            >{{ data.label || "Location Message" }}</span
                        >
                    </div>

                    <div class="node-actions flex space-x-1">
                        <button
                            @click="toggleExpand"
                            class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-200 hover:bg-gray-50 hover:text-success-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-success-400"
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
                            class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-200 hover:bg-gray-50 hover:text-success-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-success-400"
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
                <div v-show="isExpanded" class="node-content space-y-4">
                    <!-- Location Search -->
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
                                class="mr-1.5 h-3.5 w-3.5 text-success-500"
                            >
                                <circle cx="11" cy="11" r="8"></circle>
                                <line
                                    x1="21"
                                    y1="21"
                                    x2="16.65"
                                    y2="16.65"
                                ></line>
                            </svg>
                            Search Location
                        </label>
                        <div class="flex">
                            <input
                                v-model="searchQuery"
                                @keyup.enter="searchLocation"
                                class="block w-full rounded-l-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-success-500 focus:ring focus:ring-success-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                placeholder="Search for a location..."
                            />
                            <button
                                @click="searchLocation"
                                class="flex items-center justify-center rounded-r-md bg-success-600 px-3 py-2 text-white transition-colors hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-2 disabled:opacity-50 dark:bg-success-700 dark:hover:bg-success-600"
                                :disabled="isSearching"
                            >
                                <svg
                                    v-if="!isSearching"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="h-4 w-4"
                                >
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line
                                        x1="21"
                                        y1="21"
                                        x2="16.65"
                                        y2="16.65"
                                    ></line>
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
                                    class="h-4 w-4 animate-spin"
                                >
                                    <line x1="12" y1="2" x2="12" y2="6"></line>
                                    <line
                                        x1="12"
                                        y1="18"
                                        x2="12"
                                        y2="22"
                                    ></line>
                                    <line
                                        x1="4.93"
                                        y1="4.93"
                                        x2="7.76"
                                        y2="7.76"
                                    ></line>
                                    <line
                                        x1="16.24"
                                        y1="16.24"
                                        x2="19.07"
                                        y2="19.07"
                                    ></line>
                                    <line x1="2" y1="12" x2="6" y2="12"></line>
                                    <line
                                        x1="18"
                                        y1="12"
                                        x2="22"
                                        y2="12"
                                    ></line>
                                    <line
                                        x1="4.93"
                                        y1="19.07"
                                        x2="7.76"
                                        y2="16.24"
                                    ></line>
                                    <line
                                        x1="16.24"
                                        y1="7.76"
                                        x2="19.07"
                                        y2="4.93"
                                    ></line>
                                </svg>
                            </button>
                        </div>

                        <!-- Search results -->
                        <div
                            v-if="searchResults.length > 0"
                            class="mt-2 max-h-40 overflow-y-auto rounded-md border border-gray-200 shadow-sm dark:border-gray-700"
                        >
                            <div
                                v-for="(result, index) in searchResults"
                                :key="index"
                                @click="selectSearchResult(result)"
                                class="cursor-pointer border-b border-gray-100 p-2.5 text-xs transition-colors hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                {{ result.display_name }}
                            </div>
                        </div>
                    </div>

                    <!-- Map container -->
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
                                class="mr-1.5 h-3.5 w-3.5 text-success-500"
                            >
                                <polygon
                                    points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"
                                ></polygon>
                                <line x1="8" y1="2" x2="8" y2="18"></line>
                                <line x1="16" y1="6" x2="16" y2="22"></line>
                            </svg>
                            Map
                        </label>
                        <div
                            :id="`map-${props.id}`"
                            class="leaflet-container h-48 w-full rounded-md border border-gray-200 shadow-sm dark:border-gray-700"
                        ></div>
                    </div>

                    <!-- Manual coordinates input -->
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
                                class="mr-1.5 h-3.5 w-3.5 text-success-500"
                            >
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M4.93 4.93l4.24 4.24"></path>
                                <path d="M14.83 14.83l4.24 4.24"></path>
                                <path d="M14.83 9.17l4.24-4.24"></path>
                                <path d="M9.17 14.83l-4.24 4.24"></path>
                            </svg>
                            Coordinates
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400"
                                    >Latitude</label
                                >
                                <input
                                    v-model="latitude"
                                    @input="handleManualLocation"
                                    class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-success-500 focus:ring focus:ring-success-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    :class="{
                                        'border-danger-300 dark:border-danger-700':
                                            !isValidLatitude && latitude,
                                    }"
                                    placeholder="e.g. 37.7749"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400"
                                    >Longitude</label
                                >
                                <input
                                    v-model="longitude"
                                    @input="handleManualLocation"
                                    class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-success-500 focus:ring focus:ring-success-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    :class="{
                                        'border-danger-300 dark:border-danger-700':
                                            !isValidLongitude && longitude,
                                    }"
                                    placeholder="e.g. -122.4194"
                                />
                            </div>
                        </div>
                        <div
                            v-if="
                                (!isValidLatitude && latitude) ||
                                (!isValidLongitude && longitude)
                            "
                            class="mt-1.5 text-xs text-danger-500 dark:text-danger-400"
                        >
                            Please enter valid coordinates
                        </div>
                    </div>

                    <!-- Location details -->
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
                                class="mr-1.5 h-3.5 w-3.5 text-success-500"
                            >
                                <path
                                    d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"
                                ></path>
                                <polyline
                                    points="9 22 9 12 15 12 15 22"
                                ></polyline>
                            </svg>
                            Location Name
                        </label>
                        <input
                            v-model="name"
                            @input="handleNameInput"
                            @paste="setTimeout(() => handleNameInput(), 0)"
                            class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-success-500 focus:ring focus:ring-success-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            placeholder="e.g. Company Headquarters"
                            maxlength="100"
                        />
                        <div class="mt-1 flex justify-end text-xs">
                            <span
                                :class="
                                    nameCount >= 100
                                        ? 'text-warning-500'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                            >
                                {{ nameCount }}/100
                                <span v-if="nameCount >= 100" class="ml-1 text-warning-600">(Max reached)</span>
                            </span>
                        </div>
                    </div>

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
                                class="mr-1.5 h-3.5 w-3.5 text-success-500"
                            >
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"
                                ></path>
                            </svg>
                            Address
                        </label>
                        <textarea
                            v-model="address"
                            @input="handleAddressInput"
                            @paste="setTimeout(() => handleAddressInput(), 0)"
                            class="block w-full resize-none rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-success-500 focus:ring focus:ring-success-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            placeholder="Enter full address"
                            rows="3"
                            maxlength="200"
                        ></textarea>
                        <div class="mt-1 flex justify-end text-xs">
                            <span
                                :class="
                                    addressCount >= 200
                                        ? 'text-warning-500'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                            >
                                {{ addressCount }}/200
                                <span v-if="addressCount >= 200" class="ml-1 text-warning-600">(Max reached)</span>
                            </span>
                        </div>
                    </div>

                    <!-- Validation warning -->
                    <div
                        v-if="!isValidLocation"
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
                            Valid coordinates are required to send a location
                            message.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Include these styles if you're using Leaflet */
.leaflet-container {
    height: 200px;
    width: 100%;
    border-radius: 0.25rem;
}
.leaflet-control-attribution {
    display: none !important;
}
.dark .leaflet-layer,
.dark .leaflet-control-zoom-in,
.dark .leaflet-control-zoom-out,
.dark .leaflet-control-attribution {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
}
</style>
