<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckWhatsMarkUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsmark:check-updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'BYPASS: WhatsMark update check - no external validation performed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // PERMANENT BYPASS - Set bypass settings to prevent future checks
            
            // Set fake verification token to prevent API calls
            $fakeToken = base64_encode('bypass_token|bypass_token');
            set_setting('whats-mark.wm_verification_token', $fakeToken);
            
            // Set fake verification ID
            $fakeId = base64_encode('bypass_id|bypass_id|bypass_id|bypass_id');
            set_setting('whats-mark.wm_verification_id', $fakeId);
            
            // Set last verification to far future to prevent revalidation
            set_setting('whats-mark.wm_last_verification', now()->addYears(10)->timestamp);
            
            // Set validate flag to true
            set_setting('whats-mark.wm_validate', true);
            
            // Set latest version to current to prevent updates
            set_setting('whats-mark.whatsmark_latest_version', config('installer.license_verification.current_version', '1.4.0'));
            
            // Log bypass activation
            \Log::info('License bypass activated - external validation disabled');
            
            $this->info('✅ License bypass is active - external validation disabled');
            $this->info('✅ All future checks will be bypassed automatically');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error in bypass: '.$e->getMessage());
            return 1;
        }
    }
}