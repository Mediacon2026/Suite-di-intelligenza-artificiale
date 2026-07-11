-- RECOVERY-001: completa esclusivamente recapiti mancanti delle sedi note.
-- I valori già presenti non vengono sovrascritti.

BEGIN;

UPDATE offices
SET email = COALESCE(email, 'pachino@mediacon.org'),
    pec = COALESCE(pec, 'mediaconpachino@arubapec.it'),
    updated_at = CURRENT_TIMESTAMP
WHERE lower(name) = 'pachino';

UPDATE offices
SET email = COALESCE(email, 'napoli@mediacon.org'),
    pec = COALESCE(pec, 'mediaconnapoli@arubapec.it'),
    updated_at = CURRENT_TIMESTAMP
WHERE lower(name) = 'napoli';

COMMIT;
