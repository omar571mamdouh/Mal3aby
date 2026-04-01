<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;padding:16px 0">

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px 24px;">
        <div style="font-size:12px;color:#9ca3af;font-weight:500;margin-bottom:8px">📊 Total Cancellations</div>
        <div style="font-size:28px;font-weight:700;color:#111827">{{ $total }}</div>
    </div>

    <div style="background:#fff;border:1px solid #fecaca;border-radius:12px;padding:20px 24px;">
        <div style="font-size:12px;color:#9ca3af;font-weight:500;margin-bottom:8px">📅 This Month</div>
        <div style="font-size:28px;font-weight:700;color:#dc2626">{{ $this_month }}</div>
    </div>

    <div style="background:#fff;border:1px solid #fde68a;border-radius:12px;padding:20px 24px;">
        <div style="font-size:12px;color:#9ca3af;font-weight:500;margin-bottom:8px">🕐 Today</div>
        <div style="font-size:28px;font-weight:700;color:#d97706">{{ $today }}</div>
    </div>

</div>

{{-- في آخر الملف --}}
<script>
function saveReason(id) {
    const value = document.getElementById('reason_' + id).value;

    fetch('{{ route("platform.cancellations") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            _method: 'POST',
            action: 'updateReason',
            id: id,
            reason: value,
        }),
    }).then(res => {
        if (res.ok) {
            // Toast بيظهر من Orchid تلقائيًا
            window.location.reload();
        }
    });
}
</script>