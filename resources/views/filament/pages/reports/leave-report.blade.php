<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex items-center gap-4">
            <x-filament::input.wrapper label="Year">
                <x-filament::input.select wire:model.live="year">
                    @foreach(range(now()->year - 5, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
