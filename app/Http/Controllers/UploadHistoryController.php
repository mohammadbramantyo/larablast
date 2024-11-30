<?php

namespace App\Http\Controllers;

use App\Models\UploadHistory;
use Illuminate\Http\Request;

class UploadHistoryController extends Controller
{
    //
    public function show(Request $request)
    {
        $history = UploadHistory::paginate(15);

        return view('pages.history', ['histories'=>$history]);

    }
}
