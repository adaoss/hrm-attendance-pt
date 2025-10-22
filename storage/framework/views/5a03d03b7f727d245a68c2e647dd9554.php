<!--[if BLOCK]><![endif]--><?php if(filament()->hasUnsavedChangesAlerts()): ?>
        <?php
        $__scriptKey = '2949776745-0';
        ob_start();
    ?>
        <script>
            setUpUnsavedActionChangesAlert({
                resolveLivewireComponentUsing: () => window.Livewire.find('<?php echo e($_instance->getId()); ?>'),
                $wire,
            })
        </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH /home/runner/work/hrm-attendance-pt/hrm-attendance-pt/vendor/filament/filament/resources/views/components/unsaved-action-changes-alert.blade.php ENDPATH**/ ?>