

DROP TABLE IF EXISTS {{%categories}};
DROP TABLE IF EXISTS {{%books_categories}};

CREATE TABLE {{%categories}} (
  category_guid  CHAR(36) PRIMARY KEY,
  category_title VARCHAR(255)
);

CREATE TABLE {{%books_categories}} (
  book_guid CHAR(36),
  category_guid CHAR(36),
  PRIMARY KEY (book_guid, category_guid)
);