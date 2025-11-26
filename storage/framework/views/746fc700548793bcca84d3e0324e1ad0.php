<div x-data="{
    messages: [],
    add(e) {
        const eventDetail = Array.isArray(e.detail) ? e.detail[0] : e.detail;
        const { message, type } = eventDetail
        this.messages.unshift({ id: e.timeStamp, content: message, type: type || 'info' });
    },
    remove(message) {
        this.messages = this.messages.filter(i => i.id !== message.id);
    }
}" x-on:notify.window="add($event);" aria-live="assertive"
  class="pointer-events-none fixed top-14 inset-0 flex flex-col items-end px-4 py-6 space-y-4 sm:items-start sm:p-6 z-[9999]">
  <template x-for="message in messages" :key="message.id" hidden>
    <div x-data="{
        show: false,
        progress: 100,
        init() {
            this.$nextTick(() => this.show = true)
            this.startProgress();
        },
        startProgress() {
            const duration = 8000;
            const interval = 10;
            const steps = duration / interval;
            const decrement = 100 / steps;

            const timer = setInterval(() => {
                this.progress -= decrement;
                if (this.progress <= 0) {
                    clearInterval(timer);
                    this.transitionOut();
                }
            }, interval);
        },
        transitionOut() {
            this.show = false
            setTimeout(() => this.remove(this.message), 1000)
        },
    }" class="flex w-full flex-col items-center space-y-4 sm:items-end">
      <div x-show="show" x-transition:enter="transition ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="pointer-events-auto w-full max-w-sm overflow-hidden relative ring-1 ring-black ring-opacity-5 dark:bg-slate-700"
        :class="{
            'bg-success-100 text-success-500': message.type === 'success',
            'bg-warning-100 text-warning-500': message.type === 'warning',
            'bg-danger-100 text-danger-500': message.type === 'danger',
            'bg-info-100 text-info-500': message.type === 'info',
        }">
        <div class="p-4">
          <div class="flex items-center">
            <template x-if="message.type === 'success'">
              <div
                class="nline-flex items-center justify-center p-1 flex-shrink-0 w-8 h-8 text-success-500 bg-success-100 rounded-lg dark:bg-success-800 dark:text-success-200">
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-check-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 text-success-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
              </div>
            </template>

            <template x-if="message.type === 'warning'">
              <div
                class="nline-flex items-center justify-center p-1 flex-shrink-0 w-8 h-8 text-warning-500 bg-warning-100 rounded-lg dark:bg-warning-800 dark:text-warning-200">
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-exclamation-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 text-warning-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
              </div>
            </template>

            <template x-if="message.type === 'danger'">
              <div
                class="nline-flex items-center justify-center p-1 flex-shrink-0 w-8 h-8 text-danger-500 bg-danger-100 rounded-lg dark:bg-danger-800 dark:text-danger-200">
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-c-x-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 text-danger-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
              </div>
            </template>

            <template x-if="message.type === 'info'">
              <div
                class="nline-flex items-center justify-center p-1 flex-shrink-0 w-8 h-8 text-info-500 bg-info-100 rounded-lg dark:bg-info-800 dark:text-info-200">
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-s-information-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 text-info-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
              </div>
            </template>

            <div class="ml-3 flex w-0 flex-1 justify-between">
              <p x-text="message.content" class="w-0 flex-1 text-base font-normal"></p>
              <div class="flex flex-shrink-0">
                <button x-on:click="remove(message)" type="button"
                  class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-info-500 focus:ring-offset-2 dark:hover:text-slate-300">
                  <span class="sr-only"><?php echo e(t('Close')); ?></span>
                  <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                </button>
              </div>
            </div>
          </div>
        </div>
        <!-- Progress Bar -->
        <div class="absolute bottom-0 left-0 h-1 transition-all duration-75 ease-linear" :class="{
              'bg-success-500': message.type === 'success',
              'bg-warning-500': message.type === 'warning',
              'bg-danger-500': message.type === 'danger',
              'bg-info-500': message.type === 'info'
          }" :style="'width: ' + progress + '%'">
        </div>
      </div>
    </div>
  </template>
</div>
<?php if(session('notification')): ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
      // Small delay to ensure the page has loaded
      setTimeout(() => {
        window.dispatchEvent(new CustomEvent('notify', {
          detail: <?php echo json_encode(session('notification')); ?>

        }));
      }, 300);
    });
</script>
<?php endif; ?>
<?php /**PATH /home/ahtisham/app.chatvoo.com/resources/views/components/notification.blade.php ENDPATH**/ ?>