<?php

namespace App\Http\Controllers;

use App\Models\MasterData;
use App\Models\UploadHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;

class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $minAge = $request->input('min_age');
        $maxAge = $request->input('max_age');
        $selectedKotarmh = $request->input('kota_rmh');
        $selectedJabatan = $request->input('jabatan');
        $selectedKotaperush = $request->input('kota_perush');

        $nama = $request->input('name');
        $hp_utama = $request->input('phone');

        // Get user specific table
        $current_user_id = Auth::user()->id;
        $tablename = $this->get_user_table($current_user_id);

        $query = DB::table($tablename);

        // Fetch distinct `kota_rmh` values for the dropdown
        $kota_rmh_options = Cache::rememberForever($current_user_id . '_city_home_options', function () use ($tablename) {
            return DB::table($tablename)
                ->select('kota_rmh')
                ->distinct()
                ->whereNotNull('kota_rmh')
                ->pluck('kota_rmh');
        });

        $kota_perush_options = Cache::rememberForever($current_user_id . '_city_work_options', function () use ($tablename) {
            return DB::table($tablename)
                ->select('kota_perush')
                ->distinct()
                ->whereNotNull('kota_perush')
                ->pluck('kota_perush');
        });

        $jabatan_options = Cache::rememberForever($current_user_id . '_jabatan_options', function () use ($tablename) {
            return DB::table($tablename)
                ->select('jabatan')
                ->distinct()
                ->whereNotNull('jabatan')
                ->pluck('jabatan');
        });



        // Apply age filter
        if ($minAge) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= ?', [$minAge]);
        }
        if ($maxAge) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= ?', [$maxAge]);
        }

        // Apply filter nama n ho
        if ($nama) {
            $query->where('nama', 'like', '%' . $nama . '%');
        }
        if ($hp_utama) {
            $query->where('hp_utama', 'like', '%' . $hp_utama . '%');
        }

        // Apply filter domisili
        if ($selectedKotarmh) {
            $query->where('kota_rmh', 'like', '%' . $selectedKotarmh . '%');
        }
        if ($selectedKotaperush) {
            $query->where('kota_perush', 'like', '%' . $selectedKotaperush . '%');
        }
        if ($selectedJabatan) {
            $query->where('jabatan', 'like', '%' . $selectedJabatan . '%');
        }


        // Get Chart Data
        $topJobs = Cache::rememberForever($current_user_id . '_top_jobs', function () use ($tablename) {
            return DB::select('
            SELECT COALESCE(jabatan, "Unknown") AS job, 
            COUNT(*) AS count
            FROM ' . $tablename . '
            GROUP BY jabatan
            ORDER BY count DESC
            LIMIT 5
        ');
        });

        $topCity = Cache::rememberForever($current_user_id . '_top_city', function () use ($tablename) {
            return DB::select('
            SELECT COALESCE(kota_rmh, "Unknown") AS city, 
            COUNT(*) AS count
            FROM ' . $tablename . '
            GROUP BY kota_rmh
            ORDER BY count DESC
            LIMIT 5
        ');
        });


        // Others category count
        $othersCountJob = DB::table($tablename)
            ->whereNotIn('jabatan', array_column($topJobs, 'job'))
            ->count();
        $othersCountCity = DB::table($tablename)
            ->whereNotIn('kota_rmh', array_column($topJobs, 'city'))
            ->count();

        if ($othersCountJob > 0) {
            $topJobs[] = (object) [
                'job' => 'Others',
                'count' => $othersCountJob,
            ];
        }
        if ($othersCountCity > 0) {
            $topCity[] = (object) [
                'city' => 'Others',
                'count' => $othersCountCity,
            ];
        }


        $job_labels = array_column($topJobs, 'job');
        $job_counts = array_column($topJobs, 'count');
        $city_labels = array_column($topCity, 'city');
        $city_counts = array_column($topCity, 'count');



        $master_data = $query->paginate(15)->withQueryString();
        return view(
            'index',
            compact(
                'master_data',
                'job_labels',
                'job_counts',
                'city_labels',
                'city_counts',
                'kota_rmh_options',
                'kota_perush_options',
                'jabatan_options',
                'selectedKotarmh',
                'selectedJabatan',
                'selectedKotaperush',
            )
        );
    }

    public function clear_database()
    {

        $user_id = Auth::user()->id;
        $tablename = $this->get_user_table($user_id);

        DB::table($tablename)->truncate();  // This will remove all data from the table

        Cache::forget($user_id . '_city_home_options');
        Cache::forget($user_id . '_city_work_options');
        Cache::forget($user_id . '_jabatan_options');
        Cache::forget($user_id . '_top_jobs');
        Cache::forget($user_id . '_top_city');

        return Redirect::route('dashboard');  // Redirect to the dashboard route
    }

    /**
     * Remove the specified record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        $user_id = Auth::user()->id;
        $tableName = $this->get_user_table($user_id);


        try {
            // Find the record by ID
            $data = DB::table($tableName)->where('id', $id)->first();

            if ($data) {
                DB::table($tableName)->where('id', $id)->delete();
            } else {
                abort(404, 'Record not found.');
            }

            Cache::forget($user_id . '_city_home_options');
            Cache::forget($user_id . '_city_work_options');
            Cache::forget($user_id . '_jabatan_options');
            Cache::forget($user_id . '_top_jobs');
            Cache::forget($user_id . '_top_city');
            // Redirect back with a success message
            return redirect()->route('dashboard')->with('success', 'Record deleted successfully!');
        } catch (\Exception $e) {
            // Redirect back with an error message if something goes wrong
            return redirect()->route('dashboard')->with('error', 'Failed to delete the record.');
        }
    }


    private function get_user_table($user_id)
    {
        $tablename = $user_id . '_master_data';

        if (!Schema::hasTable($tablename)) {
            $tablename = 'master_data';
        }

        return $tablename;
    }
}
