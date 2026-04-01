<?php

namespace App\Orchid\Layouts\Dashboard;

use Orchid\Screen\Layouts\Chart;

class CourtsChartLayout extends Chart
{
    protected $title  = '🏟️ Courts Usage';
    protected $target = 'courts_chart';
    protected $type   = 'bar';
    protected $height = 250;
    protected $description = 'Total bookings per court';
}