<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - ACHILLES</title>
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
            <tr>
                <td style="background:#111111; padding:30px; text-align:center;">

                    <h1 style="margin:0; color:#ffffff; font-size:28px; letter-spacing:3px;">
                        ACHILLES
                    </h1>

                    <p style="margin:8px 0 0; color:#e5e5e5; font-size:12px;">
                        WEAR YOUR WEAKNESS
                    </p>

                </td>
            </tr>

            <!-- RED ACCENT -->
            <tr>
                <td style="height:5px; background:#dc2626;"></td>
            </tr>

            <!-- CONTENT -->
            <tr>
                <td style="padding:40px 35px; color:#27272a;">

                    <h2 style="margin-top:0; font-size:24px; color:#18181b;">
                        Welcome to ACHILLES!
                    </h2>

                    <p style="font-size:15px; line-height:1.7;">
                        Thank you for creating an account with us.
                    </p>

                    <p style="font-size:15px; line-height:1.7;">
                        Please verify your email address by clicking the button below.
                        This helps us secure your account and activate your access to ACHILLES.
                    </p>

                    <!-- BUTTON -->
                    <div style="text-align:center; margin:35px 0;">

                        <a href="{{ $verificationUrl }}"
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
                            VERIFY EMAIL ADDRESS
                        </a>

                    </div>

                    <p style="font-size:14px; line-height:1.7; color:#52525b;">
                        If you did not create an ACHILLES account, you can safely ignore this email.
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