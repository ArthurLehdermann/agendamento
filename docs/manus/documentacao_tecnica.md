# Documentação Técnica - Sistema de Agendamento SaaS

## Visão Geral da Arquitetura

O Sistema de Agendamento SaaS foi desenvolvido utilizando uma arquitetura moderna e escalável, baseada em Laravel com Vue.js para o frontend e PostgreSQL para o banco de dados. A aplicação segue o padrão MVC (Model-View-Controller) e implementa um sistema multi-tenant para separar os dados de diferentes empresas.

## Stack Tecnológico

### Backend
- **Framework**: Laravel 10.x
- **PHP**: 8.4
- **Banco de Dados**: PostgreSQL
- **Admin Panel**: Laravel Voyager
- **Autenticação**: Laravel Breeze

### Frontend
- **Framework JS**: Vue.js 3
- **CSS Framework**: Bootstrap 5
- **Biblioteca de Gráficos**: Chart.js
- **Biblioteca de Calendário**: FullCalendar
- **PWA**: Implementado para uso mobile

### Infraestrutura
- **Containerização**: Docker
- **Servidor Web**: Nginx
- **Cache**: Redis
- **Processamento de Filas**: Laravel Horizon

## Estrutura do Banco de Dados

### Principais Tabelas

1. **tenants**: Armazena informações das empresas/negócios
   - id, name, domain, settings, is_active, created_at, updated_at

2. **users**: Armazena informações dos usuários
   - id, name, email, password, tenant_id, role, created_at, updated_at

3. **services**: Armazena informações dos serviços oferecidos
   - id, tenant_id, name, description, duration, price, is_active, created_at, updated_at

4. **resources**: Armazena informações dos recursos (baias/profissionais)
   - id, tenant_id, name, type, description, working_hours, is_active, created_at, updated_at

5. **appointments**: Armazena informações dos agendamentos
   - id, tenant_id, service_id, resource_id, user_id, customer_name, customer_email, customer_phone, start_time, end_time, status, notes, created_at, updated_at

## Modelos e Relacionamentos

### Tenant
- hasMany: Service, Resource, Appointment, User

### User
- belongsTo: Tenant
- hasMany: Appointment

### Service
- belongsTo: Tenant
- hasMany: Appointment

### Resource
- belongsTo: Tenant
- hasMany: Appointment

### Appointment
- belongsTo: Tenant, Service, Resource, User

## Controladores Principais

### TenantController
Gerencia operações CRUD para empresas/negócios.

### ServiceController
Gerencia operações CRUD para serviços oferecidos.

### ResourceController
Gerencia operações CRUD para recursos (baias/profissionais).

### AppointmentController
Gerencia operações CRUD para agendamentos, além de funcionalidades específicas:
- Verificação de disponibilidade
- Prevenção de conflitos de horário
- Visualização de calendário

### DashboardController
Gerencia a exibição de estatísticas e métricas:
- Dashboard principal
- Dashboard de recursos
- Dashboard de serviços
- Dashboard de clientes

## Rotas Principais

```php
// Autenticação
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/resources', [DashboardController::class, 'resources'])->name('dashboard.resources');
    Route::get('/dashboard/services', [DashboardController::class, 'services'])->name('dashboard.services');
    Route::get('/dashboard/customers', [DashboardController::class, 'customers'])->name('dashboard.customers');
    
    // Recursos
    Route::resource('resources', ResourceController::class);
    
    // Serviços
    Route::resource('services', ServiceController::class);
    
    // Agendamentos
    Route::resource('appointments', AppointmentController::class);
    Route::get('/appointments/calendar', [AppointmentController::class, 'calendar'])->name('appointments.calendar');
    Route::get('/appointments/check-availability', [AppointmentController::class, 'checkAvailability'])->name('appointments.check-availability');
    Route::get('/appointments/get-appointments', [AppointmentController::class, 'getAppointments'])->name('appointments.get-appointments');
});
```

## Middleware e Segurança

### TenantMiddleware
Garante que usuários só acessem dados do seu próprio tenant.

### RoleMiddleware
Controla acesso baseado em papéis (admin, operator, customer).

### Validação
Todas as requisições são validadas usando Form Requests do Laravel.

## Funcionalidades Principais

### Sistema Multi-tenant
- Separação completa de dados entre diferentes empresas
- Subdomínios personalizados para cada tenant

### Gestão de Serviços
- CRUD completo para serviços
- Configuração de duração para bloqueio automático da agenda

### Gestão de Recursos
- CRUD completo para recursos (baias/profissionais)
- Configuração de horários de trabalho

### Agendamento
- Criação, edição e cancelamento de agendamentos
- Verificação automática de disponibilidade
- Prevenção de conflitos de horário
- Visualização de calendário (dia, semana, mês)

### Painel de Gestão
- Estatísticas e métricas em tempo real
- Gráficos interativos
- Análise de desempenho

## Configuração do Ambiente de Desenvolvimento

### Requisitos
- Docker e Docker Compose
- Git

### Passos para Instalação
1. Clone o repositório: `git clone https://github.com/ArthurLehdermann/agendamento.git`
2. Entre no diretório: `cd agendamento`
3. Copie o arquivo de ambiente: `cp .env.example .env`
4. Configure as variáveis de ambiente no arquivo `.env`
5. Inicie os containers: `docker-compose up -d`
6. Instale as dependências: `docker-compose exec app composer install`
7. Gere a chave da aplicação: `docker-compose exec app php artisan key:generate`
8. Execute as migrações: `docker-compose exec app php artisan migrate --seed`
9. Instale o Voyager: `docker-compose exec app php artisan voyager:install --with-dummy`
10. Instale as dependências do frontend: `docker-compose exec app npm install`
11. Compile os assets: `docker-compose exec app npm run dev`

## Testes

### Testes Unitários
Executar: `docker-compose exec app php artisan test --testsuite=Unit`

### Testes de Integração
Executar: `docker-compose exec app php artisan test --testsuite=Feature`

## Implantação em Produção

### Requisitos
- Servidor com Docker e Docker Compose
- Domínio configurado

### Passos para Implantação
1. Clone o repositório no servidor
2. Configure o arquivo `.env` para produção
3. Execute `docker-compose -f docker-compose.prod.yml up -d`
4. Configure o servidor web para apontar para o domínio

## Manutenção e Monitoramento

### Logs
Os logs da aplicação são armazenados em `/storage/logs/laravel.log`

### Monitoramento
Recomenda-se o uso de ferramentas como New Relic ou Laravel Telescope para monitoramento em produção.

### Backups
Backups automáticos do banco de dados são configurados para execução diária.

## Extensões e Personalizações

O sistema foi projetado para ser facilmente extensível. Novos módulos podem ser adicionados seguindo o padrão MVC do Laravel.

### Adicionando Novos Tipos de Recursos
Edite o arquivo `app/Models/Resource.php` para adicionar novos tipos de recursos.

### Personalizando o Calendário
As configurações do calendário podem ser ajustadas em `resources/views/appointments/calendar.blade.php`.

## Considerações de Segurança

- Todas as senhas são armazenadas com hash bcrypt
- Proteção contra CSRF em todos os formulários
- Validação de entrada em todas as requisições
- Escape de saída em todas as views
- Implementação de políticas de acesso baseadas em papéis

## Conformidade com LGPD

- Consentimento explícito para coleta de dados
- Opção de exclusão de dados pessoais
- Documentação clara sobre uso de dados
- Medidas técnicas para proteção de dados pessoais

## Suporte e Contato

Para suporte técnico ou dúvidas sobre a implementação, entre em contato:
- Email: suporte@agendamento-saas.com
- GitHub: https://github.com/ArthurLehdermann/agendamento
