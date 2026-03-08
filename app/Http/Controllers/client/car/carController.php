<?php

namespace App\Http\Controllers\client\car;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class carController extends Controller
{
    public function index(){
        $cars = Car::all();
        return view('client.car.car', compact('cars'));
    }

    public function show($id){
        $car = Car::findOrFail($id);
        return response()->json([
        'cars'=> $car,
        'message' => 'Successfully Retrieved'
        ],200

        );
    }


    }

