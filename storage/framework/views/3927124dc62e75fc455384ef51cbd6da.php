<div>
    <?php
        $responsiveCheckboxColumnName =
            \PowerComponents\LivewirePowerGrid\Components\SetUp\Responsive::CHECKBOX_COLUMN_NAME;

        $isCheckboxFixedOnResponsive =
            isset($this->setUp['responsive']) &&
            in_array($responsiveCheckboxColumnName, data_get($this->setUp, 'responsive.fixedColumns'));
    ?>
    <th
        <?php if($isCheckboxFixedOnResponsive): ?> fixed <?php endif; ?>
        scope="col"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([theme_style($theme, 'table.header.th'), theme_style($theme, 'checkbox.th')]); ?>"
        wire:key="checkbox-all-<?php echo e($tableName); ?>"
    >
        <div class="<?php echo e(theme_style($theme, 'checkbox.base')); ?>">
            <label class="<?php echo e(theme_style($theme, 'checkbox.label')); ?>">
                <input
                    class="<?php echo e(theme_style($theme, 'checkbox.input')); ?>"
                    type="checkbox"
                    wire:click="selectCheckboxAll"
                    wire:model="checkboxAll"
                >
            </label>
        </div>
    </th>
</div>
<?php /**PATH /home/ahtisham/app.chatvoo.com/vendor/power-components/livewire-powergrid/src/Providers/../../resources/views/components/checkbox-all.blade.php ENDPATH**/ ?>