<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private $phpmailer;
    
    public function __construct()
    {
        $this->phpmailer = new PHPMailer(true);
        $this->configureSMTP();
    }
    
    private function configureSMTP()
    {
        try {
            // Tentar carregar configurações do banco primeiro
            $emailSettings = $this->loadEmailSettingsFromDatabase();
            
            // Configurações do servidor
            $this->phpmailer->isSMTP();
            $this->phpmailer->Host = $emailSettings['mail_host'] ?? $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $this->phpmailer->SMTPAuth = true;
            $this->phpmailer->Username = $emailSettings['mail_user'] ?? $_ENV['MAIL_USER'] ?? '';
            $this->phpmailer->Password = $emailSettings['mail_pass'] ?? $_ENV['MAIL_PASS'] ?? '';
            
            // Configurar criptografia baseado nas configurações
            $encryption = $emailSettings['mail_encryption'] ?? 'tls';
            if ($encryption === 'ssl') {
                $this->phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $this->phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $this->phpmailer->SMTPSecure = '';
            }
            
            $this->phpmailer->Port = $emailSettings['mail_port'] ?? $_ENV['MAIL_PORT'] ?? 587;
            
            // Configurações do remetente
            $fromEmail = $emailSettings['mail_from'] ?? $_ENV['MAIL_FROM'] ?? $_ENV['MAIL_USER'] ?? '';
            $fromName = $emailSettings['mail_name'] ?? $_ENV['MAIL_NAME'] ?? 'Sistema CRM';
            
            // Validar email do remetente - se estiver vazio, usar um padrão válido
            if (empty($fromEmail) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                // Se não houver email válido, usar um placeholder (não será usado se não houver configuração)
                $fromEmail = 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            }
            
            $this->phpmailer->setFrom($fromEmail, $fromName);
            
            // Configurações de charset
            $this->phpmailer->CharSet = 'UTF-8';
            $this->phpmailer->isHTML(true);
            
        } catch (Exception $e) {
            throw new Exception("Erro na configuração do PHPMailer: " . $e->getMessage());
        }
    }
    
    /**
     * Carrega configurações de email do banco de dados
     */
    private function loadEmailSettingsFromDatabase()
    {
        try {
            $emailSettingsModel = new \App\Models\EmailSettings();
            $settings = $emailSettingsModel->getActiveSettings();
            return $settings ?: [];
        } catch (\Exception $e) {
            // Se houver erro ao carregar do banco, retornar array vazio para usar ENV
            return [];
        }
    }
    
    public function send($to, $subject, $body, $altBody = null, $attachments = [])
    {
        try {
            $this->phpmailer->clearAddresses();
            $this->phpmailer->clearAttachments();
            $this->phpmailer->addAddress($to);
            
            $this->phpmailer->Subject = $subject;
            $this->phpmailer->Body = $body;
            $this->phpmailer->AltBody = $altBody ?: strip_tags($body);
            
            // Adicionar anexos se fornecidos
            if (!empty($attachments) && is_array($attachments)) {
                $logMessage = '=== ADICIONANDO ANEXOS ===' . PHP_EOL;
                $logMessage .= 'Total de anexos recebidos: ' . count($attachments) . PHP_EOL;
                
                foreach ($attachments as $index => $attachment) {
                    $logMessage .= "Processando anexo #{$index}: " . json_encode($attachment) . PHP_EOL;
                    
                    try {
                        if (is_array($attachment) && isset($attachment['path'])) {
                            $filePath = $attachment['path'];
                            $name = $attachment['name'] ?? basename($filePath);
                            
                            $logMessage .= "Tentando anexar arquivo: {$filePath}" . PHP_EOL;
                            $logMessage .= "Nome do anexo: {$name}" . PHP_EOL;
                            
                            if (file_exists($filePath)) {
                                $fileSize = filesize($filePath);
                                $logMessage .= "Arquivo existe. Tamanho: {$fileSize} bytes" . PHP_EOL;
                                
                                $result = $this->phpmailer->addAttachment($filePath, $name);
                                if ($result) {
                                    $logMessage .= "✓ Anexo adicionado com sucesso: {$name}" . PHP_EOL;
                                } else {
                                    $logMessage .= "✗ ERRO: Falha ao adicionar anexo: {$name}" . PHP_EOL;
                                }
                            } else {
                                $logMessage .= "✗ ERRO: Arquivo não existe: {$filePath}" . PHP_EOL;
                            }
                        } elseif (is_string($attachment) && file_exists($attachment)) {
                            $logMessage .= "Tentando anexar arquivo (string): {$attachment}" . PHP_EOL;
                            $result = $this->phpmailer->addAttachment($attachment);
                            if ($result) {
                                $logMessage .= "✓ Anexo adicionado com sucesso: {$attachment}" . PHP_EOL;
                            } else {
                                $logMessage .= "✗ ERRO: Falha ao adicionar anexo: {$attachment}" . PHP_EOL;
                            }
                        } else {
                            $logMessage .= "✗ ERRO: Formato de anexo inválido ou arquivo não existe" . PHP_EOL;
                        }
                    } catch (\Exception $e) {
                        $logMessage .= "✗ EXCEÇÃO ao adicionar anexo: " . $e->getMessage() . PHP_EOL;
                        $logMessage .= "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
                    }
                }
                
                $logMessage .= 'Total de anexos adicionados ao PHPMailer: ' . count($this->phpmailer->getAttachments()) . PHP_EOL;
                
                // Escrever log
                $this->writeLog($logMessage, 'email-queue.log');
            } else {
                $logMessage = 'Nenhum anexo fornecido ou array vazio';
                $this->writeLog($logMessage, 'email-queue.log');
            }
            
            return $this->phpmailer->send();
            
        } catch (Exception $e) {
            throw new Exception("Erro ao enviar email: " . $e->getMessage());
        }
    }
    
    public function sendPasswordReset($email, $name, $resetToken)
    {
        $resetUrl = url("reset-password?token={$resetToken}");
        
        $subject = "Redefinição de Senha - Sistema CRM";
        $body = $this->getPasswordResetTemplate($name, $resetUrl);
        
        return $this->send($email, $subject, $body);
    }
    
    public function sendWelcomeUser($email, $name, $password)
    {
        $loginUrl = url('login');
        
        $subject = "Bem-vindo ao Sistema CRM - Suas Credenciais";
        $body = $this->getWelcomeUserTemplate($name, $email, $password, $loginUrl);
        
        return $this->send($email, $subject, $body);
    }
    
    public function sendWelcomeRepresentative($email, $name, $password)
    {
        $loginUrl = url('login');
        
        $subject = "Bem-vindo ao Sistema CRM - Suas Credenciais";
        $body = $this->getWelcomeTemplate($name, $email, $password, $loginUrl);
        
        return $this->send($email, $subject, $body);
    }
    
    public function sendClientApprovalNotification($email, $name, $clientName, $status)
    {
        $subject = $status === 'approved' ? 
            "Cliente Aprovado - {$clientName}" : 
            "Cliente Reprovado - {$clientName}";
            
        $body = $this->getClientApprovalTemplate($name, $clientName, $status);
        
        return $this->send($email, $subject, $body);
    }
    
    private function getPasswordResetTemplate($name, $resetUrl)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Redefinição de Senha</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Redefinição de Senha</h2>
                <p>Olá, <strong>{$name}</strong>!</p>
                <p>Você solicitou a redefinição de sua senha no Sistema CRM.</p>
                <p>Clique no botão abaixo para criar uma nova senha:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetUrl}' style='background-color: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Redefinir Senha</a>
                </div>
                <p>Se você não solicitou esta redefinição, ignore este email.</p>
                <p>Este link não possui data de expiração.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>Este é um email automático do Sistema CRM.</p>
            </div>
        </body>
        </html>";
    }
    
    private function getWelcomeTemplate($name, $email, $password, $loginUrl)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Bem-vindo ao Sistema CRM</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Bem-vindo ao Sistema CRM!</h2>
                <p>Olá, <strong>{$name}</strong>!</p>
                <p>Suas credenciais de acesso foram criadas com sucesso.</p>
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #495057;'>Suas Credenciais:</h3>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Senha:</strong> {$password}</p>
                </div>
                <p><strong>Importante:</strong> No primeiro acesso, você será solicitado a alterar sua senha por questões de segurança.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$loginUrl}' style='background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Acessar Sistema</a>
                </div>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>Este é um email automático do Sistema CRM.</p>
            </div>
        </body>
        </html>";
    }
    
    private function getWelcomeUserTemplate($name, $email, $password, $loginUrl)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Bem-vindo ao Sistema CRM - Usuário Administrativo</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Bem-vindo ao Sistema CRM!</h2>
                <p>Olá, <strong>{$name}</strong>!</p>
                <p>Suas credenciais de usuário administrativo foram criadas com sucesso.</p>
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #495057;'>Suas Credenciais Administrativas:</h3>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Senha:</strong> {$password}</p>
                </div>
                <p><strong>Importante:</strong> Como usuário administrativo, você tem acesso completo ao sistema. Mantenha suas credenciais seguras.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$loginUrl}' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Acessar Painel Administrativo</a>
                </div>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>Este é um email automático do Sistema CRM.</p>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Escreve log usando write_log se disponível, senão error_log
     */
    private function writeLog($message, $logFile = 'app.log')
    {
        if (function_exists('write_log')) {
            write_log($message, $logFile);
        } else {
            // Fallback: escrever diretamente no arquivo de log
            $logDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
            
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $logPath = $logDir . $logFile;
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
            
            file_put_contents($logPath, $logMessage, FILE_APPEND | LOCK_EX);
            error_log($message);
        }
    }
    
    private function getClientApprovalTemplate($name, $clientName, $status)
    {
        $statusText = $status === 'approved' ? 'aprovado' : 'reprovado';
        $statusColor = $status === 'approved' ? '#28a745' : '#dc3545';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Status do Cliente</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Status do Cliente Atualizado</h2>
                <p>Olá, <strong>{$name}</strong>!</p>
                <p>O cliente <strong>{$clientName}</strong> foi <span style='color: {$statusColor}; font-weight: bold;'>{$statusText}</span> no sistema.</p>
                <p>Acesse o sistema para mais detalhes sobre a aprovação/reprovação.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>Este é um email automático do Sistema CRM.</p>
            </div>
        </body>
        </html>";
    }
}
