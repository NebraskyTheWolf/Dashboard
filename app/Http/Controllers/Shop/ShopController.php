<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Pages;

class ShopController extends Controller {
    
    public function index(Request $request) {
        return [
            'status' => true,
            'message' => 'test'
        ];
    }
}
