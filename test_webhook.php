<?php
// Test WhatsApp E-commerce Bot Webhook
// Run this file to simulate WhatsApp messages

require_once 'vendor/autoload.php';

// Test webhook payload
$testPayload = [
    'entry' => [
        [
            'id' => '123456789',
            'changes' => [
                [
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => [
                            'display_phone_number' => '+1234567890',
                            'phone_number_id' => 'test_phone_id'
                        ],
                        'contacts' => [
                            [
                                'profile' => ['name' => 'Test User'],
                                'wa_id' => '+1987654321'
                            ]
                        ],
                        'messages' => [
                            [
                                'from' => '+1987654321',
                                'id' => 'test_msg_'.time(),
                                'timestamp' => time(),
                                'text' => ['body' => 'Show products'],  // Change this message
                                'type' => 'text'
                            ]
                        ]
                    ],
                    'field' => 'messages'
                ]
            ]
        ]
    ]
];

// Send to your webhook URL
$webhookUrl = 'https://yourdomain.com/subdomain/whatsapp/webhook';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testPayload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: WhatsApp/2.0'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

// Test different messages
$testMessages = [
    'Hi',
    'Show products', 
    'I want iPhone',
    'Add iPhone to cart',
    'My cart',
    'Checkout',
    'Order status',
    'Help'
];

foreach ($testMessages as $message) {
    $testPayload['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'] = $message;
    $testPayload['entry'][0]['changes'][0]['value']['messages'][0]['id'] = 'test_msg_'.time().rand(1000,9999);
    
    echo "\n=== Testing Message: '$message' ===\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhookUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testPayload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: WhatsApp/2.0'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Response: " . $response . "\n";
    sleep(2); // Wait between requests
}
?>
