<?php

namespace App\Http\Controllers\client\myBooking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class myBookingController extends Controller
{
    public function index(){
        return view('client.booking.booking');
    }
}
