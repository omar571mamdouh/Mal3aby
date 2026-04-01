<?php

namespace App\Orchid\Layouts\Dashboard;

use Orchid\Screen\Layouts\Chart;

class BookingsChartLayout extends Chart
{
    protected $title  = '📅 Bookings Trend';
    protected $target = 'bookings_chart';
    protected $type   = 'line'; // line | bar | pie | percentage
    protected $height = 250;
    protected $description = 'Monthly bookings over the last 6 months';
}