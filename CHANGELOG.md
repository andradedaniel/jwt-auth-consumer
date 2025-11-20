## Changelog

Todas as mudanças notáveis deste pacote serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/)
e este projeto adere ao [Versionamento Semântico](https://semver.org/lang/pt-BR/spec/v2.0.0.html).

### [1.0.0] - 2025-11-20

- **Adicionado**: Pacote inicial `andradedaniel/jwt-auth-consumer`.
- **Adicionado**: Middleware `JwtAuth` para validação de JWT em serviços consumidores.
- **Adicionado**: Middleware `AccessControl` para controle de acesso baseado em permissões.
- **Adicionado**: Serviço `JwtValidator` para abstrair a lógica de validação de tokens.
- **Adicionado**: Integração com `firebase/php-jwt` para manipulação e validação de JWTs.
- **Adicionado**: Service provider para registro automático no Laravel.

