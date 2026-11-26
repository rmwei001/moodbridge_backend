<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;

class PatientController extends Controller
{
    protected function getClinician(Request $request)
    {
        return $request->user()->clinician;
    }

    public function addPatient(Request $request)
    {
        $clinician = $this->getClinician($request);
        if (!$clinician) {
            return response()->json([
                'message' => 'Clinician profile not found'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:patients,email|unique:users,email',
            'phone' => 'required|string|max:20',
            'medication_information' => 'nullable|string',
            'therapy_start_date' => 'nullable|date'
        ]);

        try {
            // Create patient
            $patient = new Patient();
            $patient->clinician_id = $clinician->id;
            $patient->name = $request->name;
            $patient->email = $request->email;
            $patient->phone = $request->phone;
            $patient->medication_information = $request->medication_information;
            $patient->therapy_start_date = $request->therapy_start_date;
            $patient->save();

            // Send OTP to patient's email
            $patient->sendOtp();

            return response()->json([
                'message' => 'Patient registered successfully. Verification code sent to email.',
                'patient' => $patient
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to register patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function viewPatients(Request $request)
    {
        $clinician = $this->getClinician($request);
        if (!$clinician) {
            return response()->json([
                'message' => 'Clinician profile not found'
            ], 403);
        }

        try {
            $patients = Patient::where('clinician_id', $clinician->id)->get();
            return response()->json([
                'message' => 'Patients retrieved successfully',
                'patients' => $patients
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve patients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function viewPatient(Request $request, $id)
    {
        $clinician = $this->getClinician($request);
        if (!$clinician) {
            return response()->json([
                'message' => 'Clinician profile not found'
            ], 403);
        }

        try {
            $patient = Patient::where('id', $id)
                ->where('clinician_id', $clinician->id)
                ->first();

            if (!$patient) {
                return response()->json([
                    'message' => 'Patient not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Patient retrieved successfully',
                'patient' => $patient
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePatient(Request $request, $id)
    {
        $clinician = $this->getClinician($request);
        if (!$clinician) {
            return response()->json([
                'message' => 'Clinician profile not found'
            ], 403);
        }

        $patient = Patient::where('id', $id)
            ->where('clinician_id', $clinician->id)
            ->first();

        if (!$patient) {
            return response()->json([
                'message' => 'Patient not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:patients,email,' . $id,
            'phone' => 'sometimes|required|string|max:20',
            'medication_information' => 'sometimes|nullable|string',
            'therapy_start_date' => 'sometimes|nullable|date'
        ]);

        try {
            if ($request->has('name')) {
                $patient->name = $request->name;
            }

            if ($request->has('email')) {
                $patient->email = $request->email;
            }

            if ($request->has('phone')) {
                $patient->phone = $request->phone;
            }

            if ($request->has('medication_information')) {
                $patient->medication_information = $request->medication_information;
            }

            if ($request->has('therapy_start_date')) {
                $patient->therapy_start_date = $request->therapy_start_date;
            }

            $patient->save();

            return response()->json([
                'message' => 'Patient information updated successfully',
                'patient' => $patient
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update patient information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deactivatePatient(Request $request, $id)
    {
        $clinician = $this->getClinician($request);
        if (!$clinician) {
            return response()->json([
                'message' => 'Clinician profile not found'
            ], 403);
        }

        try {
            $patient = Patient::where('id', $id)
                ->where('clinician_id', $clinician->id)
                ->first();

            if (!$patient) {
                return response()->json([
                    'message' => 'Patient not found'
                ], 404);
            }

            $patient->status = 'inactive';
            $patient->save();

            return response()->json([
                'message' => 'Patient deactivated successfully',
                'patient' => $patient
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to deactivate patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
