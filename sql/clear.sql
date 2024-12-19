-- Vider les tables
TRUNCATE TABLE invalide_token, token, pin, "user" RESTART IDENTITY CASCADE;

-- Réinitialiser les séquences
ALTER SEQUENCE invalide_token_id_seq RESTART;
ALTER SEQUENCE pin_id_seq RESTART;
ALTER SEQUENCE token_id_seq RESTART;
ALTER SEQUENCE user_id_seq RESTART;
