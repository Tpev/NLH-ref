<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Referral;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReferralsTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public ?string $dischargeFilter = null;

    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    protected $paginationTheme = 'tailwind';

    /**
     * Livewire lifecycle hooks for search/filter changes.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingDischargeFilter()
    {
        $this->resetPage();
    }

    /**
     * Toggle sorting field/direction.
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            // Toggle the direction
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Set new field, default direction asc
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        // Reset to first page when sorting changes
        $this->resetPage();
    }

    public function render()
    {
        // 1) Base query with relationships (no DB orderBy yet)
        //    because we will do in-memory sorting later.
        $referrals = Referral::query()
            ->with(['workflow.stages.steps', 'progress.step'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('id', 'like', "%{$this->search}%")
                      ->orWhereHas('progress', function ($q2) {
                          $q2->where('notes', 'like', "%{$this->search}%");
                      });
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->get(); // We'll do discharge filtering in memory below

        // 2) Discharge Filter in memory
        if ($this->dischargeFilter) {
            $referrals = $referrals->filter(function ($referral) {
                $firstStepProgress = $referral->progress->firstWhere('step.type', 'form');
                if (!$firstStepProgress || !$firstStepProgress->notes) {
                    return false;
                }

                $notes = json_decode($firstStepProgress->notes, true);
                $dischargeDateRaw = $notes['date_of_discharge'] ?? null;
                if (!$dischargeDateRaw) {
                    return false;
                }

                $parsedDate = Carbon::parse($dischargeDateRaw);
                return match($this->dischargeFilter) {
                    'today'    => $parsedDate->isToday(),
                    'tomorrow' => $parsedDate->isTomorrow(),
                    default    => false,
                };
            });
        }

        // 3) Transform referral data (patient_name, progress, etc.)
        $referrals = $referrals->map(function ($referral) {
            $firstStepProgress = $referral->progress->firstWhere('step.type', 'form');

            $patientName   = '—';
            $dischargeDate = null;
            $urgencyBadge  = null;
            $facilityName  = '—';

            if ($firstStepProgress && $firstStepProgress->notes) {
                $notes = json_decode($firstStepProgress->notes, true);

                // Patient name
                $patientName = trim(($notes['first_name'] ?? '') . ' ' . ($notes['last_name'] ?? '')) ?: '—';

                // Discharge date
                if (!empty($notes['date_of_discharge'])) {
                    try {
                        $parsedDate = Carbon::parse($notes['date_of_discharge']);
                        $dischargeDate = $parsedDate->format('m/d/Y');

                        if ($parsedDate->isToday()) {
                            $urgencyBadge = 'today';
                        } elseif ($parsedDate->isTomorrow()) {
                            $urgencyBadge = 'tomorrow';
                        }
                    } catch (\Exception $e) {
                        $dischargeDate = 'Invalid Date';
                    }
                }

                // Facility
                if (!empty($notes['facility'])) {
                    $facilityName = $notes['facility'];
                }
            }

            // Steps & Progress
            $totalSteps     = $referral->workflow->stages->flatMap->steps->count();
            $completedSteps = $referral->progress->where('status', 'completed')->count();
            $progressPercent = $totalSteps ? round(($completedSteps / $totalSteps) * 100) : 0;

            // Determine current step
            $remainingSteps = $referral->workflow->stages->flatMap->steps->filter(function ($step) use ($referral) {
                return !$referral->progress
                                ->where('workflow_step_id', $step->id)
                                ->where('status', 'completed')
                                ->count();
            });
            $currentStep = $remainingSteps->first()?->name ?? 'All steps completed';

            // Attach computed data as properties on the referral model
            $referral->patient_name     = $patientName;
            $referral->facility_name    = $facilityName;
            $referral->discharge_date   = $dischargeDate ?? '—';
            $referral->urgency_badge    = $urgencyBadge;
            $referral->progress_percent = $progressPercent;
            $referral->completed_steps  = $completedSteps;
            $referral->total_steps      = $totalSteps;
            $referral->current_step     = $currentStep;

            return $referral;
        });

        // 4) Sort In Memory
        $referrals = $this->sortCollection($referrals);

        // 5) Paginate the sorted collection
        $paginated = $this->paginateCollection($referrals, 10);

        return view('livewire.referrals-table', [
            'referrals' => $paginated,
        ]);
    }

    /**
     * Sort the in-memory collection based on $sortField / $sortDirection.
     */
    protected function sortCollection(Collection $referrals): Collection
    {
        // If you want numeric sorting for certain columns, you can
        // adapt the logic below. Here, we do a basic string/alpha sort.

        if ($this->sortDirection === 'asc') {
            return $referrals->sortBy($this->sortField, SORT_REGULAR, false);
        } else {
            return $referrals->sortByDesc($this->sortField);
        }
    }

    /**
     * Paginate a Laravel Collection.
     */
    protected function paginateCollection(Collection $collection, int $perPage)
    {
        $page  = request()->get('page', 1);
        $items = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}
