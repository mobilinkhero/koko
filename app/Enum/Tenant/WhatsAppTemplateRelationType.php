<?php

namespace App\Enum\Tenant;

enum WhatsAppTemplateRelationType: string
{
    case ON_EXACT_MATCH = 'on exact match';
    case WHEN_MESSAGE_CONTAINS = 'when message contains';
    case WHEN_LEAD_OR_CLIENT_SEND_THE_FIRST_MESSAGE = 'when lead or client send the first message';
    case DEFAULT_REPLY = 'if any keyword does not match';
    case LEAD = 'lead';
    case CUSTOMER = 'customer';

    case PROFESSIONAL = 'professional';
    case FRIENDLY = 'friendly';
    case EMPATHETIC = 'empathetic';
    case STRAIGHTFORWARD = 'straightforward';

    public static function getReplyType(?int $id = null): array|string|null
    {
        $map = [
            1 => [
                'value' => self::ON_EXACT_MATCH,
                'label' => 'On Exact Match',
                'subtext' => 'Triggers only when the message exactly matches the keyword.',
            ],
            2 => [
                'value' => self::WHEN_MESSAGE_CONTAINS,
                'label' => 'When Message Contains',
                'subtext' => 'Triggers when the message contains specific words or phrases.',
            ],
            3 => [
                'value' => self::WHEN_LEAD_OR_CLIENT_SEND_THE_FIRST_MESSAGE,
                'label' => 'When Lead or Client Sends First Message',
                'subtext' => 'Triggers when a new lead or client starts a conversation.',
            ],
            4 => [
                'value' => self::DEFAULT_REPLY,
                'label' => 'Default Reply',
                'subtext' => 'Used when no other keyword matches the message.',
            ],
        ];

        return $id ? ($map[$id]['value']->value ?? null) : $map;
    }

    public static function getRelationtype(?int $type = null): array|string|null
    {
        $reply_type = [
            'lead' => self::LEAD,
            'customer' => self::CUSTOMER,
        ];

        if ($type !== null) {
            $keys = array_keys($reply_type);
            if ($type >= 0 && $type < count($keys) && array_key_exists($keys[$type], $reply_type)) {
                return $reply_type[$keys[$type]]->value;
            }

            return null;
        }

        return array_map(fn ($case) => $case->value, $reply_type);
    }

    public static function getAiChangeTone(?string $type = null): array|string|null
    {
        $changeTone = [
            'professional' => self::PROFESSIONAL,
            'friendly' => self::FRIENDLY,
            'empathetic' => self::EMPATHETIC,
            'straightforward' => self::STRAIGHTFORWARD,
        ];

        return $type ? ($changeTone[$type]->value ?? null) : array_map(fn ($case) => $case->value, $changeTone);
    }
}
