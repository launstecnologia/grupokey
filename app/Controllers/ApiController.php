<?php

namespace App\Controllers;

use App\Core\Auth;

class ApiController
{
    /**
     * Busca dados do CNPJ via API ReceitaWS
     * Esta função faz a requisição no servidor (server-side) para evitar problemas de CORS
     */
    public function buscarCnpj()
    {
        // Requer autenticação
        Auth::requireAuth();
        
        // Verificar se o CNPJ foi enviado
        $cnpj = $_GET['cnpj'] ?? $_POST['cnpj'] ?? '';
        
        if (empty($cnpj)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'CNPJ não informado'
            ]);
            return;
        }
        
        // Remover formatação do CNPJ
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Validar CNPJ (14 dígitos)
        if (strlen($cnpj) !== 14) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'CNPJ inválido. Deve conter 14 dígitos.'
            ]);
            return;
        }
        
        try {
            // Fazer requisição para a API ReceitaWS
            $url = "https://receitaws.com.br/v1/cnpj/{$cnpj}";
            
            // Usar cURL para fazer a requisição
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'User-Agent: Sistema-CRM/1.0'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new \Exception("Erro ao conectar com a API: {$error}");
            }
            
            if ($httpCode !== 200) {
                throw new \Exception("API retornou código HTTP {$httpCode}");
            }
            
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Erro ao decodificar resposta da API");
            }
            
            // Verificar se a API retornou erro
            if (isset($data['status']) && $data['status'] !== 'OK') {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $data['message'] ?? 'CNPJ não encontrado ou inválido.'
                ]);
                return;
            }
            
            // Retornar dados formatados
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar CNPJ: ' . $e->getMessage()
            ]);
        }
    }
}

