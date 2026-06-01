-- Add signature image column to information_updation_forms table
ALTER TABLE information_updation_forms 
ADD COLUMN signature_image VARCHAR(255) NULL AFTER sign_date;
