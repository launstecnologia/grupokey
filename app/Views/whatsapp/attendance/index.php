<?php
$currentPage = 'whatsapp-attendance';
ob_start();

$conversations = $conversations ?? [];
$queues = $queues ?? [];
$filters = $filters ?? [];
$connectedInstances = $connected_instances ?? [];
?>

<div class="pt-6 px-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <i class="fab fa-whatsapp mr-2 text-green-500"></i>
                    Atendimento WhatsApp
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Gerencie suas conversas e atendimentos</p>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('modal-new-conversation').classList.remove('hidden')" 
                        class="bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-500 text-white font-medium px-4 py-2 rounded-lg inline-flex items-center transition-colors shadow">
                    <i class="fas fa-plus mr-2"></i>
                    Adicionar número
                </button>
                <button onclick="refreshConversations()" class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500 text-white font-medium px-4 py-2 rounded-lg inline-flex items-center transition-colors shadow">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Atualizar
                </button>
            </div>
        </div>

        <!-- Modal Adicionar número -->
        <div id="modal-new-conversation" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if (event.target === this) this.classList.add('hidden')">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6" onclick="event.stopPropagation()">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    <i class="fas fa-phone-alt mr-2 text-green-500"></i>
                    Nova conversa
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Informe o número com DDD (ex: 5516999999999) para iniciar uma conversa.</p>
                <form id="form-new-conversation" onsubmit="startConversationByNumber(event)">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Número (WhatsApp)</label>
                        <input type="text" id="new-phone-number" placeholder="5516999999999" 
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               required>
                    </div>
                    <?php if (count($connectedInstances) > 1): ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instância</label>
                        <select id="new-instance-id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <?php foreach ($connectedInstances as $inst): ?>
                                <option value="<?= (int)$inst['id'] ?>"><?= htmlspecialchars($inst['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($queues)): ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fila (opcional)</label>
                        <select id="new-queue-id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Nenhuma</option>
                            <?php foreach ($queues as $q): ?>
                                <option value="<?= (int)$q['id'] ?>"><?= htmlspecialchars($q['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="document.getElementById('modal-new-conversation').classList.add('hidden')" 
                                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button type="submit" id="btn-start-conversation" class="bg-green-500 hover:bg-green-600 text-white font-medium px-4 py-2 rounded-lg inline-flex items-center">
                            <i class="fas fa-comment-dots mr-2"></i>
                            Iniciar conversa
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
            <div class="flex gap-4 flex-wrap">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select id="filter-status" class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="OPEN" <?= ($filters['status'] ?? '') === 'OPEN' ? 'selected' : '' ?>>Abertas</option>
                        <option value="PENDING" <?= ($filters['status'] ?? '') === 'PENDING' ? 'selected' : '' ?>>Pendentes</option>
                        <option value="CLOSED" <?= ($filters['status'] ?? '') === 'CLOSED' ? 'selected' : '' ?>>Fechadas</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fila</label>
                    <select id="filter-queue" class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Todas</option>
                        <?php foreach ($queues as $queue): ?>
                            <option value="<?= $queue['id'] ?>" <?= ($filters['queue_id'] ?? '') == $queue['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($queue['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                    <input type="text" id="filter-search" placeholder="Nome ou telefone..." 
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
        </div>

        <!-- Lista de Conversas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sidebar com conversas -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Conversas</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400" id="conversations-count">
                            <?= count($conversations) ?> conversa(s)
                        </p>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-[600px] overflow-y-auto" id="conversations-list">
                        <?php if (empty($conversations)): ?>
                            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-inbox text-4xl mb-4"></i>
                                <p>Nenhuma conversa encontrada</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversations as $conv): ?>
                                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer conversation-item" 
                                     data-conversation-id="<?= $conv['id'] ?>"
                                     onclick="openConversation(<?= $conv['id'] ?>)">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0">
                                            <?php if (!empty($conv['contact_picture'])): ?>
                                                <img src="<?= htmlspecialchars($conv['contact_picture']) ?>" 
                                                     alt="<?= htmlspecialchars($conv['contact_name'] ?? '') ?>"
                                                     class="w-12 h-12 rounded-full">
                                            <?php else: ?>
                                                <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center text-white font-semibold">
                                                    <?= strtoupper(substr($conv['contact_name'] ?? $conv['phone_number'] ?? '?', 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                                    <?= htmlspecialchars($conv['contact_name'] ?? $conv['phone_number'] ?? 'Sem nome') ?>
                                                </h3>
                                                <?php if ($conv['unread_messages'] > 0): ?>
                                                    <span class="bg-green-500 text-white text-xs font-bold rounded-full px-2 py-1">
                                                        <?= $conv['unread_messages'] ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                                <?= htmlspecialchars($conv['last_message_preview'] ?? 'Sem mensagens') ?>
                                            </p>
                                            <div class="flex items-center justify-between mt-1">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    <?= $conv['last_message_at'] ? date('d/m H:i', strtotime($conv['last_message_at'])) : '' ?>
                                                </span>
                                                <?php if ($conv['queue_name']): ?>
                                                    <span class="text-xs px-2 py-1 rounded" style="background-color: <?= htmlspecialchars($conv['queue_color'] ?? '#3B82F6') ?>20; color: <?= htmlspecialchars($conv['queue_color'] ?? '#3B82F6') ?>">
                                                        <?= htmlspecialchars($conv['queue_name']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Área de Chat -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm" id="chat-container" style="display: none;">
                    <!-- Header do Chat -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700" id="chat-header">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div id="chat-contact-avatar"></div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white" id="chat-contact-name"></h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400" id="chat-contact-phone"></p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="closeChat()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mensagens -->
                    <div class="h-[500px] overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900" id="chat-messages">
                        <!-- Mensagens serão carregadas aqui via JavaScript -->
                    </div>

                    <!-- Campo de envio -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <form id="message-form" onsubmit="sendMessage(event)">
                            <div class="flex gap-2">
                                <input type="text" 
                                       id="message-input" 
                                       placeholder="Digite sua mensagem..."
                                       class="flex-1 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                       required>
                                <button type="submit" 
                                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors">
                                    <i class="fab fa-whatsapp mr-2"></i>
                                    Enviar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Placeholder quando nenhuma conversa está aberta -->
                <div id="chat-placeholder" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
                    <i class="fab fa-whatsapp text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400">Selecione uma conversa para começar</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentConversationId = null;
let lastMessageId = 0;
let pollingInterval = null;

// Iniciar conversa por número (adicionar número)
async function startConversationByNumber(event) {
    event.preventDefault();
    const phoneInput = document.getElementById('new-phone-number');
    const phoneNumber = phoneInput.value.trim();
    if (!phoneNumber) {
        alert('Informe o número de telefone.');
        return;
    }
    const btn = document.getElementById('btn-start-conversation');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Iniciando...';
    const payload = { phone_number: phoneNumber };
    const instanceSelect = document.getElementById('new-instance-id');
    if (instanceSelect) payload.instance_id = parseInt(instanceSelect.value, 10);
    const queueSelect = document.getElementById('new-queue-id');
    if (queueSelect && queueSelect.value) payload.queue_id = parseInt(queueSelect.value, 10);
    try {
        const response = await fetch('<?= url('whatsapp/attendance/start-conversation') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.success) {
            document.getElementById('modal-new-conversation').classList.add('hidden');
            phoneInput.value = '';
            currentConversationId = data.conversation_id;
            lastMessageId = 0;
            document.getElementById('chat-container').style.display = 'block';
            document.getElementById('chat-placeholder').style.display = 'none';
            document.getElementById('chat-contact-name').textContent = data.conversation.contact_name || data.conversation.phone_number;
            document.getElementById('chat-contact-phone').textContent = data.conversation.phone_number;
            renderMessages(data.messages || []);
            startPolling();
        } else {
            alert('Erro: ' + (data.message || 'Não foi possível iniciar a conversa.'));
        }
    } catch (err) {
        console.error(err);
        alert('Erro ao iniciar conversa.');
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-comment-dots mr-2"></i> Iniciar conversa';
}

// Abrir conversa
async function openConversation(conversationId) {
    currentConversationId = conversationId;
    lastMessageId = 0;
    
    // Mostrar container de chat
    document.getElementById('chat-container').style.display = 'block';
    document.getElementById('chat-placeholder').style.display = 'none';
    
    try {
        const response = await fetch(`<?= url('whatsapp/attendance/conversations') ?>/${conversationId}/open`);
        const data = await response.json();
        
        if (data.success) {
            // Atualizar header
            document.getElementById('chat-contact-name').textContent = data.conversation.contact_name || data.conversation.phone_number;
            document.getElementById('chat-contact-phone').textContent = data.conversation.phone_number;
            
            // Renderizar mensagens
            renderMessages(data.messages);
            
            // Iniciar polling
            startPolling();
        } else {
            alert('Erro ao abrir conversa: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar conversa');
    }
}

// Renderizar mensagens
function renderMessages(messages) {
    const container = document.getElementById('chat-messages');
    container.innerHTML = '';
    
    messages.forEach(msg => {
        if (msg.id > lastMessageId) {
            lastMessageId = msg.id;
        }
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${msg.from_me ? 'justify-end' : 'justify-start'}`;
        
        messageDiv.innerHTML = `
            <div class="max-w-[70%] ${msg.from_me ? 'bg-green-500 text-white' : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white'} rounded-lg px-4 py-2">
                ${msg.message_type !== 'TEXT' ? `<p class="text-xs opacity-75">${msg.message_type}</p>` : ''}
                ${msg.body ? `<p>${escapeHtml(msg.body)}</p>` : ''}
                ${msg.media_url ? `<a href="${msg.media_url}" target="_blank" class="text-blue-400 underline">Ver mídia</a>` : ''}
                <p class="text-xs opacity-75 mt-1">${formatTime(msg.created_at)}</p>
            </div>
        `;
        
        container.appendChild(messageDiv);
    });
    
    // Scroll para baixo
    container.scrollTop = container.scrollHeight;
}

// Enviar mensagem
async function sendMessage(event) {
    event.preventDefault();
    
    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    
    if (!message || !currentConversationId) {
        return;
    }
    
    try {
        const response = await fetch('<?= url('whatsapp/attendance/messages/send') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: currentConversationId,
                message: message,
                type: 'TEXT'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageInput.value = '';
            // Adicionar mensagem à lista
            renderMessages([data.message]);
        } else {
            alert('Erro ao enviar: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao enviar mensagem');
    }
}

// Polling para novas mensagens
function startPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
    
    pollingInterval = setInterval(async () => {
        if (!currentConversationId) return;
        
        try {
            const response = await fetch(`<?= url('whatsapp/attendance/conversations') ?>/${currentConversationId}/messages?last_message_id=${lastMessageId}`);
            const data = await response.json();
            
            if (data.success && data.messages.length > 0) {
                renderMessages(data.messages);
            }
        } catch (error) {
            console.error('Erro no polling:', error);
        }
    }, 3000); // Polling a cada 3 segundos
}

// Fechar chat
function closeChat() {
    currentConversationId = null;
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
    document.getElementById('chat-container').style.display = 'none';
    document.getElementById('chat-placeholder').style.display = 'block';
}

// Atualizar conversas
function refreshConversations() {
    const status = document.getElementById('filter-status').value;
    const queue = document.getElementById('filter-queue').value;
    const search = document.getElementById('filter-search').value;
    
    const params = new URLSearchParams({
        status: status,
        queue_id: queue,
        search: search
    });
    
    window.location.href = `<?= url('whatsapp/attendance') ?>?${params.toString()}`;
}

// Utilitários
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

// Aplicar filtros
document.getElementById('filter-status').addEventListener('change', refreshConversations);
document.getElementById('filter-queue').addEventListener('change', refreshConversations);
document.getElementById('filter-search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        refreshConversations();
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
?>

