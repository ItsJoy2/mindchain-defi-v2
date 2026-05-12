<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Welcome to Mindchain Ecosystem</title>

  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700" rel="stylesheet">

  <style>
    body {
      margin: 0;
      padding: 0;
      background: #f4f6f8;
      font-family: 'Montserrat', sans-serif;
    }

    .container {
      width: 100%;
      padding: 40px 0;
      background: #f4f6f8;
    }

    .email-box {
      max-width: 600px;
      margin: auto;
      background: #ffffff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }

    .header {
      text-align: center;
      padding: 30px;
      background: #ffffff;
    }

    .content {
      padding: 40px;
      color: #444;
      font-size: 14px;
      line-height: 22px;
    }

    .title {
      font-size: 22px;
      font-weight: 700;
      color: #ffa601;
      margin-bottom: 10px;
    }

    .highlight {
      font-size: 18px;
      font-weight: 600;
      color: #263238;
    }

    .info-box {
      background: #f9fafb;
      padding: 15px;
      border-radius: 6px;
      margin-top: 15px;
    }

    .footer {
      text-align: center;
      padding: 20px;
      font-size: 12px;
      color: #999;
    }

    .btn {
      display: inline-block;
      margin-top: 20px;
      margin:auto;
      padding: 12px 25px;
      background: #01379c;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      font-weight: 600;
    }
  </style>
</head>

<body>

<div class="container">
  <div class="email-box">

    <!-- Header -->
    <div class="header">
      <img src="https://api.mindchainwallet.com/logos/final-mindchain-logo-01.png" width="140" alt="Mindchain">
    </div>

    <!-- Content -->
    <div class="content">

      <div class="title">Welcome to Mindchain Ecosystem</div>

      <p class="highlight">Hello {{$data['user_name']}},</p>

      <p>
        Congratulations! Your account has been successfully created. You are now ready to explore the Mindchain ecosystem and start your journey.
      </p>

      <div class="info-box">
        <p><strong>User Name:</strong> {{$data['user_name']}}</p>
        <p><strong>Email:</strong> {{$data['email']}}</p>
      </div>

      <p>
        You can now access your dashboard, manage your account, and participate in the global marketplace anytime, anywhere.
      </p>

      <a href="https://mindchainwallet.com/" class="btn">Go to Dashboard</a>

      <p style="margin-top: 30px;">
        We’re excited to have you on board. If you need any support, feel free to reach out anytime.
      </p>

      <p>
        Thanks,<br>
        <strong>Mindchain Ecosystem Team</strong>
      </p>

    </div>

    <!-- Footer -->
    <div class="footer">
      © {{date('Y')}} Mindchain Ecosystem. All rights reserved.
    </div>

  </div>
</div>

</body>
</html>
