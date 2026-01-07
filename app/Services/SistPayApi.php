<?php

namespace App\Services;

use App\Models\SistPaySettings;

class SistPayApi
{
    private $token;
    private $baseUrl;
    private $isSandbox;
    private $isActive;
    private $authMethod;
    
    public function __construct()
    {
        $settingsModel = new SistPaySettings();
        $settings = $settingsModel->getActiveSettings();
        
        if ($settings) {
            $this->token = $settings['token'] ?? '';
            $this->baseUrl = $settings['base_url'] ?? 'https://sistpay.com.br/api';
            $this->isSandbox = !empty($settings['is_sandbox']);
            $this->isActive = !empty($settings['is_active']);
            $this->authMethod = $settings['auth_method'] ?? 'Authorization';
        } else {
            $this->token = '';
            $this->baseUrl = 'https://sistpay.com.br/api';
            $this->isSandbox = false;
            $this->isActive = false;
            $this->authMethod = 'Authorization';
        }
    }
    
    /**
     * Verifica se a API está configurada e ativa
     * Em modo sandbox, permite uso mesmo sem estar ativa para produção
     */
    public function isConfigured()
    {
        // Se tiver token configurado, permite uso (especialmente em sandbox)
        // Se estiver em sandbox, não precisa estar ativa para produção
        if ($this->isSandbox) {
            return !empty($this->token);
        }
        // Em produção, precisa estar ativa
        return !empty($this->token) && $this->isActive;
    }
    
    /**
     * Retorna os headers de autenticação conforme o método configurado
     */
    private function getAuthHeaders()
    {
        $headers = [];
        
        switch ($this->authMethod) {
            case 'X-Authorization':
                $headers[] = 'X-Authorization: Bearer ' . $this->token;
                break;
            case 'X-Api-Token':
                $headers[] = 'X-Api-Token: ' . $this->token;
                break;
            case 'X-Api-Key':
                $headers[] = 'X-Api-Key: ' . $this->token;
                break;
            case 'Query-Param':
                // Não adiciona header, será usado como query parameter
                break;
            case 'Authorization':
            default:
                $headers[] = 'Authorization: Bearer ' . $this->token;
                break;
        }
        
        return $headers;
    }
    
    /**
     * Adiciona o token como query parameter se necessário
     */
    private function addTokenToUrl($url)
    {
        if ($this->authMethod === 'Query-Param') {
            $separator = strpos($url, '?') !== false ? '&' : '?';
            return $url . $separator . 'token=' . urlencode($this->token);
        }
        return $url;
    }
    
    /**
     * Cadastra um estabelecimento na API SistPay
     */
    public function createEstablishment($establishmentData)
    {
        if (!$this->isConfigured()) {
            throw new \Exception('API SistPay não está configurada ou não está ativa');
        }
        
        $url = $this->baseUrl . '/establishment';
        $url = $this->addTokenToUrl($url);
        
        // Headers de autenticação conforme método configurado
        $headers = [
            'Content-Type: application/json'
        ];
        
        // Adicionar headers de autenticação (exceto Query-Param que já foi adicionado na URL)
        if ($this->authMethod !== 'Query-Param') {
            $headers = array_merge($headers, $this->getAuthHeaders());
        }
        
        // Se estiver em sandbox, adicionar header
        if ($this->isSandbox) {
            $headers[] = 'X-Sandbox-Mode: true';
        }
        
        // Preparar dados conforme documentação
        $data = [
            'fant_name' => $establishmentData['nome_fantasia'] ?? '',
            'name' => $establishmentData['nome_completo'] ?? '',
            'cpf' => $this->formatCpf($establishmentData['cpf'] ?? ''),
            'cnpj' => $this->formatCnpj($establishmentData['cnpj'] ?? ''),
            'email' => $establishmentData['email'] ?? '',
            'phone1' => $this->formatPhone($establishmentData['telefone'] ?? ''),
            'phone2' => $this->formatPhone($establishmentData['telefone2'] ?? ''),
            'person_type' => ($establishmentData['registration_type'] ?? 'PF') === 'PF' ? 'f' : 'j',
            'plan' => $establishmentData['plan'] ?? 1,
            'segment' => $establishmentData['segment'] ?? 1,
            'code' => $establishmentData['code'] ?? null,
            'corp_name' => $establishmentData['razao_social'] ?? null,
            'ie' => $establishmentData['ie'] ?? null,
            'open_date' => $establishmentData['open_date'] ?? null,
            'birth_date' => $establishmentData['birth_date'] ?? null,
            'address' => $establishmentData['logradouro'] ?? '',
            'number' => $establishmentData['numero'] ?? '',
            'complement' => $establishmentData['complemento'] ?? null,
            'district' => $establishmentData['bairro'] ?? '',
            'city' => $establishmentData['cidade'] ?? '',
            'state' => strtoupper($establishmentData['uf'] ?? ''),
            'zip_code' => $this->formatCep($establishmentData['cep'] ?? '')
        ];
        
        write_log('Dados preparados para API (antes de filtrar null): ' . json_encode($data, JSON_UNESCAPED_UNICODE), 'app.log');
        
        // Remover campos null (mas manter strings vazias se necessário)
        $data = array_filter($data, function($value) {
            return $value !== null;
        });
        
        write_log('Dados finais enviados para API: ' . json_encode($data, JSON_UNESCAPED_UNICODE), 'app.log');
        write_log('URL: ' . $url, 'app.log');
        write_log('Headers: ' . json_encode($headers), 'app.log');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        write_log('=== RESPOSTA DA API SISTPAY (createEstablishment) ===', 'app.log');
        write_log('HTTP Code: ' . $httpCode, 'app.log');
        write_log('Response: ' . $response, 'app.log');
        write_log('cURL Error: ' . ($error ?: 'Nenhum'), 'app.log');
        
        if ($error) {
            write_log('Erro cURL: ' . $error, 'app.log');
            throw new \Exception('Erro na requisição: ' . $error);
        }
        
        $result = json_decode($response, true);
        write_log('Resultado decodificado: ' . json_encode($result, JSON_UNESCAPED_UNICODE), 'app.log');
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $result['data'] ?? $result,
                'message' => $result['message'] ?? 'Estabelecimento cadastrado com sucesso'
            ];
        } else {
            $errorMessage = $result['error'] ?? ($result['errors'] ?? 'Erro desconhecido');
            if (is_array($errorMessage)) {
                $errorMessage = implode(', ', $errorMessage);
            }
            // Se houver array de erros detalhados, incluir
            if (isset($result['errors']) && is_array($result['errors'])) {
                $errorMessage .= ' | Erros detalhados: ' . json_encode($result['errors'], JSON_UNESCAPED_UNICODE);
            }
            write_log('Erro da API: ' . $errorMessage, 'app.log');
            throw new \Exception('Erro na API: ' . $errorMessage);
        }
    }
    
    /**
     * Valida um estabelecimento (sandbox)
     */
    public function validateEstablishment($establishmentData)
    {
        if (!$this->isConfigured()) {
            throw new \Exception('API SistPay não está configurada ou não está ativa');
        }
        
        $url = $this->baseUrl . '/establishment/validate';
        $url = $this->addTokenToUrl($url);
        
        $headers = [
            'Content-Type: application/json'
        ];
        
        // Adicionar headers de autenticação (exceto Query-Param que já foi adicionado na URL)
        if ($this->authMethod !== 'Query-Param') {
            $headers = array_merge($headers, $this->getAuthHeaders());
        }
        
        // Preparar dados conforme documentação
        $data = [
            'fant_name' => $establishmentData['nome_fantasia'] ?? '',
            'name' => $establishmentData['nome_completo'] ?? '',
            'cpf' => $this->formatCpf($establishmentData['cpf'] ?? ''),
            'cnpj' => $this->formatCnpj($establishmentData['cnpj'] ?? ''),
            'email' => $establishmentData['email'] ?? '',
            'phone1' => $this->formatPhone($establishmentData['telefone'] ?? ''),
            'phone2' => $this->formatPhone($establishmentData['telefone2'] ?? ''),
            'person_type' => ($establishmentData['registration_type'] ?? 'PF') === 'PF' ? 'f' : 'j',
            'plan' => $establishmentData['plan'] ?? 1,
            'segment' => $establishmentData['segment'] ?? 1,
            'code' => $establishmentData['code'] ?? null,
            'corp_name' => $establishmentData['razao_social'] ?? null,
            'ie' => $establishmentData['ie'] ?? null,
            'open_date' => $establishmentData['open_date'] ?? null,
            'birth_date' => $establishmentData['birth_date'] ?? null,
            'address' => $establishmentData['logradouro'] ?? '',
            'number' => $establishmentData['numero'] ?? '',
            'complement' => $establishmentData['complemento'] ?? null,
            'district' => $establishmentData['bairro'] ?? '',
            'city' => $establishmentData['cidade'] ?? '',
            'state' => strtoupper($establishmentData['uf'] ?? ''),
            'zip_code' => $this->formatCep($establishmentData['cep'] ?? '')
        ];
        
        // Remover campos null
        $data = array_filter($data, function($value) {
            return $value !== null;
        });
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('Erro na requisição: ' . $error);
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $result['data'] ?? $result,
                'message' => $result['message'] ?? 'Dados válidos',
                'warnings' => $result['warnings'] ?? []
            ];
        } else {
            $errorMessage = $result['error'] ?? ($result['errors'] ?? 'Erro desconhecido');
            if (is_array($errorMessage)) {
                $errorMessage = implode(', ', $errorMessage);
            }
            throw new \Exception('Erro na API: ' . $errorMessage);
        }
    }
    
    /**
     * Lista os planos disponíveis
     */
    public function getPlans()
    {
        // Permitir buscar planos se tiver token configurado (mesmo em sandbox)
        if (empty($this->token)) {
            throw new \Exception('API SistPay não está configurada. Configure o token primeiro.');
        }
        
        $url = $this->baseUrl . '/plans';
        $url = $this->addTokenToUrl($url);
        
        $headers = [
            'Content-Type: application/json'
        ];
        
        // Adicionar headers de autenticação (exceto Query-Param que já foi adicionado na URL)
        if ($this->authMethod !== 'Query-Param') {
            $headers = array_merge($headers, $this->getAuthHeaders());
        }
        
        write_log('=== BUSCANDO PLANOS SISTPAY ===', 'app.log');
        write_log('URL: ' . $url, 'app.log');
        write_log('Headers: ' . json_encode($headers), 'app.log');
        write_log('Auth Method: ' . $this->authMethod, 'app.log');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        write_log('Response HTTP Code: ' . $httpCode, 'app.log');
        write_log('Response: ' . $response, 'app.log');
        
        if ($error) {
            write_log('Erro cURL: ' . $error, 'app.log');
            throw new \Exception('Erro na requisição: ' . $error);
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $plans = $result['data'] ?? [];
            write_log('Planos encontrados: ' . count($plans), 'app.log');
            write_log('Planos: ' . json_encode($plans), 'app.log');
            return $plans;
        } else {
            $errorMsg = $result['error'] ?? ($result['message'] ?? 'Erro ao buscar planos');
            write_log('Erro na API: ' . $errorMsg, 'app.log');
            throw new \Exception('Erro ao buscar planos: ' . $errorMsg);
        }
    }
    
    /**
     * Lista os segmentos disponíveis
     */
    public function getSegments()
    {
        if (!$this->isConfigured()) {
            throw new \Exception('API SistPay não está configurada ou não está ativa');
        }
        
        $url = $this->baseUrl . '/segments';
        $url = $this->addTokenToUrl($url);
        
        $headers = [];
        
        // Adicionar headers de autenticação (exceto Query-Param que já foi adicionado na URL)
        if ($this->authMethod !== 'Query-Param') {
            $headers = array_merge($headers, $this->getAuthHeaders());
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('Erro na requisição: ' . $error);
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return $result['data'] ?? [];
        } else {
            throw new \Exception('Erro ao buscar segmentos');
        }
    }
    
    /**
     * Formata CPF para XXX.XXX.XXX-XX
     */
    private function formatCpf($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
        return $cpf;
    }
    
    /**
     * Formata CNPJ para XX.XXX.XXX/XXXX-XX
     */
    private function formatCnpj($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) === 14) {
            return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
        }
        return $cnpj;
    }
    
    /**
     * Formata telefone para (XX) XXXXX-XXXX
     */
    private function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 11) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7, 4);
        } elseif (strlen($phone) === 10) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6, 4);
        }
        return $phone;
    }
    
    /**
     * Formata CEP para XXXXX-XXX
     */
    private function formatCep($cep)
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        if (strlen($cep) === 8) {
            return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
        }
        return $cep;
    }
}

