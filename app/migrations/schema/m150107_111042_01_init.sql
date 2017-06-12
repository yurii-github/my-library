
CREATE TABLE {{%books}} (
  book_guid    CHAR(36) PRIMARY KEY,
  created_date DATETIME NOT NULL,
  updated_date DATETIME NOT NULL,
  book_cover   BLOB DEFAULT NULL,
  favorite     DECIMAL(3,1) NOT NULL DEFAULT 0,
  read         VARCHAR(3) NOT NULL DEFAULT 'no',
  year         INT,
  title        VARCHAR(255),
  isbn13       VARCHAR(255),
  author       VARCHAR(255),
  publisher    VARCHAR(255),
  ext          VARCHAR(5),
  filename     TEXT
);