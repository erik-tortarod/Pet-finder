# Pet-finder

A web platform to help reunite lost pets with their families and locate stray animals.

## 🚀 Quick Start

```bash
make install-dependencies  # First time only
make install
make db
make run
```

## 🌐 Access

-   **App:** http://localhost:8000
-   **DB:** localhost:3308 (user: `petfinder`, pass: `petfinder`)

## 🛠️ Stack

-   Symfony 7 (PHP 8.3)
-   Twig, JavaScript, Tailwind CSS
-   MySQL 8.0
-   Node.js 18.x

## ⚙️ Configuration

### OAuth2 & Email Setup

1. **Google OAuth2**: Create OAuth2 credentials in [Google Cloud Console](https://console.cloud.google.com/)
    - **Authorized redirect URI**: `http://localhost:8000/login/with-google`
    - **Authorized JavaScript origins**: `http://localhost:8000`
2. **Email Credentials**: Generate app passwords at [Google App Passwords](https://myaccount.google.com/apppasswords)
3. **Telegram Bot**: Create a bot with [@BotFather](https://t.me/botfather) and get credentials
    - Send `/newbot` to BotFather
    - Get your bot token
    - Send a message to your bot and get your chat ID from [@userinfobot](https://t.me/userinfobot)
4. **Configure your `.env.local`** with the credentials:

    ```bash
    # OAuth2
    GOOGLE_CLIENT_ID=your_client_id
    GOOGLE_CLIENT_SECRET=your_client_secret

    # Email
    MAIL_SENDER=your_email@gmail.com
    MAILER_DSN=smtp://your_email:your_app_password@smtp.gmail.com:587

    # Telegram
    TELEGRAM_BOT_TOKEN=your_bot_token_from_botfather
    TELEGRAM_CHAT_ID=your_chat_id
    ```

## 📧 Automatic Email Reminders

To set up automatic email reminders for checking if publications are still active:

```bash
# Test manually
cd projects/web && php bin/console app:send-reminders

# Set up crontab (daily at 9 AM)
crontab -e
0 9 * * * cd /path/to/project/projects/web && php bin/console app:send-reminders >> var/log/reminders.log 2>&1
```

## 🙏 Acknowledgments

<div align="center">
  <img src="docs/aircury_logo.svg" alt="Aircury Logo" width="150" style="margin-bottom: 20px;">
  
  **Developed as part of Aircury's Summer of Code**
  
  Special thanks to Aircury SL for providing this amazing opportunity to contribute to the open-source community and work on meaningful projects that help reunite lost pets with their families.
</div>
