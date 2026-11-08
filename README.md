# PharmacyCare — Web-Based Pharmacy Management System

A student-project starter application built with PHP, MySQL, **MySQLi prepared statements**, Bootstrap 5, HTML and JavaScript. The interface uses blue and white.

## Requirements
- XAMPP with Apache, MySQL and PHP 8+
- A modern browser
- Internet connection for Bootstrap CDN (or download Bootstrap locally)

## Installation
1. Copy the `pharmacy_management` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in XAMPP.
3. Open `http://localhost/phpmyadmin`.
4. Import `database/pharmacy.sql`. It creates the `pharmacy_db` database and tables.
5. Check `config/database.php`. Default XAMPP settings are host `localhost`, user `root`, blank password.
6. Visit `http://localhost/pharmacy_management/setup_admin.php` once to create the first admin.
7. **Delete `setup_admin.php` immediately after it succeeds.**
8. Open `http://localhost/pharmacy_management/` and sign in.

Temporary login:
- Username: `admin`
- Password: `ChangeMe123!`

Change the temporary password before using the application beyond local demonstration. Do not deploy with default credentials.

## Included modules
- Staff login and session protection
- Dashboard summary
- Medicine inventory: add, list, search, edit, delete
- Customer registration and listing
- Sales recording with stock deduction inside a database transaction
- Paystack hosted checkout integration for GHS payments (Mobile Money and card, subject to Paystack account/channel availability)
- Prescription record register
- Daily/monthly sales, low-stock and expired-stock reports

## Notes and limitations
- The prescription module records prescription metadata only. It is not an electronic prescribing or clinical decision system. A qualified pharmacist must verify prescriptions and dispensing.
- The sales form records one medicine per transaction for a simple classroom demonstration. It can be extended to a multi-item cart.
- Cash and manually recorded payment options remain available. Paystack checkout is integrated for online GHS payments; transactions are verified server-side before a sale is recorded or stock is deducted.
- Email/API integration and PHPUnit automated tests are planned extensions and must be configured/tested separately.
- For production use, add role-based authorization on every action, audit logs, backups, HTTPS, password reset, stronger validation, and appropriate privacy/security controls.


## Login and staff registration

- Open `login.php` to sign in.
- New staff can select **Create an account** to open `signup.php`.
- Public sign-up always creates an `attendant` account. It does not allow a visitor to register as an administrator or pharmacist.
- The administrator account is created once through `setup_admin.php` (username `admin`, temporary password `ChangeMe123!`). Delete `setup_admin.php` after creating the administrator and change the temporary password.
- If the default login does not work, confirm that the `pharmacy_db` database was imported and that the `users` table contains the admin account. The password must be entered exactly, including capital letters and punctuation.


## Configure Paystack payments

1. Import `database/paystack_migration.sql` into the same `pharmacy_db` database after importing `pharmacy.sql`.
2. Create or sign in to your Paystack account and copy your **test secret key** from the Paystack Dashboard. Never share or publish the secret key.
3. Open `config/paystack.php` and replace `sk_test_REPLACE_WITH_YOUR_PAYSTACK_TEST_SECRET_KEY` with your test secret key. For deployment, prefer setting the `PAYSTACK_SECRET_KEY` environment variable and use HTTPS with your live key only after testing.
4. Confirm the callback URL in `config/paystack.php` is reachable by your application. The default is for local XAMPP testing; Paystack may not be able to reach `localhost` from the internet. For a live integration, deploy to a public HTTPS domain and update `PAYSTACK_CALLBACK_URL`.
5. Ensure PHP cURL is enabled in XAMPP (`php.ini`, extension=curl), then restart Apache.
6. In Sales, choose **Paystack**, enter the payer's email and continue. Paystack hosts the checkout page. The application verifies the transaction with Paystack before creating the sale and deducting stock.

The migration creates `paystack_payments` to store references and payment status. Test with Paystack test credentials and test payment methods first. Do not collect or store card numbers, PINs, or Mobile Money PINs in this application. This is an educational integration and should receive a security review before production use.
