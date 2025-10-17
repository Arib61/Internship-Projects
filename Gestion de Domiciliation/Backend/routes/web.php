<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $mimeType = mime_content_type($filePath);
    
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => '*',
    ]);
})->where('path', '.*')->name('storage.file');

Route::get('/generate-arabic-pdf', function () {
    // Create new TCPDF instance with RTL config
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Your Application');
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('وثيقة عربية');
    $pdf->SetSubject('نموذج PDF بالعربية');

    // Set RTL and Arabic language support
    $pdf->setRTL(true);
    $pdf->setLanguageArray([
        'a_meta_charset' => 'UTF-8',
        'a_meta_dir' => 'rtl',
        'a_meta_language' => 'ar',
    ]);

    // Add a page
    $pdf->AddPage();

    // Set Arabic font (freeserif is included with TCPDF and supports Arabic)
    $pdf->SetFont('aealarabiya', '', 14);

    // Arabic text with connected letters
    $html = <<<HTML
    <h1 style="text-align:center;">مستند عربي</h1>
    <p style="text-align:right;">هذا مثال لنص عربي في ملف PDF مع اتصال الحروف بشكل صحيح.</p>
    <p style="text-align:right;">TCPDF يدعم اللغة العربية والاتجاه من اليمين لليسار بشكل ممتاز.</p>

    <table border="1" cellpadding="3" style="width:100%;text-align:right;">
        <tr>
            <th width="20%">الرقم</th>
            <th width="40%">الاسم</th>
            <th width="40%">الوصف</th>
        </tr>
        <tr>
            <td>١</td>
            <td>العنصر الأول</td>
            <td>وصف العنصر الأول باللغة العربية</td>
        </tr>
        <tr>
            <td>٢</td>
            <td>العنصر الثاني</td>
            <td>وصف العنصر الثاني باللغة العربية</td>
        </tr>
    </table>
    HTML;

    // Write HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    // Output the PDF
    return response($pdf->Output('arabic-document.pdf', 'I'), 200)
        ->header('Content-Type', 'application/pdf');
})->name('arabic.pdf');
