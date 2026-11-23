CREATE DATABASE IF NOT EXISTS jirag;

USE jirag;

CREATE TABLE IF NOT EXISTS jira_knowledge_chunks
(
    id
    INT
    AUTO_INCREMENT
    PRIMARY
    KEY,
    ticket_key
    VARCHAR(32) NOT NULL,
    title VARCHAR(255) NOT NULL,
    resolution VARCHAR(64) NOT NULL,
    chunk_index INT NOT NULL,
    content TEXT NOT NULL,
    embedding VECTOR(768) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket_key(ticket_key),
    VECTOR INDEX(embedding) DISTANCE=cosine
) ENGINE=InnoDB;
