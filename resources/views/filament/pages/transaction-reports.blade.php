<x-filament-panels::page>
    {{-- Date Filter --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <input type="date" wire:model.live="fromDate"
                class="block rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            <span class="text-gray-400">—</span>
            <input type="date" wire:model.live="toDate"
                class="block rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        </div>
        <div class="flex items-center gap-1">
            <button wire:click="$set('fromDate', '{{ now()->startOfMonth()->format('Y-m-d') }}'); $set('toDate', '{{ now()->format('Y-m-d') }}')"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 bg-white dark:bg-gray-800 transition-colors">
                This Month
            </button>
            <button wire:click="$set('fromDate', '{{ now()->subMonth()->startOfMonth()->format('Y-m-d') }}'); $set('toDate', '{{ now()->subMonth()->endOfMonth()->format('Y-m-d') }}')"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 bg-white dark:bg-gray-800 transition-colors">
                Last Month
            </button>
            <button wire:click="$set('fromDate', '{{ now()->startOfYear()->format('Y-m-d') }}'); $set('toDate', '{{ now()->format('Y-m-d') }}')"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 bg-white dark:bg-gray-800 transition-colors">
                YTD
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        {{-- Income --}}
        <div class="flex items-center gap-3 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="p-2 rounded-lg bg-success-50 dark:bg-success-900/20">
                <x-filament::icon icon="heroicon-o-arrow-trending-up" class="w-5 h-5 text-success-600 dark:text-success-400" />
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Income</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">৳ {{ number_format($summary['total_income'] ?? 0, 2) }}</p>
            </div>
        </div>

        {{-- Expense --}}
        <div class="flex items-center gap-3 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="p-2 rounded-lg bg-danger-50 dark:bg-danger-900/20">
                <x-filament::icon icon="heroicon-o-arrow-trending-down" class="w-5 h-5 text-danger-600 dark:text-danger-400" />
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expense</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">৳ {{ number_format($summary['total_expense'] ?? 0, 2) }}</p>
            </div>
        </div>

        {{-- Net Profit/Loss --}}
        @php $net = $summary['net'] ?? 0; @endphp
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700">
                    <x-filament::icon :icon="$net >= 0 ? 'heroicon-o-scale' : 'heroicon-o-exclamation-triangle'"
                        class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $net >= 0 ? 'Net Profit' : 'Net Loss' }}</p>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">৳ {{ number_format(abs($net), 2) }}</p>
                </div>
            </div>
            <div class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">
                {{ $summary['total_transactions'] ?? 0 }} transactions
                @php $trend = ($summary['total_income'] ?? 0) > 0 ? round(($net) / $summary['total_income'] * 100, 1) : 0; @endphp
                @if($trend !== 0) · {{ $trend }}% margin @endif
            </div>
        </div>
    </div>

    {{-- Category Breakdown --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Income by Account --}}
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-success-500"></span>
                Income by Account
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-medium text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-5 py-2 text-left">Account</th>
                        <th class="px-5 py-2 text-right">Count</th>
                        <th class="px-5 py-2 text-right">Amount</th>
                        <th class="px-5 py-2 text-right">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($incomeByAccount as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20">
                        <td class="px-5 py-2.5 font-medium text-gray-700 dark:text-gray-300">{{ $row['account'] ? $row['account']['name'] : 'Uncategorized' }}</td>
                        <td class="px-5 py-2.5 text-right text-gray-500 dark:text-gray-400">{{ $row['count'] }}</td>
                        <td class="px-5 py-2.5 text-right text-success-600 dark:text-success-400 font-medium">৳ {{ number_format($row['total'], 2) }}</td>
                        <td class="px-5 py-2.5 text-right text-gray-500 dark:text-gray-400">{{ $summary['total_income'] > 0 ? round($row['total'] / $summary['total_income'] * 100, 1) : 0 }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400 dark:text-gray-500">No income recorded in this period.</td></tr>
                    @endforelse
                </tbody>
                @if(count($incomeByAccount) > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <td class="px-5 py-2.5 font-semibold text-gray-800 dark:text-gray-200">Total</td>
                        <td class="px-5 py-2.5 text-right font-semibold text-gray-700 dark:text-gray-300">{{ collect($incomeByAccount)->sum('count') }}</td>
                        <td class="px-5 py-2.5 text-right font-semibold text-success-600 dark:text-success-400">৳ {{ number_format($summary['total_income'] ?? 0, 2) }}</td>
                        <td class="px-5 py-2.5 text-right font-semibold text-gray-700 dark:text-gray-300">100%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- Expense by Account --}}
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-danger-500"></span>
                Expense by Account
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-medium text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-5 py-2 text-left">Account</th>
                        <th class="px-5 py-2 text-right">Count</th>
                        <th class="px-5 py-2 text-right">Amount</th>
                        <th class="px-5 py-2 text-right">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($expenseByAccount as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20">
                        <td class="px-5 py-2.5 font-medium text-gray-700 dark:text-gray-300">{{ $row['account'] ? $row['account']['name'] : 'Uncategorized' }}</td>
                        <td class="px-5 py-2.5 text-right text-gray-500 dark:text-gray-400">{{ $row['count'] }}</td>
                        <td class="px-5 py-2.5 text-right text-danger-600 dark:text-danger-400 font-medium">৳ {{ number_format($row['total'], 2) }}</td>
                        <td class="px-5 py-2.5 text-right text-gray-500 dark:text-gray-400">{{ $summary['total_expense'] > 0 ? round($row['total'] / $summary['total_expense'] * 100, 1) : 0 }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400 dark:text-gray-500">No expenses recorded in this period.</td></tr>
                    @endforelse
                </tbody>
                @if(count($expenseByAccount) > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <td class="px-5 py-2.5 font-semibold text-gray-800 dark:text-gray-200">Total</td>
                        <td class="px-5 py-2.5 text-right font-semibold text-gray-700 dark:text-gray-300">{{ collect($expenseByAccount)->sum('count') }}</td>
                        <td class="px-5 py-2.5 text-right font-semibold text-danger-600 dark:text-danger-400">৳ {{ number_format($summary['total_expense'] ?? 0, 2) }}</td>
                        <td class="px-5 py-2.5 text-right font-semibold text-gray-700 dark:text-gray-300">100%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-filament-panels::page>
