<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        $stats = [
            'total_bookings'   => Booking::where('user_id', $user->id)->count(),
            'today_bookings'   => Booking::where('user_id', $user->id)->whereDate('booking_date', today())->count(),
            'total_clients'    => Client::where('user_id', $user->id)->count(),
            'total_services'   => Service::where('user_id', $user->id)->count(),
            'revenue_month'    => Invoice::whereHas('booking', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->sum('total'),
            'pending_invoices' => Invoice::whereHas('booking', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'unpaid')
                ->count(),
        ];

        $upcomingBookings = Booking::with(['client', 'service'])
            ->where('user_id', $user->id)
            ->where('booking_date', '>=', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('booking_date')
            ->limit(5)
            ->get();

        $recentBookings = Booking::with(['client', 'service'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('livewire.dashboard', compact('stats', 'upcomingBookings', 'recentBookings'));
    }
}
