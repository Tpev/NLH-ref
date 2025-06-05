<!-- Referrals Table (with duplicate badge) -->
<div>
@php
    /*
     |--------------------------------------------------------------
     | Build a quick “duplicate” lookup table for this page only.
     | Key = lower-case patient name + dob   →   count
     |--------------------------------------------------------------
     */
    $dupCounts = [];
    foreach ($referrals as $r) {
        $key = strtolower(trim($r->patient_name)).'|'.($r->dob ?? '');
        $dupCounts[$key] = ($dupCounts[$key] ?? 0) + 1;
    }
@endphp

<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full divide-y divide-green-200 text-sm">
        <thead class="bg-green-50 text-green-900 uppercase text-xs font-semibold tracking-wider">
            <tr>
                <!-- sortable headers (unchanged) -->
                <th class="px-6 py-3 text-left cursor-pointer" wire:click="sortBy('id')">
                    ID   @if($sortField==='id'){{ $sortDirection==='asc'?'↑':'↓' }}@endif
                </th>
                <th class="px-6 py-3 text-left cursor-pointer" wire:click="sortBy('patient_name')">
                    Patient Name   @if($sortField==='patient_name'){{ $sortDirection==='asc'?'↑':'↓' }}@endif
                </th>
                <th class="px-6 py-3 text-left cursor-pointer" wire:click="sortBy('dob')">
                    DOB   @if($sortField==='dob'){{ $sortDirection==='asc'?'↑':'↓' }}@endif
                </th>
                <th class="px-6 py-3 text-left cursor-pointer" wire:click="sortBy('procedure')">
                    Procedure   @if($sortField==='procedure'){{ $sortDirection==='asc'?'↑':'↓' }}@endif
                </th>
                <th class="px-6 py-3 text-left cursor-pointer" wire:click="sortBy('created_at')">
                    Created   @if($sortField==='created_at'){{ $sortDirection==='asc'?'↑':'↓' }}@endif
                </th>
                <th class="px-6 py-3 text-left cursor-pointer" wire:click="sortBy('progress_percent')">
                    Progress   @if($sortField==='progress_percent'){{ $sortDirection==='asc'?'↑':'↓' }}@endif
                </th>
                <th class="px-6 py-3 text-left cursor-pointer" wire:click="sortBy('current_step')">
                    Current Step   @if($sortField==='current_step'){{ $sortDirection==='asc'?'↑':'↓' }}@endif
                </th>
                <th class="px-6 py-3 text-right">Action</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-green-100 bg-white">
        @forelse ($referrals as $referral)
            @php
                $dupKey   = strtolower(trim($referral->patient_name)).'|'.($referral->dob ?? '');
                $isDup    = ($dupCounts[$dupKey] ?? 0) > 1;
            @endphp
            <tr class="table-row-hover">
                <!-- ID -->
                <td class="px-6 py-4 font-semibold text-gray-900">#{{ $referral->id }}</td>

                <!-- Patient Name + duplicate badge -->
                <td class="px-6 py-4 text-gray-700">
                    {{ $referral->patient_name }}
                    @if($isDup)
                        <span  title="Potential duplicate: same patient &amp; DoB"
                               class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full
                                      bg-yellow-100 text-yellow-800 text-xs font-semibold">
                            ⚠ Duplicate?
                        </span>
                    @endif
                </td>

                <!-- DOB -->
                <td class="px-6 py-4 text-gray-700">{{ $referral->dob }}</td>

                <!-- Procedure -->
                <td class="px-6 py-4 text-gray-700">{{ $referral->procedure }}</td>

                <!-- Created -->
                <td class="px-6 py-4 text-gray-700">{{ $referral->created_at->format('Y-m-d') }}</td>

                <!-- Progress -->
                <td class="px-6 py-4">
                    <div class="progress-container">
                        <div class="progress-bar" style="width: {{ $referral->progress_percent }}%;"></div>
                        <div class="progress-label">{{ $referral->progress_percent }}%</div>
                    </div>
                    <div class="progress-steps">
                        {{ $referral->completed_steps }}/{{ $referral->total_steps }} steps
                    </div>
                </td>

                <!-- Current Step -->
                <td class="px-6 py-4 text-gray-700">{{ $referral->current_step }}</td>

                <!-- Action -->
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('referrals.workflow.show', $referral->id) }}"
                       class="action-button">View</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">No referrals found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($referrals->hasPages())
    <div class="mt-6">{{ $referrals->links() }}</div>
@endif

<style>
        /* Table Row Hover */
        .table-row-hover {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .table-row-hover:hover {
            background-color: #ecfdf5; /* Green-50 */
            transform: translateY(-1px) scale(1.01);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);
        }

        /* Cursor pointer for sorting columns */
        th.cursor-pointer {
            cursor: pointer;
            user-select: none;
        }

        /* Buttons */
        .toggle-button {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
            color: #374151;
            background-color: #ffffff;
            transition: all 0.3s ease-in-out;
        }
        .toggle-button:hover {
            background-color: #f9fafb;
        }
        .toggle-button.active {
            background-color: #16a34a; /* Green-600 */
            color: #ffffff;
            border-color: #16a34a;
            box-shadow: 0 2px 6px rgba(22, 163, 74, 0.4);
        }

        /* Input Fields */
        .input-field {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
            font-size: 0.875rem;
            color: #374151;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: border 0.3s, box-shadow 0.3s;
        }
        .input-field:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.3);
            outline: none;
        }

        /* Urgency Badges */
        .urgency-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .urgency-today {
            background-color: rgba(34,197,94,0.1);
            color: #15803d;
        }
        .urgency-tomorrow {
            background-color: rgba(163,230,53,0.1);
            color: #65a30d;
        }

        /* Progress Bar */
        .progress-container {
            position: relative;
            height: 1.25rem;
            width: 100%;
            background-color: #dcfce7;
            border-radius: 9999px;
            overflow: hidden;
            border: 1px solid #bbf7d0;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(to right, #4ade80, #22c55e);
            border-radius: 9999px;
            transition: width 0.7s ease-in-out;
        }
        .progress-label {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #065f46;
        }
        .progress-steps {
            margin-top: 0.25rem;
            font-size: 0.75rem;
            text-align: center;
            color: #065f46;
        }

        /* Action Button */
        .action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            background-color: #16a34a;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.375rem;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 2px 4px rgba(22, 163, 74, 0.2);
        }
        .action-button:hover {
            background-color: #15803d;
            box-shadow: 0 4px 8px rgba(22, 163, 74, 0.4);
        }
        .action-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.5);
        }
.table-row-hover{transition:all .3s;cursor:pointer}
.table-row-hover:hover{background:#ecfdf5;transform:translateY(-1px) scale(1.01);box-shadow:0 4px 12px rgba(34,197,94,.15)}
/* duplicate badge colours already declared inline */
</style>
</div>
