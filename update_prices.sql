-- ─────────────────────────────────────────────────────────────────
-- NEXUS — Price range update
-- ─────────────────────────────────────────────────────────────────
-- Run this once against your `nexus` database (phpMyAdmin → SQL tab,
-- or `mysql -u root nexus < update_prices.sql` from the command line).
--
-- Updates the price column on products in three categories:
--   • iPhones          ₱400 – ₱500   (set to ₱500)
--   • Android Phones   ₱400 – ₱500   (set to ₱500)
--   • Screen Protectors  ₱1500       (set to ₱1500)
--
-- If you'd rather use the LOW end of the range (₱400) for phones,
-- change the values below before running.
-- ─────────────────────────────────────────────────────────────────

-- iPhones → ₱500
UPDATE products p
JOIN categories c ON c.id = p.category_id
SET p.price = 500
WHERE c.name = 'iPhones';

-- Android Phones → ₱500
UPDATE products p
JOIN categories c ON c.id = p.category_id
SET p.price = 500
WHERE c.name = 'Android Phones';

-- Screen Protectors → ₱1500
UPDATE products p
JOIN categories c ON c.id = p.category_id
SET p.price = 1500
WHERE c.name = 'Screen Protectors';

-- ─────────────────────────────────────────────────────────────────
-- Verify the result
-- ─────────────────────────────────────────────────────────────────
SELECT c.name AS category, p.name AS product, p.price
FROM products p
JOIN categories c ON c.id = p.category_id
WHERE c.name IN ('iPhones', 'Android Phones', 'Screen Protectors')
ORDER BY c.name, p.name;
