## LINK The Storage (Open as Powershell as Administrator):

<storng>Equivalent to: php artisan storage:link</storng>

```bash
New-Item -ItemType SymbolicLink -Path "path-to-project\public\storage" -Target "path-to-project\storage\app\public" 
