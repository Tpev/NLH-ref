<?php

namespace App\Livewire\Referrals;

use App\Models\Referral;
use App\Models\UploadedFile;
use Livewire\Component;
use Livewire\WithFileUploads;

class GiBookingFormComponent extends Component
{
    use WithFileUploads;

    public $form = [];
    public $attachments = [];

    public function mount()
    {
        $this->form = [
            'last_name' => '',
            'first_name' => '',
            'dob' => '',
            'gender' => '',
            'primary_phone' => '',
            'height' => '',
            'weight' => '',
            'emergency_contact' => '',
            'emergency_phone' => '',
            'interpreter' => '',
            'insurance_plan' => '',
            'auth_number' => '',
            'referring_physician' => '',
            'referring_facility' => '',
            'referring_phone' => '',
            'referring_fax' => '',
            'referring_npi' => '',
            'reason' => '',
            'diagnosis' => '',
            'gi_procedures' => [],
            'clinical_summary' => '',
        ];

        $this->attachments = [
            'labs' => [],
            'imaging' => [],
            'note' => [],
            'other' => [],
        ];
    }

    public function submit()
    {
        $this->validate([
            'form.last_name' => 'required',
            'form.first_name' => 'required',
            'form.dob' => 'required|date',
            'form.gender' => 'required',
            'form.primary_phone' => 'required',
            'form.referring_physician' => 'required',
            'form.referring_facility' => 'required',
            'form.referring_phone' => 'required',
            'form.reason' => 'required',
            'form.diagnosis' => 'required',
            'form.gi_procedures' => 'array|min:1',
            'form.clinical_summary' => 'required',
        ]);

        // Create referral
        $referral = Referral::create([
            'workflow_id' => 1, // you can make this dynamic if needed
            'status' => 'draft',
        ]);

        // Save all fields into notes or custom column if needed
        $referral->update([
            'notes' => json_encode($this->form),
        ]);

        // Save attachments
        foreach ($this->attachments as $type => $files) {
            foreach ($files as $file) {
                $path = $file->store("referrals/{$referral->id}/{$type}");
                UploadedFile::create([
                    'referral_id' => $referral->id,
                    'file_path' => $path,
                    'category' => $type,
                ]);
            }
        }


    session()->flash('success', 'Referral submitted successfully.');
	$this->redirectRoute('referrals.thank-you'); 
    return redirect()->route('referrals.thank-you');
    }

    public function render()
    {
        return view('livewire.referrals.gi-booking-form')
            ->layout('layouts.app'); // Ensure this Blade exists
    }
}
