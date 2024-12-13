<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    //

    public function subscribe_user($user_id)
    {
        $user = User::findOrFail($user_id);

        // Change subscription status
        $user->is_subscribed = true;
        $user->subscribe_at = now();
        $user->subscribe_expire_at = now()->addYear();

        $user->save();

        // Create user table
        $this->cretae_user_table($user_id);

        return redirect('/admin/users');
    }

    public function unsubscribe_user($user_id)
    {
        $user = User::findOrFail($user_id);

        // Change subscription status
        $user->is_subscribed = true;
        $user->subscribe_at = now();
        $user->subscribe_expire_at = now()->addYear();
        $user->save();

        return redirect('/admin/users');
    }

    private function cretae_user_table($user_id)
    {
        $tablename = $user_id . '_master_data';

        DB::statement('CREATE TABLE ' . $tablename . ' LIKE master_data');
    }
}
