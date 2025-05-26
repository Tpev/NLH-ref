<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-3xl font-bold text-gray-900">
                New GI Referral Submission
            </h2>

        </div>
    </x-slot>


        <!-- GI Booking Form -->
        <section class="bg-white shadow rounded-lg p-6">

            <livewire:referrals.gi-booking-form-component />
        </section>

        <!-- Optional Note -->
        <div class="text-sm text-gray-500">
            Need help? Contact <a href="mailto:referralsgi@nlh.com" class="underline text-green-700">referrals@yourdomain.com</a>
        </div>
    </div>
</x-app-layout>
