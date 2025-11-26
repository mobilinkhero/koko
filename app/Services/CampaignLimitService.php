<?php

namespace App\Services;

/**
 * Service to handle campaign conversation limits
 */
class CampaignLimitService
{
    protected $featureService;

    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * Check how many contacts can receive campaign based on conversation limits
     *
     * @param  array  $contactList  Array of contacts with id, type, etc.
     * @param  int  $tenantId  The tenant ID
     * @return array Result with eligible contacts, skipped contacts, and slot info
     */
    public function getEligibleCampaignContacts($contactList, $tenantId)
    {
        $tenant_subdomain = tenant_subdomain_by_tenant_id($tenantId);

        // Get current usage and limits
        $currentUsage = $this->featureService->getCurrentUsage('conversations');
        $limit = $this->featureService->getLimit('conversations');
        $availableSlots = max(0, $limit - $currentUsage);

        $result = [
            'eligible_contacts' => [],
            'skipped_contacts' => [],
            'active_session_contacts' => [],
            'new_conversation_contacts' => [],
            'available_slots' => $availableSlots,
            'current_usage' => $currentUsage,
            'limit' => $limit,
            'slots_needed' => 0,
            'can_send_all' => false,
        ];

        // If no available slots, skip all
        if ($availableSlots <= 0) {
            $result['skipped_contacts'] = $contactList;
            app_log('Campaign: No available slots - all contacts skipped', 'warning', null, [
                'tenant_id' => $tenantId,
                'skipped_count' => count($contactList),
            ]);

            return $result;
        }

        $slotsUsed = 0;

        foreach ($contactList as $contact) {
            try {
                $contactId = $contact['id'] ?? $contact['contact_id'] ?? null;
                $contactType = $contact['type'] ?? 'lead';

                if (! $contactId) {
                    $result['skipped_contacts'][] = array_merge($contact, ['skip_reason' => 'Missing contact ID']);

                    continue;
                }

                // Check if contact has active session
                $hasActiveSession = $this->featureService->isConversationSessionActive(
                    $contactId,
                    $tenantId,
                    $tenant_subdomain,
                    $contactType
                );

                if ($hasActiveSession) {
                    // Can send without using new conversation slot
                    $result['eligible_contacts'][] = array_merge($contact, [
                        'requires_new_slot' => false,
                        'session_status' => 'active',
                    ]);
                    $result['active_session_contacts'][] = $contact;

                } else {
                    // Would need new conversation slot
                    if ($slotsUsed < $availableSlots) {
                        $result['eligible_contacts'][] = array_merge($contact, [
                            'requires_new_slot' => true,
                            'session_status' => 'new',
                        ]);
                        $result['new_conversation_contacts'][] = $contact;
                        $slotsUsed++;
                    } else {
                        $result['skipped_contacts'][] = array_merge($contact, [
                            'skip_reason' => 'No available conversation slots',
                            'session_status' => 'new_blocked',
                        ]);

                        app_log('Campaign: Contact skipped - no slots', 'warning', null, [
                            'contact_id' => $contactId,
                            'type' => $contactType,
                            'slots_used' => $slotsUsed,
                            'available_slots' => $availableSlots,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $result['skipped_contacts'][] = array_merge($contact, [
                    'skip_reason' => 'Error checking session: '.$e->getMessage(),
                    'session_status' => 'error',
                ]);

                app_log('Campaign: Error checking contact session', 'error', $e, [
                    'contact_id' => $contactId ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $result['slots_needed'] = $slotsUsed;
        $result['can_send_all'] = count($result['skipped_contacts']) === 0;

        return $result;
    }

    /**
     * Get campaign summary for UI display
     *
     * @param  array  $eligibilityResult  Result from getEligibleCampaignContacts
     * @return array UI-friendly summary
     */
    public function getCampaignSummary($eligibilityResult)
    {
        $total = count($eligibilityResult['eligible_contacts']) + count($eligibilityResult['skipped_contacts']);

        return [
            'total_contacts' => $total,
            'can_send_to' => count($eligibilityResult['eligible_contacts']),
            'will_skip' => count($eligibilityResult['skipped_contacts']),
            'active_sessions' => count($eligibilityResult['active_session_contacts']),
            'new_conversations' => count($eligibilityResult['new_conversation_contacts']),
            'conversation_slots_available' => $eligibilityResult['available_slots'],
            'conversation_slots_needed' => $eligibilityResult['slots_needed'],
            'current_usage' => $eligibilityResult['current_usage'],
            'limit' => $eligibilityResult['limit'],
            'can_send_all' => $eligibilityResult['can_send_all'],
            'limit_reached' => $eligibilityResult['available_slots'] <= 0,
            'warning_message' => $this->getWarningMessage($eligibilityResult),
        ];
    }

    /**
     * Get appropriate warning message for campaign
     *
     * @param  array  $eligibilityResult  Result from getEligibleCampaignContacts
     * @return string|null Warning message or null
     */
    protected function getWarningMessage($eligibilityResult)
    {
        if ($eligibilityResult['available_slots'] <= 0) {
            return 'Conversation limit reached. Cannot send to any new contacts. Upgrade your plan to continue.';
        }

        if (count($eligibilityResult['skipped_contacts']) > 0) {
            $skipped = count($eligibilityResult['skipped_contacts']);
            $total = count($eligibilityResult['eligible_contacts']) + $skipped;

            return 'Will send to '.count($eligibilityResult['eligible_contacts'])." of {$total} contacts. {$skipped} contacts skipped due to conversation limit.";
        }

        if (count($eligibilityResult['new_conversation_contacts']) > 0) {
            $newConv = count($eligibilityResult['new_conversation_contacts']);

            return "This campaign will use {$newConv} of your conversation limit.";
        }

        return null;
    }

    /**
     * Validate if campaign can proceed
     *
     * @param  array  $eligibilityResult  Result from getEligibleCampaignContacts
     * @param  bool  $allowPartial  Whether to allow partial sends
     * @return array Validation result
     */
    public function validateCampaign($eligibilityResult, $allowPartial = true)
    {
        $canProceed = false;
        $message = '';

        if (count($eligibilityResult['eligible_contacts']) === 0) {
            $message = 'Cannot send campaign. No eligible contacts available.';
        } elseif (! $eligibilityResult['can_send_all'] && ! $allowPartial) {
            $message = 'Cannot send to all contacts due to conversation limits. Enable partial sending or upgrade your plan.';
        } else {
            $canProceed = true;
            if (! $eligibilityResult['can_send_all']) {
                $message = 'Campaign will be sent to '.count($eligibilityResult['eligible_contacts']).' contacts. '.count($eligibilityResult['skipped_contacts']).' contacts skipped.';
            } else {
                $message = 'Campaign ready to send to all '.count($eligibilityResult['eligible_contacts']).' contacts.';
            }
        }

        return [
            'can_proceed' => $canProceed,
            'message' => $message,
            'eligible_count' => count($eligibilityResult['eligible_contacts']),
            'skipped_count' => count($eligibilityResult['skipped_contacts']),
        ];
    }
}
