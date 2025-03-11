# TheFuture Billing System

## Overview
TheFuture Billing System is a **SaaS-based ISP billing platform** that seamlessly integrates with **FreeRADIUS** to manage **PPPoE** and **Hotspot** users. It provides automated billing, user management, bandwidth control, and package assignments while ensuring efficient customer subscription handling.

## Features
### ✅ **Customer Management**
- Multi-tenant SaaS architecture
- Manage PPPoE and Hotspot users
- User authentication via FreeRADIUS
- Subscription expiration and auto-renewal

### ✅ **Billing & Invoicing**
- Subscription-based billing
- One-time installation fees
- Automated invoice generation
- Payment processing & balance management

### ✅ **Bandwidth & Package Control**
- Assign packages with rate limits
- Dynamic bandwidth adjustments
- Expired user handling with restricted access

### ✅ **FreeRADIUS Integration**
- Sync NAS devices with FreeRADIUS
- Auto-update `clients.conf`
- Manage `radcheck`, `radreply`, `radgroupcheck`, and `radgroupreply`
- Apply **Mikrotik-Rate-Limit** and **Mikrotik-Address-List** for expired users

### ✅ **Automation & Monitoring**
- Cron jobs for plan expiration and renewal
- Live traffic monitoring
- Data & time limits for users
- MAC address locking for security

## Installation
### **Requirements**
- **Linux Server** (Ubuntu recommended)
- **PHP** (Laravel Framework)
- **MySQL/MariaDB**
- **FreeRADIUS**
- **Mikrotik Router (for PPPoE & Hotspot)**

### **Setup Instructions**
1. **Clone the repository**
   ```sh
   git clone https://github.com/PalstarKe/Thefuture-Billing.git
   cd Thefuture-Billing
   ```
2. **Install dependencies**
   ```sh
   composer install
   npm install
   ```
3. **Set up environment variables**
   ```sh
   cp .env.example .env
   php artisan key:generate
   ```
4. **Configure database & FreeRADIUS integration**
   - Update `.env` with database details
   - Configure FreeRADIUS clients & NAS devices

5. **Run database migrations**
   ```sh
   php artisan migrate --seed
   ```
6. **Start the application**
   ```sh
   php artisan serve
   ```

## API Endpoints
| Endpoint                 | Method | Description                  |
|-------------------------|--------|------------------------------|
| `/api/customers`        | GET    | List all customers           |
| `/api/customers/{id}`   | GET    | Get customer details         |
| `/api/invoices`         | GET    | List all invoices            |
| `/api/packages`         | GET    | List available packages      |
| `/api/nas`              | GET    | List NAS devices             |

## Contributing
1. Fork the repository
2. Create a new branch (`feature-new-feature`)
3. Commit your changes
4. Push to your branch
5. Open a pull request

## License
TheFuture Billing System is licensed under the **MIT License**.

---
💻 **Developed & Maintained by Codevibe**

