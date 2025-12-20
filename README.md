# Threads – Social Media App with Realtime Chat

A Threads-inspired social media application featuring realtime chat functionality.

## 🛠 Tech Stack

- **Backend:** Laravel 12  
- **Frontend:** Inertia.js 2.0 + Vue.js 3  
- **Realtime & Jobs:** Laravel Reverb, Laravel Queues  

## ✅ Requirements

Make sure the following are installed on your machine:

- **PHP:** 8.3 or higher  
- **Node.js:** 20.0 or higher  
- **Composer**
- **NPM**

## 🚀 Installation

Install backend and frontend dependencies:

```bash
composer install
npm install
npm run dev
php artisan serve
```
run reverb and broadcasting
```
php artisan reverb:start
php artisan queue:work
