@extends('layouts.app')

@php
    $title = 'Leave Applications';
    $headerTitle = 'Staff Leave Filing Desk';
    $breadcrumb = 'Reception / Leave Applications';
@endphp

@section('content')
    <div style="max-width: 680px; margin: 0 auto;">
        <!-- Header -->
        <div style="margin-bottom: 1.5rem; text-align: center;">
            <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--white); margin-bottom: 0.35rem;">
                📝 Employee Leave Application Form
            </h2>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                Direct filing terminal for clinic staff vacation, sick leave, and special privilege leave requests.
            </p>
        </div>

        <!-- Direct Filing Form Card -->
        <div class="card" style="border: 1px solid var(--gold-border); box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
            <div class="card-header" style="background: rgba(212, 175, 55, 0.08); border-bottom: 1px solid var(--gold-border); padding: 1.1rem 1.5rem;">
                <div class="card-title-group">
                    <h3 class="card-title" style="font-size: 1.05rem; color: var(--gold-light); display: flex; align-items: center; gap: 8px;">
                        <span>📄 File Staff Leave Request</span>
                    </h3>
                    <span class="card-subtitle">Fill in leave dates and submit application</span>
                </div>
            </div>

            <form action="{{ route('receptionist.leaves.store') }}" method="POST">
                @csrf
                <div class="card-body" style="padding: 1.75rem;">
                    <!-- Employee Picker -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" style="font-weight: 700; color: var(--white); font-size: 0.9rem;">
                            Employee / Staff Member <span class="req">*</span>
                        </label>
                        <select name="employee_id" class="form-control" style="font-size: 0.95rem; padding: 0.75rem;" required>
                            <option value="">-- Select Staff Member --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->employee_code }} — {{ $emp->full_name }} ({{ $emp->position }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Leave Type -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" style="font-weight: 700; color: var(--white); font-size: 0.9rem;">
                            Leave Classification <span class="req">*</span>
                        </label>
                        <select name="leave_type" class="form-control" style="font-size: 0.95rem; padding: 0.75rem;" required>
                            <option value="vacation_leave">🌴 Vacation Leave (VL)</option>
                            <option value="sick_leave">🩺 Sick Leave (SL)</option>
                            <option value="special_leave">⭐ Special Privilege Leave (SPL)</option>
                        </select>
                    </div>

                    <!-- Leave Duration -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 700; color: var(--white);">
                                Start Date <span class="req">*</span>
                            </label>
                            <input type="date" name="start_date" id="leave_start_date" class="form-control" value="{{ date('Y-m-d') }}" required onchange="calcLeaveDays()">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 700; color: var(--white);">
                                End Date <span class="req">*</span>
                            </label>
                            <input type="date" name="end_date" id="leave_end_date" class="form-control" value="{{ date('Y-m-d') }}" required onchange="calcLeaveDays()">
                        </div>
                    </div>

                    <!-- Days indicator badge -->
                    <div style="background: rgba(11, 25, 44, 0.5); border: 1px solid var(--navy-border); border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.8rem; color: var(--text-muted);">Calculated Leave Period:</span>
                        <strong id="leaveDaysCount" style="color: var(--gold-light); font-size: 0.9rem;">1 Day</strong>
                    </div>

                    <!-- Reason -->
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label class="form-label" style="font-weight: 700; color: var(--white);">
                            Reason / Remarks <span class="req">*</span>
                        </label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Provide reason for leave (medical consultation, family emergency, scheduled vacation)..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-gold" style="width: 100%; padding: 0.85rem; font-size: 1rem; font-weight: 700; justify-content: center;">
                        <span>📤 Submit Leave Application</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function calcLeaveDays() {
        const startVal = document.getElementById('leave_start_date').value;
        const endVal = document.getElementById('leave_end_date').value;

        if (startVal && endVal) {
            const start = new Date(startVal);
            const end = new Date(endVal);

            if (end < start) {
                document.getElementById('leave_end_date').value = startVal;
                document.getElementById('leaveDaysCount').textContent = '1 Day';
                return;
            }

            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            document.getElementById('leaveDaysCount').textContent = diffDays + (diffDays > 1 ? ' Days' : ' Day');
        }
    }
</script>
@endpush
