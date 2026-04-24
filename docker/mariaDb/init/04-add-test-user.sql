# Testuser credentials:
# mail: test@test.de
# password : test
INSERT INTO `users` (
    `email`,
    `password_hash`,
    `email_verified_at`,
    `is_active`
)
SELECT
    'test@test.de',
    '$argon2id$v=19$m=65536,t=4,p=1$Qko2c3BqMmExeWRzNHpNbQ$EAkuwx44og86IWAedPev5Nn5iaOsr8DnGDciteN87BQ',
    NOW(),
    1
WHERE NOT EXISTS (
    SELECT 1 FROM `users` WHERE `email` = 'test@test.de'
);