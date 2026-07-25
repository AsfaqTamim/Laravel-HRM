<div class="flex flex-col gap-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
        {{-- Header: user info + status badge --}}
        <div class="flex items-center justify-between mb-4 sm:mb-6 pb-3 sm:pb-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center shrink-0">
                    <x-filament::icon icon="heroicon-m-user" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100 truncate">
                        {{ auth()->user()->name }}
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                        {{ now()->format('l, d F Y') }}
                    </p>
                </div>
            </div>

            @if($todayRecord && $todayRecord->check_out)
                <x-filament::badge color="success" icon="heroicon-m-check-circle">
                    Complete
                </x-filament::badge>
            @elseif($todayRecord && $todayRecord->check_in && $todayRecord->onBreak())
                <x-filament::badge color="warning" icon="heroicon-m-pause">
                    On Break
                </x-filament::badge>
            @elseif($todayRecord && $todayRecord->check_in)
                <x-filament::badge color="info" icon="heroicon-m-play">
                    Clocked In
                </x-filament::badge>
            @else
                <x-filament::badge color="gray" icon="heroicon-m-x-circle">
                    Not Clocked In
                </x-filament::badge>
            @endif
        </div>

        {{-- 4 stat boxes side by side on desktop (flex), stacked on mobile --}}
        <div class="flex flex-col md:flex-row gap-3 sm:gap-4 mb-4 sm:mb-6">
            {{-- Check In --}}
            <div class="flex-1 min-w-0 relative bg-gray-50 dark:bg-gray-700 rounded-xl p-3 sm:p-4 border border-gray-100 dark:border-gray-500">
                <div class="flex items-center gap-1.5 mb-1.5 sm:mb-2">
                    <x-filament::icon icon="heroicon-m-arrow-right-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400 dark:text-gray-500" />
                    <span class="text-[10px] sm:text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check In</span>
                </div>
                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $todayRecord?->check_in?->format('H:i') ?? '--:--' }}
                </p>
                @if($todayRecord && $todayRecord->late_minutes > 0)
                    <p class="text-[10px] sm:text-xs text-danger-600 mt-1 flex items-center gap-1">
                        <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        Late {{ $todayRecord->late_minutes }} min
                    </p>
                @elseif($todayRecord && $todayRecord->check_in)
                    <p class="text-[10px] sm:text-xs text-success-600 mt-1 flex items-center gap-1">
                        <x-filament::icon icon="heroicon-m-check" class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        On time
                    </p>
                @endif
            </div>

            {{-- Break --}}
            <div class="flex-1 min-w-0 relative bg-gray-50 dark:bg-gray-700 rounded-xl p-3 sm:p-4 border border-gray-100 dark:border-gray-500">
                 <div class="flex items-center gap-1.5 mb-1.5 sm:mb-2">
                     <x-filament::icon icon="heroicon-m-pause" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400 dark:text-gray-500" />
                     <span class="text-[10px] sm:text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Break</span>
                </div>
                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $todayRecord ? $todayRecord->break_minutes . ' min' : '--' }}
                </p>
                @if($todayRecord && $todayRecord->onBreak())
                    <p class="text-[10px] sm:text-xs text-warning-600 mt-1 flex items-center gap-1">
                        <x-filament::icon icon="heroicon-m-clock" class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        Since {{ $todayRecord->break_start->format('H:i') }}
                    </p>
                @elseif($todayRecord && $todayRecord->break_end)
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 mt-1">Ended {{ $todayRecord->break_end->format('H:i') }}</p>
                @endif
            </div>

            {{-- Check Out --}}
            <div class="flex-1 min-w-0 relative bg-gray-50 dark:bg-gray-700 rounded-xl p-3 sm:p-4 border border-gray-100 dark:border-gray-500">
                 <div class="flex items-center gap-1.5 mb-1.5 sm:mb-2">
                     <x-filament::icon icon="heroicon-m-arrow-left-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400 dark:text-gray-500" />
                     <span class="text-[10px] sm:text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check Out</span>
                </div>
                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $todayRecord?->check_out?->format('H:i') ?? '--:--' }}
                </p>
                @if($todayRecord && $todayRecord->overtime_minutes > 0)
                    <p class="text-[10px] sm:text-xs text-warning-600 mt-1 flex items-center gap-1">
                        <x-filament::icon icon="heroicon-m-arrow-trending-up" class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        +{{ $todayRecord->overtime_minutes }} min OT
                    </p>
                @elseif($todayRecord && $todayRecord->early_leave_minutes > 0)
                    <p class="text-[10px] sm:text-xs text-danger-600 mt-1 flex items-center gap-1">
                        <x-filament::icon icon="heroicon-m-arrow-trending-down" class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        Early {{ $todayRecord->early_leave_minutes }} min
                    </p>
                @endif
            </div>

            {{-- Worked --}}
            <div class="flex-1 min-w-0 relative bg-gray-50 dark:bg-gray-700 rounded-xl p-3 sm:p-4 border border-gray-100 dark:border-gray-500">
                 <div class="flex items-center gap-1.5 mb-1.5 sm:mb-2">
                     <x-filament::icon icon="heroicon-m-clock" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400 dark:text-gray-500" />
                     <span class="text-[10px] sm:text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Worked</span>
                </div>
                @php
                    $worked = 0;
                    if ($todayRecord?->check_in) {
                        $end = $todayRecord->check_out ?? now();
                        $total = (int) $todayRecord->check_in->diffInMinutes($end);
                        $worked = max(0, $total - $todayRecord->break_minutes);
                    }
                @endphp
                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
                    @if($todayRecord?->check_in)
                        {{ intdiv($worked, 60) }}h {{ $worked % 60 }}m
                    @else
                        --:--
                    @endif
                </p>
                @if($todayRecord && !$todayRecord->check_out)
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                        <x-filament::icon icon="heroicon-m-play" class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        In progress
                    </p>
                @endif
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex justify-center gap-2 sm:gap-3 flex-wrap">
            {{-- Check In --}}
            @if(!$todayRecord || !$todayRecord->check_in)
                <x-filament::button
                    wire:click="checkIn"
                    color="success"
                    icon="heroicon-m-arrow-right-circle"
                    size="xl"
                    class="px-5 py-3 sm:px-8 sm:py-4 text-sm sm:text-lg font-medium mt-2"
                >
                    Check In
                </x-filament::button>
            @endif

            {{-- Break In / Out + Check Out --}}
            @if($todayRecord && $todayRecord->check_in && !$todayRecord->check_out)
                @if($todayRecord->onBreak())
                    <x-filament::button
                        wire:click="breakOut"
                        color="warning"
                        icon="heroicon-m-play"
                        size="xl"
                        class="px-5 py-3 sm:px-8 sm:py-4 text-sm sm:text-lg font-medium mt-2"
                    >
                        Back to Work
                    </x-filament::button>
                @else
                    <x-filament::button
                        wire:click="breakIn"
                        color="gray"
                        icon="heroicon-m-pause"
                        size="xl"
                        class="px-5 py-3 sm:px-8 sm:py-4 text-sm sm:text-lg font-medium mt-2"
                    >
                        Break
                    </x-filament::button>
                @endif

                <x-filament::button
                    wire:click="checkOut"
                    color="danger"
                    icon="heroicon-m-arrow-left-circle"
                    size="xl"
                    class="px-5 py-3 sm:px-8 sm:py-4 text-sm sm:text-lg font-medium mt-2"
                >
                    Check Out
                </x-filament::button>
            @endif
        </div>
    </div>

    {{-- Quick tip for new users --}}
    @if(!$todayRecord || !$todayRecord->check_in)
        <div class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 rounded-xl p-3 sm:p-4 flex items-start gap-2 sm:gap-3">
            <x-filament::icon icon="heroicon-m-light-bulb" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-500 dark:text-indigo-400 mt-0.5 shrink-0" />
            <p class="text-xs sm:text-sm text-indigo-700 dark:text-indigo-300">
                Start your day by clicking <strong>Check In</strong>. Take a break anytime with the <strong>Break</strong> button, and end your shift with <strong>Check Out</strong>.
            </p>
        </div>
    @endif
</div>
