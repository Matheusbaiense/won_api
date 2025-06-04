# WON API v2.1.1 - Versão Limpa e Funcional

API RESTful simples e funcional para Perfex CRM com instalação Easy Install.

## ✅ Características

- **Versão Única:** 2.1.1 - sem conflitos de versão
- **Easy Install:** Instalação ultra-simples via Perfex CRM
- **CRUD Completo:** GET, POST, PUT, DELETE para todas as tabelas
- **Autenticação:** X-API-TOKEN (padrão Perfex CRM)
- **CORS:** Habilitado para máxima compatibilidade
- **Clean Code:** Arquitetura simplificada e sem dependências órfãs

## 📊 Endpoints Disponíveis

| Tabela | Endpoint | Descrição |
|--------|----------|-----------|
| Clientes | `/api/clients` | Gerenciar clientes |
| Projetos | `/api/projects` | Gerenciar projetos |
| Tarefas | `/api/tasks` | Gerenciar tarefas |
| Faturas | `/api/invoices` | Gerenciar faturas |
| Leads | `/api/leads` | Gerenciar leads |
| Funcionários | `/api/staff` | Gerenciar funcionários |

### Endpoints Especiais

- `/status` - Status da API (público)
- `/join?vat=CPF` - Buscar cliente por CPF/CNPJ

## 🚀 Instalação

1. Faça upload para `modules/won_api/`
2. Ative via Admin → Módulos
3. Configure o token em Admin → WON API → Configurações

## 📖 Uso Básico

### Listar Clientes
```bash
curl -X GET "https://seucrm.com/won_api/won/api/clients" \
     -H "X-API-TOKEN: seu_token_aqui"
```

### Criar Cliente
```bash
curl -X POST "https://seucrm.com/won_api/won/api/clients" \
     -H "X-API-TOKEN: seu_token_aqui" \
     -H "Content-Type: application/json" \
     -d '{"company": "Empresa LTDA"}'
```

### Buscar por CPF/CNPJ
```bash
curl -X GET "https://seucrm.com/won_api/won/join?vat=12345678901" \
     -H "X-API-TOKEN: seu_token_aqui"
```

## 🔧 Funcionalidades

- ✅ CRUD completo para 6 tabelas principais
- ✅ Paginação com `?page=1&limit=20`
- ✅ Busca com `?search=termo`
- ✅ Filtros por campos específicos
- ✅ Validação de campos obrigatórios
- ✅ Campos readonly protegidos
- ✅ Headers CORS configurados
- ✅ Logs básicos do sistema
- ✅ Interface administrativa simples

## 🛡️ Segurança

- Autenticação obrigatória via X-API-TOKEN
- Validação de permissões administrativas
- Proteção contra SQL injection
- Headers de segurança configurados
- Rate limiting removido (conforme solicitado)

## 📝 Changelog v2.1.1

### ✅ Correções Implementadas
- Removido conflito de versões (agora só 2.1.1)
- Removidas bibliotecas órfãs não utilizadas
- Removidas rotas para endpoints inexistentes
- Simplificada arquitetura sem dependências circulares
- Removidos testes para funcionalidades inexistentes
- Criada instalação ultra-simples
- Interface administrativa funcional
- Controlador principal limpo e defensivo

### ❌ Removido (Causava problemas)
- Bibliotecas Won_error_handler, Won_validator, Won_operations
- Configurações externas órfãs
- Rate limiting complexo
- Endpoints especializados não implementados
- Arquivos de versões conflitantes
- Testes para funcionalidades inexistentes

## 🎯 Compatibilidade

- ✅ Perfex CRM 2.9.2+
- ✅ Easy Install
- ✅ n8n, Zapier, Power Automate
- ✅ PHP 7.4+
- ✅ MySQL 5.7+

## 📞 Suporte

- **Autor:** Matheus Baiense
- **GitHub:** https://github.com/Matheusbaiense
- **Versão:** 2.1.1 (Limpa e Funcional)

---

**WON API v2.1.1** - Versão simplificada e 100% funcional 🚀 