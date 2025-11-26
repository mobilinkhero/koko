<?php

namespace App\Livewire\Admin\Coupon;

use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Plan;
use App\Services\CouponService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CouponForm extends Component
{
    public ?Coupon $coupon = null;

    public string $code = '';

    public string $name = '';

    public string $description = '';

    public string $type = 'percentage';

    public float $value = 0;

    public ?int $usage_limit = null;

    public ?int $usage_limit_per_customer = null;

    public ?string $starts_at = null;

    public ?string $expires_at = null;

    public ?float $minimum_amount = null;

    public ?float $maximum_discount = null;

    public array $applicable_plans = [];

    public array $applicable_billing_periods = [];

    public bool $first_payment_only = false;

    public bool $is_active = true;

    public array $availableBillingPeriods = ['monthly', 'yearly'];

    public $plans = [];

    public function rules()
    {
        $rules = [
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:percentage,fixed_amount',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_customer' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'applicable_plans' => 'nullable|array',
            'applicable_billing_periods' => 'nullable|array',
            'first_payment_only' => 'boolean',
            'is_active' => 'boolean',
        ];

        // Dynamic validation for value based on type
        if ($this->type === 'percentage') {
            $rules['value'] = 'required|numeric|min:0.01|max:100';
        } else {
            $rules['value'] = 'required|numeric|min:0.01';
        }

        return $rules;
    }

    public function mount($id = null)
    {
        $this->plans = Plan::all();

        if ($id) {
            $this->coupon = Coupon::findOrFail($id);
            $this->code = $this->coupon->code;
            $this->name = $this->coupon->name;
            $this->description = $this->coupon->description ?? '';
            $this->type = $this->coupon->type;
            $this->value = (float) $this->coupon->value;
            $this->usage_limit = $this->coupon->usage_limit;
            $this->usage_limit_per_customer = $this->coupon->usage_limit_per_customer;
            $this->starts_at = $this->coupon->starts_at ? $this->coupon->starts_at->format('Y-m-d') : null;
            $this->expires_at = $this->coupon->expires_at ? $this->coupon->expires_at->format('Y-m-d') : null;
            $this->minimum_amount = $this->coupon->minimum_amount;
            $this->maximum_discount = $this->coupon->maximum_discount;
            $this->applicable_plans = $this->coupon->applicable_plans ? array_map('intval', $this->coupon->applicable_plans) : [];
            $this->applicable_billing_periods = $this->coupon->applicable_billing_periods ?? [];
            $this->first_payment_only = (bool) $this->coupon->first_payment_only;
            $this->is_active = (bool) $this->coupon->is_active;
        }
    }

    public function generateCode()
    {
        $couponService = app(CouponService::class);
        $this->code = $couponService->generateUniqueCode('PROMO-');
    }

    public function getCurrencySymbol()
    {
        return Currency::getDefault()->symbol ?? '$';
    }

    public function updatedType()
    {
        // Reset validation errors when type changes
        $this->resetErrorBag('value');

        // Reset value if switching types to avoid confusion
        $this->value = 0;
    }

    public function save()
    {
        $this->validate();

        if (! $this->coupon) {
            $this->coupon = new Coupon;
        }

        $this->coupon->code = strtoupper($this->code);
        $this->coupon->name = $this->name;
        $this->coupon->description = $this->description;
        $this->coupon->type = $this->type;
        $this->coupon->value = (string) $this->value;
        $this->coupon->usage_limit = $this->usage_limit;
        $this->coupon->usage_limit_per_customer = $this->usage_limit_per_customer;
        $this->coupon->starts_at = $this->starts_at ? $this->starts_at : null;
        $this->coupon->expires_at = $this->expires_at ? $this->expires_at : null;
        $this->coupon->minimum_amount = $this->minimum_amount;
        $this->coupon->maximum_discount = $this->maximum_discount;
        $this->coupon->applicable_plans = ! empty($this->applicable_plans) ? $this->applicable_plans : null;
        $this->coupon->applicable_billing_periods = ! empty($this->applicable_billing_periods) ? $this->applicable_billing_periods : null;
        $this->coupon->first_payment_only = $this->first_payment_only;
        $this->coupon->is_active = $this->is_active;
        $this->coupon->created_by = Auth::id();
        $this->coupon->save();

        session()->flash('message', 'Coupon saved successfully.');

        return redirect()->route('admin.coupons.list');
    }

    public function toggleActiveSwitch()
    {
        try {
            $this->is_active = ! $this->is_active;

        } catch (\Exception $e) {
            report($e);
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_update_status').': '.$e->getMessage(),
            ]);
        }
    }

    public function togglePaymentSwitch()
    {
        try {
            $this->first_payment_only = ! $this->first_payment_only;

        } catch (\Exception $e) {
            report($e);
            $this->notify([
                'type' => 'danger',
                'message' => t('failed_to_update_payment_status').': '.$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        // Get active plans
        $activePlans = Plan::where('is_active', true)->get();

        // If editing, also include any previously selected plans that might now be inactive
        if ($this->coupon && ! empty($this->applicable_plans)) {
            $selectedPlans = Plan::whereIn('id', $this->applicable_plans)->get();
            $plans = $activePlans->merge($selectedPlans)->unique('id');
        } else {
            $plans = $activePlans;
        }

        $availableBillingPeriods = ['monthly', 'yearly', 'lifetime'];

        return view('livewire.admin.coupon.coupon-form', [
            'plans' => $plans,
            'availableBillingPeriods' => $availableBillingPeriods,
        ]);
    }
}
