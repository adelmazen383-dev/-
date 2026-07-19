<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ContractTemplate;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'مدير النظام',
            'password' => bcrypt('password'),
        ]);

        // 2. Default Contract Template
        ContractTemplate::firstOrCreate([
            'name' => 'عقد تأجير شقق',
        ], [
'html_content' => '
<div style="background-color: #14532d; color: #ffffff; padding: 10px; text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 20px;">
    رقم ترخيص السياحة للمؤجر : {{TOURISM_LICENSE}}
</div>

<table style="width: 100%; margin-bottom: 20px; table-layout: fixed;">
    <tr>
        <td style="width: 33%; vertical-align: middle; padding-left: 10px;">
            <div style="background-color: #16a34a; color: #ffffff; padding: 8px 12px; border-radius: 4px; font-weight: bold; font-size: 13px; text-align: center;">
                رقم الشقة : {{PROPERTY_DETAILS}}
            </div>
        </td>
        <td style="width: 34%; text-align: center; vertical-align: middle;">
            {{LOGO_HTML}}
        </td>
        <td style="width: 33%; vertical-align: middle; padding-right: 10px;">
            <div style="background-color: #16a34a; color: #ffffff; padding: 8px 12px; border-radius: 4px; font-weight: bold; font-size: 13px; text-align: center;">
                رقم العقد : {{CONTRACT_NUMBER}}
            </div>
        </td>
    </tr>
</table>

<div style="border-top: 3px solid #16a34a; margin-bottom: 20px;"></div>

<div style="font-size: 14px; line-height: 1.8; color: #1f2937; margin-bottom: 30px; text-align: justify;">
    تم عمل هذا العقد في يوم <strong>{{DATE}}</strong> بين كل من الطرف الأول (المؤجر) <strong>{{LESSOR_NAME}}</strong> (هوية: <strong>{{LESSOR_ID}}</strong>)، 
    والطرف الثاني (المستأجر) <strong>{{CUSTOMER_NAME}}</strong> (هوية: <strong>{{NATIONAL_ID}}</strong>).
</div>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
    <tr>
        <td style="padding: 10px 0;">
            <div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الأول</div>
            <div style="font-size: 14px; text-align: right; line-height: 1.8;">تأجير الشقة المذكورة أعلاه لغرض السكن.</div>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0;">
            <div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الثاني</div>
            <div style="font-size: 14px; text-align: right; line-height: 1.8;">مدة الإيجار من <strong>{{START_DATE}}</strong> إلى <strong>{{END_DATE}}</strong>، تجديد الإقامة قبل الساعة 12 ظهراً، وفي حالة التأخير تفرض رسوم نصف يوم.</div>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0;">
            <div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الثالث</div>
            <div style="font-size: 14px; text-align: right; line-height: 1.8;">إجمالي مبلغ الإيجار <strong>{{RENT_AMOUNT}} ريال</strong>، يُدفع قبل الإقامة.</div>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0;">
            <div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الرابع</div>
            <div style="font-size: 14px; text-align: right; line-height: 1.8;">مبلغ تأمين <strong>{{DEPOSIT_AMOUNT}} ريال</strong> يرد عند التسليم لو الشقة سليمة من أي عيوب أو تكسير.</div>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0;">
            <div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الخامس</div>
            <div style="font-size: 14px; text-align: right; line-height: 1.8;">الالتزام بمراعاة لائحة الذوق العام وعدم التسبب لأي مضايقة للجيران برفع الصوت أو أي فعل يمس براحة وخصوصية الجيران.</div>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0;">
            <div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند السادس</div>
            <div style="font-size: 14px; text-align: right; line-height: 1.8;">يلتزم المستأجر بدفع كامل فترة الإقامة قبل تسجيل الوصول.</div>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0;">
            <div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند السابع</div>
            <div style="font-size: 14px; text-align: right; line-height: 1.8;">المبلغ المدفوع لا يسترد نهائياً للحجز أو الإقامة.</div>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0;">
            <div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الثامن</div>
            <div style="font-size: 14px; text-align: right; line-height: 1.8;">يمنع التأجير من الباطن بدون موافقة خطية من المؤجر وإلا اعتبر العقد ملغياً.</div>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0;">
            <div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند التاسع</div>
            <div style="font-size: 14px; text-align: right; line-height: 1.8;">الالتزام باستخدام الإنترنت بشكل نظامي وعدم استخدامه لأي أنشطة مخالفة لأنظمة المملكة العربية السعودية.</div>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0;">
            <div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند العاشر</div>
            <div style="font-size: 14px; text-align: right; line-height: 1.8;">موعد تسجيل الوصول 3 مساءً وتسجيل الخروج 12 مساءً.</div>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0;">
            <div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الحادي عشر</div>
            <div style="font-size: 14px; text-align: right; line-height: 1.8;">يحق للمؤجر فسخ العقد وإخلاء الشقة فوراً دون تعويض عند إخلال المستأجر بأي بند من بنود هذا العقد.</div>
        </td>
    </tr>
</table>

<div style="text-align: center; font-weight: bold; color: #14532d; font-size: 16px; margin-bottom: 8px;">شروط إضافية</div>
<div style="background-color: #f9fafb; padding: 15px; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 14px; line-height: 1.8; color: #1f2937; margin-bottom: 20px; text-align: right;">
    {{ADDITIONAL_TERMS}}
</div>

<div style="border-top: 3px solid #16a34a; margin-bottom: 20px;"></div>

<table style="width: 100%; border-collapse: separate; border-spacing: 20px 0;">
    <tr>
        <td style="width: 50%; background-color: #f0fdf4; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #bbf7d0;">
            <div style="color: #14532d; font-weight: bold; margin-bottom: 30px;">توقيع الطرف الأول (المؤجر)</div>
            {{LESSOR_SIGNATURE}}
        </td>
        <td style="width: 50%; background-color: #f0fdf4; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #bbf7d0;">
            <div style="color: #14532d; font-weight: bold; margin-bottom: 30px;">توقيع الطرف الثاني (المستأجر)</div>
            {{LESSEE_SIGNATURE}}
        </td>
    </tr>
</table>',
            'is_active' => true
        ]);
    }
}
