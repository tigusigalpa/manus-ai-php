<?php

namespace Tigusigalpa\ManusAI\Helpers;

class AgentProfile
{
    /**
     * Manus 1.6 - Latest and most capable model (recommended)
     */
    public const MANUS_1_6 = 'manus-1.6';

    /**
     * Manus 1.6 Lite - Faster, lightweight version
     */
    public const MANUS_1_6_LITE = 'manus-1.6-lite';

    /**
     * Manus 1.6 Max - Maximum capability version
     */
    public const MANUS_1_6_MAX = 'manus-1.6-max';

    /**
     * Speed - Deprecated, use MANUS_1_6_LITE instead
     * @deprecated Use MANUS_1_6_LITE instead
     */
    public const SPEED = 'speed';

    /**
     * Quality - Deprecated, use MANUS_1_6 instead
     * @deprecated Use MANUS_1_6 instead
     */
    public const QUALITY = 'quality';

    /**
     * Get all available agent profiles
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            self::MANUS_1_6,
            self::MANUS_1_6_LITE,
            self::MANUS_1_6_MAX,
            self::SPEED,
            self::QUALITY,
        ];
    }

    /**
     * Get recommended agent profiles (non-deprecated)
     *
     * @return array
     */
    public static function recommended(): array
    {
        return [
            self::MANUS_1_6,
            self::MANUS_1_6_LITE,
            self::MANUS_1_6_MAX,
        ];
    }

    /**
     * Check if an agent profile is valid
     *
     * @param string $profile
     * @return bool
     */
    public static function isValid(string $profile): bool
    {
        return in_array($profile, self::all(), true);
    }

    /**
     * Check if an agent profile is deprecated
     *
     * @param string $profile
     * @return bool
     */
    public static function isDeprecated(string $profile): bool
    {
        return in_array($profile, [self::SPEED, self::QUALITY], true);
    }
}
