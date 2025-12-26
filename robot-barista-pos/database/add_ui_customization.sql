-- Add UI customization settings
-- Run this migration to add new features

USE robot_barista_pos;

-- Add new settings for UI customization only
INSERT INTO settings (setting_key, setting_value) VALUES
('ui_navbar_color', '#16a34a'),
('ui_bg_color', '#f3f4f6'),
('ui_primary_color', '#16a34a'),
('ui_bg_image', '')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Update business_name if not set
UPDATE settings SET setting_value = 'Robot Barista Cafe' 
WHERE setting_key = 'business_name' AND (setting_value IS NULL OR setting_value = '');
