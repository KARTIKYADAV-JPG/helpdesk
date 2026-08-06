<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure we have sufficient users for random assignment
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@helpdesk.com',
                'role' => 'admin',
            ]);
        }

        $agent = User::where('role', 'agent')->first();
        if (!$agent) {
            User::factory()->create([
                'name' => 'Agent User',
                'email' => 'agent@example.com',
                'role' => 'agent',
            ]);
        }

        // Create 20 more users with agent role to act as general creators/agents
        $users = User::factory()->count(20)->create(['role' => 'agent']);
        $allUsers = User::all();
        $agents = User::where('role', 'agent')->get();

        // 2. Realistic Ticket Templates mapped by Category
        $templates = [
            TicketCategory::BILLING->value => [
                [
                    'subject' => 'Double charge on subscription renewal',
                    'description' => 'Hello, I noticed that my card was charged twice for the monthly subscription on the 15th. Please refund the extra $29.00 charge. Thank you!'
                ],
                [
                    'subject' => 'Request invoice copy for order #88391',
                    'description' => 'Could you please send me a PDF invoice for order #88391? I need it to submit my expense report for this month. Thanks.'
                ],
                [
                    'subject' => 'Update credit card details failed',
                    'description' => 'I am trying to update my billing credit card on the payment settings page, but I keep getting a card validation error even though the card is active. Can you help?'
                ],
                [
                    'subject' => 'Request to cancel auto-renewal',
                    'description' => 'I would like to disable auto-renewal on my account. I plan to renew manually next year depending on project budgets. Please let me know once this is done.'
                ]
            ],
            TicketCategory::TECHNICAL_SUPPORT->value => [
                [
                    'subject' => 'Database connection timeout error',
                    'description' => 'Our staging server is throwing PDOException connection timeout errors when trying to connect to the pgsql database. Are there any known performance limits or server upgrades happening?'
                ],
                [
                    'subject' => 'API webhook payloads not receiving headers',
                    'description' => 'When our endpoint receives webhooks from your service, the custom signature header is missing. Is there a config setting we need to toggle in the developer portal?'
                ],
                [
                    'subject' => 'Image resizing service returns 502 Bad Gateway',
                    'description' => 'We are getting 502 errors when uploading profile images larger than 5MB. The image resizing service seems to time out. Please investigate.'
                ],
                [
                    'subject' => 'SSL certificate renewal error on custom domain',
                    'description' => 'We added a custom domain helpdesk.ourcompany.com, but the SSL certificate generation has been stuck in the pending state for the past 24 hours. Can you trigger a manual renewal?'
                ]
            ],
            TicketCategory::LOGIN->value => [
                [
                    'subject' => 'Password reset email not received',
                    'description' => 'I forgot my password and clicked the reset link multiple times. I checked my spam and junk folders but still have not received the reset token email. Please check my account status.'
                ],
                [
                    'subject' => 'Account locked due to too many failed attempts',
                    'description' => 'My account is locked. I typed the wrong password 5 times. How long does the lock last, or can you unlock it from your side? Email: user@company.com'
                ],
                [
                    'subject' => 'MFA verification code SMS delay',
                    'description' => 'The multi-factor verification SMS code takes over 15 minutes to arrive, by which time the session has already expired. Can you switch my MFA method to an authenticator app?'
                ],
                [
                    'subject' => 'Social login button throwing OAuth error',
                    'description' => 'When I try to log in using Google OAuth, I get an error saying redirect_uri_mismatch. Is this a known issue with the login portal?'
                ]
            ],
            TicketCategory::ACCOUNT->value => [
                [
                    'subject' => 'Change account owner permissions',
                    'description' => 'I need to transfer the owner role of our organization account to our senior developer. Her email is dev@company.com. Please let me know what steps are required.'
                ],
                [
                    'subject' => 'Request to delete personal data (GDPR)',
                    'description' => 'I would like to delete my account and all associated personal data permanently under GDPR regulations. Please confirm when the deletion is completed.'
                ],
                [
                    'subject' => 'Disable user accounts for former employees',
                    'description' => 'Could you please deactivate the accounts for john.doe@company.com and jane.smith@company.com? They are no longer with the company. Thanks!'
                ],
                [
                    'subject' => 'Upgrade organization seat limit',
                    'description' => 'We have reached our maximum seat limit of 15 users. We would like to add 5 more team members. Can you guide us on how to upgrade our team size plan?'
                ]
            ],
            TicketCategory::ORDERS->value => [
                [
                    'subject' => 'Order status stuck in processing',
                    'description' => 'I placed order #90210 on July 10th. The status is still stuck in processing. Is there a delay in shipment or stock availability?'
                ],
                [
                    'subject' => 'Modify items in order #90150',
                    'description' => 'I made a mistake in order #90150 and ordered the wrong product version. If it has not been shipped yet, could you swap it for the premium version and bill the difference?'
                ],
                [
                    'subject' => 'Cancel pending order request',
                    'description' => 'Please cancel order #88492 as I purchased the wrong items. I will place a new order immediately. Thank you.'
                ]
            ],
            TicketCategory::SHIPPING->value => [
                [
                    'subject' => 'Change delivery address for order #88410',
                    'description' => 'I entered my previous shipping address by mistake. Please change the shipping address to: 123 Main Street, Suite 400, New York, NY 10001. Hopefully it has not shipped yet.'
                ],
                [
                    'subject' => 'Tracking number shows invalid on carrier website',
                    'description' => 'I received a shipping confirmation with tracking number USPS-99201. However, when I click on it, the USPS site says tracking number not found. Can you verify?'
                ],
                [
                    'subject' => 'Package marked as delivered but not received',
                    'description' => 'According to the tracking link, my order was delivered yesterday. I checked my front porch and asked my neighbors, but nothing is here. Please advise.'
                ]
            ],
            TicketCategory::REFUND->value => [
                [
                    'subject' => 'Refund request for duplicate software license',
                    'description' => 'I accidentally purchased two licenses instead of one. I only activated one of them. Please refund the amount for the second license.'
                ],
                [
                    'subject' => 'Subscription cancelled but still charged',
                    'description' => 'I cancelled my subscription on June 28th, but my card was charged $19 on July 1st. Please refund this charge and verify my subscription status.'
                ],
                [
                    'subject' => 'Unhappy with product quality - request refund',
                    'description' => 'The software has constant crashes on macOS. It does not meet my expectations. I would like to request a full refund within the 30-day money-back guarantee period.'
                ]
            ],
            TicketCategory::PAYMENT->value => [
                [
                    'subject' => 'Bank transfer details for invoice payment',
                    'description' => 'Our accounting team prefers to pay via direct bank transfer (ACH/Wire) instead of credit card. Could you send your bank routing and account number details?'
                ],
                [
                    'subject' => 'Declined payment error code 3D-Secure',
                    'description' => 'My card payment is rejected with a 3D-Secure authentication failed error. I did not receive any OTP prompt from my bank. Can you check if the gateway is operating normally?'
                ],
                [
                    'subject' => 'Apply promo code discount retrospectively',
                    'description' => 'I forgot to enter the discount coupon SAVE20 when checking out order #90210. Is it possible to apply the 20% discount and credit the difference to my account?'
                ]
            ],
            TicketCategory::FEATURE_REQUEST->value => [
                [
                    'subject' => 'Request for Slack integration',
                    'description' => 'Our team coordinates operations on Slack. It would be amazing to receive notifications in a dedicated Slack channel whenever a new ticket is assigned. Is this on the roadmap?'
                ],
                [
                    'subject' => 'Export tickets data to CSV/Excel',
                    'description' => 'We need a button to export filtered tickets list to CSV so we can build custom analytics reports using Google Sheets. Please consider this feature.'
                ],
                [
                    'subject' => 'Bulk edit ticket status',
                    'description' => 'Currently, we have to open each ticket to change its status. It would save our agents hours if we could select multiple tickets and change their status in bulk.'
                ]
            ],
            TicketCategory::BUG_REPORT->value => [
                [
                    'subject' => 'Broken layout on mobile view Safari',
                    'description' => 'When viewing the ticket listing page on iPhone Safari, the column headers overlap with the data rows, making the text unreadable. Please check the tailwind grid/table settings.'
                ],
                [
                    'subject' => 'Rich text editor strips list formatting',
                    'description' => 'When replying to tickets, if I enter a bulleted list in the editor, it saves correctly but is sent as plain text without line breaks to the customer. This looks unprofessional.'
                ],
                [
                    'subject' => 'Session timeout warning popup loop',
                    'description' => 'The session timeout warning modal keeps popping up repeatedly every 10 seconds, even while I am actively typing a response. I have to click continue constantly.'
                ]
            ]
        ];

        $statuses = TicketStatus::values();
        $priorities = TicketPriority::values();
        $categories = TicketCategory::values();

        // 3. Generate 100 tickets
        for ($i = 0; $i < 100; $i++) {
            $category = fake()->randomElement($categories);
            $template = fake()->randomElement($templates[$category]);

            Ticket::create([
                'subject' => $template['subject'] . ' (' . fake()->numerify('Ref-####') . ')',
                'description' => $template['description'] . "\n\nAdditional comments generated automatically by user environment.",
                'category' => $category,
                'status' => fake()->randomElement($statuses),
                'priority' => fake()->randomElement($priorities),
                'created_by' => $allUsers->random()->id,
                'assigned_to' => fake()->boolean(70) ? $agents->random()->id : null,
                'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            ]);
        }
    }
}
