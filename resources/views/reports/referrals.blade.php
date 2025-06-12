<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Referral Statistics – {{ $month->format('F Y') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Summary KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach([
                ['Total Referrals', $totalReferrals],
                ['Avg. Duration (days)', $averageDuration ?: 'N/A'],
                ['Median Duration', $medianDuration ?? 'N/A'],
                ['% Under 5 Days', $percentUnder5Days . '%'],
                ['Time to First Action', $avgFirstActionDelay . ' days'],
                ['Oldest Open Referral', $oldestOpenDays . ' days'],
                ['Submitted to Anesthesia', $anesthesiaSubmissions],
                ['Sent to Scheduling', $sentToScheduling],
            ] as [$label, $value])
                <div class="bg-white rounded-xl shadow p-5 flex flex-col justify-between">
                    <h3 class="text-sm font-medium text-gray-500 mb-1 truncate">{{ $label }}</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Referrals per Week</h3>
                <canvas id="weeklyChart" class="h-64"></canvas>
            </div>
            <div class="bg-white shadow rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Procedure Types</h3>
                <canvas id="procedureChart" class="h-64"></canvas>
            </div>
        </div>

        {{-- Status Overview --}}
        <div class="bg-white shadow rounded-lg p-4">
            <h3 class="text-md font-semibold text-gray-700 mb-2">Referral Status Overview</h3>
            <ul>
                @foreach(['received', 'in_progress', 'Sent_to_Scheduling'] as $status)
                    <li class="flex justify-between border-b py-2 text-sm">
                        <span class="capitalize text-gray-600">{{ str_replace('_', ' ', $status) }}</span>
                        <span class="font-medium text-gray-900">{{ $statusCounts[$status] ?? 0 }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Employee Activity --}}
        <div class="bg-white shadow rounded-lg p-4">
            <h3 class="text-md font-semibold text-gray-700 mb-4">Employee Performance (Referrals Touched)</h3>
            @if($referralProgressByUser->isEmpty())
                <p class="text-gray-500 text-sm">No referral activity recorded for this month.</p>
            @else
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left font-semibold text-gray-700">
                            <th class="px-4 py-2">User</th>
                            <th class="px-4 py-2"># Referrals</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($referralProgressByUser as $entry)
                            <tr>
                                <td class="px-4 py-2">{{ $entry['user'] }}</td>
                                <td class="px-4 py-2">{{ $entry['count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    @push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data passed from the controller
    const stackedData = @json($weeklyStackedReferrals);
    const procedureData = @json($procedures);

    // Sort labels by date
    const weekLabels = Object.keys(stackedData).sort((a, b) => new Date(a) - new Date(b));

    // Define statuses and color palette
    const statuses = ['received', 'in_progress', 'Sent_to_Scheduling'];
    const statusColors = {
        'received': '#60A5FA',           // blue
        'in_progress': '#FBBF24',        // yellow
        'Sent_to_Scheduling': '#34D399'  // green
    };

    // Build stacked datasets
    const datasets = statuses.map(status => ({
        label: status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
        data: weekLabels.map(week => stackedData[week]?.[status] || 0),
        backgroundColor: statusColors[status],
        stack: 'stack1'
    }));

    // Render stacked bar chart
    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: weekLabels,
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Referrals per Week by Status'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    position: 'top'
                }
            },
            scales: {
                x: { stacked: true },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });

    // Render procedure pie chart
    new Chart(document.getElementById('procedureChart'), {
        type: 'pie',
        data: {
            labels: Object.keys(procedureData),
            datasets: [{
                data: Object.values(procedureData),
                backgroundColor: ['#4F46E5', '#22C55E', '#F97316', '#EF4444', '#3B82F6', '#A855F7']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Procedure Types Breakdown'
                },
                legend: {
                    position: 'right'
                }
            }
        }
    });
</script>

    @endpush
</x-app-layout>
