<?php

namespace Modules\Tickets\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\DepartmentTranslation;

class DepartmentTranslationFactory extends Factory
{
    protected $model = DepartmentTranslation::class;

    public function definition(): array
    {
        $locale = $this->faker->randomElement(['en', 'es', 'fr', 'de', 'it', 'pt']);

        $translations = [
            'en' => [
                'Technical Support' => 'Technical Support',
                'Billing & Payments' => 'Billing & Payments',
                'General Inquiries' => 'General Inquiries',
                'Sales' => 'Sales',
                'Account Management' => 'Account Management',
                'Bug Reports' => 'Bug Reports',
                'Feature Requests' => 'Feature Requests',
                'Customer Success' => 'Customer Success',
                'Pre-Sales' => 'Pre-Sales',
                'Product Support' => 'Product Support',
            ],
            'es' => [
                'Technical Support' => 'Soporte Técnico',
                'Billing & Payments' => 'Facturación y Pagos',
                'General Inquiries' => 'Consultas Generales',
                'Sales' => 'Ventas',
                'Account Management' => 'Gestión de Cuentas',
                'Bug Reports' => 'Reportes de Errores',
                'Feature Requests' => 'Solicitudes de Funciones',
                'Customer Success' => 'Éxito del Cliente',
                'Pre-Sales' => 'Pre-Ventas',
                'Product Support' => 'Soporte del Producto',
            ],
            'fr' => [
                'Technical Support' => 'Support Technique',
                'Billing & Payments' => 'Facturation et Paiements',
                'General Inquiries' => 'Demandes Générales',
                'Sales' => 'Ventes',
                'Account Management' => 'Gestion de Compte',
                'Bug Reports' => 'Rapports de Bugs',
                'Feature Requests' => 'Demandes de Fonctionnalités',
                'Customer Success' => 'Succès Client',
                'Pre-Sales' => 'Pré-Ventes',
                'Product Support' => 'Support Produit',
            ],
            'de' => [
                'Technical Support' => 'Technischer Support',
                'Billing & Payments' => 'Abrechnung & Zahlungen',
                'General Inquiries' => 'Allgemeine Anfragen',
                'Sales' => 'Vertrieb',
                'Account Management' => 'Kontoverwaltung',
                'Bug Reports' => 'Fehlerberichte',
                'Feature Requests' => 'Feature-Anfragen',
                'Customer Success' => 'Kundenerfolg',
                'Pre-Sales' => 'Vorverkauf',
                'Product Support' => 'Produktsupport',
            ],
        ];

        $baseName = $this->faker->randomElement(array_keys($translations['en']));
        $translatedName = $translations[$locale][$baseName] ?? $baseName;

        return [
            'department_id' => Department::factory(),
            'locale' => $locale,
            'name' => $translatedName,
        ];
    }

    /**
     * Create an English translation.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
        ]);
    }

    /**
     * Create a Spanish translation.
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'es',
        ]);
    }

    /**
     * Create a French translation.
     */
    public function french(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'fr',
        ]);
    }

    /**
     * Create a German translation.
     */
    public function german(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'de',
        ]);
    }

    /**
     * Create a translation for a specific department and locale.
     */
    public function forDepartment(Department $department, string $locale = 'en'): static
    {
        $translations = [
            'en' => $department->name,
            'es' => match ($department->name) {
                'Technical Support' => 'Soporte Técnico',
                'Billing & Payments' => 'Facturación y Pagos',
                'General Inquiries' => 'Consultas Generales',
                'Sales' => 'Ventas',
                default => $department->name,
            },
            'fr' => match ($department->name) {
                'Technical Support' => 'Support Technique',
                'Billing & Payments' => 'Facturation et Paiements',
                'General Inquiries' => 'Demandes Générales',
                'Sales' => 'Ventes',
                default => $department->name,
            },
        ];

        return $this->state(fn (array $attributes) => [
            'department_id' => $department->id,
            'locale' => $locale,
            'name' => $translations[$locale] ?? $department->name,
        ]);
    }
}
