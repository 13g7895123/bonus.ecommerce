-- Migration: AddMileageAmountToRedemptionItems
-- Table: mileage_redemption_items
-- Add mileage_amount column (DECIMAL 8,2, default 0.00)

ALTER TABLE `mileage_redemption_items`
  ADD COLUMN `mileage_amount` DECIMAL(8,2) NOT NULL DEFAULT '0.00' AFTER `sort_order`;
