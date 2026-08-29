<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CreateBarCodeController;
use App\Http\Controllers\CentroCostoController;
use App\Http\Controllers\DigitalTicketController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\FinalizeTicketController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\PdfGeneratorController;
use App\Http\Controllers\RecursoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehiculoController;
use Src\Modules\Recurso\Infrastructure\Http\Controllers\DeleteRecursoController;
use Src\Modules\Recurso\Infrastructure\Http\Controllers\StoreRecursoController;
use Illuminate\Support\Facades\Route;
use Src\Modules\Lote\Infrastructure\Http\Controllers\DeleteLoteController as DeleteLoteDDDController;
use Src\Modules\Lote\Infrastructure\Http\Controllers\GenerateLoteController;
use Src\Modules\Lote\Infrastructure\Http\Controllers\StoreLoteController;
use Src\Modules\Lote\Infrastructure\Http\Controllers\UpdateLoteController;
use Src\Modules\Tickets\Infrastructure\Http\Controllers\ConsumeTicketController;
use Src\Modules\Tickets\Infrastructure\Http\Controllers\DeleteTicketController;
use Src\Modules\Tickets\Infrastructure\Http\Controllers\StoreTicketController;
use Src\Modules\Tickets\Infrastructure\Http\Controllers\UpdateTicketController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/test', CreateBarCodeController::class);

Route::prefix('vehiculos')->middleware('auth')->group(function () {
    Route::get('/', [VehiculoController::class, 'index'])->middleware('permission:puede_ver_vehiculo')->name('vehiculos.index');
    Route::get('/create', [VehiculoController::class, 'create'])->middleware('permission:puede_crear_vehiculo')->name('vehiculos.create');
    Route::post('/', [VehiculoController::class, 'store'])->middleware('permission:puede_crear_vehiculo')->name('vehiculos.store');
    Route::get('/{vehiculo}/edit', [VehiculoController::class, 'edit'])->middleware('permission:puede_editar_vehiculo')->name('vehiculos.edit');
    Route::match(['put', 'patch'], '/{vehiculo}', [VehiculoController::class, 'update'])->middleware('permission:puede_editar_vehiculo')->name('vehiculos.update');
    Route::delete('/{vehiculo}', [VehiculoController::class, 'destroy'])->middleware('permission:puede_borrar_vehiculo')->name('vehiculos.destroy');
});

Route::prefix('personal')->middleware('auth')->group(function () {
    Route::get('/', [PersonalController::class, 'index'])->middleware('permission:puede_ver_personal')->name('personal.index');
    Route::get('/create', [PersonalController::class, 'create'])->middleware('permission:puede_crear_personal')->name('personal.create');
    Route::post('/', [PersonalController::class, 'store'])->middleware('permission:puede_crear_personal')->name('personal.store');
    Route::get('/{personal}/edit', [PersonalController::class, 'edit'])->middleware('permission:puede_editar_personal')->name('personal.edit');
    Route::match(['put', 'patch'], '/{personal}', [PersonalController::class, 'update'])->middleware('permission:puede_editar_personal')->name('personal.update');
    Route::delete('/{personal}', [PersonalController::class, 'destroy'])->middleware('permission:puede_borrar_personal')->name('personal.destroy');
});

Route::prefix('centro_costos')->middleware('auth')->group(function () {
    Route::get('/', [CentroCostoController::class, 'index'])->middleware('permission:puede_ver_centro_costo')->name('centro_costos.index');
    Route::get('/create', [CentroCostoController::class, 'create'])->middleware('permission:puede_crear_centro_costo')->name('centro_costos.create');
    Route::post('/', [CentroCostoController::class, 'store'])->middleware('permission:puede_crear_centro_costo')->name('centro_costos.store');
    Route::get('/{centro_costo}/edit', [CentroCostoController::class, 'edit'])->middleware('permission:puede_editar_centro_costo')->name('centro_costos.edit');
    Route::match(['put', 'patch'], '/{centro_costo}', [CentroCostoController::class, 'update'])->middleware('permission:puede_editar_centro_costo')->name('centro_costos.update');
    Route::delete('/{centro_costo}', [CentroCostoController::class, 'destroy'])->middleware('permission:puede_borrar_centro_costo')->name('centro_costos.destroy');
});

Route::prefix('tickets')->middleware('auth')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->middleware('permission:puede_ver_ticket')->name('tickets.index');
    Route::get('/gestion/{id?}', [TicketController::class, 'showManageTicket'])->middleware('permission:puede_ver_ticket')->name('tickets.showManageTicket');
    Route::get('/create', [TicketController::class, 'create'])->middleware('permission:puede_crear_ticket')->name('tickets.create');
    Route::post('/', StoreTicketController::class)->middleware('permission:puede_crear_ticket')->name('tickets.store');
    Route::post('/consumption/{ticket}', ConsumeTicketController::class)->middleware('permission:puede_crear_ticket')->name('tickets.manageTicketConsumption');
    Route::get('/{ticket}/edit', [TicketController::class, 'edit'])->middleware('permission:puede_editar_ticket')->name('tickets.edit');
    Route::match(['put', 'patch'], '/{ticket}', UpdateTicketController::class)->middleware('permission:puede_editar_ticket')->name('tickets.update');
    Route::delete('/{ticket}', DeleteTicketController::class)->middleware('permission:puede_borrar_ticket')->name('tickets.destroy');
});

Route::prefix('recursos')->middleware('auth')->group(function () {
    Route::get('/', [RecursoController::class, 'index'])->middleware('permission:puede_ver_recurso')->name('recursos.index');
    Route::get('/create', [RecursoController::class, 'create'])->middleware('permission:puede_crear_recurso')->name('recursos.create');
    Route::post('/', StoreRecursoController::class)->middleware('permission:puede_crear_recurso')->name('recursos.store');
    Route::get('/{recurso}/edit', [RecursoController::class, 'edit'])->middleware('permission:puede_editar_recurso')->name('recursos.edit');
    Route::match(['put', 'patch'], '/{recurso}', [RecursoController::class, 'update'])->middleware('permission:puede_editar_recurso')->name('recursos.update');
    Route::delete('/{recurso}', DeleteRecursoController::class)->middleware('permission:puede_borrar_recurso')->name('recursos.destroy');
});

Route::prefix('pdf')->middleware('auth')->group(function () {
    Route::get('/{id?}', PdfGeneratorController::class)->name('pdf.generate');
});

Route::prefix('digitalTicket')->middleware('auth')->group(function () {
    Route::get('/{id?}', DigitalTicketController::class)->name('pdf.digital');
});

Route::prefix('reporte')->middleware(['auth', 'permission:puede_ver_informes'])->group(function () {
    Route::get('/', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/consumidos', [ReporteController::class, 'downloadReport'])->name('reportes.download');
    Route::get('/emitidos', [ReporteController::class, 'downloadEmitidos'])->name('reportes.emitidos');
});


Route::prefix('usuarios')
    ->middleware(['auth', 'permission:puede_ver_roles_y_permisos'])
    ->group(function () {
        Route::get('/gestion', [UserController::class, 'index'])->name('usuarios.gestion');
        Route::post('/asignacion', [UserController::class, 'asignarRolesPermisos'])->name('usuarios.asignacion');
        Route::post('/remover', [UserController::class, 'removerRolesPermisos'])->name('usuarios.remover');
        Route::post('/sincronizar', [UserController::class, 'syncRolesPermisos'])->name('usuarios.sincronizar');
        Route::get('/{user}/roles-permisos', [UserController::class, 'getUserRolesPermisos'])->name('usuarios.roles-permisos');
    });

    Route::prefix('roles')->middleware(['auth', 'permission:puede_ver_roles_y_permisos'])->group(function () {
        Route::get('/', [RolesController::class, 'index'])->name('roles.index');
        Route::post('/', [RolesController::class, 'store'])->name('roles.store');
        Route::put('/{role}', [RolesController::class, 'update'])->name('roles.update');
        Route::delete('/{role}', [RolesController::class, 'destroy'])->name('roles.destroy');
    });

Route::prefix('auditoria')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [ActivityLogController::class, 'index'])->name('auditoria.index');
    Route::get('/csv', [ActivityLogController::class, 'exportCsv'])->name('auditoria.exportCsv');
    Route::get('/recursos/{recursoId}/timeline', [ActivityLogController::class, 'recursoTimeline'])->name('auditoria.recursos.timeline');
    Route::get('/recursos/{recursoId}/timeline/pdf', [ActivityLogController::class, 'recursoTimelinePdf'])->name('auditoria.recursos.timeline.pdf');
    Route::get('/tickets/{ticketId}/timeline', [ActivityLogController::class, 'ticketTimeline'])->name('auditoria.tickets.timeline');
    Route::get('/tickets/{ticketId}/timeline/pdf', [ActivityLogController::class, 'ticketTimelinePdf'])->name('auditoria.tickets.timeline.pdf');
});

Route::prefix('lotes')->middleware('auth')->group(function () {
    Route::get('/', [LoteController::class, 'index'])->middleware('permission:puede_ver_lote')->name('lotes.index');
    Route::get('/create', [LoteController::class, 'create'])->middleware('permission:puede_crear_lote')->name('lotes.create');
    Route::post('/', StoreLoteController::class)->middleware('permission:puede_crear_lote')->name('lotes.store');
    Route::get('/{lote}/edit', [LoteController::class, 'edit'])->middleware('permission:puede_editar_lote')->name('lotes.edit');
    Route::match(['put', 'patch'], '/{lote}', UpdateLoteController::class)->middleware('permission:puede_editar_lote')->name('lotes.update');
    Route::delete('/{lote}', DeleteLoteDDDController::class)->middleware('permission:puede_borrar_lote')->name('lotes.destroy');
    Route::post('/{lote}/generar', GenerateLoteController::class)->middleware('permission:puede_generar_lote')->name('lotes.generar');
    Route::get('/{lote}/imprimir', [LoteController::class, 'print'])->middleware('permission:puede_imprimir_lote')->name('lotes.imprimir');
    Route::get('/{lote}/pdf', [LoteController::class, 'downloadPdf'])->middleware('permission:puede_imprimir_lote')->name('lotes.pdf');
});

Route::prefix('api/lotes')->middleware('auth')->group(function () {
    Route::get('/empleados-por-centro/{centroCosto}', [LoteController::class, 'getEmpleadosByCentroCosto']);
});
