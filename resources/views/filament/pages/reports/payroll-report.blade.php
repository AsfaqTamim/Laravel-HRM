<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex items-center gap-4">
            <x-filament::input.wrapper label="Payroll Period">
                <x-filament::input.select wire:model.live="payroll_run_id">
                    @foreach(\App\Models\PayrollRun::orderByDesc('year')->orderByDesc('month')->get() as $run)
                        <option value="{{ $run->id }}" {{ $payroll_run_id == $run->id ? 'selected' : '' }}>
                            {{ $run->label }}
                        </option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
