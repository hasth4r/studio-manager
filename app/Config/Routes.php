<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Auth::login');

// DEBUG - remove after fixing
$routes->get('debug/calc', 'Debug::calc');
$routes->get('debug/seedBenchmarks', 'Debug::seedBenchmarks');

// Auth Routes
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::processLogin');
$routes->get('logout', 'Auth::logout');

// Notifications
$routes->get('notifications/getUnread', 'Notifications::getUnread', ['filter' => 'auth']);
$routes->get('notifications/read/(:num)', 'Notifications::markRead/$1', ['filter' => 'auth']);

// Admin Portal Routes
$routes->group('admin', ['filter' => 'auth', 'namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    
    // Clients Management
    $routes->get('clients', 'Clients::index');
    $routes->get('clients/create', 'Clients::create');
    $routes->post('clients/store', 'Clients::store');
    $routes->post('clients/createUser', 'Clients::createUser');
    
    // Admin Settings
    $routes->get('settings', 'Settings::index');
    $routes->post('settings/update', 'Settings::update');

    // Notifications Admin Tool
    $routes->get('notifications/generate', 'NotificationsAdmin::index');
    $routes->post('notifications/generate/process', 'NotificationsAdmin::process');

    // Database Manager
    $routes->get('database', 'DatabaseManager::index');
    $routes->post('database/backup', 'DatabaseManager::createBackup');
    $routes->post('database/restore', 'DatabaseManager::restoreBackup');
    $routes->get('database/download', 'DatabaseManager::downloadBackup');
    $routes->post('database/delete', 'DatabaseManager::deleteBackup');

    // Users Management
    $routes->get('users', 'Users::index');
    $routes->post('users/store', 'Users::store');
    $routes->post('users/update/(:num)', 'Users::update/$1');

    // Studio Budgeting & Economics
    $routes->get('budgeting', 'Budgeting::index');
    $routes->post('budgeting/update', 'Budgeting::update');

    // Projects Management
    $routes->get('projects', 'Projects::index');
    $routes->get('projects/create', 'Projects::create');
    $routes->post('projects/store', 'Projects::store');
    $routes->post('projects/syncFolders/(:num)', 'Projects::syncFolders/$1');
    $routes->get('projects/(:num)', 'Projects::show/$1');
    $routes->post('projects/storeSequence', 'Projects::storeSequence');
    $routes->post('projects/storeShot', 'Projects::storeShot');
    $routes->post('projects/importShots/(:num)', 'Projects::importShots/$1');
    $routes->post('projects/chunkUpload/(:num)', 'Projects::chunkUpload/$1');
    $routes->post('projects/storeAsset', 'Projects::storeAsset');
    $routes->post('projects/storeBenchmarks', 'Projects::storeBenchmarks');
    $routes->post('projects/updateSequence/(:num)', 'Projects::updateSequence/$1');
    $routes->post('projects/deleteSequence/(:num)', 'Projects::deleteSequence/$1');
    $routes->post('projects/updateShot/(:num)', 'Projects::updateShot/$1');
    $routes->post('projects/deleteShot/(:num)', 'Projects::deleteShot/$1');
    $routes->get('projects/(:num)/breakdown', 'Projects::breakdown/$1');
    $routes->post('projects/bulkAssignTasks', 'Projects::bulkAssignTasks');
    $routes->post('projects/inlineUpdateTask', 'Projects::inlineUpdateTask');
    $routes->post('projects/inlineUpdateShot', 'Projects::inlineUpdateShot');
    $routes->post('projects/inlineAddTask', 'Projects::inlineAddTask');
    $routes->post('projects/deleteTaskAjax', 'Projects::deleteTaskAjax');
    $routes->post('projects/saveAutoThumbnailAjax', 'Projects::saveAutoThumbnailAjax');
    $routes->post('projects/updateAgreedBudget', 'Projects::updateAgreedBudget');
    $routes->get('projects/(:num)/analysis', 'Projects::analysis/$1');
    $routes->get('projects/(:num)/briefing', '\App\Controllers\Client\Briefing::index/$1');

    // VFX Entities
    $routes->get('shots/(:num)', 'Shots::show/$1');
    $routes->post('shots/updateSettings/(:num)', 'Shots::updateSettings/$1');
    $routes->get('assets/(:num)', 'Assets::show/$1');
    $routes->post('tasks/store', 'Tasks::store');
    $routes->post('tasks/updateAssignee', 'Tasks::updateAssignee');
    $routes->post('tasks/updateComplexity', 'Tasks::updateComplexity');
    $routes->post('tasks/updateSettings', 'Tasks::updateSettings');
    $routes->post('tasks/recalculate/(:num)', 'Tasks::recalculate/$1');
    $routes->post('tasks/bulkRecalculate/(:num)', 'Tasks::bulkRecalculate/$1');
    $routes->post('tasks/reviewStatus/(:num)', 'Tasks::reviewStatus/$1'); // PM review tasks
    
    // Scheduling Dashboard
    $routes->get('scheduling',                     'Scheduling::index');
    $routes->get('scheduling/data',                'Scheduling::getSchedulingData');
    $routes->post('scheduling/autoSchedule',       'Scheduling::autoSchedule');
    $routes->post('scheduling/saveDates',          'Scheduling::saveDates');
    $routes->post('scheduling/setDeadline',        'Scheduling::setDeadline');
    $routes->post('scheduling/saveHoliday',        'Scheduling::saveHoliday');
    $routes->post('scheduling/deleteHoliday',      'Scheduling::deleteHoliday');
    $routes->post('scheduling/updateCapacity',     'Scheduling::updateCapacity');
    $routes->post('scheduling/updateEstimate',     'Scheduling::updateEstimate');

    // Project Phases
    $routes->get('phases',                         'Phases::index');
    $routes->post('phases/save',                   'Phases::save');
    $routes->post('phases/delete',                 'Phases::delete');
    $routes->post('phases/assignTask',             'Phases::assignTask');

    // Reviews Dashboard
    $routes->get('reviews', 'Reviews::index');
    $routes->get('reviews/player/(:num)', 'Reviews::player/$1');
    $routes->get('reviews/sequence/(:num)', 'Reviews::sequencePlayer/$1');
    $routes->get('reviews/sequence_data/(:num)', 'Reviews::getSequenceData/$1');
    $routes->post('reviews/saveAnnotation/(:num)', 'Reviews::saveAnnotation/$1');
    $routes->post('reviews/updateStatus/(:num)', 'Reviews::updateStatus/$1');
    $routes->post('reviews/uploadReference', 'Reviews::uploadReference');
    $routes->post('reviews/updateComment/(:num)', 'Reviews::updateComment/$1');
    $routes->post('reviews/deleteComment/(:num)', 'Reviews::deleteComment/$1');
    $routes->post('reviews/createShareLink', 'Reviews::createShareLink');
    $routes->get('reviews/getShareLinks/(:num)', 'Reviews::getShareLinks/$1');
    $routes->post('reviews/revokeShareLink', 'Reviews::revokeShareLink');
    
    // Media Manager
    $routes->get('media', 'MediaManager::index');
    $routes->get('media/tree', 'MediaManager::getTreeData');
    $routes->post('media/replaceMedia/(:num)', 'MediaManager::replaceMedia/$1');

    // Project Types Management
    $routes->get('project-types', 'ProjectTypes::index');
    $routes->post('project-types/store', 'ProjectTypes::store');

    // Cloudflare R2 Migration
    $routes->get('migrate-r2', 'MigrateR2::index');
    
    // Web Installer
    $routes->get('install-composer', 'InstallComposer::index');
});

// Secure Media Serving Route
$routes->get('media/serve/(.+)', 'Media::serve/$1');

// Public Presentation Share Route (No login required)
$routes->get('share/lineup/(:num)', 'Share::lineup/$1');

$routes->get('sys-migrate', function() {
    $migrate = \Config\Services::migrations();
    try {
        $migrate->latest();
        return 'Migrations run successfully.';
    } catch (\Throwable $e) {
        return 'Migration error: ' . $e->getMessage();
    }
});

// User Portal Routes (Artists)
$routes->group('user', ['filter' => 'auth', 'namespace' => 'App\Controllers\User'], function($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->post('tasks/updateStatus/(:num)', 'Tasks::updateStatus/$1');
    $routes->post('tasks/updateMeta/(:num)', 'Tasks::updateMeta/$1');
    $routes->get('tasks/submitVersionForm/(:num)', 'Tasks::submitVersionForm/$1');
    $routes->post('tasks/submitReview', 'Tasks::submitReview');
});

// Client Portal Routes
$routes->group('client', ['filter' => 'auth', 'namespace' => 'App\Controllers\Client'], function($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    
    // Client Sequence Player & Annotations
    $routes->get('reviews/player/(:num)', 'Reviews::player/$1');
    $routes->get('reviews/sequence/(:num)', 'Reviews::sequencePlayer/$1');
    $routes->post('reviews/saveAnnotation/(:num)', 'Reviews::saveAnnotation/$1');
    $routes->post('reviews/updateComment/(:num)', 'Reviews::updateComment/$1');
    $routes->post('reviews/deleteComment/(:num)', 'Reviews::deleteComment/$1');
    $routes->post('reviews/uploadReference', 'Reviews::uploadReference');

    // Client Shot Briefing & Reference Matrix
    $routes->get('projects/(:num)/briefing', 'Briefing::index/$1');
    $routes->post('projects/saveBriefAjax', 'Briefing::saveBriefAjax');
    $routes->post('projects/uploadReferenceAjax', 'Briefing::uploadReferenceAjax');
    $routes->post('projects/deleteReferenceAjax', 'Briefing::deleteReferenceAjax');
    $routes->post('projects/updateBudget', 'Dashboard::updateBudget');
});

// Shared API Endpoints
$routes->group('api', ['filter' => 'auth', 'namespace' => 'App\Controllers'], function($routes) {
    $routes->post('clients', 'Api::createClient');
    $routes->post('collaborators', 'Api::createCollaborator');
});

// Telegram Integration
$routes->get('telegram/link', 'Telegram::link');
$routes->match(['get', 'post'], 'telegram/poll', 'Telegram::poll');

$routes->get('sys-alter-tg', function() {
    $db = \Config\Database::connect();
    try {
        $db->query("ALTER TABLE users ADD COLUMN telegram_chat_id VARCHAR(255) NULL");
        echo "Added telegram_chat_id<br>";
    } catch (\Exception $e) {
        echo "Error or exists: " . $e->getMessage() . "<br>";
    }
    
    try {
        $db->query("ALTER TABLE users ADD COLUMN telegram_link_code VARCHAR(100) NULL");
        echo "Added telegram_link_code<br>";
    } catch (\Exception $e) {
        echo "Error or exists: " . $e->getMessage() . "<br>";
    }
    return 'Done.';
});
