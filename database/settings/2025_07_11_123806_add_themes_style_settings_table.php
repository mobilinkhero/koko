<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('theme.theme_style')) {
            $themeData = [
                'primary' => [
                    'valueStop' => 600,
                    'lMax' => 100,
                    'lMin' => 0,
                    'hex' => '#4f46e5',
                    'swatches' => [
                        ['stop' => 0, 'hex' => '#FFFFFF', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 100],
                        ['stop' => 50, 'hex' => '#F2F2FD', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 97],
                        ['stop' => 100, 'hex' => '#E1E0FB', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 93],
                        ['stop' => 200, 'hex' => '#C3C0F6', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 86],
                        ['stop' => 300, 'hex' => '#A5A1F2', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 79],
                        ['stop' => 400, 'hex' => '#8782ED', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 72],
                        ['stop' => 500, 'hex' => '#6D67EA', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 66],
                        ['stop' => 600, 'hex' => '#4F46E5', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 58.6],
                        ['stop' => 700, 'hex' => '#241CC5', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 44],
                        ['stop' => 800, 'hex' => '#181282', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 29],
                        ['stop' => 900, 'hex' => '#0C0943', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 15],
                        ['stop' => 950, 'hex' => '#06041F', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 7],
                        ['stop' => 1000, 'hex' => '#000000', 'h' => 243, 'hScale' => 0, 's' => 75.4, 'sScale' => 0, 'l' => 0],
                    ],
                ],
                'danger' => [
                    'valueStop' => 600,
                    'lMax' => 100,
                    'lMin' => 0,
                    'hex' => '#dc2626',
                    'swatches' => [
                        ['stop' => 0, 'hex' => '#FFFFFF', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 100],
                        ['stop' => 50, 'hex' => '#FCEDED', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 96],
                        ['stop' => 100, 'hex' => '#F9DCDC', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 92],
                        ['stop' => 200, 'hex' => '#F4B9B9', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 84],
                        ['stop' => 300, 'hex' => '#ED9191', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 75],
                        ['stop' => 400, 'hex' => '#E86E6E', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 67],
                        ['stop' => 500, 'hex' => '#E24B4B', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 59],
                        ['stop' => 600, 'hex' => '#DC2626', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 50.6],
                        ['stop' => 700, 'hex' => '#A71B1B', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 38],
                        ['stop' => 800, 'hex' => '#6E1212', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 25],
                        ['stop' => 900, 'hex' => '#390909', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 13],
                        ['stop' => 950, 'hex' => '#1A0404', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 6],
                        ['stop' => 1000, 'hex' => '#000000', 'h' => 0, 'hScale' => 0, 's' => 72.2, 'sScale' => 0, 'l' => 0],
                    ],
                ],
                'warning' => [
                    'valueStop' => 600,
                    'lMax' => 100,
                    'lMin' => 0,
                    'hex' => '#eab308',
                    'swatches' => [
                        ['stop' => 0, 'hex' => '#FFFFFF', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 100],
                        ['stop' => 50, 'hex' => '#fefce8', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 95],
                        ['stop' => 100, 'hex' => '#fef9c3', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 90],
                        ['stop' => 200, 'hex' => '#fef08a', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 80],
                        ['stop' => 300, 'hex' => '#fde047', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 70],
                        ['stop' => 400, 'hex' => '#facc15', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 60],
                        ['stop' => 500, 'hex' => '#eab308', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 50],
                        ['stop' => 600, 'hex' => '#eab308', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 40.4],
                        ['stop' => 700, 'hex' => '#a16207', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 30],
                        ['stop' => 800, 'hex' => '#854d0e', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 20],
                        ['stop' => 900, 'hex' => '#713f12', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 10],
                        ['stop' => 950, 'hex' => '#191100', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 5],
                        ['stop' => 1000, 'hex' => '#000000', 'h' => 41, 'hScale' => 0, 's' => 96.1, 'sScale' => 0, 'l' => 0],
                    ],
                ],
                'success' => [
                    'valueStop' => 600,
                    'lMax' => 100,
                    'lMin' => 0,
                    'hex' => '#16a34a',
                    'swatches' => [
                        ['stop' => 0, 'hex' => '#FFFFFF', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 100],
                        ['stop' => 50, 'hex' => '#E9FCF0', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 95],
                        ['stop' => 100, 'hex' => '#CEF8DD', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 89],
                        ['stop' => 200, 'hex' => '#A1F2BF', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 79],
                        ['stop' => 300, 'hex' => '#6FEC9D', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 68],
                        ['stop' => 400, 'hex' => '#42E67E', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 58],
                        ['stop' => 500, 'hex' => '#1DD35F', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 47],
                        ['stop' => 600, 'hex' => '#16A34A', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 36.3],
                        ['stop' => 700, 'hex' => '#107937', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 27],
                        ['stop' => 800, 'hex' => '#0B5125', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 18],
                        ['stop' => 900, 'hex' => '#052812', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 9],
                        ['stop' => 950, 'hex' => '#03160A', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 5],
                        ['stop' => 1000, 'hex' => '#000000', 'h' => 142, 'hScale' => 0, 's' => 76.2, 'sScale' => 0, 'l' => 0],
                    ],
                ],
                'info' => [
                    'valueStop' => 600,
                    'lMax' => 100,
                    'lMin' => 0,
                    'hex' => '#0284c7',
                    'swatches' => [
                        ['stop' => 0, 'hex' => '#FFFFFF', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 100],
                        ['stop' => 50, 'hex' => '#E6F6FF', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 95],
                        ['stop' => 100, 'hex' => '#CDEEFE', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 90],
                        ['stop' => 200, 'hex' => '#9ADDFE', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 80],
                        ['stop' => 300, 'hex' => '#68CBFD', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 70],
                        ['stop' => 400, 'hex' => '#35BAFD', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 60],
                        ['stop' => 500, 'hex' => '#03A9FC', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 50],
                        ['stop' => 600, 'hex' => '#0284C7', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 39.4],
                        ['stop' => 700, 'hex' => '#026597', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 30],
                        ['stop' => 800, 'hex' => '#014465', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 20],
                        ['stop' => 900, 'hex' => '#012232', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 10],
                        ['stop' => 950, 'hex' => '#001119', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 5],
                        ['stop' => 1000, 'hex' => '#000000', 'h' => 200, 'hScale' => 0, 's' => 98, 'sScale' => 0, 'l' => 0],
                    ],
                ],
                'neutral' => [
                    'valueStop' => 600,
                    'lMax' => 100,
                    'lMin' => 0,
                    'hex' => '#525252',
                    'swatches' => [
                        ['stop' => 0, 'hex' => '#FFFFFF', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 100],
                        ['stop' => 50, 'hex' => '#F0F0F0', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 94],
                        ['stop' => 100, 'hex' => '#E3E3E3', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 89],
                        ['stop' => 200, 'hex' => '#C4C4C4', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 77],
                        ['stop' => 300, 'hex' => '#A8A8A8', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 66],
                        ['stop' => 400, 'hex' => '#8C8C8C', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 55],
                        ['stop' => 500, 'hex' => '#707070', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 44],
                        ['stop' => 600, 'hex' => '#525252', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 32.2],
                        ['stop' => 700, 'hex' => '#3D3D3D', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 24],
                        ['stop' => 800, 'hex' => '#292929', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 16],
                        ['stop' => 900, 'hex' => '#141414', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 8],
                        ['stop' => 950, 'hex' => '#0A0A0A', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 4],
                        ['stop' => 1000, 'hex' => '#000000', 'h' => 0, 'hScale' => 0, 's' => 0, 'sScale' => 0, 'l' => 0],
                    ],
                ],
                'secondary' => [
                    'valueStop' => 600,
                    'lMax' => 100,
                    'lMin' => 0,
                    'hex' => '#4b5563',
                    'swatches' => [
                        ['stop' => 0, 'hex' => '#FFFFFF', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 100],
                        ['stop' => 50, 'hex' => '#F0F2F4', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 95],
                        ['stop' => 100, 'hex' => '#DFE2E7', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 89],
                        ['stop' => 200, 'hex' => '#BFC6CF', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 78],
                        ['stop' => 300, 'hex' => '#9FA9B6', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 67],
                        ['stop' => 400, 'hex' => '#7F8C9E', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 56],
                        ['stop' => 500, 'hex' => '#637083', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 45],
                        ['stop' => 600, 'hex' => '#4B5563', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 34.1],
                        ['stop' => 700, 'hex' => '#39414B', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 26],
                        ['stop' => 800, 'hex' => '#252A31', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 17],
                        ['stop' => 900, 'hex' => '#14161A', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 9],
                        ['stop' => 950, 'hex' => '#090A0C', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 4],
                        ['stop' => 1000, 'hex' => '#000000', 'h' => 215, 'hScale' => 0, 's' => 13.8, 'sScale' => 0, 'l' => 0],
                    ],
                ],
            ];

            $this->migrator->add('theme.theme_style', json_encode($themeData));
        }

        if (! $this->migrator->exists('theme.theme_style_modified_at')) {
            $this->migrator->add('theme.theme_style_modified_at', null);
        }
    }

    public function down(): void
    {
        $this->migrator->delete('theme.theme_style');
        $this->migrator->delete('theme.theme_style_modified_at');
    }
};
