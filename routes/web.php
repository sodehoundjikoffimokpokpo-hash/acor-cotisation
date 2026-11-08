<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CarnetController;
use App\Http\Controllers\CollaboratorController;
use App\Http\Controllers\CollectorManagementController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\CollectorController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\LogController;
use Illuminate\Support\Facades\Schedule;

// --- 1. ROUTES D'AUTHENTIFICATION ---
Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- 2. TÂCHES PLANIFIÉES ---
Schedule::command('stock:check-expiration')->daily();

// --- 3. GESTION DES ROUTES SÉCURISÉES (AUTH) ---
Route::middleware(['auth'])->group(function () {

    // --- NOTIFICATIONS ---
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

    // --- ADMINISTRATION (ADMIN) ---
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/rights', [CollaboratorController::class, 'rights'])->name('admin.rights');
        Route::post('/rights/store-collaborator', [CollaboratorController::class, 'storeCollaborator'])->name('admin.rights.store-collaborator');
        Route::post('/rights/{id}/toggle/{permission}', [CollaboratorController::class, 'togglePermission'])->name('admin.rights.toggle');
        Route::get('/finance', [AdminController::class, 'finance'])->name('admin.finance');
        Route::post('/set-country', [AdminController::class, 'setActiveCountry'])->name('admin.set-country');
        Route::get('/reports/tarifs', [AdminController::class, 'reportTarifs'])->name('admin.reports.tarifs');
    });

    // --- ACCÈS PAR PERMISSIONS ET RÔLES (ADMIN, SECRÉTAIRE, EMPLOYÉ, COLLECTEUR) ---
    Route::middleware(['role:admin,secretary,employee,collector'])->prefix('admin')->group(function () {
        // 1. Gestion des collecteurs
        Route::middleware(['permission:can_manage_collectors'])->group(function () {
            Route::get('/collectors', [CollectorManagementController::class, 'collectors'])->name('admin.collectors');
            Route::post('/collectors', [CollectorManagementController::class, 'storeCollector'])->name('admin.collectors.store');
            Route::post('/collectors/{id}', [CollectorManagementController::class, 'updateCollector'])->name('admin.collectors.update');
            Route::post('/collectors/{id}/toggle', [CollectorManagementController::class, 'toggleCollector'])->name('admin.collectors.toggle');
        });

        // 2. Validation financière
        Route::middleware(['permission:can_validate_contributions'])->group(function () {
            Route::get('/validation', [AdminController::class, 'validation'])->name('admin.validation');
            Route::post('/validation/{id}/approve', [AdminController::class, 'approveContribution'])->name('admin.validation.approve');
            Route::post('/validation/approve-all', [AdminController::class, 'approveAllContributions'])->name('admin.validation.approve-all');
            Route::post('/valider-caisse/{id}', [AdminController::class, 'validerCaisse'])->name('admin.valider.caisse');
        });

        // 3. Gestion Géographique (Localisations)
        Route::middleware(['permission:can_manage_locations'])->group(function () {
            Route::get('/locations', [LocationController::class, 'locations'])->name('admin.locations');
            Route::post('/countries', [LocationController::class, 'storeCountry'])->name('admin.countries.store');
            Route::post('/regions', [LocationController::class, 'storeRegion'])->name('admin.regions.store');
            Route::post('/zones', [LocationController::class, 'storeZone'])->name('admin.zones.store');
        });

        // 4. Gestion des Lots & Tarifs
        Route::middleware(['permission:can_manage_lots'])->group(function () {
            Route::prefix('carnets')->name('admin.carnets.')->group(function () {
                Route::get('/', [CarnetController::class, 'indexCarnets'])->name('index');
                Route::post('/store', [CarnetController::class, 'storeCarnet'])->name('store');
                Route::post('/store-bulk', [CarnetController::class, 'storeBulkCarnet'])->name('store-bulk');
                Route::post('/assign', [CarnetController::class, 'assignCarnets'])->name('assign');
                Route::get('/{id}', [CarnetController::class, 'showCarnet'])->name('show');
            });
            Route::put('/lots/{id}/update-penalty', [CarnetController::class, 'updateLotPenalty'])->name('admin.lots.update-penalty');
        });

        // 5. Gestion des Sauvegardes (Backups)
        Route::middleware(['permission:can_manage_backups'])->group(function () {
            Route::get('/backups', [BackupController::class, 'index'])->name('admin.backups');
            Route::get('/backups/download/{file}', [BackupController::class, 'download'])->name('admin.backups.download')->where('file', '.*');
        });
    });

    // --- HISTORIQUE & LOGS D'ACTIVITÉ (ADMIN, EMPLOYÉ, SECRÉTAIRE, COLLECTEUR) ---
    Route::middleware(['role:admin,employee,secretary,collector'])->prefix('admin')->group(function () {
        Route::middleware(['permission:can_view_audit_logs'])->group(function () {
            Route::get('/historique', [AdminController::class, 'historique'])->name('admin.historique');
            Route::get('/logs', [LogController::class, 'index'])->name('admin.logs');
        });
    });

    // --- EMPLOYÉ COLLABORATEUR ---
    Route::middleware(['role:employee'])->prefix('employee')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'employeeDashboard'])->name('employee.dashboard');
    });

    // --- SECRÉTAIRE ---
    Route::middleware(['role:secretary'])->prefix('secretary')->group(function () {
        Route::get('/dashboard', [SecretaryController::class, 'dashboard'])->name('secretary.dashboard');
        Route::post('/clients', [SecretaryController::class, 'storeClient'])->name('secretary.clients.store');
        Route::post('/clients/{id}/deliver', [SecretaryController::class, 'deliverArticle'])->name('secretary.clients.deliver');
        Route::get('/clients/{client}', [SecretaryController::class, 'show'])->name('secretary.clients.show');
        Route::post('/payments', [SecretaryController::class, 'storePayment'])->name('secretary.payments.store');
    });

    // --- COLLECTEUR ---
    Route::middleware(['role:collector'])->prefix('collector')->group(function () {
        Route::get('/dashboard', [CollectorController::class, 'dashboard'])->name('collector.dashboard');
        Route::post('/clients', [CollectorController::class, 'storeClient'])->name('collector.clients.store');
        Route::post('/clients/{id}/update', [CollectorController::class, 'updateClient'])->name('collector.clients.update');
        Route::post('/contributions', [CollectorController::class, 'storeContribution'])->name('collector.contributions.store');
        Route::get('/closure', [CollectorController::class, 'closure'])->name('collector.closure');
        Route::post('/cloturer-caisse', [CollectorController::class, 'cloturerCaisse'])->name('collector.cloturer-caisse');
    });
});

// Routes pour l'espace Secrétariat
Route::prefix('secretary')->name('secretary.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [SecretaryController::class, 'dashboard'])->name('dashboard');
    Route::get('/clients/{id}', [SecretaryController::class, 'show'])->name('clients.show');
    Route::post('/clients/{id}/deliver', [SecretaryController::class, 'deliverArticle'])->name('clients.deliver');

    // Nouvelles routes pour l'attribution des carnets et la validation des cotisations
    Route::post('/carnets/assign', [SecretaryController::class, 'assignCarnet'])->name('carnets.assign');
    Route::patch('/contributions/{id}/validate', [SecretaryController::class, 'validateContribution'])->name('contributions.validate');
    Route::patch('/closures/{id}/validate', [SecretaryController::class, 'validateClosure'])->name('closures.validate');
});
