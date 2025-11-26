<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'slug' => 'test-email',
                'name' => 'Test Email',
                'subject' => 'This is a test email from {company_name}',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['user-group', 'other-group']),
                'content' => '<p>Hello {first_name} {last_name},</p><p>This is a test email from <strong>{company_name}</strong> to confirm your email configuration is working correctly.</p><p>Thank you, </p><p>{company_name} Team</p>',
            ],
            [
                'slug' => 'tenant-welcome-mail',
                'name' => 'Tenant Welcome Email',
                'subject' => 'Welcome to {company_name}, {tenant_company_name}!',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group']),
                'content' => '<p>Hello {tenant_company_name},</p><p>Welcome to <strong>{company_name}</strong>! We are thrilled to have you on board. Your account has been successfully created and is ready to use.</p><p>If you have any questions or need assistance, feel free to reach out.</p><p>Best regards,</p><p>{company_name} Team</p>',
            ],
            [
                'slug' => 'staff-welcome-mail',
                'name' => 'Staff Welcome Email',
                'subject' => 'Welcome to {company_name}, {first_name} {last_name}!',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['user-group', 'other-group']),
                'content' => '<p>Hello {first_name} {last_name},</p>
                <p>Welcome to <strong>{company_name}</strong>! Your staff account has been successfully created and is ready to use.</p>
                <p>If you have any questions or need assistance, feel free to reach out.</p>
                <p>Best regards,</p>
                <p>{company_name} Team</p>',
            ],
            [
                'slug' => 'new-tenant-reminder-email-to-admin',
                'name' => 'New Tenant Reminder Email to Admin',
                'subject' => 'New tenant {tenant_company_name} has signed up',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'user-group']),
                'content' => '<p>Hi {first_name} {last_name},</p><p>A new tenant <strong>{tenant_company_name}</strong> has just signed up on <strong>{company_name}.</strong></p><p>Please log in to your admin panel to review the details.</p><p>Regards,</p><p>{company_name} </p>',
            ],
            [
                'slug' => 'subscription-renewal-success',
                'name' => 'Subscription Renewal Payment Successful',
                'subject' => 'Your subscription has been successfully renewed',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'subscription-group', 'plan-group']),
                'content' => '<p>Hi {tenant_company_name},</p><p>We are happy to let you know that your subscription has been successfully renewed.</p><p><strong>Plan:</strong> {plan_name}</p><p><strong>Amount:</strong> {plan_price}</p><p>Thank you for staying with us!</p><p>Best,</p><p>{company_name} Team</p>',
            ],
            [
                'slug' => 'subscription-renewal-failed',
                'name' => 'Subscription Renewal Payment Failed',
                'subject' => 'Subscription renewal failed for {tenant_company_name}',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'subscription-group', 'plan-group']),
                'content' => '<p>Hi {tenant_company_name},</p><p>Unfortunately, your recent subscription renewal attempt was unsuccessful.</p><p>Please update your payment method to avoid any disruption in service.</p><p>Best regards,</p><p>{company_name} Team</p>',
            ],
            [
                'slug' => 'subscription-created',
                'name' => 'Subscription Created',
                'subject' => 'Your subscription to {plan_name} has been created',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'subscription-group', 'plan-group']),
                'content' => '<p>Hello {tenant_company_name},</p><p>Your subscription to the {plan_name} plan has been successfully created.</p><p>Next billing date: {subscription_period_ends_at}</p><p>We hope you enjoy our services!</p><p>Thanks,</p><p>{company_name} Team</p>',
            ],
            [
                'slug' => 'subscription-activated',
                'name' => 'Subscription Activated',
                'subject' => 'Your subscription is now active!',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'subscription-group', 'plan-group']),
                'content' => '<p>Hi {tenant_company_name},</p><p>Your subscription to {plan_name} is now active.</p><p>Enjoy all the benefits that come with your plan. If you need support, don’t hesitate to contact us.</p><p>Cheers,</p><p>{company_name} Team</p>',
            ],
            [
                'slug' => 'invoice-receipt',
                'name' => 'Invoice Receipt',
                'subject' => 'Your payment receipt for Invoice #{invoice_number}',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'invoice-group']),
                'content' => '<p>Hello {tenant_company_name},</p><p>Thank you for your payment.</p><p><strong>Invoice Number:</strong> {invoice_number}</p><p><strong>Amount:</strong> {invoice_total}</p><p><strong>Date:</strong> {invoice_paid_at}</p><p>You can download your invoice from your dashboard at any time.</p><p>Best Regards,</p><p>{company_name}</p>',
            ],
            [
                'slug' => 'subscription-renewal-reminder',
                'name' => 'Subscription Renewal Reminder',
                'subject' => 'Your subscription will renew soon',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'subscription-group']),
                'content' => '<p>Hi {tenant_company_name},</p><p>This is a reminder that your subscription to {subscription_plan_name} will renew on {subscription_period_ends_at}.</p><p>If you need to make any changes to your plan or payment method, please do so before this date.</p><p>Thank you,</p><p>{company_name} Team</p>',
            ],
            [
                'slug' => 'subscription-expiring-soon',
                'name' => 'Subscription Expiring Soon',
                'subject' => 'Your subscription is expiring soon',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'subscription-group', 'plan-group']),
                'content' => '<p>Dear {company_name},</p><p>Your subscription to {subscription_plan_name} is set to expire on {subscription_period_ends_at}.</p><p>To continue uninterrupted service, please renew or update your subscription details before the expiration date.</p><p>Best regards,</p><p>{company_name} Team</p>',
            ],
            [
                'slug' => 'payment-approved',
                'name' => 'Payment Approved Email',
                'subject' => 'Payment approved for Invoice #{invoice_number}',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'invoice-group', 'transaction-group']),
                'content' => '<p>Hello {tenant_company_name},</p><p>We have successfully received your payment for <strong>Invoice #{invoice_number}</strong></p><p><strong>Amount Paid:</strong> {invoice_total}</p><p><strong>Date:</strong> {invoice_paid_at}</p><p>Thank you for your business!</p><p>Best,</p><p>{company_name} Team</p>',
            ],
            [
                'slug' => 'transection-created-reminder-mail-to-admin',
                'name' => 'Transaction Created Reminder to Admin',
                'subject' => 'New transaction recorded for tenant {tenant_company_name}',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'transaction-group', 'user-group']),
                'content' => '<p>Hello Admin,</p><p>A new transaction has been created for {tenant_company_name}.</p><p><strong>Status:</strong> {tenant_status}</p><p><strong>Amount:</strong> {transaction_amount}</p><p>Check the admin panel for more details.</p><p>Regards,</p><p>{company_name}</p>',
            ],
            [
                'slug' => 'subscription-expired',
                'name' => 'Subscription Expired',
                'subject' => 'Your subscription has expired',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'subscription-group', 'plan-group']),
                'content' => '<p>Hi {tenant_company_name},</p><p>Your subscription to {plan_name} has expired as of {subscription_period_ends_at}.</p><p>Please renew your plan to continue enjoying our services without interruption.</p><p>Thanks,</p><p>{company_name} Team</p>',
            ],
            [
                'slug' => 'subscription-cancelled',
                'name' => 'Subscription Cancelled',
                'subject' => 'Your subscription has been cancelled',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'subscription-group', 'plan-group']),
                'content' => '<p>Hello {tenant_company_name},</p><p>We\'re confirming that your subscription to {plan_name} was cancelled on {subscription_cancelled_at}.</p><p>If this was a mistake or you wish to rejoin, you can renew your subscription anytime.</p><p>Thank you,</p><p>{company_name} Team</p>',
            ],
            [
                'slug' => 'payment-rejected',
                'name' => 'Payment Rejected Notification',
                'subject' => 'Payment for your invoice was rejected',
                'layout_id' => '1',
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'invoice-group']),
                'content' => '<p>Dear {company_name},</p><p>Unfortunately, your payment for <strong>Invoice #{invoice_number}</strong> has been rejected.</p><p>Please update your payment method or try again to avoid interruption of service.</p><p>Thank you,</p><p>{company_name} Team</p>',
            ],
            [
                'slug' => 'email-confirmation',
                'name' => 'Email Confirmation',
                'subject' => 'Verify Your Email Address',
                'layout_id' => '1',
                'type' => null,
                'merge_fields_groups' => json_encode(['user-group', 'other-group']),
                'content' => '<p>Hello {first_name} {last_name},</p>
                            <p>Thank you for signing up! Please verify your email address by clicking the button below:</p>
                            <p><a href="{verification_url}" class="button">Verify Email Address</a></p>
                            <p>If you did not create an account, no further action is required.</p>
                            <p>Thanks,<br>{company_name} Team</p>',
            ],
            [
                'slug' => 'password-reset',
                'name' => 'Password Reset',
                'subject' => 'Reset Password Notification',
                'layout_id' => '1',
                'type' => null,
                'merge_fields_groups' => json_encode(['user-group', 'other-group']),
                'content' => '<p>Hello {first_name} {last_name},</p>
                            <p>You are receiving this email because we received a password reset request for your account.</p>
                            <p><a href="{reset_url}" class="button">Reset Password</a></p>
                            <p>This password reset link will expire in 60 minutes.</p>
                            <p>If you did not request a password reset, no further action is required.</p>
                            <p>Regards,<br>{company_name} Team</p>',
            ],
            [
                'slug' => 'subscription-renewal-invoice',
                'name' => 'Subscription renewal invoice email to tenant',
                'subject' => 'Subscription renewal invoice',
                'layout_id' => '1',
                'type' => null,
                'merge_fields_groups' => json_encode(['tenant-group', 'other-group', 'subscription-group', 'plan-group', 'invoice-group', 'user-group']),
                'content' => '<p>Hello {first_name} {last_name},</p>
            <p>We’ve generated your renewal invoice for the <strong>{plan_name}</strong> subscription.</p>
            <h2>Invoice Summary</h2>
              <ul style="list-style: none; padding: 0;">
                <li><strong>Plan:</strong> {plan_name}</li>
            </ul>
            <p>If you have any questions or need help with the payment process, feel free to contact our support team.</p>

            <p>Thank you for being a valued customer of {company_name}.</p>

            <p style="margin-top: 40px;">Best regards,<br>
            The {company_name} Team</p>
        </div>
               ',
            ],
            [
                'slug' => 'ticket-created',
                'name' => 'New Ticket Created (Admin Notification)',
                'subject' => 'New Support Ticket Created - {ticket_subject}',
                'layout_id' => 1,
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['ticket-group', 'other-group', 'tenant-group']),
                'content' => '<p>Hello Admin,</p>
                    <p>A new support ticket has been created and requires your attention.</p>

                    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #495057;">Ticket Details</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr><td style="padding: 8px 0; font-weight: bold;">Ticket ID:</td><td style="padding: 8px 0;">{ticket_id}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Subject:</td><td style="padding: 8px 0;">{ticket_subject}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Priority:</td><td style="padding: 8px 0;">{ticket_priority}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Department:</td><td style="padding: 8px 0;">{ticket_department}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Status:</td><td style="padding: 8px 0;">{ticket_status}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Created:</td><td style="padding: 8px 0;">{ticket_created_at}</td></tr>
                        </table>
                    </div>

                    <div style="background-color: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #1976d2;">Customer Information</h3>
                        <p><strong>Company:</strong> {tenant_company_name}</p>
                    </div>

                    <div style="background-color: #fff3e0; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #f57c00;">Ticket Description</h3>
                        <div style="background-color: #ffffff; padding: 15px; border-left: 4px solid #ff9800; border-radius: 4px;">
                            {ticket_body}
                        </div>
                    </div>

                    <p>Please review and assign this ticket at your earliest convenience.</p>

                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{admin_url}" style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">View Ticket in Admin Panel</a>
                    </div>

                    <p>Best regards,<br>{company_name} System</p>',
            ],
            // Ticket Reply Notification (To Tenant)
            [
                'slug' => 'ticket-reply-tenant',
                'name' => 'Ticket Reply Notification (To Tenant)',
                'subject' => 'Support Ticket Reply - {ticket_subject}',
                'layout_id' => 1,
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['ticket-group', 'other-group', 'tenant-group']),
                'content' => '<p>Hello {tenant_company_name},</p>
                    <p>You have received a new reply on your support ticket.</p>

                    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #495057;">Ticket Information</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr><td style="padding: 8px 0; font-weight: bold;">Ticket ID:</td><td style="padding: 8px 0;">{ticket_id}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Subject:</td><td style="padding: 8px 0;">{ticket_subject}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Status:</td><td style="padding: 8px 0;">{ticket_status}</td></tr>
                        </table>
                    </div>

                    <p>You can view the full conversation and reply by clicking the link below:</p>

                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{ticket_url}" style="background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">View & Reply to Ticket</a>
                    </div>

                    <p>Best regards,<br>{company_name} Support Team</p>',
            ],

            // Ticket Reply Notification (To Admin)
            [
                'slug' => 'ticket-reply-admin',
                'name' => 'Ticket Reply Notification (To Admin)',
                'subject' => 'New Reply on Ticket {ticket_id} - {ticket_subject}',
                'layout_id' => 1,
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['ticket-group', 'other-group', 'tenant-group']),
                'content' => '<p>Hello {assigned_user_name},</p>
                    <p>A new reply has been added to ticket <strong>{ticket_id}</strong>.</p>

                    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #495057;">Ticket Details</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr><td style="padding: 8px 0; font-weight: bold;">Ticket ID:</td><td style="padding: 8px 0;">{ticket_id}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Subject:</td><td style="padding: 8px 0;">{ticket_subject}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Priority:</td><td style="padding: 8px 0;">{ticket_priority}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Status:</td><td style="padding: 8px 0;">{ticket_status}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Customer:</td><td style="padding: 8px 0;">{tenant_company_name}</td></tr>
                        </table>
                    </div>

                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{ticket_admin_url}" style="background-color: #dc3545; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">View Ticket in Admin Panel</a>
                    </div>

                    <p>Best regards,<br>{company_name} System</p>',
            ],

            [
                'slug' => 'ticket-status-changed',
                'name' => 'Ticket Status Changed Notification',
                'subject' => 'Ticket Status Updated - {ticket_subject}',
                'layout_id' => 1,
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['ticket-group', 'other-group', 'tenant-group']),
                'content' => '<p>Hello {tenant_company_name},</p>
                    <p>The status of your support ticket has been updated.</p>

                    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #495057;">Ticket Information</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr><td style="padding: 8px 0; font-weight: bold;">Ticket ID:</td><td style="padding: 8px 0;">{ticket_id}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Subject:</td><td style="padding: 8px 0;">{ticket_subject}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Previous Status:</td><td style="padding: 8px 0;">{previous_status}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">New Status:</td><td style="padding: 8px 0; color: #007bff;"><strong>{new_status}</strong></td></tr>
                        </table>
                    </div>

                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{ticket_url}" style="background-color: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">View Ticket Details</a>
                    </div>

                    <p>Best regards,<br>{company_name} Support Team</p>',
            ],

            // Admin Ticket Status Change Notification
            [
                'slug' => 'ticket-status-changed-admin',
                'name' => 'Ticket Status Changed Notification (Admin)',
                'subject' => 'Ticket Status Updated by Tenant - {ticket_subject}',
                'layout_id' => 1,
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['ticket-group', 'other-group', 'tenant-group']),
                'content' => '<p>Hello Admin,</p>
                    <p>A ticket status has been updated by the tenant.</p>

                    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #495057;">Ticket Information</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr><td style="padding: 8px 0; font-weight: bold;">Ticket ID:</td><td style="padding: 8px 0;">{ticket_id}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Subject:</td><td style="padding: 8px 0;">{ticket_subject}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Customer:</td><td style="padding: 8px 0;">{tenant_company_name}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Previous Status:</td><td style="padding: 8px 0;">{previous_status}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">New Status:</td><td style="padding: 8px 0; color: #007bff;"><strong>{new_status}</strong></td></tr>
                        </table>
                    </div>

                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{ticket_admin_url}" style="background-color: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">View in Admin Panel</a>
                    </div>

                    <p>Best regards,<br>{company_name} System</p>',
            ],

            // Ticket Assigned
            [
                'slug' => 'ticket-assigned',
                'name' => 'Ticket Assigned Notification',
                'subject' => 'Ticket Assigned to You - {ticket_subject}',
                'layout_id' => 1,
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['ticket-group', 'other-group', 'tenant-group']),
                'content' => '<p>Hello {assigned_user_name},</p>
                    <p>A support ticket has been assigned to you.</p>

                    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #495057;">Ticket Details</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr><td style="padding: 8px 0; font-weight: bold;">Ticket ID:</td><td style="padding: 8px 0;">{ticket_id}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Subject:</td><td style="padding: 8px 0;">{ticket_subject}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Priority:</td><td style="padding: 8px 0;">{ticket_priority}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Department:</td><td style="padding: 8px 0;">{ticket_department}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Customer:</td><td style="padding: 8px 0;">{tenant_company_name}</td></tr>
                            <tr><td style="padding: 8px 0; font-weight: bold;">Created:</td><td style="padding: 8px 0;">{ticket_created_at}</td></tr>
                        </table>
                    </div>

                    <div style="background-color: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #1976d2;">Ticket Description</h3>
                        <div style="background-color: #ffffff; padding: 15px; border-left: 4px solid #2196f3; border-radius: 4px;">
                            {ticket_body}
                        </div>
                    </div>

                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{ticket_admin_url}" style="background-color: #6f42c1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">View & Respond to Ticket</a>
                    </div>

                    <p>Best regards,<br>{company_name} System</p>',
            ],
            [
                'slug' => 'transaction-success',
                'name' => 'Transaction Successful',
                'subject' => 'Payment Confirmed - {transaction_amount} from {company_name}',
                'layout_id' => 1,
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['transaction-group', 'user-group', 'other-group']),
                'content' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 28px;">✅ Payment Successful!</h1>
    </div>

    <div style="background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; line-height: 1.6; margin: 0 0 20px;">Hello <strong>{first_name} {last_name}</strong>,</p>

        <p style="font-size: 16px; line-height: 1.6; margin: 0 0 25px;">Great news! Your payment has been successfully processed. Here are the details:</p>

        <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin: 25px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Transaction ID:</td>
                    <td style="padding: 8px 0; text-align: right; font-family: monospace;">{transaction_id}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Amount:</td>
                    <td style="padding: 8px 0; text-align: right; color: #10b981; font-size: 18px; font-weight: bold;">{transaction_amount}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Payment Method:</td>
                    <td style="padding: 8px 0; text-align: right;">{transaction_type}</td>
                </tr>
                <tr style="border-top: 2px solid #e5e7eb;">
                    <td style="padding: 12px 0 8px; font-weight: bold;">Description:</td>
                    <td style="padding: 12px 0 8px; text-align: right;">{transaction_description}</td>
                </tr>
            </table>
        </div>

                    <p>Thank you for choosing {company_name}.</p>
                    <p>Best regards,<br>{company_name} Support Team</p>
    </div>
</div>',
            ],
            [
                'slug' => 'transaction-pending',
                'name' => 'Transaction Pending',
                'subject' => 'Payment Pending - Action Required for {transaction_amount} - {company_name}',
                'layout_id' => 1,
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['transaction-group', 'user-group', 'other-group']),
                'content' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 28px;">⏳ Payment Pending</h1>
    </div>

    <div style="background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; line-height: 1.6; margin: 0 0 20px;">Hello <strong>{first_name} {last_name}</strong>,</p>

        <p style="font-size: 16px; line-height: 1.6; margin: 0 0 25px;">Your payment is currently being processed. Additional verification may be required to complete this transaction.</p>

        <div style="background: #fef3c7; padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #f59e0b;">
            <p style="margin: 0; font-weight: bold; color: #92400e;">⚠️ Action May Be Required</p>
            <p style="margin: 10px 0 0; color: #92400e;">Some payments require additional authentication (like 3D Secure) or take time to process depending on your bank or payment method.</p>
        </div>

        <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin: 25px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Transaction ID:</td>
                    <td style="padding: 8px 0; text-align: right; font-family: monospace;">{transaction_id}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Amount:</td>
                    <td style="padding: 8px 0; text-align: right; color: #f59e0b; font-size: 18px; font-weight: bold;">{transaction_amount}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Payment Method:</td>
                    <td style="padding: 8px 0; text-align: right;">{transaction_type}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Status:</td>
                    <td style="padding: 8px 0; text-align: right; color: #f59e0b; font-weight: bold;">Pending</td>
                </tr>
                <tr style="border-top: 2px solid #e5e7eb;">
                    <td style="padding: 12px 0 8px; font-weight: bold;">Description:</td>
                    <td style="padding: 12px 0 8px; text-align: right;">{transaction_description}</td>
                </tr>
            </table>
        </div>

        <div style="background: #eff6ff; padding: 20px; border-radius: 8px; margin: 25px 0;">
            <h3 style="margin: 0 0 15px; color: #1e40af;">What happens next?</h3>
            <ul style="margin: 0; padding-left: 20px; color: #1e40af;">
                <li>We will send you an update once the payment is processed</li>
                <li>Most pending payments are resolved within 24 hours</li>
                <li>You may receive additional verification requests from your bank</li>
                <li>Check your email and banking app for any notifications</li>
            </ul>
        </div>

        <p style="font-size: 16px; line-height: 1.6; margin: 25px 0 0;">Thank you for your patience,</p>
        <p style="font-size: 16px; line-height: 1.6; margin: 5px 0 0;"><strong>{company_name} Team</strong></p>
    </div>
</div>',
            ],
            [
                'slug' => 'transaction-failed',
                'name' => 'Transaction Failed',
                'subject' => 'Payment Failed - {transaction_amount} - {company_name}',
                'layout_id' => 1,
                'type' => 'admin',
                'merge_fields_groups' => json_encode(['transaction-group', 'user-group', 'other-group']),
                'content' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 28px;">❌ Payment Failed</h1>
    </div>

    <div style="background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; line-height: 1.6; margin: 0 0 20px;">Hello <strong>{first_name} {last_name}</strong>,</p>

        <p style="font-size: 16px; line-height: 1.6; margin: 0 0 25px;">Unfortunately, we were unable to process your payment. Please try again.</p>

        <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin: 25px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Transaction ID:</td>
                    <td style="padding: 8px 0; text-align: right; font-family: monospace;">{transaction_id}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Amount:</td>
                    <td style="padding: 8px 0; text-align: right; color: #ef4444; font-size: 18px; font-weight: bold;">{transaction_amount}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Payment Method:</td>
                    <td style="padding: 8px 0; text-align: right;">{transaction_type}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Status:</td>
                    <td style="padding: 8px 0; text-align: right; color: #ef4444; font-weight: bold;">Failed</td>
                </tr>
                <tr style="border-top: 2px solid #e5e7eb;">
                    <td style="padding: 12px 0 8px; font-weight: bold;">Description:</td>
                    <td style="padding: 12px 0 8px; text-align: right;">{transaction_description}</td>
                </tr>
            </table>
        </div>

        <div style="background: #f0f9ff; padding: 20px; border-radius: 8px; margin: 25px 0;">
            <h3 style="margin: 0 0 15px; color: #0369a1;">💡 How to resolve this:</h3>
            <ul style="margin: 0; padding-left: 20px; color: #0369a1;">
                <li><strong>Check your card details:</strong> Ensure card number, expiry date, and CVV are correct</li>
                <li><strong>Verify billing address:</strong> Make sure your billing address matches your card</li>
                <li><strong>Check your account balance:</strong> Ensure you have sufficient funds available</li>
                <li><strong>Contact your bank:</strong> Your card issuer may have declined the transaction</li>
                <li><strong>Try a different payment method:</strong> Use another card or payment option</li>
            </ul>
        </div>

        <p style="font-size: 16px; line-height: 1.6; margin: 5px 0 0;"><strong>{company_name} Team</strong></p>
    </div>
</div>',
            ],

        ];

        foreach ($templates as $template) {
            if (! EmailTemplate::where('slug', $template['slug'])->exists()) {
                EmailTemplate::create($template);
            }
        }
    }
}
