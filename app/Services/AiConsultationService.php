<?php

namespace App\Services;

use App\Models\AiConsultationLog;
use App\Models\CustomerOrder;

/**
 * Rule-based AI consultation simulation — architecture ready for LLM APIs.
 */
class AiConsultationService
{
    public function analyze(array $responses): array
    {
        $flags = [];
        $riskScore = 0;

        if (($responses['used_before'] ?? '') === 'no') {
            $riskScore += 15;
        }
        if (($responses['doctor_prescribed'] ?? '') === 'no') {
            $riskScore += 25;
            $flags[] = 'not_doctor_prescribed';
        }
        if (! empty($responses['allergies']) && strtolower($responses['allergies']) !== 'none') {
            $riskScore += 20;
            $flags[] = 'reported_allergies';
        }
        if (($responses['pregnant'] ?? '') === 'yes') {
            $riskScore += 30;
            $flags[] = 'pregnancy';
        }
        if (! empty($responses['medical_conditions']) && strtolower($responses['medical_conditions']) !== 'none') {
            $riskScore += 15;
            $flags[] = 'medical_conditions';
        }
        if (! empty($responses['other_medications']) && strtolower($responses['other_medications']) !== 'none') {
            $riskScore += 20;
            $flags[] = 'drug_interaction_risk';
        }
        if (($responses['side_effects_before'] ?? '') === 'yes') {
            $riskScore += 25;
            $flags[] = 'previous_side_effects';
        }
        if (in_array($responses['age_group'] ?? '', ['child', 'senior'], true)) {
            $riskScore += 10;
        }

        $level = match (true) {
            $riskScore >= 50 => 'high',
            $riskScore >= 25 => 'medium',
            default => 'low',
        };

        $approved = $level === 'low';
        $requiresReview = $level !== 'low';

        $message = match ($level) {
            'high' => 'Your responses indicate potential safety concerns. We strongly recommend consulting a doctor before proceeding. This order will be flagged for pharmacist review.',
            'medium' => 'Some responses need attention. Please consult your physician if unsure. Your order may require pharmacist verification.',
            default => 'Thank you! Your responses look safe. You may proceed with your order.',
        };

        return [
            'risk_level' => $level,
            'risk_score' => $riskScore,
            'risk_flags' => $flags,
            'approved' => $approved,
            'requires_pharmacist_review' => $requiresReview,
            'message' => $message,
            'suggest_doctor' => $level !== 'low',
        ];
    }

    public function logConsultation(string $userId, array $responses, array $analysis, ?string $orderId = null): AiConsultationLog
    {
        return AiConsultationLog::create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'responses' => $responses,
            'risk_level' => $analysis['risk_level'],
            'risk_flags' => $analysis['risk_flags'],
            'approved' => $analysis['approved'],
            'messages' => [
                ['role' => 'assistant', 'content' => $analysis['message']],
            ],
        ]);
    }

    public function defaultQuestions(): array
    {
        return [
            ['key' => 'used_before', 'question' => 'Have you used this medicine before?', 'options' => ['yes', 'no']],
            ['key' => 'doctor_prescribed', 'question' => 'Was this prescribed by a doctor?', 'options' => ['yes', 'no']],
            ['key' => 'allergies', 'question' => 'Do you have any allergies? (type or "none")', 'type' => 'text'],
            ['key' => 'consume_time', 'question' => 'What time do you usually take this medicine?', 'options' => ['morning', 'afternoon', 'evening', 'night', 'as_needed']],
            ['key' => 'pregnant', 'question' => 'Are you pregnant or breastfeeding?', 'options' => ['yes', 'no', 'not_applicable']],
            ['key' => 'medical_conditions', 'question' => 'Any medical conditions? (type or "none")', 'type' => 'text'],
            ['key' => 'other_medications', 'question' => 'Are you taking other medications?', 'type' => 'text'],
            ['key' => 'age_group', 'question' => 'Your age group?', 'options' => ['child', 'adult', 'senior']],
            ['key' => 'side_effects_before', 'question' => 'Have you experienced side effects with this medicine before?', 'options' => ['yes', 'no']],
        ];
    }
}
