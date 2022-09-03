<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DailySaturationController extends Controller
{
    public function setTodaySaturation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bpm' => 'required|integer',
            'spo2' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = auth()->user();
        $date = date('Y-m-d');
        $bpm = $request->bpm;
        $spo2 = $request->spo2;


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://irwanda.pythonanywhere.com/?bpm=' . $bpm . '&spo2=' . $spo2,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $process = curl_exec($curl);

        curl_close($curl);

        $result = explode(' ', $process);
        $type = 'insert';

        $todaySaturation = DB::table('daily_saturation')->where('user_id', $user->id)->where('date', $date)->first();
        if ($todaySaturation) {
            DB::table('daily_saturation')->where('user_id', $user->id)->where('date', $date)->update([
                'bpm' => $bpm,
                'spo2' => $spo2,
                'stress_number' => intval($result[0]),
                'desc' => $result[1],
            ]);
            $type = 'update';
        } else {
            DB::table('daily_saturation')->insert([
                'user_id' => $user->id,
                'date' => $date,
                'bpm' => $bpm,
                'spo2' => $spo2,
                'stress_number' => intval($result[0]),
                'desc' => $result[1],
            ]);
        }

        return response()->json(['msg' => 'Today\'s Saturation set successfully', 'type' => $type, 'data' => ['user_id' => $user->id, 'date' => $date, 'bpm' => $bpm, 'spo2' => $spo2, 'stress_number' => $result[0], 'desc' => $result[1]]], 200);
    }

    public function getTodaySaturation()
    {
        return response()->json(['status' => 'success', 'data' => DB::select('SELECT * FROM daily_saturation WHERE date = CURDATE() AND user_id = ? LIMIT 1', [auth()->user()->id])]);
    }

    // Get data from start of the week (monday), to end of the week (sunday)
    public function getWeekSaturation()
    {
        return response()->json(['status' => 'success', 'data' => DB::table('daily_saturation')->where('user_id', auth()->user()->id)->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->get()]);
    }

    // Get data from start of the month, to end of the month
    public function getMonthSaturation()
    {
        return response()->json(['status' => 'success', 'data' => DB::table('daily_saturation')->where('user_id', auth()->user()->id)->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->get()]);
    }

    // Get data from start of the year, to end of the year
    public function getYearSaturation()
    {
        return response()->json(['status' => 'success', 'data' => DB::table('daily_saturation')->where('user_id', auth()->user()->id)->whereBetween('date', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])->get()]);
    }

    // Full week (7) days
    public function getFullWeekSaturation()
    {
        return response()->json(['status' => 'success', 'data' => DB::select('SELECT * FROM daily_saturation WHERE user_id = ? AND date BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()', [auth()->user()->id])]);
    }

    // Full month (30) days
    public function getFullMonthSaturation()
    {
        return response()->json(['status' => 'success', 'data' => DB::select('SELECT * FROM daily_saturation WHERE user_id = ? AND date BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()', [auth()->user()->id])]);
    }

    // Full year (365) days
    public function getFullYearSaturation()
    {
        return response()->json(['status' => 'success', 'data' => DB::select('SELECT * FROM daily_saturation WHERE user_id = ? AND date BETWEEN CURDATE() - INTERVAL 365 DAY AND CURDATE()', [auth()->user()->id])]);
    }
}
