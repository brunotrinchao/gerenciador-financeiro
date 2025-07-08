<?php
if ($_GET['token'] !== 'kWcqJEoujc79QLBsrgb6AcRO8c') {
    http_response_code(403);
    exit('Acesso não autorizado');
}


$repoDir = './';

shell_exec("cd {$repoDir} && git pull origin main");
shell_exec("cd {$repoDir} && php artisan config:cache");
shell_exec("cd {$repoDir} && php artisan route:cache");
shell_exec("cd {$repoDir} && php artisan view:cache");

// Se usar queue com supervisord ou similar, reinicie aqui, ex:
// shell_exec("supervisorctl restart all");

echo "Deploy executado com sucesso.";
