# 🚀 AI-Powered SaaS Boilerplate

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3.x-green?style=for-the-badge&logo=vue.js)](https://vuejs.org)
[![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)](LICENSE)
[![OpenAI](https://img.shields.io/badge/OpenAI-GPT--4-black?style=for-the-badge&logo=openai)](https://openai.com)

> A production-ready, multi-tenant SaaS starter kit built with **Laravel 11**, **Vue 3**, **Stripe**, and **OpenAI**. Launch your SaaS product in days, not months.

---

## 🎥 Live Demo
🔗 **[View Live Demo](https://your-demo-link.com)**  
📸 **[Screenshots](docs/screenshots/)**

---

## ✨ Features

### 🏢 Multi-Tenancy
- Each team/organization has isolated data
- Subdomain-based tenant routing
- Tenant-aware database queries

### 🔐 Authentication & Security
- Email/Password registration & login
- Google OAuth (Socialite)
- Two-Factor Authentication (2FA)
- Email verification
- Rate limiting on all API endpoints

### 💳 Billing & Subscriptions
- Stripe integration via Laravel Cashier
- Multiple subscription plans (Free, Pro, Enterprise)
- Usage-based billing support
- Invoice generation & download
- Webhook handling

### 🤖 AI Integration
- OpenAI GPT-4 powered features
- AI text generation
- Token usage tracking per tenant
- Queue-based async AI processing

### 👥 Team Management
- Invite team members via email
- Role & Permission system (Spatie)
- Team billing (one subscription per team)

### 📊 Admin Dashboard
- Vue 3 + Inertia.js SPA
- Tailwind CSS beautiful UI
- Real-time notifications (Laravel Echo + Pusher)
- Analytics & usage charts

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 |
| Frontend | Vue 3 + Inertia.js |
| Styling | Tailwind CSS |
| Database | MySQL + Redis |
| Payment | Stripe (Laravel Cashier) |
| AI | OpenAI GPT-4 |
| Queue | Laravel Horizon |
| Real-time | Laravel Echo + Pusher |
| Auth | Laravel Breeze + Sanctum |
| Permissions | Spatie Laravel Permission |
| Storage | AWS S3 |

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+
- Redis

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/ai-saas-boilerplate.git
cd ai-saas-boilerplate

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Environment setup
cp .env.example .env
php artisan key:generate

# 5. Configure your .env (DB, Stripe, OpenAI, Pusher)

# 6. Run migrations & seeders
php artisan migrate --seed

# 7. Build frontend assets
npm run build

# 8. Start queue worker
php artisan horizon

# 9. Start server
php artisan serve
```

### Docker (Recommended)

```bash
docker-compose up -d
```

---

## ⚙️ Environment Variables

```env
# App
APP_NAME="AI SaaS"
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_DATABASE=ai_saas
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1

# Stripe
STRIPE_KEY=pk_test_xxxx
STRIPE_SECRET=sk_test_xxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxx

# OpenAI
OPENAI_API_KEY=sk-xxxx
OPENAI_MODEL=gpt-4

# Pusher (Real-time)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=

# AWS S3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
```

---

## 📁 Project Structure

```
ai-saas-boilerplate/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── AiController.php
│   │   │       ├── BillingController.php
│   │   │       └── TeamController.php
│   │   ├── Middleware/
│   │   │   └── EnsureSubscribed.php
│   │   └── Requests/
│   │       └── AiGenerateRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Team.php
│   │   └── AiUsage.php
│   ├── Services/
│   │   ├── OpenAiService.php
│   │   └── StripeService.php
│   └── Builders/
│       └── UserBuilder.php
├── resources/
│   └── js/
│       ├── components/
│       │   ├── AiGenerator.vue
│       │   ├── BillingCard.vue
│       │   └── TeamMembers.vue
│       ├── pages/
│       │   ├── Dashboard.vue
│       │   ├── Billing.vue
│       │   └── Settings.vue
│       └── stores/
│           └── useAuthStore.js
├── routes/
│   ├── api.php
│   └── web.php
├── database/
│   ├── migrations/
│   └── seeders/
├── docker-compose.yml
└── README.md
```

---

## 📸 Screenshots

> Dashboard, Billing, AI Generator screenshots here

---

## 🧪 Testing

```bash
php artisan test
```

---

## 🤝 Contributing

Pull requests are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) first.

---

## 📄 License

MIT License — feel free to use for personal and commercial projects.

---

## 👨‍💻 Author

**Your Name** — Laravel & Vue.js Developer  
🔗 [LinkedIn](https://linkedin.com) | 🐦 [Twitter](https://twitter.com) | 💼 [Portfolio](https://yourportfolio.com)

---

⭐ **Star this repo if you find it useful!**