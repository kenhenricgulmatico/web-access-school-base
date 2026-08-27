<?php

use Illuminate\Support\Facades\Route;

// Authentication routes
Route::livewire('/', 'auth::login')->name('login');
Route::livewire('/register', 'auth::register')->name('register');
Route::livewire('/waiting', 'auth::waiting')->name('waiting');


Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::livewire('/dashboard', 'pages::admin.dashboard')->name('admin.dashboard');
    Route::livewire('/facility', 'pages::admin.reservation-facility')->name('admin.facility');
    Route::livewire('/material', 'pages::admin.reservation-material')->name('admin.material');
    Route::livewire('/students', 'pages::admin.student')->name('admin.students');
    Route::livewire('/audit-logs', 'pages::admin.audit-logs')->name('admin.audit-logs');

    //user
    Route::livewire('/users', 'pages::admin.user.view-user')->name('admin.users');
    Route::livewire('/users/create', 'pages::admin.user.create-user')->name('admin.user.create');
    Route::livewire('/users/{id}/edit', 'pages::admin.user.edit-user')->name('admin.user.edit');

    //role
    Route::livewire('/roles', 'pages::admin.role.view-role')->name('admin.roles');
    Route::livewire('/roles/create', 'pages::admin.role.create-role')->name('admin.roles.create');
    Route::livewire('/roles/{id}/edit', 'pages::admin.role.edit-role')->name('admin.roles.edit');

    //department
    Route::livewire('/departments', 'pages::admin.deparments.view-department')->name('admin.departments');
    Route::livewire('/departments/create', 'pages::admin.deparments.create-department')->name('admin.departments.create');
    Route::livewire('/departments/{id}/edit', 'pages::admin.deparments.edit-department')->name('admin.departments.edit');

    //manage coordinator
    Route::livewire('/manage-coordinator', 'pages::admin.manage-coordinator')->name('admin.manage-coordinator');

    //schedule
    Route::livewire('/schedule', 'pages::admin.schedule.view-schedule')->name('admin.schedule');
    Route::livewire('/schedule/create', 'pages::admin.schedule.create-schedule')->name('admin.schedule.create');
    Route::livewire('/schedule/{schedule}/edit', 'pages::admin.schedule.edit-schedule')->name('admin.schedule.edit');

    //Calendar
    Route::livewire('/calendar', 'pages::admin.schedule.calendar.calendar-schedule')->name('admin.calendar');


    //Inventory
    Route::livewire('/inventory-stock-material', 'pages::admin.inventory.stock-material')->name('admin.inventory-stock-material');
    Route::livewire('/inventory-stock-history', 'pages::admin.inventory.stock-history')->name('admin.inventory-stock-history');
});


Route::middleware(['auth', 'program head', 'department'])->prefix('programHead')->group(function () {
    Route::livewire('/dashboard', 'pages::coordinator.dashboard')->name('coordinator.dashboard');

    Route::livewire('/facility', 'pages::coordinator.reservation-facility')->name('coordinator.facility');
    Route::livewire('/material', 'pages::coordinator.request-material')->name('coordinator.material');

    //Notifications
    Route::livewire('/notifications', 'pages::coordinator.notification')->name('coordinator.notification');

    //Resource allocation
    Route::livewire('/resource-allocation', 'pages::coordinator.resource-allocation')->name('coordinator.resource-allocation');

    //Request to admin
    Route::livewire('/request-to-admin/view-request', 'pages::coordinator.request-to-admin.view-request')->name('coordinator.request-to-admin.view-request');
    Route::livewire('/request-to-admin/create', 'pages::coordinator.request-to-admin.create-request')->name('coordinator.request-to-admin.create-request');
    Route::livewire('/request-to-admin/{id}/edit', 'pages::coordinator.request-to-admin.edit-request')->name('coordinator.request-to-admin.edit-request');

    //audit
    Route::livewire('/audit', 'pages::coordinator.audit.audit-log')->name('coordinator.audit');

    //student|faculty
    Route::livewire('/view-student', 'pages::coordinator.student.view-student')->name('programHead.students');
});

Route::middleware(['auth', 'role:faculty|student', 'department'])->prefix('portal')->group(function () {
    Route::livewire('/dashboard', 'pages::student-faculty.dashbaord')->name('portal.dashboard');
    //Create reservation
    Route::livewire('/reservation', 'pages::student-faculty.reservation.view-reservation')->name('portal.reservation');
    Route::livewire('/reservation/create', 'pages::student-faculty.reservation.create-reservation')->name('portal.create-reservation');
    Route::livewire('/reservation/{id}/edit', 'pages::student-faculty.reservation.edit-reservation')->name('portal.edit-reservation');

    Route::livewire('/history-reservation', 'pages::student-faculty.history-reservation')->name('portal.history-reservation');

    //Create material request
    Route::livewire('/material', 'pages::student-faculty.material.view-material')->name('portal.material');
    Route::livewire('/material/create', 'pages::student-faculty.material.create-material')->name('portal.create-material');
    Route::livewire('/material/{id}/edit', 'pages::student-faculty.material.edit-material')->name('portal.edit-material');

    //Profile
    Route::livewire('/profile', 'pages::student-faculty.profile')->name('portal.profile');
});
