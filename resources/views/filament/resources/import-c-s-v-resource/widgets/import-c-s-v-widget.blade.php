<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-lg font-semibold">CSV Import</div>
                <div class="text-sm text-gray-500">Upload CSV and queue processing.</div>
            </div>

            {{ $this->uploadCsvAction }}
        </div>

        <x-filament-actions::modals />
    </x-filament::section>
</x-filament-widgets::widget>
