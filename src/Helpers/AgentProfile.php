<?php

namespace Tigusigalpa\ManusAI\Helpers;

class AgentProfile
{
    /** Current default profile. */
    public const STANDARD = 'standard';

    /** Faster, lower-cost profile. */
    public const LITE = 'lite';

    /** Highest-capability profile. */
    public const MAX = 'max';

    /**
     * Legacy alias accepted by Manus for STANDARD.
     */
    public const MANUS_1_6 = 'manus-1.6';

    /**
     * Legacy alias accepted by Manus for LITE.
     */
    public const MANUS_1_6_LITE = 'manus-1.6-lite';

    /**
     * Legacy alias accepted by Manus for MAX.
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
            self::STANDARD,
            self::LITE,
            self::MAX,
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
            self::STANDARD,
            self::LITE,
            self::MAX,
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
        return in_array($profile, [
            self::MANUS_1_6,
            self::MANUS_1_6_LITE,
            self::MANUS_1_6_MAX,
            self::SPEED,
            self::QUALITY,
        ], true);
    }
}
