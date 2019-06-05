CREATE TABLE users
(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    pseudo VARCHAR(100),
    email VARCHAR(100),
    password VARCHAR(100)
);

CREATE TABLE tags
(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    libelle VARCHAR(100)
);

CREATE TABLE articles
(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    titre VARCHAR(100),
    contenu VARCHAR(255),
    date_publication DATE,
    
    tagId int,
    userId int,

    FOREIGN KEY(tagId) REFERENCES tags(id),
    FOREIGN KEY(userId) REFERENCES users(id)
);

CREATE TABLE comments
(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    content VARCHAR(255),
    date_publication DATE,

    articleId int,
    userId int,

    FOREIGN KEY(articleId) REFERENCES articles(id),
    FOREIGN KEY(userId) REFERENCES users(id)
);
