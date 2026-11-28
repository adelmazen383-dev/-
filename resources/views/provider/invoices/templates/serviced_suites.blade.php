@php
    $d = $invoice->service_details ?? [];
    $rows = [
        'اسم المستأجر' =>$invoice->customer_name,
        'رقم الجوال' =>$invoice->customer_phone,
        'رقم الهوية' =>$invoice->customer_national_id,
        'نوع العقار / الحجز' =>$invoice->title,
        'رقم الدور' =>$d['floor_number'] ?? null,
        'رقم الشقة / الجناح' =>$d['suite_number'] ?? null,
        'تاريخ الدخول' =>$d['check_in_date'] ?? null,
        'تاريخ الخروج' =>$d['check_out_date'] ?? null,
        'عدد الليالي' =>!empty($d['nights']) ? $d['nights'] . ' ليلة' : null,
        'عدد الأشخاص' =>!empty($d['guests_count']) ? $d['guests_count'] . ' أشخاص' : null,
        'المسؤول / المنشأة' =>$provider->business_name,
    ];
@endphp

@include('provider.invoices.templates._luxury-base', [
    'invoice' =>$invoice,
    'provider' =>$provider,
    'sectionBadge' =>$provider->property_kind ?: 'قسم الشقق والأجنحة الفندقية',
    'sectionTitle' =>'بيانات المستأجر والحجز',
    'agreedLabel' =>'قيمة الحجز',
    'rows' =>$rows,
    'showMethodsStrip' =>$showMethodsStrip ?? true,
    'attachmentUrl' =>$attachmentUrl ?? null,
])
