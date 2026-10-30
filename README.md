# oneholylab

Online store for the oneholylab Christian clothing brand, with a customer shop designed for phones first and an admin back office.
Plain PHP 8 (no framework), MySQL through mysqli, Tailwind CSS, vanilla JavaScript.

## Folder map

```
public/            The only folder the web server exposes (document root)
  index.php shop.php product.php cart.php checkout.php order.php
  login.php register.php logout.php account.php     <- customer pages (thin controllers)
  admin/                                            <- admin pages (thin controllers)
  assets/ uploads/                                  <- css, js, images, product photo uploads
src/               Application code, outside the document root
  bootstrap.php helpers.php                         <- startup and small helpers
  Database Auth Csrf View ...                       <- one class per job (see docs, CRC cards)
  views/layout/ views/partials/ views/shop/ views/admin/   <- templates
config/            Settings (config.php defaults, config.local.php for your own values)
sql/               schema.sql (tables) and seed.php (demo data and first admin)
docs/              Diagrams: CRC cards, EERD, context, DFD, use case, database design
tests/smoke.sh     End to end check of the main customer and admin flows
assets-src/        Tailwind source. public/assets/css/app.css is the built file
```

How a request flows: a page in `public/` loads `src/bootstrap.php`, asks classes in `src/` for data
(for example `ProductRepository::search()`), then hands the result to a template with `View::render()`.
Pages hold no SQL and templates hold no logic beyond loops and `if`s.

## Run it locally

```bash
mysql -u root -e "CREATE DATABASE oneholylab CHARACTER SET utf8mb4"
mysql -u root oneholylab < sql/schema.sql
cp config/config.local.example.php config/config.local.php      # then put your DB user and password in it
php sql/seed.php admin@oneholylab.test choose-a-password         # demo products + first admin
php -S 127.0.0.1:8080 -t public
```

- Shop: http://127.0.0.1:8080
- Admin: http://127.0.0.1:8080/admin/

Demo customer: `thandi@example.com` / `password123`. Test card: `4242 4242 4242 4242`, any future date, any 3 digits
(`4000 0000 0000 0002` is declined). No real money moves in test mode.

Using XAMPP (macOS or Windows)? Follow `docs/XAMPP.md`. It walks you through setting `base_url` to `/oneholylab` in `config/config.local.php`, so the links work at `http://localhost/oneholylab/` and `/oneholylab/public/`.

Changing styles: `npm install`, then `npm run build:css` (or `npm run watch:css`). The built CSS is committed, so a host does not need Node.

Checks: `tests/smoke.sh` (needs the site running and a seeded database).

## Deploy (hosting)

Any host with PHP 8.1+, MySQL/MariaDB and HTTPS works (for example cPanel shared hosting or a VPS).

1. Create a MySQL database and user in the host's control panel.
2. Upload the project so that `public/` is the **document root** (on cPanel: put the project in your home folder and point the domain, or `public_html`, at `oneholylab/public`). `src/`, `config/` and `sql/` must sit **outside** the web root.
3. Import `sql/schema.sql` (phpMyAdmin > Import).
4. Create `config/config.local.php` from the example with the database details, `'app_env' => 'production'` and `'mail_transport' => 'mail'`. Leave `'base_url'` empty for a domain root, or set it to the folder the site sits in (for example `'/shop'`).
5. Create the first admin: run `php sql/seed.php you@example.com a-strong-password` over SSH. Without SSH, import the schema and insert one admin row by hand (use `password_hash()` for the password). Remove the demo products in Admin afterwards if you do not want them.
6. Make `public/uploads/products/` writable by the web server.
7. Turn on HTTPS and visit the site. The session cookie becomes `Secure` automatically.

### Before real customers pay

The checkout uses `DemoGateway` (test mode). To take real payments, add one class that implements
`src/PaymentGateway.php` for your provider (Stripe, PayFast, Yoco and others), return it from
`OrderService::gateway()`, and set `PAYMENT_PROVIDER`. Card numbers should be collected by the provider's
hosted fields or redirect, never stored by this app.

## Security notes

- Prepared statements everywhere (mysqli, on the server)
- Passwords hashed with `password_hash`
- CSRF token on every POST
- Output escaped with `e()`
- Sessions regenerated at login, with `HttpOnly` and `SameSite=Lax` cookies
- Admin pages check the admin role on every request
- Uploads are checked by type and renamed, and PHP execution is denied in `uploads/`
- Stock is locked inside the checkout transaction, so the last item cannot be sold twice
