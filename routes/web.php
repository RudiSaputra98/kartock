<?php



use App\Exports\ExportExcell;
use App\Http\Middleware\IsLogin;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\NoteController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MesinController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TrMasukController;
use App\Http\Controllers\Admin\TrPakaiController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\IsiPerballController;
use App\Http\Controllers\Admin\ReportStokController;
use App\Http\Controllers\Admin\ReportCategoryController;

Route::get('/login', [AuthController::class, 'loginView'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Route::get('/storage-link', function () {
//     $targetFolder = storage_path('app/public');
//     $linkFolder = '/DATA/thomas/kartock.swakaolin.co.id/storage';

//     if (!file_exists($linkFolder)) {
//         symlink($targetFolder, $linkFolder);
//         return "Storage link created for subdomain!";
//     } else {
//         return "Storage link already exists!";
//     }
// });

Route::middleware(IsLogin::class)->group(function () {

    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/create', [ProductController::class, 'create']);
    Route::get('/products/edit/{id}', [ProductController::class, 'edit']);
    Route::post('/products/store', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'delete']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/create', [CategoryController::class, 'create']);
    Route::get('/categories/edit/{id}', [CategoryController::class, 'edit']);
    Route::post('/categories/store', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'delete']);

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
    Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'delete'])->name('users.delete');

    Route::get('/mesin', [MesinController::class, 'index']);
    Route::get('/mesin/create', [MesinController::class, 'create']);
    Route::get('/mesin/edit/{id}', [MesinController::class, 'edit']);
    Route::post('/mesin/store', [MesinController::class, 'store']);
    Route::put('/mesin/{id}', [MesinController::class, 'update']);
    Route::delete('/mesin/{id}', [MesinController::class, 'delete']);

    Route::get('/isi_perball', [IsiPerballController::class, 'index']);
    Route::get('/isi_perball/create', [IsiPerballController::class, 'create']);
    Route::get('/isi_perball/edit/{id}', [IsiPerballController::class, 'edit']);
    Route::post('/isi_perball/store', [IsiPerballController::class, 'store']);
    Route::put('/isi_perball/{id}', [IsiPerballController::class, 'update']);
    Route::delete('/isi_perball/{id}', [IsiPerballController::class, 'delete']);

    Route::get('/tr_masuk', [TrMasukController::class, 'index'])->name('tr_masuk.index');
    Route::get('/tr_masuk/create', [TrMasukController::class, 'create'])->name('tr_masuk.create');
    Route::get('/tr_masuk/edit/{id}', [TrMasukController::class, 'edit']);
    Route::post('/tr_masuk/store', [TrMasukController::class, 'store']);
    Route::put('/tr_masuk/{id}', [TrMasukController::class, 'update']);
    Route::delete('/tr_masuk/{id}', [TrMasukController::class, 'delete']);

    // Route untuk generate
    Route::get('/tr_masuk/pdf', [TrMasukController::class, 'pdf'])->name('pdf.tr_masuk');
    Route::get('/tr_masuk/excell', [TrMasukController::class, 'exportToExcel'])->name('excell.tr_masuk');



    Route::get('tr_pakai', [TrPakaiController::class, 'index'])->name('tr_pakai.index');
    Route::get('/tr_pakai/create', [TrPakaiController::class, 'create'])->name('tr_pakai.create');
    Route::get('tr_pakai/edit/{id}', [TrPakaiController::class, 'edit']);
    Route::post('tr_pakai/store', [TrPakaiController::class, 'store']);
    Route::put('tr_pakai/{id}', [TrPakaiController::class, 'update']);
    Route::delete('tr_pakai/{id}', [TrPakaiController::class, 'delete']);
    Route::get('/generate-pdf', [TrPakaiController::class, 'pdf'])->name('pdf');
    Route::get('export-excel', [TrPakaiController::class, 'exportToExcel'])->name('export.excel');



    Route::get('/report/pdf', [ReportController::class, 'pdf'])->name('pdf.report');
    Route::get('/report/excell', [ReportController::class, 'exportToExcel'])->name('excell.report');
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('report.index');
    });


    Route::get('/report-category/pdf', [ReportCategoryController::class, 'pdf'])->name('pdf.report.category');
    Route::get('/report-category/excell', [ReportCategoryController::class, 'exportToExcel'])->name('excell.report.category');
    Route::get('report-category', [ReportCategoryController::class, 'index'])->name('report.category.index');

    Route::get('report_stok', [ReportStokController::class, 'index'])->name('report.stok.index');
    Route::get('/report_stok/pdf', [ReportStokController::class, 'pdf'])->name('pdf.report.stok');
    Route::get('/report_stok/excell', [ReportStokController::class, 'exportToExcel'])->name('excell.report.stok');

    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
});
