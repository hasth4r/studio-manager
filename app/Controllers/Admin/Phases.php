<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Phases extends BaseController
{
    private function db() { return \Config\Database::connect(); }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');
        $projectId = $this->request->getGet('project_id');
        if (!$projectId) return $this->response->setJSON(['status'=>'error','message'=>'project_id required']);
        $phases = $this->db()->table('project_phases')
            ->where('project_id', $projectId)
            ->orderBy('sort_order','ASC')
            ->get()->getResultArray();
        return $this->response->setJSON(['status'=>'success','phases'=>$phases]);
    }

    public function save()
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status'=>'error','message'=>'Unauthorized']);
        $id         = $this->request->getPost('id');
        $projectId  = $this->request->getPost('project_id');
        $name       = $this->request->getPost('name');
        $color      = $this->request->getPost('color') ?? '#8b5cf6';
        $sortOrder  = (int)$this->request->getPost('sort_order') ?? 0;
        $startDate  = $this->request->getPost('start_date') ?: null;
        $endDate    = $this->request->getPost('end_date') ?: null;

        if (!$projectId || !$name) return $this->response->setJSON(['status'=>'error','message'=>'Missing fields']);

        $data = ['project_id'=>$projectId,'name'=>$name,'color'=>$color,'sort_order'=>$sortOrder,'start_date'=>$startDate,'end_date'=>$endDate];

        $db = $this->db();
        if ($id) {
            $db->table('project_phases')->where('id',$id)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $db->table('project_phases')->insert($data);
            $id = $db->insertID();
        }

        return $this->response->setJSON(['status'=>'success','id'=>$id]);
    }

    public function delete()
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status'=>'error','message'=>'Unauthorized']);
        $id = $this->request->getPost('id');
        if (!$id) return $this->response->setJSON(['status'=>'error','message'=>'ID required']);
        $this->db()->table('project_phases')->where('id',$id)->delete();
        // Unlink tasks from this phase
        $this->db()->table('tasks')->where('phase_id',$id)->update(['phase_id'=>null]);
        return $this->response->setJSON(['status'=>'success']);
    }

    public function assignTask()
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status'=>'error','message'=>'Unauthorized']);
        $taskId  = $this->request->getPost('task_id');
        $phaseId = $this->request->getPost('phase_id');
        if (!$taskId) return $this->response->setJSON(['status'=>'error','message'=>'task_id required']);
        $this->db()->table('tasks')->where('id',$taskId)->update(['phase_id'=>$phaseId ?: null]);
        return $this->response->setJSON(['status'=>'success']);
    }
}
