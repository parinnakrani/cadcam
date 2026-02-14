-- Mark invoice migrations as complete since tables already exist from SQL dump

INSERT INTO migrations (version, class, `group`, namespace, time, batch) 
VALUES 
('2026-01-01-000014', 'App\\Database\\Migrations\\CreateInvoicesTable', 'default', 'App', UNIX_TIMESTAMP(), 11),
('2026-01-01-000015', 'App\\Database\\Migrations\\CreateInvoiceLinesTable', 'default', 'App', UNIX_TIMESTAMP(), 11);
