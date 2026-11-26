<?php

namespace App\Http\Controllers;

use App\Models\MoodEntry;
use App\Models\Patient;
use Illuminate\Http\Request;

class MoodEntryController extends Controller
{
    // Store patient mood entry
    public function store(Request $request)
    {
        $request->validate([
            'mood_rating' => 'required|string',
            'reflection' => 'nullable|string',
        ]);

        // Create mood entry
        $moodEntry = new MoodEntry();

        $moodEntry->patient_id = $request->user()->patient->id;
        $moodEntry->mood_rating = $request->mood_rating;
        $moodEntry->reflection = $request->reflection;

        try {
            $moodEntry->save();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save mood entry.'
            ], 500);
        }

        return response()->json([
            'message' => 'Mood entry saved successfully.'
        ], 201);
    }

    // View patient's own mood entries
public function patientEntries(Request $request)
{
    // Find patient profile
    try {
        $patient = $request->user()->patient;
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error finding patient profile.'
        ], 500);
    }

    if (!$patient) {
        return response()->json([
            'message' => 'Patient profile not found.'
        ], 404);
    }

    // Retrieve mood entries
    try {
        $moodEntries = MoodEntry::where('patient_id', $patient->id)
            ->latest()
            ->get();
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error retrieving mood entries.'
        ], 500);
    }

    return response()->json([
        'mood_entries' => $moodEntries
    ], 200);
  }

  // View mood entries of an assigned patient
public function clinicianPatientEntries(Request $request, $patientId)
{
    // Find clinician profile
    try {
        $clinician = $request->user()->clinician;
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error finding clinician profile.'
        ], 500);
    }

    if (!$clinician) {
        return response()->json([
            'message' => 'Clinician profile not found.'
        ], 404);
    }

    // Find patient assigned to clinician
    try {
        $patient = Patient::where('id', $patientId)
            ->where('clinician_id', $clinician->id)
            ->first();
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error finding patient.'
        ], 500);
    }

    if (!$patient) {
        return response()->json([
            'message' => 'Patient not found.'
        ], 404);
    }

    // Retrieve patient's mood entries
    try {
        $moodEntries = MoodEntry::where('patient_id', $patient->id)
            ->latest()
            ->get();
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error retrieving mood entries.'
        ], 500);
    }

    return response()->json([
        'mood_entries' => $moodEntries
    ], 200);
  }
}
