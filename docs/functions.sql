-- Funzioni iniziali
CREATE OR REPLACE FUNCTION calculate_quarter(input_date DATE)
RETURNS INTEGER AS $$
BEGIN
    RETURN EXTRACT(QUARTER FROM input_date)::INTEGER;
END;
$$ LANGUAGE plpgsql;
