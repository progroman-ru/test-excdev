<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $balance = Auth::user()->balance;

        $data['operations'] = Operation::where('balance_id', $balance->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('History', $data);
    }

}
