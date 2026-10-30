<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNotifikasiController extends Controller
{
    public function belumDibaca(Request $request): JsonResponse
    {
        $notifications = $request->user()->unreadNotifications()->latest()->limit(10)->get();
        $items = $notifications->map(function ($notification): array {
            $item = ['id' => $notification->id, ...$notification->data];
            $notification->markAsRead();

            return $item;
        })->values();

        return response()->json(['notifications' => $items]);
    }
}
