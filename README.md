# WON API v2.1.0 - Módulo RESTful para Perfex CRM

## 📋 Visão Geral

Módulo RESTful ultra otimizado para o Perfex CRM com operações CRUD, autenticação por token, rate limiting e logs detalhados.

## 🚀 Características

- **Segurança**: Autenticação por token, lista branca de tabelas, rate limiting (100 req/hora)
- **API REST**: Operações CRUD completas, paginação automática, códigos de status HTTP
- **Monitoramento**: Logs detalhados, códigos de erro padronizados
- **Validação**: Campos obrigatórios, formatos específicos (email, CPF/CNPJ)
- **Ultra Compacto**: 78KB total, 15 arquivos essenciais

## 📚 Tabelas Suportadas

`clients`, `projects`, `tasks`, `staff`, `leads`, `invoices`

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

## 🔧 Instalação

1. Upload para `/modules/won_api/`
2. Ative no painel administrativo
3. Configure token em "WON API > Configurações"

## ⚠️ Solução de Problemas

### Módulo não aparece
- Verificar estrutura de diretórios
- Ajustar permissões: `chmod 755 diretórios`, `chmod 644 arquivos`
- Executar: `php modules/won_api/verify_install.php`

### API retorna 404
- Verificar se o módulo está ativo
- Confirmar token no header: `Authorization: SEU_TOKEN`

### Instalação manual
```sql
-- Execute se instalação automática falhar:
INSERT INTO tblmodules (module_name, installed_version, active) VALUES ('won_api', '2.1.0', 1);
-- Ou execute: php modules/won_api/install_manual.php
```

## 📞 Suporte

1. **Documentação**: Admin > WON API > Documentação
2. **Verificação**: `php modules/won_api/verify_install.php`
3. **Logs**: Admin > WON API > Logs

---
**WON API v2.1.0** - Ultra otimizado para Perfex CRM 2.9.2+ 