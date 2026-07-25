<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class Reports extends Page
{
    protected static string $resource = TransactionResource::class;
    protected static string $view = 'filament.pages.transaction-reports';

    public ?string $fromDate = null;
    public ?string $toDate = null;
    public array $summary = [];
    public array $incomeByAccount = [];
    public array $expenseByAccount = [];

    public function mount(): void
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
        $this->loadReport();
    }

    public function loadReport(): void
    {
        $query = Transaction::query()
            ->when($this->fromDate, fn ($q, $d) => $q->where('date', '>=', $d))
            ->when($this->toDate, fn ($q, $d) => $q->where('date', '<=', $d));

        $totalIncome = (float) (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (float) (clone $query)->where('type', 'expense')->sum('amount');
        $net = $totalIncome - $totalExpense;

        $this->summary = [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net' => $net,
            'total_transactions' => $query->count(),
        ];

        $this->incomeByAccount = (clone $query)
            ->where('type', 'income')
            ->select('account_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('account_id')
            ->orderByDesc('total')
            ->with('account')
            ->get()
            ->toArray();

        $this->expenseByAccount = (clone $query)
            ->where('type', 'expense')
            ->select('account_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('account_id')
            ->orderByDesc('total')
            ->with('account')
            ->get()
            ->toArray();
    }

    public function updated($property): void
    {
        $this->loadReport();
    }
}
