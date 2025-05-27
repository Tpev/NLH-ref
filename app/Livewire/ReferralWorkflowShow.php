<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Referral;
use App\Models\ReferralProgress;
use Illuminate\Support\Facades\Auth;
use App\Models\UploadedFile;
use App\Models\StepComment;
use App\Models\User;
use App\Notifications\StepCompletedNotification;
use Illuminate\Support\Facades\Http;




class ReferralWorkflowShow extends Component
{
    use WithFileUploads;
 public array $intakeData = [];
    /** The ID of the referral we’re viewing. */
    public $referralId;

    /** The referral instance loaded from the DB. */
    public $referral;

    /** For Decision Step: selected answers [stepId => 'Yes'/'No'/etc.] */
    public $decisionAnswers = [];

    /** For Checkbox Step: [stepId => bool] */
    public $checkboxAnswers = [];

    /** For Upload Step: [stepId => [files]] */
    public $uploadFiles = [];

    /** For Form Step: track editing + store responses */
    public $editingFormStep = null;
    public $formAnswers = [];

    /** For Comments */
    public $newComment = [];
    public $showCommentsForStep = null;
	/** For MedRec */
	public $finalMeds = []; 
/** For Med-Rec raw lists */
public string $facilityList = '';
public string $epicList     = '';
public array $mlSuggestions = [];

    /**
     * Mount is called when this component is initialized.
     */
public function mount($referralId)
{
    $this->referralId = $referralId;

    $this->referral = Referral::with([
        'workflow.stages.steps',
        'progress.step',
        'progress.uploadedFiles',
        'uploadedFiles',
        'comments.user',
    ])->findOrFail($referralId);

    /* keep the full JSON for later */
    $this->intakeData = json_decode($this->referral->form_data, true) ?? [];

    /* you can still pull just the summary for the NLP call */
    $summary = $this->intakeData['clinical_summary'] ?? '';

    // Call NLP API as you already do …
    $response = Http::timeout(10)->post('http://localhost:8001/analyze', [
        'text' => $summary
    ]);

    /* unchanged ↓ */
    if ($response->successful()) {
        $matches = $response->json('matches') ?? [];
        $this->mlSuggestions = [];

        foreach ($matches as $match) {
            $criterion = $match['criterion'];
            $this->mlSuggestions[$criterion] = [
                'value'  => $match['negated'] ? 'no' : 'yes',
                'reason' => $match['context_sentence'],
            ];
        }
    }
}


    /**
     * Reload referral relationships
     */
    private function loadReferral()
    {
        $this->referral = Referral::with([
            'workflow.stages.steps',
            'progress.step',
            'progress.uploadedFiles',
            'uploadedFiles',
        ])->findOrFail($this->referralId);
    }

    /**
     * Checks if current user can "write" this step
     */
    private function userCanWriteStep($stepId)
    {
        // 1) Find that step from the loaded referral’s workflow
        $step = $this->referral
            ->workflow
            ->stages
            ->flatMap->steps
            ->where('id', $stepId)
            ->first();

        if (!$step) {
            return false;
        }

        // 2) If user model has e.g. $user->group = ['admin','nurse',...]
        $userGroups   = Auth::user()->group ?? [];
        $stepWriteArr = $step->group_can_write ?? [];

        // Intersection => user can write
        return !empty(array_intersect($userGroups, $stepWriteArr));
    }

public function saveDecision($stepId)
{
    // Retrieve the step from the current referral's workflow
    $step = $this->referral->workflow
        ->stages
        ->flatMap->steps
        ->where('id', $stepId)
        ->first();

    if (!$this->userCanWriteStep($stepId)) {
        session()->flash('error', 'You do not have permission to complete this decision.');
        return;
    }

    $chosen = $this->decisionAnswers[$stepId] ?? null;
    if (!$chosen) {
        session()->flash('error', 'Please select an option.');
        return;
    }

    // Create or update the progress record
    $progress = ReferralProgress::updateOrCreate(
        [
            'referral_id'      => $this->referral->id,
            'workflow_step_id' => $stepId,
        ],
        [
            'status'        => 'completed',
            'completed_by'  => Auth::id() ?: null,
            'completed_at'  => now(),
            'notes'         => "{$chosen}",
        ]
    );

    // Notify users only if it was just created (not an update)
    if ($progress->wasRecentlyCreated) {
        $users = User::all()->filter(function ($user) use ($step) {
            return !empty(array_intersect($user->group, $step->group_get_notif));
        });

        foreach ($users as $user) {
            $user->notify(new StepCompletedNotification(
                referralId: $this->referral->id,
                stepId: $stepId,
                message: "'{$step->name}' completed: {$chosen}"
            ));
        }

        session()->flash('success', 'Decision saved and notifications sent successfully.');
    } else {
        session()->flash('success', 'Decision updated successfully.');
    }

    $this->editingFormStep = null;
    $this->loadReferral();
    unset($this->decisionAnswers[$stepId]);
}

public function saveCheckbox($stepId)
{
    if (!$this->userCanWriteStep($stepId)) {
        session()->flash('error', 'You do not have permission to complete this checkbox step.');
        return;
    }

    $checked = $this->checkboxAnswers[$stepId] ?? false;

    if (!$checked) {
        session()->flash('error', 'Please check the box before confirming.');
        return;
    }

    // Remove any previous progress for this step on this referral
    ReferralProgress::where('referral_id', $this->referral->id)
        ->where('workflow_step_id', $stepId)
        ->delete();

    // Save new completed progress
    ReferralProgress::create([
        'referral_id'      => $this->referral->id,
        'workflow_step_id' => $stepId,
        'status'           => 'completed',
        'completed_by'     => Auth::id(),
        'completed_at'     => now(),
        'notes'            => 'Checkbox marked done',
    ]);

    // Optionally send notifications
    $step = $this->referral->workflow
        ->stages
        ->flatMap->steps
        ->where('id', $stepId)
        ->first();

    $users = User::all()->filter(function ($user) use ($step) {
        return !empty(array_intersect($user->group, $step->group_get_notif ?? []));
    });

    foreach ($users as $user) {
        $user->notify(new StepCompletedNotification(
            referralId: $this->referral->id,
            stepId: $stepId,
            message: "'{$step->name}' marked done"
        ));
    }

    // Refresh UI state
    $this->loadReferral();
    unset($this->checkboxAnswers[$stepId]);
    $this->editingFormStep = null;

    session()->flash('success', 'Checkbox task saved and marked completed.');
}


    // =======================
    // UPLOAD STEP
    // =======================
    public function saveUpload($stepId)
    {
        if (!$this->userCanWriteStep($stepId)) {
            session()->flash('error', 'You do not have permission to upload files for this step.');
            return;
        }

        $files = $this->uploadFiles[$stepId] ?? [];
        if (empty($files)) {
            session()->flash('error', 'Please select at least one file to upload.');
            return;
        }

        // Retrieve the step
        $step = $this->referral->workflow
            ->stages
            ->flatMap->steps
            ->where('id', $stepId)
            ->first();

        if (!$step) {
            session()->flash('error', 'Invalid step.');
            return;
        }

        $metadata      = $step->metadata ?? [];
        $allowedMimes  = $metadata['allowed_mimes'] ?? ['pdf', 'jpg', 'png'];
        $maxFiles      = $metadata['max_files'] ?? 5;
        $maxSize       = $metadata['max_size'] ?? 2048; // kilobytes

        // Validate
        $this->validate([
            "uploadFiles.{$step->id}.*" => "mimes:" . implode(',', $allowedMimes) . "|max:{$maxSize}",
        ], [
            "uploadFiles.{$step->id}.*.mimes" => "Only " . implode(', ', $allowedMimes) . " files are allowed.",
            "uploadFiles.{$step->id}.*.max"   => "Each file must not exceed {$maxSize}KB.",
        ]);

        if (count($files) > $maxFiles) {
            session()->flash('error', "You can upload a maximum of {$maxFiles} files.");
            return;
        }

        // Create a progress record
        $progress = ReferralProgress::create([
            'referral_id'      => $this->referral->id,
            'workflow_step_id' => $stepId,
            'status'           => 'completed',
            'completed_by'     => Auth::id() ?: null,
            'completed_at'     => now(),
            'notes'            => "Uploaded " . count($files) . " file(s).",
        ]);

        // Store each file
        foreach ($files as $file) {
            $filename = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('uploads', $filename, 'public');

            UploadedFile::create([
                'referral_id'          => $this->referral->id,
                'referral_progress_id' => $progress->id,
                'original_name'        => $file->getClientOriginalName(),
                'path'                 => $path,
            ]);
        }

        $this->referral->refresh();
        unset($this->uploadFiles[$stepId]);

        session()->flash('success', 'Files uploaded successfully.');
    }

    // =======================
    // FORM STEP
    // =======================
    public function editForm($stepId)
    {
        $this->editingFormStep = $stepId;

        $progress = $this->referral->progress()
            ->where('workflow_step_id', $stepId)
            ->where('status', 'completed')
            ->latest()
            ->first();

        if ($progress && $progress->notes) {
            $this->formAnswers[$stepId] = json_decode($progress->notes, true);
        }
    }

    public function saveForm($stepId)
    {
        if (!$this->userCanWriteStep($stepId)) {
            session()->flash('error', 'You do not have permission to modify this form.');
            return;
        }

        $step = $this->referral->workflow
            ->stages
            ->flatMap->steps
            ->where('id', $stepId)
            ->first();

        if (!$step) {
            session()->flash('error', 'Invalid step.');
            return;
        }

        $responses = $this->formAnswers[$stepId] ?? [];

        // Optionally validate $responses with the step's metadata
        ReferralProgress::updateOrCreate(
            [
                'referral_id'      => $this->referral->id,
                'workflow_step_id' => $stepId,
            ],
            [
                'status'       => 'completed',
                'completed_by' => Auth::id(),
                'completed_at' => now(),
                'notes'        => json_encode($responses),
            ]
        );

        $this->editingFormStep = null;
        $this->referral->refresh();

        session()->flash('success', 'Form information saved successfully.');
    }

    // =======================
    // COMMENTS
    // =======================
    public function toggleComments($stepId)
    {
        $this->showCommentsForStep = ($this->showCommentsForStep === $stepId) ? null : $stepId;
    }

    public function addComment($stepId)
    {
        // If you want to require write permission for posting comments:
        if (!$this->userCanWriteStep($stepId)) {
            session()->flash('error', 'You do not have permission to comment on this step.');
            return;
        }

        $commentContent = $this->newComment[$stepId] ?? '';

        if (empty(trim($commentContent))) {
            session()->flash('error', 'Comment cannot be empty.');
            return;
        }

        StepComment::create([
            'workflow_step_id' => $stepId,
            'referral_id'      => $this->referralId,
            'user_id'          => Auth::id(),
            'comment'          => $commentContent,
        ]);

        $this->newComment[$stepId] = '';
        $this->referral->refresh();

        session()->flash('success', 'Comment added successfully.');
    }

    // =======================
    // RENDER
    // =======================
    public function render()
    {
        $this->loadReferral();

        return view('livewire.referral-workflow-show', [
            'referral'       => $this->referral,
            'stepProgresses' => $this->referral->progress,
			'intakeData'     => $this->intakeData, 
        ]);
    }
public function saveMedRec($stepId)
{
    /* ───── 1. Guard clauses ───── */
    if (!$this->userCanWriteStep($stepId)) {
        session()->flash('error', 'You do not have permission to modify this medication reconciliation.');
        return;
    }

    $step = $this->referral->workflow
        ->stages->flatMap->steps
        ->firstWhere('id', $stepId);

    if (!$step) {
        session()->flash('error', 'Invalid step.');
        return;
    }

    /* ───── 2. Normalise & dedupe pills ───── */
    $deduped = [];
    foreach (($this->finalMeds ?? []) as $item) {
        $text = trim($item['text'] ?? '');
        if (!$text || isset($deduped[$text])) {
            continue;
        }
        $deduped[$text] = [
            'text' => $text,
            'pmp'  => !empty($item['pmp']),
        ];
    }

    /* ───── 3. Persist to referral_progress ───── */
    ReferralProgress::updateOrCreate(
        [
            'referral_id'      => $this->referral->id,
            'workflow_step_id' => $stepId,
        ],
        [
            'status'       => 'completed',
            'completed_by' => Auth::id(),
            'completed_at' => now(),
            'notes'        => json_encode([
                'final_meds'    => array_values($deduped),
                'facility_list' => $this->facilityList ?? '',
                'epic_list'     => $this->epicList ?? '',
            ]),
        ]
    );

    /* ───── 4. House-keeping ───── */
    $this->editingFormStep = null;
    $this->loadReferral();
    session()->flash('success', 'Medication reconciliation completed successfully.');
}

public function editMedRec($stepId)
{
    $this->editingFormStep = $stepId;   // reuse the same flag the Blade checks

    $progress = $this->referral->progress()
        ->where('workflow_step_id', $stepId)
        ->where('status', 'completed')
        ->latest()->first();

    if ($progress && $progress->notes) {
        $saved              = json_decode($progress->notes, true);
        $this->finalMeds    = $saved['final_meds']    ?? [];
        $this->facilityList = $saved['facility_list'] ?? '';
        $this->epicList     = $saved['epic_list']     ?? '';
    }

    /* tell the browser to repaint the diff grid */
 
}


public $notifyData = []; // [stepId => [family_name, family_email]]


public function sendFamilyNotification($stepId)
{
    $data = $this->notifyData[$stepId] ?? [];
    $name  = $data['family_name'] ?? null;
    $email = $data['family_email'] ?? null;
    $note  = $data['custom_note'] ?? null;

    if (!$name || !$email) {
        session()->flash('error', 'Name and Email are required.');
        return;
    }

    // Save progress
    $progress = $this->referral->progress()->create([
        'workflow_step_id' => $stepId,
        'user_id'          => auth()->id(),
        'status'           => 'completed',
        'completed_at'     => now(),
        'notes'            => json_encode([
            'family_name'  => $name,
            'family_email' => $email,
            'custom_note'  => $note,
        ]),
    ]);

    // Send the email (you should implement this mailable if not already)
  //  \Mail::to($email)->send(new \App\Mail\NotifyFamilyMailable($this->referral, $name, $note));

    session()->flash('success', 'Notification email sent to family.');
}

}
