<?php

use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/payslip/{payslip}/pdf', function (Payslip $payslip) {
    $payslip->load(['employee.department', 'employee.designation', 'payrollRun', 'salaryStructure']);

    $pdf = Pdf::loadView('pdfs.payslip', [
        'payslip' => $payslip,
    ]);

    $filename = 'payslip-' . $payslip->employee->name . '-' . $payslip->payrollRun->label . '.pdf';
    $filename = preg_replace('/[^a-zA-Z0-9\-\_\.]/', '_', $filename);

    return $pdf->download($filename);
})->name('payslip.pdf');
