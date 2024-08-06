# To Run The Project:

1. Install dependencies:

    ```bash
    composer install
    ```

2. Start the PHP built-in server:

    ```bash
    php -S localhost:8000 -t public
    ```

# Link the Storage (Open PowerShell as Administrator):

**Equivalent to:** `php artisan storage:link`

```powershell
New-Item -ItemType SymbolicLink -Path "path-to-project\public\storage" -Target "path-to-project\storage\app\public"
