<?php

if (!function_exists('get_user_roles')) {
    /**
     * Get all assigned roles for the currently logged-in user as an array.
     */
    function get_user_roles(): array
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return [];
        }

        $roles = $session->get('userRoles');
        if (empty($roles)) {
            $single = $session->get('userRole');
            $roles = $single ? [$single] : ['artist'];
        }

        if (is_string($roles)) {
            $decoded = json_decode($roles, true);
            $roles = is_array($decoded) ? $decoded : [$roles];
        }

        return is_array($roles) ? array_values(array_unique($roles)) : [];
    }
}

if (!function_exists('has_role')) {
    /**
     * Check if the logged-in user possesses a specific role.
     * Site Managers possess all role permissions by default.
     */
    function has_role(string $role): bool
    {
        $userRoles = get_user_roles();
        if (in_array('site_manager', $userRoles)) {
            return true;
        }
        return in_array($role, $userRoles);
    }
}

if (!function_exists('has_any_role')) {
    /**
     * Check if the logged-in user possesses ANY of the specified roles.
     */
    function has_any_role(array $roles): bool
    {
        $userRoles = get_user_roles();
        if (in_array('site_manager', $userRoles)) {
            return true;
        }
        return !empty(array_intersect($roles, $userRoles));
    }
}

if (!function_exists('is_project_supervisor')) {
    /**
     * Check if the current or specified user is a Supervisor on a given project.
     * Admins and Site Managers are supervisors across all projects.
     */
    function is_project_supervisor(int $projectId, ?int $userId = null): bool
    {
        $userId = $userId ?? session()->get('userId');
        if (!$userId) return false;

        if (has_any_role(['site_manager', 'admin'])) {
            return true;
        }

        $db = \Config\Database::connect();
        $proj = $db->table('projects')->select('supervisor_id')->where('id', $projectId)->get()->getRow();
        return $proj && (int)$proj->supervisor_id === (int)$userId;
    }
}

if (!function_exists('is_sequence_supervisor')) {
    /**
     * Check if the current or specified user is a Supervisor on a given sequence.
     */
    function is_sequence_supervisor(int $sequenceId, ?int $userId = null): bool
    {
        $userId = $userId ?? session()->get('userId');
        if (!$userId) return false;

        if (has_any_role(['site_manager', 'admin'])) {
            return true;
        }

        $db = \Config\Database::connect();
        $seq = $db->table('sequences')->select('supervisor_id, project_id')->where('id', $sequenceId)->get()->getRow();
        if (!$seq) return false;

        if ((int)$seq->supervisor_id === (int)$userId) {
            return true;
        }

        return is_project_supervisor((int)$seq->project_id, $userId);
    }
}

if (!function_exists('can_manage_project')) {
    /**
     * Determine if the user has management/breakdown/assign permissions on a project.
     */
    function can_manage_project(int $projectId, ?int $userId = null): bool
    {
        return has_any_role(['site_manager', 'admin', 'project_manager']) || is_project_supervisor($projectId, $userId);
    }
}
