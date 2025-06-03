# WON API - Módulo de API RESTful para Perfex CRM

## 📋 Visão Geral

O módulo WON API fornece uma interface RESTful completa e segura para o Perfex CRM, permitindo operações CRUD (Create, Read, Update, Delete) em diversas tabelas do sistema, além de consultas JOIN avançadas para busca de dados relacionados.

## 🚀 Características Principais

### ✅ Segurança Avançada
- **Autenticação por Token**: Todas as requisições são protegidas por token de autenticação
- **Lista Branca de Tabelas**: Apenas tabelas pré-definidas são acessíveis
- **Proteção contra SQL Injection**: Validação rigorosa de parâmetros de entrada
- **Rate Limiting**: Limite de 100 requisições por hora por IP/token

### 📊 Recursos de API
- **Operações CRUD Completas**: GET, POST, PUT, DELETE para todas as tabelas permitidas
- **Paginação Automática**: Suporte a paginação com metadados
- **Busca Avançada**: Busca global e filtros específicos por campo
- **Consultas JOIN**: Endpoint especializado para dados relacionados

### 🔍 Monitoramento e Logs
- **Logs Detalhados**: Registro de todas as operações e acessos
- **Códigos de Erro Padronizados**: Sistema consistente de códigos de erro
- **Respostas Padronizadas**: Formato JSON unificado para todas as respostas

### 🎯 Validação de Dados
- **Campos Obrigatórios**: Validação por tabela
- **Formatos Específicos**: Validação de email, CPF/CNPJ
- **Sanitização Automática**: Limpeza e formatação de dados

## 📚 Tabelas Suportadas

O módulo suporta as seguintes tabelas do Perfex CRM:

- `clients` - Clientes
- `contacts` - Contatos  
- `leads` - Leads
- `projects` - Projetos
- `tasks` - Tarefas
- `invoices` - Faturas
- `staff` - Funcionários
- `tickets` - Tickets

## 🔧 Endpoints da API

### Operações CRUD

```
GET    /won_api/won/api/{tabela}        - Listar registros
GET    /won_api/won/api/{tabela}/{id}   - Obter registro específico
POST   /won_api/won/api/{tabela}        - Criar novo registro
PUT    /won_api/won/api/{tabela}/{id}   - Atualizar registro
DELETE /won_api/won/api/{tabela}/{id}   - Excluir registro
```

### Consulta JOIN

```
GET    /won_api/won/join?vat={cnpj_cpf} - Buscar dados relacionados
```

## 📝 Formato das Respostas

### Resposta de Sucesso
```json
{
    "success": true,
    "data": [...],
    "message": "Operação realizada com sucesso",
    "meta": {
        "page": 1,
        "limit": 20,
        "total": 100,
        "total_pages": 5
    }
}
```

### Resposta de Erro
```json
{
    "success": false,
    "error": "Mensagem de erro",
    "error_code": "CODIGO_ERRO"
}
```

## 🔑 Autenticação

Todas as requisições devem incluir o header `Authorization`:

```
Authorization: seu_token_aqui
```

## 📄 Paginação

Para endpoints de listagem, use os parâmetros:

- `page`: Número da página (padrão: 1)
- `limit`: Registros por página (padrão: 20)

**Exemplo:**
```
GET /won_api/won/api/clients?page=2&limit=10
```

## 🔍 Busca e Filtros

### Busca Global
```
GET /won_api/won/api/clients?search=empresa
```

### Filtros Específicos
```
GET /won_api/won/api/clients?company=teste&active=1
```

## 📊 Códigos de Status HTTP

- `200` - OK: Operação realizada com sucesso
- `400` - Bad Request: Dados inválidos
- `401` - Unauthorized: Token inválido
- `403` - Forbidden: Operação não permitida
- `404` - Not Found: Recurso não encontrado
- `405` - Method Not Allowed: Método não suportado
- `422` - Unprocessable Entity: Formato inválido
- `429` - Too Many Requests: Rate limit excedido
- `500` - Internal Server Error: Erro interno

## ⚠️ Códigos de Erro

- `AUTH_MISSING` - Token não fornecido
- `AUTH_INVALID` - Token inválido
- `RATE_LIMIT_EXCEEDED` - Limite de requisições excedido
- `INVALID_TABLE` - Tabela não permitida
- `INVALID_ID` - ID inválido
- `INVALID_DATA` - Dados inválidos
- `MISSING_REQUIRED_FIELD` - Campo obrigatório ausente
- `INVALID_EMAIL_FORMAT` - Email inválido
- `INVALID_VAT_FORMAT` - CPF/CNPJ inválido
- `NOT_FOUND` - Registro não encontrado

## ✅ Validações

### Campos Obrigatórios

- **clients**: `company`
- **contacts**: `firstname`, `email`, `userid`
- **leads**: `name`

### Validações de Formato

- **email**: Deve ser um email válido
- **vat**: CPF (11 dígitos) ou CNPJ (14 dígitos)

## 🚀 Exemplos de Uso

### 1. Listar Clientes com Paginação
```bash
curl -X GET "https://seu-perfex.com/won_api/won/api/clients?page=1&limit=10" \
     -H "Authorization: seu_token_aqui"
```

### 2. Criar Cliente
```bash
curl -X POST "https://seu-perfex.com/won_api/won/api/clients" \
     -H "Authorization: seu_token_aqui" \
     -H "Content-Type: application/json" \
     -d '{
       "company": "Minha Empresa LTDA",
       "vat": "12345678000123",
       "email": "contato@empresa.com"
     }'
```

### 3. Atualizar Cliente
```bash
curl -X PUT "https://seu-perfex.com/won_api/won/api/clients/1" \
     -H "Authorization: seu_token_aqui" \
     -H "Content-Type: application/json" \
     -d '{
       "company": "Empresa Atualizada LTDA"
     }'
```

### 4. Buscar por CNPJ
```bash
curl -X GET "https://seu-perfex.com/won_api/won/join?vat=12345678000123" \
     -H "Authorization: seu_token_aqui"
```

## 🧪 Testes

O módulo inclui um script de teste completo em `tests/api_test.php`:

```bash
php won_api/tests/api_test.php
```

Configure `$base_url` e `$token` no arquivo antes de executar.

## 📈 Rate Limiting

- **Limite**: 100 requisições por hora
- **Escopo**: Por IP + Token
- **Reset**: Automático após 1 hora
- **Resposta**: Status 429 quando excedido

## 📝 Logs

Todos os acessos e operações são registrados nos logs do Perfex CRM:

- Acessos à API
- Operações realizadas
- Erros e exceções
- Rate limiting

## 🔧 Instalação

1. Faça upload do módulo para `/modules/won_api/`
2. Ative o módulo no painel administrativo
3. Configure um token em "WON API > Configurações"
4. Acesse a documentação em "WON API > Documentação"

## 🛠️ Configuração

1. **Gerar Token**: Acesse `Admin > WON API > Configurações`
2. **Documentação**: Acesse `Admin > WON API > Documentação`
3. **Testes**: Execute o script em `tests/api_test.php`

## 🔐 Segurança

### Boas Práticas

1. **Mantenha o token seguro**: Não compartilhe ou exponha em repositórios
2. **Use HTTPS**: Sempre em produção
3. **Monitore logs**: Verifique acessos suspeitos
4. **Rate limiting**: Respeite os limites de requisições
5. **Validação**: Sempre valide dados de entrada

### Lista Branca de Tabelas

Por segurança, apenas as seguintes tabelas são acessíveis:

```php
$tabelas_permitidas = [
    'tblclients', 'tblcontacts', 'tblleads', 'tblprojects', 
    'tbltasks', 'tblinvoices', 'tblstaff', 'tbltickets'
];
```

## 🆕 Melhorias Implementadas

### Versão 2.0 - Melhorias Abrangentes

#### Segurança
- ✅ Autenticação aprimorada com códigos de erro específicos
- ✅ Lista branca de tabelas para proteção contra SQL injection
- ✅ Rate limiting (100 req/hora)
- ✅ Logs detalhados de segurança

#### API REST
- ✅ Respostas padronizadas com formato JSON consistente
- ✅ Códigos de status HTTP apropriados
- ✅ Códigos de erro padronizados e documentados
- ✅ Paginação automática com metadados

#### Validação
- ✅ Campos obrigatórios por tabela
- ✅ Validação de formatos (email, CPF/CNPJ)
- ✅ Sanitização automática de dados
- ✅ Tratamento de erros robusto

#### Interface
- ✅ Interface administrativa aprimorada
- ✅ Botão de copiar token
- ✅ Link para documentação
- ✅ Alertas informativos

#### Documentação
- ✅ Documentação completa da API
- ✅ Exemplos de uso
- ✅ Códigos de erro documentados
- ✅ Script de testes automatizados

#### Compatibilidade
- ✅ Rotas compatíveis com Node Community
- ✅ Headers e formatos padrão REST
- ✅ Tratamento de exceções melhorado
- ✅ Logs estruturados

## 🔧 Solução de Problemas

### 🚨 Problemas Comuns de Instalação

#### ❌ Módulo não aparece na lista de módulos

**Possíveis causas:**
- Estrutura de diretórios incorreta
- Arquivo `module_info.php` ausente ou com erros
- Permissões inadequadas de arquivos

**Soluções:**

1. **Verificar estrutura de diretórios:**
```
/modules/won_api/
  ├── config/
  ├── controllers/
  ├── models/
  ├── views/
  ├── tests/
  ├── install.php
  ├── uninstall.php
  ├── module_info.php
  ├── won_api.php
  ├── diagnostic.php
  └── README.md
```

2. **Verificar permissões:**
```bash
# Ajustar permissões de diretórios
find /caminho/para/perfex/modules/won_api -type d -exec chmod 755 {} \;

# Ajustar permissões de arquivos  
find /caminho/para/perfex/modules/won_api -type f -exec chmod 644 {} \;
```

3. **Executar diagnóstico:**
```bash
php modules/won_api/diagnostic.php
```

#### ❌ Erro durante a instalação

**Mensagem:** "Erro ao conectar ao banco de dados"

**Soluções:**
1. Verificar configurações do banco em `application/config/database.php`
2. Verificar se o usuário do banco tem privilégios CREATE TABLE
3. Verificar se a conexão está ativa

**Mensagem:** "Módulo requer Perfex CRM versão X.X.X"

**Soluções:**
1. Atualizar o Perfex CRM para versão 2.9.2 ou superior
2. Verificar versão atual em Admin > Sistema > Informações

#### ❌ Instalação manual necessária

Se a instalação automática falhar, execute as consultas SQL manualmente:

```sql
-- Registrar o módulo
INSERT INTO `tblmodules` (`module_name`, `installed_version`, `active`) 
VALUES ('won_api', '2.1.0', 1)
ON DUPLICATE KEY UPDATE `installed_version` = '2.1.0', `active` = 1;

-- Criar tabela de logs
CREATE TABLE IF NOT EXISTS `tblwon_api_logs` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `endpoint` VARCHAR(255) NOT NULL,
    `method` VARCHAR(10) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `status` INT NOT NULL,
    `response_time` FLOAT NOT NULL,
    `request_data` TEXT,
    `response_data` TEXT,
    `error_message` TEXT,
    `date` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `endpoint_idx` (`endpoint`),
    INDEX `date_idx` (`date`),
    INDEX `status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Configurações básicas
INSERT INTO `tbloptions` (`name`, `value`) VALUES
('won_api_token', SUBSTRING(MD5(RAND()), 1, 32)),
('won_api_rate_limit', '100'),
('won_api_cache_duration', '300'),
('won_api_log_level', 'basic')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
```

### 🚨 Problemas de Funcionamento

#### ❌ API retorna 404 (Not Found)

**Possíveis causas:**
- Rotas não configuradas corretamente
- Arquivo `config/routes.php` ausente
- Controlador não encontrado

**Soluções:**

1. **Verificar rotas em `application/config/routes.php`:**
```php
$route['api/won/(.+)'] = 'won_api/won/$1';
$route['won_api/won/(.+)'] = 'won_api/won/$1';
```

2. **Verificar se o controlador existe:**
```bash
ls -la modules/won_api/controllers/Won.php
```

3. **Limpar cache:**
```bash
rm -rf application/logs/cache/*
```

#### ❌ API retorna 401 (Unauthorized)

**Possíveis causas:**
- Token não configurado
- Token inválido
- Header de autorização ausente

**Soluções:**

1. **Verificar token:**
```sql
SELECT * FROM tbloptions WHERE name = 'won_api_token';
```

2. **Regenerar token:**
- Acesse Admin > WON API > Configurações
- Clique em "Regenerar Token"

3. **Verificar header:**
```bash
curl -H "Authorization: SEU_TOKEN_AQUI" "https://seu-site.com/won_api/won/api/status"
```

#### ❌ API retorna 500 (Internal Server Error)

**Possíveis causas:**
- Erro de sintaxe PHP
- Extensões PHP ausentes
- Problemas de configuração

**Soluções:**

1. **Verificar logs de erro:**
```bash
tail -f /var/log/apache2/error.log
# ou
tail -f application/logs/log-YYYY-MM-DD.php
```

2. **Verificar extensões PHP:**
```bash
php -m | grep -E "(json|curl|openssl|mbstring)"
```

3. **Executar diagnóstico:**
```bash
php modules/won_api/diagnostic.php
```

### 🔍 Script de Diagnóstico

Para facilitar a identificação de problemas, use o script de diagnóstico:

```bash
# Via navegador
https://seu-site.com/modules/won_api/diagnostic.php

# Via linha de comando
php modules/won_api/diagnostic.php
```

O script verifica:
- ✅ Versão do PHP e extensões
- ✅ Conexão com banco de dados
- ✅ Registro do módulo
- ✅ Tabelas necessárias
- ✅ Opções de configuração
- ✅ Permissões de arquivos
- ✅ Estrutura de diretórios
- ✅ Compatibilidade com Perfex CRM

### 🔧 Verificações Manuais

#### Verificar versão do PHP:
```bash
php --version
```
**Requisito:** PHP 7.4 ou superior

#### Verificar extensões PHP:
```bash
php -m | grep -E "(json|curl|openssl|mbstring|mysqli)"
```

#### Verificar permissões:
```bash
ls -la modules/won_api/
```
**Esperado:** Diretórios 755, arquivos 644

#### Verificar banco de dados:
```sql
SHOW TABLES LIKE 'tblwon_api_logs';
SELECT COUNT(*) FROM tblmodules WHERE module_name = 'won_api';
```

### 📋 Lista de Verificação para Instalação

Antes de reportar problemas, verifique:

- [ ] PHP 7.4+ instalado
- [ ] Extensões PHP necessárias ativas
- [ ] Perfex CRM versão 2.9.2+
- [ ] Permissões de arquivos corretas (755/644)
- [ ] Estrutura de diretórios completa
- [ ] Banco de dados com privilégios CREATE
- [ ] Apache/Nginx com mod_rewrite ativo
- [ ] Cache limpo após instalação

### 🆘 Instalação de Emergência

Se todos os métodos falharem, use a instalação de emergência:

1. **Fazer backup do banco de dados**
2. **Executar SQL de instalação manual** (mostrado acima)
3. **Ajustar permissões de arquivos**
4. **Limpar cache do sistema**
5. **Verificar logs de erro**

### 🔄 Reinstalação Completa

Para reinstalar completamente o módulo:

1. **Desinstalar:**
```sql
DELETE FROM tblmodules WHERE module_name = 'won_api';
DROP TABLE IF EXISTS tblwon_api_logs;
DELETE FROM tbloptions WHERE name LIKE 'won_api_%';
```

2. **Limpar cache:**
```bash
rm -rf application/logs/cache/*
```

3. **Reinstalar:**
- Fazer upload dos arquivos novamente
- Executar script de instalação
- Verificar funcionamento

## 📞 Suporte

Para suporte técnico:

1. Consulte a documentação completa na interface administrativa
2. Verifique os logs do sistema
3. Execute o script de testes para diagnosticar problemas
4. Consulte os códigos de erro na documentação

## 📜 Licença

Este módulo é distribuído sob a mesma licença do Perfex CRM.

---

**Desenvolvido com ❤️ para a comunidade Perfex CRM** 