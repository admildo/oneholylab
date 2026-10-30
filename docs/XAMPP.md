# Running oneholylab with XAMPP

The site works from a subfolder such as `http://localhost/oneholylab/` (the `.htaccess` in the project folder serves everything from `public/`; `/oneholylab/public/` also works), so you can copy it straight into XAMPP's `htdocs`.

## macOS

1. Open the **XAMPP** app, go to *Manage Servers* and start **Apache** and **MySQL Database** (both show a green dot).
2. Copy the `oneholylab` folder to `/Applications/XAMPP/xamppfiles/htdocs/oneholylab`.
   Do not run it from Downloads: Apache is not allowed to read your Downloads folder.
   Inside it you must see `public`, `src`, `config` and `sql` directly.
   Then create the file `config/config.local.php` in that folder with this one line, which tells the site it lives in `/oneholylab`:
   ```
   <?php return ['base_url' => '/oneholylab'];
   ```
   The links then work at both `http://localhost/oneholylab/` and `http://localhost/oneholylab/public/`.
3. Create the database: open http://localhost/phpmyadmin, click *New*, name it `oneholylab`, collation `utf8mb4_general_ci`, *Create*.
4. Import the tables: click the `oneholylab` database, *Import*, choose `sql/schema.sql`, *Import*. You should see 12 tables.
5. Add demo products and your admin login. In Terminal:
   ```
   cd /Applications/XAMPP/xamppfiles/htdocs/oneholylab
   /Applications/XAMPP/xamppfiles/bin/php sql/seed.php admin@oneholylab.test choose-a-password
   ```
6. Open http://localhost/oneholylab/. Admin: http://localhost/oneholylab/admin/

## Windows

Same steps with these paths: copy to `C:\xampp\htdocs\oneholylab`, and run
`C:\xampp\php\php.exe sql\seed.php admin@oneholylab.test choose-a-password` from that folder in Command Prompt.

## If something goes wrong

| What you see | Fix |
|---|---|
| "The shop is temporarily unavailable" or a database message | MySQL is not running, the database `oneholylab` does not exist, or step 3 or 4 was skipped. If you set a MySQL password, copy `config/config.local.example.php` to `config/config.local.php` and put the user and password in it. |
| 403 Forbidden or "not found" | The folder is not in `htdocs`, or the URL is missing `/public/`. |
| Page has no styling or links go to the wrong place | Check that `config/config.local.php` exists and contains `['base_url' => '/oneholylab']` (step 2). |
| Port 80 or 3306 already in use | Stop the other web or MySQL server, or change the port in XAMPP and add it to the URL (for example `localhost:8080`). |
| "Database is not empty" when seeding | The demo data is already there. To start over, drop all tables in phpMyAdmin, import `schema.sql` again, and repeat step 5. |
| Blank white page | Create `config/config.local.php` with `<?php return ['app_env' => 'development'];` to see the error. |
