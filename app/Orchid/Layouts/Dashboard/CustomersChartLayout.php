<?php

namespace App\Orchid\Layouts\Dashboard;

use Orchid\Screen\Layouts\Chart;

class CustomersChartLayout extends Chart
{
    protected $title  = '👥 Customers Growth';
    protected $target = 'customers_chart';
    protected $type   = 'line';
    protected $height = 250;
    protected $description = 'New customers per month';
}