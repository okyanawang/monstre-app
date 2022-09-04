<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://167.71.203.220:3003/api/v1/sensor',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $process = json_decode(curl_exec($curl));

        curl_close($curl);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        foreach ($process as $data) {
            $sample = strtotime($data->createdAt);
            $dataDate = date('Y-m-d', $sample);
            $type = 'insert';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://irwanda.pythonanywhere.com/?bpm=' . $data->heartRate . '&spo2=' . $data->oxiRate,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $pythonProcess = curl_exec($curl);
            curl_close($curl);

            $result = explode(' ', $pythonProcess);

            $todaySaturation = DB::table('daily_saturation')->where('user_id', $user->id)->where('date', $dataDate)->first();
            if ($todaySaturation) {
                DB::table('daily_saturation')->where('user_id', $user->id)->where('date', $dataDate)->update([
                    'bpm' => $data->heartRate,
                    'spo2' => $data->oxiRate,
                    'stress_number' => intval($result[0]),
                    'desc' => $result[1],
                ]);
                $type = 'update';
            } else {
                DB::table('daily_saturation')->insert([
                    'user_id' => $user->id,
                    'date' => $dataDate,
                    'bpm' => $data->heartRate,
                    'spo2' => $data->oxiRate,
                    'stress_number' => intval($result[0]),
                    'desc' => $result[1],
                ]);
            }
        }

        return response()
            ->json(['data' => $user, 'access_token' => $token, 'token_type' => 'Bearer',]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()
                ->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://167.71.203.220:3003/api/v1/sensor/last',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $process = json_decode(curl_exec($curl));
        curl_close($curl);
        $sample = strtotime($process->createdAt);
        $dataDate = date('Y-m-d', $sample);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://irwanda.pythonanywhere.com/?bpm=' . $process->heartRate . '&spo2=' . $process->oxiRate,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $pythonProcess = curl_exec($curl);
        curl_close($curl);

        $result = explode(' ', $pythonProcess);

        $todaySaturation = DB::table('daily_saturation')->where('user_id', $user->id)->where('date', $dataDate)->first();
        if ($todaySaturation) {
            DB::table('daily_saturation')->where('user_id', $user->id)->where('date', $dataDate)->update([
                'bpm' => $process->heartRate,
                'spo2' => $process->oxiRate,
                'stress_number' => intval($result[0]),
                'desc' => $result[1],
            ]);
            $type = 'update';
        } else {
            DB::table('daily_saturation')->insert([
                'user_id' => $user->id,
                'date' => $dataDate,
                'bpm' => $process->heartRate,
                'spo2' => $process->oxiRate,
                'stress_number' => intval($result[0]),
                'desc' => $result[1],
            ]);
        }

        return response()
            ->json(['message' => 'Hi ' . $user->name . ', welcome to home', 'access_token' => $token, 'token_type' => 'Bearer',]);
    }

    // method for user logout and delete token
    public function logout(Request $request)
    {
        Auth()->user()->tokens()->delete();

        return [
            'message' => 'You have successfully logged out and the token was successfully deleted'
        ];
    }
}
