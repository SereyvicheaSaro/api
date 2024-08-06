## To Run The Project:
```bash
composer install && php -S localhost:8000 -t public


## LINK The Storage (Open as Powershell as Administrator):

===== Equivalent to: php artisan storage:link

```bash
New-Item -ItemType SymbolicLink -Path "path-to-project\public\storage" -Target "path-to-project\storage\app\public" 
