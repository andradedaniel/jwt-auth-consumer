## JWT Auth Consumer

Pacote Laravel para validar JWTs emitidos por um serviço central de autenticação e disponibilizar o usuário autenticado no contexto de aplicações consumidoras.

### Instalação

Instale o pacote diretamente do Packagist via Composer (recomendado fixar a versão principal):

```bash
composer require andradedaniel/jwt-auth-consumer:^1.0
```

### Publicação da configuração

```bash
php artisan vendor:publish --tag=config --provider="SpunetGestao\JwtAuthConsumer\JwtAuthConsumerServiceProvider"
```

Isso criará o arquivo `config/jwt-auth-consumer.php`.

### Variáveis de ambiente

Adicione no `.env` do projeto:

```
JWT_SHARED_SECRET=segredo-compartilhado
JWT_ALGO=HS256
JWT_LEEWAY=60
```

`JWT_LEEWAY` define, em segundos, a tolerância para diferenças de relógio entre emissores e consumidores do token (ajuda a evitar falhas de validação por pequenos desvios de tempo).

### Uso

Proteja rotas com o middleware fornecido:

```php
Route::middleware('jwt.auth')->group(function () {
    Route::get('/relatorios', ReportController::class);
});
```

Controle fino por permissões com o middleware `access-control`:

```php
Route::get('/atendimento', AtendimentoController::class)
    ->middleware(['jwt.auth', 'access-control:ATENDIMENTO_CRIAR_PRESENCIAL|ATENDIMENTO_CRIAR_TELEFONICO']);
```

O middleware verifica se o usuário autenticado possui ao menos uma das permissões informadas (separadas por pipe).

Dentro dos controladores, o usuário autenticado está disponível:

```php
$user = $request->user(); // instância de SpunetGestao\JwtAuthConsumer\Auth\JwtUser
```

### Observações

- O pacote apenas valida e consome tokens JWT; ele **não** emite tokens.
- Os dados do usuário são derivados das claims do token e não são persistidos em banco.
