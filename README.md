# Comunicação Interna (CI)
Aplicação para gerencimento de documentos referentes a comunicação interna da universidade.

## Funcionalidades

* Criação de Grupos na instituição
* Gerência de pessoal em Grupos
* Criação de Categorias de Documentos em Grupos
* Criação de Documentos
* Finalização de Documentos existentes (torná-los imutáveis)
* Exportação de Documentos em PDF
* Criação de Templates para padronização de Documentos

### Testando envio de e-mails utilizando a plataforma Mailtrap

Utilizando a plataforma [Mailtrap](https://mailtrap.io/) é possível capturar os e-mails enviados sem que estes cheguem à caixa de entrada dos destinatários, possibilitando assim testar e analisar o envio de e-mails antes de se colocar em produção.

__Como Utilizar__
    
Após criar e entrar com uma conta na plataforma, é possível gerar as credenciais para o envio de e-mail no sistema utilizado, no caso do Laravel as credenciais seriam semelhantes à figura a seguir:

![image](https://user-images.githubusercontent.com/47902146/206538191-1b75750d-819b-4bc6-a8cf-efd7b8bf993b.png)

Assim, basta substituir tais credenciais no `.env` do projeto e enviar os e-mails normalmente que estes serão capturados na caixa de entrada do Mailtrap, sem serem enviados aos seus destinatários.

## Requisitos

* PHP 8.2
* Conexão com banco de dados
* Token oauth
* Acesso ao replicado

### Em produção

Para receber as últimas atualizações do sistema rode:

```sh
git pull
composer install --no-dev
php artisan migrate
```

## Instalação

```sh
git clone git@github.com:uspdev/ci
composer install
cp .env.example .env
php artisan key:generate
```

Configure o .env conforme a necessidade

### Apache ou nginx

Deve apontar para a <pasta do projeto>/public, assim como qualquer projeto laravel.

No Apache é possivel utilizar a extensão MPM-ITK (http://mpm-itk.sesse.net/) que permite rodar seu Servidor Virtual com usuário próprio. Isso facilita rodar o sistema como um usuário comum e não precisa ajustar as permissões da pasta storage/.

```bash
sudo apt install libapache2-mpm-itk
sudo a2enmod mpm_itk
sudo service apache2 restart
```

Dentro do seu virtualhost coloque

```apache
<IfModule mpm_itk_module>
AssignUserId nome_do_usuario nome_do_grupo
</IfModule>
```

### Senha única

Cadastre uma nova URL no configurador de senha única utilizando o caminho https://seu_app/callback. Guarde o callback_id para colocar no arquivo .env.

### Banco de dados

* DEV

    `php artisan migrate:fresh --seed`

* Produção

    `php artisan migrate`
