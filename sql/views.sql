CREATE VIEW token_valide AS
SELECT token.*
FROM token
LEFT JOIN invalide_token
ON token.id = invalide_token.id;