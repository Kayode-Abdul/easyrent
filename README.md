<p align="center"><h1>EasyRent</h1></p>

<p align="center">
A comprehensive property rental management platform built with Laravel
</p>

## About EasyRent

EasyRent is a full-featured property rental management system designed to streamline the rental process for landlords, tenants, and property managers. The platform facilitates property listings, tenant management, rent collection, and communication between all parties involved in the rental ecosystem.

## Key Features

### User Roles and Management
- **Multi-role system**: Support for landlords, tenants, agents/marketers, regional managers, and administrators
- **User authentication**: Secure login and registration with email verification
- **Role-based access control**: Different permissions and views based on user roles

### Property Management
- **Property listings**: Landlords can add and manage multiple properties
- **Apartment management**: Support for multiple apartments within a property
- **Amenities tracking**: Record and display property amenities and features
- **Occupancy tracking**: Monitor apartment availability and occupancy status

### Financial Management
- **Proforma invoices**: Generate and send rent proforma invoices to tenants
- **Online payments**: Integrated Paystack payment gateway for secure transactions
- **Receipt generation**: Automatic PDF receipt generation after successful payments
- **Email notifications**: Send payment receipts to both landlords and tenants

### Tenant Management
- **Tenant profiles**: Comprehensive tenant information management
- **Lease tracking**: Monitor lease start and end dates
- **Tenant communication**: Built-in messaging system between landlords and tenants

### Marketing and Referrals
- **Marketer system**: Support for property marketers with commission tracking
- **Referral program**: Multi-tier referral system with commission distribution
- **Regional management**: Regional scope management for property listings

### Communication Tools
- **Messaging system**: Internal communication between users
- **Email notifications**: Automated emails for important events and updates
- **Email templates**: Customizable email templates for system communications

## Payment Flow

EasyRent implements a comprehensive payment flow:

1. **Proforma Generation**: Landlords create proforma invoices with detailed cost breakdowns
2. **Tenant Review**: Tenants can review, accept, or reject proforma invoices
3. **Payment Processing**: Upon acceptance, tenants are redirected to the payment form
4. **Secure Transactions**: Integration with Paystack for secure payment processing
5. **Receipt Generation**: Automatic generation of PDF receipts after successful payment
6. **Email Notifications**: Receipts are emailed to both tenant and landlord
7. **Apartment Updates**: Successful payments automatically update apartment occupancy status and lease dates

## Technology Stack

- **Framework**: Laravel PHP Framework
- **Database**: MySQL
- **Frontend**: Blade templates, JavaScript, jQuery
- **Payment Processing**: Paystack Integration
- **PDF Generation**: Laravel PDF library
- **Email**: Laravel Mail with SMTP support

## Installation

1. Clone the repository
```bash
git clone https://github.com/yourusername/easyrent.git
```

2. Install dependencies
```bash
composer install
npm install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Set up database
```bash
php artisan migrate
php artisan db:seed
```

5. Start the development server
```bash
php artisan serve
```

## License

The EasyRent application is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
