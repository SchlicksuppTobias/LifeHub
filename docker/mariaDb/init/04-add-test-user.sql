INSERT INTO `users` (
    `email`,
    `password_hash`,
    `email_verified_at`,
    `is_active`
)
SELECT
    'test@test.de',
    '$argon2id$v=19$m=65536,t=4,p=1$ZW03a2RFOUw5RGt1ZGE4Tg$uotDswcF2yWhVHQg0Qx5oIuRz9WyLE3Z5mDA2sF/5qU',
    NOW(),
    1
WHERE NOT EXISTS (
    SELECT 1 FROM `users` WHERE `email` = 'test@test.de'
);