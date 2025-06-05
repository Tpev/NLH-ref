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

    public string  $search          = '';
    public string  $statusFilter    = 'all';
    public ?string $dischargeFilter = null;

    public string  $sortField       = 'id';
    public string  $sortDirection   = 'asc';

    protected $paginationTheme = 'tailwind';

    /* ───────── Filter hooks ───────── */
    public function updatingSearch()          { $this->resetPage(); }
    public function updatingStatusFilter()    { $this->resetPage(); }
    public function updatingDischargeFilter() { $this->resetPage(); }

    /* ───────── Sort toggler ───────── */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    /* ───────── Main render ───────── */
    public function render()
    {
        /* 1️⃣  Fetch + eager-load */
        $referrals = Referral::query()
            ->with(['workflow.stages.steps', 'progress.step'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('id', 'like', "%{$this->search}%")
                      ->orWhereHas('progress', fn ($p) =>
                          $p->where('notes', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter !== 'all',
                   fn ($q) => $q->where('status', $this->statusFilter))
            ->get();                   // discharge filter later

        /* 2️⃣  Discharge filter (in-memory) */
        if ($this->dischargeFilter) {
            $referrals = $referrals->filter(function ($r) {
                $notes = $this->notes($r);
                if (!isset($notes['date_of_discharge'])) {
                    return false;
                }

                $date = Carbon::parse($notes['date_of_discharge']);
                return match ($this->dischargeFilter) {
                    'today'    => $date->isToday(),
                    'tomorrow' => $date->isTomorrow(),
                    default    => false,
                };
            });
        }

        /* 3️⃣  Duplicate-key pass (full data-set) */
        $keyCounts = [];
        foreach ($referrals as $r) {
            [$nameKey, $dob] = $this->nameDobKey($r);
            if ($nameKey && $dob) {
                $keyCounts["$nameKey|$dob"] = ($keyCounts["$nameKey|$dob"] ?? 0) + 1;
            }
        }

        /* 4️⃣  Transform each referral */
        $referrals = $referrals->map(function ($r) use ($keyCounts) {

            $notes = $this->notes($r);

            /* Name & DoB */
            $first = trim($notes['first_name'] ?? '');
            $last  = trim($notes['last_name']  ?? '');
            $name  = ($first || $last) ? "{$first} {$last}" : '—';
            $dob   = $notes['dob'] ?? '—';

            /* Duplicate flag */
            [$nameKey, $dobVal] = $this->nameDobKey($r);
            $isDuplicate = $nameKey && $dobVal
                ? (($keyCounts["$nameKey|$dobVal"] ?? 0) > 1)
                : false;

            /* Discharge & urgency */
            $dischargeDate = '—';
            $urgencyBadge  = null;
            if (!empty($notes['date_of_discharge'])) {
                try {
                    $parsed = Carbon::parse($notes['date_of_discharge']);
                    $dischargeDate = $parsed->format('m/d/Y');
                    $urgencyBadge  = $parsed->isToday()
                                     ? 'today'
                                     : ($parsed->isTomorrow() ? 'tomorrow' : null);
                } catch (\Exception) { $dischargeDate = 'Invalid'; }
            }

            /* Progress metrics */
            $totalSteps     = $r->workflow->stages->flatMap->steps->count();
            $completedSteps = $r->progress->where('status', 'completed')->count();
            $progressPct    = $totalSteps ? round(($completedSteps / $totalSteps) * 100) : 0;

            /* Current step */
            $remaining = $r->workflow->stages->flatMap->steps->reject(fn ($s) =>
                $r->progress->where('workflow_step_id', $s->id)
                            ->where('status','completed')->count());
            $currentStep = $remaining->first()->name ?? 'All steps completed';

            /* Attach computed props */
            $r->patient_name     = $name;
            $r->patient_dob      = $dob;
            $r->is_duplicate     = $isDuplicate;
            $r->discharge_date   = $dischargeDate;
            $r->urgency_badge    = $urgencyBadge;
            $r->progress_percent = $progressPct;
            $r->completed_steps  = $completedSteps;
            $r->total_steps      = $totalSteps;
            $r->current_step     = $currentStep;

            return $r;
        });

        /* 5️⃣  Sort + paginate */
        $referrals = $this->sortCollection($referrals);
        $paginated = $this->paginateCollection($referrals, 10);

        return view('livewire.referrals-table', [
            'referrals' => $paginated,
        ]);
    }

    /* ───── Helpers ───── */

    /** Quick access to main notes array. */
    private function notes(Referral $r): array
    {
        $raw = $r->notes ?: optional($r->progress->first())->notes;
        return $raw ? json_decode($raw, true) : [];
    }

    /** Lower-case name key + dob (or [null,null]). */
    private function nameDobKey(Referral $r): array
    {
        $n = $this->notes($r);

        $first = strtolower(trim($n['first_name'] ?? ''));
        $last  = strtolower(trim($n['last_name']  ?? ''));
        $dob   = $n['dob'] ?? null;

        return ($first && $last && $dob) ? ["$first $last", $dob] : [null, null];
    }

    /** In-memory sort. */
    protected function sortCollection(Collection $c): Collection
    {
        return $this->sortDirection === 'asc'
            ? $c->sortBy($this->sortField, SORT_REGULAR, false)
            : $c->sortByDesc($this->sortField);
    }

    /** Paginate a collection. */
    protected function paginateCollection(Collection $c, int $perPage)
    {
        $page  = request()->get('page', 1);
        $items = $c->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items, $c->count(), $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
