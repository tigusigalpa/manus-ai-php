<?php

namespace Tigusigalpa\ManusAI\Helpers;

class AgentProfile
{
    /**
     * Manus 1.5 - Latest and most capable model (recommended)
     */
    public const MANUS_1_5 = 'manus-1.5';

    /**
     * Manus 1.5 Lite - Faster, lightweight version
     */
    public const MANUS_1_5_LITE = 'manus-1.5-lite';

    /**
     * Speed - Deprecated, use MANUS_1_5_LITE instead
     * @deprecated Use MANUS_1_5_LITE instead
     */
    public const SPEED = 'speed';

    /**
     * Quality - Deprecated, use MANUS_1_5 instead
     * @deprecated Use MANUS_1_5 instead
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
            self::MANUS_1_5,
            self::MANUS_1_5_LITE,
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
            self::MANUS_1_5,
            self::MANUS_1_5_LITE,
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
