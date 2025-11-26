<!--[if BLOCK]><![endif]--><?php if(data_get($setUp, 'header.showMessageSoftDeletes') &&
        ($softDeletes === 'withTrashed' || $softDeletes === 'onlyTrashed')): ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-2 my-2">
        <div class="flex">
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <!--[if BLOCK]><![endif]--><?php if($softDeletes === 'withTrashed'): ?>
                        <?php echo app('translator')->get('livewire-powergrid::datatable.soft_deletes.message_with_trashed'); ?>
                    <?php else: ?>
                        <?php echo app('translator')->get('livewire-powergrid::datatable.soft_deletes.message_only_trashed'); ?>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </p>
            </div>
        </div>
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH /home/adphwiudpl/mark.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/frameworks/tailwind/header/message-soft-deletes.blade.php ENDPATH**/ ?>