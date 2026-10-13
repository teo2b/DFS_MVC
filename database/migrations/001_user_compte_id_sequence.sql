CREATE SEQUENCE IF NOT EXISTS "2026_M1".user_compte_id_seq AS integer;

ALTER TABLE "2026_M1"."user"
    ALTER COLUMN user_compte_id
    SET DEFAULT nextval('"2026_M1".user_compte_id_seq'::regclass);

ALTER SEQUENCE "2026_M1".user_compte_id_seq
    OWNED BY "2026_M1"."user".user_compte_id;

SELECT setval(
    '"2026_M1".user_compte_id_seq',
    COALESCE(MAX(user_compte_id), 1),
    MAX(user_compte_id) IS NOT NULL
)
FROM "2026_M1"."user";
