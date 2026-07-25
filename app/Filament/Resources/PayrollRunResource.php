<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollRunResource\Pages;
use App\Models\PayrollRun;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class PayrollRunResource extends Resource
{
    protected static ?string $model = PayrollRun::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?string $navigationLabel = 'Payroll Runs';
    protected static ?string $pluralLabel = 'Payroll Runs';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('month')
                            ->options([
                                1 => 'January', 2 => 'February', 3 => 'March',
                                4 => 'April', 5 => 'May', 6 => 'June',
                                7 => 'July', 8 => 'August', 9 => 'September',
                                10 => 'October', 11 => 'November', 12 => 'December',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('year')
                            ->required()
                            ->numeric()
                            ->default(now()->year)
                            ->minValue(2020)
                            ->maxValue(2035),
                    ]),
                Forms\Components\Textarea::make('notes')
                    ->rows(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Period')
                    ->sortable(['year', 'month']),
                Tables\Columns\TextColumn::make('total_employees')
                    ->label('Employees')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_gross')
                    ->money('BDT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_deductions')
                    ->money('BDT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_net')
                    ->money('BDT')
                    ->sortable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'processing',
                        'success' => 'finalized',
                        'info' => 'paid',
                    ]),
                Tables\Columns\TextColumn::make('processed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('year', 'desc')
            ->defaultSort('month', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'processing' => 'Processing',
                        'finalized' => 'Finalized',
                        'paid' => 'Paid',
                    ]),
            ])
            ->actions([
                Action::make('process')
                    ->label('Process Payroll')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Process Payroll Run')
                    ->modalDescription('Generate payslips for all active employees with salary structures. Existing payslips will be replaced.')
                    ->action(function (PayrollRun $record) {
                        static::processPayroll($record);
                    })
                    ->visible(fn (PayrollRun $record) => $record->status === 'draft'),

                Action::make('finalize')
                    ->label('Finalize')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PayrollRun $record) {
                        $record->update(['status' => 'finalized']);
                        Notification::make()->title('Payroll finalized')->success()->send();
                    })
                    ->visible(fn (PayrollRun $record) => $record->status === 'processing'),

                Action::make('mark_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (PayrollRun $record) {
                        $record->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                        $record->payslips()->update(['status' => 'finalized']);
                        Notification::make()->title('Payroll marked as paid')->success()->send();
                    })
                    ->visible(fn (PayrollRun $record) => $record->status === 'finalized'),

                Action::make('view_payslips')
                    ->label('View Payslips')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (PayrollRun $record) => PayslipResource::getUrl('index', [
                        'tableFilters[payroll_run_id][value]' => $record->id,
                    ]))
                    ->visible(fn (PayrollRun $record) => $record->status !== 'draft'),

                Tables\Actions\EditAction::make()
                    ->visible(fn (PayrollRun $record) => $record->status === 'draft'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (PayrollRun $record) => $record->status === 'draft'),
            ])
            ->bulkActions([]);
    }

    public static function processPayroll(PayrollRun $run): void
    {
        $run->update(['status' => 'processing']);

        DB::transaction(function () use ($run) {
            // Get active employees with active salary structures
            $employees = \App\Models\User::where('is_active', true)
                ->whereHas('salaryStructure', fn ($q) => $q->where('is_active', true))
                ->with('salaryStructure')
                ->get();

            if ($employees->isEmpty()) {
                Notification::make()
                    ->title('No employees with active salary structures found')
                    ->warning()
                    ->send();
                $run->update(['status' => 'draft']);
                return;
            }

            // Delete existing payslips for this run
            $run->payslips()->delete();

            $totalGross = 0;
            $totalDeductions = 0;
            $totalNet = 0;
            $firstOfMonth = now()->month($run->month)->year($run->year)->startOfMonth();
            $endOfMonth = $firstOfMonth->copy()->endOfMonth();
            $workingDays = $endOfMonth->weekday() >= 5 ? 22 : $firstOfMonth->daysInMonth; // rough calc

            foreach ($employees as $employee) {
                $structure = $employee->salaryStructure->where('is_active', true)->first();
                if (!$structure) continue;

                $grossPay = (float) $structure->gross_salary;

                // Calculate unpaid leave days for this month
                $unpaidLeaveDays = (float) \App\Models\Leave::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->where('leave_type_id', \App\Models\LeaveType::where('code', 'LWP')->value('id'))
                    ->where('start_date', '<=', $endOfMonth)
                    ->where('end_date', '>=', $firstOfMonth)
                    ->sum('total_days');

                // Per-day salary deduction
                $perDaySalary = $workingDays > 0 ? $grossPay / $workingDays : 0;
                $unpaidDeduction = round($perDaySalary * $unpaidLeaveDays, 2);

                // Provident fund
                $pf = round((float) $structure->basic_salary * (float) $structure->provident_fund_percent / 100, 2);

                // Tax
                $tax = round($grossPay * (float) $structure->tax_percent / 100, 2);

                $totalDed = $pf + $tax + $unpaidDeduction;
                $netPay = $grossPay - $totalDed;

                $run->payslips()->create([
                    'employee_id' => $employee->id,
                    'salary_structure_id' => $structure->id,
                    'basic_salary' => $structure->basic_salary,
                    'house_rent_allowance' => $structure->house_rent_allowance,
                    'medical_allowance' => $structure->medical_allowance,
                    'conveyance_allowance' => $structure->conveyance_allowance,
                    'other_allowance' => $structure->other_allowance,
                    'gross_pay' => $grossPay,
                    'provident_fund' => $pf,
                    'tax' => $tax,
                    'other_deduction' => 0,
                    'total_deductions' => $totalDed,
                    'unpaid_leave_days' => $unpaidLeaveDays,
                    'unpaid_leave_deduction' => $unpaidDeduction,
                    'net_pay' => $netPay,
                    'status' => 'computed',
                ]);

                $totalGross += $grossPay;
                $totalDeductions += $totalDed;
                $totalNet += $netPay;
            }

            $run->update([
                'total_employees' => $employees->count(),
                'total_gross' => $totalGross,
                'total_deductions' => $totalDeductions,
                'total_net' => $totalNet,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);
        });

        $run->refresh();
        $count = $run->payslips()->count();

        Notification::make()
            ->title("Payroll processed — {$count} payslips generated")
            ->body("Gross: ৳" . number_format($run->total_gross, 2) . " | Net: ৳" . number_format($run->total_net, 2))
            ->success()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrollRuns::route('/'),
            'create' => Pages\CreatePayrollRun::route('/create'),
            'edit' => Pages\EditPayrollRun::route('/{record}/edit'),
        ];
    }
}
