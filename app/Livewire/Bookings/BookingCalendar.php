<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Livewire\Component;

class BookingCalendar extends Component
{
    public int $month;
    public int $year;

    public function mount(): void
    {
        $now = now();
        $this->month = (int) $now->month;
        $this->year = (int) $now->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonthNoOverflow();
        $this->month = (int) $date->month;
        $this->year = (int) $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonthNoOverflow();
        $this->month = (int) $date->month;
        $this->year = (int) $date->year;
    }

    private function buildCalendarGrid(CarbonInterface $monthStart): array
    {
        $startOffset = ((int) $monthStart->dayOfWeekIso) - 1;
        $daysInMonth = (int) $monthStart->daysInMonth;

        $cells = [];

        for ($i = 0; $i < $startOffset; $i++) {
            $cells[] = null;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $cells[] = Carbon::create($this->year, $this->month, $day)->toDateString();
        }

        while (count($cells) % 7 !== 0) {
            $cells[] = null;
        }

        return array_chunk($cells, 7);
    }

    public function render()
    {
        $monthStart = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $bookings = Booking::with(['client', 'service'])
            ->where('user_id', auth()->id())
            ->whereBetween('booking_date', [$monthStart, $monthEnd])
            ->orderBy('booking_date')
            ->get();

        $bookingsByDate = $bookings
            ->groupBy(fn(Booking $booking) => $booking->booking_date->toDateString())
            ->map(fn($items) => $items->values())
            ->all();

        return view('livewire.bookings.booking-calendar', [
            'monthLabel' => $monthStart->translatedFormat('F Y'),
            'calendarGrid' => $this->buildCalendarGrid($monthStart),
            'bookingsByDate' => $bookingsByDate,
        ]);
    }
}
