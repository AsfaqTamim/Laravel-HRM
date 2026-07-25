<?php

namespace App\Filament\Resources\PayslipResource\Pages;

use App\Filament\Resources\PayslipResource;
use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Response;

class ViewPayslip extends EditRecord
{
    protected static string $resource = PayslipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print / PDF')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn () => route('payslip.pdf', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('finalize')
                ->label('Finalize Payslip')
                ->icon('heroicon-o-lock-closed')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'finalized']);
                    Notification::make()->title('Payslip finalized')->success()->send();
                })
                ->visible(fn () => $this->record->status === 'computed'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['payroll_run.label'] = $this->record->payrollRun->label ?? '-';
        $data['employee.name'] = $this->record->employee->name ?? '-';
        return $data;
    }
}
