# WON API - Integração com Perfex CRM

Este módulo permite a integração entre o Perfex CRM e a API WON, facilitando a sincronização de dados e automação de processos.

## 🚀 Funcionalidades

- Integração com a API WON
- Sincronização de dados
- Configurações personalizáveis
- Interface administrativa intuitiva

## 📋 Pré-requisitos

- Perfex CRM instalado
- PHP 7.4 ou superior
- Acesso à API WON

## 🔧 Instalação

1. Faça o download do módulo
2. Extraia os arquivos na pasta `modules` do seu Perfex CRM
3. Acesse o painel administrativo do Perfex
4. Vá em Configurações > Módulos
5. Ative o módulo "WON API"

## ⚙️ Configuração

Após a instalação, acesse as configurações do módulo para:

1. Configurar as credenciais da API
2. Definir as opções de sincronização
3. Personalizar as integrações

## 📦 Estrutura do Projeto

```
won_api/
├── config/
│   └── routes.php
├── controllers/
│   ├── Won.php
│   └── Won_api.php
├── models/
│   └── Won_api_model.php
├── views/
│   ├── configuracoes.php
│   ├── configuracoes_form.php
│   └── instructions.php
├── install.php
├── uninstall.php
└── won_api.php
```

## 🤝 Contribuição

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues ou enviar pull requests.

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👨‍💻 Autor

- **Matheus Baiense** - [GitHub](https://github.com/Matheusbaiense)

## 📞 Suporte

Para suporte, entre em contato através do GitHub ou abra uma issue no repositório. 