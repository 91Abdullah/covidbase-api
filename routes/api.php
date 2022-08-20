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
Route::get('/disease-lncRNA', [PageViewController::class, 'getAllDiseaseLncRNAPairs']);
Route::get('/genes', [PageViewController::class, 'getAllGeneNames']);
Route::get('/pdbs', [PageViewController::class, 'getAllPDBNames']);
Route::get('/miRNAs', [PageViewController::class, 'getAllMirnaNames']);
Route::get('/lncRNAs', [PageViewController::class, 'getAllLncrnaNames']);
Route::get('/alternateMedicine', [PageViewController::class, 'getAllAlternateMedicines']);

// Get options

Route::get('/getOptions', [PageViewController::class, 'getOptions']);

Route::post('/drugSearch', [KBController::class, 'getDrugSearch']);
Route::post('/alternateMedicineSearch', [KBController::class, 'getAlternateMedicineSearch']);
Route::post('/diseaseSearch', [KBController::class, 'getDiseaseSearch']);
Route::post('/geneSearch', [KBController::class, 'getGeneSearch']);
Route::post('/miRNASearch', [KBController::class, 'getMiRNASearch']);
Route::post('/lncRNASearch', [KBController::class, 'getLncRNASearch']);
Route::post('/pdbSearch', [KBController::class, 'getPDBSearch']);
Route::post('/drugDiseaseSearch', [KBController::class, 'getDrugDiseaseSearch']);
Route::post('/drugPDBSearch', [KBController::class, 'getDrugPDBSearch']);
Route::post('/diseaseGeneSearch', [KBController::class, 'getDiseaseGeneSearch']);
Route::post('/diseaseMiRNASearch', [KBController::class, 'getDiseaseMiRNASearch']);
Route::post('/diseaseLncRNASearch', [KBController::class, 'getDiseaseLncRNASearch']);

Route::get('/getTopDrugSearch', [KBController::class, 'getTopDrugSearch']);
Route::get('/getTopAlternateMedicineSearch', [KBController::class, 'getTopAlternateMedicineSearch']);
Route::get('/getTopDiseaseSearch', [KBController::class, 'getTopDiseaseSearch']);
Route::get('/getTopGeneSearch', [KBController::class, 'getTopGeneSearch']);
Route::get('/getTopPDBSearch', [KBController::class, 'getTopPDBSearch']);
Route::get('/getTopMiRNASearch', [KBController::class, 'getTopMiRNASearch']);
Route::get('/getTopLncRNASearch', [KBController::class, 'getTopLncRNASearch']);

Route::get('/getTopDrugDiseaseSearch', [KBController::class, 'getTopDrugDiseaseSearch']);
Route::get('/getTopDrugPDBSearch', [KBController::class, 'getTopDrugPDBSearch']);
Route::get('/getTopDiseaseGeneSearch', [KBController::class, 'getTopDiseaseGeneSearch']);
Route::get('/getTopDiseaseMiRNASearch', [KBController::class, 'getTopDiseaseMiRNASearch']);
Route::get('/getTopDiseaseLncRNASearch', [KBController::class, 'getTopDiseaseLncRNASearch']);

Route::post('/logPageView', [PageViewController::class, 'logPageView']);
Route::get('/getStats', [SentimentController::class, 'getStats']);
Route::get('/getSearch', [SentimentController::class, 'getSearch']);
Route::get('/getAutoComplete', [SentimentController::class, 'getAutoCompleteData']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
