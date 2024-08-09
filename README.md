# To Run The Project locally:

1. Install dependencies:

    ```bash
    composer install
    ```

2. Start the PHP built-in server:

    ```bash
    php -S localhost:<port> -t public
    ```
# To Run The Project in the Local Network:

```bash
php -S 0.0.0.0:<port> -t public

```
Access vai the local IP of the device, For example: 192.168.0.1:8000 

# Link the Storage (Open PowerShell as Administrator):

**Equivalent to:** `php artisan storage:link`

```powershell
New-Item -ItemType SymbolicLink -Path "path-to-project\public\storage" -Target "path-to-project\storage\app\public"

```

