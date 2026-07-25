<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex items-center gap-4">
            <x-filament::input.wrapper label="Month">
                <x-filament::input.select wire:model.live="month">
                    @foreach([
                        1 => 'January', 2 => 'February', 3 => 'March',
                        4 => 'April', 5 => 'May', 6 => 'June',
                        7 => 'July', 8 => 'August', 9 => 'September',
                        10 => 'October', 11 => 'November', 12 => 'December',
                    ] as $val => $label)
                        <option value="{{ $val }}" {{ $month == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
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
