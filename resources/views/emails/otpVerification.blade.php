<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'OTP Verification' }}</title>
</head>

<body style="margin:0; padding:0; background:#f4f7fb; font-family:Arial, Helvetica, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 15px;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="
                        background:#ffffff;
                        border-radius:14px;
                        overflow:hidden;
                        box-shadow:0 8px 25px rgba(0,0,0,0.08);
                    ">

                    <!-- Header -->
                    <tr>
                        <td align="center"
                            style="
                                background:linear-gradient(135deg,#4f46e5,#7c3aed);
                                padding:40px 25px;
                            ">

                            <h1 style="
                                    margin:0;
                                    color:#ffffff;
                                    font-size:30px;
                                    font-weight:700;
                                ">
                                {{ $title ?? 'OTP Verification' }}
                            </h1>

                            <p style="
                                    margin-top:12px;
                                    color:#e0e7ff;
                                    font-size:15px;
                                    line-height:24px;
                                ">
                                {{ $subtitle ?? 'Secure verification process' }}
                            </p>

                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:45px 35px;">

                            <h2 style="
                                    margin-top:0;
                                    color:#111827;
                                    font-size:24px;
                                ">
                                Hello 👋
                            </h2>

                            <p style="
                                    font-size:16px;
                                    line-height:30px;
                                    color:#4b5563;
                                    margin-top:20px;
                                ">
                                {{ $messageText ?? 'Use the verification code below to continue securely.' }}
                            </p>

                            <!-- OTP BOX -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:40px 0;">
                                <tr>
                                    <td align="center">

                                        <div style="
                                                display:inline-block;
                                                padding:20px 45px;
                                                background:#eef2ff;
                                                border:2px dashed #6366f1;
                                                border-radius:14px;
                                                color:#4338ca;
                                                font-size:42px;
                                                font-weight:bold;
                                                letter-spacing:12px;
                                            ">
                                            {{ $otp }}
                                        </div>

                                    </td>
                                </tr>
                            </table>

                            <p style="
                                    font-size:15px;
                                    line-height:28px;
                                    color:#6b7280;
                                ">
                                This OTP is valid for a 15 minute period and can only be used once.
                            </p>

                            <p style="
                                    font-size:15px;
                                    line-height:28px;
                                    color:#6b7280;
                                ">
                                Never share your OTP with anyone for security reasons.
                            </p>

                            <hr style="
                                    border:none;
                                    border-top:1px solid #e5e7eb;
                                    margin:40px 0;
                                ">

                            <p style="
                                    font-size:14px;
                                    color:#9ca3af;
                                    text-align:center;
                                    margin:0;
                                ">
                                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
