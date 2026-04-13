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
                                <div class="p-4 hover:bg-gray-100 cursor-pointer conversation-item" 
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
                                                    <span class="queue-tag-whatsapp text-xs px-2 py-1 rounded" style="background-color: <?= htmlspecialchars($conv['queue_color'] ?? '#3B82F6') ?>20; color: <?= htmlspecialchars($conv['queue_color'] ?? '#3B82F6') ?>">
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
                    <!-- Header do Chat (estilo WhatsApp Web) -->
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80" id="chat-header">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div id="chat-contact-avatar" class="flex-shrink-0"></div>
                                <div class="min-w-0">
                                    <h3 class="font-medium text-gray-900 dark:text-white truncate" id="chat-contact-name"></h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate" id="chat-contact-phone"></p>
                                </div>
                            </div>
                            <div class="flex gap-2 items-center">
                                <button type="button" id="btn-close-attendance" onclick="closeAttendance()" 
                                        class="text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 text-sm font-medium px-3 py-1.5 rounded border border-amber-500/50 hover:bg-amber-500/10" title="Encerrar esta conversa">
                                    <i class="fas fa-flag-checkered mr-1"></i> Encerrar conversa
                                </button>
                                <button onclick="closeChat()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 p-1" title="Fechar painel">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mensagens (fundo padrão WhatsApp Web) -->
                    <div class="h-[500px] overflow-y-auto px-4 py-2 space-y-1 bg-[#e5ddd5] dark:bg-gray-900" id="chat-messages" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23d4c4b0\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
                        <!-- Mensagens serão carregadas aqui via JavaScript -->
                    </div>

                    <!-- Campo de envio (estilo WhatsApp Web) -->
                    <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80">
                        <form id="message-form" onsubmit="sendMessage(event)">
                            <input type="file" id="file-attach" class="hidden" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx">
                            <div class="flex gap-1 items-end">
                                <button type="button" onclick="document.getElementById('file-attach').click()" 
                                        class="p-2.5 rounded-full text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600 dark:text-gray-400 dark:hover:text-white transition-colors" title="Anexar">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <button type="button" id="btn-record-audio" onclick="toggleRecordAudio()" 
                                        class="p-2.5 rounded-full text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600 dark:text-gray-400 dark:hover:text-white transition-colors" title="Gravar áudio">
                                    <i class="fas fa-microphone"></i>
                                </button>
                                <input type="text" 
                                       id="message-input" 
                                       placeholder="Digite uma mensagem"
                                       class="flex-1 rounded-2xl border border-gray-300 dark:border-gray-600 px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-green-500/30 focus:border-green-500">
                                <button type="submit" id="btn-send" 
                                        class="p-2.5 rounded-full bg-green-500 hover:bg-green-600 text-white transition-colors shadow" title="Enviar">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                            <div id="attach-preview" class="mt-2 hidden text-sm text-gray-600 dark:text-gray-400"></div>
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
let currentAttendanceId = null;
let lastMessageId = 0;
let pollingInterval = null;
let pendingMedia = null; // { url, type, file_name }
let mediaRecorder = null;
let recordedChunks = [];

// Se a URL tiver ?phone= (ex: vindo do CRM ou Agenda), abrir modal e preencher número
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const phone = params.get('phone');
    if (phone) {
        const modal = document.getElementById('modal-new-conversation');
        const input = document.getElementById('new-phone-number');
        if (modal && input) {
            input.value = phone.replace(/\D/g, '');
            modal.classList.remove('hidden');
        }
    }
});

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
            currentAttendanceId = data.attendance ? data.attendance.id : null;
            lastMessageId = 0;
            document.getElementById('chat-container').style.display = 'block';
            document.getElementById('chat-placeholder').style.display = 'none';
            setChatHeader(data.conversation);
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

function setChatHeader(conversation) {
    const name = conversation.contact_name || conversation.phone_number || 'Contato';
    const phone = conversation.phone_number || '';
    document.getElementById('chat-contact-name').textContent = name;
    document.getElementById('chat-contact-phone').textContent = phone;
    const avatarEl = document.getElementById('chat-contact-avatar');
    if (conversation.contact_picture) {
        avatarEl.innerHTML = '<img src="' + escapeHtml(conversation.contact_picture) + '" alt="" class="w-10 h-10 rounded-full object-cover">';
    } else {
        const initial = name.trim() ? name.trim().charAt(0).toUpperCase() : '?';
        avatarEl.innerHTML = '<div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-semibold text-lg">' + escapeHtml(initial) + '</div>';
    }
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
        const text = await response.text();
        let data = null;
        try {
            data = text ? JSON.parse(text) : {};
        } catch (e) {
            const msg = /<\s*html/i.test(text)
                ? 'O servidor retornou uma página em vez de dados. Verifique se está logado e tente novamente.'
                : 'Erro ao carregar conversa: resposta inválida do servidor.';
            alert(msg);
            return;
        }
        if (!response.ok) {
            alert(data.message || data.error || 'Erro ao carregar conversa');
            return;
        }
        if (data.success) {
            currentAttendanceId = data.attendance ? data.attendance.id : null;
            setChatHeader(data.conversation);
            renderMessages(data.messages);
            
            // Iniciar polling
            startPolling();
        } else {
            alert('Erro ao abrir conversa: ' + (data.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar conversa' + (error.message ? ': ' + error.message : ''));
    }
}

// Renderizar mensagens (texto, imagem, áudio, vídeo, documento). Se options.append === true, só adiciona ao final sem limpar.
function renderMessages(messages, options) {
    const container = document.getElementById('chat-messages');
    const append = options && options.append === true;
    if (!messages.length) {
        if (!append) container.innerHTML = '';
        return;
    }
    if (!append) container.innerHTML = '';
    
    messages.forEach(msg => {
        if (append) {
            const alreadyRendered = container.querySelector('[data-message-id="' + msg.id + '"]');
            if (alreadyRendered) return;
        }
        if (msg.id > lastMessageId) lastMessageId = msg.id;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${msg.from_me ? 'justify-end' : 'justify-start'}`;
        messageDiv.setAttribute('data-message-id', msg.id);
        
        let mediaHtml = '';
        const type = (msg.message_type || '').toUpperCase();
        let mediaUrl = msg.media_url || '';
        // Áudio/vídeo recebidos usam URL da Evolution (exige API key). Usar proxy para reproduzir.
        const useMediaProxy = (type === 'AUDIO' || type === 'VIDEO' || type === 'IMAGE') && msg.id && !msg.from_me;
        if (useMediaProxy) {
            mediaUrl = '<?= url('whatsapp/attendance/media') ?>?message_id=' + encodeURIComponent(msg.id);
        }
        if (mediaUrl) {
            if (type === 'IMAGE') {
                mediaHtml = `<img src="${escapeHtml(mediaUrl)}" alt="Imagem" class="max-w-full max-h-64 rounded object-contain my-1" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline'"><a href="${escapeHtml(mediaUrl)}" target="_blank" class="text-blue-400 underline text-sm" style="display:none">Ver imagem</a>`;
            } else if (type === 'AUDIO') {
                mediaHtml = `<audio controls preload="metadata" class="max-w-full my-1" src="${escapeHtml(mediaUrl)}" onerror="var l=this.nextElementSibling;if(l)l.style.display='inline'">Não suportado. <a href="${escapeHtml(mediaUrl)}" target="_blank" rel="noopener" style="display:none">Abrir áudio em nova aba</a></audio>`;
            } else if (type === 'VIDEO') {
                mediaHtml = `<video controls class="max-w-full max-h-48 rounded my-1" src="${escapeHtml(mediaUrl)}">Não suportado. <a href="${escapeHtml(mediaUrl)}" target="_blank">Ver vídeo</a></video>`;
            } else {
                mediaHtml = `<a href="${escapeHtml(mediaUrl)}" target="_blank" class="text-blue-400 underline">Ver/baixar arquivo</a>`;
            }
        }
        const bodyHtml = msg.body ? `<p class="break-words">${escapeHtml(msg.body)}</p>` : '';
        const sent = msg.from_me;
        const bubbleClass = sent
            ? 'chat-msg-sent bg-[#d9fdd3] dark:bg-green-800 text-gray-900 dark:text-green-50 rounded-lg rounded-br-none shadow-sm'
            : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg rounded-bl-none shadow-sm';
        const timeClass = sent
            ? 'text-[10px] opacity-90 mt-1 inline-block text-gray-700 dark:text-green-100'
            : 'text-[10px] opacity-80 mt-1 inline-block text-gray-600 dark:text-gray-300';
        messageDiv.innerHTML = `
            <div class="max-w-[75%] sm:max-w-[65%] chat-msg-bubble ${bubbleClass} px-3 py-2">
                ${type !== 'TEXT' && type ? `<p class="text-xs opacity-90 mb-1 ${sent ? 'text-gray-700 dark:text-green-100' : 'text-gray-600 dark:text-gray-300'}">${escapeHtml(type)}</p>` : ''}
                ${mediaHtml}
                ${bodyHtml}
                <div class="flex justify-end"><span class="${timeClass}">${formatTime(msg.created_at)}</span></div>
            </div>
        `;
        container.appendChild(messageDiv);
    });
    container.scrollTop = container.scrollHeight;
}

// Anexar arquivo: upload e preview
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file-attach');
    if (fileInput) {
        fileInput.addEventListener('change', async function() {
            if (!this.files || !this.files[0] || !currentConversationId) return;
            const file = this.files[0];
            const preview = document.getElementById('attach-preview');
            try {
                preview.classList.remove('hidden');
                preview.textContent = 'Enviando ' + file.name + '...';
                const formData = new FormData();
                formData.append('file', file);
                const response = await fetch('<?= url('whatsapp/attendance/upload-media') ?>', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    pendingMedia = { url: data.url, type: data.type, file_name: data.file_name || file.name };
                    preview.textContent = 'Anexado: ' + (data.file_name || file.name) + ' (' + data.type + '). Envie ou digite legenda.';
                } else {
                    preview.textContent = 'Erro: ' + (data.message || 'Falha no upload');
                    pendingMedia = null;
                }
            } catch (e) {
                preview.textContent = 'Erro ao enviar arquivo';
                pendingMedia = null;
            }
            this.value = '';
        });
    }
});

// Gravar áudio
async function toggleRecordAudio() {
    const btn = document.getElementById('btn-record-audio');
    const icon = btn.querySelector('i');
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
        icon.className = 'fas fa-microphone';
        btn.classList.remove('bg-red-500');
        return;
    }
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        recordedChunks = [];
        mediaRecorder.ondataavailable = e => { if (e.data.size) recordedChunks.push(e.data); };
        mediaRecorder.onstop = async () => {
            stream.getTracks().forEach(t => t.stop());
            const blob = new Blob(recordedChunks, { type: 'audio/webm' });
            const file = new File([blob], 'audio.webm', { type: 'audio/webm' });
            const preview = document.getElementById('attach-preview');
            preview.classList.remove('hidden');
            preview.textContent = 'Enviando áudio...';
            try {
                const formData = new FormData();
                formData.append('file', file);
                const response = await fetch('<?= url('whatsapp/attendance/upload-media') ?>', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    pendingMedia = { url: data.url, type: 'AUDIO', file_name: 'audio.webm' };
                    preview.textContent = 'Áudio gravado. Clique em Enviar.';
                } else {
                    preview.textContent = 'Erro: ' + (data.message || 'Falha no upload');
                }
            } catch (e) {
                preview.textContent = 'Erro ao enviar áudio';
            }
        };
        mediaRecorder.start();
        icon.className = 'fas fa-stop';
        btn.classList.add('bg-red-500');
    } catch (e) {
        alert('Não foi possível acessar o microfone: ' + (e.message || 'Permissão negada'));
    }
}

// Enviar mensagem (texto e/ou mídia)
async function sendMessage(event) {
    event.preventDefault();
    
    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    
    if (!currentConversationId) return;
    if (!message && !pendingMedia) {
        alert('Digite uma mensagem ou anexe um arquivo.');
        return;
    }
    
    const btn = document.getElementById('btn-send');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Enviando...';
    
    try {
        const payload = {
            conversation_id: currentConversationId,
            message: message || (pendingMedia ? '' : null),
            type: pendingMedia ? pendingMedia.type : 'TEXT',
            media_url: pendingMedia ? pendingMedia.url : null
        };
        if (pendingMedia && pendingMedia.file_name) payload.file_name = pendingMedia.file_name;
        
        const response = await fetch('<?= url('whatsapp/attendance/messages/send') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageInput.value = '';
            pendingMedia = null;
            document.getElementById('attach-preview').classList.add('hidden');
            document.getElementById('attach-preview').textContent = '';
            renderMessages([data.message], { append: true });
        } else {
            alert('Erro ao enviar: ' + (data.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao enviar mensagem');
    }
    btn.disabled = false;
    btn.innerHTML = originalHtml;
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
            
            if (data.success && data.messages && data.messages.length > 0) {
                renderMessages(data.messages, { append: true });
            }
        } catch (error) {
            console.error('Erro no polling:', error);
        }
    }, 3000); // Polling a cada 3 segundos
}

// Fechar chat
// Encerrar atendimento (fechar conversa no sistema) e fechar o painel
async function closeAttendance() {
    if (!currentAttendanceId) {
        closeChat();
        return;
    }
    if (!confirm('Encerrar esta conversa? O atendimento será marcado como fechado.')) return;
    try {
        const response = await fetch('<?= url('whatsapp/attendance') ?>/' + currentAttendanceId + '/close', { method: 'POST' });
        const data = await response.json().catch(() => ({}));
        if (response.ok && data.success !== false) {
            closeChat();
            refreshConversations();
        } else {
            alert(data.message || 'Não foi possível encerrar a conversa.');
        }
    } catch (e) {
        console.error(e);
        alert('Erro ao encerrar conversa.');
    }
}

function closeChat() {
    currentConversationId = null;
    currentAttendanceId = null;
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

