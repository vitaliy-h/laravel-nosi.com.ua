// Alpine component behind App\Filament\Tables\Columns\InlineEditColumn.
//
// Loaded on demand by Filament (x-load), not bundled by Vite: keep it a plain ES
// module without imports. The server value lives in the hidden `serverState`
// input, which Livewire refreshes on every table render, so the field always opens
// with the current value even after another cell was saved.
export default function inlineEditColumn({ name, recordKey, type }) {
    return {
        editing: false,

        isLoading: false,

        error: undefined,

        state: type === 'checkboxes' ? [] : '',

        open() {
            if (this.editing) {
                return
            }

            window.getSelection()?.removeAllRanges()

            this.state = this.getServerState()
            this.error = undefined
            this.editing = true

            this.$nextTick(() => {
                const input = this.$refs.input

                input?.focus()

                if (input?.select && type !== 'select') {
                    input.select()
                }
            })
        },

        cancel() {
            if (!this.editing || this.isLoading) {
                return
            }

            this.state = this.getServerState()
            this.error = undefined
            this.editing = false
        },

        async save() {
            if (!this.editing || this.isLoading) {
                return
            }

            if (this.normalize(this.state) === this.normalize(this.getServerState())) {
                this.editing = false
                this.error = undefined

                return
            }

            this.isLoading = true

            const response = await this.$wire.updateTableColumnState(
                name,
                recordKey,
                Alpine.raw(this.state),
            )

            this.isLoading = false
            this.error = response?.error ?? undefined

            if (this.error !== undefined) {
                this.$nextTick(() => this.$refs.input?.focus())

                return
            }

            this.editing = false
        },

        getServerState() {
            try {
                return JSON.parse(this.$refs.serverState.value)
            } catch {
                return type === 'checkboxes' ? [] : ''
            }
        },

        normalize(state) {
            if (Array.isArray(state)) {
                return JSON.stringify(state.map(String).sort())
            }

            return [null, undefined].includes(state) ? '' : String(state)
        },
    }
}
