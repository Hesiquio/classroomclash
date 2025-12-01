<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::redirect('/home', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/archived', [DashboardController::class, 'archived'])->name('dashboard.archived');
    Route::post('/dashboard/challenge/create', [DashboardController::class, 'createChallenge'])->name('challenge.create');
    Route::post('/dashboard/challenge/join', [DashboardController::class, 'joinChallenge'])->name('challenge.join');

    Route::get('/challenge/{challenge}', [ChallengeController::class, 'show'])->name('challenge.show');
    Route::get('/challenge/{challenge}/data', [ChallengeController::class, 'getData'])->name('challenge.data');
    Route::post('/challenge/{challenge}/start', [ChallengeController::class, 'startTimer'])->name('challenge.start');
    Route::post('/challenge/{challenge}/pause', [ChallengeController::class, 'pauseTimer'])->name('challenge.pause');
    Route::post('/challenge/{challenge}/submit', [ChallengeController::class, 'submit'])->name('challenge.submit');
    
    Route::post('/challenge/{challenge}/participant/{participant}/add-point', [ChallengeController::class, 'addPoint'])->name('challenge.addPoint');
    Route::post('/challenge/{challenge}/participant/{participant}/score', [ChallengeController::class, 'updateScore'])->name('challenge.updateScore');
    Route::post('/challenge/{challenge}/participant/{participant}/validate', [ChallengeController::class, 'validateSubmission'])->name('challenge.validate');
    
    Route::put('/challenge/{challenge}/update', [ChallengeController::class, 'update'])->name('challenge.update');
    Route::delete('/challenge/{challenge}', [ChallengeController::class, 'destroy'])->name('challenge.destroy');
    Route::post('/challenge/{challenge}/roulette', [ChallengeController::class, 'roulette'])->name('challenge.roulette');
    
    // Team routes
    Route::post('/challenge/{challenge}/teams/create', [ChallengeController::class, 'createTeams'])->name('challenge.teams.create');
    Route::delete('/challenge/{challenge}/teams', [ChallengeController::class, 'deleteTeams'])->name('challenge.teams.delete');
    
    Route::post('/challenge/{challenge}/duplicate', [ChallengeController::class, 'duplicate'])->name('challenge.duplicate');
    
    Route::post('/challenge/{challenge}/add-student', [ChallengeController::class, 'addStudent'])->name('challenge.addStudent');
    Route::delete('/challenge/{challenge}/participant/{participant}/delete', [ChallengeController::class, 'removeParticipant'])->name('challenge.participant.delete');
    Route::post('/challenge/{challenge}/finalize', [ChallengeController::class, 'finalize'])->name('challenge.finalize');
    
    Route::post('/challenge/{challenge}/resume', [ChallengeController::class, 'resume'])->name('challenge.resume');
    Route::post('/challenge/{challenge}/archive', [ChallengeController::class, 'archive'])->name('challenge.archive');
});
