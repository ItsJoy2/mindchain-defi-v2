<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f9; font-family:Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
        <tr>
            <td align="center">

                <!-- Main Card -->
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#4f46e5; padding:30px; text-align:center; color:#ffffff;">
                            <h2 style="margin:0;">Welcome to Mindchain Ecosystem</h2>
                            <p style="margin:5px 0 0; font-size:14px;">Please verify your email to continue</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:40px; text-align:center;">

                            <h1 style="color:#111827; font-size:22px; margin-bottom:10px;">
                                Email Verification
                            </h1>

                            <p style="color:#6b7280; font-size:15px; line-height:1.6;">
                                Thank you for registering. To complete your signup process, please verify your email address by clicking the button below.
                            </p>

                            <!-- Button -->
                            <div style="margin:30px 0;">
                                <a href="https://mindchainwallet.com/auth/verify-user?token={{ $token }}"
                                   style="background:#4f46e5; color:#ffffff; padding:14px 28px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block;">
                                    Verify Email
                                </a>
                            </div>

                            {{-- <p style="color:#9ca3af; font-size:13px;">
                                If the button doesn't work, copy and paste the link below:
                            </p>

                            <p style="font-size:12px; color:#6366f1; word-break:break-all;">
                                https://mindchainwallet.com/auth/verify-user?token={{ $token }}
                            </p> --}}

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9fafb; text-align:center; padding:20px; font-size:12px; color:#9ca3af;">
                            © {{ date('Y') }} Mindchain Ecosystem. All rights reserved.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
