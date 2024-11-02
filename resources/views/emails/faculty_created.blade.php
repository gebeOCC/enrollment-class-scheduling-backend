<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Faculty Account Credentials</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 100vw;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            text-align: center;
        }

        p {
            font-size: 16px;
            color: #555;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #888;
        }

        .highlight {
            color: #007bff;
            font-weight: bold;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 20px auto;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Welcome to the Enrollment System!</h1>
        <p>Your faculty account has been created successfully.</p>
        <p><strong>User ID:</strong> <span class="highlight">{{ $userId }}</span></p>
        <p><strong>Password:</strong> <span class="highlight">{{ $password }}</span></p>
        <p>Please change your password after logging in for the first time.</p>
        <a href="https://occ.edu.ph" class="button">Login to Your Account</a>
        <p>If you have any issues logging in, please contact support.</p>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Opol Community College. All rights reserved.</p>
        </div>
    </div>
</body>

</html>