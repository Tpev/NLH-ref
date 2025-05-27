@props(['labels', 'intake'])

<aside
  class="lg:col-span-1 bg-white dark:bg-gray-800 border rounded-xl shadow-lg
         lg:sticky lg:top-6 h-fit overflow-hidden relative"
  x-data="{ open: true }"
>
  {{-- Mobile toggle handle --}}
  <button
    class="lg:hidden absolute -left-4 top-1/2 -translate-y-1/2 w-10 h-10
           rounded-r-full bg-green-600 text-white flex items-center
           justify-center shadow-md"
    @click="open = !open"
    :aria-expanded="open"
  >
    <i class="fa-solid fa-chevron-right" x-show="!open"></i>
    <i class="fa-solid fa-chevron-left"  x-show="open"></i>
  </button>

  <div
    x-show="open || $screen('lg')"
    x-transition.duration.200ms
    class="p-6 space-y-6 max-h-[85vh] overflow-y-auto"
  >
    <h3 class="text-xl font-bold text-green-700 flex items-center space-x-2 mb-4">
      <i class="fa-solid fa-id-card-clip text-green-600"></i>
      <span>Intake Details</span>
    </h3>

    {{-- PATIENT INFO --}}
    <div>
      <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">
        Patient Info
      </h4>
      <dl class="grid grid-cols-1 gap-3">
        @foreach(['last_name','first_name','dob','gender','primary_phone'] as $key)
          @if(!empty($intake[$key]))
            <div class="flex justify-between bg-gray-50 dark:bg-gray-700
                        px-4 py-2 rounded-lg">
              <dt class="text-gray-600">{{ $labels[$key] }}</dt>
              <dd class="font-medium text-gray-900 dark:text-gray-100">
                {{ $intake[$key] }}
              </dd>
            </div>
          @endif
        @endforeach
      </dl>
    </div>

    {{-- REFERRING PROVIDER --}}
    <div>
      <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">
        Referring Provider
      </h4>
      <dl class="grid grid-cols-1 gap-3">
        @foreach(['referring_physician','referring_facility','referring_phone'] as $key)
          @if(!empty($intake[$key]))
            <div class="flex justify-between bg-gray-50 dark:bg-gray-700
                        px-4 py-2 rounded-lg">
              <dt class="text-gray-600">{{ $labels[$key] }}</dt>
              <dd class="font-medium text-gray-900 dark:text-gray-100">
                {{ $intake[$key] }}
              </dd>
            </div>
          @endif
        @endforeach
      </dl>
    </div>

    {{-- CLINICAL SUMMARY --}}
    <div>
      <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">
        Clinical
      </h4>
      <dl class="space-y-3">
        @if(!empty($intake['gi_procedures']))
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 rounded-lg">
            <dt class="text-gray-600">GI Procedures</dt>
            <dd class="font-medium text-gray-900 dark:text-gray-100">
              {{ implode(', ', $intake['gi_procedures']) }}
            </dd>
          </div>
        @endif
        @if(!empty($intake['reason']))
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 rounded-lg">
            <dt class="text-gray-600">Reason</dt>
            <dd class="font-medium text-gray-900 dark:text-gray-100">
              {{ $intake['reason'] }}
            </dd>
          </div>
        @endif
        @if(!empty($intake['diagnosis']))
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 rounded-lg">
            <dt class="text-gray-600">Diagnosis</dt>
            <dd class="font-medium text-gray-900 dark:text-gray-100">
              {{ $intake['diagnosis'] }}
            </dd>
          </div>
        @endif
        @if(!empty($intake['clinical_summary']))
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 rounded-lg">
            <dt class="text-gray-600">Summary</dt>
            <dd class="font-medium text-gray-900 dark:text-gray-100 whitespace-pre-line">
              {{ $intake['clinical_summary'] }}
            </dd>
          </div>
        @endif
      </dl>
    </div>
  </div>
</aside>
