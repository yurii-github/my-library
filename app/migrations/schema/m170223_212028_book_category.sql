

DROP TABLE IF EXISTS {{%categories}};
DROP TABLE IF EXISTS {{%books_categories}};

CREATE TABLE {{%categories}} (
  guid  CHAR(36) PRIMARY KEY,
  title VARCHAR(255)
);

CREATE TABLE {{%books_categories}} (
  book_guid CHAR(36),
  category_guid CHAR(36),
  PRIMARY KEY (book_guid, category_guid)
);