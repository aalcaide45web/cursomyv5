PRAGMA journal_mode=WAL;
PRAGMA synchronous=NORMAL;

CREATE TABLE topic (
  id INTEGER PRIMARY KEY,
  name TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE
);

CREATE TABLE instructor (
  id INTEGER PRIMARY KEY,
  name TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE
);

CREATE TABLE course (
  id INTEGER PRIMARY KEY,
  topic_id INTEGER NOT NULL,
  instructor_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE,
  cover_path TEXT,
  avg_rating REAL DEFAULT 0,
  ratings_count INTEGER DEFAULT 0,
  is_deleted INTEGER DEFAULT 0,
  FOREIGN KEY(topic_id) REFERENCES topic(id),
  FOREIGN KEY(instructor_id) REFERENCES instructor(id)
);

CREATE TABLE section (
  id INTEGER PRIMARY KEY,
  course_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  order_index INTEGER DEFAULT 0,
  FOREIGN KEY(course_id) REFERENCES course(id)
);

CREATE TABLE lesson (
  id INTEGER PRIMARY KEY,
  section_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  file_path TEXT NOT NULL UNIQUE,
  duration_seconds REAL DEFAULT 0,
  thumb_path TEXT,
  order_index INTEGER DEFAULT 0,
  FOREIGN KEY(section_id) REFERENCES section(id)
);

CREATE TABLE rating (
  id INTEGER PRIMARY KEY,
  course_id INTEGER NOT NULL,
  stars INTEGER NOT NULL CHECK(stars BETWEEN 1 AND 5),
  created_at TEXT NOT NULL,
  FOREIGN KEY(course_id) REFERENCES course(id)
);

CREATE TABLE note (
  id INTEGER PRIMARY KEY,
  lesson_id INTEGER NOT NULL,
  t_seconds REAL NOT NULL,
  text TEXT NOT NULL,
  created_at TEXT NOT NULL,
  FOREIGN KEY(lesson_id) REFERENCES lesson(id)
);

CREATE TABLE comment (
  id INTEGER PRIMARY KEY,
  lesson_id INTEGER NOT NULL,
  text TEXT NOT NULL,
  t_seconds REAL,
  created_at TEXT NOT NULL,
  FOREIGN KEY(lesson_id) REFERENCES lesson(id)
);

CREATE TABLE progress (
  id INTEGER PRIMARY KEY,
  lesson_id INTEGER NOT NULL,
  last_t_seconds REAL NOT NULL,
  updated_at TEXT NOT NULL,
  FOREIGN KEY(lesson_id) REFERENCES lesson(id)
);

CREATE TABLE scan_index (
  id INTEGER PRIMARY KEY,
  file_path TEXT NOT NULL UNIQUE,
  file_mtime INTEGER NOT NULL,
  file_size INTEGER NOT NULL,
  file_hash TEXT,
  last_seen_at TEXT NOT NULL
);

CREATE INDEX idx_section_course ON section(course_id);
CREATE INDEX idx_lesson_section ON lesson(section_id);
CREATE INDEX idx_progress_lesson ON progress(lesson_id);
CREATE INDEX idx_note_lesson ON note(lesson_id);
CREATE INDEX idx_comment_lesson ON comment(lesson_id);
CREATE INDEX idx_rating_course ON rating(course_id);

-- FTS5 para búsqueda global (opcional)
CREATE VIRTUAL TABLE search_index USING fts5(
  entity_type,   -- topic | instructor | course | section | lesson | note | comment
  title,         -- nombre o título
  body,          -- texto descriptivo
  course_id UNINDEXED,
  lesson_id UNINDEXED,
  t_seconds UNINDEXED
);
