<x-app-layout>

@php
    // Load referral with workflow and progress
    $referral = \App\Models\Referral::with(['workflow.stages.steps', 'progress'])->find($id);
    $workflow = $referral?->workflow;
    $stages = $workflow?->stages->sortBy('order') ?? collect();
    $userGroups = auth()->user()->group ?? [];
    $stepProgressMap = $referral->progress->keyBy('workflow_step_id');

    // Define helper once (prefixed to avoid conflict)
    if (!function_exists('__extractActualValue')) {
        function __extractActualValue($notes) {
            if (!$notes || !is_string($notes)) return null;
            $decoded = json_decode($notes, true);
            if (!is_array($decoded)) return $notes;
            return $decoded[array_key_first($decoded)] ?? null;
        }
    }

// Build exclusion reasons
$exclusionReasons = [];

foreach ($referral->progress as $progress) {
    $step = $workflow->stages->flatMap->steps->firstWhere('id', $progress->workflow_step_id);

    if ($step && $step->type === 'decision') {
        $notes = json_decode($progress->notes, true) ?? $progress->notes;
        $selectedValue = is_array($notes) ? reset($notes) : $notes;

        $excludeOptions = $step->metadata['options'] ?? [];
        $onTrueText = strtolower($step->metadata['on_true'] ?? '');

        // Normalize and filter out safe values
        $lowerVal = strtolower(trim($selectedValue));
        if (
            in_array($selectedValue, $excludeOptions, true) &&
            !in_array($lowerVal, ['no', 'none', 'none of the above']) &&
            str_contains($onTrueText, 'exclude')
        ) {
            $exclusionReasons[] = $step->name . ' — ' . $selectedValue;
        }
    }
}

@endphp

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Workflow Details') }}
    </h2>
</x-slot>

<div class="py-6 max-w-7xl mx-auto grid grid-cols-12 gap-6">
    {{-- RED EXCLUSION BANNER --}}
    @if(!empty($exclusionReasons))
        <div class="col-span-12 mb-6">
            <div class="bg-red-100 border border-red-400 text-red-800 p-4 rounded-lg shadow">
                <div class="flex items-center gap-2">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/>
                    </svg>
                    <strong class="text-lg">Patient Exclusion Flagged</strong>
                </div>
                <ul class="list-disc pl-6 mt-2 text-sm">
                    @foreach($exclusionReasons as $reason)
                        <li>{{ $reason }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif


    {{-- STEP INDEX SIDEBAR --}}
    <aside class="col-span-3 sticky top-6 h-fit bg-white dark:bg-gray-800 border rounded p-4 shadow-sm">
        <h3 class="text-lg font-bold mb-3 text-gray-700 dark:text-gray-100">Steps Index</h3>
        <ul class="space-y-2 text-sm text-green-700 dark:text-green-300">
            @foreach($stages as $stage)
                @php
                    $visibleSteps = $stage->steps->sortBy('order')->filter(function ($step) use ($userGroups, $stepProgressMap) {
                        $writeGroups = $step->group_can_write ?? [];
                        $seeGroups   = $step->group_can_see ?? [];
                        $canWrite = !empty(array_intersect($userGroups, $writeGroups));
                        $canSee   = $canWrite || !empty(array_intersect($userGroups, $seeGroups));

                        $dependency = $step->metadata['depends_on'] ?? null;
                        if ($dependency) {
                            $dependentStepId = $dependency['step_id'] ?? null;
                            $expectedValue   = $dependency['value'] ?? null;
                            $actualProgress  = $stepProgressMap[$dependentStepId] ?? null;
                            $actualValue     = __extractActualValue($actualProgress?->notes);
                            if ($actualValue !== $expectedValue) return false;
                        }

                        return $canSee;
                    });
                @endphp

                @if($visibleSteps->isNotEmpty())
                    <li class="font-semibold mt-3 text-gray-600 dark:text-gray-300">{{ $stage->name }}</li>
                    <ul class="ml-3 space-y-1">
                        @foreach($visibleSteps as $step)
                            <li>
                                <a href="#step-{{ $step->id }}" class="hover:underline">
                                    {{ $step->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endforeach
        </ul>
    </aside>

    {{-- MAIN WORKFLOW CONTENT --}}
    <div class="col-span-9">
        <livewire:referral-workflow-show :referralId="$id" />
    </div>
</div>

</x-app-layout>
