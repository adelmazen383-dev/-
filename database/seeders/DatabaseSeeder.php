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
            'name' => 'عقد إيجار سكني افتراضي',
        ], [
            'html_content' => '
                <h1 style="text-align: center;">عقد إيجار عقار</h1>
                <p>إنه في يوم <strong>{{START_DATE}}</strong> تم الاتفاق بين كل من:</p>
                <p>الطرف الأول (المؤجر): <strong>إدارة الأملاك</strong></p>
                <p>الطرف الثاني (المستأجر): <strong>{{CUSTOMER_NAME}}</strong> يحمل هوية رقم <strong>{{NATIONAL_ID}}</strong></p>
                
                <h3>البند الأول (العين المؤجرة)</h3>
                <p>يقر الطرف الأول بأنه أجر للطرف الثاني العقار التالي: <strong>{{PROPERTY_DETAILS}}</strong> بغرض السكن.</p>
                
                <h3>البند الثاني (مدة العقد)</h3>
                <p>مدة هذا العقد تبدأ من <strong>{{START_DATE}}</strong> وتنتهي في <strong>{{END_DATE}}</strong>.</p>
                
                <h3>البند الثالث (القيمة الإيجارية)</h3>
                <p>تم الاتفاق على أن تكون القيمة الإيجارية مبلغ <strong>{{RENT_AMOUNT}}</strong> ريال سعودي تدفع بشكل <strong>{{PAYMENT_METHOD}}</strong>.</p>
                
                <h3>شروط إضافية</h3>
                <p>{{ADDITIONAL_TERMS}}</p>
            ',
            'is_active' => true
        ]);
    }
}
