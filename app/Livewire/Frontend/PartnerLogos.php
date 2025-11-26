<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class PartnerLogos extends Component
{
    /**
     * The partner logos from settings
     *
     * @var array
     */
    public $logos;

    /**
     * Maximum number of logos to display
     *
     * @var int
     */
    public $maxLogos;

    /**
     * Additional CSS classes
     *
     * @var string
     */
    public $class;

    /**
     * Create a new component instance.
     *
     * @param  int  $maxLogos
     * @param  string  $class
     * @return void
     */
    public function __construct($maxLogos = null, $class = '')
    {
        $this->logos = $this->getPartnerLogos();
        $this->maxLogos = $maxLogos;
        $this->class = $class;
    }

    /**
     * Get the partner logos from settings
     *
     * @return array
     */
    protected function getPartnerLogos()
    {
        $logos = [];
        $settings = get_batch_settings(['theme.partner_logos']);
        $savedLogos = $settings['theme.partner_logos'] ?? null;

        if ($savedLogos) {
            $logosArray = json_decode($savedLogos, true);

            if (is_array($logosArray)) {
                $logos = $logosArray;
            }
        }

        return $logos;
    }

    public function render()
    {
        $displayLogos = $this->maxLogos ? array_slice($this->logos, 0, $this->maxLogos) : $this->logos;

        return view('livewire.frontend.partner-logos', [
            'displayLogos' => $displayLogos,
        ]);
    }
}
