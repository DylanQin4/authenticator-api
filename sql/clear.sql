-- Vider les tables
TRUNCATE TABLE author, book, category, book_category, loan, "user" RESTART IDENTITY CASCADE;

-- Réinitialiser les séquences
ALTER SEQUENCE author_id_seq RESTART;
ALTER SEQUENCE book_id_seq RESTART;
ALTER SEQUENCE category_id_seq RESTART;
ALTER SEQUENCE loan_id_seq RESTART;
ALTER SEQUENCE user_id_seq RESTART;
