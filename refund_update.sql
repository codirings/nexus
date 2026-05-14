ALTER TABLE orders
MODIFY status ENUM(
'pending',
'processing',
'shipped',
'completed',
'cancelled',
'refund_requested',
'refunded'
) NOT NULL DEFAULT 'pending';
