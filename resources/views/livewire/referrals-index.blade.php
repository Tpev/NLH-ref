<div class="referral-container">
    {{-- ───── Filters / Search ───── --}}
    <div class="filter-search-bar">
        <label class="text-sm mr-1">Statsssus:</label>
        <select wire:model="statusFilter">
            <option value="all">All</option>
            <option value="in_progress">In&nbsp;Progress</option>
            <option value="completed">Completed</option>
        </select>

        <input type="text"
               class="flex-1"
               wire:model.debounce.500ms="search"
               placeholder="Search by Referral ID, Patient…">
    </div>

    {{-- ───── Table ───── --}}
    <table class="nice-table">
        <thead>
            <tr>
                <th wire:click="sortBy('id')"            class="cursor-pointer">ID</th>
                <th wire:click="sortBy('patient_name')"  class="cursor-pointer">Patient</th>
                <th wire:click="sortBy('patient_dob')"   class="cursor-pointer">DoB</th>
                <th wire:click="sortBy('status')"        class="cursor-pointer">Status</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($referrals as $r)
                @php
                    $badge = match($r->status) {
                        'in_progress' => 'status-inprogress',
                        'completed'   => 'status-completed',
                        default       => 'status-default',
                    };
                @endphp
                <tr>
                    <td>#{{ $r->id }}</td>

                    {{-- Patient + duplicate flag --}}
                    <td>
                        {{ $r->patient_name }}
                        @if ($r->is_duplicate)
                            <span  title="Potential duplicate (same name &amp; DoB)"
                                   class="ml-1 bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full text-xs font-medium">
                                   ⚠ Duplicate?
                            </span>
                        @endif
                    </td>

                    <td>{{ $r->patient_dob }}</td>

                    <td>
                        <span class="status-badge {{ $badge }}">
                            {{ ucfirst(str_replace('_',' ',$r->status)) }}
                        </span>
                    </td>

                    {{-- progress bar --}}
                    <td>
                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress-fill"
                                     style="width: {{ $r->progress_percent }}%"></div>
                            </div>
                            <small class="progress-text">
                                {{ $r->completed_steps }}/{{ $r->total_steps }}
                            </small>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="no-data">No referrals found.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="mt-6">{{ $referrals->links() }}</div>
</div>

<style>
.referral-container{max-width:900px;margin:auto;padding:20px;font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;color:#333}
.filter-search-bar{display:flex;gap:10px;margin-bottom:20px}
.filter-search-bar select,.filter-search-bar input{padding:8px;border:1px solid #ccc;border-radius:4px}
.nice-table{width:100%;border-collapse:collapse}
.nice-table th,.nice-table td{padding:10px;border:1px solid #e2e8f0;text-align:left}
.nice-table th{background:#f5f7fa}
.nice-table tr:hover{background:#fafcff}
.status-badge{padding:4px 8px;border-radius:4px;font-size:.75rem;font-weight:600}
.status-inprogress{background:#e0f0ff;color:#007bff}
.status-completed{background:#d4f4dc;color:#0f9b5a}
.status-default{background:#f0f0f0;color:#777}
.progress-container{width:100%;background:#e4eaf1;border-radius:8px}
.progress-bar{height:8px;background:#e4eaf1;border-radius:8px;overflow:hidden}
.progress-fill{height:100%;background:#4f93e6;transition:width .3s}
.no-data{padding:15px;text-align:center;color:#999;font-style:italic}
</style>
