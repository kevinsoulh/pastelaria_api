# 🥟 Pastelaria API

Uma API completa para gerenciamento de pastelaria construída com Laravel 12, PHP 8.4 e arquitetura moderna.

## ✨ Funcionalidades

- 🔐 **Sistema de Autenticação** com Sanctum
- 👥 **Gerenciamento de Usuários** (Admin, Vendas, Atendentes)
- 🛒 **Gestão de Clientes** com dados completos
- 🥟 **Catálogo de Produtos** com categorias (Salgados, Doces, Especiais)
- 📸 **Sistema de Fotos** com storage público e URLs automáticas
- 📋 **Sistema de Pedidos** com relacionamentos e cálculos automáticos
- 🏗️ **Repository Pattern** para organização do código
- 📊 **Seeders Completos** com dados de teste realistas
- 🧪 **Testes Automatizados** (199 testes com 1500+ assertions e 92.7% coverage)
- 📧 **Sistema de Email** com MailHog configurado
- 🐳 **Docker Setup** com ambiente completo

---

## 🚀 Setup Rápido

### 1. Clone o Repositório
```bash
git clone <repository-url>
cd comerc
```

### 2. Configure o Docker
```bash
# Construir e iniciar os containers
docker compose up --build -d

# Verificar se os containers estão rodando
docker compose ps
```

### 3. Configure o Laravel
```bash
# Acessar o container da aplicação
docker exec -it app bash

# Dentro do container:
cd /var/www/app

# Gerar chave da aplicação
php artisan key:generate

# Rodar migrações e seeders
php artisan migrate:fresh --seed
```

### 4. Verificar Instalação
```bash
# Testar a API
curl http://localhost/api/health

# Verificar dados criados
php artisan tinker --execute="echo 'Users: ' . \App\Models\User::count() . PHP_EOL;"
```

🎉 **Pronto!** A API estará disponível em `http://localhost`

---

## 🔧 Comandos Essenciais

### Docker
```bash
# Iniciar ambiente
docker compose up -d

# Parar ambiente  
docker compose down

# Reconstruir containers
docker compose up --build -d

# Ver logs
docker compose logs -f app

# Acessar container da aplicação
docker exec -it app bash
```

### Laravel (dentro do container)
```bash
# Migrações
php artisan migrate:fresh --seed    # Reset completo com dados
php artisan migrate                  # Apenas migrações
php artisan migrate:rollback        # Desfazer migrações

# Seeders
php artisan db:seed                         # Todos os seeders
php artisan db:seed --class=UserSeeder     # Seeder específico

# Testes
php artisan test                            # Todos os testes (199 testes)
php artisan test --coverage                # Com cobertura (92.7%)
php artisan test tests/Feature/            # Apenas Feature tests
php artisan test tests/Unit/               # Apenas Unit tests

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Artisan úteis
php artisan route:list              # Listar todas as rotas
php artisan make:model Product      # Criar model
php artisan make:controller ProductController --api
```

---

## 🛠️ Troubleshooting

### ❌ Problema: Timeout no Composer Install

**Sintoma:** Erro de timeout durante `composer install` no Docker build

**Soluções:**

#### Opção 1: Comentar Composer Install no Dockerfile
```dockerfile
# Comentar esta linha no docker/Dockerfile
# RUN composer install --no-interaction --prefer-dist
```

Depois instalar manualmente:
```bash
# Entrar no container
docker exec -it app bash

# Instalar dependências manualmente
cd /var/www/app
composer install --no-interaction --prefer-dist
```

#### Opção 2: Aumentar Timeout
```bash
# No container, antes de instalar
composer config --global process-timeout 0
composer install --no-interaction --prefer-dist
```

#### Opção 3: Usar Cache do Host
```bash
# No host (sua máquina), na pasta domain/
composer install --no-dev --optimize-autoloader

# Depois subir o Docker normalmente
```

### ❌ Problema: Permissões de Arquivo

**Sintoma:** Erros de permissão em `storage/` ou `bootstrap/cache/`

**Solução:**
```bash
# Dentro do container
chown -R www-data:www-data /var/www/app
chmod -R 775 /var/www/app/storage /var/www/app/bootstrap/cache
```

### ❌ Problema: Banco de Dados não Conecta

**Sintoma:** Connection refused para MySQL

**Verificações:**
```bash
# Verificar se MySQL está rodando
docker compose ps

# Ver logs do MySQL
docker compose logs mysql_8

# Testar conexão dentro do container
docker exec -it app php artisan tinker --execute="DB::connection()->getPdo();"
```

### ❌ Problema: Porta 80 em Uso

**Sintoma:** Port 80 already in use

**Solução:**
```bash
# Parar Apache/Nginx local
sudo systemctl stop apache2
sudo systemctl stop nginx

# Ou alterar porta no docker-compose.yml
ports:
  - "8080:80"  # Usar porta 8080 instead
```

---

## 📊 Dados de Teste Criados

Os seeders criam automaticamente:

### 👤 Usuários (8 total)
**Administradores:**
- `admin@pastelaria.com` - Administrador da Pastelaria
- `vendas@pastelaria.com` - Gerente de Vendas  
- `atendente@pastelaria.com` - Atendente

**Senha padrão:** `password`

**+ 5 usuários aleatórios**

### 🏪 Clientes (20 total)
- 5 clientes predefinidos com dados específicos
- 15 clientes gerados aleatoriamente

### 🥟 Produtos (14 total)
- **8 Salgados:** Carne, Queijo, Frango, Pizza, etc.
- **3 Doces:** Doce de leite, Chocolate, Brigadeiro  
- **3 Especiais:** Combinações especiais
- **📸 Todos com fotos reais** servidas via storage público

### 📋 Pedidos (25 total)
- Relacionamentos completos com produtos
- 6 status diferentes: pending, confirmed, preparing, ready, delivered, cancelled
- Datas dos últimos 30 dias
- Cálculos automáticos de totais

---

### 🧪 Testes e Qualidade de Código

### Coverage de Testes - 92.7% 🎯
```bash
# Executar todos os testes
./vendor/bin/phpunit

# Testes com coverage
./vendor/bin/phpunit --coverage-html coverage

# Testes específicos
./vendor/bin/phpunit tests/Feature
./vendor/bin/phpunit tests/Unit
```

**📊 Estatísticas Atuais:**
- **199 testes** com **1500+ assertions** 
- **92.7% coverage** (industry-leading)
- **105 Unit Tests** + **94 Feature Tests**
- **100% coverage:** Models, Notifications, Traits, Repositories

### Componentes com Coverage Completo ✅
- 🎯 **BaseRepository** - Padrão Repository implementado
- 📧 **Notifications** - Sistema de email totalmente testado  
- 👤 **User Model** - Modelo principal com testes abrangentes
- 🔧 **ApiResponseTrait** - Responses padronizadas da API
- 📝 **Factories & Seeders** - Geradores de dados de teste

---

## 📧 Sistema de Email com MailHog

A aplicação inclui sistema completo de notificações por email com MailHog para desenvolvimento.

### 🔧 Configuração
O MailHog já está configurado no Docker Compose e pronto para uso:
- **SMTP Server:** mailhog:1025
- **Web Interface:** http://localhost:8025
- **From Address:** noreply@pastelaria.com

### 📬 Notificações Disponíveis

#### 1. Email de Boas-Vindas para Clientes
```bash
# Enviar para cliente específico
php artisan email:test --customer-id=1

# Enviar para primeiro cliente
php artisan email:test
```

#### 2. Confirmação de Pedidos
```bash
# Enviar para pedido específico
php artisan email:test-order --order-id=1

# Enviar para primeiro pedido
php artisan email:test-order
```

### 🎯 Testar Emails

1. **Executar comandos de teste**
```bash
# Dentro do container
docker exec -it app php artisan email:test
docker exec -it app php artisan email:test-order
```

2. **Acessar MailHog**
- Abra http://localhost:8025 no navegador
- Visualize todos os emails enviados
- Interface web amigável com preview

### 📝 Tipos de Email
- ✅ **Boas-vindas:** Enviado automaticamente para clientes predefinidos no seeder
- ✅ **Confirmação de Pedidos:** Contém detalhes completos do pedido, itens e total
- 🎨 **Template Responsivo:** Emails formatados com Laravel Mail

### 🔧 Desenvolvimento
```bash
# Comandos de email para desenvolvimento
php artisan email:test                    # Testar email de boas-vindas
php artisan email:test-order              # Testar email de pedido  
php artisan email:info                    # Ver configuração e estatísticas

# Verificar configuração de email
php artisan tinker --execute="echo config('mail.mailers.smtp.host');"

# Limpar fila de emails
php artisan queue:clear

# Testar conexão SMTP
php artisan tinker --execute="Mail::raw('Test email', function(\$message) { \$message->to('test@example.com')->subject('Test'); });"
```

**Nota:** O sistema de email está totalmente configurado e integrado com os testes automatizados!

---

## 📸 Sistema de Fotos

### Funcionalidades
- **📁 Storage Público:** Fotos armazenadas em `storage/app/public/pastels/`
- **🔗 URLs Automáticas:** Campo `photo_url` gerado automaticamente nas respostas da API
- **✅ Validação:** Foto obrigatória para novos produtos
- **🖼️ 14 Fotos Reais:** Todas as pastéis têm fotos (400x300px)

### Estrutura
```bash
storage/app/public/pastels/
├── pastel_carne.jpg
├── pastel_queijo.jpg  
├── pastel_frango.jpg
├── pastel_chocolate.jpg
└── ... (14 fotos total)
```

### Exemplo de Resposta da API
```json
{
  "id": 1,
  "name": "Pastel de Carne",
  "photo": "pastels/pastel_carne.jpg",
  "photo_url": "http://localhost/storage/pastels/pastel_carne.jpg"
}
```

### Acesso às Fotos
- **Via Web:** `http://localhost/storage/pastels/pastel_carne.jpg`
- **Via API:** Campo `photo_url` em todas as respostas de produtos
- **Storage Link:** Configurado automaticamente no Docker

---

## 🌐 Endpoints da API

### Autenticação
```bash
POST /api/auth/login
POST /api/auth/register
POST /api/auth/logout
```

### Usuários
```bash
GET    /api/users
POST   /api/users
GET    /api/users/{id}
PUT    /api/users/{id}
DELETE /api/users/{id}
```

### Clientes
```bash
GET    /api/customers
POST   /api/customers
GET    /api/customers/{id}
PUT    /api/customers/{id}
DELETE /api/customers/{id}
```

### Produtos
```bash
GET    /api/products
POST   /api/products    # ⚠️ Foto obrigatória
GET    /api/products/{id}
PUT    /api/products/{id}
DELETE /api/products/{id}
```
**📸 Nota:** Todos os produtos incluem campo `photo_url` nas respostas

### Pedidos
```bash
GET    /api/orders
POST   /api/orders
GET    /api/orders/{id}
PUT    /api/orders/{id}
DELETE /api/orders/{id}
```

---

## 🏗️ Arquitetura

### Repository Pattern
```
app/
├── Http/Controllers/Api/     # Controllers da API
├── Models/                   # Eloquent Models
├── Repositories/
│   ├── Interfaces/          # Contratos dos repositórios
│   └── Eloquent/            # Implementações Eloquent
├── Http/Requests/           # Form Requests com validação
└── Traits/                  # Traits reutilizáveis
```

### Principais Features
- **BaseRepository:** CRUD genérico reutilizável
- **ApiResponseTrait:** Respostas padronizadas da API
- **Form Requests:** Validação robusta de entrada
- **Resource Classes:** Formatação de saída da API
- **Database Seeders:** Dados realistas para desenvolvimento

---

## 🐳 Stack Tecnológica

- **Backend:** Laravel 12 + PHP 8.4
- **Banco:** MySQL 8.0
- **Cache:** Redis 6.2
- **Email:** MailHog (desenvolvimento)
- **Container:** Docker + Docker Compose
- **Web Server:** Nginx
- **Autenticação:** Laravel Sanctum
- **Testes:** PHPUnit + Feature Tests

---

## 📝 Ambiente de Desenvolvimento

### Estrutura Docker
- **app:** Laravel + PHP 8.4 + Nginx
- **mysql_8:** MySQL 8.0 para dados
- **redis:** Redis 6.2 para cache/sessions
- **mailhog:** MailHog para captura de emails

### Portas
- **80:** Aplicação web
- **3306:** MySQL
- **6379:** Redis
- **8025:** MailHog Web UI
- **1025:** MailHog SMTP

### Volumes Persistentes
- `mysql-data-comerc`: Dados do MySQL
- `redis-data-comerc`: Dados do Redis
- `./domain`: Código da aplicação (bind mount)
- `storage/app/public`: Fotos dos produtos acessíveis via web

---

## 📄 Licença

Este projeto é open-source sob a licença [MIT](https://opensource.org/licenses/MIT).
