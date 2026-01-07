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
        $this->allowedTypes = explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'jpg,jpeg,png,pdf,doc,docx');
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
        
        // Verificar se a extensão fileinfo está disponível
        if (function_exists('finfo_open')) {
            $finfo = \finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mimeType = \finfo_file($finfo, $file['tmp_name']);
                \finfo_close($finfo);
            }
        }
        
        // Fallback: usar mime_content_type se fileinfo não estiver disponível
        if (empty($mimeType) && function_exists('mime_content_type')) {
            $mimeType = \mime_content_type($file['tmp_name']);
        }
        
        // Fallback final: usar o tipo do $_FILES
        if (empty($mimeType)) {
            $mimeType = $file['type'] ?? 'application/octet-stream';
        }
        
        if (!$this->isValidMimeType($mimeType, $extension)) {
            throw new \Exception('Tipo de arquivo inválido');
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
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        ];
        
        return isset($validMimes[$extension]) && in_array($mimeType, $validMimes[$extension]);
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
