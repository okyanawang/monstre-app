<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function updatePersonality(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'personality' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();
        $user->personality = $request->personality;
        $user->save();
        return response()->json($user);
    }

    public function updateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($image = $request->file('avatar')) {
            $user = Auth::user();
            $destinationPath = public_path('/storage/images/avatars/' . $user->id);
            $name = $user->id . '-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move($destinationPath, $name);

            $user->avatar = $name;
            $user->save();
            return response()->json($user);
        }
    }

    public function getArticles()
    {
        $user = Auth::user();
        $lastSaturation = DB::table('daily_saturation')->where('user_id', $user->id)->orderBy('date', 'desc')->first();

        if ($lastSaturation && (strtolower($lastSaturation->desc) == 'stress' || strtolower($lastSaturation->desc) == 'anxious')) {
            if (strtolower($user->personality[0]) == 'i') {
                return [
                    'type' => 'articles',
                    'data' => [
                        [
                            'title' => 'Practice Meditation',
                            'desc' => 'Meditation can boost your resilience toward stress when practiced long-term and can help you to feel more relaxed in the short-term as well.'
                        ],
                        [
                            'title' => 'Organize Your Space',
                            'desc' => "Introverts love having a space of their own, a place to go and recharge. If your space is chaotic, this becomes more difficult. While cleaning may not be the most enjoyable activity you can engage in, maintaining a 'happy place' for yourself can be great for stress management, so it is entirely worth it to think of cleaning as a stress reliever and maintain a peaceful space of your own."
                        ],
                        [
                            'title' => 'Know Your Limits and Respect Them',
                            'desc' => "Many introverts feel the need to keep up with their extroverted friends in an attempt to appear more friendly. If you can push yourself to be more extroverted than you naturally would be, this isn't a bad thing—studies show that when introverts 'act extroverted', they experience an increase in feelings of happines."
                        ]
                    ]
                ];
            } else {
                return [
                    'type' => 'articles',
                    'data' => [
                        [
                            'title' => 'Go out and get some fresh air',
                            'desc' => 'Go out and participate in some social activity especially if it is a group activity e.g. an outing with friends, getting physical exercise, laughing and socializing with others, etc.'
                        ],
                        [
                            'title' => 'Talk to someone',
                            'desc' => 'Have a social network and talk to them whenever you feel overwhelmed. Arrange a meet up or talk to them on the phone to let go of any bottled up emotions.'
                        ],
                        [
                            'title' => 'Seek advice',
                            'desc' => 'Seek advice from someone you trust e.g. a professional, a counselor, a spiritual guide, etc.'
                        ]
                    ]
                ];
            }
        } else {
            return [
                'type' => 'message',
                'data' => [
                    [
                        'title' => 'Your emotion is on a good level',
                        'desc' => 'Keep your work pace, steady, and no need to rush things up.'
                    ]
                ]
            ];
        }
    }
}
