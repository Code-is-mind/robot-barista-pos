-- Add has_modifiers column to products table
ALTER TABLE products ADD COLUMN has_modifiers TINYINT(1) DEFAULT 1 AFTER is_available;

-- Update existing products to have modifiers enabled by default
UPDATE products SET has_modifiers = 1;
