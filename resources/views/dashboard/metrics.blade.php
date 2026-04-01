<div class="row g-3 mt-1">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3" style="border-left: 4px solid #1D9E75 !important;">
            <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#e8f8f2;display:flex;align-items:center;justify-content:center;font-size:16px;">🏟️</div>
                <div>
                    <p class="text-muted mb-0" style="font-size:11px;">Total Courts</p>
                    <h6 class="fw-semibold mb-0" style="font-size:13px;">Courts</h6>
                </div>
                <h4 class="fw-bold ms-auto mb-0" style="color:#1D9E75">{{ $stats['courts'] }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3" style="border-left: 4px solid #378ADD !important;">
            <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#e8f2fd;display:flex;align-items:center;justify-content:center;font-size:16px;">📅</div>
                <div>
                    <p class="text-muted mb-0" style="font-size:11px;">Total Bookings</p>
                    <h6 class="fw-semibold mb-0" style="font-size:13px;">Bookings</h6>
                </div>
                <h4 class="fw-bold ms-auto mb-0" style="color:#378ADD">{{ $stats['bookings'] }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3" style="border-left: 4px solid #1D9E75 !important;">
            <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#e8f8f2;display:flex;align-items:center;justify-content:center;font-size:16px;">👥</div>
                <div>
                    <p class="text-muted mb-0" style="font-size:11px;">Total Customers</p>
                    <h6 class="fw-semibold mb-0" style="font-size:13px;">Customers</h6>
                </div>
                <h4 class="fw-bold ms-auto mb-0" style="color:#1D9E75">{{ $stats['customers'] }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3" style="border-left: 4px solid #D85A30 !important;">
            <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#fdeee8;display:flex;align-items:center;justify-content:center;font-size:16px;">💰</div>
                <div>
                    <p class="text-muted mb-0" style="font-size:11px;">Total Revenue</p>
                    <h6 class="fw-semibold mb-0" style="font-size:13px;">Revenue</h6>
                </div>
                <h4 class="fw-bold ms-auto mb-0" style="color:#D85A30">{{ $stats['revenue'] }}</h4>
            </div>
        </div>
    </div>
</div>