<?php

namespace App\Core;

use Ramsey\Uuid\Uuid;

class FileUpload
{
    private $allowedTypes;
    private $maxSize;
    private $uploadPath;
    
    public function __construct()
    {
        $this->allowedTypes = explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx');
        $this->maxSize = $_ENV['MAX_FILE_SIZE'] ?? 10485760; // 10MB
        $this->uploadPath = __DIR__ . '/../../storage/uploads';
        
        // Criar diretório se não existir
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    public function upload($file, $destination = null)
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('Arquivo inválido');
        }
        
        // Validar tamanho
        if ($file['size'] > $this->maxSize) {
            throw new \Exception('Arquivo muito grande. Máximo permitido: ' . $this->formatBytes($this->maxSize));
        }
        
        // Validar tipo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            throw new \Exception('Tipo de arquivo não permitido. Tipos aceitos: ' . implode(', ', $this->allowedTypes));
        }
        
        // Validar MIME type
        $mimeType = null;
        $mimeTypeSource = 'unknown';
        
        // Verificar se a extensão fileinfo está disponível
        if (function_exists('finfo_open')) {
            $finfo = \finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mimeType = \finfo_file($finfo, $file['tmp_name']);
                \finfo_close($finfo);
                $mimeTypeSource = 'finfo';
            }
        }
        
        // Fallback: usar mime_content_type se fileinfo não estiver disponível
        if (empty($mimeType) && function_exists('mime_content_type')) {
            $mimeType = \mime_content_type($file['tmp_name']);
            $mimeTypeSource = 'mime_content_type';
        }
        
        // Fallback final: usar o tipo do $_FILES
        if (empty($mimeType)) {
            $mimeType = $file['type'] ?? 'application/octet-stream';
            $mimeTypeSource = 'file_type';
        }
        
        // Log para debug
        if (function_exists('write_log')) {
            write_log("Validação MIME: Extensão='{$extension}', MIME Type='{$mimeType}' (fonte: {$mimeTypeSource})", 'file-upload.log');
        }
        
        if (!$this->isValidMimeType($mimeType, $extension)) {
            $errorMsg = "Tipo de arquivo inválido. Extensão: {$extension}, MIME Type: {$mimeType}";
            if (function_exists('write_log')) {
                write_log("ERRO: {$errorMsg}", 'file-upload.log');
            }
            throw new \Exception($errorMsg);
        }
        
        // Gerar nome único
        $uuid = Uuid::uuid4()->toString();
        $fileName = $uuid . '.' . $extension;
        
        // Definir destino
        if ($destination) {
            $destinationPath = $this->uploadPath . '/' . $destination;
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $filePath = $destinationPath . '/' . $fileName;
        } else {
            $filePath = $this->uploadPath . '/' . $fileName;
        }
        
        // Mover arquivo
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \Exception('Erro ao salvar arquivo');
        }
        
        return [
            'original_name' => $file['name'],
            'file_name' => $fileName,
            'file_path' => $filePath,
            'size' => $file['size'],
            'mime_type' => $mimeType,
            'extension' => $extension
        ];
    }
    
    public function uploadMultiple($files, $destination = null)
    {
        $results = [];
        
        foreach ($files['tmp_name'] as $key => $tmpName) {
            if (is_uploaded_file($tmpName)) {
                $file = [
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $tmpName,
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                ];
                
                try {
                    $results[] = $this->upload($file, $destination);
                } catch (\Exception $e) {
                    $results[] = [
                        'error' => $e->getMessage(),
                        'original_name' => $file['name']
                    ];
                }
            }
        }
        
        return $results;
    }
    
    public function delete($filePath)
    {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    public function getFileInfo($filePath)
    {
        if (!file_exists($filePath)) {
            return null;
        }
        
        $mimeType = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $mimeType = \mime_content_type($filePath) ?: $mimeType;
        } elseif (function_exists('finfo_open')) {
            $finfo = \finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mimeType = \finfo_file($finfo, $filePath) ?: $mimeType;
                \finfo_close($finfo);
            }
        }
        
        return [
            'name' => basename($filePath),
            'size' => filesize($filePath),
            'mime_type' => $mimeType,
            'created_at' => date('Y-m-d H:i:s', filectime($filePath)),
            'modified_at' => date('Y-m-d H:i:s', filemtime($filePath))
        ];
    }
    
    private function isValidMimeType($mimeType, $extension)
    {
        $validMimes = [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'pdf' => ['application/pdf'],
            'doc' => [
                'application/msword',
                'application/vnd.ms-word',
                'application/x-msword',
                'application/octet-stream' // Alguns servidores retornam isso para DOC
            ],
            'docx' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.wordprocessingml',
                'application/zip', // DOCX é um arquivo ZIP
                'application/x-zip-compressed',
                'application/octet-stream' // Alguns servidores retornam isso para DOCX
            ],
            'xls' => [
                'application/vnd.ms-excel',
                'application/excel',
                'application/x-excel',
                'application/x-msexcel',
                'application/octet-stream' // Alguns servidores retornam isso para XLS
            ],
            'xlsx' => [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.spreadsheetml',
                'application/zip', // XLSX é um arquivo ZIP
                'application/x-zip-compressed',
                'application/octet-stream' // Alguns servidores retornam isso para XLSX
            ]
        ];
        
        // Se a extensão não está na lista, rejeitar
        if (!isset($validMimes[$extension])) {
            if (function_exists('write_log')) {
                write_log("ERRO: Extensão '{$extension}' não está na lista de tipos permitidos", 'file-upload.log');
            }
            return false;
        }
        
        // Verificar se o MIME type está na lista de válidos para esta extensão
        if (in_array($mimeType, $validMimes[$extension])) {
            if (function_exists('write_log')) {
                write_log("OK: MIME type '{$mimeType}' válido para extensão '{$extension}'", 'file-upload.log');
            }
            return true;
        }
        
        // Para DOCX e XLSX, aceitar application/zip ou application/octet-stream
        // pois alguns servidores não detectam corretamente
        if (($extension === 'docx' || $extension === 'xlsx') && 
            ($mimeType === 'application/zip' || 
             $mimeType === 'application/x-zip-compressed' || 
             $mimeType === 'application/octet-stream')) {
            if (function_exists('write_log')) {
                write_log("OK: Aceitando {$extension} com MIME type '{$mimeType}' (arquivo ZIP/Office)", 'file-upload.log');
            }
            return true;
        }
        
        // Se o MIME type é application/octet-stream mas a extensão é válida,
        // aceitar (muitos servidores retornam isso quando não conseguem detectar)
        if ($mimeType === 'application/octet-stream' && isset($validMimes[$extension])) {
            if (function_exists('write_log')) {
                write_log("AVISO: MIME type 'application/octet-stream' para extensão '{$extension}'. Aceitando baseado na extensão.", 'file-upload.log');
            }
            return true;
        }
        
        // Log do erro
        if (function_exists('write_log')) {
            write_log("ERRO: MIME type '{$mimeType}' não é válido para extensão '{$extension}'", 'file-upload.log');
        }
        
        return false;
    }
    
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}
