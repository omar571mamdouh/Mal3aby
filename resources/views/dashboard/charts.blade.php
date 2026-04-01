<div class="row g-3 mt-1">

    {{-- Card 1: Bookings Trend (Line) --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3" style="border-left: 4px solid #1D9E75 !important; min-height: 280px;">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div style="width:32px;height:32px;border-radius:8px;background:#e8f8f2;display:flex;align-items:center;justify-content:center;font-size:16px;">📈</div>
                <div>
                    <p class="text-muted mb-0" style="font-size:11px;">Status logs per court</p>
                    <h6 class="fw-semibold mb-0" style="font-size:13px;">Avg Booking Logs</h6>
                </div>
                <h4 class="fw-bold ms-auto mb-0" style="color:#1D9E75">{{ $avglogs_values->sum() }}</h4>
            </div>
            <div style="height:180px; position:relative;">
                <canvas id="avgLogsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Card 2: Time Slots (Pie) --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3" style="border-left: 4px solid #0891B2 !important; min-height: 280px;">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div style="width:32px;height:32px;border-radius:8px;background:#e0f2fe;display:flex;align-items:center;justify-content:center;font-size:16px;">🕐</div>
                <div>
                    <p class="text-muted mb-0" style="font-size:11px;">Available slots per court</p>
                    <h6 class="fw-semibold mb-0" style="font-size:13px;">Time Slots</h6>
                </div>
                <h4 class="fw-bold ms-auto mb-0" style="color:#0891B2">{{ $timeslots_values->sum() }}</h4>
            </div>
            <div style="height:180px; position:relative;">
                <canvas id="timeSlotsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Card 3: Courts Usage (Bar) --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3" style="border-left: 4px solid #378ADD !important; min-height: 280px;">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div style="width:32px;height:32px;border-radius:8px;background:#e8f2fd;display:flex;align-items:center;justify-content:center;font-size:16px;">🏟️</div>
                <div>
                    <p class="text-muted mb-0" style="font-size:11px;">Bookings per court</p>
                    <h6 class="fw-semibold mb-0" style="font-size:13px;">Courts Usage</h6>
                </div>
                <h4 class="fw-bold ms-auto mb-0" style="color:#378ADD">{{ $courts_values->sum() }}</h4>
            </div>
            <div style="height:180px; position:relative;">
                <canvas id="courtsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Card 4: Court Revenue (Doughnut) --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3" style="border-left: 4px solid #D85A30 !important; min-height: 280px;">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div style="width:32px;height:32px;border-radius:8px;background:#fdeee8;display:flex;align-items:center;justify-content:center;font-size:16px;">📊</div>
                <div>
                    <p class="text-muted mb-0" style="font-size:11px;">Revenue distribution</p>
                    <h6 class="fw-semibold mb-0" style="font-size:13px;">Court Revenue</h6>
                </div>
                <h4 class="fw-bold ms-auto mb-0" style="color:#D85A30">${{ number_format($court_revenue_values->sum(), 2) }}</h4>
            </div>
            <div style="height:180px; position:relative;">
                <canvas id="courtRevenueDoughnut"></canvas>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const avgLogsLabels      = {!! json_encode($avglogs_labels->values()) !!};
    const avgLogsValues      = {!! json_encode($avglogs_values->values()) !!};
    const timeSlotsLabels    = {!! json_encode($timeslots_labels->values()) !!};
    const timeSlotsValues    = {!! json_encode($timeslots_values->values()) !!};
    const courtLabels        = {!! json_encode($courts_labels->values()) !!};
    const courtValues        = {!! json_encode($courts_values->values()) !!};
    const courtRevenueLabels = {!! json_encode($court_revenue_labels->values()) !!};
    const courtRevenueValues = {!! json_encode($court_revenue_values->values()) !!};
    const palette            = ['#1D9E75','#378ADD','#D85A30','#BA7517','#9B59B6'];

    // Card 1: Line Chart
    new Chart(document.getElementById('avgLogsChart'), {
        type: 'line',
        data: {
            labels: avgLogsLabels,
            datasets: [{
                data: avgLogsValues,
                borderColor: '#1D9E75',
                backgroundColor: 'rgba(29,158,117,0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#1D9E75'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 11 } } }
            }
        }
    });

    // Card 2: Pie Chart
    new Chart(document.getElementById('timeSlotsChart'), {
        type: 'pie',
        data: {
            labels: timeSlotsLabels,
            datasets: [{
                data: timeSlotsValues,
                backgroundColor: palette,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 }, boxWidth: 10 } }
            }
        }
    });

    // Card 3: Vertical Bar
    new Chart(document.getElementById('courtsChart'), {
        type: 'bar',
        data: {
            labels: courtLabels,
            datasets: [{ data: courtValues, backgroundColor: '#378ADD', borderRadius: 5 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 11 } } }
            }
        }
    });

    // Card 4: Doughnut
    new Chart(document.getElementById('courtRevenueDoughnut'), {
        type: 'doughnut',
        data: {
            labels: courtRevenueLabels,
            datasets: [{ data: courtRevenueValues, backgroundColor: palette, borderWidth: 0, cutout: '65%' }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 }, boxWidth: 10 } }
            }
        }
    });
</script>
@endpush