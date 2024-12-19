DROP VIEW IF EXISTS token_valide;
CREATE VIEW token_valide AS
SELECT token.*
FROM token
LEFT JOIN invalide_token
ON token.id = invalide_token.token_id
WHERE invalide_token.token_id IS NULL;