<?php

use App\Http\Controllers\KBController;
use App\Http\Controllers\PageViewController;
use App\Http\Controllers\SentimentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/getKBStats', [PageViewController::class, 'getKBStats']);
Route::get('/drugs', [PageViewController::class, 'getAllDrugNames']);
Route::get('/diseases', [PageViewController::class, 'getAllDiseaseNames']);
Route::get('/drug-disease', [PageViewController::class, 'getAllDrugDiseasePairs']);
Route::get('/drug-pdb', [PageViewController::class, 'getAllDrugPDBPairs']);
Route::get('/disease-gene', [PageViewController::class, 'getAllDiseaseGenePairs']);
Route::get('/disease-miRNA', [PageViewController::class, 'getAllDiseaseMiRNAPairs']);
Route::get('/genes', [PageViewController::class, 'getAllGeneNames']);
Route::get('/pdbs', [PageViewController::class, 'getAllPDBNames']);
Route::get('/miRNAs', [PageViewController::class, 'getAllMirnaNames']);

Route::post('/drugSearch', [KBController::class, 'getDrugSearch']);

Route::post('/logPageView', [PageViewController::class, 'logPageView']);
Route::get('/getStats', [SentimentController::class, 'getStats']);
Route::get('/getSearch', [SentimentController::class, 'getSearch']);
Route::get('/getAutoComplete', [SentimentController::class, 'getAutoCompleteData']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
