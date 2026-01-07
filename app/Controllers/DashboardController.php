<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\User;
use App\Models\Representative;
use App\Models\Establishment;

class DashboardController
{
    private $userModel;
    private $representativeModel;
    private $establishmentModel;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->representativeModel = new Representative();
        $this->establishmentModel = new Establishment();
    }
    
    public function index()
    {
        Auth::requireAuth();
        
        if (Auth::isAdmin()) {
            return $this->adminDashboard();
        } elseif (Auth::isRepresentative()) {
            return $this->representativeDashboard();
        }
        
        // Se chegou aqui, a sessão está corrompida (autenticado mas sem tipo válido)
        // Limpar sessão e redirecionar para login
        Auth::logout();
        $_SESSION['error'] = 'Sessão inválida. Por favor, faça login novamente.';
        redirect(url('login'));
    }
    
    private function adminDashboard()
    {
        // KPIs principais
        $stats = $this->establishmentModel->getStats();
        
        // KPIs por produto
        $pagseguroStats = $this->establishmentModel->getStats(['produto' => 'PAGSEGURO_MP']);
        $fgtsStats = $this->establishmentModel->getStats(['produto' => 'FGTS']);
        $membroKeyStats = $this->establishmentModel->getStats(['produto' => 'MEMBRO_KEY']);
        
        // Top 5 cidades
        $topCities = $this->establishmentModel->getTopCities(5);
        
        // Evolução mensal - processar dados para o gráfico
        $monthlyEvolutionRaw = $this->establishmentModel->getMonthlyEvolution(12);
        
        // Agrupar dados por mês, somando todos os produtos
        $monthlyEvolution = [];
        $monthlyTotals = [];
        
        foreach ($monthlyEvolutionRaw as $item) {
            $month = $item['mes'];
            if (!isset($monthlyTotals[$month])) {
                $monthlyTotals[$month] = 0;
            }
            $monthlyTotals[$month] += $item['total'];
        }
        
        foreach ($monthlyTotals as $month => $total) {
            $monthlyEvolution[] = [
                'mes' => $month,
                'total' => $total
            ];
        }
        
        // Estatísticas de usuários e representantes
        $userStats = $this->userModel->getStats();
        $representativeStatsRaw = $this->representativeModel->getStats();
        
        // Mapear os dados dos representantes para o formato esperado
        $representativeStats = [
            'total' => $representativeStatsRaw['total'] ?? 0,
            'ativos' => $representativeStatsRaw['ativos'] ?? 0
        ];
        
        // Filtros para o período atual
        $currentMonth = date('Y-m-01');
        $lastMonth = date('Y-m-01', strtotime('-1 month'));
        
        $currentMonthStats = $this->establishmentModel->getStats([
            'date_from' => $currentMonth
        ]);
        
        $lastMonthStats = $this->establishmentModel->getStats([
            'date_from' => $lastMonth,
            'date_to' => $currentMonth
        ]);
        
        $data = [
            'title' => 'Dashboard Administrativo',
            'stats' => $stats,
            'pagseguro_stats' => $pagseguroStats,
            'fgts_stats' => $fgtsStats,
            'membro_key_stats' => $membroKeyStats,
            'top_cities' => $topCities,
            'monthly_evolution' => $monthlyEvolution,
            'user_stats' => $userStats,
            'representative_stats' => $representativeStats,
            'current_month_stats' => $currentMonthStats,
            'last_month_stats' => $lastMonthStats
        ];
        
        view('dashboard/admin', $data);
    }
    
    private function representativeDashboard()
    {
        $representative = Auth::representative();
        
        // KPIs do representante
        $representativeStats = $this->representativeModel->getRepresentativeStats($representative['id']);
        
        // Mapear para o formato esperado pela view
        $clientStats = [
            'total_clientes' => $representativeStats['total_establishments'],
            'aprovados' => $representativeStats['approved_establishments'],
            'pendentes' => $representativeStats['pending_establishments'],
            'reprovados' => $representativeStats['reproved_establishments'],
            'cadastros_ultimo_mes' => $representativeStats['establishments_last_month']
        ];
        
        // Clientes do representante
        $recentClients = $this->establishmentModel->getAll([
            'representative_id' => $representative['id'],
            'limit' => 10
        ]);
        
        // Clientes pendentes
        $pendingClients = $this->establishmentModel->getAll([
            'representative_id' => $representative['id'],
            'status' => 'PENDING'
        ]);
        
        // Estatísticas do mês atual
        $currentMonth = date('Y-m-01');
        $currentMonthStats = $this->establishmentModel->getStats([
            'representative_id' => $representative['id'],
            'date_from' => $currentMonth
        ]);
        
        $data = [
            'title' => 'Dashboard Representante',
            'representative' => $representative,
            'client_stats' => $clientStats,
            'recent_clients' => $recentClients,
            'pending_clients' => $pendingClients,
            'current_month_stats' => $currentMonthStats
        ];
        
        view('dashboard/representative', $data);
    }
}
