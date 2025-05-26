<div
    class="max-w-4xl mx-auto form-step-container"
    x-data="{
        step: 1,
        total: 5,
        titles: [
            'Patient Info',
            'Referring Provider',
            'Reason & Diagnosis',
            'Clinical Summary',
            'Clinical Docs'
        ]
    }"
>
    {{-- =================== PAGE TITLE =================== --}}
    <h2 class="text-2xl font-bold text-green-700 mb-6">
        Gastroenterology Referral Submission
    </h2>

    {{-- =================== PROGRESS & NAV =================== --}}
    <div class="mb-8">
        {{-- progress bar --}}
        <div class="progress-container">
            <div  class="progress-fill"
                  :style="`width: ${(step / total) * 100}%`">
                <span class="progress-text"
                      x-text="titles[step-1]"></span>
            </div>
        </div>

        {{-- clickable labels --}}
        <nav class="flex flex-wrap justify-between gap-2 mt-2 text-xs font-semibold text-gray-500">
            <template x-for="(label, idx) in titles" :key="idx">
                <span
                    @click="step = idx + 1"
                    class="cursor-pointer transition"
                    :class="{ 'text-green-700 font-bold': idx + 1 === step }"
                    x-text="label">
                </span>
            </template>
        </nav>
    </div>

    {{-- =================== FORM =================== --}}
    <form method="POST"
          action="{{ route('referral.store') }}"
          enctype="multipart/form-data">
        @csrf

        {{-- ───────── STEP 1 | PATIENT INFO ───────── --}}
        <section x-show="step === 1" x-cloak x-transition>
            <h3 class="form-step-title">Patient Identification</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div>
                    <label class="form-label">Last Name <span class="required-asterisk">*</span></label>
                    <input type="text" name="last_name" class="form-input-field" required>
                </div>
                <div>
                    <label class="form-label">First Name <span class="required-asterisk">*</span></label>
                    <input type="text" name="first_name" class="form-input-field" required>
                </div>
                <div>
                    <label class="form-label">Date of Birth <span class="required-asterisk">*</span></label>
                    <input type="date" name="dob" class="form-input-field" required>
                </div>
                <div>
                    <label class="form-label">Gender <span class="required-asterisk">*</span></label>
                    <select name="gender" class="form-input-field" required>
                        <option value="">Select Gender</option>
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Height (in)</label>
                    <input type="text" name="height" class="form-input-field">
                </div>
                <div>
                    <label class="form-label">Weight (lbs)</label>
                    <input type="text" name="weight" class="form-input-field">
                </div>
                <div>
                    <label class="form-label">Primary Phone <span class="required-asterisk">*</span></label>
                    <input type="text" name="primary_phone" class="form-input-field" required>
                </div>
            </div>

            <h3 class="form-step-title">Emergency Contact</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div>
                    <label class="form-label">Emergency Contact</label>
                    <input type="text" name="emergency_contact" class="form-input-field">
                </div>
                <div>
                    <label class="form-label">Emergency Phone</label>
                    <input type="text" name="emergency_phone" class="form-input-field">
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Interpreter Needed</label>
                    <select name="interpreter" class="form-input-field">
                        <option value="">Select</option>
                        <option>No</option>
                        <option>Hearing Impaired</option>
                        <option>Spanish</option>
                        <option>French</option>
                    </select>
                </div>
            </div>

            <h3 class="form-step-title">Insurance & Authorization</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Insurance Plan</label>
                    <input type="text" name="insurance_plan" class="form-input-field">
                </div>
                <div>
                    <label class="form-label">Auth / Referral #</label>
                    <input type="text" name="auth_number" class="form-input-field">
                </div>
            </div>

            <div class="mt-6 text-right">
                <button type="button" class="btn-green" @click="step++">Next</button>
            </div>
        </section>

        {{-- ───────── STEP 2 | REFERRING PROVIDER ───────── --}}
        <section x-show="step === 2" x-cloak x-transition>
            <h3 class="form-step-title">Referring Provider</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Referring Provider Name <span class="required-asterisk">*</span></label>
                    <input type="text" name="referring_physician" class="form-input-field" required>
                </div>
                <div>
                    <label class="form-label">Facility <span class="required-asterisk">*</span></label>
                    <input type="text" name="referring_facility" class="form-input-field" required>
                </div>
                <div>
                    <label class="form-label">Phone <span class="required-asterisk">*</span></label>
                    <input type="text" name="referring_phone" class="form-input-field" required>
                </div>
                <div>
                    <label class="form-label">Fax</label>
                    <input type="text" name="referring_fax" class="form-input-field">
                </div>
                <div>
                    <label class="form-label">NPI Number</label>
                    <input type="text" name="referring_npi" maxlength="10" class="form-input-field">
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" class="btn-gray" @click="step--">Previous</button>
                <button type="button" class="btn-green" @click="step++">Next</button>
            </div>
        </section>

        {{-- ───────── STEP 3 | REASON & DIAGNOSIS ───────── --}}
        <section x-show="step === 3" x-cloak x-transition>
            <h3 class="form-step-title">Reason & Diagnosis</h3>
            <div class="mb-4">
                <label class="form-label">Reason for Referral <span class="required-asterisk">*</span></label>
                <textarea name="reason" class="form-textarea-field" required></textarea>
            </div>
            <div class="mb-4">
                <label class="form-label">Diagnosis / ICD Code <span class="required-asterisk">*</span></label>
                <input type="text" name="diagnosis" class="form-input-field" required>
            </div>
            <div class="mb-4">
                <label class="form-label">GI Procedure(s) <span class="required-asterisk">*</span></label>
                <select name="gi_procedures[]" multiple class="form-input-field h-40" required>
                    @foreach([
                        'EGD','Colonoscopy','EGD with Biopsy','Colonoscopy with Biopsy',
                        'Colonoscopy with Polypectomy','Flexible Sigmoidoscopy','PEG Tube Placement',
                        'Esophageal Dilation','Bravo pH Monitoring','Esophageal Manometry',
                        'Band Ligation','Hemorrhoid Treatment','Capsule Endoscopy','ERCP','Other'
                    ] as $option)
                        <option>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" class="btn-gray" @click="step--">Previous</button>
                <button type="button" class="btn-green" @click="step++">Next</button>
            </div>
        </section>

        {{-- ───────── STEP 4 | CLINICAL SUMMARY ───────── --}}
        <section x-show="step === 4" x-cloak x-transition>
            <h3 class="form-step-title">Clinical Summary</h3>
            <label class="form-label">Summary / Notes <span class="required-asterisk">*</span></label>
            <textarea name="clinical_summary" class="form-textarea-field" required></textarea>

            <div class="mt-6 flex justify-between">
                <button type="button" class="btn-gray" @click="step--">Previous</button>
                <button type="button" class="btn-green" @click="step++">Next</button>
            </div>
        </section>

        {{-- ───────── STEP 5 | CLINICAL DOCS ───────── --}}
        <section x-show="step === 5" x-cloak x-transition>
            <h3 class="form-step-title">Clinical Documentation</h3>
            <div class="space-y-4 max-w-md">
                <div>
                    <label class="form-label">Recent Labs</label>
                    <input type="file" name="labs[]" multiple class="form-input-field">
                </div>
                <div>
                    <label class="form-label">Imaging / Radiology</label>
                    <input type="file" name="imaging[]" multiple class="form-input-field">
                </div>
                <div>
                    <label class="form-label">Office / Consult Note</label>
                    <input type="file" name="note[]" multiple class="form-input-field">
                </div>
                <div>
                    <label class="form-label">Medication List / Other Docs</label>
                    <input type="file" name="other[]" multiple class="form-input-field">
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" class="btn-gray" @click="step--">Previous</button>
                <button type="submit" class="btn-green">Submit Referral</button>
            </div>
        </section>
    </form>

    {{-- =================== STYLES =================== --}}
    <style>
        /* hide until Alpine boots */
        [x-cloak]{display:none!important;}

        /* === Container === */
        .form-step-container {
            background-color: #f9fafb;
            border: 1px solid #d1fae5;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease-in-out;
        }
        .form-step-container:hover {
            transform: scale(1.01);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
        }

        /* === Titles & Labels === */
        .form-step-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #065f46;
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .required-asterisk {
            color: #dc2626;
        }

        /* === Inputs & Textareas === */
        .form-input-field,
        .form-textarea-field {
            width: 100%;
            border-radius: 0.375rem;
            border: 1px solid #d1fae5;
            background-color: #ffffff;
            color: #111827;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            transition: border-color 0.2s ease;
        }
        .form-input-field:focus,
        .form-textarea-field:focus {
            outline: none;
            border-color: #16a34a;
            box-shadow: 0 0 0 1px #16a34a;
        }

        /* === Buttons === */
        .btn-green {
            background-color: #16a34a;
            color: #ffffff;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .btn-green:hover { background-color: #15803d; }

        .btn-gray {
            background-color: #e5e7eb;
            color: #374151;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .btn-gray:hover { background-color: #d1d5db; }

        /* === Progress bar === */
        .progress-container{
            background:#d1fae5;
            border:1px solid #a7f3d0;
            border-radius:9999px;
            height:1.25rem;
            overflow:hidden;
        }
        .progress-fill{
            background:#16a34a;
            height:100%;
            display:flex;
            align-items:center;
            justify-content:center;
            white-space:nowrap;
            transition:width 0.3s ease;
        }
        .progress-text{
            font-size:0.75rem;
            font-weight:600;
            color:#ffffff;
            padding:0 0.5rem;
        }
    </style>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
