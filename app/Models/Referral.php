<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;
    protected $appends = [
        'patient_name',
        'dob',
        'procedure',            
    ];
    protected $fillable = [
        'workflow_id',
        // 'patient_id', 'status', etc. if needed
        'status',
		'form_data',
    ];
protected $casts = [
    'form_data' => 'array',
];
    /**
     * A referral is attached to a particular workflow.
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * A referral can have many progress records.
     */
    public function progress()
    {
        return $this->hasMany(ReferralProgress::class);
    }
    /**
     * Relationship to UploadedFile.
     */
    public function uploadedFiles()
    {
        return $this->hasMany(UploadedFile::class);
    }
	public function comments()
{
    return $this->hasMany(StepComment::class);
}

    public function getPatientNameAttribute(): string
    {
        /* form_data is stored as a JSON string:
           {
               "first_name": "John",
               "last_name":  "Doe",
               ...other keys
           }
        */
        $data = json_decode($this->form_data ?? '', true) ?: [];

        $first = $data['first_name'] ?? '';
        $last  = $data['last_name']  ?? '';

        return trim("$first $last") ?: '—';
    }
	

public function getProcedureAttribute(): string
{
    // 1) Decode the JSON safely
    $data = json_decode($this->form_data ?? '[]', true) ?: [];

    // 2) Pull the value out (string|array|null)
    $procs = $data['gi_procedures'] ?? [];

    // 3) Make sure it’s an array
    $procs = is_array($procs) ? $procs : [$procs];

    // 4) Filter empty items and join with commas
    $list = array_filter($procs);
    
    return $list ? implode(', ', $list) : '—';
}

    public function getDobAttribute(): string
    {
        $data = json_decode($this->form_data ?? '', true) ?: [];

        $dob = $data['dob'] ?? '';
        

        return trim("$dob") ?: '—';
    }
}
