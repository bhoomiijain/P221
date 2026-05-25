<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = CustomerNotification::where('user_id', auth()->id())
            ->latest()
            ->limit(50)
            ->get();

        $unread = CustomerNotification::where('user_id', auth()->id())->whereNull('read_at')->count();

        return response()->json(['notifications' => $notifications, 'unread_count' => $unread]);
    }

    public function markRead(string $id)
    {
        $n = CustomerNotification::where('user_id', auth()->id())->findOrFail($id);
        $n->update(['read_at' => now()]);

        return response()->json(['notification' => $n]);
    }

    public function markAllRead()
    {
        CustomerNotification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['message' => 'All marked as read']);
    }
}
