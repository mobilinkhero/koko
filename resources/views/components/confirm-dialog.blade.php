@once
<div x-data="confirmDialog" x-show="isOpen" @confirm-dialog.window="showDialog($event.detail)"
  class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" style="display: none;">
  <!-- Your existing dialog HTML here -->
  <div x-data="confirmDialog" x-show="isOpen" @confirm-dialog.window="showDialog($event.detail)"
    class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" style="display: none;">
    <div x-data="confirmDialog" x-show="isOpen" @confirm-dialog.window="showDialog($event.detail)"
      class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" style="display: none;">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

      <!-- Dialog -->
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div x-show="isOpen" x-transition:enter="ease-out duration-300"
          x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
          x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
          x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
          <div class="sm:flex sm:items-start">
            <!-- Icon -->
            <div
              class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-danger-100 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-danger-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
              </svg>
            </div>
            <!-- Content -->
            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
              <h3 x-text="title" class="text-base font-semibold leading-6 text-gray-900"></h3>
              <div class="mt-2">
                <p x-text="message" class="text-sm text-gray-500"></p>
              </div>
            </div>
          </div>
          <!-- Buttons -->
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button type="button" @click="confirm()" x-text="confirmText"
              class="inline-flex w-full justify-center rounded-md bg-danger-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-danger-500 sm:ml-3 sm:w-auto"></button>
            <button type="button" @click="cancel()" x-text="cancelText"
              class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('alpine:init', () => {
        Alpine.data('confirmDialog', () => ({
          isOpen: false,
          title: '',
          message: '',
          confirmText: 'Confirm',
          cancelText: 'Cancel',
          resolvePromise: null,

          showDialog({
            title,
            message,
            confirmText = 'Confirm',
            cancelText = 'Cancel'
          }) {
            this.title = title;
            this.message = message;
            this.confirmText = confirmText;
            this.cancelText = cancelText;
            this.isOpen = true;

            return new Promise((resolve) => {
              this.resolvePromise = resolve;
            });
          },

          confirm() {
            if (this.resolvePromise) {
              this.resolvePromise(true);
            }
            this.isOpen = false;
          },

          cancel() {
            if (this.resolvePromise) {
              this.resolvePromise(false);
            }
            this.isOpen = false;
          }
        }));
      });

      window.showConfirmDialog = async function({
        title = 'Confirm Action',
        message = 'Are you sure you want to proceed?',
        confirmText = 'Confirm',
        cancelText = 'Cancel'
      } = {}) {
        return await window.dispatchEvent(new CustomEvent('confirm-dialog', {
          detail: {
            title,
            message,
            confirmText,
            cancelText
          }
        }));
      }
</script>
@endpush
@endonce