@php
    $d = $invoice->service_details ?? [];
    $rows = [
        'اسم العميل' =>$invoice->customer_name,
        'رقم الجوال' =>$invoice->customer_phone,
        'رقم الهوية' =>$invoice->customer_national_id,
        'نوع الخدمة' =>$invoice->title,
        'المسؤول' =>$d['responsible_name'] ?? $provider->name,
        'المنطقة' =>$d['region'] ?? $provider->city,
        'مكان الموقع' =>$d['venue'] ?? null,
        'تاريخ المناسبة' =>$d['event_date'] ?? null,
        'وقت المناسبة' =>$d['event_time'] ?? null,
    ];
@endphp

@include('provider.invoices.templates._luxury-base', [
    'invoice' =>$invoice,
    'provider' =>$provider,
    'sectionBadge' =>'قسم التنسيق والمناسبات',
    'sectionTitle' =>'بيانات العميل والمناسبة',
    'agreedLabel' =>'المبلغ المتفق عليه',
    'rows' =>$rows,
    'showMethodsStrip' =>$showMethodsStrip ?? true,
    'attachmentUrl' =>$attachmentUrl ?? null,
])
