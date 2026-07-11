-- Sprint 7 - AI Document Engine 1.0
-- Existing Sprint 6 case tables already store extracted_text and extracted_json.
-- This migration only adds optional linkage fields for future document relations.

ALTER TABLE case_documents ADD COLUMN IF NOT EXISTS linked_party_id INTEGER;
ALTER TABLE case_documents ADD COLUMN IF NOT EXISTS linked_lawyer_id INTEGER;
ALTER TABLE case_documents ADD COLUMN IF NOT EXISTS linked_session_id INTEGER;
ALTER TABLE case_documents ADD COLUMN IF NOT EXISTS linked_payment_id INTEGER;
ALTER TABLE case_documents ADD COLUMN IF NOT EXISTS linked_pec_id INTEGER;
ALTER TABLE case_documents ADD COLUMN IF NOT EXISTS linked_record_type VARCHAR(100);
ALTER TABLE case_documents ADD COLUMN IF NOT EXISTS linked_record_id INTEGER;
