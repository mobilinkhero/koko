<?php
    $value = (int) $row->{data_get($column, 'field')};

    $trueValue = data_get($column, 'toggleable')['default'][0];
    $falseValue = data_get($column, 'toggleable')['default'][1];
?>

<div class="flex flex-row justify-center">
    <!--[if BLOCK]><![endif]--><?php if($showToggleable): ?>
        <?php

            $params = [
                'id' => data_get($row, $this->realPrimaryKey),
                'isHidden' => !$showToggleable,
                'tableName' => $tableName,
                'field' => data_get($column, 'field'),
                'toggle' => $value,
                'trueValue' => $trueValue,
                'falseValue' => $falseValue,
            ];
        ?>
        <div
            x-data="pgToggleable(<?php echo \Illuminate\Support\Js::from($params)->toHtml() ?>)"
            :class="{
                'relative rounded-full w-8 h-4 transition duration-200 ease-linear': true,
                'bg-pg-secondary-600 dark:pg-secondary-500': toggle,
                'bg-pg-primary-200': !toggle
            }"
        >
            <label
                :class="{
                    'absolute left-0 bg-white border-2 mb-2 w-4 h-4 rounded-full transition transform duration-100 ease-linear cursor-pointer': true,
                    'translate-x-full border-pg-secondary-600': toggle,
                    'translate-x-0 border-pg-primary-200': !toggle
                }"
                x-on:click="save"
            ></label>
            <input
                type="checkbox"
                class="appearance-none opacity-0 w-full h-full active:outline-none focus:outline-none"
                x-on:click="save"
            >
        </div>
    <?php else: ?>
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'text-xs px-4 w-auto py-1 text-center rounded-md',
            'bg-red-200 text-red-800' => $value === 0,
            'bg-blue-200 text-blue-800' => $value === 1,
        ]); ?>">
            <?php echo e($value === 0 ? $falseValue : $trueValue); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH /home/ahtisham/app.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/frameworks/tailwind/toggleable.blade.php ENDPATH**/ ?>