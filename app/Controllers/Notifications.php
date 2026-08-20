<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Notifications extends BaseController
{
    public function getUnread()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $userId = session()->get('userId');
        $db = \Config\Database::connect();
        
        // Safety check to ensure table exists before querying
        if (!$db->tableExists('notifications')) {
            return $this->response->setJSON(['status' => 'success', 'notifications' => []]);
        }

        $notifications = $db->table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->orderBy('created_at', 'DESC')
            ->get()->getResult();

        return $this->response->setJSON(['status' => 'success', 'notifications' => $notifications]);
    }

    public function markRead($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('userId');
        $db = \Config\Database::connect();
        
        if (!$db->tableExists('notifications')) {
            return redirect()->to('/user/dashboard');
        }

        $notification = $db->table('notifications')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->get()->getRow();

        if ($notification) {
            $db->table('notifications')
                ->where('id', $id)
                ->update(['is_read' => 1]);
                
            if ($notification->link) {
                return redirect()->to($notification->link);
            }
        }

        // Fallback redirection based on role
        if (has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/admin/dashboard');
        } elseif (has_role('client')) {
            return redirect()->to('/client/dashboard');
        } elseif (has_role('project_manager') || is_any_supervisor()) {
            return redirect()->to('/pm/dashboard');
        }
        return redirect()->to('/user/dashboard');
    }
}
