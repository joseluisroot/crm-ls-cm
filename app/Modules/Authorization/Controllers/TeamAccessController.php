<?php

declare(strict_types=1);

namespace Modules\Authorization\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

final class TeamAccessController extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $teams = $db->table('teams t')
            ->select('t.*, u.name AS supervisor_name, COUNT(tm.user_id) AS member_count')
            ->join('admin_users u', 'u.id = t.supervisor_user_id', 'left')
            ->join('team_members tm', 'tm.team_id = t.id AND tm.is_active = 1', 'left')
            ->groupBy('t.id')->orderBy('t.name')->get()->getResultArray();

        return view('Modules\Authorization\Views\teams\index', [
            'title' => 'Equipos de atención',
            'teams' => $teams,
            'users' => $db->table('admin_users')->select('id, name, email')->where('status', 'active')->orderBy('name')->get()->getResultArray(),
        ]);
    }

    public function store()
    {
        $name = trim((string) $this->request->getPost('name'));
        $code = strtoupper(trim((string) $this->request->getPost('code')));
        $supervisorId = (int) $this->request->getPost('supervisor_user_id');
        if ($name === '' || $code === '') return redirect()->back()->withInput()->with('error', 'Nombre y código son obligatorios.');

        $db = db_connect();
        if ($db->table('teams')->where('code', $code)->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'Ya existe un equipo con ese código.');
        }

        $db->table('teams')->insert([
            'uuid' => $this->uuidV4(), 'code' => $code, 'name' => $name,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'supervisor_user_id' => $supervisorId > 0 ? $supervisorId : null,
            'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('admin/access/teams/' . $db->insertID()))->with('success', 'Equipo creado correctamente.');
    }

    public function show(int $id)
    {
        $db = db_connect();
        $team = $db->table('teams')->where('id', $id)->get()->getRowArray();
        if (! $team) throw PageNotFoundException::forPageNotFound('Equipo no encontrado.');

        $memberIds = array_map('intval', array_column($db->table('team_members')->select('user_id')->where('team_id', $id)->where('is_active', 1)->get()->getResultArray(), 'user_id'));
        return view('Modules\Authorization\Views\teams\show', [
            'title' => 'Administrar equipo', 'team' => $team, 'memberIds' => $memberIds,
            'users' => $db->table('admin_users')->select('id, name, email')->where('status', 'active')->orderBy('name')->get()->getResultArray(),
        ]);
    }

    public function update(int $id)
    {
        $db = db_connect();
        if ($db->table('teams')->where('id', $id)->countAllResults() === 0) throw PageNotFoundException::forPageNotFound('Equipo no encontrado.');

        $memberIds = array_values(array_unique(array_filter(array_map('intval', (array) $this->request->getPost('member_ids')))));
        $supervisorId = (int) $this->request->getPost('supervisor_user_id');
        if ($supervisorId > 0 && ! in_array($supervisorId, $memberIds, true)) $memberIds[] = $supervisorId;

        $db->transStart();
        $db->table('teams')->where('id', $id)->update([
            'name' => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'supervisor_user_id' => $supervisorId > 0 ? $supervisorId : null,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $db->table('team_members')->where('team_id', $id)->delete();
        foreach ($memberIds as $userId) {
            $db->table('team_members')->insert(['team_id' => $id, 'user_id' => $userId, 'is_active' => 1, 'assigned_by' => (int) session()->get('admin_user_id'), 'joined_at' => date('Y-m-d H:i:s')]);
        }
        $db->transComplete();

        return redirect()->back()->with($db->transStatus() ? 'success' : 'error', $db->transStatus() ? 'Equipo actualizado correctamente.' : 'No fue posible actualizar el equipo.');
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16); $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
