<?php

namespace App\MergeFields\Tenant;

use App\Models\Tenant\Contact;
use App\Models\User;

class ContactMergeFields
{
    public function name(): string
    {
        return 'tenant-contact-group';
    }

    public function templates(): array
    {
        return [
            'tenant-new-contact-assigned',
        ];
    }

    public function build(): array
    {
        return [
            [
                'name' => 'Lead Status',
                'key' => '{lead_status}',
            ],
            [
                'name' => 'Lead Source',
                'key' => '{lead_source}',
            ],
            [
                'name' => 'Lead Assigned',
                'key' => '{lead_assigned}',
            ],
            [
                'name' => 'Contact First Name',
                'key' => '{contact_first_name}',
            ],
            [
                'name' => 'Contact Last Name',
                'key' => '{contact_last_name}',
            ],
            [
                'name' => 'Contact Company',
                'key' => '{contact_company}',
            ],
            [
                'name' => 'Contact Email',
                'key' => '{contact_email}',
            ],
            [
                'name' => 'Contact Phone Number',
                'key' => '{contact_phone_number}',
            ],
            [
                'name' => 'Contact Website',
                'key' => '{contact_website}',
            ],
            [
                'name' => 'Contact Type',
                'key' => '{contact_type}',
            ],
            [
                'name' => 'Assigned By',
                'key' => '{assigned_by}',
            ],
        ];
    }

    public function format(array $context): array
    {
        if (empty($context['contactId']) || is_null($context['contactId'])) {
            return [];
        }

        $tenant_id = $context['tenantId'] ?? 0;
        $tenant_subdomain = tenant_subdomain_by_tenant_id($tenant_id);
        $contact = Contact::fromTenant($tenant_subdomain)->findOrFail($context['contactId']);

        $addedFrom = User::select('firstname', 'lastname')->find($contact->addedfrom) ?? null;

        return [
            '{lead_status}' => $contact->status->name ?? '',
            '{lead_source}' => $contact->source->name ?? '',
            '{lead_assigned}' => ($contact->user->firstname ?? '').' '.($contact->user->lastname ?? ''),
            '{contact_first_name}' => $contact->firstname ?? '',
            '{contact_last_name}' => $contact->lastname ?? '',
            '{contact_company}' => $contact->company ?? '',
            '{contact_email}' => $contact->email ?? '',
            '{contact_phone_number}' => $contact->phone ?? '',
            '{contact_website}' => $contact->website ?? '',
            '{contact_type}' => $contact->type ?? '',
            '{assigned_by}' => ($addedFrom->firstname ?? '').' '.($addedFrom->lastname ?? ''),

        ];
    }
}
