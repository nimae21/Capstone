<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - ACHILLES</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f4f5; font-family:Arial, Helvetica, sans-serif;">

    <div style="padding:40px 15px;">

        <table role="presentation"
               width="100%"
               cellspacing="0"
               cellpadding="0"
               border="0"
               style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:16px; overflow:hidden;">

            <!-- HEADER -->
            

            <!-- RED ACCENT -->
            <tr>
                <td style="height:5px; background:#dc2626;"></td>
            </tr>

            <!-- CONTENT -->
            <tr>
                <td style="padding:40px 35px; color:#27272a;">

                    <h2 style="margin-top:0; font-size:24px; color:#18181b;">
                        Reset Your Password
                    </h2>

                    <p style="font-size:15px; line-height:1.7;">
                        We received a request to reset the password for your ACHILLES account.
                    </p>

                    <p style="font-size:15px; line-height:1.7;">
                        Click the button below to create a new password.
                    </p>

                    <!-- BUTTON -->
                    <div style="text-align:center; margin:35px 0;">

                        <a href="{{ $resetUrl }}"
                           style="
                               display:inline-block;
                               padding:14px 28px;
                               background:#dc2626;
                               color:#ffffff;
                               text-decoration:none;
                               font-weight:bold;
                               border-radius:8px;
                               font-size:15px;
                           ">
                            RESET PASSWORD
                        </a>

                    </div>

                    <p style="font-size:14px; line-height:1.7; color:#52525b;">
                        This password reset link will expire in 60 minutes.
                    </p>

                    <p style="font-size:14px; line-height:1.7; color:#52525b;">
                        If you did not request a password reset, no further action is required.
                    </p>

                    <p style="font-size:14px; line-height:1.7; color:#52525b; margin-bottom:0;">
                        Regards,<br>
                        <strong>ACHILLES Wear Your Weakness</strong>
                    </p>

                </td>
            </tr>

            <!-- FOOTER -->
            <tr>
                <td style="background:#f4f4f5; padding:20px; text-align:center; color:#71717a; font-size:12px;">

                    © {{ date('Y') }} ACHILLES Wear Your Weakness.<br>
                    All rights reserved.

                </td>
            </tr>

        </table>

    </div>

</body>
</html>