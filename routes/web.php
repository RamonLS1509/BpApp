<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

// La ruta raíz '/' ahora llama al mismo controlador
Route::get('/', [ApiController::class, 'fetchAndDisplayData']);

