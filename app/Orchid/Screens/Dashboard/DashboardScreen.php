<?php

namespace App\Orchid\Screens\Dashboard;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use App\Models\Court;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\BookingStatusLog;
use App\Models\CourtTimeSlot;

class DashboardScreen extends Screen
{
    public $name = 'Dashboard';
    public $description = 'Overview of your sports facility';

    public function query(): iterable
    {
        // Avg booking status logs per court
        $rawAvgLogs = BookingStatusLog::selectRaw('courts.name as label, COUNT(booking_status_logs.id) as value')
            ->join('bookings', 'bookings.id', '=', 'booking_status_logs.booking_id')
            ->join('courts', 'courts.id', '=', 'bookings.court_id')
            ->groupBy('courts.id', 'courts.name')
            ->get();

        // Time slots per court
        $rawTimeSlots = CourtTimeSlot::selectRaw('courts.name as label, COUNT(court_time_slots.id) as value')
            ->join('courts', 'courts.id', '=', 'court_time_slots.court_id')
            ->groupBy('courts.id', 'courts.name')
            ->get();

        // Courts usage (bookings count per court)
        $rawCourts = Booking::selectRaw('courts.name as label, COUNT(bookings.id) as value')
            ->join('courts', 'courts.id', '=', 'bookings.court_id')
            ->groupBy('courts.id', 'courts.name')
            ->get();

        // Total revenue per court
        $rawCourtRevenue = Booking::selectRaw('courts.name as label, ROUND(SUM(price),2) as value')
            ->join('courts', 'courts.id', '=', 'bookings.court_id')
            ->groupBy('courts.id', 'courts.name')
            ->get();

        return [
            'stats' => [
                'courts'    => Court::count(),
                'bookings'  => Booking::count(),
                'customers' => Customer::count(),
                'revenue'   => '$' . number_format(Booking::sum('price'), 2),
            ],
            'avglogs_labels'       => $rawAvgLogs->pluck('label'),
            'avglogs_values'       => $rawAvgLogs->pluck('value')->map(fn($v) => (int)$v),
            'timeslots_labels'     => $rawTimeSlots->pluck('label'),
            'timeslots_values'     => $rawTimeSlots->pluck('value')->map(fn($v) => (int)$v),
            'courts_labels'        => $rawCourts->pluck('label'),
            'courts_values'        => $rawCourts->pluck('value')->map(fn($v) => (int)$v),
            'court_revenue_labels' => $rawCourtRevenue->pluck('label'),
            'court_revenue_values' => $rawCourtRevenue->pluck('value')->map(fn($v) => (float)$v),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('dashboard.metrics'),
            Layout::view('dashboard.charts'),

        ];
    }
}
