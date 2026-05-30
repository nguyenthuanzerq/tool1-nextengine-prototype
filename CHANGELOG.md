# Changelog - Yahoo Shopping API Integration

Date: May 29, 2026

## New Features
- **Yahoo Shopping Integration**: Full support for connecting and interacting with Yahoo Shopping seller APIs.
- **OAuth2 Authentication Flow**: Full OAuth2 flow for Yahoo (Authorization Code, Access Token exchange, and automatic Refresh Token).
- **XML API Communication**: Custom XML data structure to meet the strict format requirements (Circus APIs) of Yahoo Shopping.
- **Order Retrieval**: Integrate `OrderList` and `OrderInfo` APIs to fetch and sync order data from Yahoo.
- **Shipping Status Updates**: Integrate order status updates (`orderShipStatusChange`) into `OrderController` to push status information to Yahoo.
- **Platform Configuration UI**: Add a "Seller ID" input field to the platform connection management UI, shown or hidden based on the user's platform selection.

## Technical Changes
- **Database**: Add a migration to include the `seller_id` column (string, nullable) in `platform_connections` to store the required Yahoo Seller ID. Update the related model (`$fillable`).
- **New Class**: Create `App\Connectors\YahooConnector` implementing `OAuthConnector`, containing all Yahoo API interaction logic.
- **Routes**: Register web routes for the Yahoo OAuth2 redirect and callback flow.
- **Error Handling**: Add XML error parsing logic for Yahoo API responses to return more readable error messages.

## Bug Fixes
- Update `resources/views/btoc/shop/detail.blade.php`: Fix a `ParseError` caused by an extra `@endif` while adding the Seller ID show/hide feature.

## Testing
- Confirm all current features and components remain stable and unaffected (Regression Tests passed) via `php artisan test`.
