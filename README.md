# WON API - Módulo RESTful para Perfex CRM

## 📋 Visão Geral

Módulo RESTful completo e seguro para o Perfex CRM com operações CRUD, autenticação por token, rate limiting e logs detalhados.

## 🚀 Características

- **Segurança**: Autenticação por token, lista branca de tabelas, rate limiting (100 req/hora)
- **API REST**: Operações CRUD completas, paginação automática, códigos de status HTTP
- **Monitoramento**: Logs detalhados, códigos de erro padronizados
- **Validação**: Campos obrigatórios, formatos específicos (email, CPF/CNPJ)

## 📚 Tabelas Suportadas

`clients`, `contacts`, `leads`, `projects`, `tasks`, `invoices`, `staff`, `tickets`

## 🔧 Endpoints

```
GET/POST/PUT/DELETE /won_api/won/api/{tabela}[/{id}]
GET /won_api/won/join?vat={cnpj_cpf}
```

## 🔑 Autenticação

```
Authorization: seu_token_aqui
```

## 📄 Formato de Resposta

```json
{
    "success": true,
    "data": [...],
    "meta": {"page": 1, "limit": 20, "total": 100}
}
```

## 🚀 Exemplo de Uso

```bash
# Listar clientes
curl -H "Authorization: TOKEN" "https://seu-site.com/won_api/won/api/clients"

# Criar cliente
curl -X POST -H "Authorization: TOKEN" -H "Content-Type: application/json" \
     -d '{"company":"Empresa LTDA"}' \
     "https://seu-site.com/won_api/won/api/clients"
```

## 📊 Códigos de Status

- `200` OK, `201` Created, `400` Bad Request, `401` Unauthorized
- `404` Not Found, `422` Invalid Data, `429` Rate Limited, `500` Server Error

## 🔧 Instalação

1. Upload para `/modules/won_api/`
2. Ative no painel administrativo
3. Configure token em "WON API > Configurações"

## ⚠️ Solução de Problemas

### Módulo não aparece
- Verificar estrutura de diretórios
- Ajustar permissões: `chmod 755 diretórios`, `chmod 644 arquivos`
- Executar: `php modules/won_api/diagnostic.php`

### API retorna 404
- Verificar rotas em `application/config/routes.php`:
```php
$route['api/won/(.+)'] = 'won_api/won/$1';
```

### API retorna 401
- Regenerar token em Admin > WON API > Configurações
- Verificar header: `Authorization: SEU_TOKEN`

### Instalação manual
```sql
INSERT INTO tblmodules (module_name, installed_version, active) VALUES ('won_api', '2.1.0', 1);
CREATE TABLE tblwon_api_logs (id INT AUTO_INCREMENT PRIMARY KEY, endpoint VARCHAR(255), method VARCHAR(10), ip_address VARCHAR(45), status INT, response_time FLOAT, date DATETIME);
INSERT INTO tbloptions (name, value) VALUES ('won_api_token', MD5(RAND())), ('won_api_rate_limit', '100');
```

## 📞 Suporte

1. Documentação: Admin > WON API > Documentação
2. Diagnóstico: `php modules/won_api/diagnostic.php`
3. Logs: `application/logs/`

---
**WON API v2.1.0** - Desenvolvido para Perfex CRM 