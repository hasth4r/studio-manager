<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SettingsModel;
use App\Models\ProjectModel;
use App\Models\TaskModel;
use App\Models\UserModel;
use Config\Database;

class Budgeting extends BaseController
{
    protected $settingsModel;
    protected $projectModel;
    protected $taskModel;
    protected $userModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
        $this->projectModel  = new ProjectModel();
        $this->taskModel     = new TaskModel();
        $this->userModel     = new UserModel();
    }

    public function index()
    {
        if (!has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        // 1. Load Monthly Expense & Studio Settings
        $currency           = $this->settingsModel->getSetting('studio_currency', '₹');
        $monthlyAiBills     = (float)$this->settingsModel->getSetting('monthly_ai_bills', '15000.00');
        $monthlyStorage     = (float)$this->settingsModel->getSetting('monthly_storage_bills', '8000.00');
        $monthlySoftware    = (float)$this->settingsModel->getSetting('monthly_software_bills', '10000.00');
        $monthlyOps         = (float)$this->settingsModel->getSetting('monthly_ops_bills', '5000.00');
        $monthlyHours       = (float)$this->settingsModel->getSetting('monthly_billable_hours', '300.00');
        $commissionPct      = (float)$this->settingsModel->getSetting('studio_commission_pct', '30.00');
        $defaultArtistRate  = (float)$this->settingsModel->getSetting('default_artist_rate', '500.00');

        if ($monthlyHours <= 0) $monthlyHours = 300.0;

        // Auto-calculated Studio Ops Hourly Rate
        $totalMonthlyBills = $monthlyAiBills + $monthlyStorage + $monthlySoftware + $monthlyOps;
        $opsHourlyRate     = round($totalMonthlyBills / $monthlyHours, 2);

        // Keep studio_ops_hourly_rate synchronized in settings
        $this->settingsModel->setSetting('studio_ops_hourly_rate', $opsHourlyRate);

        // 2. Fetch Team Rates for calculations
        $users = $this->userModel->findAll();
        $userRateMap = [];
        $totalArtistHourlySum = 0;
        $artistCount = 0;
        foreach ($users as $u) {
            $r = (float)($u->hourly_rate ?? $defaultArtistRate);
            $userRateMap[$u->id] = $r;
            if ($u->global_role === 'artist' || empty($u->global_role)) {
                $totalArtistHourlySum += $r;
                $artistCount++;
            }
        }
        $avgArtistRate = $artistCount > 0 ? round($totalArtistHourlySum / $artistCount, 0) : $defaultArtistRate;

        // 3. Active Projects Economics Overview
        $projects = $this->projectModel->orderBy('id', 'DESC')->findAll();
        $projectBudgets = [];
        $totalActiveHours = 0.0;
        $totalActiveArtistCost = 0.0;
        $totalActiveOpsCost = 0.0;
        $totalActiveClientBudget = 0.0;

        $db = Database::connect();
        foreach ($projects as $proj) {
            $shotCount = $db->table('shots')->where('project_id', $proj->id)->countAllResults();
            $tasks = $this->taskModel->where('project_id', $proj->id)->findAll();
            
            $projHours = 0.0;
            $projArtistCost = 0.0;
            $projOpsCost = 0.0;
            $projClientCost = 0.0;

            foreach ($tasks as $t) {
                $h = (float)($t->estimated_hours ?? 0);
                $r = !empty($t->assigned_to) && isset($userRateMap[$t->assigned_to]) ? $userRateMap[$t->assigned_to] : $defaultArtistRate;
                $aCost = $h * $r;
                $oCost = $h * $opsHourlyRate;
                $margin = ($aCost + $oCost) * ($commissionPct / 100.0);
                $cCost = $aCost + $oCost + $margin;

                $projHours += $h;
                $projArtistCost += $aCost;
                $projOpsCost += $oCost;
                $projClientCost += $cCost;
            }

            $projectBudgets[] = (object)[
                'id'            => $proj->id,
                'name'          => $proj->name,
                'status'        => $proj->status ?? 'active',
                'shot_count'    => $shotCount,
                'task_count'    => count($tasks),
                'total_hours'   => round($projHours, 1),
                'artist_cost'   => round($projArtistCost, 0),
                'ops_cost'      => round($projOpsCost, 0),
                'margin_cost'   => round($projClientCost - ($projArtistCost + $projOpsCost), 0),
                'client_budget' => round($projClientCost, 0),
            ];

            $totalActiveHours += $projHours;
            $totalActiveArtistCost += $projArtistCost;
            $totalActiveOpsCost += $projOpsCost;
            $totalActiveClientBudget += $projClientCost;
        }

        $data = [
            'pageTitle'               => 'Studio Economics & Budgeting',
            'currency'                => $currency,
            'monthlyAiBills'          => $monthlyAiBills,
            'monthlyStorage'          => $monthlyStorage,
            'monthlySoftware'         => $monthlySoftware,
            'monthlyOps'              => $monthlyOps,
            'totalMonthlyBills'       => $totalMonthlyBills,
            'monthlyHours'            => $monthlyHours,
            'opsHourlyRate'           => $opsHourlyRate,
            'commissionPct'           => $commissionPct,
            'defaultArtistRate'       => $defaultArtistRate,
            'avgArtistRate'           => $avgArtistRate,
            'projectBudgets'          => $projectBudgets,
            'totalActiveHours'        => round($totalActiveHours, 1),
            'totalActiveArtistCost'   => round($totalActiveArtistCost, 0),
            'totalActiveOpsCost'      => round($totalActiveOpsCost, 0),
            'totalActiveProfitMargin' => round($totalActiveClientBudget - ($totalActiveArtistCost + $totalActiveOpsCost), 0),
            'totalActiveClientBudget' => round($totalActiveClientBudget, 0),
        ];

        return view('admin/budgeting/index', $data);
    }

    public function update()
    {
        if (!has_any_role(['site_manager', 'admin'])) {
            return redirect()->to('/admin/dashboard')->with('error', 'Unauthorized access.');
        }

        $fields = [
            'studio_currency'        => $this->request->getPost('studio_currency'),
            'monthly_ai_bills'       => (float)$this->request->getPost('monthly_ai_bills'),
            'monthly_storage_bills'  => (float)$this->request->getPost('monthly_storage_bills'),
            'monthly_software_bills' => (float)$this->request->getPost('monthly_software_bills'),
            'monthly_ops_bills'      => (float)$this->request->getPost('monthly_ops_bills'),
            'monthly_billable_hours' => max(1, (float)$this->request->getPost('monthly_billable_hours')),
            'studio_commission_pct'  => (float)$this->request->getPost('studio_commission_pct'),
            'default_artist_rate'    => (float)$this->request->getPost('default_artist_rate'),
        ];

        foreach ($fields as $k => $v) {
            if ($v !== null) {
                $this->settingsModel->setSetting($k, $v);
            }
        }

        // Auto-compute and persist studio_ops_hourly_rate
        $totalMonthly = (float)$fields['monthly_ai_bills'] + (float)$fields['monthly_storage_bills'] + (float)$fields['monthly_software_bills'] + (float)$fields['monthly_ops_bills'];
        $hrs = (float)$fields['monthly_billable_hours'] ?: 300.0;
        $opsRate = round($totalMonthly / $hrs, 2);
        $this->settingsModel->setSetting('studio_ops_hourly_rate', $opsRate);

        return redirect()->back()->with('message', 'Studio Monthly Bills & Economics successfully saved and synchronized.');
    }
}
