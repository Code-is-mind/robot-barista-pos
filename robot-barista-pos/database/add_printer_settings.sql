-- Add printer settings to existing database
INSERT INTO settings (setting_key, setting_value) VALUES
('printer_enabled', '1'),
('printer_type', 'network'),
('printer_paper_width', '80')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Update existing printer settings if they don't exist
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('printer_ip', '192.168.1.100'),
('printer_port', '9100');
