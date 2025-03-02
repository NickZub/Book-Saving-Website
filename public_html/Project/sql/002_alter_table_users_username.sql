ALTER TABLE Users ADD COLUMN username VARCHAR(30)
NOT NULL UNIQUE DEFAULT (SUBSTRING_INDEX(email, '@', 1))
COMMENT 'Username field that defaults to the name of the email given';