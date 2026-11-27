@php
    $d = $invoice->service_details ?? [];
    $rows = [
        'اسم العميل'          => $invoice->customer_name,
        'رقم الجوال'          => $invoice->customer_phone,
        'نوع التصوير'         => $d['session_type'] ?? null,
        'الباقة المطلوبة'     => $invoice->title,
        'موقع التصوير'        => $d['location'] ?? null,
        'تاريخ التصوير'       => $d['session_date'] ?? null,
        'وقت الحضور'          => $d['arrival_time'] ?? null,
        'مدة التصوير'         => $d['duration_hours'] ?? null,
        'اسم المصور المسؤول'  => $d['responsible_name'] ?? $provider->name,
    ];
@endphp

@include('provider.invoices.templates._luxury-base', [
    'invoice'          => $invoice,
    'provider'         => $provider,
    'sectionBadge'     => 'قسم المصورين',
    'sectionTitle'     => 'بيانات العميل وجلسة التصوير',
    'agreedLabel'      => 'السعر المتفق عليه',
    'rows'             => $rows,
    'showMethodsStrip' => $showMethodsStrip ?? true,
    'attachmentUrl'    => $attachmentUrl ?? null,
])
