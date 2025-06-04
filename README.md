# WON API v2.1.1 - Sistema Básico REAL

API RESTful **SIMPLES** para Perfex CRM. Esta documentação reflete **EXATAMENTE** o que está implementado.

## ⚠️ **IMPORTANTE: O que esta versão REALMENTE faz**

Esta é uma versão **BÁSICA** com apenas CRUD simples. **NÃO possui** funcionalidades avançadas.

## ✅ **O que REALMENTE está implementado**

- ✅ **CRUD Básico:** GET, POST, PUT, DELETE para 6 tabelas
- ✅ **Autenticação:** X-API-TOKEN (padrão Perfex CRM)  
- ✅ **CORS:** Headers básicos habilitados
- ✅ **Busca por CPF/CNPJ:** Endpoint `/join`
- ✅ **Status Público:** Endpoint `/status`
- ✅ **Paginação Simples:** `?page=1&limit=20`
- ✅ **Interface Admin:** Configurações básicas
- ✅ **Easy Install:** Instalação ultra-simples

## ❌ **O que NÃO está implementado**

- ❌ **Rate limiting** (removido)
- ❌ **Validações robustas** de CPF/CNPJ
- ❌ **Sistema de logs avançado**
- ❌ **Endpoints especializados** (estimate/convert, invoice/send, etc.)
- ❌ **Dashboard de métricas**
- ❌ **Bibliotecas auxiliares** (Won_validator, Won_error_handler)
- ❌ **Configurações avançadas**

## 📊 **Tabelas Suportadas (6 total)**

| Tabela | Endpoint | Campos Obrigatórios |
|--------|----------|---------------------|
| Clientes | `/api/clients` | `company` |
| Projetos | `/api/projects` | `name`, `clientid` |
| Tarefas | `/api/tasks` | `name` |
| Faturas | `/api/invoices` | `clientid` |
| Leads | `/api/leads` | `name` |
| Funcionários | `/api/staff` | `firstname`, `lastname`, `email` |

## 🚀 **Instalação**

1. Upload para `modules/won_api/`
2. Ativar via Admin → Módulos  
3. Configurar em Admin → WON API → Configurações

## 📖 **Uso Real**

### **Listar Clientes**
```bash
curl -X GET "https://seucrm.com/won_api/won/api/clients" \
     -H "X-API-TOKEN: seu_token_aqui"
```

### **Criar Cliente**  
```bash
curl -X POST "https://seucrm.com/won_api/won/api/clients" \
     -H "X-API-TOKEN: seu_token_aqui" \
     -H "Content-Type: application/json" \
     -d '{"company": "Empresa LTDA"}'
```

### **Buscar por CPF/CNPJ**
```bash
curl -X GET "https://seucrm.com/won_api/won/join?vat=12345678901" \
     -H "X-API-TOKEN: seu_token_aqui"
```

### **Status da API (público)**
```bash
curl -X GET "https://seucrm.com/won_api/won/status"
```

## 🔧 **Funcionalidades Reais**

- ✅ **CRUD:** GET (listar/obter), POST (criar), PUT (atualizar), DELETE (excluir)
- ✅ **Paginação:** `?page=1&limit=20` (máximo 100 por página)
- ✅ **Busca Inteligente:** `?search=termo` (campo apropriado por tabela)
- ✅ **Validação Básica:** Campos obrigatórios e readonly
- ✅ **CORS:** Headers básicos para integração frontend
- ✅ **Logs Melhorados:** Sistema nativo CodeIgniter com contexto de segurança

## 🛡️ **Segurança**

- ✅ **Autenticação obrigatória:** X-API-TOKEN em todas as rotas (exceto `/status`)
- ✅ **Validação de permissões:** Apenas admins acessam configurações
- ✅ **Proteção SQL:** Uso de query builder nativo CI
- ✅ **Headers seguros:** Content-Type, CORS configuráveis, X-Frame-Options
- ✅ **Logs de Segurança:** Tentativas de autenticação inválidas logadas
- ✅ **Sanitização:** Entrada de busca sanitizada contra XSS

## 📱 **Compatibilidade**

- ✅ **Perfex CRM:** 2.9.2+ (Easy Install)
- ✅ **n8n:** Headers X-API-TOKEN compatíveis
- ✅ **Zapier:** REST API básica
- ✅ **Power Automate:** JSON responses
- ✅ **PHP:** 7.4+
- ✅ **MySQL:** 5.7+

## 📂 **Estrutura Real**

```
won_api/
├── won_api.php              # Arquivo principal
├── install.php              # Instalação mínima
├── controllers/
│   ├── Won.php              # Controller API (390 linhas)
│   └── Won_api.php          # Controller admin (104 linhas)
├── views/
│   ├── settings.php         # Configurações administrativas
│   ├── api_documentation.php # Documentação da API
│   └── logs.php             # Logs básicos
├── config/
│   └── routes.php           # Rotas implementadas
├── README.md                # Esta documentação
└── CHANGELOG.md             # Histórico de mudanças
```

## 📝 **Responses da API**

### **Sucesso:**
```json
{
  "success": true,
  "data": {...},
  "message": "Registro criado",
  "timestamp": 1703123456,
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 50,
    "total_pages": 3
  }
}
```

### **Erro:**
```json
{
  "success": false,
  "data": null,
  "message": "Token X-API-TOKEN obrigatório",
  "timestamp": 1703123456
}
```

## 🎯 **Use Cases Reais**

### ✅ **O que você PODE fazer:**
- Integrar com n8n para automações básicas
- Criar/listar/atualizar/deletar registros
- Buscar clientes por CPF/CNPJ
- Paginar resultados
- Busca simples por texto

### ❌ **O que você NÃO pode fazer:**
- Rate limiting (não implementado)
- Validações avançadas (não implementado)
- Endpoints especializados (não implementado)
- Métricas ou dashboards (não implementado)
- Logs avançados (não implementado)

## 📞 **Suporte**

- **Autor:** Matheus Baiense
- **GitHub:** https://github.com/Matheusbaiense/won_api
- **Versão:** 2.1.1 (Sistema Básico)
- **Status:** Funcional para CRUD básico

---

**🎯 WON API v2.1.1** - Sistema básico que **realmente funciona** para CRUD simples 