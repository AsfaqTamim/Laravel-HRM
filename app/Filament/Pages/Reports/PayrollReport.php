<?php

namespace App\Filament\Pages\Reports;

use App\Models\PayrollRun;
use App\Models\Payslip;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class PayrollReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?string $navigationLabel = 'Report';
    protected static ?int $navigationSort = 4;

    public ?int $payroll_run_id = null;

    public function mount(): void
    {
        $latest = PayrollRun::latest()->first();
        $this->payroll_run_id = $latest?->id;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payslip::query()
                    ->when($this->payroll_run_id, fn ($q) => $q->where('payroll_run_id', $this->payroll_run_id))
                    ->with('employee', 'payrollRun')
            )
            ->columns([
                TextColumn::make('payrollRun.label')
                    ->label('Period')
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.employee_code')
                    ->label('Code')
                    ->searchable(),
                TextColumn::make('gross_pay')
                    ->money('BDT')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('total_deductions')
                    ->label('Deductions')
                    ->money('BDT')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('net_pay')
                    ->money('BDT')
                    ->sortable()
                    ->alignEnd()
                    ->color('success'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('employee.name')
            ->striped();
    }

    public function getView(): string
    {
        return 'filament.pages.reports.payroll-report';
    }

    public function getTitle(): string
    {
        $run = $this->payroll_run_id ? PayrollRun::find($this->payroll_run_id) : null;
        return 'Payroll Report' . ($run ? " — {$run->label}" : '');
    }

    public static function getNavigationLabel(): string
    {
        return 'Payroll';
    }
}
