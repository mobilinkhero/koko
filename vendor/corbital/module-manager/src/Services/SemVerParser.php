<?php

namespace Corbital\ModuleManager\Services;

class SemVerParser
{
    /**
     * Parse a version requirement string into components
     * Examples: '>=1.0.0', '1.0.*', '^2.0.0', '~1.2.3', '1.0.0 - 2.0.0'
     *
     * @param  string  $requirement
     * @return array ['operator' => string, 'version' => string, 'secondVersion' => string|null]
     */
    public function parseRequirement($requirement)
    {
        $requirement = trim($requirement);

        // Handle range notation (e.g., '1.0.0 - 2.0.0')
        if (strpos($requirement, ' - ') !== false) {
            [$min, $max] = explode(' - ', $requirement);

            return [
                'operator' => 'range',
                'version' => trim($min),
                'secondVersion' => trim($max),
            ];
        }

        // Handle caret notation (e.g., '^1.2.3' means '>=1.2.3 <2.0.0')
        if (strpos($requirement, '^') === 0) {
            $version = substr($requirement, 1);

            return [
                'operator' => 'caret',
                'version' => $version,
            ];
        }

        // Handle tilde notation (e.g., '~1.2.3' means '>=1.2.3 <1.3.0')
        if (strpos($requirement, '~') === 0) {
            $version = substr($requirement, 1);

            return [
                'operator' => 'tilde',
                'version' => $version,
            ];
        }

        // Handle wildcard notation (e.g., '1.0.*' means '>=1.0.0 <1.1.0')
        if (strpos($requirement, '*') !== false) {
            $operator = 'wildcard';
            $version = substr($requirement, 0, strpos($requirement, '*'));

            return [
                'operator' => $operator,
                'version' => trim($version),
            ];
        }

        // Default to exact version if no operator is specified
        if (preg_match('/^[0-9]+(\.[0-9]+)*$/', $requirement)) {
            return [
                'operator' => '=',
                'version' => $requirement,
            ];
        }

        // Handle operator notation (e.g., '>=1.0.0')
        if (preg_match('/^([<>=!]{1,2})(.+)$/', $requirement, $matches)) {
            return [
                'operator' => $matches[1],
                'version' => trim($matches[2]),
            ];
        }

        return [
            'operator' => '=',
            'version' => $requirement,
        ];
    }

    /**
     * Check if a version satisfies a requirement
     *
     * @param  string  $version  Version to check
     * @param  string  $requirement  Version requirement
     * @return bool
     */
    public function satisfies($version, $requirement)
    {
        $parsedRequirement = $this->parseRequirement($requirement);
        $version = $this->normalizeVersion($version);
        $requiredVersion = $this->normalizeVersion($parsedRequirement['version']);

        switch ($parsedRequirement['operator']) {
            case '=':
            case '==':
                return $this->compareVersions($version, $requiredVersion) === 0;
            case '>':
                return $this->compareVersions($version, $requiredVersion) > 0;
            case '>=':
                return $this->compareVersions($version, $requiredVersion) >= 0;
            case '<':
                return $this->compareVersions($version, $requiredVersion) < 0;
            case '<=':
                return $this->compareVersions($version, $requiredVersion) <= 0;
            case '!=':
                return $this->compareVersions($version, $requiredVersion) !== 0;
            case 'range':
                $secondVersion = $this->normalizeVersion($parsedRequirement['secondVersion']);

                return $this->compareVersions($version, $requiredVersion) >= 0 && $this->compareVersions($version, $secondVersion) <= 0;
            case 'caret':
                // ^1.2.3 means >=1.2.3 <2.0.0
                $versionParts = explode('.', $requiredVersion);
                $upperBound = $versionParts;
                $upperBound[0] = (int) $upperBound[0] + 1;
                $upperBound[1] = 0;
                $upperBound[2] = 0;
                $upperBoundStr = implode('.', $upperBound);

                return $this->compareVersions($version, $requiredVersion) >= 0 && $this->compareVersions($version, $upperBoundStr) < 0;
            case 'tilde':
                // ~1.2.3 means >=1.2.3 <1.3.0
                $versionParts = explode('.', $requiredVersion);
                $upperBound = $versionParts;
                $upperBound[1] = (int) $upperBound[1] + 1;
                $upperBound[2] = 0;
                $upperBoundStr = implode('.', $upperBound);

                return $this->compareVersions($version, $requiredVersion) >= 0 && $this->compareVersions($version, $upperBoundStr) < 0;
            case 'wildcard':
                // 1.2.* means >=1.2.0 <1.3.0
                $versionParts = explode('.', $requiredVersion);

                // Count the number of parts before the wildcard
                $partCount = count($versionParts);

                // Prepare version bounds
                $lowerBound = $versionParts;
                $upperBound = $versionParts;

                // Fill missing parts with zeros for lower bound
                while (count($lowerBound) < 3) {
                    $lowerBound[] = '0';
                }

                // Set upper bound based on which part has the wildcard
                if ($partCount === 1) {
                    // *.x.x or 1.*.* means any version with the same major
                    $upperBound[0] = (int) $upperBound[0] + 1;
                    while (count($upperBound) < 3) {
                        $upperBound[] = '0';
                    }
                } elseif ($partCount === 2) {
                    // 1.*.x means >=1.0.0 <2.0.0
                    $upperBound[0] = (int) $upperBound[0] + 1;
                    $upperBound[1] = 0;
                    while (count($upperBound) < 3) {
                        $upperBound[] = '0';
                    }
                } else {
                    // 1.2.* means >=1.2.0 <1.3.0
                    $upperBound[1] = (int) $upperBound[1] + 1;
                    $upperBound[2] = 0;
                }

                $lowerBoundStr = implode('.', $lowerBound);
                $upperBoundStr = implode('.', $upperBound);

                return $this->compareVersions($version, $lowerBoundStr) >= 0 && $this->compareVersions($version, $upperBoundStr) < 0;
        }

        return false;
    }

    /**
     * Normalize a version string for comparison
     *
     * @param  string  $version
     * @return string
     */
    public function normalizeVersion($version)
    {
        // Remove 'v' prefix if present
        $version = ltrim($version, 'v');

        // Split version into components
        $versionParts = explode('.', $version);

        // Ensure we have at least 3 parts (major.minor.patch)
        while (count($versionParts) < 3) {
            $versionParts[] = '0';
        }

        // Return normalized version
        return implode('.', array_slice($versionParts, 0, 3));
    }

    /**
     * Compare two version strings
     *
     * @param  string  $version1
     * @param  string  $version2
     * @return int 0 if equal, -1 if version1 < version2, 1 if version1 > version2
     */
    public function compareVersions($version1, $version2)
    {
        $v1 = explode('.', $this->normalizeVersion($version1));
        $v2 = explode('.', $this->normalizeVersion($version2));

        for ($i = 0; $i < 3; $i++) {
            $num1 = (int) $v1[$i];
            $num2 = (int) $v2[$i];

            if ($num1 < $num2) {
                return -1;
            }
            if ($num1 > $num2) {
                return 1;
            }
        }

        return 0;
    }
}
