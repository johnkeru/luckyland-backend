<?php

use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CottageController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\EmployeeLogsController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OtherController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UnavailableController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\WasteController;
use Illuminate\Support\Facades\Route;


Route::get('hi', function () {
    return response()->json([
        'data' => 'the developer of this api is awesome!',
    ]);
});

Route::get('/visitor', [VisitorController::class, 'show']);
Route::post('/visitor/increment', [VisitorController::class, 'increment']);

Route::get('faqs', [FAQController::class, 'index']);
Route::post('faqs', [FAQController::class, 'question']);
Route::get('settings/faqs', [FAQController::class, 'noAnswersFAQs']);
Route::post('settings/faqs/{faq}/answer', [FAQController::class, 'answer']);
Route::get('categories', [InventoryController::class, 'getCategories']);

Route::prefix('landing')->group(function () {
    Route::get('accommodations', [RoomController::class, 'landingAccommodations']);
    Route::get('rooms', [RoomController::class, 'getLandingPageRooms']);
    Route::get('cottages', [CottageController::class, 'getLandingPageCottages']);
    Route::get('others', [OtherController::class, 'getLandingPageOthers']);
    // to test and remove
    Route::get('/', [RoomController::class, 'getAllRoomTypes']);
});

// customer
Route::prefix('reservations')->group(function () {
    Route::post('unavailable-dates-by-rooms', [ReservationController::class, 'getUnavailableDatesByRooms']);
    Route::post('unavailable-dates-by-cottages', [ReservationController::class, 'getUnavailableDatesByCottages']);
    Route::post('unavailable-dates-by-others', [ReservationController::class, 'getUnavailableDatesByOthers']);
    Route::post('unavailable-dates-by-rooms-and-cottages', [ReservationController::class, 'getUnavailableDatesByRoomsAndCottages']);

    Route::post('available-rooms', [ReservationController::class, 'getAvailableRooms']);
    Route::post('available-cottages', [ReservationController::class, 'getAvailableCottages']);
    Route::post('available-others', [ReservationController::class, 'getAvailableOthers']);
    Route::post('available-suggestions', [ReservationController::class, 'suggestions']);

    Route::post('create-reservation', [ReservationController::class, 'customerCreateReservation']);
    Route::put('reschedule', [ReservationController::class, 'reschedule']);
});

Route::get('findItem', [InventoryController::class, 'findItem']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user', [UserController::class, 'user']); //after login this will get called.
    Route::post('logout', [UserController::class, 'logout']);
    Route::put('changePassword/{id}', [UserController::class, 'changePassword']);

    Route::prefix('employees')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::patch('update-image', [UserController::class, 'updateImage']);
        Route::post('add-employee', [UserController::class, 'addEmployee']);
        Route::post('add-regular-employee', [UserController::class, 'addRegularEmployee']);
        Route::patch('update-employee/{id}', [UserController::class, 'updateEmployee']);
        Route::delete('delete-employee/{id}', [UserController::class, 'softDeleteOrRestoreEmployee']);
        Route::get('employee-logs/{employeeId}', [EmployeeLogsController::class, 'employeeLogs']);
        Route::get('logs-visited/{employeeId}', [EmployeeLogsController::class, 'logsVisited']);
    });

    Route::prefix('logs')->group(function () {
        Route::get('employee', [EmployeeLogsController::class, 'unReadLogs']);
    });

    // Middleware for routes that require ADMIN, INVENTORY, and FRONT DESK roles
    Route::middleware(['all_roles'])->group(function () {
        Route::get('inventories', [InventoryController::class, 'index']);
        Route::get('returned-items/{id}', [InventoryController::class, 'returnedItems']);
        Route::get('roles', [UserController::class, 'getRoles']);
        Route::get('deliveries', [DeliveryController::class, 'index']);
        Route::get('customers', [CustomerController::class, 'index']);
        Route::get('unavailables', [UnavailableController::class, 'index']);
        Route::get('wastes', [WasteController::class, 'index']);
        Route::get('reservations', [ReservationController::class, 'index']);
        Route::get('reservations/rooms-cottages-options', [ReservationController::class, 'getOptionsForRoomsAndCottages']);
        Route::get('rooms', [RoomController::class, 'getAllRooms']);
        Route::get('rooms/types', [RoomController::class, 'getAllRoomTypes']);
        Route::get('cottages', [CottageController::class, 'getAllCottages']);
        Route::get('cottages/types', [CottageController::class, 'getAllCottageTypes']);
        Route::get('others', [OtherController::class, 'getAllOthers']);
        Route::get('others/types', [OtherController::class, 'getAllOtherTypes']);


        Route::get('dashboard/bar-graph', [DashboardController::class, 'barMonthlyReservation']);
        Route::get('dashboard/pie-graph', [DashboardController::class, 'pieReservation']);
        Route::get('dashboard/line-graph', [DashboardController::class, 'lineReservation']);
        Route::get('dashboard/today-overview', [DashboardController::class, 'todayOverview']);
        Route::get('dashboard/month-overview', [DashboardController::class, 'monthOverview']);

        Route::get('dashboard/inventory-summary', [DashboardController::class, 'inventorySummary']);
        Route::get('dashboard/room-stock-level', [DashboardController::class, 'roomStockLevel']);
        Route::get('dashboard/pie-inventory', [DashboardController::class, 'pieInventory']);
    });

    Route::middleware('inventory')->group(function () {
        Route::prefix('inventories')->group(function () {
            Route::post('add-item', [InventoryController::class, 'addItem']);
            Route::post('update-item/{item}', [InventoryController::class, 'updateItem']);
            Route::patch('inline-update-item/{item}', [InventoryController::class, 'inlineUpdateItem']);
            Route::delete('delete-item/{id}', [InventoryController::class, 'softDeleteOrRestoreItem']);
        });
        Route::prefix('deliveries')->group(function () {
            Route::post('addDelivery', [DeliveryController::class, 'addDelivery']);
        });
        Route::prefix('wastes')->group(function () {
            Route::post('add-waste', [WasteController::class, 'addWaste']);
            Route::put('update-waste/{waste}', [WasteController::class, 'updateWaste']);
        });
        Route::prefix('unavailable')->group(function () {
            Route::post('add-unavailable', [UnavailableController::class, 'addUnavailable']);
            Route::post('unavailable-to-waste/{id}', [UnavailableController::class, 'unavailableToWaste']);
            Route::post('unavailable-to-inventory/{id}', [UnavailableController::class, 'unavailableToInventory']);
            Route::put('update-unavailable/{unavailable}', [UnavailableController::class, 'editUnavailable']);
            Route::patch('unavailable-inline-update/{unavailable}', [UnavailableController::class, 'inlineUpdate']);
        });
    });

    Route::middleware('frontDesk')->group(function () {
        Route::prefix('inventories')->group(function () {
            Route::get('borrowers/{id}', [CustomerController::class, 'getCustomerWhoBorrows']);
            Route::post('borrow/{item}/{customer}', [InventoryController::class, 'customerBorrow']);
            Route::patch('return-all', [InventoryController::class, 'customerReturnAllBorrowedItems']);
            Route::patch('return-partially', [InventoryController::class, 'customerPartiallyReturnItems']);
        });
        Route::prefix('reservations')->group(function () {
            Route::patch('update-status/{reservation}', [ReservationController::class, 'updateReservationStatus']);
            Route::post('cancel-reservation/{reservation}', [ReservationController::class, 'cancelReservation']);
        });
    });

    Route::middleware('houseKeeping')->group(function () {
        Route::prefix('rooms')->group(function () {
            Route::post('add-room', [RoomController::class, 'addRoom']);
            Route::put('update-room/{room}', [RoomController::class, 'updateRoom']);
            // Route::post('add-room-type', [RoomController::class, 'addRoomsByType']);
            Route::put('update-rooms-by-type', [RoomController::class, 'updateRoomsByType']);
        });
        Route::prefix('cottages')->group(function () {
            Route::post('add-cottage', [CottageController::class, 'addCottage']);
            Route::put('update-cottage/{cottage}', [CottageController::class, 'updateCottage']);
            // Route::post('add-cottage-type', [CottageController::class, 'addCottagesByType']);
            Route::put('update-cottages-by-type', [CottageController::class, 'updateCottagesByType']);
        });
        Route::prefix('others')->group(function () {
            Route::post('add-other', [OtherController::class, 'addOther']);
            Route::put('update-other/{other}', [OtherController::class, 'updateOther']);
            Route::put('update-others-by-type', [OtherController::class, 'updateOthersByType']);
        });
    });

    Route::middleware('admin')->group(function () {
        Route::get('customer-records', [CustomerController::class, 'customerRecords']);
        Route::prefix('backups')->group(function () {
            Route::get('/', [BackupController::class, 'index']);
            Route::get('create-backup', [BackupController::class, 'backup']);
            Route::get('download/{backup}', [BackupController::class, 'download']);
            // Route::post('restore', [BackupController::class, 'restore']);
        });
    });
});
