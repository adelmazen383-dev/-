<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز التحقق لاستعادة كلمة المرور</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:'Segoe UI', Tahoma, Arial, sans-serif; direction:rtl; text-align:right;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding:24px 28px; text-align:center;">
                            <div style="font-size:20px; font-weight:800; color:#ffffff; margin-bottom:4px;">بوابة مقدمي الخدمات</div>
                            <div style="font-size:12px; color:#94a3b8;">استعادة كلمة المرور عبر رمز التحقق الآمن (OTP)</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 12px; font-size:15px; font-weight:700; color:#0f172a;">
                                مرحباً {{ $provider->name }}،
                            </p>
                            <p style="margin:0 0 20px; font-size:14px; line-height:1.7; color:#475569;">
                                تلقّينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك
                                ({{ $provider->business_name }}).
                                استخدم رمز التحقق التالي لإتمام العملية:
                            </p>

                            <div style="background:#f8fafc; border:2px dashed #6366f1; border-radius:12px; padding:18px; text-align:center; margin:0 0 20px;">
                                <div style="font-size:11px; color:#64748b; margin-bottom:6px; font-weight:700;">رمز التحقق (OTP)</div>
                                <div style="font-size:34px; font-weight:900; letter-spacing:10px; color:#1e1b4b; font-family:monospace; direction:ltr; unicode-bidi:embed;">
                                    {{ $otpCode }}
                                </div>
                            </div>

                            <p style="margin:0 0 10px; font-size:13px; color:#334155;">
                                ⏱️ هذا الرمز صالح لمدة <strong>{{ $expiresInMinutes }} دقائق</strong> فقط ويُستخدم لمرة واحدة.
                            </p>
                            <p style="margin:0; font-size:12px; color:#94a3b8; line-height:1.6;">
                                إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذه الرسالة بأمان ولن يطرأ أي تغيير على حسابك. لا تشارك هذا الرمز مع أي شخص.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fafc; padding:14px 28px; text-align:center; font-size:11px; color:#94a3b8; border-top:1px solid #e2e8f0;">
                            رسالة آلية من منصة بوابة مقدمي الخدمات — يُرجى عدم الرد على هذا البريد
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
