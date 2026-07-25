<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayslipResource\Pages;
use App\Models\Payslip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class PayslipResource extends Resource
{
    protected static ?string $model = Payslip::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?string $navigationLabel = 'Payslips';
    protected static ?string $pluralLabel = 'Payslips';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Employees see only their own payslips; admins see all
        if (! auth()->user()?->hasRole('super_admin')) {
            $query->where('employee_id', auth()->id());
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $isAdmin = auth()->user()?->hasRole('super_admin');

        return $form
            ->schema([
                Forms\Components\Section::make('Employee Info')
                    ->schema([
                        Forms\Components\TextInput::make('employee.name')->label('Employee')->disabled(),
                        Forms\Components\TextInput::make('payroll_run.label')->label('Period')->disabled(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Earnings')
                    ->schema([
                        Forms\Components\TextInput::make('basic_salary')->prefix('৳')->disabled(),
                        Forms\Components\TextInput::make('house_rent_allowance')->prefix('৳')->disabled(),
                        Forms\Components\TextInput::make('medical_allowance')->prefix('৳')->disabled(),
                        Forms\Components\TextInput::make('conveyance_allowance')->prefix('৳')->disabled(),
                        Forms\Components\TextInput::make('other_allowance')->prefix('৳')->disabled(),
                        Forms\Components\TextInput::make('gross_pay')->prefix('৳')->disabled()->extraAttributes(['class' => 'font-bold']),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Deductions')
                    ->schema([
                        Forms\Components\TextInput::make('provident_fund')->prefix('৳')->disabled(),
                        Forms\Components\TextInput::make('tax')->prefix('৳')->disabled(),
                        Forms\Components\TextInput::make('other_deduction')->prefix('৳')->disabled(),
                        Forms\Components\TextInput::make('unpaid_leave_days')->suffix(' days')->disabled(),
                        Forms\Components\TextInput::make('unpaid_leave_deduction')->prefix('৳')->disabled(),
                        Forms\Components\TextInput::make('total_deductions')->prefix('৳')->disabled()->extraAttributes(['class' => 'font-bold text-danger-600']),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Net Pay')
                    ->schema([
                        Forms\Components\TextInput::make('net_pay')->prefix('৳')->disabled()->extraAttributes(['class' => 'text-2xl font-bold text-success-600']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isAdmin = auth()->user()?->hasRole('super_admin');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payroll_run.label')
                    ->label('Period')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gross_pay')
                    ->money('BDT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_deductions')
                    ->money('BDT')
                    ->sortable()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('net_pay')
                    ->money('BDT')
                    ->sortable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('payroll_run_id')
                    ->label('Payroll Run')
                    ->relationship('payrollRun', 'id')
                    ->getOptionLabelFromRecordUsing(fn (\App\Models\PayrollRun $record): string => $record->label)
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['computed' => 'Computed', 'finalized' => 'Finalized']),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Payslip $record) => PayslipResource::getUrl('edit', ['record' => $record])),
                Action::make('finalize')
                    ->label('Finalize')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Payslip $record) {
                        $record->update(['status' => 'finalized']);
                        \Illuminate\Support\Facades\Mail::to($record->employee->email)
                            ->queue(new \App\Mail\PayslipGeneratedMail($record));
                        Notification::make()->title('Payslip finalized')->success()->send();
                    })
                    ->visible(fn (Payslip $record) => $isAdmin && $record->status === 'computed'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayslips::route('/'),
            'edit' => Pages\ViewPayslip::route('/{record}/edit'),
        ];
    }
}
