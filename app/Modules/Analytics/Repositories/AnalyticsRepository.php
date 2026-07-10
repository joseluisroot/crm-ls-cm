<?php

namespace Modules\Analytics\Repositories;

use CodeIgniter\Database\BaseConnection;

class AnalyticsRepository
{
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function totalCitizens(): int
    {
        return $this->db
            ->table('citizens')
            ->countAllResults();
    }

    public function newCitizensToday(): int
    {
        return $this->db
            ->table('citizens')
            ->where('DATE(created_at)', date('Y-m-d'), false)
            ->countAllResults();
    }

    public function totalConversations(): int
    {
        return $this->db
            ->table('conversations')
            ->countAllResults();
    }

    public function openConversations(): int
    {
        return $this->db
            ->table('conversations')
            ->where('status', 'open')
            ->countAllResults();
    }

    public function conversationsToday(): int
    {
        return $this->db
            ->table('conversations')
            ->where('DATE(created_at)', date('Y-m-d'), false)
            ->countAllResults();
    }

    public function totalCases(): int
    {
        return $this->db
            ->table('cases')
            ->countAllResults();
    }

    public function openCases(): int
    {
        return $this->db
            ->table('cases')
            ->join(
                'case_statuses',
                'case_statuses.id = cases.status_id'
            )
            ->whereNotIn('case_statuses.slug', [
                'atendido',
                'cerrado',
                'finalizado',
                'resuelto',
            ])
            ->countAllResults();
    }

    public function resolvedCases(): int
    {
        return $this->db
            ->table('cases')
            ->join(
                'case_statuses',
                'case_statuses.id = cases.status_id'
            )
            ->whereIn('case_statuses.slug', [
                'atendido',
                'cerrado',
                'finalizado',
                'resuelto',
            ])
            ->countAllResults();
    }

    public function unassignedCases(): int
    {
        return $this->db
            ->table('cases')
            ->groupStart()
            ->where('assigned_user_id', null)
            ->orWhere('assigned_user_id', 0)
            ->groupEnd()
            ->countAllResults();
    }

    public function assignedCases(): int
    {
        return $this->db
            ->table('cases')
            ->where('assigned_user_id IS NOT NULL', null, false)
            ->where('assigned_user_id >', 0)
            ->countAllResults();
    }

    public function totalInboundMessages(): int
    {
        return $this->db
            ->table('messages')
            ->where('direction', 'inbound')
            ->countAllResults();
    }

    public function totalOutboundMessages(): int
    {
        return $this->db
            ->table('messages')
            ->where('direction', 'outbound')
            ->whereIn('sent_status', ['sent', 'delivered', 'read'])
            ->countAllResults();
    }

    public function messagesToday(): int
    {
        return $this->db
            ->table('messages')
            ->where('DATE(created_at)', date('Y-m-d'), false)
            ->countAllResults();
    }

    public function recurringCitizens(): int
    {
        $query = $this->db
            ->table('conversations')
            ->select('citizen_id')
            ->groupBy('citizen_id')
            ->having('COUNT(id) >', 1)
            ->get();

        return count($query->getResultArray());
    }

    public function citizensWithCompletedContext(): int
    {
        if (!$this->db->tableExists('conversation_context')) {
            return 0;
        }

        $query = $this->db
            ->table('conversation_context')
            ->select('conversation_id')
            ->whereIn('context_key', [
                'municipality',
                'community',
                'description',
            ])
            ->groupBy('conversation_id')
            ->having('COUNT(DISTINCT context_key) >=', 3)
            ->get();

        return count($query->getResultArray());
    }

    public function casesByStatus(): array
    {
        return $this->db
            ->table('cases')
            ->select([
                'case_statuses.id',
                'case_statuses.name',
                'case_statuses.slug',
                'COUNT(cases.id) AS total',
            ])
            ->join(
                'case_statuses',
                'case_statuses.id = cases.status_id'
            )
            ->groupBy([
                'case_statuses.id',
                'case_statuses.name',
                'case_statuses.slug',
            ])
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function casesByCategory(): array
    {
        return $this->db
            ->table('cases')
            ->select([
                'categories.id',
                'categories.name',
                'categories.slug',
                'COUNT(cases.id) AS total',
            ])
            ->join(
                'categories',
                'categories.id = cases.category_id',
                'left'
            )
            ->groupBy([
                'categories.id',
                'categories.name',
                'categories.slug',
            ])
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function casesByMunicipality(): array
    {
        return $this->db
            ->table('cases')
            ->select([
                'citizens.municipality',
                'COUNT(cases.id) AS total',
            ])
            ->join(
                'citizens',
                'citizens.id = cases.citizen_id'
            )
            ->where('citizens.municipality IS NOT NULL', null, false)
            ->where('citizens.municipality !=', '')
            ->groupBy('citizens.municipality')
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();
    }

    public function casesByResponsible(): array
    {
        return $this->db
            ->table('cases')
            ->select([
                'admin_users.id',
                'admin_users.name',
                'COUNT(cases.id) AS total',
            ])
            ->join(
                'admin_users',
                'admin_users.id = cases.assigned_user_id',
                'left'
            )
            ->where('cases.assigned_user_id IS NOT NULL', null, false)
            ->groupBy([
                'admin_users.id',
                'admin_users.name',
            ])
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function casesCreatedLastDays(int $days = 14): array
    {
        $startDate = date(
            'Y-m-d',
            strtotime('-' . max(1, $days - 1) . ' days')
        );

        return $this->db
            ->table('cases')
            ->select([
                'DATE(created_at) AS date',
                'COUNT(id) AS total',
            ])
            ->where('created_at >=', $startDate . ' 00:00:00')
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function messagesCreatedLastDays(int $days = 14): array
    {
        $startDate = date(
            'Y-m-d',
            strtotime('-' . max(1, $days - 1) . ' days')
        );

        return $this->db
            ->table('messages')
            ->select([
                'DATE(created_at) AS date',
                'COUNT(id) AS total',
            ])
            ->where('created_at >=', $startDate . ' 00:00:00')
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->get()
            ->getResultArray();
    }
}