<script>
    function assigneeMultiselect({
        selected = [],
        options = []
    }) {
        return {
            open: false,
            selected,
            options,

            get selectedOptions() {
                return this.options.filter(opt => this.selected.includes(opt.id));
            },

            get availableOptions() {
                return this.options.filter(opt => !this.selected.includes(opt.id));
            },

            toggle(id) {
                if (this.selected.includes(id)) {
                    this.selected = this.selected.filter(i => i !== id);
                } else {
                    this.selected.push(id);
                }
            },

            remove(id) {
                this.selected = this.selected.filter(i => i !== id);
            },
        }
    }
</script>
