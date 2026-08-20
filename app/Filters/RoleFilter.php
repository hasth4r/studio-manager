<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Verify that the logged-in user possesses at least one of the required roles.
     * Usage in Routes: ['filter' => 'role:site_manager,admin']
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');

        $isAjax = $request->isAJAX() 
               || $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest'
               || stripos($request->getHeaderLine('Accept'), 'application/json') !== false;

        if (!session()->get('isLoggedIn')) {
            if ($isAjax) {
                return service('response')->setStatusCode(401)->setJSON([
                    'success' => false,
                    'error'   => 'Authentication required. Please log in.',
                    'redirect' => '/login'
                ]);
            }
            return redirect()->to('/login')->with('error', 'Please log in to continue.');
        }

        if (empty($arguments)) {
            return;
        }

        // Check if user has any of the passed role arguments
        if (!has_any_role($arguments)) {
            if ($isAjax) {
                return service('response')->setStatusCode(403)->setJSON([
                    'success' => false,
                    'error'   => 'Unauthorized. Insufficient permissions for this action.'
                ]);
            }

            // Role-based smart fallback redirect
            if (has_role('client')) {
                return redirect()->to('/client/dashboard')->with('error', 'Access restricted.');
            }
            if (has_any_role(['site_manager', 'admin'])) {
                return redirect()->to('/admin/dashboard')->with('error', 'Access restricted.');
            }
            if (has_role('project_manager') || is_any_supervisor()) {
                return redirect()->to('/pm/dashboard')->with('error', 'Access restricted.');
            }
            return redirect()->to('/user/dashboard')->with('error', 'You do not have permission to access that area.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
