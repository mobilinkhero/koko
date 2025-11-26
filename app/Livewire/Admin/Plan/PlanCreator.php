<?php

namespace App\Livewire\Admin\Plan;

use App\Models\Plan;
use App\Repositories\PlanRepository;
use App\Rules\AllowNegativeOneOrPositive;
use App\Rules\PurifiedInput;
use App\Services\PlanService;
use App\Services\StripeWebhookService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Livewire\Component;

class PlanCreator extends Component
{
    public $name;

    public $slug;

    public $description;

    public $color = '#000000';

    public $price;

    public $billing_period = 'monthly';

    public $trial_days = 14;

    public $featured = false;

    public $is_active = true;

    public $is_free = false;

    public $planId;

    public bool $isUpdate = false;

    // Feature management
    public $features = [];

    public $availableFeatures = [];

    protected $planService;

    protected $planRepository;

    protected $webhookService;

    public $baseCurrencySymbol;

    public function boot(PlanService $planService, PlanRepository $planRepository, StripeWebhookService $webhookService)
    {
        $this->planService = $planService;
        $this->planRepository = $planRepository;
        $this->webhookService = $webhookService;

        $baseCurrency = get_base_currency();
        // Make the base currency symbol available to the view
        $this->baseCurrencySymbol = $baseCurrency ? $baseCurrency->symbol : '$';
    }

    protected function rules()
    {
        $rule = [
            'name' => ['required', 'unique:plans,name,'.$this->planId, 'max:255', 'min:3', new PurifiedInput(t('sql_injection_error'))],
            'description' => ['required', new PurifiedInput(t('sql_injection_error'))],
            'color' => ['required', new PurifiedInput(t('sql_injection_error'))],
            'price' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($this->is_free) {
                        if ((float) $value != 0) {
                            $fail('The price must be 0 for free plans.');
                        }
                    } else {
                        if ((float) $value < 3) {
                            $fail('The price must be at least 3 for paid plans.');
                        }
                    }
                },
                new PurifiedInput(t('sql_injection_error')),
            ],
            'billing_period' => ['required', 'in:monthly,yearly', new PurifiedInput(t('sql_injection_error'))],
            'trial_days' => ['nullable', 'integer', 'min:0', new PurifiedInput(t('sql_injection_error'))],
            'featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_free' => ['nullable', 'boolean'],
            'features' => ['array'],
            'features.*' => ['required'],
        ];

        // Add slug validation based on create/update state
        if (! $this->isUpdate) {
            // For new plans, slug is required and must be unique
            $rule['slug'] = ['required', 'unique:plans,slug', 'max:255', new PurifiedInput(t('sql_injection_error'))];
        }

        foreach ($this->availableFeatures as $key => $feature) {
            $ruleKey = 'features.'.$feature['id'];

            // Handle specific feature rules
            if ($feature['slug'] === 'conversations') {
                $rule[$ruleKey] = ['numeric', function ($attribute, $value, $fail) {
                    if ($value != -1 && $value <= 0) {
                        $fail('The value must be -1 or greater than 0.');
                    }
                }];
            } elseif (in_array($feature['slug'], ['enable_api', 'ai_assistant', 'ecommerce_bot'])) {
                $rule[$ruleKey] = ['numeric', function ($attribute, $value, $fail) {
                    if (! in_array((int) $value, [-1, 0], true)) {
                        $fail('The value must be either -1 or 0.');
                    }
                }];
            } else {
                // Default rules for other features
                $rule[$ruleKey] = $feature['default'] == 1
                    ? ['required', 'numeric', new AllowNegativeOneOrPositive]
                    : ['numeric', 'min:-1'];
            }
        }

        return $rule;
    }

    public function messages(): array
    {
        $message = [];
        foreach ($this->availableFeatures as $key => $feature) {
            $message['features.'.$feature['id'].'.required'] = 'This field is required';
            $message['features.'.$feature['id'].'.numeric'] = 'This field must be a number';
            $message['features.'.$feature['id'].'.min'] = 'The field value must be at least -1.';
        }

        return $message;
    }

    public function mount()
    {
        if (! checkPermission(['admin.plans.edit', 'admin.plans.create'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return $this->redirect(route('admin.dashboard'), navigate: true);
        }

        $this->planId = request()->route('planId') ?? null;

        // Load available features - use cache if possible
        // Use static caching to prevent multiple feature loads
        static $featuresCache = null;
        if ($featuresCache === null) {
            $featuresCache = app()->make('App\Services\PlanFeatureCache')->getFeatures();
            if ($featuresCache->isEmpty()) {
                $featuresCache = $this->planRepository->getAllFeatures();
            }
        }
        $this->availableFeatures = $featuresCache;

        if ($this->planId) {
            // Use static caching for the plan to prevent reloading
            static $planCache = [];
            if (! isset($planCache[$this->planId])) {
                // More efficient loading with specific columns and eager loading
                $planCache[$this->planId] = Plan::with(['planFeatures:id,plan_id,feature_id,value'])
                    ->select([
                        'id',
                        'name',
                        'slug',
                        'description',
                        'color',
                        'price',
                        'billing_period',
                        'trial_days',
                        'featured',
                        'is_free',
                        'is_active',
                    ])
                    ->find($this->planId);
            }

            $plan = $planCache[$this->planId];

            if ($plan) {
                $this->name = $plan->name;
                $this->slug = $plan->slug;
                $this->description = $plan->description;
                $this->color = $plan->color;
                $this->price = $plan->price;
                $this->billing_period = $plan->billing_period;
                $this->trial_days = $plan->trial_days;
                $this->featured = (bool) $plan->featured;
                $this->is_free = (bool) $plan->is_free;

                // Load feature values
                foreach ($plan->planFeatures as $planFeature) {
                    $this->features[$planFeature->feature_id] = $planFeature->value;
                }
            }
        } else {
            // Set default values for features
            foreach ($this->availableFeatures as $feature) {
                if ($feature->slug == 'conversations') {
                    $this->features[$feature->id] = -1;
                } elseif (in_array($feature->slug, ['enable_api', 'ai_assistant', 'ecommerce_bot'])) {
                    $this->features[$feature->id] = 0; // Disabled by default
                } else {
                    $this->features[$feature->id] = 0;
                }
            }
        }

        $this->isUpdate = (bool) $this->planId;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        // Auto-generate slug when name changes (only for new plans)
        if ($propertyName === 'name' && empty($this->slug) && ! $this->isUpdate) {
            $this->slug = Str::slug($this->name);
        }

        // If plan is free, set price to 0
        if ($propertyName === 'is_free' && $this->is_free) {
            $this->price = 0;
        }
    }

    public function save()
    {

        if (checkPermission(['admin.plans.create', 'admin.plans.edit'])) {
            $this->validate();
            // Prepare plan data
            $baseCurrency = get_base_currency();

            $planData = [
                'name' => $this->name,
                'description' => $this->description,
                'color' => $this->color,
                'price' => $this->is_free ? 0 : $this->price,
                'billing_period' => $this->billing_period,
                'trial_days' => $this->is_free ? $this->trial_days : 0,
                'featured' => $this->featured ?? false,
                'is_active' => true,
                'is_free' => $this->is_free ?? false,
                'currency_id' => $baseCurrency?->id,
                'interval' => 1,
            ];

            // Set the slug for new plans
            if (! $this->isUpdate) {
                // Ensure we have a slug either from the input or generated from name
                $this->slug = $this->slug ?: Str::slug($this->name);
                $planData['slug'] = $this->slug;
            }

            try {
                if ($this->isUpdate) {
                    // Update existing plan
                    $this->planService->updatePlan($this->planId, $planData, $this->features);
                    $message = t('plan_updated_successfully');
                } else {
                    // Create new plan
                    $this->planService->createPlan($planData, $this->features);
                    $message = t('plan_created_successfully');
                }

                Artisan::call('cache:clear');

                $this->notify(['type' => 'success', 'message' => $message], true);

                return $this->redirect(route('admin.plans.list'), navigate: true);
            } catch (\Exception $e) {
                $this->notify([
                    'type' => 'danger',
                    'message' => t('error_saving_plan').': '.$e->getMessage(),
                ]);
            }
        }
    }

    public function render()
    {
        // Precompute this only once
        static $renderedFeatures = null;
        if ($renderedFeatures === null) {
            $renderedFeatures = $this->availableFeatures;
        }

        return view('livewire.admin.plan.plan-creator', [
            'availableFeatures' => $renderedFeatures,
            'baseCurrencySymbol' => $this->baseCurrencySymbol,
        ]);
    }
}
