<?php

namespace App\Contracts;

use App\Models\PlatformConnection;

/** For API-Key platforms: Rakuten */
interface ApiKeyConnector extends PlatformConnector
{
    /** Return HTTP headers array with the pre-built Authorization header. */
    public function buildAuthHeaders(PlatformConnection $conn): array;
}
