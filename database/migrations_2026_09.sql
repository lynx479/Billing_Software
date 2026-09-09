-- =============================================================================
-- Migration: 2026-09 modernization update
-- Safe to run against an EXISTING lx_accounting database (fresh installs that
-- import database/lx_accounting.sql already have these changes and can skip
-- this file). Every statement below is idempotent (IF NOT EXISTS / guarded).
-- =============================================================================

-- 1. Timestamp for Pay-In / Pay-Out records, needed to show a correct
--    system Date + Time (separate columns, 24-hour clock) in listings/previews.
ALTER TABLE `acc_payments`
    ADD COLUMN IF NOT EXISTS `created_at` datetime DEFAULT current_timestamp();

-- 2. Configurable application timezone (Settings > Customization). Falls back
--    to Asia/Kolkata if this row is missing (see app/core/Currency.php::app_timezone()).
INSERT INTO `acc_settings` (`setting_key`, `setting_value`)
VALUES ('timezone', 'Asia/Kolkata')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- 3. is_interstate flag on Credit Notes was already part of the table but is
--    now always populated on save; backfill any legacy NULLs to a safe default.
UPDATE `acc_credit_notes` SET `is_interstate` = 0 WHERE `is_interstate` IS NULL;
