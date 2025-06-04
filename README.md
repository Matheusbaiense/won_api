# 🚀 WON API v2.1.1 - API RESTful Profissional para Perfex CRM

[![Versão](https://img.shields.io/badge/versão-2.1.1-brightgreen.svg)](https://github.com/Matheusbaiense/won_api)
[![PHP](https://img.shields.io/badge/PHP-7.4+-blue.svg)](https://php.net)
[![Perfex CRM](https://img.shields.io/badge/Perfex%20CRM-2.9.2+-orange.svg)](https://perfexcrm.com)
[![Licença](https://img.shields.io/badge/licença-MIT-blue.svg)](LICENSE)

> **API RESTful completa e segura para integração com Perfex CRM, incluindo CORS, rate limiting avançado, validações robustas e monitoramento profissional.**

## 🌟 **Novidades v2.1.1 (Profissional)**

### 🔥 **Funcionalidades Principais**
- ✅ **CORS Completo** - Integração front-end e SPAs
- ✅ **Rate Limiting Avançado** - Headers informativos + limpeza automática
- ✅ **Logs Detalhados** - User-agent, performance, debug completo
- ✅ **Validações Robustas** - CPF/CNPJ, email, IDs numéricos
- ✅ **Endpoint de Status** - Monitoramento e health check
- ✅ **Headers de Segurança** - X-Frame-Options, CSRF protection
- ✅ **Atualização Automática** - Script com backup e rollback

### 📊 **Endpoints Disponíveis**

| Método | URL | Descrição | Autenticação |
|--------|-----|-----------|--------------|
| `GET` | `/won_api/won/status` | Status da API e monitoramento | ❌ Não |
| `GET` | `/won_api/won/api/{table}` | Listar registros com paginação | ✅ Sim |
| `GET` | `/won_api/won/api/{table}/{id}` | Buscar registro específico | ✅ Sim |
| `POST` | `/won_api/won/api/{table}` | Criar novo registro | ✅ Sim |
| `PUT` | `/won_api/won/api/{table}/{id}` | Atualizar registro | ✅ Sim |
| `DELETE` | `/won_api/won/api/{table}/{id}` | Deletar registro | ✅ Sim |
| `GET` | `/won_api/won/join?vat=CPF/CNPJ` | Busca por CPF/CNPJ | ✅ Sim |

### 🗂️ **Tabelas Suportadas**

| Tabela | Endpoint | Campos Obrigatórios | Campos Somente Leitura |
|--------|----------|---------------------|------------------------|
| **Clientes** | `clients` | `company` | `userid`, `datecreated` |
| **Projetos** | `projects` | `name`, `clientid` | `id`, `datecreated` |
| **Tarefas** | `tasks` | `name` | `id`, `datecreated` |
| **Funcionários** | `staff` | `firstname`, `lastname`, `email` | `staffid`, `datecreated` |
| **Leads** | `leads` | `name` | `id`, `datecreated` |
| **Faturas** | `invoices` | `clientid` | `id`, `datecreated` |

## 🛡️ **Segurança e Performance**

### 🔐 **Rate Limiting Inteligente**
```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 85
X-RateLimit-Reset: 1703181600
```

- **Limite:** 100 requisições por hora por IP
- **Headers informativos** em todas as respostas
- **Limpeza automática** de dados antigos (>48h)
- **Bloqueio temporário** com tempo de reset

### 🌐 **CORS Configurável**
```http
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
```

### 🛡️ **Headers de Segurança**
```http
X-WON-API-Version: 2.1.1
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
```

## 📝 **Exemplos de Uso**

### 1. **Verificar Status da API** (Público)
```bash
curl -X GET "https://seusite.com/won_api/won/status"
```

**Resposta:**
```json
{
  "api_name": "WON API",
  "version": "2.1.1",
  "status": "online",
  "endpoints": {
    "base_url": "https://seusite.com/won_api/won/api/",
    "authentication": "Header: Authorization",
    "methods": ["GET", "POST", "PUT", "DELETE"]
  },
  "rate_limiting": {
    "limit": 100,
    "window": "1 hour"
  },
  "tables": ["clients", "projects", "tasks", "staff", "leads", "invoices"]
}
```

### 2. **Listar Clientes com Paginação**
```bash
curl -X GET "https://seusite.com/won_api/won/api/clients?page=1&limit=10" \
     -H "Authorization: SEU_TOKEN_AQUI"
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "userid": 1,
      "company": "Empresa Exemplo LTDA",
      "email": "contato@empresa.com.br",
      "phonenumber": "(11) 99999-9999"
    }
  ],
  "meta": {
    "page": 1,
    "limit": 10,
    "total": 50,
    "total_pages": 5
  },
  "timestamp": 1703123456,
  "response_time_ms": 45.67
}
```

### 3. **Criar Novo Cliente**
```bash
curl -X POST "https://seusite.com/won_api/won/api/clients" \
     -H "Authorization: SEU_TOKEN_AQUI" \
     -H "Content-Type: application/json" \
     -d '{
       "company": "Nova Empresa LTDA",
       "email": "contato@novaempresa.com.br",
       "phonenumber": "(11) 88888-8888",
       "vat": "12345678901234"
     }'
```

### 4. **Buscar por CPF/CNPJ**
```bash
curl -X GET "https://seusite.com/won_api/won/join?vat=12.345.678/0001-90" \
     -H "Authorization: SEU_TOKEN_AQUI"
```

### 5. **Testar Rate Limiting**
```bash
# Verificar headers de rate limiting
curl -I -X GET "https://seusite.com/won_api/won/api/clients" \
     -H "Authorization: SEU_TOKEN_AQUI"
```

## ⚙️ **Instalação e Configuração**

### 📦 **Instalação Rápida**
1. Faça upload do módulo para `/modules/won_api/`
2. Acesse **Admin → Módulos** no Perfex CRM
3. Ative o módulo **WON API**
4. Configure em **Admin → WON API → Configurações**

### 🔄 **Atualização da v2.1.0**
```bash
# Execute o script de atualização automática
php modules/won_api/update_v2_1_1.php
```

### 🛠️ **Configurações Avançadas**

```php
// Configurações disponíveis em Admin → WON API → Configurações

// CORS
won_api_cors_enabled = true
won_api_cors_origins = "*" // ou "https://meusite.com,https://app.meusite.com"

// Rate Limiting
won_api_rate_limit = 100
won_api_rate_limit_headers = true

// Debug (apenas desenvolvimento)
won_api_debug_mode = false
won_api_log_level = "info"

// Validações
won_api_strict_validation = true
won_api_validate_cpf_cnpj = true
```

## 🔍 **Monitoramento e Debug**

### 📊 **Endpoint de Status Detalhado**
```bash
curl "https://seusite.com/won_api/won/status"
```

### 📝 **Logs Detalhados**
- **Local:** Admin → WON API → Logs
- **Informações:** IP, endpoint, método, status, tempo de resposta, user-agent
- **Níveis:** debug, info, warning, error

### 🧪 **Verificação de Saúde**
```bash
# Diagnóstico completo
php modules/won_api/verify_install.php
```

## 🚀 **Performance**

### ⚡ **Otimizações**
- **Queries indexadas** para rate limiting
- **Limpeza automática** de dados antigos
- **Paginação eficiente** com meta dados
- **Cache de configurações** (futuro)

### 📈 **Métricas**
- **Tempo de resposta** em cada request
- **Rate limiting** por IP com headers
- **Logs estruturados** para análise

## 🔗 **Integração com n8n**

### 📋 **Headers Necessários**
```javascript
// Configuração para n8n
{
  "Authorization": "SEU_TOKEN_AQUI",
  "Content-Type": "application/json"
}
```

### ✅ **Compatibilidade**
- ✅ **n8n-nodes-perfex** - Header `Authorization` correto
- ✅ **Zapier** - Webhooks e polling
- ✅ **Power Automate** - REST API
- ✅ **Frontend JS** - CORS habilitado

## 🛠️ **Requisitos Técnicos**

| Requisito | Versão Mínima | Recomendado |
|-----------|---------------|-------------|
| **Perfex CRM** | 2.9.2+ | 3.0+ |
| **PHP** | 7.4+ | 8.1+ |
| **MySQL** | 5.7+ | 8.0+ |
| **Extensões PHP** | json, curl, mbstring | + openssl |

## 📋 **Códigos de Erro**

| Código | Status | Descrição |
|--------|--------|-----------|
| `AUTH_MISSING` | 401 | Token não fornecido |
| `AUTH_INVALID` | 401 | Token inválido |
| `RATE_LIMIT_EXCEEDED` | 429 | Limite excedido |
| `INVALID_TABLE` | 400 | Tabela não permitida |
| `INVALID_ID` | 400 | ID inválido |
| `VALIDATION_ERROR` | 422 | Dados inválidos |
| `NOT_FOUND` | 404 | Registro não encontrado |

## 🏗️ **Estrutura do Projeto**

```
won_api/
├── 📁 config/
│   ├── won_api_config.php      # Configurações avançadas
│   └── won_api_tables.php      # Definições de tabelas
├── 📁 controllers/
│   ├── Won.php                 # Controller principal da API
│   └── Won_api.php             # Controller administrativo
├── 📁 views/                   # Interface administrativa
├── won_api.php                 # Arquivo principal do módulo
├── install.php                 # Instalação automática
├── update_v2_1_1.php          # Atualização automática
├── verify_install.php          # Diagnóstico do sistema
└── README.md                   # Documentação completa
```

## 📞 **Suporte e Contribuição**

### 🆘 **Suporte**
- **GitHub Issues:** [https://github.com/Matheusbaiense/won_api/issues](https://github.com/Matheusbaiense/won_api/issues)
- **Documentação:** Consulte este README
- **Logs:** `application/logs/` no Perfex CRM

### 🤝 **Contribuir**
1. Fork o projeto
2. Crie uma branch: `git checkout -b feature/nova-funcionalidade`
3. Commit: `git commit -m 'Adiciona nova funcionalidade'`
4. Push: `git push origin feature/nova-funcionalidade`
5. Abra um Pull Request

## 📄 **Licença**

Este projeto está licenciado sob a **MIT License** - veja o arquivo [LICENSE](LICENSE) para detalhes.

---

## 🎯 **Roadmap Futuro**

### v2.2.0 (Planejado)
- [ ] Cache inteligente com Redis/Memcached
- [ ] Webhooks para eventos
- [ ] Autenticação OAuth2
- [ ] Backup automático de dados
- [ ] Dashboard de métricas
- [ ] Integração GraphQL

### v2.3.0 (Planejado)
- [ ] Rate limiting por usuário
- [ ] Filtros avançados de busca
- [ ] Exportação em múltiplos formatos
- [ ] Versionamento de API
- [ ] SDK em múltiplas linguagens

---

<div align="center">

**🚀 Desenvolvido com ❤️ por [Matheus Baiense](https://github.com/Matheusbaiense)**

**⭐ Se este projeto foi útil, considere dar uma estrela!**

</div> 