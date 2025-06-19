<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSettings;

class UserSettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $settings = $user->settings ?? UserSettings::create([
            'user_id' => $user->id,
            'theme' => 'light',
            'language' => 'pt',
        ]);
        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'theme' => 'in:light,dark',
            'language' => 'string|max:5',
        ]);

        $user = $request->user();
        $settings = $user->settings;

        if (!$settings) {
            $settings = new UserSettings(['user_id' => $user->id]);
        }

        $settings->fill($data);
        $settings->save();

        return response()->json(['message' => 'Configurações atualizadas', 'settings' => $settings]);
    }
}
