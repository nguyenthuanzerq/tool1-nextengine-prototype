<?php

namespace Tests\Unit;

use App\Models\PlatformOrder;
use PHPUnit\Framework\TestCase;

class PlatformOrderStateTest extends TestCase
{
    public function test_supported_sync_statuses_are_explicit(): void
    {
        $this->assertSame('pending', PlatformOrder::STATUS_PENDING);
        $this->assertSame('success', PlatformOrder::STATUS_SUCCESS);
        $this->assertSame('failed', PlatformOrder::STATUS_FAILED);
        $this->assertSame('ignored', PlatformOrder::STATUS_IGNORED);
    }
}
