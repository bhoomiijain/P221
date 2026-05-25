<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Inertia\Inertia;

class PrescriptionPageController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Customer/Prescriptions', [
            'prescriptions' => Prescription::where('user_id', auth()->id())->latest()->get(),
        ]);
    }
}
