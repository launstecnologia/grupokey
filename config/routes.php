<?php

use App\Core\Router;

$router = new Router();

// Configurar prefixo dinâmico baseado na constante FOLDER
// A constante FOLDER já foi definida pelo AutoConfig
$router->setPrefix(FOLDER);

// Rotas de autenticação
$router->get('/', 'AuthController@showLogin');
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');

// Rotas de configurações de email (apenas admin)
$router->get('/email-settings', 'EmailSettingsController@index');
$router->post('/email-settings/update', 'EmailSettingsController@update');
$router->post('/email-settings/test', 'EmailSettingsController@test');

// Rotas de configurações API SistPay (apenas admin)
$router->get('/sistpay-settings', 'SistPaySettingsController@index');
$router->post('/sistpay-settings/update', 'SistPaySettingsController@update');
$router->post('/sistpay-settings/test', 'SistPaySettingsController@test');

// Webhook do SistPay (público - sem autenticação)
$router->post('/sistpay/webhook', 'SistPayWebhookController@handle');

// Rotas do dashboard
$router->get('/dashboard', 'DashboardController@index');

// Rotas de estabelecimentos
// IMPORTANTE: Rotas específicas devem vir ANTES de rotas com parâmetros dinâmicos
$router->get('/estabelecimentos/import', 'EstablishmentController@import');
$router->post('/estabelecimentos/import', 'EstablishmentController@processImport');
$router->get('/estabelecimentos/create', 'EstablishmentController@create');
$router->get('/estabelecimentos', 'EstablishmentController@index');
$router->post('/estabelecimentos', 'EstablishmentController@store');
$router->get('/estabelecimentos/{id}/edit', 'EstablishmentController@edit');
$router->get('/estabelecimentos/{id}/documentos/{documentId}/download', 'EstablishmentController@downloadDocument');
$router->post('/estabelecimentos/{id}/migrate-sistpay', 'EstablishmentController@migrateToSistPay');
$router->post('/estabelecimentos/{id}/approve', 'EstablishmentController@approve');
$router->post('/estabelecimentos/{id}/reprove', 'EstablishmentController@reprove');
$router->get('/estabelecimentos/{id}', 'EstablishmentController@show');
$router->put('/estabelecimentos/{id}', 'EstablishmentController@update');
$router->delete('/estabelecimentos/{id}', 'EstablishmentController@destroy');

// Rotas de representantes (apenas admin)
$router->get('/representantes', 'RepresentativeController@index');
$router->get('/representantes/create', 'RepresentativeController@create');
$router->post('/representantes', 'RepresentativeController@store');
$router->get('/representantes/{id}', 'RepresentativeController@show');
    $router->get('/representantes/{id}/edit', 'RepresentativeController@edit');
    $router->post('/representantes/{id}/edit', 'RepresentativeController@edit'); // Rota POST para processar o formulário
$router->put('/representantes/{id}', 'RepresentativeController@update');
$router->delete('/representantes/{id}', 'RepresentativeController@destroy');
$router->post('/representantes/{id}/reset-password', 'RepresentativeController@resetPassword');
$router->post('/representantes/{id}/toggle-status', 'RepresentativeController@toggleStatus');

// Rotas de usuários (apenas admin)
$router->get('/usuarios', 'UserController@index');
$router->get('/usuarios/create', 'UserController@create');
$router->post('/usuarios', 'UserController@store');
$router->get('/usuarios/{id}', 'UserController@show');
$router->get('/usuarios/{id}/edit', 'UserController@edit');
$router->post('/usuarios/{id}/edit', 'UserController@edit'); // Rota POST para processar o formulário
$router->put('/usuarios/{id}', 'UserController@update');
$router->delete('/usuarios/{id}', 'UserController@destroy');
$router->post('/usuarios/{id}/reset-password', 'UserController@resetPassword');
$router->post('/usuarios/{id}/toggle-status', 'UserController@toggleStatus');

// Rotas de chamados
$router->get('/chamados', 'TicketController@index');
$router->get('/chamados/create', 'TicketController@create');
$router->post('/chamados', 'TicketController@store');
$router->get('/chamados/{id}', 'TicketController@show');
$router->get('/chamados/{id}/edit', 'TicketController@edit');
$router->put('/chamados/{id}', 'TicketController@update');
$router->delete('/chamados/{id}', 'TicketController@destroy');
$router->post('/chamados/{id}/responder', 'TicketController@responder');
$router->post('/chamados/{id}/fechar', 'TicketController@fechar');

// Rotas de material de apoio
$router->get('/material', 'MaterialController@index');
$router->get('/material/download/{id}', 'MaterialController@download');

// Administração - Categorias
$router->get('/material/categories', 'MaterialController@categories');
$router->get('/material/categories/create', 'MaterialController@createCategory');
$router->post('/material/categories', 'MaterialController@storeCategory');
$router->get('/material/categories/{id}/edit', 'MaterialController@editCategory');
$router->put('/material/categories/{id}', 'MaterialController@updateCategory');
$router->delete('/material/categories/{id}', 'MaterialController@destroyCategory');

// Administração - Subcategorias
$router->get('/material/subcategories', 'MaterialController@subcategories');
$router->get('/material/subcategories/create', 'MaterialController@createSubcategory');
$router->post('/material/subcategories', 'MaterialController@storeSubcategory');
$router->get('/material/subcategories/{id}/edit', 'MaterialController@editSubcategory');
$router->put('/material/subcategories/{id}', 'MaterialController@updateSubcategory');
$router->delete('/material/subcategories/{id}', 'MaterialController@destroySubcategory');

// Administração - Arquivos
$router->get('/material/files', 'MaterialController@files');
$router->get('/material/files/create', 'MaterialController@createFile');
$router->post('/material/files', 'MaterialController@storeFile');
$router->get('/material/files/{id}/edit', 'MaterialController@editFile');
$router->put('/material/files/{id}', 'MaterialController@updateFile');
$router->delete('/material/files/{id}', 'MaterialController@destroyFile');

// Rotas de segmentos
$router->get('/segmentos', 'SegmentController@index');
$router->get('/segmentos/create', 'SegmentController@create');
$router->post('/segmentos', 'SegmentController@store');
$router->get('/segmentos/{id}/edit', 'SegmentController@edit');
$router->put('/segmentos/{id}', 'SegmentController@update');
$router->delete('/segmentos/{id}', 'SegmentController@destroy');
$router->post('/segmentos/{id}/toggle-status', 'SegmentController@toggleStatus');

// Rotas de perfil
$router->get('/perfil', 'ProfileController@index');
$router->get('/perfil/edit', 'ProfileController@edit');
$router->put('/perfil', 'ProfileController@update');
$router->post('/perfil/update', 'ProfileController@update');
$router->get('/change-password', 'ProfileController@showChangePassword');
$router->post('/change-password', 'ProfileController@changePassword');
$router->post('/perfil/change-password', 'ProfileController@changePassword');

// Rotas de upload
$router->post('/upload/document', 'UploadController@document');

// Rotas de API
$router->get('/api/buscar-cnpj', 'ApiController@buscarCnpj');
$router->post('/api/buscar-cnpj', 'ApiController@buscarCnpj');

// Rotas de faturamento
$router->get('/billing', 'BillingController@index');
$router->get('/billing/create', 'BillingController@create');
$router->post('/billing', 'BillingController@store');
$router->get('/billing/{id}', 'BillingController@show');
$router->delete('/billing/{id}', 'BillingController@destroy');
$router->get('/billing/{id}/export', 'BillingController@export');
$router->get('/billing/search-establishments', 'BillingController@searchEstablishments');
$router->post('/billing/link-establishment', 'BillingController@linkEstablishment');
$router->post('/billing/unlink-establishment', 'BillingController@unlinkEstablishment');

// Rotas de E-mail Marketing
$router->get('/email-marketing', 'EmailMarketingController@index');
$router->get('/email-marketing/create', 'EmailMarketingController@create');
$router->post('/email-marketing', 'EmailMarketingController@store');
$router->get('/email-marketing/{id}', 'EmailMarketingController@show');
$router->get('/email-marketing/{id}/edit', 'EmailMarketingController@edit');
$router->put('/email-marketing/{id}', 'EmailMarketingController@update');
$router->delete('/email-marketing/{id}', 'EmailMarketingController@destroy');
$router->get('/email-marketing/{id}/recipients', 'EmailMarketingController@recipients');
$router->post('/email-marketing/{id}/recipients', 'EmailMarketingController@addRecipients');
$router->delete('/email-marketing/{campaignId}/recipients/{recipientId}', 'EmailMarketingController@removeRecipient');
$router->post('/email-marketing/{id}/start', 'EmailMarketingController@start');
$router->post('/email-marketing/{id}/pause', 'EmailMarketingController@pause');
$router->post('/email-marketing/{id}/cancel', 'EmailMarketingController@cancel');
$router->delete('/email-marketing/{campaignId}/attachments/{attachmentId}', 'EmailMarketingController@removeAttachment');
$router->get('/email-marketing/process-queue', 'EmailMarketingController@processQueue');
$router->post('/email-marketing/process-queue', 'EmailMarketingController@processQueue');

// Rotas de CRM (apenas admin)
$router->get('/crm', 'CRMController@index');
$router->post('/crm/move-deal', 'CRMController@moveDeal');
$router->post('/crm/update-deal-order', 'CRMController@updateDealOrder');

// Rotas de Deals
$router->get('/crm/deals/create', 'CRMController@createDeal');
$router->post('/crm/deals', 'CRMController@storeDeal');
$router->get('/crm/deals/{id}', 'CRMController@showDeal');
$router->get('/crm/deals/{id}/edit', 'CRMController@editDeal');
$router->put('/crm/deals/{id}', 'CRMController@updateDeal');
$router->delete('/crm/deals/{id}', 'CRMController@deleteDeal');
$router->post('/crm/deals/{id}/activities', 'CRMController@addActivity');

// Rotas de Pipelines (configuração)
$router->get('/crm/pipelines', 'CRMController@pipelines');
$router->get('/crm/pipelines/create', 'CRMController@createPipeline');
$router->post('/crm/pipelines', 'CRMController@storePipeline');
$router->get('/crm/pipelines/{id}/edit', 'CRMController@editPipeline');
$router->put('/crm/pipelines/{id}', 'CRMController@updatePipeline');
$router->delete('/crm/pipelines/{id}', 'CRMController@deletePipeline');

// Rotas de Stages (configuração)
$router->get('/crm/pipelines/{pipelineId}/stages', 'CRMController@stages');
$router->get('/crm/pipelines/{pipelineId}/stages/create', 'CRMController@createStage');
$router->post('/crm/pipelines/{pipelineId}/stages', 'CRMController@storeStage');
$router->get('/crm/pipelines/{pipelineId}/stages/{id}/edit', 'CRMController@editStage');
$router->put('/crm/pipelines/{pipelineId}/stages/{id}', 'CRMController@updateStage');
$router->delete('/crm/pipelines/{pipelineId}/stages/{id}', 'CRMController@deleteStage');
$router->post('/crm/pipelines/{pipelineId}/stages/update-order', 'CRMController@updateStageOrder');

// Rotas de Tarefas
$router->post('/crm/deals/{dealId}/tasks', 'CRMController@createTask');
$router->post('/crm/tasks/{taskId}/complete', 'CRMController@completeTask');
$router->delete('/crm/tasks/{taskId}', 'CRMController@deleteTask');

// Rotas de Notificações
$router->get('/crm/notifications', 'CRMController@getNotifications');
$router->post('/crm/notifications/{id}/read', 'CRMController@markNotificationRead');
$router->post('/crm/notifications/read-all', 'CRMController@markAllNotificationsRead');

// Processamento de lembretes (cron)
$router->get('/crm/process-reminders', 'CRMController@processTaskReminders');
$router->post('/crm/process-reminders', 'CRMController@processTaskReminders');

// ===========================================
// ROTAS DO SISTEMA WHATSAPP
// ===========================================

// Webhook da Evolution API (público - sem autenticação)
$router->get('/whatsapp/webhook', 'WhatsAppWebhookController@ping');
$router->post('/whatsapp/webhook', 'WhatsAppWebhookController@handle');

// Rotas de Instâncias WhatsApp (apenas admin)
$router->get('/whatsapp/instances', 'WhatsAppController@instances');
$router->get('/whatsapp/instances/create', 'WhatsAppController@createInstance');
$router->post('/whatsapp/instances', 'WhatsAppController@createInstance');
$router->get('/whatsapp/instances/{id}', 'WhatsAppController@showInstance');
$router->post('/whatsapp/instances/{id}/connect', 'WhatsAppController@connect');
$router->post('/whatsapp/instances/{id}/disconnect', 'WhatsAppController@disconnect');
$router->post('/whatsapp/instances/{id}/check-status', 'WhatsAppController@checkStatus');
$router->post('/whatsapp/instances/{id}/set-webhook', 'WhatsAppController@setWebhook');
$router->post('/whatsapp/instances/{id}/delete', 'WhatsAppController@deleteInstance');

// Rotas de Filas WhatsApp (apenas admin)
$router->get('/whatsapp/queues', 'WhatsAppQueueController@index');
$router->get('/whatsapp/queues/create', 'WhatsAppQueueController@create');
$router->post('/whatsapp/queues', 'WhatsAppQueueController@create');
$router->get('/whatsapp/queues/{id}/edit', 'WhatsAppQueueController@edit');
$router->post('/whatsapp/queues/{id}/edit', 'WhatsAppQueueController@edit');
$router->delete('/whatsapp/queues/{id}', 'WhatsAppQueueController@delete');

// Rotas de Atendimento (requer autenticação)
$router->get('/whatsapp/attendance', 'WhatsAppAttendanceController@index');
$router->post('/whatsapp/attendance/start-conversation', 'WhatsAppAttendanceController@startConversationByNumber');
$router->get('/whatsapp/attendance/conversations/{id}/open', 'WhatsAppAttendanceController@openConversation');
$router->post('/whatsapp/attendance/messages/send', 'WhatsAppAttendanceController@sendMessage');
$router->post('/whatsapp/attendance/upload-media', 'WhatsAppAttendanceController@uploadMedia');
$router->post('/whatsapp/attendance/{id}/close', 'WhatsAppAttendanceController@closeAttendance');
$router->post('/whatsapp/attendance/{id}/transfer', 'WhatsAppAttendanceController@transferAttendance');
$router->get('/whatsapp/attendance/conversations/{id}/messages', 'WhatsAppAttendanceController@getNewMessages');

// Executar roteamento
$router->dispatch();
