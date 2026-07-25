<?php

use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/payslip/{payslip}/pdf', function (Payslip $payslip) {
    $pdf = Pdf::loadView('pdfs.payslip', ['payslip' => $payslip]);
    return $pdf->download("payslip-{$payslip->employee->employee_code}-{$payslip->payrollRun->label}.pdf");
})->name('payslip.pdf');
