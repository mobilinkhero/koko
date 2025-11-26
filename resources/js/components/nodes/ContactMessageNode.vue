<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { Handle, useVueFlow, useNode } from "@vue-flow/core";

const props = defineProps({
    id: { type: String, required: true },
    data: { type: Object, required: true },
    selected: { type: Boolean, default: false },
});

// Add emit to communicate with parent component
const emit = defineEmits(["update:isValid"]);

const { removeNodes, nodes, addNodes } = useVueFlow();
const node = useNode();

// Initialize output with default data structure if not available
const output = ref(
    props.data.output?.[0] || {
        contacts: [],
    }
);

// Ensure we always have at least one contact
const contacts = ref(
    output.value.contacts && output.value.contacts.length > 0
        ? output.value.contacts
        : [
              {
                  id: Date.now(),
                  firstName: "",
                  lastName: "",
                  phone: "",
                  email: "",
                  company: "",
                  title: "",
              },
          ]
);

const isExpanded = ref(true);
const errors = ref({});

function addContact() {
    contacts.value.push({
        id: Date.now(),
        firstName: "",
        lastName: "",
        phone: "",
        email: "",
        company: "",
        title: "",
    });
    updateNodeData();
    validateForm(); // Validate after adding a contact
}

function removeContact(index) {
    if (contacts.value.length > 1) {
        contacts.value.splice(index, 1);
        updateNodeData();
        validateForm(); // Validate after removing a contact
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

// Input handlers with automatic trimming for contact fields
function handleFirstNameInput(index) {
    if (contacts.value[index].firstName.length > 100) {
        contacts.value[index].firstName = contacts.value[index].firstName.substring(0, 100);
    }
    updateNodeData();
}

function handleLastNameInput(index) {
    if (contacts.value[index].lastName.length > 100) {
        contacts.value[index].lastName = contacts.value[index].lastName.substring(0, 100);
    }
    updateNodeData();
}

function handlePhoneInput(index) {
    if (contacts.value[index].phone.length > 20) {
        contacts.value[index].phone = contacts.value[index].phone.substring(0, 20);
    }
    updateNodeData();
}

function handleEmailInput(index) {
    if (contacts.value[index].email.length > 320) {
        contacts.value[index].email = contacts.value[index].email.substring(0, 320);
    }
    updateNodeData();
}

function handleCompanyInput(index) {
    if (contacts.value[index].company.length > 100) {
        contacts.value[index].company = contacts.value[index].company.substring(0, 100);
    }
    updateNodeData();
}

function handleTitleInput(index) {
    if (contacts.value[index].title.length > 100) {
        contacts.value[index].title = contacts.value[index].title.substring(0, 100);
    }
    updateNodeData();
}

function updateNodeData() {
    props.data.output = [
        {
            contacts: contacts.value,
        },
    ];

    // Update validation state in node data
    props.data.isValid = isValid.value;
    validateForm();
}

function toggleExpand() {
    isExpanded.value = !isExpanded.value;
}

// Phone number validation with international format
function validatePhone(phone) {
    if (!phone) return false;

    // Allow WhatsApp variable format: {{variable}}
    if (/^\{\{[\w_]+\}\}$/.test(phone)) return true;

    // International format validator
    // Allows: +1234567890, +123 456 7890, +123-456-7890
    return /^\+[1-9]\d{1,14}$/.test(phone.replace(/[\s-]/g, ""));
}

// Email validation
function validateEmail(email) {
    if (!email) return true; // Email is optional

    // Allow variable format
    if (/^\{\{[\w_]+\}\}$/.test(email)) return true;

    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Contact validation
const contactValidations = computed(() => {
    return contacts.value.map((contact) => {
        return {
            id: contact.id,
            firstNameValid: !!contact.firstName.trim(),
            lastNameValid: !!contact.lastName.trim(),
            phoneValid: validatePhone(contact.phone),
            emailValid: validateEmail(contact.email),
            isValid:
                !!contact.firstName.trim() &&
                !!contact.lastName.trim() &&
                validatePhone(contact.phone),
        };
    });
});

// Overall validation state
const isValid = computed(() => {
    return contactValidations.value.every((validation) => validation.isValid);
});

// Function to perform form validation and update errors
function validateForm() {
    // Reset errors
    errors.value = {};

    // Check each contact for validation errors
    contacts.value.forEach((contact, index) => {
        if (!contact.firstName.trim()) {
            if (!errors.value[index]) errors.value[index] = {};
            errors.value[index].firstName = true;
        }

        if (!contact.lastName.trim()) {
            if (!errors.value[index]) errors.value[index] = {};
            errors.value[index].lastName = true;
        }

        if (!validatePhone(contact.phone)) {
            if (!errors.value[index]) errors.value[index] = {};
            errors.value[index].phone = true;
        }

        if (contact.email && !validateEmail(contact.email)) {
            if (!errors.value[index]) errors.value[index] = {};
            errors.value[index].email = true;
        }
    });

    // Update the node data with validation state
    props.data.isValid = isValid.value;

    // Emit the validation status to parent components
    emit("update:isValid", isValid.value);

    return isValid.value;
}

const nodeClasses = computed(() => {
    return `contact-message-node  rounded-lg border-2 ${
        props.selected ? "border-info-500" : "border-gray-200"
    } ${!isValid.value ? "border-danger-300" : ""} bg-white shadow`;
});

// Watch for changes to validate
watch(
    contacts,
    () => {
        validateForm();
    },
    { deep: true }
);

// Initial validation on mount
onMounted(() => {
    validateForm();
});
</script>

<template>
    <div class="h-full w-full">
        <div
            :class="[
                nodeClasses,
                'overflow-hidden rounded-lg border-2 border-gray-200 bg-white shadow-lg transition-all duration-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800',
            ]"
            style="min-width: 280px; max-width: 400px"
        >
            <!-- Node type indicator - gradient bar with unique indigo-to-violet gradient -->
            <div
                :class="[
                    'h-1.5',
                    isValid
                        ? 'bg-gradient-to-r from-primary-500 to-info-500'
                        : 'bg-gradient-to-r from-danger-500 to-orange-500',
                ]"
            ></div>

            <Handle
                type="target"
                position="left"
                :class="[
                    '!h-4 !w-4 !border-2 !border-white',
                    isValid ? '!bg-primary-500' : '!bg-danger-500',
                ]"
            />

            <div class="p-4">
                <!-- Node Header -->
                <div class="node-header mb-3 flex items-center justify-between">
                    <div class="node-title flex items-center">
                        <div
                            :class="[
                                'node-icon mr-3 rounded-lg p-2 shadow-sm',
                                isValid
                                    ? 'bg-primary-100 text-primary-600 dark:bg-primary-900/50 dark:text-primary-300'
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
                                    d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"
                                ></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <span
                            class="text-sm font-medium text-gray-800 dark:text-gray-200"
                            >{{ data.label || "Contact Message" }}</span
                        >
                    </div>

                    <div class="node-actions flex space-x-1">
                        <button
                            @click="toggleExpand"
                            class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-200 hover:bg-gray-50 hover:text-primary-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-primary-400"
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
                            class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-200 hover:bg-gray-50 hover:text-primary-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-primary-400"
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
                            Please fill in all required fields marked with *
                        </div>
                    </div>

                    <div class="contacts-container space-y-4">
                        <div
                            v-for="(contact, index) in contacts"
                            :key="contact.id"
                            class="contact rounded-md border border-gray-200 p-3 shadow-sm transition-all duration-200 hover:shadow dark:border-gray-700"
                            :class="{
                                'border-danger-300 bg-danger-50 dark:border-danger-800 dark:bg-danger-900/30':
                                    !contactValidations[index].isValid,
                            }"
                        >
                            <div
                                class="mb-2.5 flex items-center justify-between"
                            >
                                <div class="flex items-center">
                                    <div
                                        class="mr-2 flex h-6 w-6 items-center justify-center rounded-full bg-primary-100 text-primary-600 dark:bg-primary-900/50 dark:text-primary-300"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="h-3.5 w-3.5"
                                        >
                                            <path
                                                d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"
                                            ></path>
                                            <circle
                                                cx="12"
                                                cy="7"
                                                r="4"
                                            ></circle>
                                        </svg>
                                    </div>
                                    <span
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Contact {{ index + 1 }}
                                    </span>
                                </div>
                                <button
                                    @click="removeContact(index)"
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 text-danger-500 transition-colors hover:bg-danger-100 hover:text-danger-700 dark:bg-gray-700 dark:text-danger-400 dark:hover:bg-danger-900/50 dark:hover:text-danger-300"
                                    :disabled="contacts.length <= 1"
                                    :class="{
                                        'cursor-not-allowed opacity-50':
                                            contacts.length <= 1,
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

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-600 dark:text-gray-400"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="mr-1.5 h-3 w-3 text-primary-500"
                                        >
                                            <path
                                                d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"
                                            ></path>
                                            <circle
                                                cx="12"
                                                cy="7"
                                                r="4"
                                            ></circle>
                                        </svg>
                                        First Name*
                                        <span
                                            v-if="
                                                !contactValidations[index]
                                                    .firstNameValid
                                            "
                                            class="ml-1 text-xs font-medium text-danger-500"
                                            >(Required)</span
                                        >
                                    </label>
                                    <input
                                        v-model="contact.firstName"
                                        @input="handleFirstNameInput(index)"
                                        @paste="setTimeout(() => handleFirstNameInput(index), 0)"
                                        class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                        :class="{
                                            'border-danger-300 dark:border-danger-700':
                                                !contactValidations[index]
                                                    .firstNameValid,
                                        }"
                                        placeholder="First name"
                                        maxlength="100"
                                    />
                                    <div class="mt-1 flex justify-end text-xs">
                                        <span
                                            :class="
                                                contact.firstName.length >= 100
                                                    ? 'text-warning-500'
                                                    : 'text-gray-500 dark:text-gray-400'
                                            "
                                        >
                                            {{ contact.firstName.length }}/100
                                            <span v-if="contact.firstName.length >= 100" class="ml-1 text-warning-600">(Max reached)</span>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-600 dark:text-gray-400"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="mr-1.5 h-3 w-3 text-primary-500"
                                        >
                                            <path
                                                d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"
                                            ></path>
                                            <circle
                                                cx="12"
                                                cy="7"
                                                r="4"
                                            ></circle>
                                        </svg>
                                        Last Name*
                                        <span
                                            v-if="
                                                !contactValidations[index]
                                                    .lastNameValid
                                            "
                                            class="ml-1 text-xs font-medium text-danger-500"
                                            >(Required)</span
                                        >
                                    </label>
                                    <input
                                        v-model="contact.lastName"
                                        @input="handleLastNameInput(index)"
                                        @paste="setTimeout(() => handleLastNameInput(index), 0)"
                                        class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                        :class="{
                                            'border-danger-300 dark:border-danger-700':
                                                !contactValidations[index]
                                                    .lastNameValid,
                                        }"
                                        placeholder="Last name"
                                        maxlength="100"
                                    />
                                    <div class="mt-1 flex justify-end text-xs">
                                        <span
                                            :class="
                                                contact.lastName.length >= 100
                                                    ? 'text-warning-500'
                                                    : 'text-gray-500 dark:text-gray-400'
                                            "
                                        >
                                            {{ contact.lastName.length }}/100
                                            <span v-if="contact.lastName.length >= 100" class="ml-1 text-warning-600">(Max reached)</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label
                                    class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-600 dark:text-gray-400"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="mr-1.5 h-3 w-3 text-primary-500"
                                    >
                                        <path
                                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"
                                        ></path>
                                    </svg>
                                    Phone*
                                    <span
                                        v-if="
                                            !contactValidations[index]
                                                .phoneValid
                                        "
                                        class="ml-1 text-xs font-medium text-danger-500"
                                        >(Invalid format)</span
                                    >
                                </label>
                                <input
                                    v-model="contact.phone"
                                    @input="handlePhoneInput(index)"
                                    @paste="setTimeout(() => handlePhoneInput(index), 0)"
                                    class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    :class="{
                                        'border-danger-300 dark:border-danger-700':
                                            !contactValidations[index]
                                                .phoneValid,
                                    }"
                                    placeholder="e.g. +1234567890 or {{phone}}"
                                    maxlength="20"
                                />
                                <div class="mt-1 flex justify-between text-xs">
                                    <span
                                        v-if="
                                            contact.phone &&
                                            !contactValidations[index].phoneValid
                                        "
                                        class="text-danger-500 dark:text-danger-400"
                                    >
                                        Phone must be in international format (e.g., +1234567890)
                                    </span>
                                    <span
                                        :class="
                                            contact.phone.length >= 20
                                                ? 'text-warning-500'
                                                : 'text-gray-500 dark:text-gray-400'
                                        "
                                        class="ml-auto"
                                    >
                                        {{ contact.phone.length }}/20
                                        <span v-if="contact.phone.length >= 20" class="ml-1 text-warning-600">(Max reached)</span>
                                    </span>
                                </div>

                            </div>

                            <div class="mt-3">
                                <label
                                    class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-600 dark:text-gray-400"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="mr-1.5 h-3 w-3 text-primary-500"
                                    >
                                        <path
                                            d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"
                                        ></path>
                                        <polyline
                                            points="22,6 12,13 2,6"
                                        ></polyline>
                                    </svg>
                                    Email
                                    <span
                                        v-if="
                                            contact.email &&
                                            !contactValidations[index]
                                                .emailValid
                                        "
                                        class="ml-1 text-xs font-medium text-danger-500"
                                        >(Invalid format)</span
                                    >
                                </label>
                                <input
                                    v-model="contact.email"
                                    @input="handleEmailInput(index)"
                                    @paste="setTimeout(() => handleEmailInput(index), 0)"
                                    class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    :class="{
                                        'border-danger-300 dark:border-danger-700':
                                            contact.email &&
                                            !contactValidations[index]
                                                .emailValid,
                                    }"
                                    placeholder="Email (optional)"
                                    maxlength="320"
                                />
                                <div class="mt-1 flex justify-end text-xs">
                                    <span
                                        :class="
                                            contact.email.length >= 320
                                                ? 'text-warning-500'
                                                : 'text-gray-500 dark:text-gray-400'
                                        "
                                    >
                                        {{ contact.email.length }}/320
                                        <span v-if="contact.email.length >= 320" class="ml-1 text-warning-600">(Max reached)</span>
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-600 dark:text-gray-400"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="mr-1.5 h-3 w-3 text-primary-500"
                                        >
                                            <rect
                                                x="2"
                                                y="7"
                                                width="20"
                                                height="14"
                                                rx="2"
                                                ry="2"
                                            ></rect>
                                            <path
                                                d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"
                                            ></path>
                                        </svg>
                                        Company
                                    </label>
                                    <input
                                        v-model="contact.company"
                                        @input="handleCompanyInput(index)"
                                        @paste="setTimeout(() => handleCompanyInput(index), 0)"
                                        class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                        placeholder="Company (optional)"
                                        maxlength="100"
                                    />
                                    <div class="mt-1 flex justify-end text-xs">
                                        <span
                                            :class="
                                                contact.company.length >= 100
                                                    ? 'text-warning-500'
                                                    : 'text-gray-500 dark:text-gray-400'
                                            "
                                        >
                                            {{ contact.company.length }}/100
                                            <span v-if="contact.company.length >= 100" class="ml-1 text-warning-600">(Max reached)</span>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-600 dark:text-gray-400"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="mr-1.5 h-3 w-3 text-primary-500"
                                        >
                                            <path
                                                d="M20 7h-4V4c0-1.105-.895-2-2-2h-4c-1.105 0-2 .895-2 2v3H4c-1.105 0-2 .895-2 2v6c0 1.105.895 2 2 2h16c1.105 0 2-.895 2-2V9c0-1.105-.895-2-2-2z"
                                            ></path>
                                        </svg>
                                        Title
                                    </label>
                                    <input
                                        v-model="contact.title"
                                        @input="handleTitleInput(index)"
                                        @paste="setTimeout(() => handleTitleInput(index), 0)"
                                        class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                        placeholder="Title (optional)"
                                        maxlength="100"
                                    />
                                    <div class="mt-1 flex justify-end text-xs">
                                        <span
                                            :class="
                                                contact.title.length >= 100
                                                    ? 'text-warning-500'
                                                    : 'text-gray-500 dark:text-gray-400'
                                            "
                                        >
                                            {{ contact.title.length }}/100
                                            <span v-if="contact.title.length >= 100" class="ml-1 text-warning-600">(Max reached)</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            @click="addContact"
                            class="w-full rounded-md bg-primary-50 px-3 py-2 text-sm font-medium text-primary-600 transition-colors hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-opacity-50 dark:bg-primary-900/30 dark:text-primary-400 dark:hover:bg-primary-900/50"
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
                                Add Another Contact
                            </span>
                        </button>
                    </div>

                    <div
                        class="mt-3 rounded-md bg-gray-50 p-3 text-xs text-gray-500 dark:bg-gray-800/50 dark:text-gray-400"
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
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            Required fields are marked with *
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
