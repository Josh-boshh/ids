# Case study admin and MySQL

The admin at `/admin/` uses the `ids_integrated.case_studies` MySQL table.

## Local XAMPP setup

1. Start Apache and MySQL in the XAMPP control panel.
2. Create the database and table from the project root:

   ```powershell
   Get-Content admin/schema.sql | C:\xampp\mysql\bin\mysql.exe -u root
   ```

The schema import creates both tables and seeds the three current case studies. `INSERT IGNORE` keeps repeated schema imports from overwriting existing case edits. The admin password hash is intentionally not included; generate a hash locally and set it separately in `admin_users.password_hash`.

The default connection is `127.0.0.1:3306`, database `ids_integrated`, user `root`, and an empty password, matching a default local XAMPP install. Configure `IDS_DB_HOST`, `IDS_DB_PORT`, `IDS_DB_NAME`, `IDS_DB_USER`, and `IDS_DB_PASSWORD` in the PHP/Apache environment for other installations. Do not commit production credentials.

For an existing database that already has the social-link columns, create the admin account table:

```powershell
Get-Content admin/migrations/002_add_admin_users.sql | C:\xampp\mysql\bin\mysql.exe -u root
```

For an existing database with case-level SEO columns, create the global site SEO settings table and move SEO ownership to the site:

```powershell
Get-Content admin/migrations/004_site_seo_settings.sql | C:\xampp\mysql\bin\mysql.exe -u root
```

The admin has separate Case studies and Site SEO sections. Global SEO edits apply to the I.D.S Integrated homepage and its social previews; case editing contains no SEO fields.

Each case can store a project website plus LinkedIn, Instagram, Facebook, and X profile URLs. These are optional and validated as HTTP/HTTPS links.

MySQL is the source of truth for case-study cards and content, and the separate Site SEO settings control I.D.S Integrated's homepage title, description, canonical URL, and social previews. Original HTML remains as a fallback while pages load published settings from the database.

Generate a hash in the local browser utility at `/admin/password-hash.php`. Enter a password and submit the form; the resulting hash is displayed in a read-only field and is not saved. The page accepts requests only from localhost.

To change the admin password, generate a new hash in that utility and replace the existing value in `ids_integrated.admin_users.password_hash` directly in your database. Use a password with at least 12 characters. For a database created with the earlier username-plus-password schema, first apply `admin/migrations/003_password_only_admin.sql`; it preserves any saved hash while dropping the username column.

The admin requires a signed-in account for editing, and should still be served over HTTPS in production.