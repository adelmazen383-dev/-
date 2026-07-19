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
<table style="width: 100%; margin-bottom: 30px; border-bottom: 2px solid #254a34; padding-bottom: 15px;">
    <tr>
        <td style="width: 60%; vertical-align: top;">
            <div style="font-size: 28px; font-weight: bold; color: #254a34; margin-bottom: 5px;">عقد تأجير عقار</div>
            <div style="color: #64748b; font-size: 14px;">فاتورة ومسودة عقد / Contract Agreement</div>
        </td>
        <td style="width: 40%; text-align: left; vertical-align: top;">
            <img src="{{LOGO_BASE64}}" style="max-height: 80px;" />
        </td>
    </tr>
</table>

<table style="width: 100%; margin-bottom: 30px;">
    <tr>
        <td style="width: 33%; vertical-align: top;">
            <div style="font-size: 11px; color: #64748b; font-weight: bold; margin-bottom: 5px;">تفاصيل العقد</div>
            <div style="font-size: 14px; font-weight: bold; color: #1e293b;">رقم العقد: <span style="font-weight:normal;">{{CONTRACT_NUMBER}}</span></div>
            <div style="font-size: 14px; font-weight: bold; color: #1e293b; margin-top: 3px;">تاريخ الإصدار: <span style="font-weight:normal;">{{DATE}}</span></div>
        </td>
        <td style="width: 33%; vertical-align: top;">
            <div style="font-size: 11px; color: #64748b; font-weight: bold; margin-bottom: 5px;">الطرف الأول (المالك)</div>
            <div style="font-size: 15px; font-weight: bold; color: #254a34;">إدارة الأملاك</div>
        </td>
        <td style="width: 33%; vertical-align: top;">
            <div style="font-size: 11px; color: #64748b; font-weight: bold; margin-bottom: 5px;">الطرف الثاني (المستأجر)</div>
            <div style="font-size: 15px; font-weight: bold; color: #254a34;">{{CUSTOMER_NAME}}</div>
            <div style="font-size: 13px; color: #64748b; margin-top: 3px;">هوية رقم: {{NATIONAL_ID}}</div>
        </td>
    </tr>
</table>

<table class="invoice-table">
    <thead>
        <tr>
            <th style="width: 40%;">وصف العقار</th>
            <th style="width: 30%;">الفترة الإيجارية</th>
            <th style="width: 30%;">رقم الرخصة السياحية</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <div style="font-weight: bold; color: #1e293b;">شقة سكنية</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 3px;">{{PROPERTY_DETAILS}}</div>
            </td>
            <td>
                من: {{START_DATE}}<br>
                إلى: {{END_DATE}}
            </td>
            <td>{{TOURISM_LICENSE}}</td>
        </tr>
    </tbody>
</table>

<div class="total-section">
    <table style="width: 100%;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px; font-weight: bold; color: #475569; text-align: right; border-bottom: 1px solid #e2e8f0;">القيمة الإيجارية</td>
                        <td style="padding: 10px; font-weight: bold; color: #1e293b; text-align: left; border-bottom: 1px solid #e2e8f0;">{{RENT_AMOUNT}} ريال</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 10px; font-weight: bold; color: #254a34; font-size: 18px; text-align: right;">الإجمالي المتفق عليه</td>
                        <td style="padding: 12px 10px; font-weight: bold; color: #254a34; font-size: 18px; text-align: left;">{{RENT_AMOUNT}} ريال</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div style="margin-top: 40px; page-break-inside: avoid;">
    <div style="font-size: 14px; font-weight: bold; color: #254a34; margin-bottom: 10px; border-bottom: 1px solid #254a34; padding-bottom: 5px; width: max-content;">الشروط الإضافية والملاحظات</div>
    <div class="terms">
        {{ADDITIONAL_TERMS}}
    </div>
</div>
            ',
            'is_active' => true
        ]);
    }
}
