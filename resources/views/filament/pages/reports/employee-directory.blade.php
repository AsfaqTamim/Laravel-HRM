<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex items-center gap-4">
            <x-filament::input.wrapper label="Department">
                <x-filament::input.select wire:model.live="department_id">
                    <option value="">All Departments</option>
                    @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                        <option value="{{ $dept->id }}" {{ $department_id == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
