<?php

namespace App\Contracts;

use App\Models\PlatformConnection;
use Illuminate\Http\Request;

interface PlatformConnector
{
    /** Returns 'oauth2' | 'api_key' | 'basic' */
    public function authType(): string;

    /** Ping the platform API with the stored credentials. Returns true if OK. */
    public function testConnection(PlatformConnection $conn): bool;

    /** Fetch orders from the platform. Yields/returns array of raw platform rows. */
    public function fetchOrders(PlatformConnection $conn, array $opts = []): iterable;

    /** Fetch inventory from the platform. Yields/returns array of raw platform rows. */
    public function fetchInventory(PlatformConnection $conn, array $opts = []): iterable;

    /**
     * Map a single raw order row from this platform into our standard field set.
     *
     * Standard fields (all nullable):
     *   platform_order_id, platform_order_status,
     *   ordered_at, shipped_at,
     *   buyer_name, buyer_email, buyer_phone, buyer_zip, buyer_address, customer_type,
     *   delivery_name, delivery_zip, delivery_address, delivery_method,
     *   payment_method,
     *   goods_amount, delivery_fee, total_amount,
     *   tracking_number
     *
     * raw_data is set automatically by persistOrders() — do NOT include it here.
     */
    public function normalizeOrder(array $raw): array;

    /**
     * Map a single raw inventory row from this platform into our standard field set.
     *
     * Standard fields (all nullable):
     *   sku, product_name, quantity, warehouse_code, synced_at
     */
    public function normalizeInventory(array $raw): array;

    /**
     * Batch-fetch line items for a set of order IDs from the platform.
     * Returns raw platform rows. Return [] if not supported.
     *
     * @param  string[] $orderIds  Platform order IDs (e.g. receive_order_id for NextEngine)
     */
    public function fetchOrderItems(PlatformConnection $conn, array $orderIds): iterable;

    /**
     * Map a single raw order item row into our standard field set.
     *
     * Standard fields:
     *   platform_item_id  — platform's own row identifier (required)
     *   product_code      — SKU (nullable)
     *   product_name      — (nullable)
     *   quantity          — integer
     *   unit_price        — decimal
     *   total_price       — decimal
     *
     * meta is set automatically by persistOrders() — do NOT include it here.
     */
    public function normalizeOrderItem(array $raw): array;

    /** Handle inbound webhook payload (no-op for platforms without webhooks). */
    public function webhookHandler(Request $request): void;
}
