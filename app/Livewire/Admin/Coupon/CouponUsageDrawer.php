<?php

namespace App\Livewire\Admin\Coupon;

use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class CouponUsageDrawer extends Component
{
    public bool $open = false;

    public ?Coupon $coupon = null;

    public $usageDetails = [];

    #[On('showUsageDetails')]
    public function showUsageDetails($couponId)
    {
        try {
            $this->coupon = Coupon::find($couponId);

            if ($this->coupon) {
                $this->usageDetails = CouponUsage::with(['tenant', 'invoice', 'subscription.plan'])
                    ->where('coupon_id', $couponId)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($usage) {
                        return [
                            'id' => $usage->id,
                            'tenant_name' => $usage->tenant->company_name ?? 'N/A',
                            'tenant_domain' => $usage->tenant->subdomain ?? 'N/A',
                            'discount_amount' => $usage->discount_amount,
                            'formatted_discount' => get_base_currency()->format($usage->discount_amount),
                            'invoice_id' => $usage->invoice_id,
                            'invoice_total' => $usage->invoice ? get_base_currency()->format($usage->invoice->total()) : 'N/A',
                            'plan_name' => $usage->subscription && $usage->subscription->plan ? $usage->subscription->plan->name : 'N/A',
                            'created_at' => $usage->created_at->format('M d, Y H:i'),
                            'metadata' => $usage->metadata,
                        ];
                    })
                    ->toArray();
            }

            $this->open = true;
        } catch (\Exception $e) {
            Log::error('Error loading coupon usage details: '.$e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to load coupon usage details. Please try again.',
            ]);
        }
    }

    public function closeDrawer()
    {
        $this->open = false;
        $this->coupon = null;
        $this->usageDetails = [];
    }

    public function render()
    {
        return view('livewire.admin.coupon.coupon-usage-drawer');
    }
}
