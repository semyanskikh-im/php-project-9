CREATE TABLE IF NOT EXISTS urls (
    id BIGINT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS checks (
    id BIGINT PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    url_id BIGINT NOT NULL REFERENCES urls (id),
    status_code integer,
    h1 text,
    title text,
    description text,
    created_at TIMESTAMP NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_checks_url_id_created_at ON checks(url_id, created_at DESC);