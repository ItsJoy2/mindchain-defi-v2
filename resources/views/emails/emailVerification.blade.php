<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Notification' }}</title>
</head>

<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
    <tr>
        <td align="center">

            <!-- Main Card -->
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                <!-- Header -->
                <tr>
                    <td style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:32px;text-align:center;color:#fff;">
                        <h2 style="margin:0;font-size:22px;letter-spacing:0.5px;">
                            Mindchain Ecosystem
                        </h2>
                        <p style="margin:6px 0 0;font-size:13px;opacity:1;">
                            {{ $subHeader ?? 'Secure Notification Center' }}
                        </p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:40px 35px;text-align:center;color:#111827;">

                        <!-- Heading -->
                        <h1 style="font-size:22px;margin-bottom:12px;font-weight:700;">
                            {{ $heading }}
                        </h1>

                        <!-- Message -->
                        <p style="color:#6b7280;font-size:15px;line-height:1.7;margin-bottom:25px;">
                            {{ $messageText }}
                        </p>

                        <!-- Button -->
                        @if(!empty($buttonUrl))
                        <div style="margin:35px 0;">
                            <a href="{{ $buttonUrl }}"
                               style="background:{{ $buttonColor ?? '#4f46e5' }};
                                      color:#ffffff;
                                      padding:14px 30px;
                                      text-decoration:none;
                                      border-radius:10px;
                                      font-weight:600;
                                      font-size:15px;
                                      display:inline-block;
                                      box-shadow:0 5px 15px rgba(79,70,229,0.3);">
                                {{ $buttonText ?? 'Continue' }}
                            </a>
                        </div>
                        @endif

                        <!-- Security Notice Box -->
                        <div style="background:#f9fafb;border:1px solid #e5e7eb;padding:15px;border-radius:10px;margin-top:20px;">
                            <p style="font-size:13px;color:#6b7280;line-height:1.6;margin:0;">
                            This link is secure and will expire automatically after a limited time for your protection.
                            </p>
                        </div>

                        <!-- Extra Text -->
                        @if(!empty($extraText))
                        <p style="margin-top:20px;font-size:13px;color:#9ca3af;line-height:1.6;">
                            {{ $extraText }}
                        </p>
                        @endif

                        <!-- Footer Signature -->
                        <p style="margin-top:35px;font-size:14px;color:#374151;">
                            Best regards,<br>
                            <strong>Mindchain Security Team</strong>
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f9fafb;text-align:center;padding:18px;font-size:12px;color:#9ca3af;">
                        © {{ date('Y') }} Mindchain Ecosystem. All rights reserved.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
