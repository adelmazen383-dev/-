@php
    $d = $invoice->service_details ?? [];
    $customType = $d['custom_service_type'] ?? ($provider->custom_service_name ?: 'خدمة مخصصة');
    $rows = [
        'اسم العميل'    => $invoice->customer_name,
        'رقم الجوال'    => $invoice->customer_phone,
        'رقم الهوية'    => $invoice->customer_national_id,
        'نوع الخدمة'    => $invoice->title ?: $customType,
        'المسؤول'       => $d['responsible_name'] ?? $provider->name,
        'تاريخ الخدمة'  => $d['execution_date'] ?? null,
        'الموقع'        => $d['location'] ?? $provider->city,
    ];
@endphp

@include('provider.invoices.templates._luxury-base', [
    'invoice'          => $invoice,
    'provider'         => $provider,
    'sectionBadge'     => $customType,
    'sectionTitle'     => 'بيانات العميل والخدمة',
    'agreedLabel'      => 'المبلغ المتفق عليه',
    'rows'             => $rows,
    'showMethodsStrip' => $showMethodsStrip ?? true,
    'attachmentUrl'    => $attachmentUrl ?? null,
])
